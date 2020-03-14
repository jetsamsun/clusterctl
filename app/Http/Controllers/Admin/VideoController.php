<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use app\Libs\Xyz;
use App\Models\Config;
use App\Models\ListOtype;
use App\Models\ScreenOtype;
use App\Models\SiteRate;
use App\Models\StarList;
use App\Models\TransLog;
use App\Models\VideoAdminLog;
use App\Models\VideoList;
use App\Models\VideoOtype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;


class VideoController extends AdminController
{
    function __construct() {
        set_time_limit(0);

        $cfgs = Config::get();
        foreach ($cfgs as $v) {
            $this->cfgs[$v['name']] = $v['value'];
        }

        $this->uploaddir = PUBLIC_PATH.$this->cfgs['upload_dir'].'/';
        $this->productdir = PUBLIC_PATH.$this->cfgs['video_dir'].'/';
        $this->tmpdir = PUBLIC_PATH.'/video/tmp/';
    }
    public function video() {
        $conname = $this->scanvideo();  //扫描文件夹

        foreach ($conname as $v) {  //遍历文件
            $arr = explode('.', $v);
            $ret = VideoList::where('title',$arr[0])->first();

            if(!$ret) {
                $insert['title'] = $arr[0];
                $insert['url'] = str_replace(PUBLIC_PATH, '', $this->uploaddir.$v);
                $insert['createtime'] = time();
                $insert['name'] =  mt_rand(10101,99999).time().mt_rand(101,999).'.'.$arr[1];

                $xyz = new Xyz();
                $videodir = PUBLIC_PATH.$insert['url'];
                $videomsg = $xyz->format($videodir);  //源文件数据
                $insert['size'] = $videomsg['size'];   // '视频大小',
                $insert['width'] = $videomsg['width'];
                $insert['height'] = $videomsg['height'];
                $insert['bit_rate'] = $videomsg['bit_rate'];  // '比特率',
                $insert['duration'] = $videomsg['duration'];   // '视频时长',
                $insert['audio'] = $videomsg['audio'];
                $insert['vcode'] = $videomsg['video'];
                $insert['ext'] = $videomsg['ext'];
                $insert['dis_ratio'] = $videomsg['dis_ratio'];
                $insert['acode'] = $videomsg['audio'];

                if(!$bool = VideoList::insertGetId($insert)) {
                    dd('扫描插入信息失败！');
                }
            }
        }

        $data = VideoAdminLog::select('log_id')->get()->toArray();
        return view('video.list',compact('data'));
    }
    public function getVideoList(Request $request){
        $limit = $request->input('limit');
        $title = $request->input('title');
        $count = VideoList::select('*');
        if($title){
            $count = $count->where("title",'like','%'.$title.'%');
        }
        $count = $count->where('is_visible',1)->count();

        $dataTmp = VideoList::select('*');
        if($title){
            $dataTmp = $dataTmp->where("title",'like','%'.$title.'%');
        }
        $dataTmp = $dataTmp->where('is_visible',1)->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                if(!empty($value['size']) ) {
                    $dataTmp[$key]['src_bit'] = format_bytes($value['size']) ;
                    $dataTmp[$key]['src_rate'] = round((int)$value['bit_rate']/1024).'kbps' ;
                    $dataTmp[$key]['src_size'] = $value['width'].'x'.$value['height'];
                    $dataTmp[$key]['videotime'] = format_time($value['duration']);
                    $dataTmp[$key]['vcode'] = $value['vcode'];
                    $dataTmp[$key]['acode'] = $value['audio'];
                    $dataTmp[$key]['ext'] = $value['ext'];
                    $dataTmp[$key]['dis_ratio'] = $value['dis_ratio'];

                    if($value['status']==0) {
                        $dataTmp[$key]['status_txt'] = '待转码';
                    } else if($value['status']==1) {
                        $dataTmp[$key]['status_txt'] = '已转码';
                    } else {
                        $dataTmp[$key]['status_txt'] = '转码异常';
                    }
                } else {
//                    $xyz = new Xyz();
//                    $videodir = PUBLIC_PATH.$value['url'];
//                    $videomsg = $xyz->format($videodir);  //源文件数据
//                    $dataTmp[$key]['src_bit'] = format_bytes($videomsg['size']) ;
//                    $dataTmp[$key]['src_rate'] = round((int)$videomsg['bit_rate']/1024).'kbps' ;
//                    $dataTmp[$key]['src_size'] = $videomsg['width'].'x'.$videomsg['height'];
//                    $dataTmp[$key]['videotime'] = format_time($videomsg['duration']);
//                    $dataTmp[$key]['vcode'] = $videomsg['video'];
//                    $dataTmp[$key]['acode'] = $videomsg['audio'];
//                    $dataTmp[$key]['ext'] = $videomsg['ext'];
//                    $dataTmp[$key]['dis_ratio'] = $videomsg['dis_ratio'];
                }

                $dataTmp[$key]['otype'] = $this->getotype($value['otype']);
                $dataTmp[$key]['firstotype'] = $this->getfirstotype($value['firstotype']);
                $dataTmp[$key]['secondotype'] = $this->getsecondotype($value['secondotype']);
                $dataTmp[$key]['screenotype'] = $this->getscreenotype($value['screenotype']);
                $dataTmp[$key]['star'] = $this->getStarName($value['star']);
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
            $designation = $request->input('designation');
            $imdb = $request->input('imdb');
            $score = $request->input('score');
            $screen = $request->input('screen');
            $star = $request->input('star');
            $content = $request->input('content');
            $play_urls = $request->input('play_urls');
            $download_urls = $request->input('download_urls');

            if(!empty($otype)) $otype = implode(',',$otype);
            if(!empty($firstotype)) $firstotype = implode(',',$firstotype);
            if(!empty($secondotype)) $secondotype = implode(',',$secondotype);
            if(!empty($secondbestotype)) $secondbestotype = implode(',',$secondbestotype);

            // 排序后  ,拼接 筛选条件+明星
            if(!empty($screen)) {
                sort($screen);
                $screen = implode(',', $screen);
            }
            if(!empty($star)) {
                sort($star);
                $star = implode(',', $star);
            }

            if($pic){
                if(substr($pic,0,7)!="http://"){
                    $pic = $this->urlPic().$pic;
                }
            }

            $reg = DB::table('video_list')->insert(array(
                'title'=>$title,'otype'=>$otype,'firstotype'=>$firstotype,'secondotype'=>$secondotype,
                'secondbestotype'=>$secondbestotype,'hotcount'=>$hotcount,
                'designation'=>$designation,'imdb'=>$imdb,'score'=>$score,
                'screenotype'=>$screen,'star'=>$star,'content'=>$content,
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
        $secondotype = VideoOtype::select('*')->get()->toArray();
        $tree = getTree($secondotype);
        // 筛选条件
        $screen = ScreenOtype::select('oid','otypename')->where('pid',0)->where('otype','!=',1)->get()->toArray();
        foreach($screen as $key=>$value){
            $son = ScreenOtype::select('oid','otypename')->where('pid',$value['oid'])->get()->toArray();
            $screen[$key]['son'] = $son;
        }
        // 明星列表
        $star =  StarList::select('sid','uname')->get()->toArray();

        return view('video.add',compact('firstotype','secondotype','star','screen','tree'));
    }
    public function editvideo(Request $request,$vid){
        if($request->isMethod('post')){
            $title = $request->input('title');
            $otype = $request->input('otype');
            $pic = $request->input('imgdemo');
            $gif = $request->input('gifdemo');
            $imgval_old = $request->input('imgval_old');
            //$url = $request->input('filesval');
            $filesval_old = $request->input('filesval_old');
            $firstotype = $request->input('otype2');
            $secondotype = $request->input('otype3');
            $secondbestotype = $request->input('secondbestotype');
            $hotcount = $request->input('hotcount');
            $designation = $request->input('designation');
            $imdb = $request->input('imdb');
            $score = $request->input('score');
            $screen = $request->input('screen');
            $star = $request->input('star');
            $content = $request->input('content');
            $play_urls = $request->input('play_urls');
            $download_urls = $request->input('download_urls');


            if(!empty($otype) && is_array($otype)) $otype = implode(',',$otype);
            if(!empty($firstotype) && is_array($firstotype))  $firstotype = implode(',',$firstotype);
            if(!empty($secondotype) && is_array($secondotype))  $secondotype = implode(',',$secondotype);
            if(!empty($secondbestotype) && is_array($secondbestotype))  $secondbestotype = implode(',',$secondbestotype);


            if($pic){
                if(substr($pic,0,7)!="http://"){
                    $pic = $this->urlPic().$pic;
                }
            }
            $pic = str_replace($this->cfgs['site_url'], '', $pic);
            $gif = str_replace($this->cfgs['site_url'], '', $gif);


            // 排序后  ,拼接 筛选条件+明星
            if(!empty($screen) && is_array($screen)) {
                sort($screen);
                $screen = implode(',', $screen);
            }
            if(!empty($star) && is_array($star)) {
                sort($star);
                $star = implode(',', $star);
            }


            $reg = DB::table('video_list')->where('vid',$vid)->update(array(
                'title'=>$title,'otype'=>$otype,'firstotype'=>$firstotype,'secondotype'=>$secondotype,
                'secondbestotype'=>$secondbestotype,'hotcount'=>$hotcount,
                'designation'=>$designation,'imdb'=>$imdb,'score'=>$score,
                'screenotype'=>$screen,'star'=>$star,'content'=>$content,
                'pic'=>$pic,'gif'=>$gif,'play_urls'=>$play_urls,'download_urls'=>$download_urls,
            ));
//            if($pic != $imgval_old){
//                if( file_exists('.'.$imgval_old) ){
//                    unlink('.'.$imgval_old);
//                }
//            }
//            if($url != $filesval_old){
//                if( file_exists('.'.$imgval_old) ){
//                    unlink('.'.$filesval_old);
//                }
//            }
            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }
        // 导航分类
        $firstotype = ListOtype::select('oid','otypename')->get()->toArray();
        // 视频分类
        $secondotype = VideoOtype::select('*')->get()->toArray();
        $tree = getTree($secondotype);
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


        if($data['m3u8']) {
            $str = array();
            $m3u8 = json_decode($data['m3u8']);
            $siteurl = Config::where('name','site_url')->value('value');
            foreach ($m3u8 as $k=>$v) {
                $str[] = $siteurl . $v;
            }
            $data['m3u8'] = $str;
        }

        $cfgs = $this->cfgs;

        return view('video.edit',compact('vid','data','star','screen','firstotype','secondotype','tree','cfgs'));
    }
    public function transcode(Request $request,$vid){
        if($request->isMethod('post')){
            //检测清理日志
            $logs = TransLog::get();
            foreach ($logs as $log) {
                if(strpos($log['msg'],'转码完毕')!==false) {
                    TransLog::where('code',$log['code'])->delete();
                }
            }

            $idarr = explode('_', $vid);
            foreach ($idarr as $v) {
                $post['ids'] = $v;
                $post['size_rate'] = json_encode($_POST['siterate']);

                $url = VideoList::where('vid',$v)->value('url');
                if(count($idarr)==1) {
                    if (!file_exists('.' . $url)) {
                        return response()->json(array('code'=>0,'msg'=>"源文件已删除"));
                    }
                } else {
                    if (!file_exists('.' . $url)) {
                        continue ;  //文件已删除，继续
                    }
                }


                //检测当前对应日志
                $last = 0;
                $nickname = VideoList::where('vid',$v)->value('nickname');
                if($nickname) {
                    $randsring = str_replace('.mp4', '', $nickname);
                    $logs = TransLog::where('code',$randsring)->get();
                    foreach ($logs as $log) {
                        $last = $log['time'];
                    }
                    if(time()-$last>6*3600) {  //产出超过3小时的发送异常而没有成功的记录
                        TransLog::where('code',$randsring)->delete();
                    } else {
                        return response()->json(array('code'=>-1,'msg'=>"[".$v."]后台转码中"));
                    }
                } else {  //没有转过或还没生成
                    $logs = TransLog::where('vid',$v)->first();
                    if($logs) {
                        return response()->json(array('code'=>-1,'msg'=>"[".$v."]后台转码中"));
                    }
                }


                $url = url('api/v1/video/execute');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                $res = curl_exec($ch);
                curl_close($ch);
            }
            return response()->json(array('code'=>1,'msg'=>"已添加后台转码"));
        }

        $siterate = Config::where('name','trans_default_size')->get()->toArray(); $siterate = $siterate[0];
        return view('video.transcode',compact('vid','siterate'));
    }
    public function delvideo(Request $request){
        $vid = $request->input('vid');
        $data = VideoList::select('*')->where('vid',$vid)->first()->toArray();
        try {
            if(!empty($data['video'])) {  //删除目录
                $arr = explode('/', $data['video']);
                $path = './'.$arr[1].'/'.$arr[2].'/'.$arr[3].'/'.$arr[4];
                deldir($path);
                @rmdir($path);
            }
            if (file_exists('.' . $data['url'])) {  //删除源文件
                unlink('.' . $data['url']);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        $reg = DB::table('video_list')->where('vid',$vid)->delete();
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

    public function transqueue(Request $request) {
        return view('video.transqueue');
    }
    public function getTransLog(Request $request) {
        $limit = $request->input('limit');
        $title = $request->input('title');

        $dataTmp = TransLog::select('*');
        if($title){
            $dataTmp = $dataTmp->where("msg",'like','%'.$title.'%');
        }
        $dataTmp = $dataTmp->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            $arr = [];
            foreach($dataTmp as $k=>$v){
                $arr[$v['code']][] = $v;
            }

            $dataTmp = [];
            $curs = [];
            foreach($arr as $k=>$v){
                $arr3=[];
                $index = 0;

                $st = 0;
                $et = 0;
                $cur_state = '';
                foreach($v as $vv){
                    $data = json_decode($vv['data']);

                    $code = $vv['code'];
                    $time = $vv['time'];
                    $msg = $vv['msg'];
                    $ids = $data->ids;
                    $file = $data->file;

                    if($index==0) {
                        $arr3['src_file'] = $file;
                        $arr3['dir_file'] = $code.'.mp4';
                        $arr3['code'] = $code;
                        $arr3['ids'] = $ids;

                        $video = VideoList::where('vid',$ids)->value('video');
                        if(!empty($video)) {
                            $arr3['date'] = explode('/', $video)[3];
                        } else {
                            $arr3['date'] = date('Ymd');
                        }


                        $rastr = '';
                        $sistr = '';
                        $rasi = '';
                        $cnt = 0;
                        foreach ($data->size_rate as $rk=>$ra) {
                            $ras = explode('：', $ra);
                            if($cnt==0) {
                                $rastr = $ras[0];
                                $sistr = $ras[1];
                                $rasi = $ra;
                            } else {
                                $rastr = $rastr.'|'.$ras[0].'p';
                                $sistr = $sistr.'|'.$ras[1];
                                $rasi = $rasi.'|'.$ra;
                            }
                            $cnt++;
                        }
                        $arr3['dir_rate'] = $rastr;
                        $arr3['dir_size'] = $sistr;

                        $st = $time;
                    }

                    if(isset($data->size_rate)) {
                        $cur_state = $msg;
                    } else {
                        $cur_state = $data->rate.'p '.$msg;
                    }
                    $curs[]=$file.' '.$cur_state;

                    $et = $time;
                    $index++;
                }

                $arr3['dir_path'] = str_replace(ROOR_PATH, '', $this->productdir).$arr3['date'].'/'.$code.'/';

                $arr3['starttime'] = date('Y-m-d H:i:s',$st);
                if($et == $st) {
                    $arr3['usetime'] = format_time(time() - $st);
                } else {
                    $arr3['usetime'] = format_time($et - $st);
                }
                $arr3['cur_state'] = $cur_state;

                if(isset($arr3['ids'])) {
                    $row = VideoList::where('vid',$arr3['ids'])->get()->toArray();
                    $row = $row[0];
                    
                    if(!empty($row['size']) ) {
                        $arr3['src_bit'] = format_bytes($row['size']) ;
                        $arr3['src_rate'] = round((int)$row['bit_rate']/1024).'kbps' ;
                        $arr3['src_size'] = $row['width'].'x'.$row['height'];
                    } else {
//                        $xyz = new Xyz();
//                        $videodir = PUBLIC_PATH.$row['url'];
//                        $videomsg = $xyz->format($videodir);  //源文件数据
//                        $arr3['src_bit'] = format_bytes($videomsg['size']) ;
//                        $arr3['src_rate'] = round((int)$videomsg['bit_rate']/1024).'kbps' ;
//                        $arr3['src_size'] = $videomsg['width'].'x'.$videomsg['height'];
                    }
                }

                $dataTmp[] = $arr3;
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','count'=>count($dataTmp),'data'=>$dataTmp));
    }
    public function translogs($code) {
        $logs = TransLog::where('code',$code)->get();
        $arr = [];
        foreach($logs as $v){
            $data = json_decode($v['data']);
            $code = $v['code'];
            $time = $v['time'];

            if(isset($data->size_rate)) {
                $rasi='';
                $cnt=0;
                foreach ($data->size_rate as $rk=>$ra) {
                    $ra = str_replace('：', ':', $ra);
                    if($cnt==0) {
                        $rasi = $ra;
                    } else {
                        $rasi = $rasi.'|'.$ra;
                    }
                    $cnt++;
                }
                $str = '['.date('Y-m-d H:i:s',$time) .'] '.$rasi.' '.$v['msg'];
            } else if(isset($data->rate)) {
                $str = '['.date('Y-m-d H:i:s',$time) .'] '.$data->rate.'p '.$v['msg'];
            } else {
                $str = '['.date('Y-m-d H:i:s',$time) .'] '.$v['msg'];
            }

            $arr[] = $str;
        }
        $logs = $arr;
        return view('video.translogs',compact('code','logs'));
    }
    public function uploadlist()
    {
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $videos = $this->scanvideo() ;
            $this->view->assign('videos', $videos);
            $result = array("total" => count($videos), "rows" => $videos);
            return json($result);
        }
        return $this->view->fetch();
    }
    public function scanvideo()
    {
        $absuploaddir = $this->uploaddir;
        $filename = scandir($absuploaddir);
        $conname = array();

        foreach($filename as $k=>$v){
            if($v=="." || $v=="..") continue;
            $conname[] = $v;
        }
        return $conname;
    }
    public function cutjpg()
    {
        $src_path = $_POST['src_path'];
        $time = $_POST['time'];

        $src_path_new = PUBLIC_PATH.$src_path;
        $obj_path = dirname($src_path_new).'/cover.jpg';
        $xyz = new Xyz();
        $re = $xyz->vodtojpg($src_path_new, $obj_path, $time);
        if ($re == 'ok') {
            return response()->json(array('code'=>1,'msg'=>'截图成功','data'=>str_replace(PUBLIC_PATH, '', $obj_path)));
        }
        return response()->json(array('code'=>0,'msg'=>'截图失败'));
    }
    public function vodtogif() {
        $xyz = new Xyz();
        $src_path = PUBLIC_PATH.$_POST['src_path'];
        $stime = $_POST['time'];
        $etime = $this->cfgs['shot_gif_space'];
        $obj_path = dirname($src_path).'/cover.gif';
        $re = $xyz->vodtogif($src_path,$obj_path,$stime,$etime);
        if ($re == 'ok') {
            return response()->json(array('code'=>1,'msg'=>'生成gif动图成功','data'=>str_replace(PUBLIC_PATH, '', $obj_path)));
        }
        return response()->json(array('code'=>0,'msg'=>'生成gif动图失败'));
    }
    public function setmainjpg($src_path, $image_path)
    {
        $this->model = model('app\common\model\Category');
        $re = $this->model->where('video',$src_path)->setField('image', $image_path);
        if ($re) {
            $ret['flag'] = 1;
            $ret['msg'] = '设置成功';
            $ret['info'] = $image_path;
        } else {
            $ret['flag'] = 0;
            $ret['msg'] = '设置失败';
        }
        return json_encode($ret);
    }
    public function delcutimage($src_path, $image_path)
    {
        $this->model = model('app\common\model\Category');
        $row = $this->model->where('video',$src_path)->find();
        $cut_images = str_replace(','.$image_path, '', ','.$row->cut_images);
        $cut_images = trim($cut_images, ',');
        $re = $this->model->where('video',$src_path)->setField('cut_images', $cut_images);
        if ($re) {
            $ret['flag'] = 1;
            $ret['msg'] = '删除成功';
        } else {
            $ret['flag'] = 0;
            $ret['msg'] = '删除失败';
        }
        return json_encode($ret);
    }
    public function cutvideo($src_path, $time)
    {
        $obj_path = dirname($src_path).'/'.'preview.mp4';
//        echo $src_path.'--'.$obj_path.'--'.format_time($time, 1);die;
        $xyz = new Xyz();
        $stime = format_time($time, 1);

        $re = $xyz->cutvideo('.'.$src_path, '.'.$obj_path, $stime, 20);
        if($re == 'ok'){
            //保存预览
            $this->model = model('app\common\model\Category');
            $this->model->where('video',$src_path)->setField('preview', $obj_path);
            $ret['flag'] = 1;
            $ret['msg'] = '成功';
            $ret['info'] = $obj_path . "?t=" . time();
        }else{
            $ret['flag'] = 0;
            $ret['msg'] = '截取失败';
        }
        return json_encode($ret);
    }
    public function generategif()
    {
        $src_path = $_POST['src_path'];
        $time = $_POST['time'];

        $arrtime = json_decode($time);
        $flag = 1;
        foreach ($arrtime as $val) {
            if($val->etime <= $val->stime) $flag = 0;
        }
        if(!$flag){
            $ret['flag'] = 0;
            $ret['msg'] = '参数错误';
            return json_encode($ret);
        }
        $xyz = new Xyz();
        $tmppath = $this->tmpdir;
        foreach ($arrtime as $key=>$value) {
            $xyz->generateimages('.'.$src_path, $tmppath, $key+1, format_time($value->stime, 1), format_time($value->etime, 1));
        }
        $this->tmpfilerename();
        //将图片合成gif
        $obj_path = dirname($src_path).'/cover.gif';
        $re = $xyz->imagestogif($this->tmpdir.'%d.jpg', '.'.$obj_path);
        if ($re == 'ok') {
            //删除tmp
            //$this->deldir($this->tmpdir);
            return response()->json(array('code'=>1,'msg'=>'生成gif动图成功','data'=>str_replace(PUBLIC_PATH, '', $obj_path)));
        }
        return response()->json(array('code'=>0,'msg'=>'生成gif动图失败'));
    }
    public function tmpfilerename(){
        $tmpdir = $this->tmpdir;
        $filename = scandir($tmpdir);
        $num = 1;
        foreach($filename as $k=>$v){
            if($v=="." || $v==".."){continue;}
            rename( $tmpdir.$v, $tmpdir."$num.jpg");
            $num ++;
        }
    }
    protected function deldir($path)
    {
        if (is_dir($path)) {
            $dirs = scandir($path);
            foreach ($dirs as $dir) {
                if ($dir != '.' && $dir != '..') {
                    $sonDir = $path.'/'.$dir;
                    unlink($sonDir);
                }
            }
        }
    }
    public function syncvideo()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $timearr = explode(' - ', $params['datarange'], 2);
            $starttime = strtotime($timearr[0]);
            $endtime = strtotime($timearr[1]);
            $this->model = model('app\common\model\Category');
            $rows = $this->model->where('createtime', 'between', [$starttime, $endtime])->select();
            $rows = collection($rows)->toArray();
            $data['rows'] = $rows;
            $re = get_api('syncvideo', $data); //var_dump($data) ;die;
            $re = json_decode($re);
            if($re->code == 1){
                $this->success($re->msg);
            }else{
                $this->error($re->msg);
            }
            exit();
        }
        return $this->view->fetch();
    }

    public function manualslice()
    {
        $xyz = new Xyz();
        $redis = new Redis;


        $videostr = explode('/',$_POST['video']);
        // /video/product/20200217/TForfU7k/360/TForfU7k.mp4
        $tovideodir = PUBLIC_PATH.$videostr[1].'/'.$videostr[2].'/'.$videostr[3].'/'.$videostr[4].'/'.$_POST['rate'].'/'.$videostr[6];
        $ids = $_POST['ids'];
        $randsring = $videostr[4];
        $rate = $_POST['rate'];
        $date = $videostr[3];
        $month = substr($date,0,6);
        $logpath = createpath(ROOR_PATH.'/runtime/log/'.$month.'/'.$randsring.'_log.txt');
        $dirpath = $this->productdir.$date.'/'.$randsring.'/';


        $muname = $this->cfgs['trans_m3u8'] ? $this->cfgs['trans_m3u8'] : 'index.m3u8';
        $re = $xyz->transm3u8($tovideodir, dirname($tovideodir).'/'.$muname);
        if($re) {
            $this->model = model('app\common\model\Category');
            $info = $this->model->where(array('name'=>$ids))->find();

            $m3u8arr = json_decode($info->m3u8, true);
            if(!$m3u8arr) $m3u8arr=[];

            $m3u8file = dirname($tovideodir).'/'.$muname;
            $m3u8arr[$rate] = str_replace(PUBLIC_PATH,'/', $m3u8file);
            ksort($m3u8arr);

            $info->m3u8 = json_encode($m3u8arr);
            $info->save();

            if($this->cfgs['trans_default_size'] != $rate) { //切片完成是否删除源文件
                if($this->cfgs['transm3u8del']) unlink($tovideodir);
            }

            return array('code'=>0, 'msg'=>'切片成功','data'=>$this->cfgs['m3u8_url'].$m3u8file);
        } else { //切片失败
            return array('code'=>-1, 'msg'=>'切片失败');
        }
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
