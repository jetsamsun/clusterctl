<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ \Illuminate\Support\Facades\URL::asset('/layui/css/layui.css') }}"  media="all">
    <!-- 注意：如果你直接复制所有代码到本地，上述css路径需要改成你本地的 -->
</head>
<body>
    <form class="layui-form layui-form-pane" action="" method="post">

        <div class="layui-form-item" style="padding-top: 10px;">
            <div class="layui-card-header"><font color="red">* </font>我的上级</div>
            <div class="layui-input-block">
                一级: {{ $level['first_level'] }}
                二级: {{ $level['second_level'] }}
                三级: {{ $level['third_level'] }}
                四级: {{ $level['fourth_level'] }}
            </div>
        </div>
        <div class="layui-form-item" style="padding-top: 10px;">
            <div class="layui-card-header"><font color="red">* </font>四级</div>
            <div class="layui-input-block">
                @if($res3)
                @foreach( $res3 as $value )
                        {{ $value }}
                @endforeach
                @endif
            </div>
        </div>
        <div class="layui-form-item" style="padding-top: 10px;">
            <div class="layui-card-header"><font color="red">* </font>三级</div>
            <div class="layui-input-block">
                @if($res2)
                    @foreach( $res2 as $value )
                        {{ $value }}
                    @endforeach
                @endif
            </div>
        </div>
        <div class="layui-form-item" style="padding-top: 10px;">
            <div class="layui-card-header"><font color="red">* </font>二级</div>
            <div class="layui-input-block">
                @if($res1)
                    @foreach( $res1 as $value )
                        {{ $value }}
                    @endforeach
                @endif
            </div>
        </div>
        <div class="layui-form-item" style="padding-top: 10px;">
            <div class="layui-card-header"><font color="red">* </font>一级</div>
            <div class="layui-input-block">
                @if($res0)
                    @foreach( $res0 as $value )
                        {{ $value }}
                    @endforeach
                @endif
            </div>
        </div>
    </form>

    <script src="{{ \Illuminate\Support\Facades\URL::asset('/layui/layui.js') }}" charset="utf-8"></script>

</body>
</html>