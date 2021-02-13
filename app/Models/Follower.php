<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model {
    protected $table = 'followers';
    public $timestamps = false;

    protected $fillable = [
        'follower_id',
        'following_id',
        'datetime_follow'
    ];
}