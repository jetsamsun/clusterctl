@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        <a class="layui-btn layui-btn-normal" href="{{ url('/admin/addtags') }}" >新增</a>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>标签列表</legend>
    </fieldset>

    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>

    @section('script')
    <script>
        layui.use('table', function(){
            var table = layui.table,$= layui.jquery,form = layui.form;

            table.render({
                elem: '#test'
                ,url:'/admin/getTagsList'
                ,width:430
                ,page:false
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'Id', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'Name', width:150,title: '标签名称'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:150}
                ]]
            });
            //监听工具条
            table.on('tool(demo)', function(obj){
                var data = obj.data;
                if(obj.event === 'detail'){
                    layer.msg('ID：'+ data.Id + ' 的查看操作');
                } else if(obj.event === 'del'){
                    layer.confirm('确认删除吗?', function(index){
                        //obj.del();
                        $.ajax({
                            type: "POST", url: "/admin/deltags",
                            data: { oid: data.Id }, dataType: "json",
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
                    oid = data.Id;
                    window.location.href = "/admin/edittags/"+oid;
                }
            });
        });
    </script>
    @endsection
@endsection