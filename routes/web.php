<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return redirect("/upload/share.html");
});
Route::get('/admintv', function () {
    if(!session('id')){
        return view('login');
    }
    //return view('index.index');
    return redirect("/admin/index");
});
Route::group(['namespace' => 'Admin','prefix' => 'admin'], function () {
    // index
    Route::any('index','IndexController@index');
    Route::any('changePwd','IndexController@changePwd');

    // login
    Route::any('login','LoginController@login');
    Route::any('loginout', 'LoginController@loginout');
    Route::any('login/captchamews', 'LoginController@captchamews');

    // video
    Route::any('video', 'VideoController@video');
    Route::any('video/getVideoList', 'VideoController@getVideoList');
    Route::any('addVideo', 'VideoController@addVideo');
    Route::any('getVideoOtype', 'VideoController@getVideoOtype');
    Route::any('getVideoOtype3', 'VideoController@getVideoOtype3');
    Route::any('uploadVideoImg', 'VideoController@uploadVideoImg');
    Route::any('uploadVideoFile', 'VideoController@uploadVideoFile');
    Route::any('video/delvideo', 'VideoController@delvideo');
    Route::any('video/editvideo/{id}', 'VideoController@editvideo');
    Route::any('video/videofree', 'VideoController@videofree');
    Route::any('video/clickfree', 'VideoController@clickfree');

    // star
    Route::any('star', 'StarController@star');
    Route::any('star/getStarList', 'StarController@getStarList');
    Route::any('addStar', 'StarController@addStar');
    Route::any('star/uploadStarImg', 'StarController@uploadStarImg');
    Route::any('star/delstar', 'StarController@delstar');
    Route::any('star/editstar/{id}', 'StarController@editstar');
    // user
    Route::any('user', 'UserController@user');
    Route::any('user/getUserList', 'UserController@getUserList');
    Route::any('user/vip', 'UserController@vip');
    Route::any('user/tuiguang', 'UserController@tuiguang');

    // 分类
    Route::any('firstotype', 'OtypeController@firstotype');
    Route::any('firstotype/getFirstOtypeList', 'OtypeController@getFirstOtypeList');
    Route::any('firstotype/addfirstotype', 'OtypeController@addfirstotype');
    Route::any('firstotype/delfirstotype', 'OtypeController@delfirstotype');
    Route::any('firstotype/editfirstotype/{id}', 'OtypeController@editfirstotype');

    Route::any('advert', 'OtypeController@advert');
    Route::any('advert/getadvert', 'OtypeController@getadvert');
    Route::any('advert/addadvert', 'OtypeController@addadvert');
    Route::any('advert/deladvert', 'OtypeController@deladvert');
    Route::any('advert/editadvert/{id}', 'OtypeController@editadvert');

    Route::any('vidotype', 'OtypeController@videootype');
    Route::any('vidotype/getVideoOtypeList', 'OtypeController@getVideoOtypeList');
    Route::any('vidotype/addvideootype', 'OtypeController@addvideootype');
    Route::any('vidotype/delvideootype', 'OtypeController@delvideootype');
    Route::any('vidotype/editvideootype/{id}', 'OtypeController@editvideootype');
    Route::any('vidotype/uploadOtypeImg', 'OtypeController@uploadOtypeImg');

    Route::any('screenotype', 'OtypeController@screenotype');
    Route::any('screenotype/getScreenOtypeList', 'OtypeController@getScreenOtypeList');
    Route::any('screenotype/addscreenotype', 'OtypeController@addscreenotype');
    Route::any('screenotype/delscreenotype', 'OtypeController@delscreenotype');
    Route::any('screenotype/editscreenotype/{id}', 'OtypeController@editscreenotype');

    Route::any('screenotype/screendetailotype', 'OtypeController@screendetailotype');
    Route::any('screenotype/getScreenDetailOtypeList', 'OtypeController@getScreenDetailOtypeList');
    Route::any('screenotype/addscreendetailotype', 'OtypeController@addscreendetailotype');
    Route::any('screenotype/delscreendetailotype', 'OtypeController@delscreendetailotype');
    Route::any('screenotype/editscreendetailotype/{id}', 'OtypeController@editscreendetailotype');


    Route::any('msg', 'MsgController@msg');
    Route::any('msg/getMsgList', 'MsgController@getMsgList');
    Route::any('seek', 'MsgController@seek');
    Route::any('seek/getSeekList', 'MsgController@getSeekList');
    Route::any('seek/delseek', 'MsgController@delseek');
    Route::any('trouble', 'MsgController@trouble');
    Route::any('trouble/getTroubleList', 'MsgController@getTroubleList');
    Route::any('trouble/delTrouble', 'MsgController@delTrouble');

    //app
    Route::any('app', 'AppController@app');
    Route::any('app/getAppList', 'AppController@getAppList');
    Route::any('app/uploadImg', 'AppController@uploadImg');
    Route::any('app/editapp/{id}', 'AppController@editapp');

    Route::any('withdraw', 'WithdrawController@withdraw');
    Route::any('withdraw/getWithdrawList', 'WithdrawController@getWithdrawList');
    Route::any('withdraw/editstatus', 'WithdrawController@editstatus');

    Route::any('vip', 'VipController@vip');

    Route::any('push', 'PushController@push');
    Route::any('addPush', 'PushController@addPush');


    Route::any('advert', 'AdvertController@advert');
    Route::any('advert/getAdvertList', 'AdvertController@getAdvertList');
    Route::any('advert/addAdvert', 'AdvertController@addAdvert');
    Route::any('advert/uploadImg', 'AdvertController@uploadImg');
    Route::any('advert/editAdvert/{id}', 'AdvertController@editAdvert');
    Route::any('advert/delAdvert', 'AdvertController@delAdvert');

    // 临时测试
    Route::any('video2', 'Video2Controller@video');
    Route::any('video2/getVideoList', 'Video2Controller@getVideoList');
});
