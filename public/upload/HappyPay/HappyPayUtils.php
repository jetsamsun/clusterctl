<?php

class HappyPayUtils
{
    private $SysTrustCode='2tlyNvbVU9';  // 系統信任碼
    private $ShopTrustCode='NtrdD613Ss';  //廠商(商家)信任碼
    private $SHOP_ID = 'M017316';  //廠商代碼SHOP_ID
    private $ORDER_ITEM = '购买会员';  //商品名稱：供消費者了解購買之物品 要用 Url Encode ex1.abcd1234(encode 後為 abcd1234)  ex2.測試(urlencode 後為%B4%FA%B8%D5) （如有中文要以 big5 編碼）
    private $CURRENCY =  'CNY';
    private $SHOP_PARA = '';
    private $URL =  'https://api.55168957.com/paysel_amt.php';
    public function doPay($orderid , $AMOUNT , $PROD_ID){
        $CHECK_CODE = md5($this->SysTrustCode.'#'.$this->SHOP_ID.'#'.$orderid.'#'.$AMOUNT.'#'.$this->ShopTrustCode);
        $data = [
            'SHOP_ID'=>$this->SHOP_ID,
            'ORDER_ID'=>$orderid,
            'ORDER_ITEM'=>urlencode($this->ORDER_ITEM),
            'AMOUNT'=>$AMOUNT,
            'PROD_ID'=>$PROD_ID,
            'CURRENCY'=>$this->CURRENCY,
            'SHOP_PARA'=>urlencode($this->SHOP_PARA),
            'CHECK_CODE'=>$CHECK_CODE,
        ];
        return $this->curlRequest($this->URL , $data);
    }
    /**
     * 订单创建
     * @param Request $request
     */
    public function createPayQrcode($orderid,$price,$PROD_ID)
    {
        $cSysTrustCode = $this->SysTrustCode; //系統信任碼
        $cShopTrustCode = $this->ShopTrustCode; //廠商(商家)信任碼
        $cShopID = $this->SHOP_ID; //廠商代碼SHOP_ID
        $cOrderID = $orderid;
        $cOrderItem = "会员";
        //商品名稱需作url encode
        $cOrderItemUrlEncode=urlencode($cOrderItem);
        $nAmount = $price;
        $cCurrency = "CNY";

        $cHost="http://api.55168957.com";
        $cPaySelectUrl=$cHost."/paysel_amt.php";

        //指定金流代碼（見附錄2）
        $cProdID = $PROD_ID;

        //如有自訂參數可由此帶入
        $cShopPara="";
        $cShopParaUrlEncode=urlencode($cShopPara);

        $cTmp=$cSysTrustCode."#".$cShopID."#".$cOrderID."#".$nAmount."#".$cShopTrustCode;
        $cCheckCode=md5($cTmp);

        $payHtml = <<<dreamer
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                    <meta content="telephone=no" name="format-detection">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

                </head>
                <style>

@-webkit-keyframes g {
	0% {
	-webkit-transform:rotate(0deg)
}
to {
	-webkit-transform:rotate(359deg)
}
}@-o-keyframes g {
	0% {
	-o-transform:rotate(0deg)
}
to {
	-o-transform:rotate(359deg)
}
}@keyframes g {
	0% {
	-webkit-transform:rotate(0deg);
	-o-transform:rotate(0deg);
	transform:rotate(0deg)
}
to {
	-webkit-transform:rotate(359deg);
	-o-transform:rotate(359deg);
	transform:rotate(359deg)
}
}
.spinner .loader,.spinner .spinnerImage {
	height:100px;
	width:100px;
	position:absolute;
	top:0;
	left:50%;
	opacity:1;
	filter:alpha(opacity=100)
}
.spinner .loader {
	margin:0 0 0 -55px;
	background-color:transparent;
	-webkit-animation:g .7s infinite linear;
	-o-animation:g .7s infinite linear;
	animation:g .7s infinite linear;
	border-left:5px solid #cbcbca;
	border-right:5px solid #cbcbca;
	border-bottom:5px solid #cbcbca;
	border-top:5px solid #2380be;
	border-radius:100%
}
.spinner.preloader {
	position:fixed;
	top:0;
	left:0;
	z-index:1000;
	background-color:#fff
}
.spinner {
    height: 100%;
    width: 100%;
    position: fixed;
    z-index: 10;
}
.spinner .spinWrap {
    width: 200px;
    height: 100px;
    position: fixed;
    top: 42%;
    left: 50%;
    margin-left: -98px;
    margin-top: -50px;
}
.spinner .spinnerImage {
    margin: 28px 0 0 -25px;
    background: url(https://www.paypalobjects.com/images/checkout/hermes/icon_ot_spin_lock_skinny.png) no-repeat;
}
.spinner .loadingMessage {
	-webkit-box-sizing:border-box;
	-ms-box-sizing:border-box;
	box-sizing:border-box;
	width:100%;
	margin-top:125px;
	text-align:center;
	z-index:100;
	outline:none
}
.spinner .loader,.spinner .spinnerImage {
	height:100px;
	width:100px;
	position:absolute;
	top:0;
	left:50%;
	opacity:1;
	filter:alpha(opacity=100)
}
.spinner .loader {
	margin:0 0 0 -55px;
	background-color:transparent;
	-webkit-animation:g .7s infinite linear;
	-o-animation:g .7s infinite linear;
	animation:g .7s infinite linear;
	border-left:5px solid #cbcbca;
	border-right:5px solid #cbcbca;
	border-bottom:5px solid #cbcbca;
	border-top:5px solid #2380be;
	border-radius:100%
}

</style>
                <body>
                    <!-- <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" id="go_pay"> -->
                    <form action="{$cPaySelectUrl}" method="post" id="go_pay">
                    <input type="hidden" name="SHOP_ID" value="{$cShopID}">
                    <input type="hidden" name="ORDER_ID" value="{$cOrderID}">
                    <input type="hidden" name="ORDER_ITEM" value="{$cOrderItemUrlEncode}">
                    <input type="hidden" name="AMOUNT" value="{$nAmount}">
                    <input type="hidden" name="PROD_ID" value="{$cProdID}">
                    <input type="hidden" name="CURRENCY" value="{$cCurrency}">
                    <input type="hidden" name="SHOP_PARA" value="{$cShopParaUrlEncode}">
                    <input type="hidden" name="CHECK_CODE" value="{$cCheckCode}">


                    <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" name="submit" alt="Make payments with payPal - it's fast,free and secure!">
                    <img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif"  width="1" height="1">
                    </form>
                    <div id="preloaderSpinner" class="preloader spinner" style="/* display: none; */">
                        <div class="spinWrap">
                            <p class="spinnerImage"></p>
                            <p class="loader"></p>
                            <p class="loadingMessage" id="spinnerMessage"></p>
                            <p class="loadingSubHeading" id="spinnerSubHeading"></p>
                        </div>
                    </div>
                    <script type="text/javascript">
                       window.onload = function(){
                            document.getElementById("go_pay").submit();
                        }
                    </script>
                </body>
                </html>
dreamer;

        return $payHtml;



    }
    //curl请求，支持post和get
    private function curlRequest($url,$data=null){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
        if(!empty($data)){
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        }

        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        $output = curl_exec($curl);
        \Illuminate\Support\Facades\Log::error($curl);
        curl_close($curl);
        return $output;
    }
}