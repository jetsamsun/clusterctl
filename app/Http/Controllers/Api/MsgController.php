<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\MsgLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MsgController extends  ApiController{

    public function subMsg(Request $request){
        $token = $request->input('token');
        $content = $request->input('content');

        if(empty($token) || empty($content) ){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $reg = DB::table('msg_log')->insert(array('uid'=>$uid,'content'=>$content,'time'=>strtotime(date('Y-m-d H:i:s'))));
        if($reg){
            return $this->ajaxMessage(0,'留言成功');
        }else{
            return $this->ajaxMessage(1,'留言失败');
        }

    }
}