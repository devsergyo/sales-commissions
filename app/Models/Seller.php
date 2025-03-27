<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seller extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'first_name',
        'last_name',
        'email'
    ];

    protected function fullname(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => "{$attributes['first_name']} {$attributes['last_name']}",
        );
    }
}
