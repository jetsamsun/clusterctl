<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\LoginLogs;
use App\Models\SiteRate;
use App\Models\StarList;
use App\Models\UserCollect;
use App\Models\UserInfo;
use App\Models\VideoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Libs\Xyz;


class VideoController extends  ApiController{
    function __construct() {
        set_time_limit(0);
        $this->uploaddir = env('PUBLIC_PATH').'/assets/uploads/files/video/';  // '/video/upload/'
        $this->productdir = env('PUBLIC_PATH').'/video/product/';
        $this->tmpdir = env('PUBLIC_PATH').'/video/tmp/';
    }

    public function execute() {
        $xyz = new Xyz();
        $type = '';
        $size_rate = json_decode($_POST['size_rate']);

        $row = VideoList::where('vid',$_POST['ids'])->get()->toArray();
        $row = $row[0];
        $filename = str_replace('/assets/uploads/files/video/', '', $row['url']);
        $videodir = $this->uploaddir.$filename;
        $videomsg = $xyz->format($videodir);  //源文件数据

        $datestr = date('Ymd');
        $randsring = $row['randsring'];
        if(!$randsring) {
            $randsring = generate_random_string();
        }
        $dirpath = $this->productdir.$datestr.'/'.$randsring.'/';

        foreach ($size_rate as $val) {
            $sizetmp = explode('-', $val);
            $rate = $sizetmp[0];
            $size = $sizetmp[1];
            $default = SiteRate::where('default',1)->where('rate',$rate)->get()->toArray(); //是否默认码率

            $dirtmp = $dirpath.$rate.'/';  mk_dir($dirtmp);
            $tovideodir = $dirtmp.$randsring.'.mp4';
            $toimgedir = $dirtmp.$randsring.'.jpg';
            $togifdir = $dirtmp.$randsring.'.gif';


            if('success' == $xyz->transcode($videodir, $tovideodir, $toimgedir, $togifdir, $size, $rate)) {
                $m3u8arr = [];
                $muname = 'mmm.m3u8';
                $flag = false;

                if(config('site.transm3u8')) {  //判断是否切片
                    if('success' == $xyz->transm3u8($tovideodir, dirname($tovideodir).'/'.$muname)) {
                        if($default) { //切片完成是否删除源文件
                            if(config('site.transm3u8del')) unlink($tovideodir);
                        }
                        $flag = true;
                    }
                }

                if($flag) {
                    $info = VideoList::where('vid', $_POST['ids'])->get()->toArray();
                    $info = $info[0];

                    if ($info['m3u8']) { //拼接
                        $m3u8arr = json_decode($info['m3u8'], true);
                        if (!$m3u8arr) $m3u8arr = [];
                        $m3u8arr[$rate] = str_replace(env('PUBLIC_PATH'), '', dirname($tovideodir) . '/' . $muname);
                        ksort($m3u8arr);
                    } else {  //第一次加入
                        $m3u8arr[$rate] = str_replace(env('PUBLIC_PATH'), '', dirname($tovideodir) . '/' . $muname);
                        ksort($m3u8arr);
                    }
                }

                //更新数据
                $update['m3u8'] = !empty($m3u8arr) ? $m3u8arr : $row['m3u8'];
                $update['pic'] = str_replace(env('PUBLIC_PATH'), '', $toimgedir);
                $update['gif'] = str_replace(env('PUBLIC_PATH'), '', $togifdir);
                $update['video'] = !empty($default) ? str_replace(env('PUBLIC_PATH'), '', $tovideodir) : $row['video'];
                $update['nickname'] = $randsring.'.mp4';
                $update['size'] = $videomsg['size'];
                $update['width'] = $videomsg['width'];
                $update['height'] = $videomsg['height'];
                $update['bit_rate'] = $videomsg['bit_rate'];
                $update['duration'] = $videomsg['duration'];
                $update['audio'] = $videomsg['audio'];
                $update['vcode'] = $videomsg['video'];

                VideoList::where('vid', $_POST['ids'])->update($update);
            }
        }

        if(config('site.tanscodedel')) {
            unlink($videodir);   //转码完成是否删除源文件
        }
    }
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *          获取视频列表
     *          /api/v1/video/getVideoList
     */
    public function getVideoList(Request $request){
        $token = $request->input('token');
        $otype = $request->input('otype');
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;
        $firstotype = $request->input('firstotype');
        $secondotype = $request->input('secondotype');
        $screenotype = $request->input('screenotype');
        $search = $request->input('search');
        if(empty($token)){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $dataTmp = VideoList::select('vid','title','otype','pic','url','is_free');
        if($otype){ // mv 视频
            $dataTmp = $dataTmp->where('otype',$otype);
        }
        if($firstotype){ // 头部导航分类  例如： 最新 最热
            $dataTmp = $dataTmp->where('firstotype',$firstotype);
        }
        if($secondotype){ // 视频分类 例如：综艺 动漫
            $dataTmp = $dataTmp->where('secondotype',$secondotype);
        }
        if($screenotype){ // 筛选条件
            $screenotype = explode(',',$screenotype);
            sort($screenotype);
            $screenotype = implode(',',$screenotype);

            $dataTmp = $dataTmp->where('screenotype','like','%'.$screenotype.'%');
        }
        if($search){ // 搜索条件
            $dataTmp = $dataTmp->where('title','like','%'.$search.'%');
        }
        $dataTmp = $dataTmp->where(['is_visible'=>1])
            ->orderBy('vid', 'desc')->paginate($pageSize);
        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];
            foreach($dataTmp as $key=>$value){
                if($value['pic']){
                    $dataTmp[$key]['pic'] = $value['pic'];
                }
                if($value['url']){
                    $dataTmp[$key]['url'] = $value['url'];
                }
                if($value['otype']==1){
                    $dataTmp[$key]['otype'] = 5;
                }elseif($value['otype'] == 2){
                    $dataTmp[$key]['otype'] = 10;
                }

                $collect = UserCollect::select('cid')->where(['uid'=>$uid,'oid'=>$value['vid']])->where('otype','!=',1 )->first();
                if($collect){
                    $dataTmp[$key]['is_collect'] = 1;
                }else{
                    $dataTmp[$key]['is_collect'] = 0;
                }
            }
        }

