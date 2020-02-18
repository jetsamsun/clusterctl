@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒两个常见“问题”
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>视频问题反馈</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        {{--手机号/随机账号：--}}
        {{--<div class="layui-inline">--}}
            {{--<input class="layui-input" id="number" name="number"  autocomplete="off">--}}
        {{--</div>--}}
        视频标题：
        <div class="layui-inline">
            <input class="layui-input" id="title" name="title"  autocomplete="off">
        </div>
        提交内容：
        <div class="layui-inline">
            <input class="layui-input" id="content" name="content"  autocomplete="off">
        </div>
        <button class="layui-btn" id="search" data-type="reload">搜索</button>
    </div>
    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
    @section('script')
    <script>
        layui.use('table', function(){
            var table = layui.table,$= layui.jquery,form = layui.form;

            var tableIn = table.render({
                elem: '#test'
                ,url:'/admin/trouble/getTroubleList'
                ,page: true
                ,limit:10    // 每页显示的条数
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'id', width:100, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'uid',width:100, title: 'UID'}
                    ,{field:'randomnum',width:250, title: '随机账号'}
                    ,{field:'mobile',width:160, title: '手机号'}
                    ,{field:'title',width:450, title: '视频标题'}
                    ,{field:'content',width:450, title: '内容'}
                    ,{field:'time',width:170,  title: '发布时间'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:150}
                ]]
            });
            //搜索
            $('#search').on('click', function () {
                var number = $('#number').val();
                var title = $('#title').val();
                var content = $('#content').val();
                tableIn.reload({
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    where: {number: number,title:title,content:content}
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
                            type: "POST", url: "/admin/trouble/delTrouble",
                            data: { id: data.id }, dataType: "json",
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
                }
            });
        });
    </script>
    @endsection
@endsection