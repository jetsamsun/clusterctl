<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Http\Controllers\AdminController;
use App\Models\LoginLogs;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends AdminController
{
    public function index(){
        $count = 0;
        $sumcount = UserInfo::select('uid')->count(); // 总人数
        $vipcount = UserInfo::select('uid')->where("vipendtime",">",strtotime(date("Y-m-d H:i:s")))->count(); // vip人数
        $onlinecount = LoginLogs::select("id")
            ->whereBetween("logintime",[strtotime(date("Y-m-d H:i:s"))-7200,strtotime(date("Y-m-d H:i:s"))])
            ->count();
        return view('index.index',compact('sumcount',"vipcount","onlinecount"));
    }

    // 修改密码
    public function changePwd(Request $request){
        if($request->isMethod('post')){
            $old_pwd = $request->input('old_pwd');
            $pwd = $request->input('pwd');
            $qx_pwd = $request->input('qx_pwd');

            $res = Admin::select('adminid','username','password')
                ->where('adminid',session('id'))->first();

            if(md5($old_pwd)!=$res['password'] ){
                return response()->json(array('code'=>0,'msg'=>"旧密码不正确"));
            }elseif( $pwd == $old_pwd ){
                return response()->json(array('code'=>0,'msg'=>"新密码不能和旧密码相同"));
            }elseif( $pwd != $qx_pwd ){
                return response()->json(array('code'=>0,'msg'=>"两次输入的密码不一样"));
            }else{

                $reg = DB::table('admin')->where('adminid',$res['adminid'])->update(array(
                    'password'=>md5($pwd)
                ));
                return response()->json(array('code'=>1,'msg'=>"修改成功",'url'=>'/admin/admin/changePwd'));
            }
        }
        return view('index.changePwd');
    }
}
