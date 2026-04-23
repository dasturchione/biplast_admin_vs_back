<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'file',
        'type',
        'link',
        'order',
        'is_active',
    ];
}
