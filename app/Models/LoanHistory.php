<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester',
        'book_id',
        'loan_date',
        'return_date',
        'status',
        'user_id',
        'user_name',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Asigna automáticamente el usuario que registra el préstamo.
     */
    protected static function booted()
    {
        static::creating(function ($loanHistory) {
            if (auth()->check()) {
                $loanHistory->user_id = auth()->id();
                $loanHistory->user_name = auth()->user()->name;
            }
        });
    }
}

