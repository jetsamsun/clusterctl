<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\StarList;
use App\Models\UserCollect;
use App\Models\VideoList;
use Illuminate\Http\Request;

class StarController extends  ApiController{

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *      获取明星列表
     */
    public function getStarList(Request $request)
    {
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;
        $token = $request->input('token');
        $screenotype = $request->input('screenotype');
        $search = $request->input('search');
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $dataTmp = StarList::select('sid','uname','pic');
        if($screenotype){
            $screenotype = explode(',',$screenotype);
            sort($screenotype);
            $screenotype = implode(',',$screenotype);

            $dataTmp = $dataTmp->where('screenotype','like','%'.$screenotype.'%');
        }
        if($search){
            $dataTmp = $dataTmp->where('uname','like','%'.$search.'%');
        }
        $dataTmp = $dataTmp->paginate($pageSize)->toArray();
        $dataTmp = $dataTmp['data'];
        foreach($dataTmp as $key=>$value)
        {
            if($value['pic'])
            {
                $dataTmp[$key]['pic'] = $this->urlPic().$value['pic'];
            }

            // 判断是否已加入收藏
            $data = UserCollect::select('cid')->where(['uid'=>$uid,'otype'=>1,'oid'=>$value['sid']])->first();
            if($data){
                $dataTmp[$key]['is_collect'] = 1; // 已收藏
            }else{
                $dataTmp[$key]['is_collect'] = 0;  // 未收藏
            }
        }
        return $this->ajaxMessage(0,'success',$dataTmp);
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *    获取明星简介
     *      /api/v1/star/getStarInfoBySID
     */
    public function getStarInfoBySID(Request $request){
        $token = $request->input('token');
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;
        $sid = $request->input('sid');
        if(empty($token) || empty($sid))
        {
            return $this->ajaxMessage(101,'请求参数不正确');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $star = StarList::select('sid','uname','pic')->where('sid',$sid)->first()->toArray();
        if($star['pic']){
            $star['pic'] = $this->urlPic().$star['pic'];
        }
        // 判断是否已加入收藏
        $data = UserCollect::select('cid')->where(['uid'=>$uid,'otype'=>1,'oid'=>$sid])->first();
        if($data){
            $star['is_collect'] = 1; // 已收藏
        }else{
            $star['is_collect'] = 0;  // 未收藏
        }
        // 作品
        $count = VideoList::select('vid')->where(['is_visible'=>1])
            ->where('star','like','%'.$sid.'%')->count();
        $star['count'] = $count;

        $dataTmp = VideoList::select('vid','title','otype','pic','url','is_free');
        $dataTmp = $dataTmp->where(['is_visible'=>1])->where('star','like','%'.$sid.'%')
            ->orderBy('vid', 'desc')->paginate($pageSize);
        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];
            foreach($dataTmp as $key=>$value){
                if($value['pic']){
                    $dataTmp[$key]['pic'] = $this->urlPic().$value['pic'];
                }
                if($value['url']){
                    $dataTmp[$key]['url'] = $this->urlPic().$value['url'];
                }
                if($value['otype'] == 1){
                    $dataTmp[$key]['otype'] = 5;
                }else{
                    $dataTmp[$key]['otype'] =10;
                }
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