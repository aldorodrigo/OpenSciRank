<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookAuthor extends Model
{
    protected $guarded = [];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Scopes for filtering by role
    public function scopeAuthors($query)
    {
        return $query->where('role', 'author');
    }

    public function scopeEditors($query)
    {
        return $query->where('role', 'editor');
    }

    public function scopeTranslators($query)
    {
        return $query->where('role', 'translator');
    }

    public function scopeCoordinators($query)
    {
        return $query->where('role', 'coordinator');
    }
}
