<?php
namespace App\Http\Controllers\Api;

use App\Models\AppInfo;
use App\Http\Controllers\ApiController;
use App\Models\ShareLogs;
use App\Models\UserInfo;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppController extends  ApiController{

    public function getAppCon(Request $request)
    {
        $os = $request->input('os');

        if(empty($os)){
            return $this->ajaxMessage(101,'请求参数不完整');
        }

        // 生成用户
        $data = AppInfo::select("qrcode",'pic','bgpic','text')->where('os',$os)->first();
        if($data){
            $data = $data->toArray();
            if($data['qrcode']){
                $data['qrcode'] = $this->urlPic().$data["qrcode"];
            }
            if($data['pic']){
                $data['pic'] = $this->urlPic().$data["pic"];
            }
            if($data['bgpic']){
                $data['bgpic'] = $this->urlPic().$data["bgpic"];
            }
            return $this->ajaxMessage(0,'success',$data);
        }else{
            return $this->ajaxMessage(1,'暂无数据');
        }
    }

    public function share(Request $request){
        $token = $request->input('token');
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

        $time = date("Y-m-d H:i:s",$dataTmp['vipendtime']);
        if($time < date('Y-m-d H:i:s')){
            $time = date('Y-m-d H:i:s');
        }

        $sharelog = ShareLogs::select("uid")->where(["uid"=>$uid,"time"=>date("Y-m-d")])->first();
        if($sharelog){
            return $this->ajaxMessage(0,'今天观看天数已增加');
        }else{
            DB::table("share_logs")->insert(array('uid'=>$uid,"time"=>date("Y-m-d")));

            $reg = DB::table("user_info")->where("uid",$uid)->update(array(
                'vipendtime'=>strtotime(date('Y-m-d H:i:s',strtotime("$time +1 day")))
            ));
        }


        if($reg)
        {
            return $this->ajaxMessage(0,'增加VIP天数成功');
        }
        else
        {
            return $this->ajaxMessage(1,'增加VIP天数失败');
        }
    }
    public function getVersion(Request $request){
        $os = $request->input('os');
        $curversion = $request->input('curversion');
        if(empty($os)||empty($curversion)){
            return $this->ajaxMessage(101,'请求参数不正确');
        }
        $data = Version::select("download_url",'new_version','update_content','is_force')->where('os',$os)->first();
        if($data){
            $data = $data->toArray();
            return $this->ajaxMessage(0,'success',$data);
        }else{
            return $this->ajaxMessage(1,'error'); 
        }
    }
    public function getStatus(){
        return $this->ajaxMessage(0,'success');
    }
}