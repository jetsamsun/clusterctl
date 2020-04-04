<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Config;
use App\Models\MediaActors;
use App\Models\MediaCats;
use App\Models\MediaEpisodes;
use App\Models\MediaMovies;
use App\Models\MediaTags;
use App\Models\SiteCfg;


class MediaController extends ApiController
{
    function __construct() {
        $cfgs = Config::get();
        foreach ($cfgs as $v) {
            $this->cfgs[$v['name']] = $v['value'];
        }

        $this->uploaddir = PUBLIC_PATH.$this->cfgs['upload_dir'].'/';
        $this->productdir = PUBLIC_PATH.$this->cfgs['video_dir'].'/';
        $this->tmpdir = PUBLIC_PATH.'/video/tmp/';
    }

    // http://clusterctl.xyz/api/v1/getmedia
    function getmedia() {
        if(!isset($_POST['key']) || (!isset($_POST['id']) && !isset($_POST['updatetime']))) {
            return json_encode(array('code'=>0,'msg'=>'parameter error'));
        }

        $key = $_POST['key'];
        $site = SiteCfg::where('Key',$key)->first();
        if(!$site) {
            return json_encode(array('code'=>0,'msg'=>'key error'));
        }

        //检查ip白名单
        $ip = getip();
        if($ip != $site->Ip) {
            return json_encode(array('code'=>0,'msg'=>'Access denied'));
        }


        if(isset($_POST['id'])) {
            $id = $_POST['id'];
            $data = MediaMovies::orderBy('Id', 'asc')->where('Id', '>', $id)->limit(50)->get()->toArray();
        } else if(isset($_POST['updatetime'])) {
            $updatetime = $_POST['updatetime'];
            $data = MediaMovies::orderBy('Id', 'asc')->where('Update_time', '>', $updatetime)->limit(50)->get()->toArray();
        } else {
            dd('未知错误！');
        }


        if(empty($data)) {
            return json_encode(array('code'=>0,'msg'=>'data empty','data'=>$data));
        }

        $Cats = MediaCats::get()->toArray();
        $_arr = [];
        foreach ($Cats as $v) {
            $_arr[$v['Id']] = $v['Name'];
        }
        $Cats = $_arr;

        $Actors = MediaActors::get()->toArray();
        $_arr = [];
        foreach ($Actors as $v) {
            $_arr[$v['Id']] = $v['Name'];
        }
        $Actors = $_arr;

        $Tags = MediaTags::get()->toArray();
        $_arr = [];
        foreach ($Tags as $v) {
            $_arr[$v['Id']] = $v['Name'];
        }
        $Tags = $_arr;


        foreach ($data as &$v) {
            $v['Image'] = empty($v['Image'])?'':$this->cfgs['img_url'].$v['Image'];
            $v['Image_big'] = empty($v['Image_big'])?'':$this->cfgs['img_url'].$v['Image_big'];

            $arr = [];
            $catid = $v['Cats'];
            $arr['id'] =$catid;
            $arr['name'] = $Cats[$catid];
            $v['CatsArr'] = $arr;

            $_arr = [];
            $acts = $v['Actors'];
            if($acts) {
                $acts = explode(',', $acts);
                foreach ($acts as $aid) {
                    $arr = [];
                    $arr['id'] = $aid;
                    $arr['name'] = $Actors[$aid];
                    $_arr[] = $arr;
                }
            }
            $v['ActorsArr'] = $_arr;

            $_arr = [];
            $tags = $v['Tags'];
            if($tags) {
                $tags = explode(',', $tags);
                foreach ($tags as $tid) {
                    $arr = [];
                    $arr['id'] = $tid;
                    $arr['name'] = $Tags[$tid];
                    $_arr[] = $arr;
                }
            }
            $v['TagsArr'] = $_arr;
        }

        return json_encode(array('code'=>1,'msg'=>'ok','data'=>$data));
    }

    function getepisodes() {
        if(!isset($_POST['key']) || (!isset($_POST['id']) && !isset($_POST['updatetime']))) {
            return json_encode(array('code'=>0,'msg'=>'parameter error'));
        }

        $key = $_POST['key'];
        $site = SiteCfg::where('Key',$key)->first();
        if(!$site) {
            return json_encode(array('code'=>0,'msg'=>'key error'));
        }

        //检查ip白名单
        $ip = getip();
        if($ip != $site->Ip) {
            return json_encode(array('code'=>0,'msg'=>'Access denied'));
        }

        if(isset($_POST['id'])) {
            $id = $_POST['id'];
            $data = MediaEpisodes::orderBy('Id','asc')->where('Id','>',$id)->limit(50)->get()->toArray();
        } else if(isset($_POST['updatetime'])) {
            $updatetime = $_POST['updatetime'];
            $data = MediaEpisodes::orderBy('Id', 'asc')->where('Update_time', '>', $updatetime)->limit(50)->get()->toArray();
        } else {
            dd('未知错误！');
        }


        if(empty($data)) {
            return json_encode(array('code'=>0,'msg'=>'data empty','data'=>$data));
        }

        foreach ($data as &$v) {
            $v['Image'] = empty($v['Image']) ? '' : $this->cfgs['img_url'] . $v['Image'];
            $v['Gif'] = empty($v['Gif']) ? '' : $this->cfgs['img_url'] . $v['Gif'];
            $v['Play_url'] = empty($v['Play_url']) ? '' : $this->cfgs['m3u8_url'] . $v['Play_url'];
        }

        return json_encode(array('code'=>1,'msg'=>'ok','data'=>$data));
    }
}
