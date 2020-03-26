<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\ListOtype;
use App\Models\MediaEpisodes;
use App\Models\MediaMovies;
use App\Models\ScreenOtype;
use App\Models\StarList;
use App\Models\VideoAdminLog;
use App\Models\VideoList;
use App\Models\VideoOtype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaController extends AdminController
{
    public function media(){
        $data = VideoAdminLog::select('log_id')->get()->toArray();
        return view('media.list',compact('data'));
    }

    public function getMediaList(Request $request){
        $limit = $request->input('limit');
        $title = $request->input('title');
        $count = MediaMovies::select('*');
        if($title){
            $count = $count->where("title",'like','%'.$title.'%');
        }
        $count = $count->count();

        $dataTmp = MediaMovies::select('*');
        if($title){
            $dataTmp = $dataTmp->where("title",'like','%'.$title.'%');
        }
        $dataTmp = $dataTmp->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value) {
                $dataTmp[$key]['Cats'] = $this->getsecondotype($value['Cats']);
                $dataTmp[$key]['Type'] = $this->getfirstotype($value['Type']);
                $dataTmp[$key]['Tags'] = $this->getTags($value['Tags']);
                $dataTmp[$key]['Actors'] = $this->getStarName($value['Actors']);
                $dataTmp[$key]['Directors'] = $this->getStarName($value['Directors']);
                $dataTmp[$key]['Country'] = $this->getCountryName($value['Country']);
                $dataTmp[$key]['Create_time'] = date('Y-m-d H:i:s',$value['Create_time']);
                $dataTmp[$key]['Update_time'] = empty($value['Update_time'])?'--':date('Y-m-d H:i:s',$value['Update_time']);
            }
        }

        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }

    public function episode(Request $request,$mid){
        if($request->isMethod('post')) {
            return response()->json(array('code'=>0,'msg'=>"失败"));
        }

        return view('media.episode',compact('mid'));
    }

    public function getEpisodeList(Request $request){
        $limit = $request->input('limit');
        $title = $request->input('title');
        $mid = $request->input('mid');
        $count = MediaEpisodes::select('*');
        if($title){
            $count = $count->where("title",'like','%'.$title.'%');
        }
        $count = $count->where('MId',$mid)->count();

        $dataTmp = MediaEpisodes::select('*');
        if($title){
            $dataTmp = $dataTmp->where("title",'like','%'.$title.'%');
        }
        $dataTmp = $dataTmp->where('MId',$mid)->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value) {
                $dataTmp[$key]['Create_time'] = date('Y-m-d H:i:s',$value['Create_time']);
                $dataTmp[$key]['Update_time'] = empty($value['Update_time'])?'--':date('Y-m-d H:i:s',$value['Update_time']);
            }
        }

        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }
}
