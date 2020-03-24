<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaActors extends Model
{
    //
    public $table='media_actors';//这样寻找的就是没s的表
    public $timestamps = false;
    //protected $primaryKey = 'id';
}
