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
                        <li> <a href="{{ url('/admin/watermark') }}">水印设置</a> </li>
                        <li class="active"> <a href="{{ url('/admin/shots') }}">截图设置</a> </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <form action="{{url('admin/shots')}}" method="post" id="formid" name="edit-form" class="edit-form">
                                @foreach($cfgs as $v)
                                    @if($v->type == 'switch')
                                        <div class="form-group">
                                            <label class="btn-block" for="{{$v->name}}">{{$v->title}}</label>
                                            <label class="lyear-switch switch-solid switch-primary">
                                                <input name="{{$v->name}}" type="checkbox" @if($v->value==1) checked @endif ><span></span>
                                            </label>
                                            <small class="help-block">变量名：<code>{$site.{{$v->name}}}</code></small>
                                        </div>
                                    @elseif($v->type == 'string')
                                        <div class="form-group">
                                            <label for="{{$v->name}}">{{$v->title}}</label>
                                            <input class="form-control" type="text" id="{{$v->name}}" name="{{$v->name}}" value="{{$v->value}}" >
                                            <small class="help-block">变量名：<code>{$site.{{$v->name}}}</code></small>
                                        </div>
                                    @elseif($v->type == 'number')
                                        <div class="form-group">
                                            <label for="{{$v->name}}">{{$v->title}}</label>
                                            <input class="form-control" type="number" id="{{$v->name}}" name="{{$v->name}}" value="{{$v->value}}" >
                                            <small class="help-block">变量名：<code>{$site.{{$v->name}}}</code></small>
                                        </div>
                                    @endif
                                @endforeach

                                <div class="form-group">
                                    <button type="button" class="btn btn-primary m-r-5 confirm">确 定</button>
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

    <script>
        $('.confirm').on('click',function () {
            $.post($("#formid").attr('action'),$("#formid").serialize(),function (ret) {
                if(ret.code===1) {
                    lightyear.notify(ret.msg+'，页面即将刷新~', 'success', 2000);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    lightyear.notify(ret.msg, 'danger', 2000);
                }
            },'json');
        });
    </script>
    @endsection
@endsection

