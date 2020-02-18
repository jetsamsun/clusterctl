@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒。
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>VIP 金额</legend>
    </fieldset>

    <form class="layui-form" action="{{ url('/admin/vip') }}" method="post">
        <input type="hidden" name="vip_id" value="{{ $vip['vip_id'] }}" >
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>月卡金额</label>
            <div class="layui-inline">
                <input type="text" name="vip_month_money" lay-verify="required" value="{{ $vip['vip_month_money'] }}" autocomplete="off" placeholder="请输入月卡金额" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>季卡金额</label>
            <div class="layui-inline">
                <input type="text" name="vip_season_money" lay-verify="required" value="{{ $vip['vip_season_money'] }}" autocomplete="off" placeholder="请输入季卡金额" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>年卡金额</label>
            <div class="layui-inline">
                <input type="text" name="vip_year_money" lay-verify="required" value="{{ $vip['vip_year_money'] }}" autocomplete="off" placeholder="请输入年卡金额" class="layui-input">
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
                            layer.msg(res.msg, {icon: 1, time: 1000});
                            window.location.href = "/admin/vip";
                        }else{
                            layer.msg(res.msg, {icon: 2, anim: 6, time: 1000});
                        }
                    }
                });
                return false;
            });
        });
        //点击图片
        layui.use('upload', function(){
            var $ = layui.jquery,upload = layui.upload;

            //普通图片上传
            var uploadInst = upload.render({
                elem: '#img'
                ,url: '/admin/star/uploadStarImg'
                ,size: 500
                ,exts: 'jpg|png|jpeg'
                ,multiple: true
                ,before: function(obj){
                    //预读本地文件示例，不支持ie8
                    obj.preview(function(index, file, result){
                        $('#showimg').html('<img style="width:55px;height:55px;" src="'+result+'">');
                        //$('#showimg').attr('src', result); //图片链接（base64）
                    });
                }
                ,done: function(res){
                    //如果上传失败
                    if(res.code > 0){
                        return layer.msg('上传失败');
                    }
                    //上传成功
                    if(res.code ==0){
                        $('#imgval').val(res.data.src);
                        return layer.msg('上传成功');
                    }
                }
//                ,error: function(){
//                    //演示失败状态，并实现重传
//                    var demoText = $('#demoText');
//                    demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-mini demo-reload">重试</a>');
//                    demoText.find('.demo-reload').on('click', function(){
//                        uploadInst.upload();
//                    });
//                }
            });
        });
    </script>
    @endsection
@endsection