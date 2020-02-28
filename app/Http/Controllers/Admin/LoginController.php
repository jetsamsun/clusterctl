<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mews\Captcha\Facades\Captcha;

class LoginController extends Controller
{
    public function login(Request $request){
        if($request->isMethod('post')){
            $username=$request->input('username');
            $password=$request->input('password');
            $captcha=$request->input('vercode');

            if(!Captcha::check($captcha)){
                //return view('admin.login.login')->with('error','* 验证码输入有误');
                return response()->json(array('code'=>0,'msg'=>"验证码输入有误"));
            }
            $userInfo=Admin::select('adminid','username','password')->where(['username'=>$username,'is_visible'=>1])->first();
            //echo 1111;die;
            if(!$userInfo){
                return response()->json(array('code'=>0,'msg'=>"用户不存在"));
            }elseif(md5($password)!=$userInfo->password){
                return response()->json(array('code'=>0,'msg'=>"用户密码输入错误"));
            }else{
                $request->session()->put('id', $userInfo['adminid']);
                $request->session()->put('username', $userInfo['username']);
                return response()->json(array('code'=>1,'msg'=>"登陆成功",'url'=>'/admin/index'));
            }
        }

        return view('login');
    }
    public function loginout(Request $request){
        $request->session()->flush();
        return redirect('/admintv');
    }
    /*创建验证码*/
    public function captchamews() {
        return Captcha::create('flat');
    }

}
