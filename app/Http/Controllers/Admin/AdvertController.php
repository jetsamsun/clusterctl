<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\AdvertList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvertController extends AdminController
{
    public function advert(Request $request)
    {
        return view('advert.advertList');
    }
    public function getAdvertList(Request $request){
        $title = $request->input('title');
        $dataTmp = AdvertList::select('advertId','title','otype','pic','url');
        if($title){
            $dataTmp = $dataTmp->where('title','like','%'.$title.'%');
        }
        $dataTmp = $dataTmp->get();

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            //$dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                $dataTmp[$key]['otype'] = $value['otype']==1?"播放器":"播放列表";
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','data'=>$dataTmp));
    }
    public function addAdvert(Request $request){
        if($request->isMethod('post')){
            $title = $request->input('title');
            $otype = $request->input('otype');
            $pic = $request->input('imgval');
            $url = $request->input('url');

            $reg = DB::table('advert_list')->insert(array(
                'title'=>$title,'otype'=>$otype,'pic'=>$pic,'url'=>$url
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }
        return view('advert.addadvert');
    }
    /*上传图片文件*/
    public function uploadImg(Request $request)
    {
        $file = $request->file('file');
        if ( $file->isValid()) { //判断文件是否有效
            //$originalName = $file->getClientOriginalName();//获取原文件名
            $ext = $file->getClientOriginalExtension();//扩展名
            //$type = $file->getClientMimeType();//文件类型
            $rootPath='/assets/uploads/image/advert/';
            $path = $rootPath.date('Y').'/'.date('md').'/';
            @mkdir($path, 0777, true);
            $filename = $this->msectime() . '.'.$ext; // 毫秒
            $file -> move('.'.$path,$filename);
            $url = $path.$filename;
            return response()->json(array('code'=>0,'msg'=>"上传成功",'data'=>array('src'=>$url)));

        }
        return response()->json(array('code'=>1,'msg'=>"上传失败"));
    }
    public function editAdvert(Request $request,$advertId){
        if($request->isMethod('post')){
            $title = $request->input('title');
            $otype = $request->input('otype');
            $pic = $request->input('imgval');
            $url = $request->input('url');
            $reg = DB::table('advert_list')->where('advertId',$advertId)->update(array(
                'title'=>$title,'otype'=>$otype,'pic'=>$pic,'url'=>$url
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }

        // 分类
        $data = AdvertList::select('advertId','title','otype','pic','url')
            ->where('advertId',$advertId)
            ->first()->toArray();

        return view('advert.editAdvert',compact('advertId','data'));
    }
    public function delAdvert(Request $request){
        $advertId = $request->input('advertId');
        $reg = DB::table('advert_list')->where('advertId',$advertId)->delete();
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }
}
