<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteCfg extends Model
{
    //
    public $table='site_cfg';//这样寻找的就是没s的表
    public $timestamps = false;
    //protected $primaryKey = 'id';
}
