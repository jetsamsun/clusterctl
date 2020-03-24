<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\MediaActors;
use App\Models\MediaCountry;
use App\Models\MediaRole;
use App\Models\ScreenOtype;
use App\Models\StarList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StarController extends AdminController
{
    public function star(){
        return view('star.list');
    }
    public function getStarList(Request $request){
        $limit = $request->input('limit');
        $uname = $request->input('uname');
        $count = MediaActors::select('Id');
        if($uname){
            $count = $count->where('Name','like','%'.$uname.'%')->orWhere('English_name','like','%'.$uname.'%');
        }
        $count = $count->count();

        $dataTmp = MediaActors::select('*');
        if($uname){
            $dataTmp = $dataTmp->where('Name','like','%'.$uname.'%')->orWhere('English_name','like','%'.$uname.'%');
        }
        $dataTmp = $dataTmp->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                $dataTmp[$key]['Country'] = MediaCountry::where('Code', $value['Country'])->value('Name');

                if($value['Role']) {
                    $rolestr = '';
                    $roles = explode(',', $value['Role']);
                    foreach ($roles as  $v) {
                        $name = MediaRole::where('Id',$v)->value('Name');
                        if($rolestr) $rolestr = $rolestr.','.$name;
                        else $rolestr = $name;
                    }
                    $dataTmp[$key]['Role'] = $rolestr;
                }
            }
        }

        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }
    public function addStar(Request $request){
        if($request->isMethod('post')){
            $uname = $request->input('uname');
            $uname_en = $request->input('uname_en');
            $pic = $request->input('imgval');
            $role = $request->input('role');
            $country = $request->input('country');

            if(!empty($role) && is_array($role)) {
                sort($role);
                $role = implode(',', $role);
            }

            $reg = DB::table('media_actors')->insert(array(
                'Name'=> $uname, 'English_name'=> $uname_en, 'Country'=> $country, 'Image'=> $pic, 'Role'=>$role
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }

        $country = MediaCountry::get()->toArray();
        $role = MediaRole::get()->toArray();

        return view('star.add',compact('country','role'));
    }
    public function editstar(Request $request,$sid){
        if($request->isMethod('post')){
            $uname = $request->input('uname');
            $uname_en = $request->input('uname_en');
            $pic = $request->input('imgval');
            $role = $request->input('role');
            $country = $request->input('country');

            if(!empty($role) && is_array($role)) {
                sort($role);
                $role = implode(',', $role);
            }

            $reg = DB::table('media_actors')->where('Id',$sid)->update(array(
                'Name'=> $uname, 'English_name'=> $uname_en, 'Country'=> $country, 'Image'=> $pic, 'Role'=>$role
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }

        $country = MediaCountry::get()->toArray();
        $role = MediaRole::get()->toArray();
        $data = MediaActors::select('*')->where('Id',$sid)->first()->toArray();

        return view('star.edit',compact('sid','country','role','data'));
    }
    public function delstar(Request $request){
        $sid = $request->input('sid');
        $reg = DB::table('media_actors')->where('Id',$sid)->delete();
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }

    /*上传图片文件*/
    public function uploadStarImg(Request $request)
    {
        $file = $request->file('file');
        if ( $file->isValid()) { //判断文件是否有效
            //$originalName = $file->getClientOriginalName();//获取原文件名
            $ext = $file->getClientOriginalExtension();//扩展名
            //$type = $file->getClientMimeType();//文件类型
            $rootPath='/assets/uploads/image/star/';
            $path = $rootPath.date('Y').'/'.date('md').'/';
            @mkdir($path, 0777, true);
            $filename = $this->msectime() . '.'.$ext; // 毫秒
            $file -> move('.'.$path,$filename);
            $url = $path.$filename;
            return response()->json(array('code'=>0,'msg'=>"上传成功",'data'=>array('src'=>$url)));

        }
        return response()->json(array('code'=>1,'msg'=>"上传失败"));
    }
}
