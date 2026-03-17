<?php

namespace App\Services;

use App\Models\HarvestedArticle;
use App\Models\Journal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OaiPmhService
{
    /**
     * Execute Identify verb to get repository information.
     */
    public function identify(string $baseUrl): array
    {
        $response = Http::timeout(30)->get($baseUrl, [
            'verb' => 'Identify',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("Failed to connect to OAI endpoint: {$baseUrl}");
        }

        $xml = simplexml_load_string($response->body());
        if ($xml === false) {
            throw new \RuntimeException("Invalid XML response from: {$baseUrl}");
        }

        $identify = $xml->Identify;

        return [
            'repositoryName' => (string) $identify->repositoryName,
            'baseURL' => (string) $identify->baseURL,
            'protocolVersion' => (string) $identify->protocolVersion,
            'earliestDatestamp' => (string) $identify->earliestDatestamp,
            'adminEmail' => (string) $identify->adminEmail,
            'granularity' => (string) $identify->granularity,
        ];
    }

    /**
     * Harvest all records from a journal's OAI endpoint using ListRecords verb.
     * Supports resumption token pagination.
     * Returns count of new/updated records.
     */
    public function listRecords(Journal $journal, ?callable $onProgress = null): int
    {
        if (!$journal->oai_base_url) {
            throw new \RuntimeException("Journal #{$journal->id} has no OAI base URL configured.");
        }

        // Determine best metadata prefix if not set
        $metadataPrefix = $journal->oai_metadata_prefix;
        if (!$metadataPrefix) {
            $metadataPrefix = $this->getBestMetadataPrefix($journal->oai_base_url);
            $journal->update(['oai_metadata_prefix' => $metadataPrefix]);
        }

        $count = 0;
        $resumptionToken = null;

        // Use from parameter for incremental harvesting
        $from = $journal->oai_last_harvested_at?->toIso8601String();

        do {
            $params = $this->buildListRecordsParams($journal, $resumptionToken, $from);
            // Ensure we use the correct metadata prefix in the params
            if (!$resumptionToken) {
                $params['metadataPrefix'] = $metadataPrefix;
            }

            $response = Http::timeout(60)->get($journal->oai_base_url, $params);

            if ($response->failed()) {
                Log::error("OAI harvest failed for journal #{$journal->id}", [
                    'status' => $response->status(),
                    'url' => $journal->oai_base_url,
                ]);
                throw new \RuntimeException("HTTP request failed with status: {$response->status()}");
            }

            $xml = simplexml_load_string($response->body());
            if ($xml === false) {
                throw new \RuntimeException("Invalid XML response from: {$journal->oai_base_url}");
            }

            // Check for OAI-PMH errors
            if (isset($xml->error)) {
                $errorCode = (string) $xml->error['code'];
                $errorMessage = (string) $xml->error;

                // noRecordsMatch is not fatal — just means no new records
                if ($errorCode === 'noRecordsMatch') {
                    break;
                }

                throw new \RuntimeException("OAI-PMH error [{$errorCode}]: {$errorMessage}");
            }

            $listRecords = $xml->ListRecords;
            if (!$listRecords) {
                break;
            }

            foreach ($listRecords->record as $record) {
                $parsed = null;
                if ($metadataPrefix === 'marcxml') {
                    $parsed = $this->parseRecordMarc($record);
                } else {
                    $parsed = $this->parseRecord($record);
                }

                if ($parsed === null) {
                    continue;
                }

                HarvestedArticle::updateOrCreate(
                    ['identifier' => $parsed['identifier']],
                    array_merge($parsed, ['journal_id' => $journal->id])
                );

                $count++;

                if ($onProgress) {
                    $onProgress($count, $parsed['title']);
                }
            }

            // Handle resumption token for pagination
            $resumptionToken = null;
            if (isset($listRecords->resumptionToken) && (string) $listRecords->resumptionToken !== '') {
                $resumptionToken = (string) $listRecords->resumptionToken;
            }

        } while ($resumptionToken !== null);

        // Update harvest timestamp
        $journal->update([
            'oai_last_harvested_at' => now(),
        ]);

        return $count;
    }

    /**
     * Preview a few records from a URL (for testing).
     */
    public function previewRecords(string $baseUrl, ?string $setSpec = null, string $prefix = 'oai_dc', int $limit = 5): array
    {
        $params = ['verb' => 'ListRecords', 'metadataPrefix' => $prefix];
        if ($setSpec) {
            $params['set'] = $setSpec;
        }

        $response = Http::timeout(30)->get($baseUrl, $params);

        if ($response->failed()) {
            throw new \RuntimeException("Failed to connect to: {$baseUrl}");
        }

        $xml = simplexml_load_string($response->body());
        if ($xml === false) {
            throw new \RuntimeException("Invalid XML response");
        }

        if (isset($xml->error)) {
            throw new \RuntimeException("OAI error [{$xml->error['code']}]: {$xml->error}");
        }

        $records = [];
        $i = 0;
        foreach ($xml->ListRecords->record ?? [] as $record) {
            if ($i >= $limit) break;
            $parsed = ($prefix === 'marcxml') ? $this->parseRecordMarc($record) : $this->parseRecord($record);
            if ($parsed) {
                $records[] = $parsed;
                $i++;
            }
        }

        return $records;
    }

    /**
     * Build query parameters for ListRecords request.
     */
    private function buildListRecordsParams(Journal $journal, ?string $resumptionToken, ?string $from): array
    {
        // If we have a resumption token, that's the only param needed
        if ($resumptionToken) {
            return [
                'verb' => 'ListRecords',
                'resumptionToken' => $resumptionToken,
            ];
        }

        $params = [
            'verb' => 'ListRecords',
            'metadataPrefix' => $journal->oai_metadata_prefix ?? 'oai_dc',
        ];

        if ($journal->oai_set_spec) {
            $params['set'] = $journal->oai_set_spec;
        }

        if ($from) {
            $params['from'] = $from;
        }

        return $params;
    }

    /**
     * Parse a single OAI-PMH record into an array.
     * Extracts Dublin Core fields from oai_dc metadata.
     */
    private function parseRecord(\SimpleXMLElement $record): ?array
    {
        $header = $record->header;
        $identifier = (string) $header->identifier;

        // Skip deleted records
        if (isset($header['status']) && (string) $header['status'] === 'deleted') {
            return null;
        }

        $metadata = $record->metadata;
        if (!$metadata || !$metadata->children('oai_dc', true)->count()) {
            return null;
        }

        $dc = $metadata->children('oai_dc', true)->dc ?? $metadata->children('oai_dc', true);
        $dcElements = $dc->children('dc', true);

        // Extract title with language priority
        $titles = [];
        foreach ($dcElements->title as $t) {
            $titles[] = [
                'value' => (string) $t,
                'lang' => (string) $t->attributes('xml', true)->lang,
            ];
        }

        $title = null;
        // Priority 1: Spanish
        foreach ($titles as $t) {
            if (str_starts_with(strtolower($t['lang']), 'es')) {
                $title = $t['value'];
                break;
            }
        }
        // Priority 2: First available
        if (!$title && !empty($titles)) {
            $title = $titles[0]['value'];
        }

        if (empty($title)) {
            return null;
        }

        // Extract all identifiers to find URL and PDF
        $url = null;
        $pdfUrl = null;
        foreach ($dcElements->identifier as $id) {
            $value = (string) $id;
            if (str_starts_with($value, 'http')) {
                if (preg_match('/\.pdf$/i', $value) || str_contains($value, '/download/') || str_contains($value, 'galley')) {
                    $pdfUrl = $pdfUrl ?? $value;
                } else {
                    $url = $url ?? $value;
                }
            }
        }

        // Extract authors
        $authors = [];
        foreach ($dcElements->creator as $creator) {
            $authors[] = (string) $creator;
        }

        // Extract date
        $date = null;
        if (isset($dcElements->date)) {
            $dateStr = (string) $dcElements->date;
            try {
                $date = \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
            } catch (\Exception $e) {
                $date = null;
            }
        }

        // Extract description (abstract) with language priority
        $descriptions = [];
        if (isset($dcElements->description)) {
            foreach ($dcElements->description as $d) {
                $descriptions[] = [
                    'value' => (string) $d,
                    'lang' => (string) $d->attributes('xml', true)->lang,
                ];
            }
        }

        $description = null;
        // Priority 1: Spanish
        foreach ($descriptions as $d) {
            if (str_starts_with(strtolower($d['lang']), 'es')) {
                $description = $d['value'];
                break;
            }
        }
        // Priority 2: First available
        if (!$description && !empty($descriptions)) {
            $description = $descriptions[0]['value'];
        }

        if ($description) {
            $description = strip_tags($description);
            if (strlen($description) > 2000) {
                $description = substr($description, 0, 2000) . '...';
            }
        }

        // Extract language
        $language = isset($dcElements->language) ? (string) $dcElements->language : null;

        // Build raw metadata JSON
        $rawMeta = [];
        foreach (['subject', 'type', 'format', 'source', 'relation', 'coverage', 'rights', 'publisher'] as $field) {
            if (isset($dcElements->$field)) {
                $values = [];
                foreach ($dcElements->$field as $val) {
                    $values[] = (string) $val;
                }
                if (!empty($values)) {
                    $rawMeta[$field] = count($values) === 1 ? $values[0] : $values;
                }
            }
        }

        return [
            'identifier' => $identifier,
            'title' => $title,
            'authors' => implode('; ', $authors) ?: null,
            'authors_json' => null, // oai_dc does not support structured authors
            'date' => $date,
            'url' => $url,
            'pdf_url' => $pdfUrl,
            'language' => $language ? substr($language, 0, 10) : null,
            'description' => $description,
            'metadata' => !empty($rawMeta) ? $rawMeta : null,
        ];
    }

    /**
     * Get the best available metadata prefix for a journal.
     */
    private function getBestMetadataPrefix(string $baseUrl): string
    {
        try {
            $response = Http::timeout(30)->get($baseUrl, ['verb' => 'ListMetadataFormats']);
            if ($response->failed()) {
                return 'oai_dc';
            }

            $xml = simplexml_load_string($response->body());
            if ($xml === false || !isset($xml->ListMetadataFormats)) {
                return 'oai_dc';
            }

            $formats = [];
            foreach ($xml->ListMetadataFormats->metadataFormat as $format) {
                $formats[] = (string) $format->metadataPrefix;
            }

            // Priority list: marcxml (rich, standard) > nlm (pmc) > oai_dc (fallback)
            if (in_array('marcxml', $formats)) {
                return 'marcxml';
            }
            if (in_array('nlm', $formats)) {
                return 'nlm'; // Could be implemented later
            }

            return 'oai_dc';
        } catch (\Exception $e) {
            Log::warning("Failed to determine metadata formats for {$baseUrl}: " . $e->getMessage());
            return 'oai_dc';
        }
    }

    /**
     * Parse a single OAI-PMH record using MARCXML format.
     */
    private function parseRecordMarc(\SimpleXMLElement $record): ?array
    {
        $header = $record->header;
        $identifier = (string) $header->identifier;

        // Skip deleted records
        if (isset($header['status']) && (string) $header['status'] === 'deleted') {
            return null;
        }

        $metadata = $record->metadata;
        if (!$metadata) {
            return null;
        }

        // Namespaces can be tricky, try standard marc first
        $marc = $metadata->children('http://www.loc.gov/MARC21/slim');
        if (!$marc->count()) {
            // Try without namespace or other common variations if needed
            $marc = $metadata->children('marc', true); 
        }
        
        $recordNode = $marc->record ?? $record->metadata->record ?? null;

        if (!$recordNode) {
             // Fallback: try finding 'record' element anywhere in metadata children
             foreach($metadata->children() as $child) {
                 if ($child->getName() == 'record') {
                     $recordNode = $child;
                     break;
                 }
             }
        }
        
        if (!$recordNode) {
            return null;
        }

        // Helper to get subfield value
        $getSubfield = function ($dataField, $code) {
            // Try direct access first, then with attributes()
            foreach ($dataField->subfield as $subfield) {
                if ((string)$subfield['code'] === $code) {
                    return (string)$subfield;
                }
                // Fallback for namespaced attributes
                $attrs = $subfield->attributes();
                if (isset($attrs['code']) && (string)$attrs['code'] === $code) {
                     return (string)$subfield;
                }
            }
            return null;
        };

        // Helper to get tag
        $getTag = function($field) {
             $tag = (string)$field['tag'];
             if ($tag) return $tag;
             
             $attrs = $field->attributes();
             return (string)($attrs['tag'] ?? '');
        };

        // Title (245 $a $b)
        $title = '';
        foreach ($recordNode->datafield as $field) {
            if ($getTag($field) === '245') {
                $title = $getSubfield($field, 'a') . ' ' . $getSubfield($field, 'b');
                $title = trim($title, " /:.");
                break;
            }
        }

        // Authors (100, 700)
        // 100: Main entry - Personal Name
        // 700: Added entry - Personal Name
        $authors = [];
        $authorsStructured = [];

        foreach ($recordNode->datafield as $field) {
            $tag = $getTag($field);
            if ($tag === '100' || $tag === '700' || $tag === '720') {
                $name = $getSubfield($field, 'a');
                $affiliation = $getSubfield($field, 'u');
                $orcid = $getSubfield($field, '0'); // ORCID usually in $0
                
                // Clean ORCID (sometimes it's a URL)
                if ($orcid) {
                    $orcid = str_replace('https://orcid.org/', '', $orcid);
                    $orcid = str_replace('http://orcid.org/', '', $orcid);
                }

                if ($name) {
                    $authors[] = trim($name, ",.");
                    $authorsStructured[] = [
                        'name' => trim($name, ",."),
                        'affiliation' => $affiliation,
                        'orcid' => $orcid
                    ];
                }
            }
        }

        // Date (260 $c or 008)
        $date = null;
        foreach ($recordNode->datafield as $field) {
            if ($getTag($field) === '260') {
                $dateStr = $getSubfield($field, 'c');
                if ($dateStr) {
                    // Try to extract year-month-day or just year
                    if (preg_match('/(\d{4})-\d{2}-\d{2}/', $dateStr, $matches)) {
                        $date = $matches[0];
                    } elseif (preg_match('/(\d{4})/', $dateStr, $matches)) {
                        $date = $matches[1] . '-01-01';
                    }
                }
            }
        }

        // URL (856 $u)
        $url = null;
        $pdfUrl = null;
        foreach ($recordNode->datafield as $field) {
            if ($getTag($field) === '856') {
                $u = $getSubfield($field, 'u');
                if ($u) {
                     // Heuristic to detect PDF vs abstract
                     // 856 with ind1=4, ind2=0 is usually HTTP URL
                     // Check if it looks like a file or download link
                    if (preg_match('/\.pdf$/i', $u) || str_contains($u, '/download/') || str_contains($u, '/view/')) {
                        // OJS specific: /view/ID/ID is often PDF, /view/ID is abstract
                        // But verifying exact URL type is hard without HEAD request. 
                        // We will assume first 856 is URL, if specific PDF indicators exist, use as PDF.
                        
                        // OJS often puts PDF in a separate 856 with implicit logic
                        // Let's grab the first one as main URL
                        if (!$url) $url = $u;
                        
                        // If it has file extension or explicit hint
                         if (preg_match('/\.pdf$/i', $u)) {
                             $pdfUrl = $u;
                         }
                    } else {
                        if (!$url) $url = $u;
                    }
                }
            }
        }

        // Description (520 $a)
        $description = null;
        foreach ($recordNode->datafield as $field) {
            if ($getTag($field) === '520') {
                $description = $getSubfield($field, 'a');
                break;
            }
        }

         // Language (008 or 546 or 041)
         $language = 'en'; // default ??
         // 008 is fixed length, lang is chars 35-37.
         if (isset($recordNode->controlfield)) {
             foreach ($recordNode->controlfield as $cf) {
                 if ((string)$cf['tag'] === '008') {
                     $val = (string)$cf;
                     if (strlen($val) >= 38) {
                         $language = substr($val, 35, 3);
                     }
                 }
             }
         }

        return [
            'identifier' => $identifier,
            'title' => $title,
            'authors' => implode('; ', $authors) ?: null,
            'authors_json' => !empty($authorsStructured) ? $authorsStructured : null,
            'date' => $date,
            'url' => $url,
            'pdf_url' => $pdfUrl,
            'language' => $language,
            'description' => $description,
            'metadata' => ['format' => 'marcxml'],
        ];
    }
}
