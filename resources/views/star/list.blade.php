@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        <a class="layui-btn layui-btn-normal" href="{{ url('/admin/addStar') }}" >新增</a>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>明星列表</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        明星姓名：
        <div class="layui-inline">
            <input class="layui-input" id="uname" name="uname" id="test-table-demoReload" autocomplete="off">
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

            var tableIn = table.render({
                elem: '#test'
                ,url:'/admin/star/getStarList'
                ,width: 1024
                ,page: true
                ,limit:10    // 每页显示的条数
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'Id', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'Name',width:200, title: '明星名称'}
                    ,{field:'English_name',width:200, title: '外文名称'}
                    ,{field:'Image',width:140, title: '展示图',templet: '<div><img src="@{{ d.Image  }}" width="40px" ></div>'}
                    ,{field:'Role',width:100, title: '角色'}
                    ,{field:'Country',width:100, title: '国家/地区'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:150}
                ]]
            });
            //搜索
            $('#search').on('click', function () {
                var uname = $('#uname').val();
                tableIn.reload({
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    where: {uname: uname}
                });
            });
            //监听工具条
            table.on('tool(demo)', function(obj){
                var data = obj.data;
                if(obj.event === 'detail'){
                    layer.msg('ID：'+ data.Id + ' 的查看操作');
                } else if(obj.event === 'del'){
                    layer.confirm('确认删除吗?', function(index){
                        $.ajax({
                            type: "POST", url: "/admin/star/delstar",
                            data: { sid: data.Id }, dataType: "json",
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
                    sid = data.Id;
                    window.location.href = "/admin/star/editstar/"+sid;
                }
            });
        });
    </script>
    @endsection
@endsection