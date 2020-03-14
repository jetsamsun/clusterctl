<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
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
        $count = StarList::select('sid');
        if($uname){
            $count = $count->where('uname','like','%'.$uname.'%');
        }
        $count = $count->count();
        $dataTmp = StarList::select('sid','uname','pic','screenotype');
        if($uname){
            $dataTmp = $dataTmp->where('uname','like','%'.$uname.'%');
        }
        $dataTmp = $dataTmp->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                if($value['screenotype']){
                    $dataTmp[$key]['screenotype'] = $this->getscreenotype($value['screenotype']);
                }else{
                    $dataTmp[$key]['screenotype'] = "";
                }
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }
    public function addStar(Request $request){
        if($request->isMethod('post')){
            $uname = $request->input('uname');
            $pic = $request->input('imgval');
            $screen = $request->input('screen');

            if($screen) {
                // 排序后  ,拼接 筛选条件+明星
                sort($screen);
                $screen = implode(',', $screen);
            }

            $reg = DB::table('star_list')->insert(array(
                'uname'=>$uname, 'screenotype'=>$screen,'pic'=>$pic
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }
        // 筛选条件
        $screen = ScreenOtype::select('oid','otypename')->where('pid',0)->where('otype',1)->get()->toArray();
        foreach($screen as $key=>$value){
            $son = ScreenOtype::select('oid','otypename')->where('pid',$value['oid'])->get()->toArray();
            $screen[$key]['son'] = $son;
        }

        return view('star.add',compact('screen'));
    }
    public function editstar(Request $request,$sid){
        if($request->isMethod('post')){
            $uname = $request->input('uname');
            $pic = $request->input('imgval');
            $screen = $request->input('screen');

            if($screen) {
                // 排序后  ,拼接 筛选条件+明星
                sort($screen);
                $screen = implode(',', $screen);
            }

            $reg = DB::table('star_list')->where('sid',$sid)->update(array(
                'uname'=>$uname, 'screenotype'=>$screen,'pic'=>$pic
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }
        // 筛选条件
        $screen = ScreenOtype::select('oid','otypename')->where('pid',0)->where('otype',1)->get()->toArray();
        foreach($screen as $key=>$value){
            $son = ScreenOtype::select('oid','otypename')->where('pid',$value['oid'])->get()->toArray();
            $screen[$key]['son'] = $son;
        }
        $data = StarList::select('*')->where('sid',$sid)->first()->toArray();
        $data['screenotype'] = explode(',',$data['screenotype']); // 筛选条件

        return view('star.edit',compact('sid','data','screen'));
    }
    public function delstar(Request $request){
        $sid = $request->input('sid');
        $reg = DB::table('star_list')->where('sid',$sid)->delete();
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
