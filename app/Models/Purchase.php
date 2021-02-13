<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model {
    protected $table = 'purchases';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'buyer_id',
        'post_id',
        'quantity',
        'total_price',
        'rating',
        'comment',
        'status_id',
        'datetime_purchased',
        'datetime_delivered',
        'datetime_confirmation',
        'datetime_rating',
        'nth_rating',
    ];

    public function post(){
        return $this->belongsTo(Post::class);
    }
}