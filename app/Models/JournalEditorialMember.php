<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEditorialMember extends Model
{
    public const ROLE_EDITOR_IN_CHIEF = 'editor_in_chief';
    public const ROLE_ASSOCIATE = 'associate';
    public const ROLE_BOARD = 'board';
    public const ROLE_GUEST = 'guest';

    public const ROLES = [
        self::ROLE_EDITOR_IN_CHIEF,
        self::ROLE_ASSOCIATE,
        self::ROLE_BOARD,
        self::ROLE_GUEST,
    ];

    public const ORCID_REGEX = '/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/';

    protected $fillable = [
        'journal_id',
        'name',
        'role',
        'orcid',
        'email',
        'affiliation',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function orcidUrl(): ?string
    {
        return $this->orcid ? "https://orcid.org/{$this->orcid}" : null;
    }
}
