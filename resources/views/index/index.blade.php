@extends('layouts.layouts')
@section('content')
    <p class="layui-elem-quote">统计注册人数，VIP人数，在线人数。</p>
    <blockquote class="layui-elem-quote layui-text">
        统计方式自定义
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>统计集合演示</legend>
    </fieldset>
    <form class="layui-form layui-form-pane" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">总人数</label>
            <div class="layui-input-inline">
                <input type="text" style="text-align: center;" value="{{ $sumcount }}" disabled lay-verify="required" placeholder="请输入" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">VIP人数</label>
            <div class="layui-input-inline">
                <input type="text" style="text-align: center;" value="{{ $vipcount }}" disabled lay-verify="required" placeholder="请输入" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">在线人数</label>
            <div class="layui-input-inline">
                <input type="text" style="text-align: center;" value="{{ $onlinecount }}" disabled lay-verify="required" placeholder="请输入" autocomplete="off" class="layui-input">
            </div>
        </div>
    </form>
    @section('script')
    <script>
        //Demo
        layui.use('form', function(){
            var form = layui.form;

            //监听提交
            form.on('submit(formDemo)', function(data){
                layer.msg(JSON.stringify(data.field));
                return false;
            });
        });
    </script>
    @endsection
@endsection