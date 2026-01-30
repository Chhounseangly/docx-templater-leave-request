<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'mapping',
    ];

    protected $casts = [
        'mapping' => 'array',
    ];
}
