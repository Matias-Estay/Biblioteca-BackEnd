<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_types extends Model
{
    use HasFactory, HasFactory, Notifiable;
    
    protected $fillable = [
        'type'
    ];
}
