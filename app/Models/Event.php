<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

     protected $fillable = [
        'title',
        'description',
        'venue',
        'date',
        'category',
        'price',
        'total_tickets',
        'sold_tickets',
        'image',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
