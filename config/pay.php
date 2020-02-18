<?php
/**
 * @desc 支付平台配置信息
 * @date 2019-05-31
 * @user chenglong
 */
return [
    'juhe' => array(
        'pay_memberid'=>'10009',
        'pay_url'=>'http://pay.artedu99class.com/Pay_Index.html',
        'key'=>'srmbtz796w5b50oajvnoyj2ycb0ol52v',
        'pay_bankcode'=>array(
            'aliPay'=>919,
            'weixinPay'=>920
        ),
    ),
    'huoshan'=>array(
        'account_id'=>'10001',
        'pay_url'=>'http://119.23.41.58/gateway/index/checkpoint.do ',
        'robin'=>'2',//轮训，2：开启轮训，1：进入单通道模式
        'keyId'=>'7DB43437FCD2B7',
        'DEVICE_Key_weixin'=>'3D8052071A3FEC85BF',
        'DEVICE_Key_ali'=>'BD9D701F72A106347C',
    ),
];
?>