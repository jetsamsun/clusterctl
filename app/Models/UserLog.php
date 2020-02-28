<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    //
    public $table='user_log';//这样寻找的就是没s的表
    public static function addLog($uid,$type,$typeId,$content){
        if(is_array($content)){
            $content = json_encode($content);
        }
        self::insert(array("uid"=>$uid,"type"=>$type,"typeId"=>$typeId,'content'=>$content,"addTime"=>date("Y-m-d H:i:s")));
        return true;
    }
}
