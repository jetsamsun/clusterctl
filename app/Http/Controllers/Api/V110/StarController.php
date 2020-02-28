<?php
namespace App\Http\Controllers\Api\V110;

use App\Http\Controllers\ApiController;
use App\Models\StarList;
use App\Models\UserCollect;
use App\Models\VideoList;
use Illuminate\Http\Request;

class StarController extends  ApiController{

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *    获取明星简介
     *      /api/v110/star/getStarInfoBySID
     */
    public function getStarInfoBySID(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;
        $sid = $request->input('sid');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token) || empty($sid))
        {
            return $this->ajaxMessage(101,'请求参数不正确');
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

        $star = StarList::select('sid','uname','pic')->where('sid',$sid)->first();
        if($star['pic']){
            $star['pic'] = $this->urlPic().$star['pic'];
        }else{
            return $this->ajaxMessage(103,'明星信息不存在');
        }
        // 判断是否已加入收藏
        $data = UserCollect::select('cid')->where(['uid'=>$uid,'otype'=>1,'oid'=>$sid])->first();
        if($data){
            $star['is_collect'] = 1; // 已收藏
        }else{
            $star['is_collect'] = 0;  // 未收藏
        }
        // 作品数量
        $count = VideoList::select('vid')->where(['is_visible'=>1])
            ->where('star','like','%'.$sid.'%')->count();
        $star['count'] = $count;

        // 人气
        $usercount = UserCollect::where(['otype'=>1,'oid'=>$sid])->count();
        $star['usercount'] = $usercount;

        $dataTmp = VideoList::select('vid','title','otype','pic','url','is_free','hotcount','videotime');
        $dataTmp = $dataTmp->where(['is_visible'=>1])->where('star','like','%'.$sid.'%')
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
                if($value['otype'] == 1){
                    $dataTmp[$key]['otype'] = 5;
                }else{
                    $dataTmp[$key]['otype'] =10;
                }
                // 时长时分秒
                //$dataTmp[$key]['videotime'] = gmstrftime('%H:%M:%S',$value["videotime"]);

                // 是否已收藏
                $collect = UserCollect::select('cid')->where(['uid'=>$uid,'oid'=>$value['vid'] ])->where('otype','!=',1)->first();
                if($collect){
                    $dataTmp[$key]['is_collect'] = 1;
                }else{
                    $dataTmp[$key]['is_collect'] = 0;
                }
            }
        }


        return $this->ajaxMessage(0,'success',array('star'=>$star,'dataTmp'=>$dataTmp));
    }
}