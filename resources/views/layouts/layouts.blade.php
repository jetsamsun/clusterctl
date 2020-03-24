<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>视频 - Admin</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="{{ URL::asset('/layui/css/layui.css') }}"  media="all">
    <!-- 注意：如果你直接复制所有代码到本地，上述css路径需要改成你本地的 -->

    @yield('css')

    <style>
        .layui-body{
            padding: 15px;
        }
    </style>
</head>
<?php
    use App\Http\Controllers\Admin;
    $menu = new Admin\MenuController();
    $menuList = $menu->getMenus(); // 获取菜单栏

        // 获取当前路由
        $path = \Illuminate\Support\Facades\Request::path();
        $mok = '/error';
        if($path && $path!="/"){
            $path = explode('/',$path);
            if(count($path)>=2){
                $mok=$path['1'];
            }
        }
?>
<body>
    <div class="layui-layout layui-layout-admin">
        <div class="layui-header">
            <div class="layui-logo">视频APP 后台管理</div>
            <!-- 头部区域（可配合layui已有的水平导航） -->
            <ul class="layui-nav layui-layout-left">
                <li class="layui-nav-item"><a href="/admin/index">控制台</a></li>
                {{--<li class="layui-nav-item"><a href="">管理用户</a></li>--}}
                {{--<li class="layui-nav-item"><a href="">用户</a></li>--}}
                {{--<li class="layui-nav-item">--}}
                    {{--<a href="javascript:;">其它系统</a>--}}
                    {{--<dl class="layui-nav-child">--}}
                        {{--<dd><a href="">邮件管理</a></dd>--}}
                        {{--<dd><a href="">消息管理</a></dd>--}}
                        {{--<dd><a href="">授权管理</a></dd>--}}
                    {{--</dl>--}}
                {{--</li>--}}
            </ul>
            <ul class="layui-nav layui-layout-right">
                <li class="layui-nav-item">
                    <a href="javascript:;">
                        <img src="{{ url('/upload/logo.png') }}" class="layui-nav-img">
                        {{ session('username') }}
                    </a>
                    <dl class="layui-nav-child">
                        {{--<dd><a href="">基本资料</a></dd>--}}
                        <dd><a href="{{ url("/admin/changePwd") }}">安全设置</a></dd>
                    </dl>
                </li>
                <li class="layui-nav-item"><a href="{{ url('/admin/loginout') }}">退了</a></li>
            </ul>
        </div>
        <div class="layui-side layui-bg-black">
            <div class="layui-side-scroll">
                <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
                <ul class="layui-nav layui-nav-tree"  lay-filter="test">
                    @foreach($menuList as $value)
                    <li class="layui-nav-item @if(strpos($value['str_p_uri'],$mok)!==false) layui-nav-itemed @endif">
                        <a class="" href="javascript:;">{{ $value['menuname'] }}</a>
                        <dl class="layui-nav-child">
                            @foreach($value['son'] as $v)
                            <dd class="@if(strpos($v['uri'],'/'.$mok)!==false) layui-this @endif"><a href="{{ $v['uri'] }}">{{ $v['menuname'] }}</a></dd>
                            @endforeach
                        </dl>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="layui-body">
            <!-- 内容主体区域 -->
            {{--<div style="padding: 15px;">内容主体区域</div>--}}
            @yield('content')
        </div>

        <div class="layui-footer">
            <!-- 底部固定区域 -->
            © layui.com - 底部固定区域
        </div>
    </div>
<script src="{{ URL::asset('/layui/layui.js') }}" charset="utf-8"></script>
<script src="{{ URL::asset('/layui/jquery.js') }}" charset="utf-8"></script>
<!-- 注意：如果你直接复制所有代码到本地，上述js路径需要改成你本地的 -->
<script>
    //JavaScript代码区域
    layui.use(['element', 'layer', 'jquery'], function(){
        var element = layui.element;
        var layer = layui.layer;
        var $ = layui.$;
    });
</script>

@yield('script')

</body>
</html>