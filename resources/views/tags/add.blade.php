@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        <a class="layui-btn layui-btn-sm layui-btn-primary" href="javascript:history.back();" >返回</a>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>新增表单</legend>
    </fieldset>

    <form class="layui-form" action="/admin/addtags" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>标签名称</label>
            <div class="layui-input-block">
                <input type="text" name="otypename" lay-verify="required" autocomplete="off" placeholder="请输入标签名称" class="layui-input">
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
        var i = 1;
        var count = 0;

        layui.use('form', function(){
            var form = layui.form,$ = layui.jquery;

            //监听提交
            form.on('submit(submit)', function(data){
//                layer.msg(JSON.stringify(data.field));
//                return false;
                $.ajax({
                    url: data.form.action,
                    type: data.form.method,
                    data: data.field,
                    success: function (res) {
                        if(res.code == 1){
                            layer.msg(res.msg, {icon: 1, time: 1000});
                            window.location.href = "/admin/tags";
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