<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Loan;

class Book extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */   
    use HasFactory;


    protected $fillable = [
        'title',
        'type_code_id',
        'book_code',
        'status',
        'publishing_house_id',
        'publishing_year',
        'edition',
        'inventory_number',
        'physic_location',
        'themes',
    ];

    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function publishingHouse()
    {
        return $this->belongsTo(Publishing_house::class);
    }

    public function typeCode()
    {
        return $this->belongsTo(TypeCode::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
