@extends('layouts.layouts')

@section('css')
    <style>
        /* 下拉多选样式 需要引用*/
        select[multiple]+.layui-form-select>.layui-select-title>input.layui-input{ border-bottom: 0}
        select[multiple]+.layui-form-select dd{ padding:0;}
        select[multiple]+.layui-form-select .layui-form-checkbox[lay-skin=primary]{ margin:0 !important; display:block; line-height:36px !important; position:relative; padding-left:26px;}
        select[multiple]+.layui-form-select .layui-form-checkbox[lay-skin=primary] span{line-height:36px !important;padding-left: 10px; float:none;}
        select[multiple]+.layui-form-select .layui-form-checkbox[lay-skin=primary] i{ position:absolute; left:10px; top:0; margin-top:9px;}
        .multiSelect{ line-height:normal; height:auto; padding:4px 10px; overflow:hidden;min-height:38px; margin-top:-38px; left:0; z-index:99;position:relative;background:none;}
        .multiSelect a{ padding:2px 5px; background:#908e8e; border-radius:2px; color:#fff; display:block; line-height:20px; height:20px; margin:2px 5px 2px 0; float:left;}
        .multiSelect a span{ float:left;}
        .multiSelect a i {float:left;display:block;margin:2px 0 0 2px;border-radius:2px;width:8px;height:8px;padding:4px;position:relative;-webkit-transition:all .3s;transition:all .3s}
        .multiSelect a i:before, .multiSelect a i:after {position:absolute;left:8px;top:2px;content:'';height:12px;width:1px;background-color:#fff}
        .multiSelect a i:before {-webkit-transform:rotate(45deg);transform:rotate(45deg)}
        .multiSelect a i:after {-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}
        .multiSelect a i:hover{ background-color:#545556;}
        .multiOption{display: inline-block; padding: 0 5px;cursor: pointer; color: #999;}
        .multiOption:hover{color: #5FB878}

        @font-face {font-family: "iconfont"; src: url('data:application/x-font-woff;charset=utf-8;base64,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') format('woff')}
        .iconfont {font-family:"iconfont" !important;font-size:16px;font-style:normal;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;}
        .icon-fanxuan:before { content: "\e837"; }
        .icon-quanxuan:before { content: "\e623"; }
        .icon-qingkong:before { content: "\e63e"; }

        /* 下面是页面内样式，无需引用 */
        .unshow>#result {
            display: none;
        }
        pre { padding: 5px; margin: 5px; }
    </style>
@endsection

@section('content')
    <blockquote class="layui-elem-quote layui-text">
        <button type="button" class="layui-btn layui-btn-primary" onclick="javascript:history.back(-1);return false;">返 回</button>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>视频主体编辑表单</legend>
    </fieldset>

    <form class="layui-form" action="{{ url('/admin/media/editmedia/'.$mid) }}" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>标题</label>
            <div class="layui-input-block">
                <input type="text" name="Name" lay-verify="required" autocomplete="off" placeholder="请输入标题" class="layui-input" value="{{ $data['Name'] }}" >
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">封面图</label>
            <div class="layui-input-block">
                <div class="col-lg-2">
                    <span id="showimg">
                        @if($data['Image'])
                            <img style="width:auto;max-height:55px;" src="{{ $data['Image'] }}">
                        @endif
                    </span>
                    <a href="javascript:;" id="img">
                        <img onerror="this.src='{{asset("assets/images/placeholder.jpg")}}'" src="{{asset('assets/images/placeholder.jpg')}}" data-url="" style="width:auto;max-height:55px;" class="listpic" alt="列表图">
                    </a>
                </div>
                <input type="hidden"  name="imgval" id="imgval" value="{{ $data['Image'] }}">
                <input type="hidden"  name="imgval_old" value="{{ $data['Image'] }}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">封面大图</label>
            <div class="layui-input-block">
                <div class="col-lg-2">
                    <span id="showbigimg">
                        @if($data['Image_big'])
                            <img style="width:auto;max-height:80px;" src="{{ $data['Image_big'] }}">
                        @endif
                    </span>
                    <a href="javascript:;" id="bigimg">
                        <img onerror="this.src='{{asset("assets/images/placeholder.jpg")}}'" src="{{asset('assets/images/placeholder.jpg')}}" data-url="" style="width:auto;max-height:80px;" class="listpic" alt="列表图">
                    </a>
                </div>
                <input type="hidden"  name="bigimgval" id="bigimgval" value="{{ $data['Image_big'] }}">
                <input type="hidden"  name="bigimgval_old" value="{{ $data['Image_big'] }}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">类型</label>
            <div class="layui-input-block">
                @foreach($types as $v)
                    <input type="radio" name="Type" value="{{ $v['Id'] }}" @if( in_array( $v['Id'],$data['Type'] ) ) checked @endif  lay-skin="primary" title="{{ $v['Name'] }}">
                @endforeach
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">国家/地区</label>
            <div class="layui-input-block">
                <select name="Country" lay-filter="myselect">
                    <option value="" ></option>
                    @foreach($countrys as $v)
                        <option  value="{{$v['Code']}}" @if(in_array($v['Code'],$data['Country'])) selected @endif>{{$v['Name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>从属分类</label>
            <div class="layui-input-block">
                <select name="Cats" lay-filter="myselect">
                    <option value="0"></option>
                    @foreach($cats as $v)
                        <option  value="{{$v['Id']}}" @if(in_array($v['Id'],$data['Cats'])) selected @endif>{{$v['html']}}{{$v['Name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">年份</label>
                <div class="layui-input-inline">
                    <input type="text" name="Year" value="{{ $data['Year'] }}" autocomplete="off" placeholder="请输入年份" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">总集数</label>
                <div class="layui-input-inline">
                    <input type="text" name="Episodes" value="{{ $data['Episodes'] }}" autocomplete="off" placeholder="请输入集数" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">上一部ID</label>
                <div class="layui-input-inline">
                    <input type="text" name="Preid"  value="{{ $data['Preid'] }}" autocomplete="off" placeholder="请输入上一部ID" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">番号</label>
                <div class="layui-input-inline">
                    <input type="text" name="FH" value="{{ $data['FH'] }}" autocomplete="off" placeholder="请输入番号" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">IMDB</label>
                <div class="layui-input-inline">
                    <input type="text" name="IMDB"  value="{{ $data['IMDB'] }}" autocomplete="off" placeholder="请输入视频IMDB" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">评分</label>
                <div class="layui-input-inline">
                    <input type="number" name="Score"   value="{{ $data['Score'] }}" autocomplete="off" placeholder="请输入视频评分" class="layui-input">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">标签</label>
            <div class="layui-input-block">
                @foreach($tags as $v)
                    <input type="checkbox" name="Tags[]" value="{{ $v['Id'] }}" @if( in_array( $v['Id'],$data['Tags'] ) ) checked @endif  lay-skin="primary" title="{{ $v['Name'] }}">
                @endforeach
            </div>
        </div>

        <div class="layui-row">
            <div class="layui-col-lg12 layui-col-md12">
                <fieldset class="layui-elem-field  ">
                    <div class="layui-block">
                        <label class="layui-form-label">从属导演</label>
                        <div class="layui-input-inline">
                            <select name="Directors" multiple lay-search lay-tools>
                                <option value="">请选择导演</option>
                                @foreach($directors as $v)
                                    <option  value="{{ $v['Id'] }}" @if(in_array($v['Id'],$data['Directors'])) selected @endif>{{ $v['Name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>


        <div class="layui-row">
            <div class="layui-col-lg12 layui-col-md12">
                <fieldset class="layui-elem-field  ">
                    <div class="layui-block">
                        <label class="layui-form-label">从属演员</label>
                        <div class="layui-input-inline">
                            <select name="Actors" multiple lay-search lay-tools>
                                <option value="">请选择参演明星</option>
                                @foreach($actors as $v)
                                    <option  value="{{ $v['Id'] }}" @if(in_array($v['Id'],$data['Actors'])) selected @endif>{{ $v['Name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">状态</label>
            <div class="layui-input-block">
                @foreach($status as $v)
                    <input type="radio" name="Status" value="{{ $v['Id'] }}" @if($v['Id'] == $data['Status']) checked @endif  lay-skin="primary" title="{{ $v['Name'] }}">
                @endforeach
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">发布状态</label>
            <div class="layui-input-block">
                <input type="checkbox" name="Issue" value="1" title="发布" @if($data['Issue']) checked @endif>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">关键字</label>
            <div class="layui-input-block">
                <input type="text" name="KeyWord" autocomplete="off" placeholder="请输入关键字" class="layui-input" value="{{ $data['KeyWord'] }}" >
            </div>
        </div>

        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">内容介绍</label>
            <div class="layui-input-block">
                <textarea placeholder="请输入内容" name="Content"  class="layui-textarea">{{ $data['Content'] }}</textarea>
            </div>
        </div>

        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">备注</label>
            <div class="layui-input-block">
                <textarea placeholder="请输入备注" name="Mark"  class="layui-textarea">{{ $data['Mark'] }}</textarea>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
@endsection

@section('script')

<script>
    layui.use('form', function(){
        var form = layui.form,$ = layui.jquery;

        //监听提交
        form.on('submit(submit)', function(data){
            $.ajax({
                url: data.form.action,
                type: data.form.method,
                data: data.field,
                success: function (res) {
                    if(res.code == 1){
                        layer.msg(res.msg, {icon: 1, time: 1000});
                        window.location.href = "/admin/media";
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
        upload.render({
            elem: '#img'
            ,url: '/admin/uploadVideoImg'
            ,size: 500
            ,exts: 'jpg|png|jpeg'
            ,multiple: false
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#showimg').html('<img style="width:auto;max-height:55px;" src="'+result+'">');
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

        upload.render({
            elem: '#bigimg'
            ,url: '/admin/uploadVideoImg'
            ,size: 500
            ,exts: 'jpg|png|jpeg'
            ,multiple: false
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#showbigimg').html('<img style="width:auto;max-height:80px;" src="'+result+'">');
                    //$('#showbigimg').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                //如果上传失败
                if(res.code > 0){
                    return layer.msg('上传失败');
                }
                //上传成功
                if(res.code ==0){
                    $('#bigimgval').val(res.data.src);
                    return layer.msg('上传成功');
                }
            }
        });

        //指定允许上传的文件类型
        upload.render({
            elem: '#files'
            ,url: '/admin/uploadVideoFile'
            ,accept: 'file' //普通文件
            ,size: 3072000
            ,before: function(obj){
                loading =layer.load(1, {shade: [0.1,'#fff'] });//0.1透明度的白色背景
            }
            ,done: function(res, index, upload){
                // layer.close(layer.msg());//关闭上传提示窗口
                console.log(res);
                if(res.code == 0){ //上传成功
                    if(res.progress == '100'){
                        top.layer.close(loading);//关闭上传提示窗口
                        $("#video").html('<video id="video" width="220" height="140" controls autoplay><source src="'+res.data.src+'" type="video/mp4"></video>');
                        $('#filesval').val(res.data.src);
                        layer.msg(res.msg, {icon: 1, time: 1000});
                    }else{
                        layer.msg('视频已上传'+parseInt(res.progress)+"%");
                    }
                }
                //this.error(index, upload,res.info);
            }
            ,error: function(index, upload,info){
                // layer.close(layer.msg());//关闭上传提示窗口
                top.layer.close(load);//关闭上传提示窗口
                return layer.msg('上传失败');
            }
        });

        var upurl = "{:url('/admin/uploadVideoFile',['author'=>'Ycl_24'])}";//上传图片地址
        var duotu = false;//是否为多图上传true false
        //多文件列表示例
        var demoListView = $('#demoList');
        uploadListIns = upload.render({
            elem: '#testList'
            ,url: '/admin/uploadVideoFile'
            ,accept:  'file'
            ,multiple: duotu
            ,auto: false
            ,bindAction: '#testListAction'
            ,size: 3072000
            ,processData: false
            ,contentType: false
            ,choose: function(obj){
                //console.log(obj);
                // layer.close(layer.msg());//关闭上传提示窗口
                var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
                console.log(files);
                data = JSON.parse(files);
                console.log(data);
                return false;
                //读取本地文件
                obj.preview(function(index, file, result){
                    console.log(file);
                    //return false;
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
                load =  top.layer.load(2, {content:'正在上传...',shade: [0.001, '#393D49']});
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
                        tds.eq(3).html('<a href="'+res.data.src+'" target="_blank" class="layui-btn layui-btn-mini layui-btn-normal">查看</a>');
                        if (duotu == true) {//调用多图上传方法,其中res.imgid为后台返回的一个随机数字

                            $('#upload_img_list').append('<input type="hidden" name="file_info[]" value="' + res.data.src + '" />');

                        }else{//调用单图上传方法,其中res.imgid为后台返回的一个随机数字

                            $('#upload_img_list').html('<input type="hidden" name="file_info" value="' + res.data.src + '" />');

                        }
                    }else{
                        tds.eq(2).html('<div class="layui-progress layui-progress-big" lay-showpercent="true"><div class="layui-progress-bar"  lay-percent="'+res.progress+'%" style=" width: '+res.progress+'%;"><span class="layui-progress-text">'+res.progress+'%</span></div></div>');
                        tds.eq(3).html(''); //清空操作
                    }


                    return delete this.files[index]; //删除文件队列已经上传成功的文件
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
        //Js 数据容量单位转换(kb,mb,gb,tb)
        function bytesToSize(bytes) {
            if (bytes === 0) return '0 B';
            var k = 1000, // or 1024
                sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                i = Math.floor(Math.log(bytes) / Math.log(k));

            return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
        }
    });
</script>

@endsection
