<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\ListOtype;
use App\Models\MediaCats;
use App\Models\MediaTags;
use App\Models\ScreenOtype;
use App\Models\VideoOtype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OtypeController extends  AdminController{
    public function firstotype(){
        return view('otype.firstotypelist');
    }
    public function getFirstOtypeList(Request $request){

        $dataTmp = ListOtype::select('oid','otypename','otype','title_data','pic_data','urlotype_data','url_data','ios_url_data')->get();

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            //$dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                $dataTmp[$key]['otype'] = $value['otype']==1?"MV":"视频";
                $pics = array();
                if($value['title_data']){
                    $dataTmp[$key]['title_data'] = explode(',',$value["title_data"]);
                    $dataTmp[$key]['pic_data'] = explode(',',$value["pic_data"]);
                    $dataTmp[$key]['urlotype_data'] = explode(',',$value["urlotype_data"]);
                    $dataTmp[$key]['url_data'] = explode(',',$value["url_data"]);
                    $dataTmp[$key]['ios_url_data'] = explode(',',$value["ios_url_data"]);
                }
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','data'=>$dataTmp));
    }

    public function addfirstotype(Request $request){
        if($request->isMethod('post')){
            $otypename = $request->input('otypename');
            $otype = $request->input('otype');
            $title = $request->input('title');
            $pic = $request->input('imgval');
            $urlotype = $request->input('urlotype');
            $url = $request->input('url');
            $ios_url = $request->input('ios_url');
            if($title && $pic){
                $title =implode(',',$title);
                $pic =implode(',',$pic);
                $urlotype =implode(',',$urlotype);
                $url =implode(',',$url);
                $ios_url =implode(',',$ios_url);
            }

            $reg = DB::table('list_otype')->insert(array(
                'otypename'=>$otypename,'otype'=>$otype,'title_data'=>$title,'pic_data'=>$pic,"urlotype_data"=>$urlotype,'url_data'=>$url,"ios_url_data"=>$ios_url
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }

        return view('otype.addfirstotype');
    }
    public function editfirstotype(Request $request,$oid){
        if($request->isMethod('post')){
            $otypename = $request->input('otypename');
            $otype = $request->input('otype');
            $title = $request->input('title');
            $pic = $request->input('imgval');
            $urlotype = $request->input('urlotype');
            $url = $request->input('url');
            $ios_url = $request->input('ios_url');
            if($title && $pic){
                $title =implode(',',$title);
                $pic =implode(',',$pic);
                $urlotype =implode(',',$urlotype);
                $url =implode(',',$url);
                $ios_url =implode(',',$ios_url);
            }

            $reg = DB::table('list_otype')->where('oid',$oid)->update(array(
                'otypename'=>$otypename,'otype'=>$otype,'title_data'=>$title,'pic_data'=>$pic,"urlotype_data"=>$urlotype,'url_data'=>$url,"ios_url_data"=>$ios_url
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }

        // 分类
        $data = ListOtype::select('oid','otypename','otype','pic_data','title_data','urlotype_data','url_data','ios_url_data')
            ->where('oid',$oid)
            ->first()->toArray();
        //if($data['title_data'] && $data['pic_data']){
            $data['title_data'] = explode(',',$data['title_data']);
            $data['pic_data'] = explode(',',$data['pic_data']);
            $data['urlotype_data'] = explode(',',$data['urlotype_data']);
            $data['url_data'] = explode(',',$data['url_data']);
            $data['ios_url_data'] = explode(',',$data['ios_url_data']);
       // }
        //print_r($data);die;
        return view('otype.editfirstotype',compact('oid','data'));
    }
    public function delfirstotype(Request $request){
        $oid = $request->input('oid');
        $reg = DB::table('list_otype')->where('oid',$oid)->delete();
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }

    /**
     *  标签
     */
    public function tags(){
        return view('tags.list');
    }
    public function getTagsList(Request $request){
        $dataTmp = MediaTags::select('*')->get();
        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
        }
        return response()->json(array('code'=>0,'msg'=>'','data'=>$dataTmp));
    }
    public function addtags(Request $request){
        if($request->isMethod('post')){
            $otypename = $request->input('otypename');

            $reg = DB::table('media_tags')->insert(array(
                'Name'=>$otypename
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }

        return view('tags.add');
    }
    public function edittags(Request $request,$oid){
        if($request->isMethod('post')){
            $otypename = $request->input('otypename');

            $reg = DB::table('media_tags')->where('Id',$oid)->update(array(
                'Name'=>$otypename
            ));

            if($reg) {
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            } else {
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }

        // 分类
        $data = MediaTags::select('*')
            ->where('Id',$oid)
            ->first()->toArray();

        return view('tags.edit',compact('oid','data'));
    }
    public function deltags(Request $request){
        $oid = $request->input('oid');
        $reg = DB::table('media_tags')->where('Id',$oid)->delete();
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }

    /**
     *  视频分类
     */
    public function videootype(){
        return view('otype.videootypelist');
    }
    public function getVideoOtypeList(Request $request){
        $catname = $request->input('catname');
        $dataTmp = MediaCats::select('*');
        if($catname){
            $dataTmp = $dataTmp->where('Name','like','%'.$catname.'%');
        }
        $dataTmp = $dataTmp->get();

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            //$dataTmp = $dataTmp['data'];
            $dataTmp = getTree($dataTmp);
            foreach($dataTmp as $k=>$v){
                $parent = MediaCats::where('Id',$v['Pid'])->value('Name');
                $dataTmp[$k]['parent'] = !empty($parent)?$parent:'--';
                $dataTmp[$k]['Name'] = $dataTmp[$k]['html'].$dataTmp[$k]['Name'];
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','data'=>$dataTmp));
    }
    public function addvideootype(Request $request){
        if($request->isMethod('post')){
            $Name = $request->input('Name');
            $Cats = $request->input('Cats');
            $Sort = $request->input('Sort');
            $Mark = $request->input('Mark');

            $reg = DB::table('media_cats')->insert(array(
                'Name'=>$Name,'Pid'=>$Cats,'Sort'=>$Sort,'Mark'=>$Mark
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }
        $tree = MediaCats::get()->toArray();
        $tree = getTree($tree);
        return view('otype.videootypeadd',compact('tree'));
    }
    public function editvideootype(Request $request,$oid){
        if($request->isMethod('post')){
            $Name = $request->input('Name');
            $Cats = $request->input('Cats');
            $Sort = $request->input('Sort');
            $Mark = $request->input('Mark');

            if($oid == $Cats) {
                return response()->json(array('code'=>0,'msg'=>"不可选自己"));
            }

            $reg = DB::table('media_cats')->where('Id',$oid)->update(array(
                'Name'=>$Name,'Pid'=>$Cats,'Sort'=>$Sort,'Mark'=>$Mark
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }


        // 分类
        $data = MediaCats::where('Id',$oid)->first();
        if($data) $data = $data->toArray();

        $tree = MediaCats::get()->toArray();
        $tree = getTree($tree);

        return view('otype.videootypeedit',compact('oid','data','tree'));
    }
    public function delvideootype(Request $request){
        $oid = $request->input('oid');
        $reg = DB::table('media_cats')->where('Id',$oid)->delete();
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }

    /*上传图片文件*/
    public function uploadOtypeImg(Request $request)
    {
        $file = $request->file('file');
        if ( $file->isValid()) { //判断文件是否有效
            //$originalName = $file->getClientOriginalName();//获取原文件名
            $ext = $file->getClientOriginalExtension();//扩展名
            //$type = $file->getClientMimeType();//文件类型
            $rootPath='/assets/uploads/image/otype/';
            $path = $rootPath.date('Y').'/'.date('md').'/';
            @mkdir($path, 0777, true);
            $filename = $this->msectime() . '.'.$ext; // 毫秒
            $file -> move('.'.$path,$filename);
            $url = $path.$filename;
            return response()->json(array('code'=>0,'msg'=>"上传成功",'data'=>array('src'=>$url)));

        }
        return response()->json(array('code'=>1,'msg'=>"上传失败"));
    }

    /**
     *  筛选分类
     *
     *
     */
    public function screenotype(){
        return view('otype.screenotypelist');
    }
    public function getScreenOtypeList(Request $request){

        $dataTmp = ScreenOtype::select('oid','otypename','otype','pid')->where('pid',0)->get();

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            //$dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                if($value['otype']==1){
                    $otype = "明星";
                }elseif($value['otype']==5){
                    $otype  ="排行";
                }elseif($value['otype']==10){
                    $otype = "其他";
                }
                $dataTmp[$key]['otype'] = $otype;
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','data'=>$dataTmp));
    }

    public function addscreenotype(Request $request){
        if($request->isMethod('post')){
            $otypename = $request->input('otypename');
            $otype = $request->input('otype');

            $reg = DB::table('screen_otype')->insert(array(
                'otypename'=>$otypename,'otype'=>$otype,'pid'=>0
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }

        return view('otype.screenotypeadd');
    }
    public function editscreenotype(Request $request,$oid){
        if($request->isMethod('post')){
            $otypename = $request->input('otypename');
            $otype = $request->input('otype');

            $reg = DB::table('screen_otype')->where('oid',$oid)->update(array(
                'otypename'=>$otypename,'otype'=>$otype
            ));
            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }

        // 分类
        $data = ScreenOtype::select('oid','otypename','otype')
            ->where('oid',$oid)
            ->first()->toArray();

        return view('otype.screenotypeedit',compact('oid','data'));
    }
    public function delscreenotype(Request $request){
        $oid = $request->input('oid');
        $reg = DB::table('screen_otype')->where('oid',$oid)->delete();
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }

    public function screendetailotype(Request $request){
        $pid = $request->input('pid');
        return view('otype.screendetailotypelist',compact('pid'));
    }
    public function getScreenDetailOtypeList(Request $request){
        $pid = $request->input('pid');
        $dataTmp = ScreenOtype::select('oid','otypename','otype','pid')->where('pid',$pid)->get();
        $otypename = ScreenOtype::where('oid',$pid)->value('otypename');

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            //$dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                if($value['otype']==1){
                    $otype = $otypename; //"明星";
                }elseif($value['otype']==5){
                    $otype  = $otypename; //"排行";
                }elseif($value['otype']==10){
                    $otype = $otypename; //"其他";
                }
                $dataTmp[$key]['otype'] = $otype;
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','data'=>$dataTmp));
    }
    public function addscreendetailotype(Request $request){
        $pid = $request->input("pid");
        if($request->isMethod('post')){
            $otypename = $request->input('otypename');
            $pid = $request->input('pid');

            $screen = ScreenOtype::select("otype")->where('oid',$pid)->first()->toArray();

            $reg = DB::table('screen_otype')->insert(array(
                'otypename'=>$otypename,'otype'=>$screen["otype"],'pid'=>$pid
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }

        return view('otype.screendetailotypeadd',compact('pid'));
    }
    public function editscreendetailotype(Request $request,$oid){
        $pid = $request->input("pid");
        if($request->isMethod('post')){
            $otypename = $request->input('otypename');

            $reg = DB::table('screen_otype')->where('oid',$oid)->update(array(
                'otypename'=>$otypename
            ));
            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }

        // 分类
        $data = ScreenOtype::select('oid','otypename')
            ->where('oid',$oid)
            ->first()->toArray();

        return view('otype.screendetailotypeedit',compact('oid','data','pid'));
    }
    public function delscreendetailotype(Request $request){
        $oid = $request->input('oid');
        $reg = DB::table('screen_otype')->where('oid',$oid)->delete();
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }
}