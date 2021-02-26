<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model {
    protected $table = 'favourites';
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'datetime_favourite'
    ];
}