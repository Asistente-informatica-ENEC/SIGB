<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester',
        'book_id',
        'user_id',
        'loan_date',
        'return_date',
        'status',
        'procedencia',
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
        static::created(function ($loan) {
            $loan->book->update(['status' => 'prestado']);
        });

        static::updated(function ($loan) {
            if ($loan->status === 'devuelto') {
                $loan->book->update(['status' => 'disponible']);
            }
        });
    }


}
