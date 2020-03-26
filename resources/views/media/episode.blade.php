@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        <button type="button" class="layui-btn layui-btn-primary" onclick="javascript:history.back(-1);return false;">返 回</button>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>剧集列表</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        搜索标题：
        <div class="layui-inline">
            <input class="layui-input" id="title" name="title" id="test-table-demoReload" autocomplete="off">
            <input style="display: none" class="layui-input" id="mid" name="mid" autocomplete="off" value="{{$mid}}">
        </div>
        <button class="layui-btn" id="search" data-type="reload">搜索</button>
    </div>

    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
@endsection

@section('script')
    <script>
        layui.use('table', function(){
            var table = layui.table,$= layui.jquery,form = layui.form;


            var tableIn = table.render({
                elem: '#test'
                ,url:'/admin/media/getEpisodeList'
                ,page: true
                ,limit:10    // 每页显示的条数
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'Id', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'MId',width:100, title: '对应主体'}
                    ,{field:'Sid',width:100, title: '原始ID'}
                    ,{field:'Title',width:180, title: '标题'}
                    ,{field:'Code', width:100,title: '码率'}
                    ,{field:'Image',width:80, title: '封面图',templet: '<div><img src="@{{ d.Image  }}" width="30px" height="40px" ></div>'}
                    ,{field:'Gif',width:80, title: 'Gif图',templet: '<div><img src="@{{ d.Gif  }}" width="30px" height="40px" ></div>'}
                    ,{field:'Play_url', width:400,title: '播放地址'}
                    ,{field:'Play_time', width:140,title: '总播放时间'}
                    ,{field:'Description', width:140,title: '简介'}
                    ,{field:'Episode', width:120,title: '第几集'}
                    ,{field:'Season', width:120,title: '第几季'}
                    ,{field:'Lang', width:120,title: '语言'}
                    ,{field:'Source', width:140,title: '来源'}
                    ,{field:'Create_time', width:200,title: '创建时间'}
                    ,{field:'Update_time', width:200,title: '更新时间'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:180}
                ]]
                ,where: {
                    mid: $('#mid').val()
                }
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
                if(obj.event === 'detail'){
                    layer.msg('ID：'+ data.id + ' 的查看操作');
                } else if(obj.event === 'del'){
                    layer.confirm('确认删除吗?', function(index){
                        //obj.del();
                        $.ajax({
                            type: "POST", url: "/admin/media/delmedia",
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
                } else if(obj.event === 'edit'){
                    vid = data.vid;
                    window.location.href = "/admin/media/editmedia/"+vid;
                }
            });
        });
    </script>
@endsection