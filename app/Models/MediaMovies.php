<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaMovies extends Model
{
    //
    public $table='media_movies';//这样寻找的就是没s的表
    public $timestamps = false;
    //protected $primaryKey = 'id';
}