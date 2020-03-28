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
use App\Models\MediaStatus;
use App\Models\MediaTags;
use App\Models\MediaType;
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
        return view('media.list');
    }

    public function getMediaList(Request $request){
        $limit = $request->input('limit');
        $title = $request->input('title');
        $count = MediaMovies::select('*');
        if($title){
            $count = $count->where("Name",'like','%'.$title.'%');
        }
        $count = $count->count();

        $dataTmp = MediaMovies::select('*');
        if($title){
            $dataTmp = $dataTmp->where("Name",'like','%'.$title.'%');
        }
        $dataTmp = $dataTmp->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value) {
                $dataTmp[$key]['Cats'] = $this->getMediaCats($value['Cats']);

                $type = $this->getMediaType($value['Type']);
                $dataTmp[$key]['Type'] = empty($type)?'未定义':$type;

                $dataTmp[$key]['Tags'] = $this->getMediaTags($value['Tags']);
                $dataTmp[$key]['Actors'] = $this->getMediaActors($value['Actors']);
                $dataTmp[$key]['Directors'] = $this->getMediaActors($value['Directors']);
                $dataTmp[$key]['Country'] = $this->getCountryName($value['Country']);
                $dataTmp[$key]['Status'] = $this->getStatusName($value['Status']);
                $dataTmp[$key]['Create_time'] = date('Y-m-d H:i:s',$value['Create_time']);
                $dataTmp[$key]['Update_time'] = empty($value['Update_time'])?'--':date('Y-m-d H:i:s',$value['Update_time']);
            }
        }

        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }

    public function addmedia(Request $request) {
        $cfgs = $this->cfgs;

        if($request->isMethod('post')) {
            $Name = $request->input('Name');
            $Image = $request->input('imgval');
            $imgval_old = $request->input('imgval_old');
            $Image_big = $request->input('bigimgval');
            $bigimgval_old = $request->input('bigimgval_old');
            $Type = $request->input('Type');
            $Country = $request->input('Country');
            $Cats = $request->input('Cats');
            $Year = $request->input('Year');
            $Episodes = $request->input('Episodes');
            $Preid = $request->input('Preid');
            $FH = $request->input('FH');
            $IMDB = $request->input('IMDB');
            $Score = $request->input('Score');
            $Tags = $request->input('Tags');   //可多个
            $Directors = $request->input('Directors');   //可多个
            $Actors = $request->input('Actors');  //可多个
            $Status = $request->input('Status');
            $KeyWord = $request->input('KeyWord');
            $Content = $request->input('Content');
            $Mark = $request->input('Mark');


            if(!empty($Tags) && is_array($Tags)) {
                sort($Tags);
                $Tags = implode(',', $Tags);
            }
            if(!empty($Directors) && is_array($Directors)) {
                sort($Directors);
                $Directors = implode(',', $Directors);
            }
            if(!empty($Actors) && is_array($Actors)) {
                sort($Actors);
                $Actors = implode(',', $Actors);
            }

            $reg = DB::table('media_movies')->insert(array(
                'Name'=>$Name,
                'Image'=>empty($Image)?'':$Image,
                'Image_big'=>empty($Image_big)?'':$Image_big,
                'Type'=>empty($Type)?0:$Type,
                'Country'=>empty($Country)?'':$Country,
                'Cats'=>empty($Cats)?'':$Cats,
                'Year'=>empty($Year)?'':$Year,
                'Episodes'=>empty($Episodes)?0:$Episodes,
                'Preid'=>empty($Preid)?0:$Preid,
                'FH'=>empty($FH)?'':$FH,
                'IMDB'=>empty($IMDB)?'':$IMDB,
                'Score'=>empty($Score)?0:$Score,
                'Tags'=>empty($Tags)?'':$Tags,
                'Directors'=>empty($Directors)?'':$Directors,
                'Actors'=>empty($Actors)?'':$Actors,
                'Status'=>empty($Status)?0:$Status,
                'KeyWord'=>empty($KeyWord)?'':$KeyWord,
                'Content'=>empty($Content)?'':$Content,
                'Mark'=>empty($Mark)?'':$Mark,
                'Create_time'=>time(),
            ));

            if($Image != $imgval_old){
                if(  !empty($imgval_old) &&  file_exists('.'.$imgval_old) ){
                    unlink('.'.$imgval_old);
                }
            }
            if($Image_big != $bigimgval_old){
                if( !empty($bigimgval_old) && file_exists('.'.$bigimgval_old) ){
                    unlink('.'.$bigimgval_old);
                }
            }

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            } else {
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }

        // 类型
        $types = MediaType::get()->toArray();
        // 视频分类
        $cats = MediaCats::get()->toArray();   $cats = getTree($cats);
        // 标签
        $tags = MediaTags::get()->toArray();
        // 状态列表
        $status = MediaStatus::get()->toArray();
        // 明星列表
        $actors =  MediaActors::where("Role",'like','%'.'2'.'%')->get()->toArray();
        // 导演列表
        $directors =  MediaActors::where("Role",'like','%'.'1'.'%')->get()->toArray();
        //国家/地区
        $countrys = MediaCountry::get()->toArray();


        return view('media.mediaadd',compact('directors','countrys','actors','tags','types','cats','cfgs','status'));
    }

    public function editmedia(Request $request,$mid) {
        $cfgs = $this->cfgs;

        if($request->isMethod('post')) {
            $Name = $request->input('Name');
            $Image = $request->input('imgval');
            $imgval_old = $request->input('imgval_old');
            $Image_big = $request->input('bigimgval');
            $bigimgval_old = $request->input('bigimgval_old');
            $Type = $request->input('Type');
            $Country = $request->input('Country');
            $Cats = $request->input('Cats');
            $Year = $request->input('Year');
            $Episodes = $request->input('Episodes');
            $Preid = $request->input('Preid');
            $FH = $request->input('FH');
            $IMDB = $request->input('IMDB');
            $Score = $request->input('Score');
            $Tags = $request->input('Tags');   //可多个
            $Directors = $request->input('Directors');   //可多个
            $Actors = $request->input('Actors');  //可多个
            $Status = $request->input('Status');
            $KeyWord = $request->input('KeyWord');
            $Content = $request->input('Content');
            $Mark = $request->input('Mark');


            if(!empty($Tags) && is_array($Tags)) {
                sort($Tags);
                $Tags = implode(',', $Tags);
            }
            if(!empty($Directors) && is_array($Directors)) {
                sort($Directors);
                $Directors = implode(',', $Directors);
            }
            if(!empty($Actors) && is_array($Actors)) {
                sort($Actors);
                $Actors = implode(',', $Actors);
            }

            $reg = DB::table('media_movies')->where('Id',$mid)->update(array(
                'Name'=>$Name,
                'Image'=>empty($Image)?'':$Image,
                'Image_big'=>empty($Image_big)?'':$Image_big,
                'Type'=>empty($Type)?0:$Type,
                'Country'=>empty($Country)?'':$Country,
                'Cats'=>empty($Cats)?'':$Cats,
                'Year'=>empty($Year)?'':$Year,
                'Episodes'=>empty($Episodes)?0:$Episodes,
                'Preid'=>empty($Preid)?0:$Preid,
                'FH'=>empty($FH)?'':$FH,
                'IMDB'=>empty($IMDB)?'':$IMDB,
                'Score'=>empty($Score)?0:$Score,
                'Tags'=>empty($Tags)?'':$Tags,
                'Directors'=>empty($Directors)?'':$Directors,
                'Actors'=>empty($Actors)?'':$Actors,
                'Status'=>empty($Status)?0:$Status,
                'KeyWord'=>empty($KeyWord)?'':$KeyWord,
                'Content'=>empty($Content)?'':$Content,
                'Mark'=>empty($Mark)?'':$Mark,
                'Update_time'=>time(),
            ));

            if($Image != $imgval_old){
                if(  !empty($imgval_old) &&  file_exists('.'.$imgval_old) ){
                    unlink('.'.$imgval_old);
                }
            }
            if($Image_big != $bigimgval_old){
                if( !empty($bigimgval_old) && file_exists('.'.$bigimgval_old) ){
                    unlink('.'.$bigimgval_old);
                }
            }

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }

        // 类型
        $types = MediaType::get()->toArray();
        // 视频分类
        $cats = MediaCats::get()->toArray();   $cats = getTree($cats);
        // 标签
        $tags = MediaTags::get()->toArray();
        // 状态列表
        $status = MediaStatus::get()->toArray();
        // 明星列表
        $actors =  MediaActors::where("Role",'like','%'.'2'.'%')->get()->toArray();
        // 导演列表
        $directors =  MediaActors::where("Role",'like','%'.'1'.'%')->get()->toArray();
        //国家/地区
        $countrys = MediaCountry::get()->toArray();

        // 主体列表
        $data = MediaMovies::select('*')->where('Id',$mid)->first();    if($data) $data = $data->toArray(); else dd("data null");
        $data['Type'] = explode(',',$data['Type']);
        $data['Cats'] = explode(',',$data['Cats']);
        $data['Country'] = explode(',',$data['Country']);
        $data['Actors'] = explode(',',$data['Actors']);
        $data['Tags'] = explode(',',$data['Tags']);
        $data['Directors'] = explode(',',$data['Directors']);

        return view('media.mediaedit',compact('mid','data','directors','countrys','actors','tags','types','cats','cfgs','status'));
    }

    public function delmedia(Request $request) {
        $ids = explode('_', $_POST['mid']);

        foreach ($ids as $mid) {
            $reg = DB::table('media_movies')->where('Id', $mid)->delete();
            if ($reg) {
                $ep = MediaEpisodes::where('MId', $mid)->get()->toArray();

                if(count($ep)>0) {
                    DB::table('media_episodes')->where('MId', $mid)->delete();
                }
            } else {
                return json_encode(array('code' => 0, 'msg'=>'删除失败'));
            }
        }
        return json_encode(array('code' => 1, 'msg'=>'删除成功'));
    }

    public function episode(Request $request,$mid){
        if($request->isMethod('post')) {
            return response()->json(array('code'=>0,'msg'=>"失败"));
        }

        $media = MediaMovies::where('Id',$mid)->first()->toArray();

        return view('media.episode',compact('mid','media'));
    }

    public function getEpisodeList(Request $request){
        $limit = $request->input('limit');
        $title = $request->input('title');
        $mid = $request->input('mid');
        $count = MediaEpisodes::select('*');
        if($title){
            $count = $count->where("Name",'like','%'.$title.'%');
        }
        $count = $count->where('MId',$mid)->count();

        $dataTmp = MediaEpisodes::select('*');
        if($title){
            $dataTmp = $dataTmp->where("Name",'like','%'.$title.'%');
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

    public function addepisode(Request $request) {
        $cfgs = $this->cfgs;

        if($request->isMethod('post')) {
            $Name = $request->input('Name');
            $Image = $request->input('imgval');
            $imgval_old = $request->input('imgval_old');
            $Image_big = $request->input('bigimgval');
            $bigimgval_old = $request->input('bigimgval_old');
            $Type = $request->input('Type');
            $Country = $request->input('Country');
            $Cats = $request->input('Cats');
            $Year = $request->input('Year');
            $Episodes = $request->input('Episodes');
            $Preid = $request->input('Preid');
            $FH = $request->input('FH');
            $IMDB = $request->input('IMDB');
            $Score = $request->input('Score');
            $Tags = $request->input('Tags');   //可多个
            $Directors = $request->input('Directors');   //可多个
            $Actors = $request->input('Actors');  //可多个
            $Status = $request->input('Status');
            $KeyWord = $request->input('KeyWord');
            $Content = $request->input('Content');
            $Mark = $request->input('Mark');


            if(!empty($Tags) && is_array($Tags)) {
                sort($Tags);
                $Tags = implode(',', $Tags);
            }
            if(!empty($Directors) && is_array($Directors)) {
                sort($Directors);
                $Directors = implode(',', $Directors);
            }
            if(!empty($Actors) && is_array($Actors)) {
                sort($Actors);
                $Actors = implode(',', $Actors);
            }

            $reg = DB::table('media_movies')->insert(array(
                'Name'=>$Name,
                'Image'=>empty($Image)?'':$Image,
                'Image_big'=>empty($Image_big)?'':$Image_big,
                'Type'=>empty($Type)?0:$Type,
                'Country'=>empty($Country)?'':$Country,
                'Cats'=>empty($Cats)?'':$Cats,
                'Year'=>empty($Year)?'':$Year,
                'Episodes'=>empty($Episodes)?0:$Episodes,
                'Preid'=>empty($Preid)?0:$Preid,
                'FH'=>empty($FH)?'':$FH,
                'IMDB'=>empty($IMDB)?'':$IMDB,
                'Score'=>empty($Score)?0:$Score,
                'Tags'=>empty($Tags)?'':$Tags,
                'Directors'=>empty($Directors)?'':$Directors,
                'Actors'=>empty($Actors)?'':$Actors,
                'Status'=>empty($Status)?0:$Status,
                'KeyWord'=>empty($KeyWord)?'':$KeyWord,
                'Content'=>empty($Content)?'':$Content,
                'Mark'=>empty($Mark)?'':$Mark,
                'Create_time'=>time(),
            ));

            if($Image != $imgval_old){
                if(  !empty($imgval_old) &&  file_exists('.'.$imgval_old) ){
                    unlink('.'.$imgval_old);
                }
            }
            if($Image_big != $bigimgval_old){
                if( !empty($bigimgval_old) && file_exists('.'.$bigimgval_old) ){
                    unlink('.'.$bigimgval_old);
                }
            }

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"新增成功"));
            } else {
                return response()->json(array('code'=>0,'msg'=>"新增失败"));
            }
        }

        // 类型
        $types = MediaType::get()->toArray();
        // 视频分类
        $cats = MediaCats::get()->toArray();   $cats = getTree($cats);
        // 标签
        $tags = MediaTags::get()->toArray();
        // 状态列表
        $status = MediaStatus::get()->toArray();
        // 明星列表
        $actors =  MediaActors::where("Role",'like','%'.'2'.'%')->get()->toArray();
        // 导演列表
        $directors =  MediaActors::where("Role",'like','%'.'1'.'%')->get()->toArray();
        //国家/地区
        $countrys = MediaCountry::get()->toArray();


        return view('media.episodeadd',compact('directors','countrys','actors','tags','types','cats','cfgs','status'));
    }

    public function editepisode(Request $request,$id) {
        $cfgs = $this->cfgs;

        if($request->isMethod('post')) {

        }

        // 类型
        $types = MediaType::get()->toArray();
        // 视频分类
        $cats = MediaCats::get()->toArray();   $cats = getTree($cats);
        // 标签
        $tags = MediaTags::get()->toArray();
        // 状态列表
        $status = MediaStatus::get()->toArray();
        // 明星列表
        $actors =  MediaActors::where("Role",'like','%'.'2'.'%')->get()->toArray();
        // 导演列表
        $directors =  MediaActors::where("Role",'like','%'.'1'.'%')->get()->toArray();
        //国家/地区
        $countrys = MediaCountry::get()->toArray();

        // 剧集
        $data = MediaEpisodes::select('*')->where('Id',$id)->first();    if($data) $data = $data->toArray(); else dd("data null");


        return view('media.episodeedit',compact('id','data','directors','countrys','actors','tags','types','cats','cfgs','status'));
    }

    public function delepisode(Request $request) {
        $ids = explode('_', $_POST['mid']);

        foreach ($ids as $mid) {
            $reg = DB::table('media_episodes')->where('Id', $mid)->delete();
            if ($reg) {

            } else {
                return json_encode(array('code' => 0, 'msg'=>'删除失败'));
            }
        }
        return json_encode(array('code' => 1, 'msg'=>'删除成功'));
    }
}
