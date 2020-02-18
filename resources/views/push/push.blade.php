@extends('layouts.layouts')
@section('content')
<blockquote class="layui-elem-quote layui-text">
    鉴于小伙伴的普遍反馈，先温馨提醒。
</blockquote>
<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
    <legend>IOS推送</legend>
</fieldset>
<form class="layui-form" action="{{ url('/admin/addPush') }}" method="post">
    <div class="layui-form-item layui-form-text">
        <label class="layui-form-label">
            <font color="red">* </font>推送内容：
        </label>
        <div class="layui-input-block">
            <textarea placeholder="" name="push_content" lay-verify="required" class="layui-textarea"></textarea>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn" lay-submit="" lay-filter="submit">立即提交</button>
            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
    </div>
</form>
@section('script')
<script>
    //监听提交
    layui.use('form', function(){
        var form = layui.form,$ = layui.jquery;
        return false;
    });
</script>
@endsection
@endsection