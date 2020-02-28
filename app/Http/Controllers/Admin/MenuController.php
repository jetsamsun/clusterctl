<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\MenuInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends AdminController
{
    public function getMenus(){
        $dataTmp = MenuInfo::select('menuid','menuname','icon','uri','pid','sort','is_show')
            ->where(['pid'=>0,'is_visible'=>1])
            ->orderBy('sort','asc')->get()->toArray();
        $res = array();
        foreach($dataTmp as $key=>$value){
            $res = MenuInfo::select('menuid','menuname','icon','uri','pid','sort','is_show')
                ->where(['pid'=>$value['menuid'],'is_visible'=>1])
                ->orderBy('sort','asc')->get()->toArray();
            $dataTmp[$key]['son'] = $res;
            $str_p_uri = '';
            foreach($res as $v){
                $str_p_uri.= $v['uri'];
            }
            $dataTmp[$key]['str_p_uri'] = $str_p_uri;
        }
        return $dataTmp;
    }
}
