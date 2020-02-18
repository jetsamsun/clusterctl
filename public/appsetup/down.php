<?php
$agent = strtolower($_SERVER['HTTP_USER_AGENT']); 
$iphone = (strpos(strtolower($agent), 'iphone')) ? true : false; 
$ipad = (strpos(strtolower($agent), 'ipad')) ? true : false; 
$android = (strpos(strtolower($agent), 'android')) ? true : false; 
$url = "./test.apk";
if($iphone || $ipad)
{
    $url = "./install.php"; 
}
header("Location: " . $url);
exit;
?>