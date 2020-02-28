<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Http\Controllers\AdminController;
use App\Models\UserInfo;
use App\Models\UserWithdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawController extends AdminController
{
    public function withdraw(){
        return view('withdraw.list');
    }
    public function getWithdrawList(Request $request){
        $limit = $request->input('limit');
        $uname = $request->input('uname');
        $bankcard = $request->input('bankcard');
        $count = UserWithdraw::select('id');
        if($uname){
            $count = $count->where('uname','like','%'.$uname.'%');
        }
        if($bankcard){
            $count = $count->where('bankcard','like','%'.$bankcard.'%');
        }
        $count = $count->where('is_visible',1)->count();

        $dataTmp = UserWithdraw::select('id','uid','uname','bankcard','cash_asset',
            'status','content','time');
        if($uname){
            $dataTmp = $dataTmp->where('uname','like','%'.$uname.'%');
        }
        if($bankcard){
            $dataTmp = $dataTmp->where('bankcard','like','%'.$bankcard.'%');
        }
        $dataTmp = $dataTmp->where('is_visible',1)->paginate($limit);

        if($dataTmp){
            $dataTmp = $dataTmp->toArray();
            $dataTmp = $dataTmp['data'];

            foreach($dataTmp as $key=>$value){
                if($value['status']==1){
                    $dataTmp[$key]['status_txt'] = "<font color='red'>提交审核中</font>";
                }elseif($value['status']==2){
                    $dataTmp[$key]['status_txt'] = "<font color='blue'>成功</font>";
                }else{
                    $dataTmp[$key]['status_txt'] = "<font color='red'>拒绝</font>";
                }
                $dataTmp[$key]['time'] = date('Y-m-d H:i:s',$value['time']);
            }
        }
        return response()->json(array('code'=>0,'msg'=>'','count'=>$count,'data'=>$dataTmp));
    }

    public function editstatus(Request $request){
        $id = $request->input('id');
        $status = $request->input('status');
        $withdraw = UserWithdraw::select('uid','status','cash_asset')->where('id',$id)->first();
        if($withdraw['status'] != 1){
            return response()->json(array('status'=>$withdraw['status']));
        }

        $data = UserInfo::select('uid','asset','residual_asset','cash_asset','frozen_asset')
            ->where(['uid'=>$withdraw['uid']])->first();
        DB::beginTransaction();
        try{
            if($status==2){
                $reg = DB::table('user_info')->where('uid',$withdraw['uid'])->update(array(
                    'frozen_asset'=>$data["frozen_asset"]-$withdraw["cash_asset"],
                    'cash_asset'=>$data["cash_asset"]+$withdraw["cash_asset"]
                ));
            }else{
                $reg = DB::table('user_info')->where('uid',$withdraw['uid'])->update(array(
                    'frozen_asset'=>$data["frozen_asset"]-$withdraw["cash_asset"],
                    'residual_asset'=>$data["residual_asset"]+$withdraw["cash_asset"]
                ));
            }
            if(!$reg)
            {
                throw new \Exception("用户资产更新失败");
                Log::error("UID--".$withdraw["uid"]."用户资产更新失败"."---".$withdraw["cash_asset"]);
            }
            $reg = DB::table('user_withdraw')->where('id',$id)->update(array('status'=>$status));
            if(!$reg)
            {
                throw new \Exception("用户提现记录更新失败");
                Log::error("withdrawID--".$id."用户提现记录更新失败"."---".$status);
            }
            DB::commit();

            return response()->json(array('status'=>1));

        }catch (\Exception $e){

            DB::rollback();//事务回滚
            return response()->json(array('status'=>0));
        }
    }
}
