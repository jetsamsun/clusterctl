@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒。
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>筛选新增表单</legend>
    </fieldset>

    <form class="layui-form" action="/admin/screenotype/addscreenotype" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>条件名称</label>
            <div class="layui-input-block">
                <input type="text" name="otypename" lay-verify="required" autocomplete="off" placeholder="请输入分类名称" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>分类</label>
            <div class="layui-input-block">
                <select name="otype" lay-filter="myselect"  lay-verify="required">
                    <option value=""></option>
                    <option  value="1">明星</option>
                    <option  value="5">排行</option>
                    <option  value="10">其他</option>
                </select>
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
        layui.use('form', function(){
            var form = layui.form,$ = layui.jquery;
            //点击图片
            layui.use('upload', function(){
                var $ = layui.jquery,upload = layui.upload;
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
                                window.location.href = "/admin/screenotype";
                            }else{
                                layer.msg(res.msg, {icon: 2, anim: 6, time: 1000});
                            }
                        }
                    });
                    return false;
                });
            });
        });
    </script>
    @endsection
@endsection