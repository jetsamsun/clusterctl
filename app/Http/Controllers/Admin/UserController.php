<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\UserInfo;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends AdminController
{
    public function user(){
        return view('user.list');
    }
    public function getUserList(Request $request){
        $key = $request->input('key');
        $limit = $request->input('limit');
        $count = UserInfo::select('uid','randomnum','pic','mobile','email','asset','residual_asset','cash_asset','frozen_asset',
            'nickname','vipendtime','os','registertime');
        if($key){
            $count = $count->where(function ($query) use ($key) {
                $query->where('mobile', 'like', "%{$key}%")
                    ->orWhere('email', 'like', "%{$key}%")
                    ->orWhere('randomnum', 'like', "%{$key}%")
                    ->orWhere('uid', $key);
            });
        }
        $count = $count->where('is_visible',1)->count();

        $dataTmp = UserInfo::select('uid','randomnum','pic','mobile','email','asset','residual_asset','cash_asset','frozen_asset',
            'nickname','vipendtime','os','registertime');
        if($key){
            $dataTmp = $dataTmp->where(function ($query) use ($key) {
                $query->where('mobile', 'like', "%{$key}%")
                    ->orWhere('email', 'like', "%{$key}%")
                    ->orWhere('randomnum', 'like', "%{$key}%")
                    ->orWhere('uid', $key);
            });
        }
        $dataTmp = $dataTmp->where('is_visible',1)->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                // 判断是否是vip
                if($value['vipendtime'] && $value['vipendtime']>strtotime(date('Y-m-d H:i:s'))){
                    $dataTmp[$key]['is_vip'] = "<font color='red'> √ </font>";
                    $dataTmp[$key]['vipendtime'] = date('Y-m-d H:i:s',$value['vipendtime']);
                }else{
                    $dataTmp[$key]['is_vip'] = "X";
                    $dataTmp[$key]['vipendtime'] = "";
                }

                $dataTmp[$key]['os'] = $value['os']==1?"Ios":"Andriod";
                $dataTmp[$key]['registertime'] = date('Y-m-d H:i:s',$value['registertime']);
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }

    public function vip(Request $request){
        $uid = $request->input("uid");
        if($request->isMethod("post")){
            $num = $request->input("num");
            $title = "";
            $price = "";
            if($num == 30){
                $title = "全站月卡 CNY$30";
                $price = 30;
                $vipotype = 1;
            }elseif($num == 90){
                $title = "全站季卡 CNY$68";
                $price = 68;
                $vipotype = 5;
            }else{
                $title = "全站年卡 CNY$268";
                $price = 268;
                $vipotype = 10;
            }
            $user = UserInfo::select("vipendtime",'vipotype')->where("uid",$uid)->first();
            $time = date("Y-m-d H:i:s",$user['vipendtime']);
            if($time < date('Y-m-d H:i:s')){
                $time = date('Y-m-d H:i:s');
                $vipotype = $vipotype;
            }else{
                if($vipotype > $user['vipotype']){
                    $vipotype = $vipotype;
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

            $ordernum = $this->msectime();
            $reg = DB::table('order_info')->insert(array(
                "ordernum"=>$ordernum,"payotype"=>3,"title"=>$title,"uid"=>$uid,'num'=>$num,'vipotype'=>$vipotype,'vipstarttime'=>strtotime($time),
                'vipendtime'=>strtotime($time." +$num day"),"price"=>$price,"orderstatus"=>2,
                "createtime"=>strtotime(date("Y-m-d H:i:s")),'is_visible'=>1
            ));

            // 更新用户vip到期时间
            DB::table('user_info')->where("uid",$uid)->update(array(
                'vipendtime'=>strtotime($time." +$num day"),'downcount'=>$downcount,'vipotype'=>$vipotype
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"失败"));
            }

        }
        return view('user.vip',compact('uid'));
    }
    public function tuiguang(Request $request){
        $uid = $request->input("uid");
        $level = UserLevel::select('level_id',"first_level","second_level","third_level","fourth_level")->where("uid",$uid)->first();
//        if($level){
//            $level['first_level'] = $this->userLevel($level['first_level']);
//            $level['second_level']= $this->userLevel($level['second_level']);
//            $level['third_level']= $this->userLevel($level['third_level']);
//            $level['fourth_level']= $this->userLevel($level['fourth_level']);
//        }else{
//            $level['first_level'] = '';
//            $level['second_level']= '';
//            $level['third_level']= '';
//            $level['fourth_level']= '';
//        }
        $res3 = array();
        $user3 = UserLevel::select("third_level")->where("fourth_level",$uid)->get();
        if($user3){
            $user3 = $user3->toArray();
            foreach($user3 as $value){
                $res3[] = $this->userLevel($value['third_level']); // 4级用户
            }
        }
        $res2 = array();
        $user2 = UserLevel::select("second_level")->where("third_level",$uid)->get();
        if($user2){
            $user2 = $user2->toArray();
            foreach($user2 as $value){
                $res2[] = $this->userLevel($value['second_level']); // 3级用户
            }
        }
        $res1 = array();
        $user1 = UserLevel::select('first_level')->where("second_level",$uid)->get();
        if($user1){
            $user1 = $user1->toArray();
            foreach($user1 as $value){
                $res1[] = $this->userLevel($value['first_level']); // 2级用户
            }
        }
        $res0 = array();
        $user0 = UserLevel::select('uid')->where("first_level",$uid)->get();
        if($user0){
            $user0 = $user0->toArray();
            foreach($user0 as $value){
                $res0[] = $this->userLevel($value['uid']); // 1级用户
            }
        }
        return view('user.tuiguang',compact('level','res1','res2','res3','res0'));
    }
}
