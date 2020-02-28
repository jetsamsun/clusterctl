<?php
namespace App\Http\Controllers\Api\V110;

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
     *      /api/v110/otype/getListOtype
     *      获取
     */
    public function getListOtype(Request $request)
    {
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $otype = $request->input('otype');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty( $otype ) )
        {
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

        if($otype == 1 || $otype==5){
            $dataTmp = ListOtype::select('oid','otypename','title_data','pic_data','urlotype_data','url_data','ios_url_data')->where('otype',$otype)->where('oid',"!=",'10025')->where('oid',"!=",'10026')->get()->toArray();
            foreach($dataTmp as $key=>$value){
                $pics = array();
                if($value['title_data']){
                    $titles = explode(',',$value["title_data"]);
                    $picdata = explode(',',$value["pic_data"]);
                    $urlotype_data = explode(',',$value["urlotype_data"]);
                    $url_data = explode(',',$value["url_data"]);
                    $ios_url_data = explode(',',$value["ios_url_data"]);
                    if($titles && $picdata && $urlotype_data && $url_data && $ios_url_data ){
                        foreach($titles as $k=>$v){
                            $pics[]=array('title'=>$v,'pic'=>$this->urlPic().@$picdata[$k],'urlotype'=>@$urlotype_data[$k],
                                'url'=>@$url_data[$k],'ios_url'=>@$ios_url_data[$k]);
                        }
                    }
                    $dataTmp[$key]['pics'] = $pics;
                }else{
                    $dataTmp[$key]['pics'] = $pics;
                }
                unset($dataTmp[$key]['title_data']);
                unset($dataTmp[$key]['pic_data']);
                unset($dataTmp[$key]['urlotype_data']);
                unset($dataTmp[$key]['url_data']);
                unset($dataTmp[$key]['ios_url_data']);
            }
        }else{
            return $this->ajaxMessage(102,'请求参数不正确');
        }


        return $this->ajaxMessage(0,'success',$dataTmp);
    }


}