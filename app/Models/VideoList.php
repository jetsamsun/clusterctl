<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoList extends Model
{
    //
    public $table='video_list';//这样寻找的就是没s的表
    public $timestamps = false;
    protected $primaryKey = 'vid';
}
