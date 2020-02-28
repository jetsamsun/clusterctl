<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Vip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VipController extends AdminController
{
    public function vip(Request $request){
        if($request->isMethod('post')){
            $vip_id = $request->input('vip_id');
            $vip_month_money = $request->input('vip_month_money');
            $vip_season_money = $request->input('vip_season_money');
            $vip_year_money = $request->input('vip_year_money');

            $reg = DB::table('vip')->where('vip_id',$vip_id)->update(array(
                'vip_month_money'=>$vip_month_money, 'vip_season_money'=>$vip_season_money,'vip_year_money'=>$vip_year_money
            ));

            if($reg){
                return response()->json(array('code'=>1,'msg'=>"编辑成功"));
            }else{
                return response()->json(array('code'=>0,'msg'=>"编辑失败"));
            }
        }

        $vip = Vip::select('vip_id','vip_month_money','vip_season_money','vip_year_money')
            ->first();

        return view('vip.vip',compact('vip'));
    }
}
