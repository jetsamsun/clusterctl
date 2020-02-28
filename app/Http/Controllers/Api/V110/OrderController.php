<?php
namespace App\Http\Controllers\Api\V110;

use App\Http\Controllers\ApiController;
use App\Models\OrderInfo;
use App\Models\OrderLogs;
use App\Models\UserInfo;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserLog;

//include __DIR__."/../../../../../public/upload/Alipay/AlipayUtils.php";
include __DIR__."/../../../../../public/upload/HappyPay/HappyPayUtils.php";

class OrderController extends  ApiController{
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *          下订单
     *      /api/v110/order/subOrder
     *
     * /api/v110/order/subOrder?token=06a365d56073371f72a8c4745a35e38e
     *  &&vipotype=1&&num=30&&payotype=1&&price=0.01&&randomstr=123&&timestamp=1544214676&&signature=0de4c906f0d69ac18cccd67bee69695a
     */
    public function subOrder(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $token = $request->input('token');
        $vipotype = $request->input('vipotype');  // 1:月卡 5：季卡 10：年卡
        $num = $request->input('num');  // 购买天数
        $payotype = $request->input('payotype');  // 支付方式 1：支付宝 2：微信 3: 余额支付
        $price = $request->input('price'); // 价格

        if( empty($randomstr)|| empty($timestamp) || empty($signature) || empty($token)  || empty($vipotype) || empty($num) || empty($payotype) || empty($price) ){
            return $this->ajaxMessage(101,'请求参数不完整');
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

        DB::beginTransaction();
        try{
            $title = "";
            if($num == 30){
                $title = "全站月卡 CNY$30";
            }elseif($num == 90){
                $title = "全站季卡 CNY$68";
            }else{
                $title = "全站年卡 CNY$268";
            }
            $time = date("Y-m-d H:i:s",$dataTmp['vipendtime']);
            if($time < date('Y-m-d H:i:s')){
                $time = date('Y-m-d H:i:s');
            }
//            echo $time;
//            echo strtotime($time." +$num day");die;
            $ordernum = $this->msectime();
            $id = DB::table('order_info')->insertGetId(array(
                "ordernum"=>$ordernum,"payotype"=>$payotype,"title"=>$title,"uid"=>$uid,'num'=>$num,'vipotype'=>$vipotype,'vipstarttime'=>strtotime($time),
                'vipendtime'=>strtotime($time." +$num day"),"price"=>$price,"orderstatus"=>1,
                "createtime"=>strtotime(date("Y-m-d H:i:s")),'is_visible'=>1
            ));
            if(!$id){
                UserLog::addLog($uid,"1",$ordernum,"订单写入失败");
                throw new \Exception("订单写入失败");
            }
            $reg = OrderLogs::insert(array("uid"=>$uid,"orderid"=>$id,"order_status"=>1,"createtime"=>date("Y-m-d H:i:s")));
            if(!$reg)
            {
                UserLog::addLog($uid,"1",$ordernum,"订单状态记录失败");
                throw new \Exception("订单状态记录失败");
            }

            //余额支付
            if($payotype ==3){
                $user = UserInfo::select('vipotype',"residual_asset")->where('uid',$uid)->first();
                if($user["residual_asset"]<$price){
                    UserLog::addLog($uid,"1",$ordernum,"用户余额不足");
                    throw new \Exception("用户余额不足");
                }

                // 扣除余额 修改订单状态 修改用户vip时间
                if(strtotime($time) >time() ){
                    if($vipotype>$user['vipotype']){
                        //
                    }else{
                        $vipotype = $user['vipotype'];
                    }
                }
                $downcount = 0;
                if($vipotype == 1){
                    $downcount = 5;
                }elseif($vipotype==5){
                    $downcount = 8;
                }elseif($vipotype==10){
                    $downcount = 12;
                }
                // 修改用户到期时间
                $reg = DB::table("user_info")->where('uid',$uid)->update(array('residual_asset'=>$user['residual_asset']-$price,'vipendtime'=>strtotime($time." +$num day"),'downcount'=>$downcount,'vipotype'=>$vipotype));
                if(!$reg){
                    UserLog::addLog($uid,"1",$ordernum,"用户信息更新失败--余额支付");
                    throw new \Exception("用户信息更新失败");
                }
                // 修改订单状态
                $reg = DB::table("order_info")->where('oid',$id)->update(array('orderstatus'=>2));
                if(!$reg)
                {
                    UserLog::addLog($uid,"1",$ordernum,"订单状态更新失败--余额支付");
                    throw new \Exception("订单状态更新失败");
                }


                DB::commit();
                UserLog::addLog($uid,"1",$ordernum,"订单创建成功--余额支付");
                return $this->ajaxMessage(0,"操作成功");
            }

            DB::commit();
            UserLog::addLog($uid,"1",$ordernum,"订单创建成功--payotype：".$payotype);
            return $this->ajaxMessage(0,"success",$ordernum);
        }catch (\Exception $e){

            DB::rollback();//事务回滚
            return $this->ajaxMessage(1,$e->getMessage());
        }

    }

    public function payOrder_old(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $ordernum = $request->input('ordernum');
        $token = $request->input('token');

        if( empty($randomstr)|| empty($timestamp) || empty($signature)|| empty($token) || empty($ordernum) ){
            return $this->ajaxMessage(101,'请求参数不完整');
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

        // $data = $this->urlPic().'/api/v110/order/pay2Order?randomstr='.$randomstr.'&&timestamp='.$timestamp.'&&signature='.$signature.'&&token='.$token.'&&ordernum='.$ordernum;
        $data = "https://cctv4.me/note.php";
        Log::info($data);
        return $this->ajaxMessage(0,"success",$data);

    }
    public function payOrder(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $ordernum = $request->input('ordernum');
        $token = $request->input('token');

        if( empty($randomstr)|| empty($timestamp) || empty($signature)|| empty($token) || empty($ordernum) ){
            return $this->ajaxMessage(101,'请求参数不完整');
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

        $data = $this->urlPic().'/api/v110/order/pay3Order?randomstr='.$randomstr.'&&timestamp='.$timestamp.'&&signature='.$signature.'&&token='.$token.'&&ordernum='.$ordernum;
        UserLog::addLog($uid,"1",$ordernum,"请求payOrder");
        Log::info($data);
        return $this->ajaxMessage(0,"success",$data);

    }

    public function pay3Order(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $ordernum = $request->input('ordernum');
        $token = $request->input('token');

        if( empty($randomstr)|| empty($timestamp) || empty($signature)|| empty($token) || empty($ordernum) ){
            return $this->ajaxMessage(101,'请求参数不完整');
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

        $order = OrderInfo::select('oid','price',"payotype")->where('ordernum',$ordernum)->first();
        if($order){
            $order = $order->toArray();
        }else{
            return $this->ajaxMessage(104,'订单不存在');
        }
        if($order["payotype"] ==3){
            return $this->ajaxMessage(103,'订单选择余额支付');
        }
        $price = intval($order['price']);
        $payotype = "";
        if($order["payotype"] ==1 ){ // 支付宝
            $payotype = "PD-EPOINT-ALIPAY";
        }elseif($order["payotype"] == 2){ // weixin
            $payotype = "PD-EPOINT-WECHAT";
            // return 'https://cctv4.me/note.php';
            return "<script>location.href='https://cctv4.me/note.php';</script>";
        }
        // $price = $order['price'];
        // $price = "0.01";
        //Log::info($price);
        //默认支付
        // $pay = new \HappyPayUtils();
        // $data = $pay->createPayQrcode($ordernum,$price,$payotype);
        //聚合支付
        UserLog::addLog($uid,"1",$ordernum,"请求pay3Order");
        // $data = repayfGetOrder($uid,$ordernum,$price,$order["payotype"]);
        //火山支付
        $data = checkpoint($uid,$ordernum,$price,$order["payotype"]);

        //print_r($data);
        return $data;
    }
    /**
     * @param Request $request
     * @return string  二次调用支付
     */

    public function pay2Order(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $ordernum = $request->input('ordernum');
        $token = $request->input('token');

        if( empty($randomstr)|| empty($timestamp) || empty($signature)|| empty($token) || empty($ordernum) ){
            return $this->ajaxMessage(101,'请求参数不完整');
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

        $order = OrderInfo::select('oid','price',"payotype")->where('ordernum',$ordernum)->first();
        if($order){
            $order = $order->toArray();
        }else{
            return $this->ajaxMessage(104,'订单不存在');
        }
        if($order["payotype"] ==3){
            return $this->ajaxMessage(103,'订单选择余额支付');
        }

        $payotype = "";
        if($order["payotype"] ==1 ){ // 支付宝
            $payotype = "PD-EPOINT-ALIPAY";
        }elseif($order["payotype"] == 2){ // weixin
            $payotype = "PD-EPOINT-WECHAT";
        }
        $price = intval($order['price']);
        // $price = $order['price'];
        //Log::info($price);
        //默认支付
        $pay = new \HappyPayUtils();
        $data = $pay->createPayQrcode($ordernum,$price,$payotype);
        //聚合支付
        // $data = repayfGetOrder($ordernum,$price,$payotype);

        //print_r($data);
        return $data;
    }
    // 支付过程
    public function payorderdetail(Request $request){
        $SHOP_ID = $request->input('SHOP_ID');
        $ORDER_ID = $request->input('ORDER_ID');
        $SESS_ID = $request->input('SESS_ID');
        $PROD_ID = $request->input('PROD_ID');
        $AMOUNT = $request->input('AMOUNT');
        $CURRENCY = $request->input('CURRENCY');
        $CHECK_CODE = $request->input('CHECK_CODE');

        if(empty($SHOP_ID) || empty($ORDER_ID) || empty($SESS_ID) || empty($PROD_ID) || empty($AMOUNT) || empty($CURRENCY) ){
            Log::error('USER_ID='.$ORDER_ID.'&RES_CODE=20001');
            return 'USER_ID='.$ORDER_ID.'&RES_CODE=20001';
        }

        $cTmp='2tlyNvbVU9'."#".$SHOP_ID."#".$ORDER_ID."#".$AMOUNT.'#'.$SESS_ID."#".$PROD_ID.'#NtrdD613Ss';
        $code = md5($cTmp);
        if($code != $CHECK_CODE){
            Log::error('USER_ID='.$ORDER_ID.'&RES_CODE=20002');
            return 'USER_ID='.$ORDER_ID.'&RES_CODE=20002';
        }

        return 'USER_ID='.$ORDER_ID.'&RES_CODE=0&RET_URL=http://naisir.kuaibaotv.com/api/v110/order/callback';
    }
    public function callback(Request $request){
        $SESS_ID = $request->input('SESS_ID');
        $PROD_ID = $request->input('PROD_ID');
        $AMOUNT = $request->input('AMOUNT');
        $ORDER_ID = $request->input('ORDER_ID');
        $SHOP_ID = $request->input('SHOP_ID');
        $USER_ID = $request->input('USER_ID');
        $TRADE_CODE = $request->input('TRADE_CODE');
        $CHECK_CODE = $request->input('CHECK_CODE');
        //Log::error('222');
        if(empty($ORDER_ID) || empty($PROD_ID) || empty($USER_ID)  || empty($CHECK_CODE) ){
            Log::error(20201);
            return 'RES_CODE=20201';
        }

        $code = md5('2tlyNvbVU9#'.$SHOP_ID.'#'.$ORDER_ID.'#'.$AMOUNT.'#'.$SESS_ID.'#'.$PROD_ID.'#'.$USER_ID.'#NtrdD613Ss');
        if($code != $CHECK_CODE){
            Log::error('RES_CODE=20299');
            return 'RES_CODE=20299';
        }

        if($TRADE_CODE != 0){
            Log::error('TRADE_CODE : RES_CODE=20299');
            return 'RES_CODE=20299';
        }

        $order = OrderInfo::select('oid','uid','vipotype','price','vipstarttime','vipendtime','orderstatus')->where('ordernum',$ORDER_ID)->first()->toArray();

        $user = UserInfo::select('lookcount','vipotype')->where('uid',$order['uid'])->first();

        if($order["vipstarttime"] >time() ){
            if($order['vipotype']>$user['vipotype']){
                $vipotype = $order['vipotype'];
            }else{
                $vipotype = $user['vipotype'];
            }
        }else{
            $vipotype = $order['vipotype'];
        }
        $downcount = 0;
        if($vipotype == 1){
            $downcount = 5;
        }elseif($vipotype==5){
            $downcount = 8;
        }elseif($vipotype==10){
            $downcount = 12;
        }
        if($order['orderstatus']==1){
            // 修改用户到期时间
            DB::table("user_info")->where('uid',$order['uid'])->update(array('vipendtime'=>$order["vipendtime"],'downcount'=>$downcount,'vipotype'=>$vipotype));
            // 修改订单状态
            DB::table("order_info")->where('oid',$order['oid'])->update(array('orderstatus'=>2));

            // 记录收益明细
            $this->recordAsset($order['uid'],$order['price']);
        }

        echo 'RES_CODE=0';
    }
//    public function callback(){
//        //	只有支付成功时API支付才会通知商户.
//        // 支付成功回调有两次，都会通知到在线支付请求参数中的p8_Url上：浏览器重定向;服务器点对点通讯.
//        //Log::info($_GET);
//        //	解析返回参数.
//        $return = \AlipayUtils::getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
//
//        //	判断返回签名是否正确（True/False）
//        $bRet = \AlipayUtils::CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
//        //	以上代码和变量不需要修改.
//
//        //	校验码正确.
//        if($bRet){
//            if($r1_Code=="1"){
//
//                //	需要比较返回的金额与商家数据库中订单的金额是否相等，只有相等的情况下才认为是交易成功.
//                //	并且需要对返回的处理进行事务控制，进行记录的排它性处理，在接收到支付结果通知后，判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理，防止对同一条交易重复发货的情况发生.
//
//                if($r9_BType=="1"){
//                    echo "交易成功";
//                    echo  "<br />在线支付页面返回";
//                }elseif($r9_BType=="2"){
//                    #如果需要应答机制则必须回写流,以success开头,大小写不敏感.
//                    echo "success";
////                    echo "<br />交易成功";
////                    echo  "<br />在线支付服务器返回"
//                }
//                $order = OrderInfo::select('oid','uid','vipotype','vipstarttime','vipendtime')->where('ordernum',$r6_Order)->first()->toArray();
//
//                $user = UserInfo::select('lookcount','vipotype')->where('uid',$order['uid'])->first();
//
//                if($order["vipstarttime"] >time() ){
//                    if($order['vipotype']>$user['vipotype']){
//                        $vipotype = $order['vipotype'];
//                    }else{
//                        $vipotype = $user['vipotype'];
//                    }
//                }else{
//                    $vipotype = $order['vipotype'];
//                }
//                $downcount = 0;
//                if($vipotype == 1){
//                    $downcount = 5;
//                }elseif($vipotype==5){
//                    $downcount = 8;
//                }elseif($vipotype==10){
//                    $downcount = 12;
//                }
//                // 修改用户到期时间
//                DB::table("user_info")->where('uid',$order['uid'])->update(array('vipendtime'=>$order["vipendtime"],'downcount'=>$downcount,'vipotype'=>$vipotype));
//                // 修改订单状态
//                DB::table("order_info")->where('oid',$order['oid'])->update(array('orderstatus'=>2));
//            }
//
//        }else{
//            //echo "交易信息被篡改";
//            return $this->ajaxMessage(1,"交易信息被篡改");
//        }
//    }
    public function recordAsset($uid,$price){
        $level = UserLevel::select("first_level","second_level","third_level","fourth_level","uid")->where("uid",$uid)->first();
        DB::beginTransaction();
        try{
            // 增加记录
            // 自身收益记录
            $reg = Db::table("asset_detail")->insert(array("uid"=>$uid,"money"=>$price,'intro'=>'-','time'=>time()));
            if(!$reg){
                throw new \Exception("自身收益记录失败");
            }
            if($level){
                if($level["first_level"]){
                    $reg = Db::table("asset_detail")->insert(array("uid"=>$level["first_level"],"byuid"=>$uid,"money"=>$price*1,'intro'=>'+','time'=>time())); // 100%返利
                    if(!$reg){
                        throw new \Exception("上级收益记录失败");
                    }
                    $reg = DB::table("user_info")->where("uid",$level["first_level"])->increment("asset",$price*1,['residual_asset'=>DB::raw('residual_asset + '.$price*1)]);
                    if(!$reg){
                        throw new \Exception("上级资产增加失败");
                    }
                    // 提升追剧1次 上限为3次
                    $reg = DB::table("user_info")->where("uid",$level["first_level"])->increment('lookcount',1);
                    if(!$reg){
                        throw new \Exception("上级增加追剧次数失败");
                    }
                }
                if($level["second_level"]){
                    $money = round($price*0.5,2);
                    if( $money ){
                        $reg = Db::table("asset_detail")->insert(array("uid"=>$level["second_level"],"byuid"=>$uid,"money"=>$money,'intro'=>'+','time'=>time())); // 25%返利
                        if(!$reg){
                            throw new \Exception("上二级收益记录失败");
                        }
                        $reg = DB::table("user_info")->where("uid",$level["second_level"])->increment("asset",$money,['residual_asset'=>DB::raw('residual_asset + '.$money )]);
                        if(!$reg){
                            throw new \Exception("上二级资产增加失败");
                        }
                    }
                }
                if($level["third_level"]){
                    $money = round($price*0.15,2);
                    if( $money ){
                        $reg = Db::table("asset_detail")->insert(array("uid"=>$level["third_level"],"byuid"=>$uid,"money"=>$money,'intro'=>'+','time'=>time())); // 15%返利
                        if(!$reg){
                            throw new \Exception("上三级收益记录失败");
                        }
                        $reg = DB::table("user_info")->where("uid",$level["third_level"])->increment("asset",$money,['residual_asset'=>DB::raw('residual_asset + '.$money )]);
                        if(!$reg){
                            throw new \Exception("上三级资产增加失败");
                        }
                    }
                }
                if($level["fourth_level"]){
                    $money = round($price*0.1,2);
                    if( $money ){
                        $reg = Db::table("asset_detail")->insert(array("uid"=>$level["fourth_level"],"byuid"=>$uid,"money"=>$money,'intro'=>'+','time'=>time())); // 10%返利
                        if(!$reg){
                            throw new \Exception("四级收益记录失败");
                        }
                        $reg = DB::table("user_info")->where("uid",$level["fourth_level"])->increment("asset",$money,['residual_asset'=>DB::raw('residual_asset + '.$money)]);
                        if(!$reg){
                            throw new \Exception("四级资产增加失败");
                        }
                    }
                }
            }
            // 增加资产

            DB::commit();
            return true;
        }catch (\Exception $e){

            DB::rollback();//事务回滚
            Log::error($e->getMessage());
            return false;
        }
    }
}
