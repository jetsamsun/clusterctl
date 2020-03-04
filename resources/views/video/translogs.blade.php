@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒。
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>转码日志</legend>
    </fieldset>

    <div class="layui-form-item layui-form-text" style="margin-left: 30px;">
        @foreach($logs as $log)
            <p style="line-height: 30px">{{$log}}</p>
        @endforeach
    </div>
    <div class="layui-form-item" style="margin-left: -80px;">
        <div class="layui-input-block">
            <button type="button" class="layui-btn" onclick="javascript:history.back(-1);return false;">返 回</button>
        </div>
    </div>

    @section('script')
    <script>
        layui.use('form', function(){
            var form = layui.form,$ = layui.jquery;
        });
    </script>
    @endsection
@endsection