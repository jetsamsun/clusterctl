<?php
use function GuzzleHttp\json_decode;

include_once('include.php');
$hostUrl = "http://naisir.cctv4.me";
$url = $hostUrl . "/api/v1/user/getUserShareQrCode";
$postData = array();
$token = isset($_GET['token']) ? $_GET['token'] : "";
if ($token) {
    $postData['token'] = $token;
}
$getData = postData($url, $postData);
$getData = \json_decode(postData($url, $postData), true);
$invitecode = "????";
$qrUrl = "";
if ($getData['code'] == "0") {
    $invitecode = $getData['data']['invitecode'];
    $qrUrl = $getData['data']['qrUrl'];
} else {
    echo "<script>";
    echo "alert('" . $getData['msg'] . "');";
    echo "</script>";
}
?>
<!DOCTYPE html>
<html>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
<title>青瓜TV 推广分享</title>
<meta name="format-detection" content="telephone=no" />
<link rel="stylesheet" type="text/css" href="./css/sharelink.css?v=1" />
<script src="./js/clipboard.min.js"></script>
</head>
<div class="bgImg">
</div>
<div class="qrCode">
    <img src="<?php echo $hostUrl . "/" . $qrUrl; ?>" style="border:4px solid #fff;"/>
</div>
<div class="invitationCode">
    我的邀请码： <?php echo $invitecode; ?>
</div>
<div class="title">
    好友推荐分享！免费观影福利！
</div>
<div class="bottom copy">
    <img src="images/sharelink_button.png?v=1" />
</div>
<div class="list text1">
    <img src="images/sharelink_dot.png" style="display:none" />
    截图二维码或复制“分享链接”发送给好友，好友（新用户）下载成功即可增加你的每日追剧次数，好友充值你还能获得充值返利。
</div>
<div class="list text1">
    <img src="images/sharelink_dot.png" style="display:none" />
    友情提示：</br>
    1. 请妥善保管自己的账号和推广码。</br>
    2. 若出现好友下载成功后，推广次数未增加情况，请让好友在“我的”页面输入邀请码来建立邀请关系。</br>
    3. 复制推广链接，请在Safari等浏览器中打开，微信或QQ等第三方内置浏览器无法访问！
</div>
<div class="list text1">
    <img src="images/sharelink_dot.png" style="display:none" />
    推广福利：</br>
    1. 成功推广1人，每日获10次追剧次数</br>
    2. 成功推广3人，获“月卡”不限追剧次数</br>
    3. 成功推广10人，获“年卡”不限追剧次数</br>（注：充值会员本身无限看，以上福利只是针对非会员）
</div>


<div class="bottom " style="margin-bottom:40px;">
<a href="group2.php?v=1"><img src="./images/btn-potato.png?v=1" style="width:12rem" /></a>
<div style="color:yellow;line-height: 26px;margin-top:4px;">加入”土豆“交流群</br>获取最新动态</div>
</br>
</br>
</div>

</html>

<script>
    var clipboard = new ClipboardJS('.copy', {
        text: function() {
            return "青瓜TV，独家资源每日更新，请复制链接用手机自带浏览器打开，QQ或微信第三方软件内置浏览器无法打开网页 http://cctv4.me/?f=<?php echo $invitecode; ?>";
        }
    });
    clipboard.on('success', function(e) {
        alert('\n\n复制分享链接成功\n');
    });
    clipboard.on('error', function(e) {
        alert('复制失败，请稍后重试~');
    });
</script>