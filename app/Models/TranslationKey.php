<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationKey extends Model
{
    protected $fillable = ['key', 'english', 'translation', 'group'];

    public $timestamps = false;

    /**
     * This is a non-database model used for Filament tables.
     */
    public function getKeyName(): string
    {
        return 'key';
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKey()
    {
        return $this->getAttribute('key');
    }
}
