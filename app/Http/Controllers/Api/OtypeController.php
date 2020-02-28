<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\ListOtype;
use App\Models\ScreenOtype;
use App\Models\VideoOtype;
use Illuminate\Http\Request;

class OtypeController extends  ApiController{

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      otype =1 mv   otype = 5 视频
     *      获取
     */
    public function getListOtype(Request $request)
    {
        $otype = $request->input('otype');
        $os = empty($request->input('os'))?'ios':$request->input('os');
        if( empty( $otype ) )
        {
            return $this->ajaxMessage(101,'请求参数不完整');
        }

        $dataTmp = ListOtype::select('oid','otypename','title','pic','urlotype','url','ios_url')->where('otype',$otype)->get()->toArray();
        foreach($dataTmp as $key=>$value){
            if($value['pic']){
                $dataTmp[$key]['pic'] = $this->urlPic().$value['pic'];
            }
            if($os =='ios'){
                unset($dataTmp[$key]["url"]);
            }else{
                unset($dataTmp[$key]["ios_url"]);
            }
        }

        return $this->ajaxMessage(0,'success',$dataTmp);
    }
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      otype =1 mv   otype = 5 视频
     *      获取
     */
    public function getVideoOtype(Request $request)
    {
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;
        $otype = $request->input('otype');
        if( empty( $otype ) )
        {
            return $this->ajaxMessage(101,'请求参数不完整');
        }

        $dataTmp = VideoOtype::select('oid','otypename','pic')
            ->where('otype',$otype)
            ->paginate($pageSize);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];
            foreach($dataTmp as $key=>$value){
                if($value['pic']){
                    $dataTmp[$key]['pic'] = $this->urlPic().$value['pic'];
                }
            }
        }

        return $this->ajaxMessage(0,'success',$dataTmp);
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *      筛选条件
     */
    public function getScreenOtype(Request $request)
    {
        $otype = $request->input('otype'); //  1：明星 5：排行 10 其他

        if( empty( $otype ) )
        {
            return $this->ajaxMessage(101,'请求参数不完整');
        }

        $dataTmp = ScreenOtype::select('oid','otypename')->where(['otype'=>$otype,'pid'=>0])->get();
        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
        }else{
            return $this->ajaxMessage(0,'暂无数据');
        }
        foreach($dataTmp as $key=>$value){

            $data = ScreenOtype::select('oid','otypename')->where(['otype'=>$otype,'pid'=>$value['oid']])
                ->get();
            if($data){
                $data = $data->toArray();
            }
            $dataTmp[$key]['param'] = $data;
        }

        return $this->ajaxMessage(0,'success',$dataTmp);
    }


}