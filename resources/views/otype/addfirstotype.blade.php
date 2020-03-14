@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        <a class="layui-btn layui-btn-sm layui-btn-primary" href="javascript:history.back();" >返回</a>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>导航分类新增表单</legend>
    </fieldset>

    <form class="layui-form" action="/admin/firstotype/addfirstotype" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>分类名称</label>
            <div class="layui-input-block">
                <input type="text" name="otypename" lay-verify="required" autocomplete="off" placeholder="请输入分类名称" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item" style="display: none">
            <label class="layui-form-label"><font color="red">* </font>分类</label>
            <div class="layui-input-block">
                <select name="otype" lay-filter="myselect"  lay-verify="required">
                    <option value=""></option>
                    <option  value="1">MV</option>
                    <option  value="5" selected>视频</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">广告图</label>
            <div class="layui-input-block">
                <div class="col-lg-2">
                    <span id="showimg">
                    </span>
                    <a href="javascript:;" id="img">
                        <img onerror="this.src='{{asset("assets/images/placeholder.jpg")}}'" src="{{asset('assets/images/placeholder.jpg')}}" data-url="" style="width:auto;max-height:55px;" class="listpic" alt="列表图">
                    </a>
                </div>
                {{--<input type="hidden"  name="imgval[]" value="" id="imgval">--}}
                <p id="demoText" style="display: none;">

                </p>
            </div>
        </div>
        <div class="layui-form-item">
            <table class="layui-table">
                <thead>
                    <tr>
                        <th>标题</th>
                        <th width="150px;">链接类型<br>内部链接:<font color="red">1</font> 外部链接:<font color="red">2</font></th>
                        <th>安卓链接</th>
                        <th>ios链接</th>
                    </tr>
                </thead>
                <tbody id="info">
                </tbody>
            </table>
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
        //点击图片
        layui.use('upload', function() {
            var $ = layui.jquery, upload = layui.upload;
            //普通图片上传
            var uploadInst = upload.render({
                elem: '#img'
                , url: '/admin/vidotype/uploadOtypeImg'
                , size: 500
                , exts: 'jpg|png|jpeg'
                , multiple: true
                , before: function (obj) {
                    if(count<3){
                        //预读本地文件示例，不支持ie8
                        obj.preview(function (index, file, result) {
                            $('#showimg').append('<img onclick="del_img(this)" name="'+i+'" style="width:55px;height:55px;" src="' + result + '">');
                            //$('#showimg').attr('src', result); //图片链接（base64）
                        });
                    }
                }
                , done: function (res) {
                    //如果上传失败
                    if (res.code > 0) {
                        return layer.msg('上传失败');
                    }
                    //上传成功
                    if (res.code == 0) {
                        if(count>=3){
                            layer.msg('广告图只能上传三张', {icon: 2, anim: 6, time: 1000});
                            return false;
                        }
                        $('#demoText').append('<input type="hidden" id="img'+i+'" name="imgval[]" value="'+res.data.src+'" >');
                        $('#info').append('<tr id="tr'+i+'">' +
                                '<td>' +
                                '<input type="text" name="title[]" autocomplete="off" placeholder="请输入图片标题" class="layui-input">' +
                                '</td>' +
                                '<td>' +
                                '<input type="number" name="urlotype[]" autocomplete="off" class="layui-input">'+
                                '</td>' +
                                '<td>' +
                                '<input type="text" name="url[]" lay-verify="required" autocomplete="off" placeholder="请输入安卓链接" class="layui-input">' +
                                '</td>' +
                                '<td>' +
                                '<input type="text" name="ios_url[]" lay-verify="required" autocomplete="off" placeholder="请输入ios链接" class="layui-input">' +
                                '</td></tr>');
                        i++;
                        count++;
                        return layer.msg('上传成功');
                    }
                }
            });
        });
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
                            window.location.href = "/admin/firstotype";
                        }else{
                            layer.msg(res.msg, {icon: 2, anim: 6, time: 1000});
                        }
                    }
                });
                return false;
            });
        });
        function del_img(obj){
            name = $(obj).attr('name');
            obj.remove();
            $('#tr'+name).remove();
            $('#img'+name).remove();
            count--;
        }
    </script>
    @endsection
@endsection