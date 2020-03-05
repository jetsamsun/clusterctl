<?php
use function Qiniu\json_decode;
use App\Models\UserLog;
/**
 * @desc 自定义函数
 * @date 2019-04-16
 * @user chenglong
 */
/**
 * @desc 发送短信 253平台（国内）
 * @date 2019-04-16
 */
function sendSms253($phone,$code){
    $text = config('sms.253.msg')."您的验证码是：".$code."。请不要把验证码泄露给其他人。如非本人操作，请不要理会！";
    $needstatus = 'true';//是否需要状态报告
    //创蓝接口参数
    $postArr = array (
        'account'  =>  config('sms.253.name'),
        'password' => config('sms.253.password'),
        'msg' => urlencode($text),
        'phone' => $phone,
        'report' => $needstatus
    );
    $url = 'https://smssh1.253.com/msg/send/json'; //创蓝发送短信接口URL
    $postFields = $postArr;
    $postFields = json_encode($postFields);
    $ch = curl_init ();
    curl_setopt( $ch, CURLOPT_URL, $url ); 
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8'   //json版本需要填写  Content-Type: application/json;
        )
    );
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt( $ch, CURLOPT_TIMEOUT,60); 
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
    $ret = curl_exec ( $ch );
    if (false == $ret) {
        $result = curl_error(  $ch);
    } else {
        $rsp = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
        if (200 != $rsp) {
            $result = "请求状态 ". $rsp . " " . curl_error($ch);
        } else {
            $result = $ret;
        }
    }
    curl_close ( $ch );
    $return = false;
    if(!is_null(json_decode($result))){
        $output=json_decode($result,true);
        if(isset($output['code'])  && $output['code']=='0'){
            $return = true;
        }
    }
    return $return;
}
/**
 * @desc 发送短信 253平台（全球）
 * @date 2019-05-08
 */
function sendSms253Itn($phone,$code){
    $text = config('sms.253.msg')."您的验证码是：".$code."。请不要把验证码泄露给其他人。如非本人操作，请不要理会！";
    $postArr = array(
        'account'  =>  config('sms.253.nameItn'),
        'password' => config('sms.253.passwordItn'),
        'msg' => $text,
        'mobile' => $phone
    );
	$url = 'http://intapi.253.com/send/json?';
    $postFields = json_encode($postArr);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json; charset=utf-8'
        )
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $ret = curl_exec($ch);
    if (false == $ret) {
        $result = curl_error($ch);
    } else {
        $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $rsp) {
            $result = "请求状态 " . $rsp . " " . curl_error($ch);
        } else {
            $result = $ret;
        }
    }
    curl_close($ch);
    $return = false;
    if(!is_null(json_decode($result))){
        $output=json_decode($result,true);
        if(isset($output['code'])  && $output['code']=='0'){
            $return = true;
        }
    }
    return $return;
}
/**
 * @desc crul post提交
 * @date 2019-05-23
 */
function postData($url, $data){
    $ch = curl_init();
    $timeout = 300;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
    
    $handles = curl_exec($ch);
    curl_close($ch);
    $handles = trim($handles,chr(239).chr(187).chr(191).PHP_EOL);
    return $handles;
}
/**
 * @desc 聚合支付
 * @date 2019-05-23
 */
function repayfGetOrder($uid,$orderid,$price,$payotype){
    $url = config('pay.juhe.pay_url');
    $key = config('pay.juhe.key');
    $thisWebUrl = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"];
    $pay_bankcode = config('pay.juhe.pay_bankcode.aliPay');
    if($payotype==2){
        $pay_bankcode = config('pay.juhe.pay_bankcode.weixinPay');
    }
    $postData['pay_memberid'] = config('pay.juhe.pay_memberid');
    $postData['pay_orderid'] = $orderid;
    $postData['pay_applydate'] = date('Y-m-d H:i:s');
    $postData['pay_bankcode'] = $pay_bankcode;
    $postData['pay_notifyurl'] = $thisWebUrl.'/api/v1/order/repayfNotifyurl';
    $postData['pay_callbackurl'] = $thisWebUrl.'/api/v1/order/repayfCallbackurl';
    $postData['pay_amount'] = $price;
    $postData['pay_md5sign'] = getRepayfGetSign($postData,$key);
    $postData['pay_productname'] = "购买会员";
    $return = postData($url,$postData);
    UserLog::addLog($uid,"1",$orderid,"执行聚合支付");
    UserLog::addLog($uid,"1",$orderid,$return);
    return $return;
}

function getRepayfGetSign($Obj,$key=""){
    foreach ($Obj as $k => $v){
        $Parameters[$k] = $v;
    }
    $string = "";
    ksort($Parameters);
    foreach ($Parameters as $k => $v){
        if($v){
            $string .= $k . "=" . $v . "&";
        }
    }
    if(strlen($string) > 0){
        $string = substr($string, 0, strlen($string)-1);
    }
    $string = $string."&key=".$key;
    //签名步骤三：MD5加密
    $result_ = strtoupper(md5($string));
    return $result_;
}
/**
 * @desc 火山支付
 * @date 2019-06-05
 */