        // 记录登录用户 登录时间
        $login = LoginLogs::select('id')->where('uid',$uid)->first();
        if($login){
            $login = $login->toArray();
            DB::table('login_logs')->where('id',$login['id'])->update(array("logintime"=>strtotime(date("Y-m-d H:i:s"))));
        }else{
            DB::table('login_logs')->insert(array('uid'=>$uid,"logintime"=>strtotime(date("Y-m-d H:i:s"))));
        }

        return $this->ajaxMessage(0,'success',$dataTmp);
    }

    public function getVideoDetails(Request $request){
        $token = $request->input('token');
        $vid = $request->input('vid');

        if(empty($token) || empty($vid) ){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];
        $vipendtime = $dataTmp['vipendtime'];

        $dataTmp = VideoList::select('vid','title','pic','content','otype','url','star','is_free','createtime')
            ->where(['vid'=>$vid,'is_visible'=>1])->first();
        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
        }
        if($dataTmp['pic']){
            $dataTmp['pic'] = $dataTmp['pic'];
        }
        if($dataTmp['otype'] == 1){
            $dataTmp['otype'] = 5;
        }else{
            $dataTmp['otype'] = 10;
        }
        if($dataTmp['url']){
            $dataTmp['url'] = $dataTmp['url'];
        }
        $dataTmp['createtime'] = date('Y-m-d H:i:s',$dataTmp['createtime']);

        $collect = UserCollect::select('cid')->where(['uid'=>$uid,'oid'=>$dataTmp['vid']])
            ->where('otype','!=',1)
            ->first();
        if($collect){
            $dataTmp['is_collect'] = 1;
        }else{
            $dataTmp['is_collect'] = 0;
        }

        // 是否是vip
        if( strtotime(date('Y-m-d H:i:s')) > $vipendtime ){
            $dataTmp['is_vip'] = 0;  // 不是
        }else{
            $dataTmp['is_vip'] = 1;  // 是
        }
        // 明星
        if($dataTmp['star']){
            $star = explode(',',$dataTmp['star']);
            foreach($star as $value){
                $starinfo = StarList::select('sid','uname','pic')->where('sid',$value)->first()->toArray();
                if($starinfo['pic'])
                {
                    $starinfo['pic'] = $starinfo['pic'];
                }

                // 判断是否已加入收藏
                $data = UserCollect::select('cid')->where(['uid'=>$uid,'otype'=>1,'oid'=>$starinfo['sid']])->first();
                if($data){
                    $starinfo['is_collect'] = 1; // 已收藏
                }else{
                    $starinfo['is_collect'] = 0;  // 未收藏
                }
                $dataTmp['starlist'][] = $starinfo;
            }
            unset($dataTmp['star']);
        }
        return $this->ajaxMessage(0,'success',$dataTmp);
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *          获取相关视频列表
     *          /api/v1/video/getRandomVideoList
     */
    public function getRandomVideoList(Request $request){
        $token = $request->input('token');
        if(empty($token)){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $dataTmp = VideoList::select('vid','title','otype','pic','url','is_free');
        $dataTmp = $dataTmp->where(['is_visible'=>1])
            ->orderBy(DB::raw('RAND()'))
            ->take(4)
            ->get();
        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            foreach($dataTmp as $key=>$value){
                if($value['pic']){
                    $dataTmp[$key]['pic'] = $value['pic'];
                }
                if($value['url']){
                    $dataTmp[$key]['url'] = $value['url'];
                }
                if($value['otype']==1){
                    $dataTmp[$key]['otype'] = 5;
                }elseif($value['otype'] == 2){
                    $dataTmp[$key]['otype'] = 10;
                }

                $collect = UserCollect::select('cid')->where(['uid'=>$uid,'oid'=>$value['vid']])->where('otype','!=',1 )->first();
                if($collect){
                    $dataTmp[$key]['is_collect'] = 1;
                }else{
                    $dataTmp[$key]['is_collect'] = 0;
                }
            }
        }
        return $this->ajaxMessage(0,'success',$dataTmp);
    }
}