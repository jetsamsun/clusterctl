@extends('layouts.layouts')
@section('content')
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>用户列表</legend>
    </fieldset>
    <div class="test-table-reload-btn" style="margin-bottom: 10px;">
        搜索账号/ID/邮箱：
        <div class="layui-inline">
            <input class="layui-input" id="key" name="id" id="test-table-demoReload" autocomplete="off">
        </div>
        <button class="layui-btn" id="search" data-type="reload">搜索</button>
    </div>

    <table class="layui-hide" lay-filter="demo" id="test"></table>

    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs layui-btn-danger " lay-event="vip">充值会员</a>
        <a class="layui-btn layui-btn-xs " lay-event="tuiguang">推广人数</a>
    </script>

    @section('script')
    <script>
        layui.use('layer', function(){ //独立版的layer无需执行这一句
            var $ = layui.jquery, layer = layui.layer; //独立版的layer无需执行这一句

            //触发事件
            var active = {
                notice: function(){
                    //示范一个公告层
                    layer.open({
                        type: 1
                        ,title: false //不显示标题栏
                        ,closeBtn: false
                        ,area: '300px;'
                        ,shade: 0.8
                        ,id: 'LAY_layuipro' //设定一个id，防止重复弹出
                        ,btn: ['火速围观', '残忍拒绝']
                        ,btnAlign: 'c'
                        ,moveType: 1 //拖拽模式，0或者1
                        ,content: '<div style="padding: 50px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">你知道吗？亲！<br>layer ≠ layui<br><br>layer只是作为Layui的一个弹层模块，由于其用户基数较大，所以常常会有人以为layui是layerui<br><br>layer虽然已被 Layui 收编为内置的弹层模块，但仍然会作为一个独立组件全力维护、升级。<br><br>我们此后的征途是星辰大海 ^_^</div>'
                        ,success: function(layero){
                            var btn = layero.find('.layui-layer-btn');
                            btn.find('.layui-layer-btn0').attr({
                                href: 'http://www.layui.com/'
                                ,target: '_blank'
                            });
                        }
                    });
                }
            };
            $('#layerDemo .layui-btn').on('click', function(){
                var othis = $(this), method = othis.data('method');
                active[method] ? active[method].call(this, othis) : '';
            });

        });
        layui.use('table', function(){
            var table = layui.table,$= layui.jquery,form = layui.form;

            var tableIn = table.render({
                elem: '#test'
                ,url:'/admin/user/getUserList'
                ,page: true
                ,limit:10    // 每页显示的条数
                ,cols: [[
                    {type:'checkbox', fixed: 'left'}
                    ,{field:'uid', width:80, title: 'ID', sort: true, fixed: 'left'}
                    ,{field:'randomnum',width:180, title: '随机账号'}
                    ,{field:'pic',width:80, title: '头像',templet: '<div><img src="@{{ d.pic  }}" width="30px" height="40px" ></div>'}
                    ,{field:'mobile', width:150,title: '手机号'}
                    ,{field:'is_vip', width:100, title: '是否是VIP'}
                    ,{field:'vipendtime',width:180, title: '到期时间'}
                    ,{field:'asset',width:100, title: '总资产'}
                    ,{field:'residual_asset',width:100, title: '剩余资产'}
                    ,{field:'cash_asset',width:100, title: '提现资产'}
                    ,{field:'frozen_asset',width:100,title: '冻结资产'}
                    ,{field:'os', width:100, title: '手机类型'}
                    ,{field:'registertime', title:'加入时间', width:220}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]]
            });
            //搜索
            $('#search').on('click', function () {
                var key = $('#key').val();
//                if ($.trim(key) === '') {
//                    layer.msg('请输入关键字！', {icon: 0});
//                    return;
//                }
                tableIn.reload({
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    where: {key: key}
                });
            });
            //监听工具条
            table.on('tool(demo)', function(obj){
                var data = obj.data;
                if(obj.event === 'detail'){
                    layer.msg('ID：'+ data.id + ' 的查看操作');
                } else if(obj.event === 'del'){

                } else if(obj.event === 'tuiguang'){
                    uid = data.uid;
                    layer.open({
                        type: 2,
                        title: '推广人数',
                        content:['/admin/user/tuiguang?uid='+uid] ,//不允许出现滚动条
                        area:['800px', '650px']
                    });
                } else if(obj.event === 'vip'){
                    uid = data.uid;
                    layer.open({
                        type: 2,
                        title: '充值后台',
                        // content:['http://up.kuman.cn/settle/eject','no'] ,//不允许出现滚动条
                        content:['/admin/user/vip?uid='+uid,'no'] ,//不允许出现滚动条
                        area:['400px', '250px']
                    });
                }
            });
        });
    </script>
    @endsection
@endsection