function checkpoint($uid,$orderid,$price,$payotype){
    $url = config('pay.huoshan.pay_url');
    $keyId = config('pay.huoshan.keyId');
    $DEVICE_Key = config('pay.huoshan.DEVICE_Key_ali');
    $account_id = config('pay.huoshan.account_id');
    $thisWebUrl = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"];
    $thoroughfare = "alipay_auto";
    $type = "2";
    if($payotype==2){
        $thoroughfare = "wechat_auto";
        $DEVICE_Key = config('pay.huoshan.DEVICE_Key_weixin');
        $type = "1";
    }
    // $thoroughfare = "service_auto";
    // $postData['account_id'] =  $account_id;
    // $postData['content_type'] = 'text';
    // $postData['thoroughfare'] = $thoroughfare;
    // $postData['type'] = $type;
    // $postData['out_trade_no'] = $orderid;
    // $postData['robin'] = config('pay.huoshan.robin');
    // $postData['keyId'] = $DEVICE_Key;
    // $postData['amount'] = $price;
    // $postData['callback_url'] = $thisWebUrl.'/api/v1/order/huoshanCallBack';
    // $postData['success_url'] = $thisWebUrl.'/api/v1/order/repayfCallbackurl';
    // $postData['error_url'] = $thisWebUrl.'/api/v1/order/repayfCallbackurl';
    // $postData['sign'] = signHuoshan($keyId,$postData);
    // $return = postData($url,$postData);

    $callback_url = $thisWebUrl.'/api/v1/order/huoshanCallBack';
    $success_url = $thisWebUrl.'/api/v1/order/repayfCallbackurl?oId='.$orderid.'&amount='.$price.'&sign='.signHuoshan($keyId,['amount'=>$price,'out_trade_no'=>$orderid]);
    $error_url = $thisWebUrl.'/api/v1/order/repayfCallbackurl';
    UserLog::addLog($uid,"1",$orderid,"执行火山支付");
    // UserLog::addLog($uid,"1",$orderid,$return);

    return '<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>接口调用</title>
    </head>
    <body>
    <form action="'.$url.' " method="post" id="frmSubmit">
        <input type="hidden" name="account_id" value="'.$account_id.'" />
        <input type="hidden" name="content_type" value="text"/>
        <input type="hidden" name="thoroughfare" value="'.$thoroughfare.'"/>
        <input type="hidden" name="out_trade_no" value="'.$orderid.'"/>
        <input type="hidden" name="sign" value="'.signHuoshan($keyId,['amount'=>$price,'out_trade_no'=>$orderid]).'"/>
        <input type="hidden" name="robin" value="'.config('pay.huoshan.robin').'" />
        <input type="hidden" name="callback_url" value="'.$callback_url.'" />
        <input type="hidden" name="success_url" value="'.$success_url.'" />
        <input type="hidden" name="error_url" value="'.$error_url.'" />
        <input type="hidden" name="amount" value="'.$price.'" />
        <input type="hidden" name="type" value="'.$type.'" />
        <input type="hidden" name="keyId" value="'.$DEVICE_Key.'" />
        <input type="submit" name="btn" value="submit" />
    </form>
    <script type="text/javascript">
    document.getElementById("frmSubmit").submit();
    </script>
    </body>
    </html>';
    // return $return;
}
function signHuoshan($key_id, $array){
    $data = md5(sprintf("%.2f", $array['amount']) . $array['out_trade_no']);
    $key[] = "";
    $box[] = "";
    $pwd_length = strlen($key_id);
    $data_length = strlen($data);
    $cipher = "";
    for ($i = 0; $i < 256; $i++) {
        $key[$i] = ord($key_id[$i % $pwd_length]);
        $box[$i] = $i;
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $key[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $data_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;

        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;

        $k = $box[(($box[$a] + $box[$j]) % 256)];
        $cipher .= chr(ord($data[$i]) ^ $k);
    }
    return md5($cipher);
}
/*自定设置环境变量*/
function checkcmd($cmd) {
    $path = shell_exec('echo $PATH');
    if(strpos($path, $cmd) === false) {
        putenv('PATH='.trim($path).':'.$cmd);
    }

    if(strpos(php_uname(), 'Windows') === false) {
        $arr = explode('/',$cmd);
        $cmd = $arr[count($arr)-1];
    }
    return $cmd;
}
/*创建目录*/
function mk_dir($dir, $mode = 0755)
{
    if (is_dir($dir) || @mkdir($dir,$mode))
        return true;
    if (!mk_dir(dirname($dir),$mode))
        return false;
    return @mkdir($dir,$mode);
}
/*随机字符串*/
function generate_random_string($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
/*格式化时间格式*/
function format_time($time,$sign=0)
{
    $h = $m = $s = 0;
    $str = '';
    $s = floor($time%60);
    $m = floor($time/60)%60;
    $h = floor($time/60/60);
    if($sign==0){
        if($h>0)  return $str = $h."时".$m."分".$s.'秒';
        if($m>0)  return $str = $m."分".$s.'秒';
        return $str = $s.'秒';
    }
    if($sign==1){
        if($m<10) $m = '0'.$m;
        if($s<10) $s = '0'.$s;
        if($h>0)  return $str = $h.":".$m.":".$s;
        if($m>0)  return $str = '00:'.$m.":".$s;
        return $str = '00:00:'.$s;
    }
}
/**
 * 将字节转换为可读文本
 * @param int    $size      大小
 * @param string $delimiter 分隔符
 * @return string
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 6; $i++) {
        $size /= 1024;
    }
    return round($size, 2) . $delimiter . $units[$i];
}

function site_url($msg) {
    return $msg;
}
function config_item($msg) {
    return $msg;
}