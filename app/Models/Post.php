<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    protected $table = 'posts';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'image',
        'description',
        'quantity_left',
        'price',
        'category_id',
        'datetime_posted',
        'seller_id',
        'city_id',
        'is_active'
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }
}