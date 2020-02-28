<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\StarList;
use App\Models\UserCollect;
use App\Models\VideoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectController extends  ApiController{

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *      获取收藏列表
     */
    public function getCollectList(Request $request)
    {
        $token = $request->input('token');
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;
        $otype = !empty($request->input('otype'))?$request->input('otype'):1;

        if(empty($token))
        {
            return $this->ajaxMessage(101,'请求参数不正确');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        if($otype == 1) // 明星
        {
            $dataTmp = UserCollect::select('cid','oid','uname as name','pic')
                ->join('star_list','user_collect.oid','=','star_list.sid');
        }elseif($otype==5 || $otype==10) // mv 视频
        {
            $dataTmp = UserCollect::select('cid','oid','title as name','pic','video_list.is_free')
                ->join('video_list','user_collect.oid','=','video_list.vid');
        }
        $dataTmp = $dataTmp->where(['user_collect.otype'=>$otype,'uid'=>$uid])->paginate($pageSize)->toArray();
        $dataTmp = $dataTmp['data'];
        foreach($dataTmp as $key=>$value){
            $dbPic = $value['pic'];
            if($dbPic){
                $dataTmp[$key]['pic'] = $dbPic;//$this->urlPic().
                if(substr($dbPic,0,4)!="http"){
                    $dataTmp[$key]['pic'] = $this->urlPic().$dbPic;
                }
            }
            $dataTmp[$key]['is_collect'] = 1;
        }
        return $this->ajaxMessage(0,'success',$dataTmp);
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      加入收藏
     */
    public function addCollect(Request $request)
    {
        $token = $request->input('token');
        $oid = $request->input('oid');
        $otype = $request->input('otype'); // 1：明星  5： mv  10 ：视频

        if(empty($token) || empty($oid) || empty($otype))
        {
            return $this->ajaxMessage(101,'请求参数不正确');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];
        if($otype==1)
        {
            $data = StarList::select('sid')->where('sid',$oid)->first(); // 收藏明星
        }else{
            $data = VideoList::select('vid','otype')->where('vid',$oid)->first(); // mv  视频
            if(!$data)
            {
                return $this->ajaxMessage(102,'收藏信息不存在');
            }
            if($data['otype'] == 1){
                $otype = 5;
            }else{
                $otype =10;
            }
        }
        if(!$data)
        {
            return $this->ajaxMessage(102,'收藏信息不存在');
        }
        $data = UserCollect::select('cid')->where(['uid'=>$uid,'oid'=>$oid,'otype'=>$otype])->first();
        if($data){
            return $this->ajaxMessage(103,'该信息已收藏');
        }
        $reg = DB::table('user_collect')->insertGetId(array(
            'uid'=>$uid,'oid'=>$oid,'otype'=>$otype
        ));
        if($reg)
        {
            return $this->ajaxMessage(0,'收藏信息成功');
        }
        else
        {
            return $this->ajaxMessage(1,'收藏信息失败');
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *  取消收藏
     */
    public function delCollect(Request $request)
    {
        $token = $request->input('token');
        $oid = $request->input('oid');
        $otype = $request->input('otype'); // 1：明星  5： mv  10 ：视频
        if(empty($token) || empty($oid) || empty($otype) )
        {
            return $this->ajaxMessage(101,'请求参数不正确');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $dataTmp = UserCollect::select('cid')->where(['uid'=>$uid,'oid'=>$oid ,'otype'=>$otype])->first();
        if(!$dataTmp)
        {
            return $this->ajaxMessage(103,'收藏信息不存在');
        }

        $reg = DB::table('user_collect')->where(['uid'=>$uid,'oid'=>$oid ,'otype'=>$otype])->delete();
        if($reg)
        {
            return $this->ajaxMessage(0,'取消收藏成功');
        }
        else
        {
            return $this->ajaxMessage(1,'取消收藏失败');
        }
    }
}