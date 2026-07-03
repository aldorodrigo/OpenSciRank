<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Journal;
use App\Models\Setting;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Reúne los datos de marca institucional configurables (grupo `documents`
 * en Setting) para inyectarlos en los PDF de certificado e informe.
 *
 * Las imágenes (logos, firmas, QR) se devuelven como data-URI base64:
 * dompdf NO descarga imágenes remotas ni resuelve `Storage::url()`, por lo
 * que hay que embeberlas inline. Si un archivo no existe o falta la
 * librería QR, se degrada a null y la vista simplemente no lo pinta.
 */
class DocumentBranding
{
    public const DEFAULT_REPORT_INTRO = 'El presente informe de evaluación ha sido elaborado por Editorial Standards Platform como resultado de un proceso de valoración técnica basado en revisión por pares ciegos (double-blind peer review) y en el análisis de indicadores editoriales ponderados conforme a estándares científicos internacionales. Su propósito es ofrecer, con carácter orientativo y de mejora continua, un detalle transparente de los criterios evaluados y su grado de cumplimiento, sirviendo como referencia para el equipo editorial en el fortalecimiento de sus prácticas científicas y editoriales.';

    public const DEFAULT_CERTIFICATE_DESCRIPTION = 'Se certifica que la revista «{journal}» ha demostrado un cumplimiento sobresaliente de los estándares editoriales y de calidad científica evaluados, acreditando prácticas editoriales rigurosas y transparentes conforme a los criterios de Editorial Standards Platform.';

    /**
     * Payload de marca para las vistas dompdf.
     *
     * @return array<string,mixed>
     */
    public function assemble(?Journal $journal = null): array
    {
        return [
            'institution' => [
                'name' => Setting::get('institution_name'),
                'address' => Setting::get('institution_address'),
                'phone' => Setting::get('institution_phone'),
                'email' => Setting::get('institution_email'),
                'website' => Setting::get('institution_website'),
            ],
            'logos' => $this->logos(),
            'signatories' => $this->signatories(),
            'report_intro' => Setting::get('report_intro', self::DEFAULT_REPORT_INTRO),
            'certificate_description' => $this->certificateDescription($journal),
            'footer_note' => Setting::get('certificate_footer_note'),
        ];
    }

    /**
     * QR (data-URI PNG) que apunta a la ficha pública de la revista.
     *
     * @return string|null null si no hay slug, falta la librería o falla el build
     */
    public function qrForJournal(Journal $journal): ?string
    {
        if (! $journal->slug || ! class_exists(Builder::class)) {
            return null;
        }

        try {
            $builder = new Builder(
                writer: new PngWriter,
                data: route('journal.show', $journal->slug),
                size: 240,
                margin: 2,
            );

            return $builder->build()->getDataUri();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Data-URIs de los logos configurados, en orden y sin huecos.
     *
     * @return array<int,string>
     */
    private function logos(): array
    {
        return collect(['logo_primary', 'logo_secondary_1', 'logo_secondary_2'])
            ->map(fn (string $key): ?string => $this->dataUri(Setting::get($key)))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Firmantes con nombre. Cada uno con cargo e imagen de firma (data-URI).
     *
     * @return array<int,array{name:string,title:?string,signature:?string}>
     */
    private function signatories(): array
    {
        $out = [];

        for ($i = 1; $i <= 3; $i++) {
            $name = Setting::get("signatory_{$i}_name");

            if (! $name) {
                continue;
            }

            $out[] = [
                'name' => $name,
                'title' => Setting::get("signatory_{$i}_title"),
                'signature' => $this->dataUri(Setting::get("signatory_{$i}_signature")),
            ];
        }

        return $out;
    }

    private function certificateDescription(?Journal $journal): string
    {
        $template = (string) Setting::get('certificate_description', self::DEFAULT_CERTIFICATE_DESCRIPTION);

        $title = $journal
            ? ($journal->getTranslation('title', app()->getLocale()) ?: $journal->title)
            : '';

        return str_replace('{journal}', (string) $title, $template);
    }

    /**
     * Convierte un path relativo del disco `public` en data-URI base64.
     * Devuelve null si no hay path o el archivo no existe.
     */
    private function dataUri(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $abs = storage_path('app/public/'.$path);

        if (! is_file($abs)) {
            return null;
        }

        $mime = mime_content_type($abs) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($abs));
    }
}
