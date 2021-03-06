<?php

namespace App\Http\Controllers;


use App\Models\ListOtype;
use App\Models\MediaActors;
use App\Models\MediaCats;
use App\Models\MediaCountry;
use App\Models\MediaStatus;
use App\Models\MediaTags;
use App\Models\MediaType;
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
    function getMediaType($firstotype){
        $firstotype = explode(',',$firstotype);
        $otypename = array();
        foreach($firstotype as $value){
            $data = MediaType::where('Id',$value)->first();
            if($data){
                $otypename[] = $data['Name'];
            }
        }
        $otypename = implode(',',$otypename);
        return $otypename;
    }
    function getMediaCats($secondotype){
        $secondotype = explode(',',$secondotype);
        $otypename = array();
        foreach($secondotype as $value){
            $data = MediaCats::select('*')->where('Id',$value)->first();
            if($data){
                $otypename[] = $data['Name'];
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
    // 获取标签
    function getMediaTags($star){
        $tags = explode(',',$star);
        $uname = array();
        foreach($tags as $value){
            $data = MediaTags::where('Id',$value)->first();
            if($data){
                $uname[] = $data['Name'];
            }

        }
        $unamestr = implode(',',$uname);
        return $unamestr;
    }
    // 获取国家地区名称
    function getStatusName($code) {
        $star = explode(',',$code);
        $uname = array();
        foreach($star as $value){
            $data = MediaStatus::where('Id',$value)->first();
            if($data){
                $uname[] = $data['Name'];
            }
        }
        $unamestr = implode(',',$uname);
        return $unamestr;
    }
    // 获取国家地区名称
    function getCountryName($code) {
        $star = explode(',',$code);
        $uname = array();
        foreach($star as $value){
            $data = MediaCountry::select('*')->where('Code',$value)->first();
            if($data){
                $uname[] = $data['Name'];
            }
        }
        $unamestr = implode(',',$uname);
        return $unamestr;
    }
    // 获取明星
    function getMediaActors($star){
        $star = explode(',',$star);
        $uname = array();
        foreach($star as $value){
            $data = MediaActors::select('*')->where('Id',$value)->first();
            if($data){
                $uname[] = $data['Name'];
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
