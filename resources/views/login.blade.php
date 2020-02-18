<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>登入 - Admin</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{ URL::asset('/layui/css/layui.css') }}" media="all">
    <link rel="stylesheet" href="{{ URL::asset('/layuiAdmin/admin.css') }}" media="all">
    <link rel="stylesheet" href="{{ URL::asset('/layuiAdmin/login.css') }}" media="all">
</head>
<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">

    <div class="layadmin-user-login-main">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2>青瓜TV</h2>
            <p>视频APP后台管理模板系统</p>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body">
            <form class="layui-form" action="/admin/login" method="post">
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-username" for="LAY-user-login-username"></label>
                    <input type="text" name="username" id="LAY-user-login-username" lay-verify="required" placeholder="用户名" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-user-login-password"></label>
                    <input type="password" name="password" id="LAY-user-login-password" lay-verify="required" placeholder="密码" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <div class="layui-row">
                        <div class="layui-col-xs7">
                            <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-user-login-vercode"></label>
                            <input type="text" id="captcha"  name="vercode" id="LAY-user-login-vercode" lay-verify="required" placeholder="图形验证码" class="layui-input">
                        </div>
                        <div class="layui-col-xs5">
                            <div style="margin-left: 10px;" class="captcha">
                                <img src="{{ url('/admin/login/captchamews') }}" title="点击切换" alt="captcha" onclick="this.src='{{ url('/admin/login/captchamews') }}?r='+Math.random();" class="layadmin-user-login-codeimg" id="LAY-user-get-vercode">
                            </div>
                        </div>
                    </div>
                </div>
                {{--<div class="layui-form-item" style="margin-bottom: 20px;">--}}
                    {{--<input type="checkbox" name="remember" lay-skin="primary" title="记住密码">--}}
                    {{--<a href="forget.html" class="layadmin-user-jump-change layadmin-link" style="margin-top: 7px;">忘记密码？</a>--}}
                {{--</div>--}}
                <div class="layui-form-item">
                    <button type="submit" class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-user-login-submit">登 入</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="{{ URL::asset('/layui/layui.js') }}"></script>
<script>
    layui.use('form', function(){
        var form = layui.form,$ = layui.jquery;

        //监听提交
        form.on('submit(LAY-user-login-submit)', function(data){
            //layer.msg(JSON.stringify(data.field));
            $.ajax({
                url: data.form.action,
                type: data.form.method,
                data: data.field,
                success: function (res) {
                    if(res.code == 1){
                        layer.msg(res.msg, {icon: 1, time: 1000}, function(){
                            location.href = res.url;
                        });
                    }else{
                        $('#captcha').val('');
                        layer.msg(res.msg, {icon: 2, anim: 6, time: 1000});
                        $('.captcha img').attr('src','{{ url('/admin/login/captchamews') }}?r='+Math.random());
                    }
                }
            });
            return false;
        });
    });
</script>
</body>
</html>