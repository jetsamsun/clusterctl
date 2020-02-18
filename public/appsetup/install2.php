<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <title><?php echo time();?></title>
    <meta name="format-detection" content="telephone=no" />
    <meta name="description" content="青瓜TV cctv4.me www.cctv4.me 青瓜视频 青瓜影视">
    <meta name="keywords" content="青瓜TV cctv4.me www.cctv4.me 青瓜视频 青瓜影视">
    <script src="./js/clipboard.min.js"></script>
    <link rel="stylesheet" type="text/css" href="./css/style.css?v=7" />
</head>

<body>
    <div class="bgImg">
    </div>
    <div style="height:1px;"></div>
    <div class="logo">
        <img src="./images/logo1.png" />
    </div>
    <div class="mainLeft">
        <img src="./images/iphone.png" />
    </div>
    <div class="mainRight">
        <div class="erweima">
            <img src="./images/erweima.png" />
        </div>
        <div class="bottomtext">最新版：1.0.8 2019-05-19</div>
        <div class="downloadImg">
            <a href="itms-services://?action=download-manifest&url=https://web2.hotbox99.tv/app/test.plist" class="copy">
                <img src="./images/btn-ios.png" />
            </a>
        </div>
        <div class="downloadImg">
            <a href="test.apk" class="copy">
                <img src="./images/btn-android.png" />
            </a>
        </div>
        <div style="height:2rem;"></div>
        <div class="downloadImg">
            <a href="group.php?v=1">
                <img src="./images/btn-potato.png?v=1" />
            </a>
        </div>
        <div class="downloadImg" style="display:none;">
<a href="https://www.chaoxin.com/g/38E057">            
<img src="./images/btn-chaoxin.png" /></a>
        </div>
    </div>
</body>
<div style="display:none;">
    <script type="text/javascript" src="https://s96.cnzz.com/z_stat.php?id=1277594270&web_id=1277594270"></script>
</div>
<script type="text/javascript" src="//js.users.51.la/20043143.js"></script>

</body>
<?php
$f = isset($_GET['f']) ? $_GET['f'] : "";
if ($f) {
    ?>
    <script>
        var clipboard = new ClipboardJS('.copy', {
            text: function() {
                return "qgtv=<?php echo $f; ?>";
            }
        });
    </script>
<?php } ?>

</html>
