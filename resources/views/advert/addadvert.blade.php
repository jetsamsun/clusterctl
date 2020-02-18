@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒。
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>广告新增表单</legend>
    </fieldset>

    <form class="layui-form" action="/admin/advert/addAdvert" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" lay-verify="required" autocomplete="off" placeholder="请输入广告标题" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>跳转链接 </label>
            <div class="layui-input-block">
                <input type="text" name="url" lay-verify="required" autocomplete="off" placeholder="请输入广告跳转链接" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>广告图片</label>
            <div class="layui-input-block">
                <div class="col-lg-2">
                    <span id="showimg">
                    </span>
                    <a href="javascript:;" id="img">
                        <img onerror="this.src='{{asset("assets/images/placeholder.jpg")}}'" src="{{asset('assets/images/placeholder.jpg')}}" data-url="" style="width:auto;max-height:55px;" class="listpic" alt="列表图">
                    </a>
                </div>
                <input type="hidden" lay-verify="required" name="imgval" id="imgval">
                <p id="demoText"></p>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>分类</label>
            <div class="layui-input-block">
                <select name="otype" lay-filter="myselect"  lay-verify="required">
                    <option value=""></option>
                    <option  value="1">播放器</option>
                    <option  value="2">播放列表</option>
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

                //普通图片上传
                var uploadInst = upload.render({
                    elem: '#img'
                    ,url: '/admin/advert/uploadImg'
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
                });
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
                                window.location.href = "/admin/advert";
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