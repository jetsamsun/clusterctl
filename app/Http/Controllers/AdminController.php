<?php

namespace App\Http\Controllers;


use App\Models\ListOtype;
use App\Models\ScreenOtype;
use App\Models\StarList;
use App\Models\UserInfo;
use App\Models\VideoOtype;

class AdminController extends Controller
{
    //
    function getotype($otype){
        $otype = explode(',',$otype);
        $otypename = array();
        foreach($otype as $value){
            if($value == 1){
                $otypename[] = 'MV';
            }elseif($value == 2){
                $otypename[] = '视频';
            }
        }
        $otypename = implode(',',$otypename);
        return $otypename;
    }
    // 获取视频分类
    function getfirstotype($firstotype){
        $firstotype = explode(',',$firstotype);
        $otypename = array();
        foreach($firstotype as $value){
            $data = ListOtype::select('oid','otypename')->where('oid',$value)->first();
            if($data){
                $otypename[] = $data['otypename'];
            }
        }
        $otypename = implode(',',$otypename);
        return $otypename;
    }
    function getsecondotype($secondotype){
        $secondotype = explode(',',$secondotype);
        $otypename = array();
        foreach($secondotype as $value){
            $data = VideoOtype::select('oid','otypename')->where('oid',$value)->first();
            if($data){
                $otypename[] = $data['otypename'];
            }
        }
        $otypename = implode(',',$otypename);
        return $otypename;
    }
    // 获取筛选条件
    function getscreenotype($screenotype){
        $screenotype = explode(',',$screenotype);
        $otypename = array();
        foreach($screenotype as $value){
            $data = ScreenOtype::select('oid','otypename')->where('oid',$value)->first();
            if($data){
                $otypename[] = $data['otypename'];
            }
        }
        $otypename = implode(',',$otypename);
        return $otypename;
    }
    // 获取明星
    function getStarName($star){
        $star = explode(',',$star);
        $uname = array();
        foreach($star as $value){
            $data = StarList::select('sid','uname')->where('sid',$value)->first();
            if($data){
                $uname[] = $data['uname'];
            }

        }
        $unamestr = implode(',',$uname);
        return $unamestr;
    }

    /**
     *  获取毫秒
     */
    function msectime(){
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    function userLevel($uid){
        $data = UserInfo::select('uid','nickname')->where('uid',$uid)->first();
        $name = '';
        if($data){
            $name = $data['uid'];
        }
        return $name;
    }
    function urlPic(){
        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"];
        return $url;
    }
}
