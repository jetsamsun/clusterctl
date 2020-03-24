@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒两个常见“问题”
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>视频列表</legend>
    </fieldset>
    {{--<div class="demoTable">--}}
        {{--搜索ID：--}}
        {{--<div class="layui-inline">--}}
            {{--<input class="layui-input" name="id" id="demoReload" autocomplete="off">--}}
        {{--</div>--}}
        {{--<button class="layui-btn" data-type="reload">搜索</button>--}}
    {{--</div>--}}
    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit">查看</a>
    </script>

    @section('script')
    <script>
        layui.use('table', function(){
            var table = layui.table,$= layui.jquery,form = layui.form;

            table.render({
                elem: '#test'
                ,url:'/admin/app/getAppList'
                ,page:false
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'oid', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'os',width:200, title: '标题'}
                    ,{field:'pic',width:140, title: '封面图',templet: '<div><img src="@{{ d.pic  }}" width="30px" height="40px" ></div>'}
                    ,{field:'text', width:140,title: '简介'}
                    ,{field:'text', width:140,title: '标签'}
                    ,{field:'url', width:140,title: '类型'}
                    ,{field:'url', width:140,title: '从属分类'}
                    ,{field:'url', width:140,title: '从属导演'}
                    ,{field:'url', width:140,title: '从属演员'}
                    ,{field:'url', width:140,title: '地区'}
                    ,{field:'url', width:140,title: '年份'}
                    ,{field:'url', width:140,title: 'IMDB'}
                    ,{field:'url', width:140,title: '番号'}
                    ,{field:'url', width:140,title: '评分'}
                    ,{field:'url', width:140,title: '总集数'}
                    ,{field:'url', width:140,title: '状态'}
                    ,{field:'url', width:200,title: '创建时间'}
                    ,{field:'url', width:200,title: '更新时间'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:150}
                ]]
            });
            //监听工具条
            table.on('tool(demo)', function(obj){
                var data = obj.data;
                if(obj.event === 'detail'){
                    layer.msg('ID：'+ data.id + ' 的查看操作');
                } else if(obj.event === 'del'){

                } else if(obj.event === 'edit'){
                    //layer.alert('编辑行：<br>'+ JSON.stringify(data))
                    oid = data.oid;
                    window.location.href = "/admin/app/editapp/"+oid;
                }
            });
        });
    </script>
    @endsection
@endsection