<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'v1','namespace' => 'Api'], function () {
    // user
    Route::any('user/randomUser', 'UserController@randomUser');
    Route::any('user/getUserInfo', 'UserController@getUserInfo');
    Route::any('user/bindUserInfo', 'UserController@bindUserInfo');
    Route::any('user/updatePwd', 'UserController@updatePwd');
    Route::any('user/login', 'UserController@login');
    Route::any('user/forgetPwd', 'UserController@forgetPwd');
    Route::any('user/confirmPwd', 'UserController@confirmPwd');
    Route::any('user/confirmPwd', 'UserController@confirmPwd');
    Route::any('user/getUserShareQrCode', 'UserController@getUserShareQrCode');
    // otype
    Route::any('otype/getListOtype', 'OtypeController@getListOtype');
    Route::any('otype/getVideoOtype', 'OtypeController@getVideoOtype');
    Route::any('otype/getScreenOtype', 'OtypeController@getScreenOtype');

    // star
    Route::any('star/getStarList', 'StarController@getStarList');
    Route::any('star/getStarInfoBySID', 'StarController@getStarInfoBySID');

    // collect
    Route::any('collect/getCollectList', 'CollectController@getCollectList');
    Route::any('collect/addCollect', 'CollectController@addCollect');
    Route::any('collect/delCollect', 'CollectController@delCollect');

    // video
    Route::any('video/getVideoList', 'VideoController@getVideoList');
    Route::any('video/getVideoDetails', 'VideoController@getVideoDetails');
    Route::any('video/getRandomVideoList', 'VideoController@getRandomVideoList');
    Route::any('video/execute', 'VideoController@execute');

    // msg
    Route::any('msg/subMsg', 'MsgController@subMsg');

    Route::any('order/subOrder', 'OrderController@subOrder');
    Route::any('order/getOrderList', 'OrderController@getOrderList');
    Route::any('order/payOrder', 'OrderController@payOrder');
    Route::any('order/callback', 'OrderController@callback');
    Route::any('order/repayfNotifyurl', 'OrderController@repayfNotifyurl');
    Route::any('order/repayfCallbackurl', 'OrderController@repayfCallbackurl');
    Route::any('order/huoshanCallBack', 'OrderController@huoshanCallBack');

    Route::any('app/getAppCon', 'AppController@getAppCon');
    Route::any('app/share', 'AppController@share');
    Route::any('app/getVersion', 'AppController@getVersion');
    Route::any('app/getStatus', 'AppController@getStatus');
});

Route::group(['prefix' => 'v110','namespace' => 'Api\V110'], function () {
    // test
    Route::any('test/signature', 'TestController@signature');
    // video
    Route::any('video/getVideoList', 'VideoController@getVideoList');
    Route::any('video/getVideoDetails', 'VideoController@getVideoDetails');
    Route::any('video/doDown', 'VideoController@doDown');
    Route::any('video/doFlag', 'VideoController@doFlag');
    Route::any('video/doLook', 'VideoController@doLook');
    Route::any('video/getLookLogs', 'VideoController@getLookLogs');
    // otype
    Route::any('otype/getListOtype', 'OtypeController@getListOtype');
    // star
    Route::any('star/getStarInfoBySID', 'StarController@getStarInfoBySID');
    // user
    Route::any('user/randomUser', 'UserController@randomUser');
    Route::any('user/doSafeCode', 'UserController@doSafeCode');
    Route::any('user/getUserInfo', 'UserController@getUserInfo');
    Route::any('user/doUserMobile', 'UserController@doUserMobile');
    Route::any('user/getUserQrcode', 'UserController@getUserQrcode');
    Route::any('user/getUserTokenByRandomnum', 'UserController@getUserTokenByRandomnum');
    Route::any('user/doLogin', 'UserController@doLogin');
    Route::any('user/subInviteCode', 'UserController@subInviteCode');
    Route::any('user/switchNumber', 'UserController@switchNumber');
    Route::any('user/getVerifycode', 'UserController@getVerifycode');
    Route::any('user/setPushID', 'UserController@setPushID');
    Route::any('user/setPushClear', 'UserController@setPushClear');
    Route::any('user/getInviteUrl', 'UserController@getInviteUrl');
    Route::any('user/getBanner', 'UserController@getBanner');
    Route::any('user/getTopBanner', 'UserController@getTopBanner');
    Route::any('user/getStartBanner', 'UserController@getStartBanner');
    // msg
    Route::any('msg/subSeekVideo', 'MsgController@subSeekVideo');
    Route::any('msg/subVideoTrouble', 'MsgController@subVideoTrouble');

    Route::any('app/share', 'AppController@share');
    Route::any('app/doClick', 'AppController@doClick');
    Route::any('app/getVip', 'AppController@getVip');

    Route::any('asset/getUserAssetInfo', 'AssetController@getUserAssetInfo');
    Route::any('asset/subUserWithdraw', 'AssetController@subUserWithdraw');
    Route::any('asset/getUserWithdraw', 'AssetController@getUserWithdraw');
    Route::any('asset/getUserAssetDetail', 'AssetController@getUserAssetDetail');

    Route::any('order/subOrder', 'OrderController@subOrder');
    Route::any('order/payOrder', 'OrderController@payOrder');
    Route::any('order/pay2Order', 'OrderController@pay2Order');
    Route::any('order/payorderdetail', 'OrderController@payorderdetail');
    Route::any('order/callback', 'OrderController@callback');
    Route::any('order/payOrder2', 'OrderController@payOrder2');
    Route::any('order/pay3Order', 'OrderController@pay3Order');
});