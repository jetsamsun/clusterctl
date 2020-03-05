@extends('layouts.layouts')
@section('content')
    @section('css')
        <link href="{{ URL::asset('/lyear/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ URL::asset('/lyear/css/materialdesignicons.min.css') }}" rel="stylesheet">
        <link href="{{ URL::asset('/lyear/css/style.min.css') }}" rel="stylesheet">
    @endsection

    <!--页面主要内容-->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <ul class="nav nav-tabs page-tabs">
                    <li> <a href="{{ url('/admin/index') }}">基础配置</a> </li>
                    <li class="active"> <a href="{{ url('/admin/transet') }}">转码设置</a> </li>
                    <li> <a href="{{ url('/admin/watermark') }}">水印设置</a> </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">

                        <form action="#!" method="post" name="edit-form" class="edit-form">
                            <div class="form-group">
                                <label for="trans_mode">转码方式</label>
                                <div class="form-controls">
                                    <select name="trans_mode" class="form-control">
                                        <option value="ABC">极速转码</option>
                                        <option value="ICBC" selected>速度优先</option>
                                        <option value="BOC">均衡设置</option>
                                        <option value="CCB">画质优先</option>
                                    </select>
                                </div>
                                <small class="help-block">变量名：<code>{$site.trans_mode}</code></small>
                            </div>
                            <div class="form-group">
                                <label for="trans_ts_mask">Ts伪装</label>
                                <input class="form-control" type="text" id="trans_ts_mask" name="trans_ts_mask" value="" placeholder="Ts伪装成其他文件，如：jpg，该功能只在开启m3u8防盗后有效" >
                                <small class="help-block">变量名：<code>{$site.trans_ts_mask}</code></small>
                            </div>
                            <div class="form-group">
                                <label for="trans_ts_space">Ts时长</label>
                                <input class="form-control" type="number" id="trans_ts_space" name="trans_ts_space" value="180" placeholder="" >
                                <small class="help-block">变量名：<code>{$site.trans_ts_space}</code></small>
                            </div>
                            <div class="form-group">
                                <label for="trans_m3u8">M3U8后缀</label>
                                <input class="form-control" type="text" id="trans_m3u8" name="trans_m3u8" value="mmm.m3u8" placeholder="" >
                                <small class="help-block">变量名：<code>{$site.trans_m3u8}</code></small>
                            </div>
                            <div class="form-group">
                                <label for="trans_default_size">默认尺寸</label>
                                <div class="controls-box">
                                    <label class="lyear-checkbox checkbox-inline checkbox-primary">
                                        <input type="checkbox" name="trans_default_size[]"><span>2160p：3840x2160</span>
                                    </label>
                                    <label class="lyear-checkbox checkbox-inline checkbox-primary">
                                        <input type="checkbox" name="trans_default_size[]"><span>1440p：2560x1440</span>
                                    </label>
                                    <label class="lyear-checkbox checkbox-inline checkbox-primary">
                                        <input type="checkbox" name="trans_default_size[]"><span>1080p：1920x1080</span>
                                    </label>
                                    <label class="lyear-checkbox checkbox-inline checkbox-primary">
                                        <input type="checkbox" name="trans_default_size[]"><span>720p：1280x720</span>
                                    </label>
                                    <label class="lyear-checkbox checkbox-inline checkbox-primary">
                                        <input type="checkbox" name="trans_default_size[]"><span>480p：854x480</span>
                                    </label>
                                    <label class="lyear-checkbox checkbox-inline checkbox-primary">
                                        <input type="checkbox" name="trans_default_size[]" checked><span>360p：640x360</span>
                                    </label>
                                    <label class="lyear-checkbox checkbox-inline checkbox-primary">
                                        <input type="checkbox" name="trans_default_size[]"><span>240p：426x240</span>
                                    </label>
                                </div>
                                <small class="help-block">变量名：<code>{$site.trans_default_size}</code></small>
                            </div>
                            <div class="form-group">
                                <label class="btn-block" for="trans_secret_on">是否加密</label>
                                <label class="lyear-switch switch-solid switch-primary">
                                    <input name="trans_secret_on" type="checkbox" checked ><span></span>
                                </label>
                                <small class="help-block">变量名：<code>{$site.trans_secret_on}</code></small>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary m-r-5">确 定</button>
                                <button type="button" class="btn btn-default" onclick="javascript:history.back(-1);return false;">返 回</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--End 页面主要内容-->

    @section('script')
        <script type="text/javascript" src="{{ URL::asset('/lyear/js/jquery.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('/lyear/js/bootstrap.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('/lyear/js/perfect-scrollbar.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('/lyear/js/main.min.js') }}"></script>

        <!--消息提示-->
        <script type="text/javascript" src="{{ URL::asset('/lyear/js/bootstrap-notify.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('/lyear/js/lightyear.js') }}"></script>
    @endsection
@endsection