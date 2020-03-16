@extends('layouts.layouts')

@section('css')
    <link href="{{ URL::asset('/lyear/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/lyear/css/materialdesignicons.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/lyear/css/style.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <blockquote class="layui-elem-quote layui-text">
        {{--@if(!$data)--}}
            {{--<a class="layui-btn layui-btn-sm layui-btn-primary"  onclick="click_free(1)" >一键限免</a>--}}
        {{--@else--}}
            {{--<a class="layui-btn layui-btn-sm layui-btn-primary" onclick="click_free(2)" >取消限免</a>--}}
        {{--@endif--}}
        <span>温馨提示，同一个文件不要同时转码多次。</span>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>视频列表</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        搜索标题：
        <div class="layui-inline">
            <input class="layui-input" id="title" name="title" id="test-table-demoReload" autocomplete="off">
        </div>
        <button class="layui-btn" id="search" data-type="reload">搜索</button>
        <button class="layui-btn" id="transcode">转码</button>
        <button class="layui-btn layui-btn-danger" id="dels">删除</button>
        {{--<button class="layui-btn" id="transfer">同步</button>--}}
    </div>

    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="is_free">
        <!-- 这里的 checked 的状态只是演示 -->
        <input type="checkbox" name="is_free" value="@{{d.vid }}" title="限免" lay-filter="is_free" @{{ d.is_free == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="transcode">转码</a>
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
@endsection

@section('script')
    <!--消息提示-->
    <script type="text/javascript" src="{{ URL::asset('/lyear/js/bootstrap-notify.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('/lyear/js/lightyear.js') }}"></script>

    <script>
        $(function () {
            //先将$data转成json编码，再用eval将json格式转为js数组
            var arr = eval(<?php echo json_encode($no_display_aspect_ratio);?>);
            for(let i in arr) {
                lightyear.notify('[no_display_aspect_ratio] '+arr[i], 'danger', 1500);
            }
        });

        $('#transfer').on('click',function () {
            $.post("{{url('admin/video/syncdata')}}",{token:"{{csrf_token()}}"},function (ret) {
                alert(ret.msg);
            },'json');
        });
    </script>
    <script>
        layui.use('table', function(){
            var table = layui.table,$= layui.jquery,form = layui.form;

            window.click_free = function(sta){
                $.ajax({
                    type: "POST", url: "/admin/video/clickfree",
                    data: { sta: sta }, dataType: "json",
                    success: function (e) {
                        if (e.status == 1) {
                            layer.msg(e.msg, { time: 1500 } , function(){
                                history.go(0);
                            });
                        } else {
                            layer.msg(e.msg, { time: 1500 });
                        }
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            }
            var tableIn = table.render({
                elem: '#test'
                ,url:'/admin/video/getVideoList'
                ,page: true
                ,limit:10    // 每页显示的条数
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'vid', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'title',width:200, title: '标题'}
                    ,{field:'status_txt',width:100, title: '是否转码'}
                    ,{field:'pic',width:100, title: '封面图',templet: '<div><img src="@{{ d.pic  }}" width="30px" height="40px" ></div>'}
                    ,{field:'gif',width:100, title: '动态图',templet: '<div><img src="@{{ d.gif  }}" width="30px" height="40px" ></div>'}

                    ,{field:'url', width:400,title: '源文件'}
                    ,{field:'src_bit', width:100,title: '源视频大小'}
                    ,{field:'src_size', width:100,title: '源文件尺寸'}
                    ,{field:'videotime', width:100,title: '视频时长'}
                    ,{field:'ext', width:200,title: '格式'}
                    ,{field:'src_rate', width:100,title: '源码率'}
                    ,{field:'vcode', width:100,title: '编码'}
                    ,{field:'acode', width:100,title: '音频'}
                    ,{field:'dis_ratio', width:100,title: '显示比例'}
                    ,{field:'video', width:400,title: '目标文件'}

                    ,{field:'otype', width:150,title: '类型'}
                    ,{field:'firstotype',width:150, title: '导航分类'}
                    ,{field:'secondotype',width:150,  title: '视频分类'}
                    ,{field:'screenotype',width:200,  title: '筛选条件'}
                    ,{field:'star',width:200, title: '参演明星'}
                    ,{field:'hotcount', title:'视频热度', width:100}
                    ,{field:'createtime', title:'加入时间', width:200}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:180}
                ]]
            });
            //搜索
            $('#search').on('click', function () {
                var title = $('#title').val();
//                if ($.trim(title) === '') {
//                    layer.msg('请输入关键字！', {icon: 0});
//                    return;
//                }
                tableIn.reload({
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    where: {title: title}
                });
            });
            //复选
            $('#transcode').on('click',function () {
                let vid = '';
                let obj = table.checkStatus('test').data;
                for(let i=0; i<obj.length; i++) {
                    if(i===0) {
                        vid = obj[i].vid;
                    } else {
                        vid = vid+'_'+obj[i].vid;
                    }
                }
                window.location.href = "/admin/video/transcode/"+vid;
            });
            //批量删除
            $('#dels').on('click',function () {
                let vid = '';
                let obj = table.checkStatus('test').data;
                for(let i=0; i<obj.length; i++) {
                    if(i===0) {
                        vid = obj[i].vid;
                    } else {
                        vid = vid+'_'+obj[i].vid;
                    }
                }

                layer.confirm('操作也将删除文件，确认删除吗?', function(index){
                    $.post("{{url('admin/video/delsvideo')}}",{ids:vid},function (ret) {
                        if(ret.code===1) {
                            layer.msg(ret.msg, {icon: 1, time: 1000},function () {
                                window.location.reload();
                            });
                        } else {
                            layer.msg(ret.msg, {icon: 2, anim: 6, time: 1000});
                        }
                    },'json');
                    layer.close(index);
                });
            });
            //监听锁定操作
            form.on('checkbox(is_free)', function(obj){
                //layer.tips(this.value + ' ' + this.name + '：'+ obj.elem.checked, obj.othis);
                var vid = this.value;
                var is_free = obj.elem.checked===true?1:0;
                $.ajax({
                    type: "POST", url: "/admin/video/videofree",
                    data: { vid: vid ,is_free : is_free }, dataType: "json",
                    success: function (e) {
                        if (e.status == 1) {
                            layer.msg('修改成功！', { time: 1500 });
                        } else {
                            layer.msg('修改失败！', { time: 1500 });
                        }
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            });
            //监听工具条
            table.on('tool(demo)', function(obj){
                var data = obj.data;
                if(obj.event === 'detail'){
                    layer.msg('ID：'+ data.id + ' 的查看操作');
                } else if(obj.event === 'del'){
                    layer.confirm('操作也将删除文件，确认删除吗?', function(index){
                        //obj.del();
                        $.ajax({
                            type: "POST", url: "/admin/video/delvideo",
                            data: { vid: data.vid }, dataType: "json",
                            success: function (e) {
                                if (e.status === 1) {
                                    layer.msg('删除成功！', { time: 1500 }, function () {
                                        obj.del();
                                    });
                                } else {
                                    layer.msg('删除失败！', { time: 1500 });
                                }
                            },
                            error: function (data) {
                                console.log(data);
                            }
                        });
                        layer.close(index);
                    });
                } else if(obj.event === 'edit'){
                    //layer.alert('编辑行：<br>'+ JSON.stringify(data))
                    vid = data.vid;
                    window.location.href = "/admin/video/editvideo/"+vid;
                } else if(obj.event === 'transcode'){
                    //layer.alert('编辑行：<br>'+ JSON.stringify(data))
                    vid = data.vid;
                    window.location.href = "/admin/video/transcode/"+vid;
                }
            });
        });
    </script>
@endsection