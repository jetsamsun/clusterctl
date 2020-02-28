<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\OrderInfo;
use App\Models\OrderLogs;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserLog;

include __DIR__."/../../../../public/upload/Alipay/AlipayUtils.php";

class OrderController extends  ApiController{
    public function subOrder(Request $request){
        $token = $request->input('token');
        $num = $request->input('num');  // 购买天数
        $payotype = $request->input('payotype');  // 支付方式 1：支付宝 2：微信
        $price = $request->input('price'); // 价格
        //$price = 0.01;

        if(empty($token) || empty($num) || empty($payotype) || empty($price) ){
            return $this->ajaxMessage(101,'请求参数不完整');
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
                $title = "全站包月 CNY$30";
            }elseif($num == 180){
                $title = "全站半年 CNY$180";
            }else{
                $title = "全站包年 CNY$300";
            }
            $time = date("Y-m-d H:i:s",$dataTmp['vipendtime']);
            if($time < date('Y-m-d H:i:s')){
                $time = date('Y-m-d H:i:s');
            }
//            echo $time;
//            echo strtotime($time." +$num day");die;
            $ordernum = $this->msectime();
            $id = DB::table('order_info')->insertGetId(array(
                "ordernum"=>$ordernum,"payotype"=>$payotype,"title"=>$title,"uid"=>$uid,'num'=>$num,'vipstarttime'=>strtotime($time),
                'vipendtime'=>strtotime($time." +$num day"),"price"=>$price,"orderstatus"=>1,
                "createtime"=>strtotime(date("Y-m-d H:i:s")),'is_visible'=>1
            ));
            if(!$id){
                throw new \Exception("订单写入失败");
            }
            $reg = OrderLogs::insert(array("uid"=>$uid,"orderid"=>$id,"order_status"=>1,"createtime"=>date("Y-m-d H:i:s")));
            if(!$reg)
            {
                throw new \Exception("订单状态记录失败");
            }
//            // 修改用户vip到期时间
//            $reg = DB::table('user_info')->where("uid",$uid)
//                ->update(array('vipendtime'=>strtotime($time." +$num day")));
//            if(!$reg)
//            {
//                throw new \Exception("用户修改到期时间失败");
//            }
            DB::commit();

            return $this->ajaxMessage(0,"success",$ordernum);
        }catch (\Exception $e){

            DB::rollback();//事务回滚
            return $this->ajaxMessage(1,$e->getMessage());
        }

    }

    public function payOrder(Request $request){

        $ordernum = $request->input('ordernum');
        $token = $request->input('token');

        if(empty($token) || empty($ordernum) ){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];

        $order = OrderInfo::select('oid','price',"payotype")->where('ordernum',$ordernum)->first()->toArray();

        $returnUrl  = $this->urlPic()."/api/v1/order/callback";
        $payotype = "";
        if($order["payotype"] ==1 ){ // 支付宝
            $payotype = "alipaywap";
        }elseif($order["payotype"] == 2){ // H5 支付
            $payotype = "wxwap";
        }

        $data = \AlipayUtils::doPay($ordernum,$order['price'],$returnUrl,$payotype);

        //print_r($data);
        return $this->ajaxMessage(0,"success",$data);

    }
    public function callback(){
        //	只有支付成功时API支付才会通知商户.
        // 支付成功回调有两次，都会通知到在线支付请求参数中的p8_Url上：浏览器重定向;服务器点对点通讯.
        Log::info($_GET);
        //	解析返回参数.
        $return = \AlipayUtils::getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);

        //	判断返回签名是否正确（True/False）
        $bRet = \AlipayUtils::CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
        //	以上代码和变量不需要修改.

        //	校验码正确.
        if($bRet){
            if($r1_Code=="1"){

                //	需要比较返回的金额与商家数据库中订单的金额是否相等，只有相等的情况下才认为是交易成功.
                //	并且需要对返回的处理进行事务控制，进行记录的排它性处理，在接收到支付结果通知后，判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理，防止对同一条交易重复发货的情况发生.

                if($r9_BType=="1"){
                    echo "交易成功";
                    echo  "<br />在线支付页面返回";
                }elseif($r9_BType=="2"){
                    #如果需要应答机制则必须回写流,以success开头,大小写不敏感.
                    echo "success";
//                    echo "<br />交易成功";
//                    echo  "<br />在线支付服务器返回"
                }
                $order = OrderInfo::select('oid','uid','vipendtime')->where('ordernum',$r6_Order)->first()->toArray();
                // 修改用户到期时间
                DB::table("user_info")->where('uid',$order['uid'])->update(array('vipendtime'=>$order["vipendtime"]));
                // 修改订单状态
                DB::table("order_info")->where('oid',$order['oid'])->update(array('orderstatus'=>2));
            }

        }else{
            //echo "交易信息被篡改";
            return $this->ajaxMessage(1,"交易信息被篡改");
        }
    }

    public function getOrderList(Request $request){
        $token = $request->input('token');
        $page = !empty($request->input('page'))?$request->input('page'):1;

        $token = $request->input('token');
        if(empty($token) ){
            return $this->ajaxMessage(101,'请求参数不完整');
        }
        $dataTmp=$this->isLogin($token);
        if(!$dataTmp)
        {
            return $this->ajaxMessage(102,'用户登录信息不存在');
        }
        $uid = $dataTmp['uid'];
        $order = array();
        $data = array();

        $time = date("Y-m-d H:i:s",$dataTmp['vipendtime']);
        if($time > date('Y-m-d H:i:s')){
            // 为何再次查询  如果访问第二页没有数据 写下标报错
            //$order = $data[0];
            $order = OrderInfo::select("oid","ordernum","payotype",'title',"price","vipendtime","createtime")
                ->where(["uid"=>$uid,"orderstatus"=>2,"is_visible"=>1])
                ->orderBy("createtime","desc")
                ->first();
            if($order){
                $order = $order->toArray();
                if($order["payotype"] == 1){
                    $order["payotype"] = "支付宝";
                }elseif($order["payotype"] == 2){
                    $order["payotype"] = "微信支付";
                }elseif($order["payotype"] == 3){
                    $order["payotype"] = "后台充值";
                }
                $order["vipendtime"] = date("Y-m-d",$order["vipendtime"]);
                $order["createtime"] = date("Y-m-d",$order["createtime"]);
            }
        }


        $data = OrderInfo::select("oid","ordernum","payotype",'title',"price","vipendtime","createtime")
            ->where(["uid"=>$uid,"orderstatus"=>2,"is_visible"=>1])
            ->orderBy("createtime","desc")
            ->paginate(10);

        if($data){
            $data = $data->toArray();
            $data = $data["data"];

            foreach($data as $key=>$value){
                if($value["payotype"] == 1){
                    $data[$key]["payotype"] = "支付宝";
                }elseif($value["payotype"] == 2){
                    $data[$key]["payotype"] = "微信支付";
                }elseif($value["payotype"] == 3){
                    $data[$key]["payotype"] = "后台充值";
                }
                $data[$key]["vipendtime"] = date("Y-m-d",$value["vipendtime"]);
                $data[$key]["createtime"] = date("Y-m-d",$value["createtime"]);
            }

            return $this->ajaxMessage(0,"success",array("order"=>$order,'data'=>$data));
        }else{
            return $this->ajaxMessage(0,"暂无数据");
        }
    }
    /**
     * @desc 服务端返回地址.（POST返回数据） 服务端通知
     * @date 2019-05-23
     */
    public function repayfNotifyurl(Request $request){
        $message = "ERROR";
        $memberid = $request->input('memberid');
        $orderid = $request->input('orderid');
        $amount = $request->input('amount');
        $transaction_id = $request->input('transaction_id');
        $returncode = $request->input('returncode');
        $sign = $request->input('sign');
        $input = $request->input();
        UserLog::addLog('0',"1",$orderid?$orderid:'',$input);
        //验证加密信息
        unset($input['sign']);
        $thisSign = getRepayfGetSign($input,config('pay.juhe.key'));
        if($thisSign==$sign){
            if($orderid){
                $order = OrderInfo::select('oid','uid','vipendtime','orderstatus','price','vipotype','vipstarttime')->where('ordernum',$orderid)->first();
                if($returncode=='00'){
                    if($order){
                        $order = $order->toArray();
                        if($amount>=$order['price']){
                            if($order['orderstatus']=='1'){
                                $user = UserInfo::select('lookcount','vipotype')->where('uid',$order['uid'])->first();
                                if ($order["vipstarttime"] > time()) {
                                    if ($order['vipotype'] > $user['vipotype']) {
                                        $vipotype = $order['vipotype'];
                                    } else {
                                        $vipotype = $user['vipotype'];
                                    }
                                } else {
                                    $vipotype = $order['vipotype'];
                                }
                                $downcount = 0;
                                if ($vipotype == 1) {
                                    $downcount = 5;
                                } elseif ($vipotype == 5) {
                                    $downcount = 8;
                                } elseif ($vipotype == 10) {
                                    $downcount = 12;
                                }
                                // 修改用户到期时间
                                DB::table("user_info")->where('uid',$order['uid'])->update(array('vipendtime'=>$order["vipendtime"],'downcount'=>$downcount,'vipotype'=>$vipotype));
                                // 修改订单状态
                                DB::table("order_info")->where('oid',$order['oid'])->update(array('orderstatus'=>2));
                            }
                            $message = "OK";
                            UserLog::addLog($order['uid'],"1",$orderid,'异步执行成功');
                        }
                    }
                }
            }else{
                UserLog::addLog('0',"1",$orderid?$orderid:'',"异步通知不存在orderId");
            }
        }else{
            UserLog::addLog('0',"1",$orderid?$orderid:'','加密验证失败');
        }
        echo $message;exit;
    }
    /**
     * @desc 页面跳转返回地址（POST返回数据） 页面跳转通知
     * @date 2019-05-23
     */
    public function repayfCallbackurl(Request $request){
        header('Content-Type:text/html;charset=utf-8');
        $message = "支付失败";
        $orderid = $request->input('oId');
        $uid = "0";
        UserLog::addLog($uid,"1",$orderid?$orderid:'','同步回调'.\json_encode($request->input()));
        if($orderid){
            $order = OrderInfo::select('oid','uid','vipendtime','orderstatus','price')->where('ordernum',$orderid)->first();
            if($order){
                $order = $order->toArray();
                $uid = $order['uid'];
                if($order['orderstatus']=='2'){
                    $message = "支付成功";
                }
            }
        }
        UserLog::addLog($uid,"1",$orderid?$orderid:'','同步回调'.$message);
        echo '<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">';
        echo '<div style="text-align: center;font-size: 16px;margin-top: 150px;"> ';
        echo $message;
        echo ',请返回APP</div>';exit;
    }
    /**
     * @desc 火山支付回调
     * @date 2019-06-05
     */
    public function huoshanCallBack(Request $request){
        //商户名称
        $account_name  = $request->input('account_name');
        //支付时间戳
        $pay_time  = $request->input('pay_time');
        //支付状态
        $status  = $request->input('status');
        //支付金额
        $amount  = $request->input('amount');
        //支付时提交的订单信息
        $orderid  = $request->input('out_trade_no');
        //平台订单交易流水号
        $trade_no  = $request->input('trade_no');
        //该笔交易手续费用
        $fees  = $request->input('fees');
        //签名算法
        $sign  = $request->input('sign');
        //回调时间戳
        $callback_time  = $request->input('callback_time');
        //支付类型
        $type = $request->input('type');
        //商户KEY（S_KEY）
        $account_key = $request->input('account_key');
        $keyId = config('pay.huoshan.keyId');
        UserLog::addLog('0',"1",$orderid?$orderid:'','火山异步回调开始'.\json_encode($request->input()));
        //第一步，检测商户KEY是否一致
        if ($account_key != $keyId){
            UserLog::addLog('0',"1",$orderid?$orderid:'','商户KEY不一致');
            exit('error:key');
        };
        //第二步，验证签名是否一致
        if (signHuoshan($keyId, ['amount' => $amount, 'out_trade_no' => $orderid]) != $sign){
            UserLog::addLog('0',"1",$orderid?$orderid:'','加密验证失败');
            exit('error:sign');
        };
        $message = 'error';
        if($orderid){
            $order = OrderInfo::select('oid','uid','vipendtime','orderstatus','price','vipotype','vipstarttime')->where('ordernum',$orderid)->first();
            if($status=='success'){
                if($order){
                    $order = $order->toArray();
                    if($amount>=$order['price']){
                        if($order['orderstatus']=='1'){
                            $user = UserInfo::select('lookcount','vipotype')->where('uid',$order['uid'])->first();
                            if ($order["vipstarttime"] > time()) {
                                if ($order['vipotype'] > $user['vipotype']) {
                                    $vipotype = $order['vipotype'];
                                } else {
                                    $vipotype = $user['vipotype'];
                                }
                            } else {
                                $vipotype = $order['vipotype'];
                            }
                            $downcount = 0;
                            if ($vipotype == 1) {
                                $downcount = 5;
                            } elseif ($vipotype == 5) {
                                $downcount = 8;
                            } elseif ($vipotype == 10) {
                                $downcount = 12;
                            }
                            // 修改用户到期时间
                            DB::table("user_info")->where('uid',$order['uid'])->update(array('vipendtime'=>$order["vipendtime"],'downcount'=>$downcount,'vipotype'=>$vipotype));
                            // 修改订单状态
                            DB::table("order_info")->where('oid',$order['oid'])->update(array('orderstatus'=>2));
                        }
                        $message = "success";
                        UserLog::addLog($order['uid'],"1",$orderid,'异步执行成功');
                    }
                }
            }
        }else{
            UserLog::addLog('0',"1",$orderid?$orderid:'',"异步通知不存在orderId");
        }
        echo $message;
    }
}