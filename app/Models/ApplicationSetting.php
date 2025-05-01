<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationSetting extends Model
{
    use HasFactory;

    protected $table = 'application_settings';

    protected $fillable = [
        'name',
        'value',
    ];
}
