<?php
namespace App\Http\Controllers\Api\V110;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MsgController extends  ApiController{
    /**
     *  求片
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      /api/v110/msg/subSeekVideo?token=06a365d56073371f72a8c4745a35e38e&&content=123456&&randomstr=123&&timestamp=1544214676&&signature=0de4c906f0d69ac18cccd67bee69695a
     */
    public function subSeekVideo(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $content = $request->input('content');

        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token) || empty($content) ){
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

        $reg = DB::table('seek_video')->insert(array('uid'=>$uid,'content'=>$content,'time'=>time() ));
        if($reg){
            return $this->ajaxMessage(0,'留言成功');
        }else{
            return $this->ajaxMessage(1,'留言失败');
        }

    }
    /**
     *  问题反馈
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     */
    public function subVideoTrouble(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $content = $request->input('content');
        $vid = $request->input('vid');

        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token) || empty($content)  || empty($vid)){
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

        $reg = DB::table('video_trouble')->insert(array('uid'=>$uid,'vid'=>$vid,'content'=>$content,'time'=>time() ));
        if($reg){
            return $this->ajaxMessage(0,'反馈成功');
        }else{
            return $this->ajaxMessage(1,'反馈失败');
        }

    }
}