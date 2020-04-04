@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒。
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>编辑表单</legend>
    </fieldset>

    <form class="layui-form" action="{{ url('/admin/editsites/'.$oid) }}" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>用户名</label>
            <div class="layui-input-block">
                <input type="text" name="User" lay-verify="required" autocomplete="off" placeholder="请输入用户名" class="layui-input" value="{{ $data['User'] }}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>Api Key</label>
            <div class="layui-input-block">
                <input type="text" name="Key" lay-verify="required" autocomplete="off" placeholder="请输入key" class="layui-input" value="{{ $data['Key'] }}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>Ip</label>
            <div class="layui-input-block">
                <input type="text" name="Ip" lay-verify="required" autocomplete="off" placeholder="请输入key" class="layui-input" value="{{ $data['Ip'] }}">
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
        var len = $("#info").find("tr").length;
        var i = 1;
        var count = len;

        layui.use('form', function(){
            var form = layui.form,$ = layui.jquery;
            //监听提交
            form.on('submit(submit)', function(data){
                //layer.msg(JSON.stringify(data.field));
                //return false;
                $.ajax({
                    url: data.form.action,
                    type: data.form.method,
                    data: data.field,
                    success: function (res) {
                        if(res.code == 1){
                            layer.msg(res.msg, {icon: 1, time: 1500});

                            setTimeout(function(){
                                window.location.href = "/admin/sites";
                            }, 1000);
                        }else{
                            layer.msg(res.msg, {icon: 2, anim: 6, time: 1000});
                        }
                    }
                });
                return false;
            });
        });
    </script>
    @endsection
@endsection