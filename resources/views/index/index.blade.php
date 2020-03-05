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
                    <li class="active"> <a href="{{ url('/admin/index') }}">基础配置</a> </li>
                    <li> <a href="{{ url('/admin/transet') }}">转码设置</a> </li>
                    <li> <a href="{{ url('/admin/watermark') }}">水印设置</a> </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">

                        <form action="#!" method="post" name="edit-form" class="edit-form">
                            <div class="form-group">
                                <label for="web_site_title">网站URL</label>
                                <input class="form-control" type="text" id="site_url" name="site_url" value="" placeholder="请输入网站URL" >
                                <small class="help-block">变量名：<code>{$site.site_url}</code></small>
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