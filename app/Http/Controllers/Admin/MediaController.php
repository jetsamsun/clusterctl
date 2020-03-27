<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Config;
use App\Models\ListOtype;
use App\Models\M3u8List;
use App\Models\MediaActors;
use App\Models\MediaCats;
use App\Models\MediaCountry;
use App\Models\MediaEpisodes;
use App\Models\MediaMovies;
use App\Models\MediaTags;
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
    function __construct() {
        $cfgs = Config::get();
        foreach ($cfgs as $v) {
            $this->cfgs[$v['name']] = $v['value'];
        }

        $this->uploaddir = PUBLIC_PATH.$this->cfgs['upload_dir'].'/';
        $this->productdir = PUBLIC_PATH.$this->cfgs['video_dir'].'/';
        $this->tmpdir = PUBLIC_PATH.'/video/tmp/';
    }

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

    public function editmedia(Request $request,$vid) {
        if($request->isMethod('post')) {

        }

        // 导航分类
        $firstotype = ListOtype::select('oid','otypename')->get()->toArray();
        // 视频分类
        $secondotype = MediaCats::select('*')->get()->toArray();   $tree = getTree($secondotype);
        //标签
        $tags = MediaTags::get()->toArray();
        // 明星列表
        $star =  MediaActors::select('*')->where("Role",'like','%'.'2'.'%')->get()->toArray();
        // 导演列表
        $director =  MediaActors::select('*')->where("Role",'like','%'.'1'.'%')->get()->toArray();
        // 主体列表
        $data = MediaMovies::select('*')->where('Id',$vid)->first();    if($data) $data = $data->toArray(); else dd("data null");
        //国家/地区
        $country = MediaCountry::get()->toArray();


        $data['firstotype'] = explode(',',$data['Type']);
        $data['secondotype'] = explode(',',$data['Cats']);
        $data['country'] = explode(',',$data['Country']);
        $data['star'] = explode(',',$data['Actors']);           // 明星
        $data['tags'] = explode(',',$data['Tags']);
        $data['director'] = explode(',',$data['Directors']);
        $data['m3u8'] = [];
        $data['video'] = '';


        $cfgs = $this->cfgs;
        $mid = $vid;

        return view('media.mediaedit',compact('mid','data','director','country','star','tags','firstotype','tree','cfgs'));
    }

    public function delmedia(Request $request,$mid) {

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
                $dataTmp[$key]['Episode'] = empty($value['Episode'])?'--':$value['Episode'];
                $dataTmp[$key]['Season'] = empty($value['Season'])?'--':$value['Season'];
                $dataTmp[$key]['Lang'] = empty($value['Lang'])?'--':$value['Lang'];
                $dataTmp[$key]['Source'] = empty($value['Source'])?'--':$value['Source'];

                $dataTmp[$key]['Code'] = $value['Code'].'p';

                $dataTmp[$key]['Create_time'] = date('Y-m-d H:i:s',$value['Create_time']);
                $dataTmp[$key]['Update_time'] = empty($value['Update_time'])?'--':date('Y-m-d H:i:s',$value['Update_time']);
            }
        }

        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }

    public function editepisode(Request $request,$mid) {
        return view('media.episodeedit',compact('mid'));
    }

    public function delepisode(Request $request,$mid) {

    }
}
