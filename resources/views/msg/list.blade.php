@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒两个常见“问题”
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>留言列表</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        手机号：
        <div class="layui-inline">
            <input class="layui-input" id="mobile" name="mobile"  autocomplete="off">
        </div>
        相关内容：
        <div class="layui-inline">
            <input class="layui-input" id="content" name="content"  autocomplete="off">
        </div>
        <button class="layui-btn" id="search" data-type="reload">搜索</button>
    </div>
    <table class="layui-hide" lay-filter="demo" id="test"></table>


    @section('script')
    <script>
        layui.use('table', function(){
            var table = layui.table,$= layui.jquery,form = layui.form;

            var tableIn=table.render({
                elem: '#test'
                ,url:'/admin/msg/getMsgList'
                ,page: true
                ,limit:10    // 每页显示的条数
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'mid', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'uid',width:80, title: 'UID'}
                    ,{field:'randomnum',width:200, title: '随机账号'}
                    ,{field:'mobile',width:160, title: '手机号'}
                    ,{field:'email', width:150,title: '邮箱'}
                    ,{field:'content',width:350, title: '内容'}
                    ,{field:'time',width:170,  title: '发布时间'}
                ]]
            });
            //搜索
            $('#search').on('click', function () {
                var mobile = $('#mobile').val();
                var content = $('#content').val();
                tableIn.reload({
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    where: {mobile: mobile,content:content}
                });
            });
        });
    </script>
    @endsection
@endsection