<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaEpisodes extends Model
{
    //
    public $table='media_episodes';//这样寻找的就是没s的表
    public $timestamps = false;
    //protected $primaryKey = 'id';
}