@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        <span>鉴于小伙伴的普遍反馈，先温馨提醒。</span>
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>视频主体列表</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        搜索标题：
        <div class="layui-inline">
            <input class="layui-input" id="title" name="title" id="test-table-demoReload" autocomplete="off">
        </div>
        <button class="layui-btn" id="search" data-type="reload">搜索</button>
        <button class="layui-btn" id="add">新增</button>
        <button class="layui-btn layui-btn-danger" id="dels">删除</button>
    </div>
    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="is_free">
        <!-- 这里的 checked 的状态只是演示 -->
        <input type="checkbox" name="is_free" value="@{{d.vid }}" title="限免" lay-filter="is_free" @{{ d.is_free == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="episode">剧集</a>
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
                ,url:'/admin/media/getMediaList'
                ,page: true
                ,limit:10    // 每页显示的条数
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'Id', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'Name',width:200, title: '标题'}
                    ,{field:'Image',width:140, title: '封面图',templet: '<div><img src="@{{ d.Image  }}" width="30px" height="40px" ></div>'}
                    ,{field:'Country', width:140,title: '地区'}
                    ,{field:'Score', width:140,title: '评分'}
                    ,{field:'Year', width:140,title: '年份'}
                    ,{field:'Episodes', width:140,title: '总集数'}
                    ,{field:'Status', width:140,title: '状态'}
                    ,{field:'IMDB', width:140,title: 'IMDB'}
                    ,{field:'FH', width:140,title: '番号'}
                    ,{field:'Content', width:140,title: '简介'}
                    ,{field:'Tags', width:140,title: '标签'}
                    ,{field:'Type', width:140,title: '类型'}
                    ,{field:'Cats', width:140,title: '从属分类'}
                    ,{field:'Directors', width:140,title: '从属导演'}
                    ,{field:'Actors', width:140,title: '从属演员'}
                    ,{field:'Create_time', width:200,title: '创建时间'}
                    ,{field:'Update_time', width:200,title: '更新时间'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:180}
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
            //批量
            $('#add').on('click',function () {
                window.location.href = "/admin/media/addmedia";
            });
            //批量删除
            $('#dels').on('click',function () {
                let mid = '';
                let obj = table.checkStatus('test').data;
                for(let i=0; i<obj.length; i++) {
                    if(i===0) {
                        mid = obj[i].Id;
                    } else {
                        mid = mid+'_'+obj[i].Id;
                    }
                }

                if(!mid) {
                    layer.msg('请选择所要删除的项', {icon: 2, anim: 6, time: 1000});
                    return ;
                }

                layer.confirm('删除包括剧集记录，确认删除吗?', function(index){
                    $.post("{{url('admin/media/delmedia')}}",{mid:mid},function (ret) {
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
            //监听工具条
            table.on('tool(demo)', function(obj){
                var data = obj.data;
                if(obj.event === 'detail'){
                    layer.msg('ID：'+ data.Id + ' 的查看操作');
                } else if(obj.event === 'del'){
                    layer.confirm('删除包括剧集记录，确认删除吗?', function(index){
                        //obj.del();
                        $.ajax({
                            type: "POST", url: "/admin/media/delmedia",
                            data: { mid: data.Id }, dataType: "json",
                            success: function (e) {
                                if (e.code === 1) {
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
                    mid = data.Id;
                    window.location.href = "/admin/media/editmedia/"+mid;
                } else if(obj.event === 'episode'){
                    mid = data.Id;
                    window.location.href = "/admin/media/episode/"+mid;
                }
            });
        });
    </script>
@endsection