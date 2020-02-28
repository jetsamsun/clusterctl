<?php
namespace App\Http\Controllers;

use App\Models\ListOtype;
use App\Models\ScreenOtype;
use App\Models\StarList;
use App\Models\UserInfo;
use App\Models\VideoOtype;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public $version = 1.0;
    public $verify_code = "6688";

    private  $key = "lutube110";

    function urlPic(){
        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"];
        return $url;
    }
    /**
     * @param $code
     * @param $msg
     * @param null $data
     * @return json
     */
    public function ajaxMessage($code,$msg,$data=''){
        if($data=='暂无数据'){
            $data='';
        }
        if(gettype($data)=='object' || gettype($data)=='array'){
            array_walk_recursive($data, function (& $val, $key ) {
                if ($val === null) {
                    $val = '';
                }
            });
        }
        if($data){
            $arr = array(
                'code' => $code,
                'msg' => $msg,
                'data' => $data,
            );
        }else{
            $arr = array(
                'code' => $code,
                'msg' => $msg
            );
        }
        return response()->json($arr);
    }
    /**
     *  根据token 获取用户信息
     */
    function isLogin($t){
        $dataTmp = UserInfo::select('uid','vipendtime')->where('token',$t)->first();
        return $dataTmp;
    }
    /**
     *  获取毫秒
     */
    function msectime(){
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $msectime = $msectime+767552360488;
        return $msectime;
    }
    /**
     * 产生随机字符串
     */
    function generateRandomStr($len=6)
    {

        $returnStr='';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for($i = 0; $i < $len; $i ++) {
            $returnStr .= $pattern {mt_rand ( 0, 61 )}; //生成php随机数
        }
        return $returnStr;
    }
    /**
     * @param int $len
     * @return string
     */
    public function getCode($len=4){
        $code='';
        for($i=0;$i<$len;$i++){
            $code.=mt_rand(1,9);
        }
        return $code;
    }

    public function secToday($tim){
        $time = time()-$tim;
        if( $time < 60 ){ // 小于1分钟 60秒
            $strtime = $time."秒";
        }elseif( floor($time/60) < 60 ){  // 小于1小时 60分钟
            $strtime = floor($time/60)."分钟";
        }elseif( floor($time/60/60) < 24 ){ // 小于1天  24小时
            $strtime = floor($time/60/60)."个小时";
        }elseif( floor($time/60/60/24) < 30 ){ //小于30天 一个月
            $strtime = floor($time/60/60/24)."天";
        }elseif( floor($time/60/60/24) >= 30 &&  floor($time/60/60/24)< 365 ){
            $strtime = floor($time/60/60/24/30)."个月";
        }elseif( floor($time/60/60/24) >= 365 ){
            $strtime = floor($time/60/60/24/365)."年";
        }

        return $strtime;
    }
    // 获取视频分类
    function getApifirstotype($firstotype){
        $otypename = '';
        $data = ListOtype::select('oid','otypename')->where('oid',$firstotype)->first();
        if($data){
            $otypename = $data['otypename'];
        }
        return $otypename;
    }
    function getApisecondotype($secondotype){
        $otypename = '';
        $data = VideoOtype::select('oid','otypename')->where('oid',$secondotype)->first();
        if($data){
            $otypename = $data['otypename'];
        }
        return $otypename;
    }
    // 获取筛选条件
    function getApiscreenotype($screenotype){
        $screenotype = explode(',',$screenotype);
        $otypename = array();
        foreach($screenotype as $value){
            $data = ScreenOtype::select('oid','otypename')->where('oid',$value)->first();
            if($data){
                $otypename[] = $data['otypename'];
            }
        }
        $otypename = implode(',',$otypename);
        return $otypename;
    }
    // 获取明星
    function getApiStarName($star){
        $star = explode(',',$star);
        $uname = array();
        foreach($star as $value){
            $data = StarList::select('sid','uname')->where('sid',$value)->first();
            if($data){
                $uname[] = $data['uname'];
            }

        }
        $unamestr = implode(',',$uname);
        return $unamestr;
    }
    public function checkSignature($randomstr,$timestamp,$signature){
        $sign=MD5($randomstr.$timestamp.$this->key);

        if($sign!=$signature){
            return 99;
        }
        if( time()>intval($timestamp)+300 || intval($timestamp)>time()+300 ){
            return 100;
        }
    }

    public function getSecondOtypeStr($str){
        $type = explode(',',$str);
        $res = array();
        foreach($type as $value){
            $data = VideoOtype::select('oid','otypename')->where('oid',$value)->first();
            if($data){
                $res[] = $data;
            }

        }
        return $res;
    }
}