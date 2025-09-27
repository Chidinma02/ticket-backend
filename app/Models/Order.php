<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

     protected $fillable = [
        'event_id',
        'buyer_name',
        'buyer_email',
        'buyer_address',
        'quantity',
        'status',
    ];
}
