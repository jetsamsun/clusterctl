<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\ListOtype;
use App\Models\ScreenOtype;
use App\Models\StarList;
use App\Models\VideoAdminLog;
use App\Models\VideoList;
use App\Models\VideoOtype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Video2Controller extends AdminController
{
    public function video(){

        $data = VideoAdminLog::select('log_id')->get()->toArray();
        return view('video.list',compact('data'));
    }
    public function getVideoList(Request $request){
        $limit = $request->input('limit');
        $title = $request->input('title');
        $count = VideoList::select('vid','title','pic','otype','firstotype',
            'secondotype','screenotype','star','is_free','createtime');
        if($title){
            $count = $count->where("title",'like','%'.$title.'%');
        }
        $count = $count->where('is_visible',1)->count();

        $dataTmp = VideoList::select('vid','title','pic','otype','firstotype',
            'secondotype','screenotype','star','is_free','hotcount','videotime','createtime');
        if($title){
            $dataTmp = $dataTmp->where("title",'like','%'.$title.'%');
        }
        $dataTmp = $dataTmp->where('is_visible',1)->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                $dataTmp[$key]['otype'] = $this->getotype($value['otype']);
                $dataTmp[$key]['firstotype'] = $this->getfirstotype($value['firstotype']);
                $dataTmp[$key]['secondotype'] = $this->getsecondotype($value['secondotype']);
                $dataTmp[$key]['screenotype'] = $this->getscreenotype($value['screenotype']);
                $dataTmp[$key]['star'] = $this->getStarName($value['star']);
                //$dataTmp[$key]['videotime'] = gmstrftime('%H:%M:%S',$value["videotime"]);
                $dataTmp[$key]['createtime'] = date('Y-m-d H:i:s',$value['createtime']);
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }

    public function addVideo(Request $request){
        if($request->isMethod('post')){
            $title = $request->input('title');
            $pic = $request->input('imgval');
            $url = $request->input('filesval');
            $otype = $request->input('otype');
            $firstotype = $request->input('otype2');
            $secondotype = $request->input('otype3');
            $secondbestotype = $request->input('secondbestotype');
            $hotcount = $request->input('hotcount');
            $videotime = $request->input('videotime');
            $screen = $request->input('screen');
            $star = $request->input('star');
            $is_free = empty($request->input('is_free'))?0:$request->input('is_free');
            $content = $request->input('content');
            $play_urls = $request->input('play_urls');
            $download_urls = $request->input('download_urls');
            if( !$otype || !$firstotype || !$secondotype || !$secondbestotype || !$screen || !$star){
                return response()->json(array('code'=>0,'msg'=>"分类/条件/明星是必填项"));
            }
            if(count($secondotype)>15){
                return response()->json(array('code'=>0,'msg'=>"新闻分类过多"));
            }
            $otype = implode(',',$otype);
            $firstotype = implode(',',$firstotype);
            $secondotype = implode(',',$secondotype);
            $secondbestotype = implode(',',$secondbestotype);
            // 排序后  ,拼接 筛选条件+明星
            sort($screen);
            $screen = implode(',',$screen);
            sort($star);
            $star = implode(',',$star);
            $videotime = implode(':',$videotime);
            if($pic){
                if(substr($pic,0,7)!="http://"){
                    $pic = $this->urlPic().$pic;
                }
            }
            $reg = DB::table('video_list')->insert(array(
                'title'=>$title,'otype'=>$otype,'firstotype'=>$firstotype,'secondotype'=>$secondotype,
                'secondbestotype'=>$secondbestotype,'hotcount'=>$hotcount,'videotime'=>$videotime,
                'screenotype'=>$screen,'star'=>$star,'is_free'=>$is_free,'content'=>$content,
                'pic'=>$pic,'url'=>$url,'play_urls'=>$play_urls,'download_urls'=>$download_urls,
                'createtime'=>strtotime(date('Y-m-d H:i:s')),'is_visible'=>1
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }
        // 导航分类
        $firstotype = ListOtype::select('oid','otypename')->get()->toArray();
        // 视频分类
        $secondotype = VideoOtype::select('oid','otypename')->get()->toArray();
        // 筛选条件
        $screen = ScreenOtype::select('oid','otypename')->where('pid',0)->where('otype','!=',1)->get()->toArray();
        foreach($screen as $key=>$value){
            $son = ScreenOtype::select('oid','otypename')->where('pid',$value['oid'])->get()->toArray();
            $screen[$key]['son'] = $son;
        }
        // 明星列表
        $star =  StarList::select('sid','uname')->get()->toArray();

        return view('video.add',compact('firstotype','secondotype','star','screen'));
    }
    public function editvideo(Request $request,$vid){
        if($request->isMethod('post')){
            $title = $request->input('title');
            $otype = $request->input('otype');
            $pic = $request->input('imgval');
            $imgval_old = $request->input('imgval_old');
            $url = $request->input('filesval');
            $filesval_old = $request->input('filesval_old');
            $firstotype = $request->input('otype2');
            $secondotype = $request->input('otype3');
            $secondbestotype = $request->input('secondbestotype');
            $hotcount = $request->input('hotcount');
            $videotime = $request->input('videotime');
            $screen = $request->input('screen');
            $star = $request->input('star');
            $is_free = empty($request->input('is_free'))?0:$request->input('is_free');
            $content = $request->input('content');
            $play_urls = $request->input('play_urls');
            $download_urls = $request->input('download_urls');

            if( !$otype || !$firstotype || !$secondotype || !$secondbestotype || !$screen || !$star){
                return response()->json(array('code'=>0,'msg'=>"分类/条件/明星是必填项"));
            }
            if(count($secondotype)>15){
                return response()->json(array('code'=>0,'msg'=>"新闻分类过多"));
            }
            $otype = implode(',',$otype);
            $firstotype = implode(',',$firstotype);
            $secondotype = implode(',',$secondotype);
            $secondbestotype = implode(',',$secondbestotype);
            if($pic){
                if(substr($pic,0,7)!="http://"){
                    $pic = $this->urlPic().$pic;
                }
            }
            // 排序后  ,拼接 筛选条件+明星
            sort($screen);
            $screen = implode(',',$screen);
            sort($star);
            $star = implode(',',$star);
            $videotime = implode(':',$videotime);

            $reg = DB::table('video_list')->where('vid',$vid)->update(array(
                'title'=>$title,'otype'=>$otype,'firstotype'=>$firstotype,'secondotype'=>$secondotype,
                'secondbestotype'=>$secondbestotype,'hotcount'=>$hotcount,'videotime'=>$videotime,
                'screenotype'=>$screen,'star'=>$star,'is_free'=>$is_free,'content'=>$content,
                'pic'=>$pic,'url'=>$url,'play_urls'=>$play_urls,'download_urls'=>$download_urls,
            ));
            if($pic != $imgval_old){
                if( file_exists('.'.$imgval_old) ){
                    unlink('.'.$imgval_old);
                }
            }
            if($url != $filesval_old){
                if( file_exists('.'.$imgval_old) ){
                    unlink('.'.$filesval_old);
                }
            }
            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }
        // 导航分类
        $firstotype = ListOtype::select('oid','otypename')->get()->toArray();
        // 视频分类
        $secondotype = VideoOtype::select('oid','otypename')->get()->toArray();
        // 筛选条件
        $screen = ScreenOtype::select('oid','otypename')->where('pid',0)->where('otype','!=',1)->get()->toArray();
        foreach($screen as $key=>$value){
            $son = ScreenOtype::select('oid','otypename')->where('pid',$value['oid'])->get()->toArray();
            $screen[$key]['son'] = $son;
        }
        // 明星列表
        $star =  StarList::select('sid','uname')->get()->toArray();
        $data = VideoList::select('*')->where('vid',$vid)->first()->toArray();

        $data['otype'] = explode(',',$data['otype']);
        $data['firstotype'] = explode(',',$data['firstotype']);
        $data['secondotype'] = explode(',',$data['secondotype']);
        $data['secondbestotype'] = explode(',',$data['secondbestotype']);
        $data['screenotype'] = explode(',',$data['screenotype']); // 筛选条件
        $data['star'] = explode(',',$data['star']);           // 明星
        $data['videotime'] = explode(':',$data['videotime']);

        return view('video.edit',compact('vid','data','star','screen','firstotype','secondotype'));
    }
    public function delvideo(Request $request){
        $vid = $request->input('vid');
        $data = VideoList::select('*')->where('vid',$vid)->first()->toArray();
        try {
            if (file_exists('.' . $data['pic'])) {
                unlink('.' . $data['pic']);
            }
            if (file_exists('.' . $data['url'])) {
                unlink('.' . $data['url']);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        $reg = DB::table('video_list')->where('vid',$vid)->update(array('is_visible'=>0));
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }
    public function videofree(Request $request){
        $vid = $request->input('vid');
        $is_free = $request->input('is_free');

        $reg = DB::table('video_list')->where('vid',$vid)->update(array('is_free'=>$is_free));
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }
    public function clickfree(Request $request){
        $sta = $request->input('sta');
        $reg1 = '';
        $reg2 = '';
        if($sta == 1){
            $video = VideoList::select('vid')->where('is_free',0)->get();
            if($video){
                $video = $video->toArray();
                foreach($video as $value){
                    $reg1 = DB::table('video_list')->where('vid',$value['vid'])->update(array('is_free'=>1));
                    $reg2 = DB::table('video_admin_log')->insert(array('video_id'=>$value['vid']));
                }
                if($reg1 && $reg2){
                    return response()->json(array('status'=>1,'msg'=>'一键限免成功'));
                }else{
                    return response()->json(array('status'=>0,'msg'=>'一键限免失败'));
                }
            }
        }else{
            $logs = DB::table('video_admin_log')->select('log_id','video_id')->get();
            if($logs){
                $logs = $logs->toArray();
                foreach($logs as $value){
                    $reg1 = DB::table('video_list')->where('vid',$value->video_id)->update(array('is_free'=>0));
                    $reg2 = DB::table('video_admin_log')->where('log_id',$value->log_id)->delete();
                }
                if($reg1 && $reg2){
                    return response()->json(array('status'=>1,'msg'=>'取消限免成功'));
                }else{
                    return response()->json(array('status'=>0,'msg'=>'取消限免失败'));
                }
            }
        }
    }
    public function getVideoOtype(Request $request){
        $otype = $request->input('otype');
        $data = ListOtype::select('oid','otypename')->where('otype',$otype)->get()->toArray();

        return $data;
    }
    public function getVideoOtype3(Request $request){
        $otype = $request->input('otype');
        $data = VideoOtype::select('oid','otypename')->where('otype',$otype)->get()->toArray();

        return $data;
    }

    /*上传图片文件*/
    public function uploadVideoImg(Request $request)
    {
        $file = $request->file('file');
        if ( $file->isValid()) { //判断文件是否有效
            //$originalName = $file->getClientOriginalName();//获取原文件名
            $ext = $file->getClientOriginalExtension();//扩展名
            //$type = $file->getClientMimeType();//文件类型
            $rootPath='/assets/uploads/image/video/';
            $path = $rootPath.date('Y').'/'.date('md').'/';
            @mkdir($path, 0777, true);
            $filename = $this->msectime() . '.'.$ext; // 毫秒
            $file -> move('.'.$path,$filename);
            $url = $path.$filename;
            return response()->json(array('code'=>0,'msg'=>"上传成功",'data'=>array('src'=>$url)));

        }
        return response()->json(array('code'=>1,'msg'=>"上传失败"));
    }
    // 上传文件
    public function uploadVideoFile(Request $request)
    {
        $file = $request->file('file');

        if ($file->isValid()) { //判断文件是否有效
            $totalPieces = $_POST['totalPieces'];  //上传文件切片总数

            $index = $_POST['index'];  //上传文件当前切片
            $progress = round(($index/$totalPieces),2)*100;

            if($index == ($totalPieces - 1)){

                $progress = 100;  //进度条
            }

            $originalName = $file->getClientOriginalName();//获取原文件名
            $ext = $file->getClientOriginalExtension();//扩展名
            //$type = $file->getClientMimeType();//文件类型
            $path = '/assets/uploads/files/video/';
            $realPath = $file->getRealPath();   //临时文件的绝对路径
            @mkdir($path, 0777, true);
            $time = $this->msectime();

            if($totalPieces ==1){
                $filename = $time . '.' . $ext; // 毫秒
                $bool = Storage::disk('uploads')->put($filename, file_get_contents($realPath));
            }else{
                //分片临时文件名
                $filename = md5($originalName).'-'.($index+1).'.tmp';
                //保存临时文件
                $bool = Storage::disk('tmp')->put($filename, file_get_contents($realPath));
                //当分片上传完时 合并
                if(($index+1) == $totalPieces){
                    //最后合成后的名字及路径
                    $u  = 'assets/uploads/files/video/';
                    $filename = $time . '.' . $ext; // 毫秒
                    $filenames = $u.$filename;
                    //打开文件
                    $fp = fopen($filenames,"ab");
                    //循环读取临时文件，写入最终文件
                    for($i=0;$i<$totalPieces;$i++){
                        Log::info($i);
                        //临时文件路径及名称
                        $tmp_files = 'assets/uploads/files/video/tmp/'.md5($originalName).'-'.($i+1).'.tmp';
                        //打开临时文件
                        $handle = fopen($tmp_files,"rb");
                        //读取临时文件 写入最终文件
                        fwrite($fp,fread($handle,filesize($tmp_files)));
                        //关闭句柄 不关闭删除文件会出现没有权限
                        fclose($handle);
                        //删除临时文件
                        unlink($tmp_files);
                    }
                    //关闭句柄
                    fclose($fp);
                }
            }



            $url = $path.$filename;
            return response()->json(array('code' => 0,'progress'=>$progress, 'msg' => "上传成功",'data'=>array('src'=>$url)));
        }
        return response()->json(array('code' => 1, 'progress'=>0,'msg' => "上传失败"));
    }
}
