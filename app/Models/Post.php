<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- missing import
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'note',
    ];
}
