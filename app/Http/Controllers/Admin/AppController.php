<?php

namespace App\Http\Controllers\Admin;

use App\Models\AppInfo;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AppController extends AdminController
{
    public function app(){
        return view('app.list');
    }
    public function getAppList(Request $request){

        $dataTmp = AppInfo::select('oid','os','url','pic',"bgpic",'qrcode','text')->get();

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();

            foreach($dataTmp as $key=>$value){
                $dataTmp[$key]['os'] = $value['os']==1?"ios":"andriod";
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','data'=>$dataTmp));
    }

    public function editapp(Request $request,$oid){

        if($request->isMethod('post')){
            $url = $request->input('url');
            $imgval = $request->input('imgval');
            $bgpic = $request->input('bgpic');
            $text = $request->input('text');

            $rootPath = '/assets/qrcodes/';
            @mkdir($rootPath, 0777, true);
            $time = $this->msectime();
            $filename = $time . '.png'; // 毫秒
            $qrcode = $rootPath.$filename;
            QrCode::format('png')->size(100)->margin(1)
                ->generate($url,public_path($qrcode));


            $reg = DB::table('app_info')->where('oid',$oid)->update(array(
                "url"=>$url,"text"=>$text,'qrcode'=>$qrcode,'pic'=>$imgval,"bgpic"=>$bgpic
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }

        $data = AppInfo::select('oid','url',"qrcode",'pic','bgpic',"text")->where('oid',$oid)->first()->toArray();

        return view('app.edit',compact('oid','data'));
    }
    /*上传图片文件*/
    public function uploadImg(Request $request)
    {
        $file = $request->file('file');
        if ( $file->isValid()) { //判断文件是否有效
            //$originalName = $file->getClientOriginalName();//获取原文件名
            $ext = $file->getClientOriginalExtension();//扩展名
            //$type = $file->getClientMimeType();//文件类型
            $rootPath='/assets/uploads/image/qrcode/';
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
