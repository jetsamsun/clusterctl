@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒两个常见“问题”
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>提现列表</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        收款人：
        <div class="layui-inline">
            <input class="layui-input" id="uname" name="uname"  autocomplete="off">
        </div>
        银行卡号：
        <div class="layui-inline">
            <input class="layui-input" id="bankcard" name="bankcard"  autocomplete="off">
        </div>
        <button class="layui-btn" id="search" data-type="reload">搜索</button>
    </div>
    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="pass">通过</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="refuse">拒绝</a>
    </script>

    @section('script')
    <script>
        layui.use('table', function(){
            var table = layui.table,$= layui.jquery,form = layui.form;

            var tableIn = table.render({
                elem: '#test'
                ,url:'/admin/withdraw/getWithdrawList'
                ,page:true
                ,limit:10    // 每页显示的条数
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'id', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'uid',width:90, title: 'UID'}
                    ,{field:'uname',width:140, title: '收款人'}
                    ,{field:'bankcard', title:"银行卡号"}
                    ,{field:'cash_asset', title: '提现金额'}
                    ,{field:'status_txt', width:150,title: '提现状态'}
                    ,{field:'time', width:200,title: '创建时间'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:150}
                ]]
            });
            //搜索
            $('#search').on('click', function () {
                var uname = $('#uname').val();
                var bankcard = $('#bankcard').val();
                tableIn.reload({
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    where: {uname: uname,bankcard:bankcard}
                });
            });
            //监听工具条
            table.on('tool(demo)', function(obj){
                var data = obj.data;
                if(obj.event === 'detail'){
                    layer.msg('ID：'+ data.id + ' 的查看操作');
                } else if(obj.event === 'del'){

                } else if(obj.event === 'edit'){
                    //layer.alert('编辑行：<br>'+ JSON.stringify(data))

                }else if(obj.event === 'pass'){
                    id = data.id;
                    status = 2;
                    layer.confirm('确认通过吗?', function(index){
                        $.ajax({
                            type: "POST", url: "/admin/withdraw/editstatus",
                            data: { id: id,status:status }, dataType: "json",
                            success: function (e) {
                                if (e.status == 1) {
                                    layer.msg('成功！', { time: 1500 });
                                    location.reload();
                                } else if(e.status == 2){
                                    layer.msg('该提现记录已通过', { time: 1500 });
                                } else if(e.status == 3){
                                    layer.msg('该提现记录已拒绝', { time: 1500 });
                                }  else {
                                    layer.msg('失败！', { time: 1500 });
                                }
                            },
                            error: function (data) {
                                console.log(data);
                            }
                        });
                        layer.close(index);
                    });

                }else if(obj.event === 'refuse'){
                    id = data.id;
                    status = 3;
                    layer.confirm('确认拒绝吗?', function(index){
                        $.ajax({
                            type: "POST", url: "/admin/withdraw/editstatus",
                            data: { id: id,status:status }, dataType: "json",
                            success: function (e) {
                                if (e.status == 1) {
                                    layer.msg('成功！', { time: 1500 });
                                    location.reload();
                                } else if(e.status == 2){
                                    layer.msg('该提现记录已通过', { time: 1500 });
                                } else if(e.status == 3){
                                    layer.msg('该提现记录已拒绝', { time: 1500 });
                                }   else {
                                    layer.msg('失败！', { time: 1500 });
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