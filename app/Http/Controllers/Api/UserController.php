<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends  ApiController{

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *      随机生成账户
     *      api/v1/user/randomUser?os=1
     */
    public function randomUser(Request $request)
    {
        $os = $request->input('os');
        $randomnum = $this->msectime().$this->generateRandomStr(3);
        $version = $this->version;
        $token = md5( $randomnum . $this->getCode(4) );   // token
        if(empty($os)){
            return $this->ajaxMessage(101,'请求参数不完整');
        }

        // 生成用户
        $reg = DB::table('user_info')->insertGetId(array(
            'randomnum'=>$randomnum,'token'=>$token,'os'=>$os,'version'=>$version,
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
     *      获取用户信息
     *  user/getUserInfo
     */
    public function getUserInfo(Request $request){
        $token = $request->input('token');
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $dataTmp = UserInfo::select('uid','pic','randomnum','mobile','email','vipendtime')
            ->where('token',$token)->first()->toArray();

        if($dataTmp['pic']){
            $dataTmp['pic'] = $this->urlPic().$dataTmp['pic'];
        }
        // 判断是否是vip
        if($dataTmp['vipendtime'] && $dataTmp['vipendtime']>strtotime(date('Y-m-d H:i:s'))){
            $dataTmp['is_vip'] = 1;
            $dataTmp["daycount"] = round(($dataTmp['vipendtime']-strtotime(date('Y-m-d H:i:s')))/60/60/24);
        }else{
            $dataTmp['is_vip'] = 0;
            $dataTmp["daycount"] = 0;
        }
        $dataTmp['vipendtime'] = date('Y-m-d',$dataTmp['vipendtime']);

        return $this->ajaxMessage(0,'success',$dataTmp);
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *  绑定页面
     *
     */
    public function bindUserInfo(Request $request){
        $token = $request->input('token');
        $email = $request->input('email');
        $mobile = $request->input('mobile');
        $password = $request->input('password');
        $qrpassword = $request->input('qrpassword');
        $data = array();
        if( empty($token) || (empty($email) && empty($mobile)) || empty($password) || empty($qrpassword) ){
            return $this->ajaxMessage(101,'请求信息不完整');
        }
        if($password!=$qrpassword){
            return $this->ajaxMessage(102,'两次输入密码不一样');
        }
        $data = UserInfo::select('uid');
        if($email){
            $data = $data->where('email',$email);
        }
        if($mobile){
            $data = $data->where('mobile',$mobile);
        }
        $data=$data->first();
        if($data)
        {
            return $this->ajaxMessage(103,'账号已被绑定');
        }
        $salt = $this->getCode();
        $data = array('email'=>$email,'password'=>MD5($password.$salt),'salt'=>$salt,'mobile'=>$mobile);


        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(104,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $reg = DB::table('user_info')->where('uid',$uid)->update($data);

        if($reg){
            return $this->ajaxMessage(0,'绑定成功');
        }else{
            return $this->ajaxMessage(1,'绑定失败');
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      更新密码
     */
    public function updatePwd(Request $request){
        $token = $request->input('token');
        $password = $request->input('password');
        $newpassword = $request->input('newpassword');
        if(empty($token) || empty($password) || empty($newpassword) ){
            return $this->ajaxMessage(101,'请求信息不完整');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $user = UserInfo::select('password','salt')->where('uid',$uid)->first()->toArray();
        if($user['password'] != MD5($password.$user['salt'])){
            return $this->ajaxMessage(103,'原密码输入错误');
        }

        if($password === $newpassword){
            return $this->ajaxMessage(104,'新密码和原密码相同');
        }

        $salt = $this->getCode();

        $reg = DB::table('user_info')->where('uid',$uid)->update(array(
            'password'=>md5($newpassword.$salt),'salt'=>$salt
        ));

        if($reg){
            return $this->ajaxMessage(0,'更新成功');
        }else{
            return $this->ajaxMessage(1,'更新失败');
        }
    }
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      忘记密码
     */
    public function forgetPwd(Request $request){
        $number = $request->input('number');
        $oldpassword = $request->input('oldpassword');
        $password = $request->input('password');
        if( empty($number) || empty($password) || empty($oldpassword) ){
            return $this->ajaxMessage(101,'请求信息不完整');
        }

        $user = UserInfo::select('uid','password','salt');
        if(strstr($number, '@')){
            $user = $user->where('email',$number)->first();
        }else{
            $user = $user->where('mobile',$number)->first();
        }
        if(!$user){
            return $this->ajaxMessage(102,'用户不存在');
        }
        $user = $user->toArray();

        if($user['password'] != MD5($oldpassword.$user['salt'])){
            return $this->ajaxMessage(103,'原密码输入错误');
        }
        $salt = $this->getCode();

        $reg = DB::table('user_info')->where('uid',$user['uid'])->update(array(
            'password'=>md5($password.$salt),'salt'=>$salt
        ));

        if($reg){
            return $this->ajaxMessage(0,'找回成功');
        }else{
            return $this->ajaxMessage(1,'找回失败');
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      登录
     */
    public function login(Request $request){
        $mobile = $request->input('mobile');
        $email = $request->input('email');
        $password = $request->input('password');

        if((empty($mobile) && empty($email) ) || empty($password)){
            return $this->ajaxMessage(101,'请求信息不完整');
        }

        $user = UserInfo::select('uid','password','salt');
        if($mobile){
            $user = $user->where('mobile',$mobile)->first();
        }elseif($email){
            $user = $user->where('email',$email)->first();
        }



        if(!$user){
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $user = $user->toArray();
        if($user['password'] != MD5($password.$user['salt'])){
            return $this->ajaxMessage(103,'原密码输入错误');
        }

        $randomnum = $this->msectime().$this->generateRandomStr(3);
        $token = md5( $randomnum . $this->getCode(4) );   // token

        $reg = DB::table('user_info')->where('uid',$user['uid'])->update(array('token'=>$token));
        if($reg){
            return $this->ajaxMessage(0,'success',array('token'=>$token));
        }else{
            return $this->ajaxMessage(1,'登录失败');
        }

    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *          确认密码
     */
    public function confirmPwd(Request $request){
        $token = $request->input('token');
        $password = $request->input('password');
        if(empty($token) || empty($password)  ){
            return $this->ajaxMessage(101,'请求信息不完整');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $user = UserInfo::select('password','salt')->where('uid',$uid)->first()->toArray();
        if($user['password'] != MD5($password.$user['salt'])){
            return $this->ajaxMessage(103,'密码输入错误');
        }

        return $this->ajaxMessage(0,'success');
    }
    public function getUserShareQrCode(Request $request){
        $token = $request->input('token');
        if(empty($token)){
            return $this->ajaxMessage(101,'请求信息不完整');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp){
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];
        $getInvitecode = UserInfo::select('invitecode')->where('uid',$uid)->first()->toArray();


        $rootPath = "/assets/uploads/image/userShare/";
        $filename = $uid."_".$getInvitecode['invitecode'] . '.png'; // 毫秒
        $qrcode = $rootPath.$filename;
        $url = "http://cctv4.me/?f=".$getInvitecode['invitecode'];
        QrCode::format('png')->size(100)->margin(0)
            ->generate($url,public_path($qrcode));

        return $this->ajaxMessage(0,'success',array('qrUrl'=>$qrcode,'invitecode'=>$getInvitecode['invitecode']));
    }
}