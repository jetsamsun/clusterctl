<?php
namespace App\Http\Controllers\Api\V110;

use App\Models\AssetDetail;
use App\Http\Controllers\ApiController;
use App\Models\UserInfo;
use App\Models\UserLevel;
use App\Models\UserWithdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends  ApiController{
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      /api/v110/asset/getUserAssetInfo
     *    提现
     */
    public function getUserAssetInfo(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        if( empty($randomstr)||empty($timestamp)||empty($signature)|| empty($token) ){
            return $this->ajaxMessage(101,'请求信息不完整');
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
        $data = array();
        $data  = UserInfo::select("uid","asset",'residual_asset')->where("uid",$uid)->first();
        if(!$data){
            return $this->ajaxMessage(102,'用户信息不存在');
        }

        $data['first_level'] = UserLevel::where("first_level",$uid)->count();
        $data['second_level'] = UserLevel::where("second_level",$uid)->count();
        $data['third_level'] = UserLevel::where("third_level",$uid)->count();
        $data['fourth_level'] = UserLevel::where("fourth_level",$uid)->count();
        $data['brokerage']=5; // 手续费

        return $this->ajaxMessage(0,'success',$data);
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *      /api/v110/asset/subUserWithdraw
     */
    public function subUserWithdraw(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $uname = $request->input('uname'); // 收款人
        $bankcard = $request->input('bankcard'); // 银行卡
        $cash_asset = $request->input('cash_asset'); // 价格
        $safecode = $request->input('safecode'); // 安全码
        if( empty($randomstr)||empty($timestamp)||empty($signature)|| empty($token) || empty($uname) || empty($bankcard) || empty($cash_asset) || empty($safecode) ){
            return $this->ajaxMessage(101,'请求信息不完整');
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

        if($cash_asset < 100){
            return $this->ajaxMessage(103,'提现金额不能小于100元');
        }

        $user = UserInfo::select("uid","residual_asset","cash_asset","frozen_asset","invitecode","safecode")->where("uid",$uid)->first();
        if( $user["residual_asset"] < $cash_asset ){
            return $this->ajaxMessage(103,'用户余额不足');
        }
        // 判断安全码
        if( md5(md5($safecode.$user['invitecode'])) != $user["safecode"] ){
            return $this->ajaxMessage(104,'安全码不正确');
        }
        DB::beginTransaction();
        try{
            // 修改用户资产
            $reg = DB::table("user_info")->where("uid",$uid)->update(array(
                "residual_asset"=>$user["residual_asset"]-$cash_asset,
                "cash_asset"=>$user["cash_asset"]+$cash_asset,
                "frozen_asset"=>$user["frozen_asset"]+$cash_asset,
            ));
            if(!$reg){
                throw new \Exception("更新用户资产失败");
            }
            // 新增提现记录
            $reg = DB::table("user_withdraw")->insert(array(
                "uid"=>$uid,"uname"=>$uname,"bankcard"=>$bankcard,"cash_asset"=>$cash_asset,"time"=>time()
            ));
            if(!$reg){
                throw new \Exception("新增提现记录失败");
            }

            DB::commit();
            return $this->ajaxMessage(0,"提交提现记录成功");
        }catch (\Exception $e){
            DB::rollback();//事务回滚
            return $this->ajaxMessage(1,$e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *   /api/v110/asset/getUserWithdraw
     */
    public function getUserWithdraw(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;
        if( empty($randomstr)||empty($timestamp)||empty($signature)|| empty($token) ){
            return $this->ajaxMessage(101,'请求信息不完整');
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
        $dataTmp = array();
        $data = UserWithdraw::select("id","uname","bankcard","cash_asset","status","time")
            ->where(["uid"=>$uid,"is_visible"=>1])
            ->orderBy("time","desc")
            ->paginate($pageSize);

        if($data){
            $dataTmp = $data->toArray();
            $dataTmp = $dataTmp['data'];
            foreach($dataTmp as $key=>$value){
                if($value["status"]==1){
                    $dataTmp[$key]["status"] = "已提交";
                }elseif($value["status"]==2){
                    $dataTmp[$key]["status"] = "成功";
                }elseif($value["status"]==3){
                    $dataTmp[$key]["status"] = "已拒绝";
                }
                $dataTmp[$key]["time"] = date("Y-m-d",$value['time']);
            }
        }
        return $this->ajaxMessage(0,"success",$dataTmp);
    }
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *   /api/v110/asset/getUserAssetDetail
     * 明细
     */
    public function getUserAssetDetail(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $page = !empty($request->input('page'))?$request->input('page'):1;
        $pageSize = !empty($request->input('pageSize'))?$request->input('pageSize'):10;
        if( empty($randomstr)||empty($timestamp)||empty($signature)|| empty($token) ){
            return $this->ajaxMessage(101,'请求信息不完整');
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
        $dataTmp = array();
        $data = AssetDetail::select("money","intro","time")
            ->where(["uid"=>$uid])
            ->orderBy("time","desc")
            ->paginate($pageSize);

        if($data){
            $dataTmp = $data->toArray();
            $dataTmp = $dataTmp['data'];
            foreach($dataTmp as $key=>$value){
                $dataTmp[$key]["time"] = date("Y-m-d",$value['time']);
                $dataTmp[$key]["money"] = $value["intro"].$value["money"];
                unset($dataTmp[$key]["intro"]);
            }
        }
        return $this->ajaxMessage(0,"success",$dataTmp);
    }
}