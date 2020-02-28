<?php
namespace App\Http\Controllers\Api\V110;

use App\Http\Controllers\ApiController;
use App\Models\LoginLogs;
use App\Models\StarList;
use App\Models\UserCollect;
use App\Models\UserInfo;
use App\Models\UserlookLogs;
use App\Models\VideoList;
use App\Models\VideoUserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends  ApiController{
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *          获取视频列表
     *          /api/v110/video/getVideoList
     */
    public function getVideoList(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $otype = $request->input('otype');
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;
        $firstotype = $request->input('firstotype');
        $secondotype = $request->input('secondotype');
        $bestotype = $request->input('bestotype');
        $screenotype = $request->input('screenotype');
        $search = $request->input('search');

        if(empty($randomstr)||empty($timestamp)||empty($signature) || empty($token)){
            return $this->ajaxMessage(101,'请求参数不完整');
        }

        // 验证签名
        $s = $this->checkSignature($randomstr,$timestamp,$signature);
        if($s==99){
            return $this->ajaxMessage($s,'签名格式不正确');
        }
        if($s==100){
            return $this->ajaxMessage($s,'请求过期');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $dataTmp = VideoList::select('vid','title','otype','pic','url','is_free','hotcount','videotime','createtime');
        if($otype){ // mv 视频
            $dataTmp = $dataTmp->where('otype','like','%'.$otype.'%');
        }
        if($firstotype){ // 头部导航分类  例如： 最新 最热
            $dataTmp = $dataTmp->where('firstotype','like','%'.$firstotype.'%');
        }
        if($secondotype){ // 视频分类 例如：综艺 动漫
            $dataTmp = $dataTmp->where('secondotype','like','%'.$secondotype.'%');
        }
        if($bestotype){ // 视频分类 最新 最热
            $dataTmp = $dataTmp->where('secondbestotype','like','%'.$bestotype.'%');
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
                // 时长时分秒
                //$dataTmp[$key]['videotime'] = gmstrftime('%H:%M:%S',$value["videotime"]);

                $collect = UserCollect::select('cid')->where(['uid'=>$uid,'oid'=>$value['vid']])->where('otype','!=',1 )->first();
                if($collect){
                    $dataTmp[$key]['is_collect'] = 1;
                }else{
                    $dataTmp[$key]['is_collect'] = 0;
                }

                $dataTmp[$key]['createtime'] = $this->secToday($value['createtime'])."前更新";

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

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *      视频详情
     *  /api/v110/video/getVideoDetails
     */
    public function getVideoDetails(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $vid = $request->input('vid');

        if( empty($randomstr)|| empty($timestamp) || empty($signature) || empty($token) || empty($vid) ){
            return $this->ajaxMessage(101,'请求参数不完整');
        }

        // 验证签名
        $s = $this->checkSignature($randomstr,$timestamp,$signature);
        if($s==99){
            return $this->ajaxMessage($s,'签名格式不正确');
        }
        if($s==100){
            return $this->ajaxMessage($s,'请求过期');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];
        $vipendtime = $dataTmp['vipendtime'];

        $dataTmp = VideoList::select('vid','title','pic','content','otype','secondotype','url','star','is_free','hotcount','createtime','play_urls','download_urls')
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

        if($dataTmp["secondotype"]){
            $dataTmp['secondotype'] = $this->getSecondOtypeStr($dataTmp['secondotype']);
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
        // 是否可观看
        if( $dataTmp["is_vip"] ==1 ){ // vip 永远可观看
            $dataTmp['is_look'] = 1;  // 可观看
        }else{
            $user  = UserInfo::select("lookcount")->where('uid',$uid)->first();
            if($user){
                if($user["lookcount"] <= 0){
                    $dataTmp['is_look'] = 0;  // 不可观看
                }else{
                    $dataTmp['is_look'] = 1;  // 可观看
                }
            }
        }
        // 下载状态 点赞 点踩
        $log = VideoUserLog::select("is_down","is_flag")->where(['uid'=>$uid,"vid"=>$vid])->first();
        if($log){
            $dataTmp["is_down"] = $log["is_down"];
            $dataTmp["is_flag"] = $log["is_flag"];
        }else{
            $dataTmp["is_down"] = 0;
            $dataTmp["is_flag"] = 0;
        }
        // 明星
        if($dataTmp['star']){
            $star = explode(',',$dataTmp['star']);
            foreach($star as $value){
                $starinfo = StarList::select('sid','uname','pic')->where('sid',$value)->first()->toArray();
                if($starinfo['pic'])
                {
                    $starinfo['pic'] = $this->urlPic().$starinfo['pic'];
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
        $dataTmp['m3u8'] = array();
        if(!empty($dataTmp['play_urls'])){
            $play_urls = $dataTmp['play_urls'];
            $play_urls_array = explode('|',$play_urls);
            if(isset($play_urls_array['0'])){
                $playUrl480p = $play_urls_array['0'];
                if(!(strripos($playUrl480p,"XiaoShiPin")>0)){
                    $dataTmp['m3u8'][] = array('res'=>'标清','url'=>$play_urls_array['0']);
                }
            }
            if(isset($play_urls_array['1'])){
                $dataTmp['m3u8'][] = array('res'=>'高清','url'=>$play_urls_array['1']);
            }
            if(isset($play_urls_array['2'])){
                $playUrl1080p = $play_urls_array['2'];
                if(!(strripos($playUrl1080p,"XiaoShiPin")>0)){
                    $dataTmp['m3u8'][] = array('res'=>'超清','url'=>$play_urls_array['2']);
                }
            }
        }
        $dataTmp['m3u8_default'] = '0';
        $dataTmp['v_download_default'] = '0';
        $dataTmp['share_text'] = '分享文字来自服务器API';
        $dataTmp['v_download'] = array();
        if(!empty($dataTmp['download_urls'])){
            $download_urls = $dataTmp['download_urls'];
            $download_urls_array = explode('|',$download_urls);
            if(isset($download_urls_array['0'])){
                $dataTmp['v_download'][] = array('res'=>'480p','url'=>$download_urls_array['0']);
            }
            if(isset($download_urls_array['1'])){
                $dataTmp['v_download'][] = array('res'=>'720p','url'=>$download_urls_array['1']);
            }
            if(isset($download_urls_array['2'])){
                $dataTmp['v_download'][] = array('res'=>'1080p','url'=>$download_urls_array['2']);
            }
        }
        $dataTmp['banner_pic'] = "http://naisir.kuaibaotv.com/banner/test2.jpg";
        $dataTmp['banner_url'] = "http://web2.edutopcloud.tv/qg99tv.mobileconfig";
        unset($dataTmp['play_urls']);
        unset($dataTmp['download_urls']);
        return $this->ajaxMessage(0,'success',$dataTmp);
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *      下载
     *      /api/v110/video/doDown
     */
    public function doDown(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $vid = $request->input('vid');
        $is_down = $request->input('is_down'); // 标识 1：已下载  0：否
        if( empty($randomstr)|| empty($timestamp) || empty($signature) || empty($token) || empty($vid) || !isset($is_down)){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        // 验证签名
        $s = $this->checkSignature($randomstr,$timestamp,$signature);
        if($s==99){
            return $this->ajaxMessage($s,'签名格式不正确');
        }
        if($s==100){
            return $this->ajaxMessage($s,'请求过期');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        // 判断视频ID是否存在
        $data = VideoList::select("vid")->where(["vid"=>$vid,"is_visible"=>1])->first();
        if(!$data){
            return $this->ajaxMessage(103,'视频信息不存在');
        }
        if($is_down==1){
            $user  = UserInfo::select("downcount")->where('uid',$uid)->first();
            if($user){
                if($user["downcount"] == 0){
                    return $this->ajaxMessage(104,'下载次数已用完');
                }
            }
        }

 //       $data = VideoUserLog::select("logid","uid","vid")->where(['uid'=>$uid,"vid"=>$vid])->first();
// 下载的时候 只调用
//        if($data){
//            $reg = DB::table("video_user_log")->where(['uid'=>$uid,"vid"=>$vid])
//                ->update(array('is_down'=>$is_down));
//        }else{
//            $reg = DB::table("video_user_log")->insert(array('uid'=>$uid,"vid"=>$vid,"is_down"=>$is_down));
//        }
        if($is_down==1){
            $reg = DB::table("user_info")->where("uid",$uid)->decrement("downcount");
        }

        if($reg){
            return $this->ajaxMessage(0,'成功~');
        }else{
            return $this->ajaxMessage(1,'失败~');
        }

    }
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *      点赞 点踩
     *      /api/v110/video/doFlag
     */
    public function doFlag(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $vid = $request->input('vid');
        $is_flag = $request->input('is_flag'); // 标识 1：点赞 2：点踩 3:取消点赞 4：取消点踩
        if( empty($randomstr)|| empty($timestamp) || empty($signature) || empty($token) || empty($vid) || empty($is_flag)){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        // 验证签名
        $s = $this->checkSignature($randomstr,$timestamp,$signature);
        if($s==99){
            return $this->ajaxMessage($s,'签名格式不正确');
        }
        if($s==100){
            return $this->ajaxMessage($s,'请求过期');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];
        // 判断视频ID是否存在
        $data = VideoList::select("vid")->where(["vid"=>$vid,"is_visible"=>1])->first();
        if(!$data){
            return $this->ajaxMessage(103,'视频信息不存在');
        }
        $data = VideoUserLog::select("logid","uid","vid",'is_flag')->where(['uid'=>$uid,"vid"=>$vid])->first();
        $code = 1;
        $msg = "失败~";
        $db_is_flag = "";
        $isDelete = false;
        $isInsert = false;
        $reg = false;
        if($data){
            $db_is_flag = $data['is_flag'];
            if($db_is_flag==1){
                $msg = "您已点过赞了~";
                if($is_flag=="3"){
                    $isDelete = true;
                    $rmsg = "取消点赞成功~";
                }
                if($is_flag=="4"){
                    $msg = "您还没有点过踩~";
                }
            }
            if($db_is_flag==2){
                $msg = "您已点过踩了~";
                if($is_flag=="4"){
                    $isDelete = true;
                    $rmsg = "取消点踩成功~";
                }
                if($is_flag=="3"){
                    $msg = "您还没有点过赞~";
                }
            }
            if($isDelete){
                $reg = DB::table("video_user_log")->where(array('uid'=>$uid,"vid"=>$vid,"logid"=>$data['logid']))->delete();
            }
        }else{
            if($is_flag==1){
                $isInsert = true;
                $rmsg = "点赞成功~";
            }
            if($is_flag==2){
                $isInsert = true;
                $rmsg = "点踩成功~";
            }
            if($is_flag == "3"){
                $msg = "您还没有点过赞~";
            }
            if($is_flag == "4"){
                $msg = "您还没有点过踩~";
            }
            if($isInsert){
                $reg = DB::table("video_user_log")->insert(array('uid'=>$uid,"vid"=>$vid,"is_flag"=>$is_flag));
            }
        }
        if($reg){
            $code = 0;
            $msg = $rmsg;
        }
        return $this->ajaxMessage($code,$msg);
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      观看视频 消耗次数
     *
     *          /api/v110/video/doLook
     */
    public function doLook(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $vid = $request->input('vid');
        $looktime = $request->input('looktime'); // 观看时长
        $flag = $request->input('flag'); //安卓点击观看消耗次数   结束更新观看时间    0:首次 消耗次数   1：不消耗次数
        if( empty($randomstr)|| empty($timestamp) || empty($signature) || empty($token)  || empty($vid) ){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        // 验证签名
        $s = $this->checkSignature($randomstr,$timestamp,$signature);
        if($s==99){
            return $this->ajaxMessage($s,'签名格式不正确');
        }
        if($s==100){
            return $this->ajaxMessage($s,'请求过期');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        // 判断视频是否存在
        $v = VideoList::select('vid','title','pic')->where(['vid'=>$vid,'is_visible'=>1])->first();
        if(!$v){
            return $this->ajaxMessage(103,'视频信息不存在');
        }

        $user  = UserInfo::select('vipendtime',"lookcount")->where('uid',$uid)->first();
        $reg= "";
        if(!$flag){
            if( $user['vipendtime'] < strtotime(date('Y-m-d H:i:s')) && $user["lookcount"] ){
                if($user['lookcount']==0){
                    return $this->ajaxMessage(104,'观看次数已用完');
                }else{
                    $reg = DB::table("user_info")->where("uid",$uid)->decrement("lookcount");
                    $reg = DB::table("user_info")->where("uid",$uid)->increment("lookedcount");
                }
            }
        }

        // 记录播放历史
        $looklogs = UserlookLogs::select('lookid')->where(['uid'=>$uid,'vid'=>$vid])->first();
        if($looklogs){
            $reg = DB::table("userlook_logs")->where(['uid'=>$uid,'vid'=>$vid])->update(array("title"=>$v["title"],'pic'=>$v['pic'],'looktime'=>$looktime,'createtime'=>time()));
        }else{
            $reg = DB::table("userlook_logs")->insert(array(
                "uid"=>$uid,"vid"=>$vid,"title"=>$v["title"],'pic'=>$v['pic'],'looktime'=>$looktime,'createtime'=>time()
            ));
        }
        if($reg)
        {
            return $this->ajaxMessage(0,'操作成功');
        }
        else
        {
            return $this->ajaxMessage(1,'操作失败');
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *          /api/v110/video/getLookLogs
     */
    public function getLookLogs(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;

        if( empty($randomstr)|| empty($timestamp) || empty($signature) || empty($token) ){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        // 验证签名
        $s = $this->checkSignature($randomstr,$timestamp,$signature);
        if($s==99){
            return $this->ajaxMessage($s,'签名格式不正确');
        }
        if($s==100){
            return $this->ajaxMessage($s,'请求过期');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $data = UserlookLogs::select("lookid","userlook_logs.vid","userlook_logs.title","userlook_logs.pic","userlook_logs.looktime","userlook_logs.createtime")
            ->join('video_list',"video_list.vid","=","userlook_logs.vid")
            ->where(["userlook_logs.uid"=>$uid,"is_visible"=>1])
            ->orderBy('userlook_logs.createtime', 'desc')->paginate($pageSize);

        $arr = array();
        $res = array();
        if($data){
            $dataTmp = $data->toArray();
            $dataTmp = $dataTmp['data'];
            foreach($dataTmp as $key=>$value){
                if($value['pic']){
                    $dataTmp[$key]['pic'] = $value['pic'];
                }
                $dataTmp[$key]['createtime'] = date("Y-m-d H:i:s",$value["createtime"]);

                $res[date("Y-m-d",$value["createtime"])][] = $dataTmp[$key];
            }
            foreach($res as $key=>$value){
                $arr[] = array('time'=>$key,'data'=>$value);
            }
        }

        return $this->ajaxMessage(0,'success',$arr);
    }
}