<?php
namespace App\Http\Controllers\Api\V110;

use App\Http\Controllers\ApiController;
use App\Models\SmsLogs;
use App\Models\UserInfo;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use function Qiniu\json_decode;
use function GuzzleHttp\json_encode;

class UserController extends  ApiController{

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *      随机生成账户
     *      api/v110/user/randomUser?os=1
     */
    public function randomUser(Request $request)
    {
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $os = $request->input('os');
        $randomnum = $this->generateRandomStr(15);
        $invitecode = $this->generateRandomStr(4);
        $version = $this->version;
        $token = md5( $randomnum . $this->getCode(4).$this->getCode(4) );   // token
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($os)){
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
        // 生成用户
        $reg = DB::table('user_info')->insertGetId(array(
            'randomnum'=>$randomnum,'token'=>$token,'os'=>$os,'version'=>$version,'invitecode'=>$invitecode,
            'registertime'=>strtotime(date('Y-m-d H:i:s'))
        ));
        if($reg){
            return $this->ajaxMessage(0,'success',array('token'=>$token));
        }else{
            return $this->ajaxMessage(1,'随机账户生成失败');
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      安全码设置
     *      /api/v110/user/doSafeCode
     */
    public function doSafeCode(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $code = $request->input('code');

        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token) || empty($code) ){
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

        $dataTmp = UserInfo::select('uid','invitecode')
            ->where('uid',$uid)->first();

        // 更新安全码
        $reg = DB::table("user_info")->where('uid',$uid)->update(array(
            'safecode'=>md5(md5($code.$dataTmp['invitecode']))
        ));

        if($reg){
            return $this->ajaxMessage(0,'操作成功');
        }else{
            return $this->ajaxMessage(1,'操作失败');
        }
    }
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      获取用户信息
     *  /api/v110/user/getUserInfo
     */
    public function getUserInfo(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token) ){
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

        $dataTmp = UserInfo::select('uid','pic','randomnum','mobile','vipendtime','vipotype',
            'invitecode','safecode','downcount','lookcount','lookedcount','residual_asset')
            ->where('uid',$uid)->first()->toArray();

        if($dataTmp['pic']){
            $dataTmp['pic'] = $this->urlPic().$dataTmp['pic'];
        }
        // 判断是否是vip
        if($dataTmp['vipendtime'] && $dataTmp['vipendtime']>strtotime(date('Y-m-d H:i:s'))){
            $dataTmp['is_vip'] = 1;
            $dataTmp["daycount"] = round(($dataTmp['vipendtime']-strtotime(date('Y-m-d H:i:s')))/60/60/24);
            if($dataTmp['vipotype'] == 1){
                $dataTmp['vipotype'] = "月卡";
            }elseif($dataTmp['vipotype'] == 5){
                $dataTmp['vipotype'] = "季卡";
            }elseif($dataTmp['vipotype'] == 10){
                $dataTmp['vipotype'] = "年卡";
            }else{
                $dataTmp['vipotype'] = "无";
            }

            $dataTmp['vipendtime'] = date('Y-m-d',$dataTmp['vipendtime']);
        }else{
            $dataTmp['is_vip'] = 0;
            $dataTmp["daycount"] = 0;
            $dataTmp['vipendtime'] = '';
        }

        // 是否设置了安全码
        if($dataTmp['safecode']){
            $dataTmp['is_safe'] = 1;
        }else{
            $dataTmp['is_safe'] = 0;
        }

        // 推广人数
        $dataTmp['sharecount'] = 0;
        return $this->ajaxMessage(0,'success',$dataTmp);
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *              绑定手机号
     *
     */
    public function doUserMobile(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $mobile = $request->input('mobile');
        $verify_code = $request->input('verifycode');
        $flag = $request->input('flag'); // 1:绑定 2：找回

        if( empty($randomstr)||empty($timestamp)||empty($signature)|| empty($token) || empty($mobile) || empty($verify_code)){
            return $this->ajaxMessage(101,'请求信息不完整');
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

        // 判断验证码
        $dataTmp=SmsLogs::select('mobile','verify_code','time')->where('mobile',$mobile)->orderBy('id', 'DESC')->first();
        if(($dataTmp['verify_code'] != $verify_code) && ( $verify_code != $this->verify_code ) ){
            return $this->ajaxMessage(103,'验证码不正确,请重新输入');
        }
        if( (( time()-strtotime($dataTmp['time'])) > 60*5) && ( $verify_code != $this->verify_code )  )
        {
            return $this->ajaxMessage(103,'验证码已过期,请重新获取');
        }

        $data = UserInfo::select('uid','randomnum','mobile')->where("uid",$uid)->first();
        if(!$data["mobile"])
        {
            $data = UserInfo::select('uid','randomnum','mobile')->where("mobile",$mobile)->first();
            if($data){
                return $this->ajaxMessage(105,'手机号已被绑定~');
            }
            $reg = DB::table('user_info')->where('uid',$uid)->update(array('mobile'=>$mobile));
            if($reg){
                return $this->ajaxMessage(0,'操作成功');
            }else{
                return $this->ajaxMessage(1,'操作失败');
            }

        }elseif($data["mobile"] == $mobile && $flag == 2){

            return $this->ajaxMessage(104,'当前已绑定该手机号~');
        }elseif($data["mobile"] == $mobile && $flag == 1){

            $reg = DB::table('user_info')->where('uid',$uid)->update(array('mobile'=>''));
            if($reg){
                return $this->ajaxMessage(0,'解绑成功');
            }else{
                return $this->ajaxMessage(1,'解绑失败');
            }
        }
        $data = UserInfo::select('uid','randomnum','mobile')->where("mobile",$mobile)->first();
        if(!$data){
            return $this->ajaxMessage(105,'手机号还未绑定~');
        }else{
            return $this->ajaxMessage(0,'success',$data['randomnum']);
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      邀请码
     */
    public function subInviteCode(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $invitecode = $request->input('invitecode');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token) || empty($invitecode) ){
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

        $data = UserInfo::select("uid",'registertime')->where('uid',$uid)->first();
        if( time() > $data['registertime']+60*60*24 ){
            return $this->ajaxMessage(103,'用户注册时间超过一天无法填写邀请码');
        }

        $u = UserInfo::select("uid")->where('invitecode',$invitecode)->first();
        if(!$u){
            return $this->ajaxMessage(104,'邀请码不存在');
        }
        $lev = UserLevel::select('level_id',"first_level","second_level","third_level","fourth_level")->where("uid",$uid)->first();
        if($lev){
            return $this->ajaxMessage(105,'您已被邀请过');
        }

        // 增加分销等级
        $level = UserLevel::select('level_id',"first_level","second_level","third_level","fourth_level")->where("uid",$u['uid'])->first();
        if(!$level){
            $level["first_level"] = 0;
            $level["second_level"] = 0;
            $level["third_level"] = 0;
        }
        $reg = DB::table('user_level')->insert(array(
            'uid'=>$uid,'first_level'=>$u["uid"],'second_level'=>$level["first_level"],"third_level"=>$level["second_level"],'fourth_level'=>$level["third_level"]
        ));

        if($reg){
            return $this->ajaxMessage(0,'操作成功');
        }else{
            return $this->ajaxMessage(0,'操作失败');
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *  获取用户二维码
     * /api/v110/user/getUserQrcode
     */
    public function getUserQrcode(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token) ){
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
        $dataTmp = UserInfo::select('randomnum')->where('uid',$uid)->first();

        $rootPath = "/assets/uploads/image/userqrcode/";
        $filename = $uid . '.png'; // 毫秒
        $qrcode = $rootPath.$filename;
        $timestamp = time();
        $url = $this->urlPic()."/api/v110/user/getUserTokenByRandomnum?num=".$dataTmp['randomnum']."&times=".$timestamp."&key=".md5(md5($timestamp.$dataTmp['randomnum']));
        QrCode::format('png')->size(100)->margin(0)
            ->generate($url,public_path($qrcode));
        $qrcode = $this->urlPic().$qrcode.'?'.$timestamp;

        return $this->ajaxMessage(0,'success',array('qrcode'=>$qrcode,'randomnum'=>$dataTmp['randomnum']));
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      扫码登录
     */
    public function getUserTokenByRandomnum(Request $request){
        $num = $request->input("num");
        $timestamp = $request->input("times");
        $key = $request->input("key");
        $flag = $request->input("flag"); // 1:安卓 返回json
        if(time()>$timestamp+180){
            return $this->ajaxMessage(102,'二维码过期,请刷新');
        }
        if( md5(md5($timestamp.$num)) != $key ){
            return $this->ajaxMessage(103,'验证失败');
        }

        $data = UserInfo::select("uid","token")->where('randomnum',$num)->first();
        if($data){
            $qrcodetoken = md5($this->getCode(4).$this->getCode(4) );   // 扫码临时token
            $reg = DB::table("user_info")->where('uid',$data["uid"])->update(array('qrcodetoken'=>$qrcodetoken));
            if($reg){
                if($flag){
                    return $this->ajaxMessage(0,'success',$qrcodetoken);
                }else{
                    //echo $qrcodetoken;
                    return $this->ajaxMessage(0,'success',$qrcodetoken);
                }
                //return $this->ajaxMessage(0,'success',$qrcodetoken);
            }else{
                return $this->ajaxMessage(1,'新登陆信息失败');
            }
        }else{
            return $this->ajaxMessage(1,'信息不存在');
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *   重新登录
     * /api/v110/user/doLogin
     */
    public function doLogin(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $str = $request->input('str');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($str) ){
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

        $dataTmp = UserInfo::select('uid','randomnum')->where('qrcodetoken',$str)->first();
        if(!$dataTmp){
            return $this->ajaxMessage(103,'信息失效');
        }

        $token = md5($dataTmp['randomnum'].$this->getCode(4).$this->getCode(4) );   // 扫码临时token
        $reg = DB::table("user_info")->where('uid',$dataTmp["uid"])
            ->update(array('qrcodetoken'=>'','token'=>$token));

        if($reg){
            return $this->ajaxMessage(0,'success',array('token'=>$token));
        }else{
            return $this->ajaxMessage(1,'访问失败');
        }

    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *  切换账号
     *  /api/v110/user/switchNumber
     */
    public function switchNumber(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $number = $request->input('number');
        $safecode = $request->input('safecode');
        if( empty($randomstr) ||empty($timestamp)||empty($signature) || empty($token) || empty($number) ){
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

        $user = UserInfo::select("uid","randomnum","safecode","invitecode")
            ->where('randomnum',$number)
            ->orWhere('mobile',$number)
            ->first();
        if(!$user){
            return $this->ajaxMessage(103,'账号信息不存在');
        }
        if($user["uid"] == $uid){
            return $this->ajaxMessage(104,'输入账号与当前账号一样');
        }
        if($safecode){
            if( md5(md5($safecode.$user['invitecode'])) != $user["safecode"] ){
                return $this->ajaxMessage(105,'安全码输入不对');
            }
        }

        $token = md5($user['randomnum'].$this->getCode(4).$this->getCode(4) );   // 扫码临时token
        $reg = DB::table("user_info")->where('uid',$user["uid"])
            ->update(array('token'=>$token));

        if($reg){
            return $this->ajaxMessage(0,'success',array('token'=>$token));
        }else{
            return $this->ajaxMessage(1,'访问失败');
        }

    }
    /**
     * @desc 获取短信验证码
     * @date 2019-04-16
     * @user chenglong
     */
    public function getVerifycode(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $mobile = $request->input('mobile');
        // $flag = $request->input('flag');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token) || empty($mobile)){
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
        if(!$dataTmp){
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $data['mobile'] = $mobile;
        $sendCode = rand(100000, 199999);
        $reg = false;
        $mobileLength = strlen($mobile);
        if($mobileLength==11 && substr($mobile,0,1)=="1"){
            $mobile = "86".$mobile;
        }
        if(sendSms253Itn($mobile,$sendCode)){
            $data['verify_code'] = $sendCode;
            $data['time'] = date('Y-m-d H:i:s');
            $reg = DB::table('sms_logs')->insert($data);
        }
        if($reg){
            return $this->ajaxMessage(0,'success');
        }else{
            return $this->ajaxMessage(1,'发送失败');
        }
    }
    /**
     * @desc 提交 iOS设备 推送用的 device token
     * @date 2019-04-25
     */
    public function setPushID(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $devicetoken = $request->input('devicetoken');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token) || empty($devicetoken)){
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
        if(!$dataTmp){
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $userUid = $dataTmp['uid'];
        DB::table("user_info")->where('uid',$userUid)->update(array('devicetoken'=>$devicetoken));
        return $this->ajaxMessage(0,'设置成功');
    }
    /**
     * @desc 清空该用户的推送计数
     * @date 2019-04-26
     */
    public function setPushClear(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token)){
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
        if(!$dataTmp){
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $userUid = $dataTmp['uid'];
        DB::table("user_info")->where('uid',$userUid)->update(array('pushcount'=>0));
        return $this->ajaxMessage(0,'OK');
    }
    public function getInviteUrl(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token)){
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
        if(!$dataTmp){
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];
        $getInvitecode = UserInfo::select('invitecode')->where('uid',$uid)->first();
        $url = "http://api.t.sina.com.cn/short_url/shorten.json?source=2815391962&url_long=http://naisir.kuaibaotv.com/appsetup/install.php?f=".$getInvitecode['invitecode'];
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
        $get = \json_decode($tmpInfo,true);
        $invitetext = "青瓜TV，独家资源每日更新，请复制链接用手机自带浏览器打开，QQ或微信第三方软件内置浏览器无法打开网页 ".$get['0']['url_short'];
        return $this->ajaxMessage(0,'success',array('invitetext'=>$invitetext));
    }
    public function getBanner(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $pos = $request->input('pos');//1 播放页banner、2 列表页banner
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token)){
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
        if(!$dataTmp){
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $data['pic'] = "http://naisir.kuaibaotv.com/banner/test1.jpg";
        $data['url'] = "http://cctv4.me";
        return $this->ajaxMessage(0,'success',$data);
    }
    public function getTopBanner(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token)){
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
        $data['bar_content'] = "公告：安装修复程序，排除软件问题！";
        $data['bar_prompt'] = "青瓜TV应用闪退时，可以用此工具修复闪退，随时访问官网，请务必安装";
        $data['bar_url'] = "http://web2.edutopcloud.tv/qg99tv.mobileconfig";
        return $this->ajaxMessage(0,'success',$data);
    }
    public function getStartBanner(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if( empty($randomstr)||empty($timestamp)||empty($signature) || empty($token)){
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
        $data['bar_content'] = "公告：安装修复程序，排除软件问题！";
        $data['bar_prompt'] = "青瓜TV应用闪退时，可以用此工具修复闪退，随时访问官网，请务必安装";
        $data['bar_url'] = "http://web2.edutopcloud.tv/qg99tv.mobileconfig";
        return $this->ajaxMessage(0,'success',$data);
    }
}