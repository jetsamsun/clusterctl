@extends('layouts.layouts')
@section('content')
    @section('css')
    <link href="{{ URL::asset('/lyear/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/lyear/css/materialdesignicons.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/lyear/css/style.min.css') }}" rel="stylesheet">
    <!--标签插件-->
    <link rel="stylesheet" href="{{ URL::asset('/lyear/js/jquery-tags-input/jquery.tagsinput.min.css') }}">
    @endsection


        <div class="row lyear-layout-sidebar-scroll">
            <div class="col-lg-12">
                <div class="card">
                    <ul class="nav nav-tabs page-tabs">
                        <li> <a href="{{ url('/admin/index') }}">基础配置</a> </li>
                        <li> <a href="{{ url('/admin/transet') }}">转码设置</a> </li>
                        <li class="active"> <a href="{{ url('/admin/watermark') }}">水印设置</a> </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <form action="#!" method="post" name="edit-form" class="edit-form">
                                <div class="form-group">
                                    <label for="mark_space">水印间距</label>
                                    <input class="form-control" type="text" id="mark_space" name="mark_space" value="50:10" placeholder="" >
                                    <small class="help-block">变量名：<code>{$site.mark_space}</code></small>
                                </div>
                                <div class="form-group">
                                    <label class="btn-block" for="mark_zs">左上水印</label>
                                    <label class="lyear-switch switch-solid switch-primary">
                                        <input name="mark_zs" type="checkbox" checked ><span></span>
                                    </label>
                                    <small class="help-block">变量名：<code>{$site.mark_zs}</code></small>
                                </div>
                                <div class="form-group">
                                    <label class="btn-block" for="mark_ys">右上水印</label>
                                    <label class="lyear-switch switch-solid switch-primary">
                                        <input name="mark_ys" type="checkbox"><span></span>
                                    </label>
                                    <small class="help-block">变量名：<code>{$site.mark_ys}</code></small>
                                </div>
                                <div class="form-group">
                                    <label class="btn-block" for="mark_zx">左下水印</label>
                                    <label class="lyear-switch switch-solid switch-primary">
                                        <input name="mark_zx" type="checkbox"><span></span>
                                    </label>
                                    <small class="help-block">变量名：<code>{$site.mark_zx}</code></small>
                                </div>
                                <div class="form-group">
                                    <label class="btn-block" for="mark_yx">右下水印</label>
                                    <label class="lyear-switch switch-solid switch-primary">
                                        <input name="mark_yx" type="checkbox"><span></span>
                                    </label>
                                    <small class="help-block">变量名：<code>{$site.mark_yx}</code></small>
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


    @section('script')
    <script type="text/javascript" src="{{ URL::asset('/lyear/js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('/lyear/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('/lyear/js/perfect-scrollbar.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('/lyear/js/main.min.js') }}"></script>

    <!--消息提示-->
    <script type="text/javascript" src="{{ URL::asset('/lyear/js/bootstrap-notify.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('/lyear/js/lightyear.js') }}"></script>

    <!--标签插件-->
    <script src="{{ URL::asset('/lyear/js/jquery-tags-input/jquery.tagsinput.min.js') }}"></script>
    @endsection
@endsection

