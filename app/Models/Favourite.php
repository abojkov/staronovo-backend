<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model {
    protected $table = 'favourites';

    protected $fillable = [
        'post_id',
        'user_id',
        'datetime_favourite'
    ];
}