<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use HasFactory;

    protected $fillable = [
        'Género',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
}
