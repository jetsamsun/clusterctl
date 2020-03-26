@extends('layouts.layouts')
@section('content')
    <blockquote class="layui-elem-quote layui-text">
        转码切片完成才可进行同步操作。
    </blockquote>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>视频同步</legend>
    </fieldset>


    <form class="layui-form" action="{{ url('/admin/video/sync/'.$vid) }}" method="post">
        <div class="layui-form-item">
            <label class="layui-form-label">标题名称</label>
            <div class="layui-input-block">
                <span style="line-height: 40px;">{{$filestr}}</span>
            </div>
        </div>

        <input style="display: none" type="text" name="ids" value="{{$vid}}" title="" checked>

        <div class="layui-form-item">
            <label class="layui-form-label">从属主体</label>
            <div class="layui-input-inline">
                <select name="media" lay-search lay-tools>
                    <option value="">请选择从属主体</option>
                    @foreach($media as $value)
                        <option  value="{{ $value['Id'] }}">{{ $value['Name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">移除记录</label>
            <div class="layui-input-block">
                <input type="checkbox" name="is_move" value="1" title="移除" @if($cfgs['syncdo']) checked @endif>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="submit">确认同步</button>
                <button type="button" class="layui-btn layui-btn-primary" onclick="javascript:history.back(-1);return false;">返 回</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        layui.use('form', function(){
            var form = layui.form,$ = layui.jquery;
            //监听提交
            form.on('submit(submit)', function(data){
                $.ajax({
                    url: data.form.action,
                    type: data.form.method,
                    data: data.field,
                    success: function (res) {
                        if(res.code === 1){
                            layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                                window.location.href = "/admin/video";
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