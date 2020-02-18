@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        <a class="layui-btn layui-btn-normal" href="{{ url('/admin/advert/addAdvert') }}" >新增</a>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>广告列表</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        广告名称：
        <div class="layui-inline">
            <input class="layui-input" id="title" name="title" id="test-table-demoReload" autocomplete="off">
        </div>
        <button class="layui-btn" id="search" data-type="reload">搜索</button>
    </div>
    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>

    @section('script')
    <script>
        layui.use('table', function(){
            var table = layui.table,$= layui.jquery,form = layui.form;

            var tableIn= table.render({
                elem: '#test'
                ,url:'/admin/advert/getAdvertList'
                ,width: 880
                ,page:false
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'advertId', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'title', width:150,title: '标题'}
                    ,{field:'otype', width:150,title: '分类'}
                    ,{field:'url', width:150,title: '跳转链接'}
                    ,{field:'pic', width:150,title: '展示图',templet: '<div><img src="@{{ d.pic  }}" width="30px" height="40px" ></div>'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:150}
                ]]
            });
            //搜索
            $('#search').on('click', function () {
                var title = $('#title').val();
                tableIn.reload({
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    where: {title: title}
                });
            });
            //监听工具条
            table.on('tool(demo)', function(obj){
                var data = obj.data;
                // console.log(data);return false;
                if(obj.event === 'detail'){
                    layer.msg('ID：'+ data.id + ' 的查看操作');
                } else if(obj.event === 'del'){
                    layer.confirm('确认删除吗?', function(index){
                        //obj.del();
                        $.ajax({
                            type: "POST", url: "/admin/advert/delAdvert",
                            data: { advertId: data.advertId }, dataType: "json",
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
                } else if(obj.event === 'edit'){
                    //layer.alert('编辑行：<br>'+ JSON.stringify(data))
                    advertId = data.advertId;
                    window.location.href = "/admin/advert/editAdvert/"+advertId;
                }
            });
        });
    </script>
    @endsection
@endsection