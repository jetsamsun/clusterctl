<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Http\Controllers\AdminController;
use App\Models\Config;
use App\Models\LoginLogs;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends AdminController
{
    public function index(Request $request){
        $cfgs = Config::where('group', 'basic')->get();

        if($request->isMethod('post')){
            $data = $_POST;
            foreach ($cfgs as $v) {
                if(isset($data[$v->name])) {
                    if($v->type=='switch') $data[$v->name] = 1;
                    Config::where('name',$v->name)->update(array('value'=>$data[$v->name]));
                } else {
                    if($v->type=='switch') Config::where('name',$v->name)->update(array('value'=>0));
                }
            }
            return response()->json(array('code'=>1,'msg'=>"修改成功"));
        }

        return view('index.index',compact('cfgs'));
    }

    public function transet(Request $request){
        $cfgs = Config::where('group', 'trans')->get();

        if($request->isMethod('post')){
            $data = $_POST;
            foreach ($cfgs as $v) {
                if(isset($data[$v->name])) {
                    if($v->type=='switch') $data[$v->name] = 1;
                    Config::where('name',$v->name)->update(array('value'=>$data[$v->name]));
                } else {
                    if($v->type=='switch') Config::where('name',$v->name)->update(array('value'=>0));
                }
            }
            return response()->json(array('code'=>1,'msg'=>"修改成功"));
        }

        return view('index.transet',compact('cfgs'));
    }

    public function watermark(Request $request){
        $cfgs = Config::where('group', 'watermark')->get();

        if($request->isMethod('post')){
            $data = $_POST;
            foreach ($cfgs as $v) {
                if(isset($data[$v->name])) {
                    if($v->type=='switch') $data[$v->name] = 1;
                    Config::where('name',$v->name)->update(array('value'=>$data[$v->name]));
                } else {
                    if($v->type=='switch') Config::where('name',$v->name)->update(array('value'=>0));
                }
            }
            return response()->json(array('code'=>1,'msg'=>"修改成功"));
        }

        return view('index.watermark',compact('cfgs'));
    }

    public function shots(Request $request){
        $cfgs = Config::where('group', 'screenshot')->get();

        if($request->isMethod('post')){
            $data = $_POST;
            foreach ($cfgs as $v) {
                if(isset($data[$v->name])) {
                    if($v->type=='switch') $data[$v->name] = 1;
                    Config::where('name',$v->name)->update(array('value'=>$data[$v->name]));
                } else {
                    if($v->type=='switch') Config::where('name',$v->name)->update(array('value'=>0));
                }
            }
            return response()->json(array('code'=>1,'msg'=>"修改成功"));
        }

        return view('index.shots', compact( 'cfgs'));
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
