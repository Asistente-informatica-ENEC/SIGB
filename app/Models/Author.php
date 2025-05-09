<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
        /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use HasFactory;

    protected $fillable = [
        'name',
        'lastname_1',
        'lastname_2',
        'country',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->lastname}";
    }
}
