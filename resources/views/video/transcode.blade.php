@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        鉴于小伙伴的普遍反馈，先温馨提醒。
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>视频转码  </legend>
    </fieldset>

    <form class="layui-form" action="{{ url('/admin/video/transcode/'.$vid) }}" method="post">
        <input style="display: none" type="text" name="ids" value="{{$vid}}" title="" checked>
        <div class="layui-form-item">
            <label class="layui-form-label"><font color="red">* </font>码率尺寸</label>
            <div class="layui-input-block">
                @foreach($siterate as $value)
                    <input type="checkbox" name="siterate[]" value="{{ $value['rate'] }}-{{ $value['width'] }}x{{ $value['heigth'] }}" lay-verify="required"  @if($value['default']==1) checked @endif lay-skin="primary" title="{{ $value['width'] }}x{{ $value['heigth'] }}">
                @endforeach
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">是否切片</label>
            <div class="layui-input-block">
                <input type="checkbox" name="is_slice" value="1" title="切片" checked>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="submit">开始转码</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

    @section('script')
    <script>
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
                        if(res.code === 1){
                            layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                                //window.location.href = "/admin/video";
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
    @endsection
@endsection