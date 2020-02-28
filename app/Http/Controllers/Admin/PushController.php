<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class PushController extends AdminController
{
    public function push()
    {
        return view('push.push');
    }
    public function addPush(Request $request)
    {
        if ($request->isMethod('post')) {
            $push_content = $request->input('push_content');
            //推送目标设备号(测试环境和正式环境不一样)
            $users =  UserInfo::select('devicetoken','uid','pushcount')->where('devicetoken', '!=', '')->get()->toArray();
            echo "一共" . count($users) . "个用户";
            echo "<br/>";
            if ($users) {
                //证书路径
                $pem = app_path() . '/Common/iosPush/apns_product.pem';
                //测试服务器
                $apnsHost = 'ssl://gateway.sandbox.push.apple.com:2195';
                //正式服务器
                //$apnsHost = 'ssl://gateway.push.apple.com:2195';
                $ctx = stream_context_create();
                stream_context_set_option($ctx, "ssl", "local_cert", $pem);
                $pass = "";     //如果有密码的话
                stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
                $fp = stream_socket_client($apnsHost, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
                if (!$fp) {
                    echo "Failed to connect $err $errstr";
                    exit;
                }
                echo "链接苹果服务器成功<br/>";
                $content = $push_content;
                if (ob_get_level() == 0) ob_start();
                foreach ($users as $userss) {
                    try{
                        $pushCount = $userss['pushcount']+1;
                        $body = array("aps" => array("alert" => $content, "badge" => $pushCount, "sound" => 'default'), 'url' => '');
                        $payload = json_encode($body);
                        $msg = chr(0) . pack("n", 32) . pack("H*", str_replace(' ', '', $userss['devicetoken'])) . pack("n", strlen($payload)) . $payload;
                        fwrite($fp, $msg);
                        echo $userss['uid']."发送成功<br/>";
                        DB::table("user_info")->where('uid',$userss['uid'])->update(array('pushcount'=>$pushCount));
                        echo str_pad('',4096)."\n";
                        ob_flush();
                        flush();
                        // sleep(1);
                    } catch (Exception $e){
                        echo $userss['uid']."发送失败<br/>";
                    }
                }
                fclose($fp);
                ob_end_flush();
                echo "<a href='".url('/admin/push')."'>返回</a>";
            }
        }
    }
}
