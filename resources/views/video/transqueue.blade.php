@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        {{--@if(!$data)--}}
        {{--<a class="layui-btn layui-btn-sm layui-btn-primary"  onclick="click_free(1)" >一键限免</a>--}}
        {{--@else--}}
        {{--<a class="layui-btn layui-btn-sm layui-btn-primary" onclick="click_free(2)" >取消限免</a>--}}
        {{--@endif--}}
        <span>鉴于小伙伴的普遍反馈，先温馨提醒。</span>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>转码队列</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        搜索标题：
        <div class="layui-inline">
            <input class="layui-input" id="title" name="title" id="test-table-demoReload" autocomplete="off">
        </div>
        <button class="layui-btn" id="search" data-type="reload">搜索</button>
    </div>

    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="is_free">
        <!-- 这里的 checked 的状态只是演示 -->
        <input type="checkbox" name="is_free" value="@{{d.vid }}" title="限免" lay-filter="is_free" @{{ d.is_free == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="look">查看</a>
        {{--<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>--}}
    </script>

@section('script')
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
                ,url:'/admin/video/getTransLog'
                ,page: true
                ,limit:1000    // 每页显示的条数
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'ids', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'starttime', title:'开始时间', width:180}
                    ,{field:'usetime', title:'耗时', width:120}
                    ,{field:'cur_state', title:'当前状态', width:220}
                    ,{field:'src_file',width:200, title: '源码文件'}
                    ,{field:'src_rate', width:150,title: '源码码率'}
                    ,{field:'src_size',width:150, title: '源码尺寸'}
                    ,{field:'src_bit',width:150,  title: '源码大小'}
                    ,{field:'dir_path',width:350,  title: '目标目录'}
                    ,{field:'dir_rate',width:150, title: '目标码率'}
                    ,{field:'dir_file', width:150, title: '目标文件'}
                    ,{field:'dir_size', title:'目标尺寸', width:200}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:100}
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
                    layer.confirm('确认删除吗?', function(index){
                        //obj.del();
                        $.ajax({
                            type: "POST", url: "/admin/video/delvideo",
                            data: { vid: data.vid }, dataType: "json",
                            success: function (e) {
                                if (e.status == 1) {
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
                } else if(obj.event === 'look'){
                    //layer.alert('编辑行：<br>'+ JSON.stringify(data))
                    code = data.code;
                    window.location.href = "/admin/video/translogs/"+code;
                }
            });
        });
    </script>
@endsection
@endsection