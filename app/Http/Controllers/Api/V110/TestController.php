<?php
namespace App\Http\Controllers\Api\V110;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends  ApiController{
    /**
     * @param Request $request
     * @return \App\Http\Controllers\json
     *          测试加密签名
     *          /api/v110/test/signature
     */
    public function signature(Request $request){
        $randomstr = $request->input('randomstr');
        $timestamp = $request->input('timestamp');
        $key = "lutube110";

        $sign=MD5($randomstr.$timestamp.$key);

        if(time()>intval($timestamp)+300){
            return $this->ajaxMessage(100,"请求过期");
        }

        return $sign;
    }
}