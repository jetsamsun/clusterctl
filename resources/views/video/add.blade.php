@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒。
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>视频新增表单</legend>
    </fieldset>

    <form class="layui-form" action="{{ url('/admin/addVideo') }}" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>视频标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" lay-verify="required" autocomplete="off" placeholder="请输入标题" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">展示图</label>
            <div class="layui-input-block">
                <div class="col-lg-2">
                    <span id="showimg">
                    </span>
                    <a href="javascript:;" id="img">
                        <img onerror="this.src='{{asset("assets/images/placeholder.jpg")}}'" src="{{asset('assets/images/placeholder.jpg')}}" data-url="" style="width:auto;max-height:55px;" class="listpic" alt="列表图">
                    </a>
                </div>
                <input type="hidden" name="imgval" id="imgval">
                <p id="demoText"></p>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>视频</label>
            <div class="layui-input-block">
                <span id="video"></span>
                <button type="button" class="layui-btn" id="files">上传视频</button>
                <input type="hidden" lay-verify="required" id="filesval" name="filesval">
            </div>
        </div>

        <div style="display: none">
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label"> m3u8地址：</label>
                <div class="layui-input-block">
                    <textarea placeholder="多个地址用|分割（例如：http://www.480p.m3u8|http://www.720p.m3u8|http://www.1080p.m3u8）" name="play_urls"  class="layui-textarea"></textarea>
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">下载地址：</label>
                <div class="layui-input-block">
                    <textarea placeholder="多个地址用|分割（例如：http://www.480p.mp4|http://www.720p.mp4|http://www.1080p.mp4）" name="download_urls"  class="layui-textarea"></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">类型</label>
                <div class="layui-input-block">
                    <input type="checkbox" name="otype[]" lay-skin="primary" title="MV"  value="1" >
                    <input type="checkbox" name="otype[]" lay-skin="primary" checked title="视频" value="2">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">导航分类</label>
                <div class="layui-input-block">
                    @foreach($firstotype as $value)
                        <input type="checkbox" name="otype2[]" value="{{ $value['oid'] }}"  lay-skin="primary" title="{{ $value['otypename'] }}">
                    @endforeach
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">视频分类</label>
                <div class="layui-input-block">
                    <select name="otype3" lay-filter="myselect" >
                        <option value="0"></option>
                        @foreach($tree as $v)
                            <option  value="{{$v['oid']}}">{{$v['html']}}{{$v['otypename']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">最新/热门</label>
                <div class="layui-input-block">
                    <input type="checkbox" name="secondbestotype[]" lay-skin="primary" title="最新"  value="1" >
                    <input type="checkbox" name="secondbestotype[]" lay-skin="primary" title="热门" value="2">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">番号</label>
                    <div class="layui-input-inline">
                        <input type="text" name="designation"  autocomplete="off" placeholder="请输入番号" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">IMDB</label>
                    <div class="layui-input-inline">
                        <input type="text" name="imdb"  autocomplete="off" placeholder="请输入视频IMDB" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">评分</label>
                    <div class="layui-input-inline">
                        <input type="number" name="score"   autocomplete="off" placeholder="请输入视频评分" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">热度</label>
                    <div class="layui-input-inline">
                        <input type="number" name="hotcount"  autocomplete="off" placeholder="请输入视频热度" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item" pane="">
                <label class="layui-form-label">筛选条件</label>
                <div class="layui-input-block">
                    <table class="layui-table">
                        <colgroup>
                            <col width="100">
                            <col>
                        </colgroup>
                        <tbody>
                        @foreach($screen as $value)
                        <tr>
                            <td>{{ $value['otypename'] }}</td>
                            <td>
                            @foreach($value['son'] as $v)
                                <input type="checkbox" name="screen[]" value="{{ $v['oid'] }}"  lay-skin="primary" title="{{ $v['otypename'] }}">
                            @endforeach
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
            <div class="layui-form-item" pane="">
                <label class="layui-form-label">参演明星</label>
                <div class="layui-input-block">
                    @foreach($star as $value)
                    <input type="checkbox" name="star[]" value="{{ $value['sid'] }}" lay-skin="primary" title="{{ $value['uname'] }}">
                    @endforeach
                </div>
            </div>
        </div>

        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">视频简介</label>
            <div class="layui-input-block">
                <textarea placeholder="请输入内容" name="content" class="layui-textarea"></textarea>
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

//            form.on('select(myselect)', function(data){
//                var otype=data.value;
//                $('#otype2').html('');
//                $('#otype3').html('');
//                $.ajax({
//                    type: 'POST',
//                    url: '/admin/getVideoOtype',
//                    data: {otype:otype},
//                    success: function(e){
//                        $.each(e, function(key, val) {
//                            var option1 = $("<option>").val(val.oid).text(val.otypename);
//
//                            $("#otype2").append(option1);
//                            form.render('select');
//                        });
//                        $("#otype2").get(0).selectedIndex=0;
//                    }
//                });
//                $.ajax({
//                    type: 'POST',
//                    url: '/admin/getVideoOtype3',
//                    data: {otype:otype},
//                    success: function(res){
//                        $.each(res, function(key, val) {
//                            var option1 = $("<option>").val(val.oid).text(val.otypename);
//                            $("#otype3").append(option1);
//                            form.render('select');
//                        });
//                        $("#otype3").get(0).selectedIndex=0;
//                    }
//                });
//            });

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
                            window.location.href = "/admin/video";
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
            var $ = layui.jquery,upload = layui.upload,element = layui.element;

            //普通图片上传
            var uploadInst = upload.render({
                elem: '#img'
                ,url: '/admin/uploadVideoImg'
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

            //指定允许上传的文件类型
            upload.render({
                elem: '#files'
                ,url: '/admin/uploadVideoFile'
                ,accept: 'file' //普通文件
                ,before: function(obj){
                    loading =layer.load(1, {shade: [0.1,'#fff'] });//0.1透明度的白色背景
                }
                ,done: function(res, index, upload){
                    // layer.close(layer.msg());//关闭上传提示窗口
                    console.log(res);
                    if(res.code == 0){ //上传成功
                        if(res.progress == '100'){
                            top.layer.close(loading);//关闭上传提示窗口
                            $("#video").html('<video id="video" width="360" height="202" controls autoplay><source src="'+res.data.src+'" type="video/mp4"></video>');
                            $('#filesval').val(res.data.src);
                            layer.msg(res.msg, {icon: 1, time: 1000});
                        }else{
                            console.log(res);
                            layer.msg('视频已上传'+parseInt(res.progress)+"%");
                        }
                    }
                    //this.error(index, upload,res.info);
                }
                ,error: function(index, upload,info){
                    // layer.close(layer.msg());//关闭上传提示窗口
                    top.layer.close(loading);//关闭上传提示窗口
                    return layer.msg('上传失败');
//                    var tr = demoListView.find('tr#upload-'+ index)
//                            ,tds = tr.children();
//                    tds.eq(2).html('<span style="color: #FF5722;">上传失败.'+info+'</span>');
//                    tds.eq(3).find('.demo-reload').removeClass('layui-hide'); //显示重传
                }
//                ,done: function(res){
//                    console.log(res);
//                    layer.close(loading);
//                    //如果上传失败
//                    if(res.code > 0){
//                        return layer.msg('上传失败');
//                    }
//                    //上传成功
//                    if(res.code ==0){
//                        $("#video").html('<video id="video" width="220" height="140" controls autoplay><source src="'+res.data.src+'" type="video/mp4"></video>');
//                        $('#filesval').val(res.data.src);
//                        layer.msg(res.msg, {icon: 1, time: 1000});
//                    }
//                }
            });
        });
        /*
         上传参数设定
         */
        //多文件列表示例
        duotu = false;
        layui.use('upload', function(){
            var $ = layui.jquery
                    ,upload = layui.upload;

            var load = '';

            //多文件列表示例
            var demoListView = $('#demoList');
            uploadListIns = upload.render({
                elem: '#testList'
                ,url: "/admin/uploadVideoFile"
                ,accept: 'video'
                ,multiple: false
                ,auto: false
                ,bindAction: '#testListAction'
                ,size: 3072000
                ,processData: false
                ,contentType: false
                ,choose: function(obj){
                    console.log(obj);
                    //读取本地文件
                    obj.preview(function(index, file, result){
                        console.log(file);
                        var tr = $(['<tr id="upload-'+ index +'" class="'+file.lastModified+'">'
                            ,'<td>'+ file.name +'</td>'
                            ,'<td>'+ bytesToSize(file.size) +'</td>'
                            ,'<td>等待上传</td>'
                            ,'<td>'
                            ,'<button class="layui-btn layui-btn-mini demo-reload layui-hide">重传</button>'
                            ,'<button class="layui-btn layui-btn-mini layui-btn-danger demo-delete">删除</button>'
                            ,'</td>'
                            ,'</tr>'].join(''));

                        //单个重传
                        tr.find('.demo-reload').on('click', function(){
                            obj.upload(index, file);
                        });

                        //删除
                        tr.find('.demo-delete').on('click', function(){
                            delete files[index]; //删除对应的文件
                            tr.remove();
                            uploadListIns.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                        });

                        /*文件上传判断，多选：文件不能重复，单选：只能选择一个*/
                        if(duotu){
                            var trUpload = demoListView.find('tr.'+file.lastModified).html();  //判断文件上传是否重复
                            if(trUpload){
                                layer.msg('该选中文件,列表中已存在', {icon:2 , time: 2000 });
                            }else{
                                demoListView.append(tr);
                            }
                        }else{
                            demoListView.html(tr);
                        }

                    });
                }
                ,before: function(obj) {
                    load =  top.layer.load(2, {content:'正在上传...',shade: [0.001, '#393D49'],success: function(layero) {
                        layero.find('.layui-layer-content').css({
                            'padding-left': '40px',
                            'width': '100px',
                            'padding-top': '5px'
                        });
                        layero.css({
                            'border-radius': '0',
                            'background': 'white',
                            'box-shadow': '1px 1px 37px rgb0.value = '
                        });

                    }
                    });
                }
                ,done: function(res, index, upload){
                    // layer.close(layer.msg());//关闭上传提示窗口

                    if(res.code == 0){ //上传成功
                        console.log(res.progress);
                        var tr = demoListView.find('tr#upload-'+ index)
                                ,tds = tr.children();
                        if(res.progress == '100'){
                            top.layer.close(load);//关闭上传提示窗口
                            tds.eq(2).html('<span style="color: #5FB878;">上传成功</span>');
                            tds.eq(3).html('<a href="'+res.tolink+'" target="_blank" class="layui-btn layui-btn-mini layui-btn-normal">查看</a>');
                            if (duotu == true) {//调用多图上传方法,其中res.imgid为后台返回的一个随机数字

                                $('#upload_img_list').append('<input type="hidden" name="file_info[]" value="' + res.tolink + '" />');

                            }else{//调用单图上传方法,其中res.imgid为后台返回的一个随机数字

                                $('#upload_img_list').html('<input type="hidden" name="file_info" value="' + res.tolink + '" />');

                            }
                        }else{
                            tds.eq(2).html('<div class="layui-progress layui-progress-big" lay-showpercent="true"><div class="layui-progress-bar"  lay-percent="'+res.progress+'%" style=" width: '+res.progress+'%;"><span class="layui-progress-text">'+res.progress+'%</span></div></div>');
                            tds.eq(3).html(''); //清空操作
                        }


                        //return delete this.files[index]; //删除文件队列已经上传成功的文件
                    }
                    this.error(index, upload,res.info);
                }
                ,error: function(index, upload,info){
                    // layer.close(layer.msg());//关闭上传提示窗口
                    top.layer.close(load);//关闭上传提示窗口
                    var tr = demoListView.find('tr#upload-'+ index)
                            ,tds = tr.children();
                    tds.eq(2).html('<span style="color: #FF5722;">上传失败.'+info+'</span>');
                    tds.eq(3).find('.demo-reload').removeClass('layui-hide'); //显示重传
                }
            });


        });
        //Js 数据容量单位转换(kb,mb,gb,tb)
        function bytesToSize(bytes) {
            if (bytes === 0) return '0 B';
            var k = 1000, // or 1024
                    sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                    i = Math.floor(Math.log(bytes) / Math.log(k));

            return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
        }
    </script>
    @endsection
@endsection