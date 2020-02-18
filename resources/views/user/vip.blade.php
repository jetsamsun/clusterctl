<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ \Illuminate\Support\Facades\URL::asset('/layui/css/layui.css') }}"  media="all">
    <!-- 注意：如果你直接复制所有代码到本地，上述css路径需要改成你本地的 -->
</head>
<body>
    <form class="layui-form layui-form-pane" action="{{ url('/admin/user/vip?uid='.$uid) }}" method="post">

        <div class="layui-form-item" style="padding-top: 10px;">
            <label class="layui-form-label"><font color="red">* </font>VIP方案</label>
            <div class="layui-input-inline">
                <select name="num" lay-filter="myselect"  lay-verify="required">
                    <option value=""></option>
                    <option  value="30">月卡</option>
                    <option  value="90">季卡</option>
                    <option  value="365">年卡</option>
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block" >
                <button class="layui-btn" lay-submit="" lay-filter="submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

    <script src="{{ \Illuminate\Support\Facades\URL::asset('/layui/layui.js') }}" charset="utf-8"></script>
    <!-- 注意：如果你直接复制所有代码到本地，上述js路径需要改成你本地的 -->
    <script>
        //JavaScript代码区域
        layui.use('form', function(){
            var form = layui.form,$ = layui.jquery;

            //监听提交
            form.on('submit(submit)', function(data){
                //layer.msg(JSON.stringify(data.field));
                //return false;
                $.ajax({
                    url: data.form.action,
                    type: data.form.method,
                    data: data.field,
                    success: function (res) {
                        if(res.code == 1){
                            layer.msg(res.msg, {icon: 1, time: 1000}, function(){
                                //location.href = res.url;
                                window.parent.location.reload();  // 刷新父类
                                parent.layer.close(index);  // 关闭当前弹窗
                            });

                        }else{
                            layer.msg(res.msg, {icon: 2, anim: 6, time: 1000});
                        }
                    }
                });
                return false;
            });
        });
    </script>

</body>
</html>