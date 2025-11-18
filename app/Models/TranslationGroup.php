<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationGroup extends Model
{
    protected $fillable = ['group', 'status', 'progress', 'missing'];

    public $timestamps = false;

    /**
     * This is a non-database model used for Filament tables.
     */
    public function getKeyName(): string
    {
        return 'group';
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKey()
    {
        return $this->getAttribute('group');
    }
}
