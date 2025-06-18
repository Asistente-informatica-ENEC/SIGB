<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookRemoval extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use HasFactory;

    protected $fillable = [
        'book_id',
        'reason',
        'observation',
        'user_id',
        'user_name',

    ];

        public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    protected static function booted()
    {
        static::creating(function ($removal) {
            if (auth()->check()) {
                $removal->user_id = auth()->id();
                $removal->user_name = auth()->user()->name;
            }
        });
    }
}
