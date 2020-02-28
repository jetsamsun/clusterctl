<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\MsgLog;
use App\Models\SeekVideo;
use App\Models\VideoTrouble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MsgController extends  AdminController{
    public function msg(){
        return view('msg.list');
    }
    public function getMsgList(Request $request){
        $limit = $request->input('limit');
        $mobile = $request->input('mobile');
        $content = $request->input('content');
        $count = MsgLog::select('mid','msg_log.uid','content','time','mobile',"email","randomnum");
        if($mobile){
            $count = $count->where('mobile','like','%'.$mobile.'%');
        }
        if($content){
            $count = $count->where('content','like','%'.$content.'%');
        }
        $count = $count->join('user_info',"user_info.uid","=","msg_log.uid")->count();

        $dataTmp = MsgLog::select('mid','msg_log.uid','content','time','mobile',"email","randomnum");
        if($mobile){
            $dataTmp = $dataTmp->where('mobile','like','%'.$mobile.'%');
        }
        if($content){
            $dataTmp = $dataTmp->where('content','like','%'.$content.'%');
        }
        $dataTmp = $dataTmp->join('user_info',"user_info.uid","=","msg_log.uid")
            ->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];
            foreach($dataTmp as $key=>$value){
                $dataTmp[$key]["time"] = date("Y-m-d H:i:s",$value['time']);
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }

    public function seek(){
        return view('seek.list');
    }
    public function getSeekList(Request $request){
        $limit = $request->input('limit');
        $mobile = $request->input('mobile');
        $content = $request->input('content');
        $count = SeekVideo::select('id','seek_video.uid','content','time','mobile',"randomnum");
        if($mobile){
            $count = $count->where('mobile','like','%'.$mobile.'%');
        }
        if($content){
            $count = $count->where('content','like','%'.$content.'%');
        }
        $count = $count->join('user_info',"user_info.uid","=","seek_video.uid")->count();

        $dataTmp = SeekVideo::select('id','seek_video.uid','content','time','mobile',"randomnum");
        if($mobile){
            $dataTmp = $dataTmp->where('mobile','like','%'.$mobile.'%');
        }
        if($content){
            $dataTmp = $dataTmp->where('content','like','%'.$content.'%');
        }
        $dataTmp = $dataTmp->join('user_info',"user_info.uid","=","seek_video.uid")
            ->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];
            foreach($dataTmp as $key=>$value){
                $dataTmp[$key]["time"] = date("Y-m-d H:i:s",$value['time']);
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }

    public function delseek(Request $request){
        $id = $request->input("id");
        $reg = DB::table('seek_video')->where('id',$id)->delete();
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }

    public function trouble(){
        return view('trouble.list');
    }
    public function getTroubleList(Request $request){
        $limit = $request->input('limit');
        $number = $request->input('number');
        $title = $request->input('title');
        $content = $request->input('content');
        $count = VideoTrouble::select('id')
            ->join('video_list','video_list.vid','=','video_trouble.vid')
            ->join('user_info',"user_info.uid","=","video_trouble.uid");
        if($number){
            $count = $count->where('user_info.mobile','like','%'.$number.'%')
                ->orWhere('user_info.randomnum','like','%'.$number.'%');
        }
        if($title){
            $count = $count->where('video_list.title','like','%'.$title.'%');
        }
        if($content){
            $count = $count->where('video_trouble.content','like','%'.$content.'%');
        }
        $count = $count->count();

        $dataTmp = VideoTrouble::select('id','video_trouble.uid','video_list.title','video_trouble.content','video_trouble.time','mobile',"randomnum")
            ->join('video_list','video_list.vid','=','video_trouble.vid')
            ->join('user_info',"user_info.uid","=","video_trouble.uid");
        if($number){
            $dataTmp = $dataTmp->where('user_info.mobile','like','%'.$number.'%')
                ->orWhere('user_info.randomnum','like','%'.$number.'%');
        }
        if($title){
            $dataTmp = $dataTmp->where('video_list.title','like','%'.$title.'%');
        }
        if($content){
            $dataTmp = $dataTmp->where('video_trouble.content','like','%'.$content.'%');
        }
        $dataTmp = $dataTmp->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];
            foreach($dataTmp as $key=>$value){
                $dataTmp[$key]["time"] = date("Y-m-d H:i:s",$value['time']);
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }

    public function delTrouble(Request $request){
        $id = $request->input("id");
        $reg = DB::table('video_trouble')->where('id',$id)->delete();
        if($reg){
            return response()->json(array('status'=>1));
        }else{
            return response()->json(array('status'=>0));
        }
    }
}