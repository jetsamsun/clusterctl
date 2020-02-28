<?php
namespace App\Http\Controllers\Api\V110;

use App\Http\Controllers\ApiController;
use App\Models\ShareLogs;
use App\Models\UserClick;
use App\Models\UserInfo;
use App\Models\Vip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppController extends  ApiController{
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      /api/v110/app/share
     */
    public function share(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if(empty($randomstr)||empty($timestamp)||empty($signature) || empty($token))
        {
            return $this->ajaxMessage(101,'请求参数不正确');
        }
        // 验证签名
        $s = $this->checkSignature($randomstr,$timestamp,$signature);
        if($s==99){
            return $this->ajaxMessage($s,'签名格式不正确');
        }
        if($s==100){
            return $this->ajaxMessage($s,'请求过期');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $time = date("Y-m-d H:i:s",$dataTmp['vipendtime']);
        if($time > date('Y-m-d H:i:s')){
            return $this->ajaxMessage(0,'VIP用户不增加观看次数');
        }

        $sharelog = ShareLogs::select("uid")->where(["uid"=>$uid,"time"=>date("Y-m-d")])->first();
        if($sharelog){
            return $this->ajaxMessage(0,'今天观看次数已增加');
        }else{
            DB::table("share_logs")->insert(array('uid'=>$uid,"time"=>date("Y-m-d")));

            $reg = DB::table("user_info")->where("uid",$uid)->increment("lookcount");
        }
        if($reg)
        {
            return $this->ajaxMessage(0,'操作成功');
        }
        else
        {
            return $this->ajaxMessage(1,'操作失败');
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      /api/v110/app/doClick
     */
    public function doClick(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if(empty($randomstr)||empty($timestamp)||empty($signature) || empty($token))
        {
            return $this->ajaxMessage(101,'请求参数不正确');
        }
        // 验证签名
        $s = $this->checkSignature($randomstr,$timestamp,$signature);
        if($s==99){
            return $this->ajaxMessage($s,'签名格式不正确');
        }
        if($s==100){
            return $this->ajaxMessage($s,'请求过期');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        if($dataTmp['vipendtime'] && $dataTmp['vipendtime']>strtotime(date('Y-m-d H:i:s'))){
            return $this->ajaxMessage(0,'新增成功');
        }

        $count = UserClick::select('id','lookcount','time')->where('uid',$uid)->first();
        if($count){
            if( ($count['time'] > date('Y-m-d')) && $count['lookcount'] < 3 ){

                DB::table('user_click')->where('id',$count['id'])->update( array( 'lookcount'=>$count['lookcount']+1,'time'=>date('Y-m-d H:i:s') ) );

            }elseif(($count['time'] > date('Y-m-d')) && $count['lookcount'] >= 3){

                return $this->ajaxMessage(0,'已新增三次');
            }elseif( $count['time'] < date('Y-m-d') ){

                DB::table('user_click')->where('id',$count['id'])->update( array( 'lookcount'=>1,'time'=>date('Y-m-d H:i:s') ) );
            }
        }else{
            DB::table('user_click')->insert(array('uid'=>$uid,'lookcount'=>1,'time'=>date('Y-m-d H:i:s')  ));
        }

        $reg=DB::table("user_info")->where("uid",$uid)->increment("lookcount");
        if($reg)
        {
            return $this->ajaxMessage(0,'成功');
        }
        else
        {
            return $this->ajaxMessage(1,'失败');
        }

    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *
     *          获取VIP金额
     */
    public function getVip(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if(empty($randomstr)||empty($timestamp)||empty($signature) || empty($token))
        {
            return $this->ajaxMessage(101,'请求参数不正确');
        }
        // 验证签名
        $s = $this->checkSignature($randomstr,$timestamp,$signature);
        if($s==99){
            return $this->ajaxMessage($s,'签名格式不正确');
        }
        if($s==100){
            return $this->ajaxMessage($s,'请求过期');
        }

        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $vip = Vip::select('vip_id','vip_month_money','vip_season_money','vip_year_money')
            ->first();

        return $this->ajaxMessage(0,'success',$vip);

    }
}