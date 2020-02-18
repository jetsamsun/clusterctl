<?php
    /**
     * Alipay.com Inc.
     * Copyright (c) 2004-2014 All Rights Reserved.
     */

    class AlipayUtils
    {

        private static $aId='1572';
        private static $a_private_key='qH7Q961fIQjLdqZXoapqLZvYElqIs4P7';

        public static function doPay($ordernum,$price,$returnUrl,$payotype){
            #	商家设置用户购买商品的支付信息.
            ##API支付平台统一使用GBK/GB2312编码方式,参数如用到中文，请注意转码

            # 业务类型
            # 支付请求，固定值"Buy" .
            $p0_Cmd = "Buy";

            #	商户编号p1_MerId,以及密钥merchantKey 需要从API支付平台获得
            $p1_MerId = "1572";				 		#测试使用


            #	商户订单号,选填.
            ##若不为""，提交的订单号必须在自身账户交易中唯一;为""时，API支付会自动生成随机的商户订单号.
            $p2_Order = $ordernum;
            #	支付金额,必填.
            ##单位:元，精确到分.
            $p3_Amt = $price;

            #	交易币种,固定值"CNY".
            $p4_Cur = "CNY";
            #	商品名称
            ##用于支付时显示在API支付网关左侧的订单产品信息.
            $p5_Pid	= "buy";

            #	商品种类
            $p6_Pcat = "buy";

            #	商品描述
            $p7_Pdesc = "buy";
            #	商户接收支付成功数据的地址,支付成功后API支付会向该地址发送两次成功通知.
            $p8_Url	= $returnUrl;

            $p9_SAF = "0";
            #	商户扩展信息
            ##商户可以任意填写1K 的字符串,支付成功时将原样返回.
            $pa_MP = "mP";

            #	支付通道编码
            ##默认为""，到API支付网关.若不需显示API支付的页面，直接跳转到各银行、神州行支付、骏网一卡通等支付页面，该字段可依照附录:银行列表设置参数值.
            $pd_FrpId = $payotype ;

            #	应答机制
            ##默认为"1": 需要应答机制;
            $pr_NeedResponse	= "1";

            #调用签名函数生成签名串
            $hmac = AlipayUtils::getReqHmacString($p0_Cmd,$p1_MerId,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$p9_SAF,$pa_MP,$pd_FrpId,$pr_NeedResponse);


//            $data = Array("p0_Cmd"=>$p0_Cmd,'p1_MerId'=>$p1_MerId,"p2_Order"=>$p2_Order,'p3_Amt'=>$p3_Amt,
//                "p4_Cur"=>$p4_Cur,"p5_Pid"=>$p5_Pid,"p6_Pcat"=>$p6_Pcat,"p7_Pdesc"=>$p7_Pdesc,"p8_Url"=>$p8_Url,"p9_SAF"=>$p9_SAF,"pd_FrpId"=>$pd_FrpId,
//                "pr_NeedResponse"=>$pr_NeedResponse,"hmac"=>$hmac);

            $str = "?p0_Cmd=".$p0_Cmd."&&p1_MerId=".$p1_MerId."&&p2_Order=".$p2_Order."&&p3_Amt=".$p3_Amt."&&p4_Cur=".$p4_Cur."&&p5_Pid=".$p5_Pid."&&p6_Pcat=".$p6_Pcat."&&p7_Pdesc=".$p7_Pdesc."&&p8_Url=".$p8_Url;
            $str.="&&p9_SAF=".$p9_SAF."&&pa_MP=".$pa_MP."&&pd_FrpId=".$pd_FrpId."&&pr_NeedResponse=".$pr_NeedResponse."&&hmac=".$hmac;

            $url = "http://do.upepay.com/GateWay/ReceiveBank.aspx".$str;

            return $url;
        }

        #签名函数生成签名串
        public static function getReqHmacString($p0_Cmd,$p1_MerId,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$p9_SAF,$pa_MP,$pd_FrpId,$pr_NeedResponse)
        {

            //进行签名处理，一定按照文档中标明的签名顺序进行
            $sbOld = "";
            //加入业务类型
            $sbOld = $sbOld.$p0_Cmd;
            //加入商户编号
            $sbOld = $sbOld.$p1_MerId;
            //加入商户订单号
            $sbOld = $sbOld.$p2_Order;
            //加入支付金额
            $sbOld = $sbOld.$p3_Amt;
            //加入交易币种
            $sbOld = $sbOld.$p4_Cur;
            #加入商品名称
            $sbOld = $sbOld.$p5_Pid;
            #加入商品分类
            $sbOld = $sbOld.$p6_Pcat;
            #加入商品描述
            $sbOld = $sbOld.$p7_Pdesc;
            //加入商户接收支付成功数据的地址
            $sbOld = $sbOld.$p8_Url;
            //加入送货地址标识
            $sbOld = $sbOld.$p9_SAF;
            #加入商户扩展信息
            $sbOld = $sbOld.$pa_MP;
            //加入支付通道编码
            $sbOld = $sbOld.$pd_FrpId;
            //加入是否需要应答机制
            $sbOld = $sbOld.$pr_NeedResponse;
            // 记录日志
            return AlipayUtils::HmacMd5($sbOld,AlipayUtils::$a_private_key);

        }
        public static function HmacMd5($data,$key)
        {
            // RFC 2104 HMAC implementation for php.
            // Creates an md5 HMAC.
            // Eliminates the need to install mhash to compute a HMAC
            // Hacked by Lance Rushing(NOTE: Hacked means written)

            //需要配置环境支持iconv，否则中文参数不能正常处理
            $key = iconv("GB2312","UTF-8",$key);
            $data = iconv("GB2312","UTF-8",$data);

            $b = 64; // byte length for md5
            if (strlen($key) > $b) {
                $key = pack("H*",md5($key));
            }
            $key = str_pad($key, $b, chr(0x00));
            $ipad = str_pad('', $b, chr(0x36));
            $opad = str_pad('', $b, chr(0x5c));
            $k_ipad = $key ^ $ipad ;
            $k_opad = $key ^ $opad;

            return md5($k_opad . pack("H*",md5($k_ipad . $data)));
        }

        function logstr($orderid,$str,$hmac)
        {
 //  include 'merchantProperties.php';
//            $james=fopen($logName,"a+");
//            fwrite($james,"\r\n".date("Y-m-d H:i:s")."|orderid[".$orderid."]|str[".$str."]|hmac[".$hmac."]");
//            fclose($james);
        }
        #	取得返回串中的所有参数
        public static function getCallBackValue(&$r0_Cmd,&$r1_Code,&$r2_TrxId,&$r3_Amt,&$r4_Cur,&$r5_Pid,&$r6_Order,&$r7_Uid,&$r8_MP,&$r9_BType,&$hmac)
        {
            $r0_Cmd		= $_REQUEST['r0_Cmd'];
            $r1_Code	= $_REQUEST['r1_Code'];
            $r2_TrxId	= $_REQUEST['r2_TrxId'];
            $r3_Amt		= $_REQUEST['r3_Amt'];
            $r4_Cur		= $_REQUEST['r4_Cur'];
            $r5_Pid		= $_REQUEST['r5_Pid'];
            $r6_Order	= $_REQUEST['r6_Order'];
            $r7_Uid		= $_REQUEST['r7_Uid'];
            $r8_MP		= $_REQUEST['r8_MP'];
            $r9_BType	= $_REQUEST['r9_BType'];
            $hmac			= $_REQUEST['hmac'];

            return null;
        }

        public static function CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac)
        {
            if($hmac==AlipayUtils::getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType))
                return true;
            else
                return false;
        }
        public static function getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType)
        {
            #取得加密前的字符串
            $sbOld = "";
            #加入商家ID
            $sbOld = $sbOld.AlipayUtils::$aId;
            #加入消息类型
            $sbOld = $sbOld.$r0_Cmd;
            #加入业务返回码
            $sbOld = $sbOld.$r1_Code;
            #加入交易ID
            $sbOld = $sbOld.$r2_TrxId;
            #加入交易金额
            $sbOld = $sbOld.$r3_Amt;
            #加入货币单位
            $sbOld = $sbOld.$r4_Cur;
            #加入产品Id
            $sbOld = $sbOld.$r5_Pid;
            #加入订单ID
            $sbOld = $sbOld.$r6_Order;
            #加入用户ID
            $sbOld = $sbOld.$r7_Uid;
            #加入商家扩展信息
            $sbOld = $sbOld.$r8_MP;
            #加入交易结果返回类型
            $sbOld = $sbOld.$r9_BType;

            return AlipayUtils::HmacMd5($sbOld,AlipayUtils::$a_private_key);

        }
    }
?>