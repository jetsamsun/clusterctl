<?php
namespace app\Libs;

use App\Models\Config;

class Xyz {
    function __construct() {
        set_time_limit(0);

        $cfgs = Config::get();
        foreach ($cfgs as $v) {
            $this->cfgs[$v['name']] = $v['value'];
        }

        if(strpos(php_uname(), 'Windows') !== false) {
            $this->sypath = './ffmpeg/packs/xxx';
            $this->ffmpeg = PUBLIC_PATH.'/ffmpeg/ffmpeg.exe';
            $this->ffprobe = PUBLIC_PATH.'/ffmpeg/ffprobe.exe';
        } else {
            $this->sypath = PUBLIC_PATH.'/ffmpeg/packs/xxx';
            $this->ffmpeg = '/usr/local/ffmpeg/bin/ffmpeg';
            $this->ffprobe = '/usr/local/ffmpeg/bin/ffprobe';
        }
        $this->gpu = 0;  //开启显卡转码命令
    }

    public function transcode($src_path,$m3u8_path='',$jpg_path='',$gif_path='', $size = '', $rate = '') {
        $src_path = str_replace("//","/",str_replace("\\","/",$src_path));
        $m3u8_path = str_replace("//","/",str_replace("\\","/",$m3u8_path));
        //jpg + gif
        if(!empty($jpg_path)) $jpg = $this->vodtojpg($src_path,$jpg_path);
        if(!empty($gif_path)) $gif = $this->vodtogif($src_path,$gif_path);
        //获取格式命令
        $format = $this->format($src_path);
        //字幕+水印
        $watermark = $this->watermark_zm();
        //缩放
        $Mu_Size = $size;
        $change = !empty($Mu_Size) ? '-s '.$Mu_Size : '';
        $Mu_Kbps = $rate;
        $bit_rate = (int)$format['bit_rate'];
        $kbps = '';
        if($bit_rate > 0){
        	$bit = $bit_rate / 1000;
        	if($Mu_Kbps > 0 && $bit > $Mu_Kbps){
                $kbps = '-b:v '.$Mu_Kbps.'k';
        	}
        }
        //速度
        $prearr = array('ultrafast','superfast','faster','fast','medium','slow','slower');
        $mupreset = $this->cfgs['trans_mode'] ? $this->cfgs['trans_mode'] : 'fast';
        if(!in_array($mupreset, $prearr) || $mupreset == 'medium') $mupreset = '';
        $preset = !empty($mupreset) ? '-preset:v '.$mupreset : '';

		if($this->gpu) {
        	$make_command = $this->ffmpeg." -hwaccel cuvid -y -i $src_path $preset -c:v h264_nvenc -c:a aac -strict -2 $watermark  $kbps  $change  $m3u8_path";
		} else {
			$make_command = $this->ffmpeg." -y  -i  $src_path $preset -c:v libx264 -c:a aac -strict -2 $watermark  $kbps  $change  $m3u8_path";
		}

        exec($make_command,$arr,$log);

        if($log == 0){
            return 'success';
        } else {
            return 'fail';
        }
    }
    //获取视频格式信息
    public function format($src_path){
        $src_path = str_replace("//","/",str_replace("\\","/",$src_path));
        $arr = array(
            'video' => '',
            'audio' => '',
            'duration' => 0,
            'width' => 0,
            'height' => 0,
            'dis_ratio' => '',
            'size' => 0,
            'bit_rate' => 0
        );
        if(empty($src_path)) return $arr;
        $format_command = $this->ffprobe.' -v quiet -print_format json -show_format -show_streams '.$src_path;
        $format = shell_exec($format_command);
        $json = json_decode($format);
        $audio = '';$video = '';

        if(!isset($json->streams)) {
            $temp['cmd'] = $format_command;
            $temp['ret'] = $format;
//            file_put_contents(__DIR__.'/error_streams_'.date('Y-m-d').'.txt', date('Y-m-d H:i:s').' '.$src_path.PHP_EOL,FILE_APPEND);

            $arr['video'] = '';
            $arr['duration'] = 0;
            $arr['width'] = 0;
            $arr['height'] = 0;
            $arr['dis_ratio']= '';
            $arr['audio'] = '';
            $arr['size'] = 0;
            $arr['bit_rate'] = 0;
            $arr['ext'] = '';

            return $arr;
        }

        foreach($json->streams as $row){
            if($row->codec_type=='video'){
                $arr['video'] = $row->codec_name;
                $arr['duration'] = $row->duration;
                $arr['width'] = $row->width;
                $arr['height'] = $row->height;

                if(!isset($row->display_aspect_ratio)) {
//                    file_put_contents(__DIR__.'/error_display_aspect_ratio_'.date('Y-m-d').'.txt', date('Y-m-d H:i:s').' '.$format.PHP_EOL,FILE_APPEND);
                }

                $arr['dis_ratio']= isset($row->display_aspect_ratio)?$row->display_aspect_ratio:'';
            }
            if($row->codec_type=='audio'){
                $arr['audio'] = $row->codec_name;
            }
        }
        if(empty($arr['duration'])) $arr['duration'] = $json->format->duration;
        $arr['size'] = $json->format->size;
        $arr['bit_rate'] = $json->format->bit_rate;
        $arr['ext'] = $json->format->format_name;
        return $arr;
    }
    //视频截图JPG
    function vodtojpg($src_path,$obj_path, $time = 5){
        $jpgsize = $this->cfgs['shot_size'];
        $size = !empty($jpgsize) ? '-s '.$jpgsize : '';
        $tmp = explode('/', $obj_path);
        $filename = end($tmp);
        $jpg_command = $this->ffmpeg.' -y -i '.$src_path.' -y -f image2 -ss '.$time.' '.$size.' -t 0.001 '.$obj_path;
        $jpg = exec($jpg_command,$arr,$log);
        if($log==0){
            return 'ok';
        }else{
            return $jpg_command;
        }
    }
    //截取GIF
    function vodtogif($src_path,$obj_path,$gif_pos=3,$gif_len=3){
        $Gif_Size = $this->cfgs['shot_gif_size'];
        $size = !empty($Gif_Size)?'-s '.$Gif_Size:'';
        $gif_command = $this->ffmpeg.' -y -ss '.$gif_pos.' -t '.$gif_len.' -i '.$src_path.' '.$size.' -f gif '.$obj_path;
        $gif = exec($gif_command,$arr,$log);
        if($log==0){
            return 'ok';
        }else{
            return '';
        }
    }
    //水印OR字幕
    function watermark_zm(){
        $cmd = '';
        $markspace = $this->cfgs['mark_space'] ? $this->cfgs['mark_space'] : '10:10';
        $sypath = $this->sypath;
        //左上
        if( $this->cfgs['mark_zs'] ){
            $cmd = '-vf "movie='.$sypath.'.png[wm1];[in][wm1]overlay='.$markspace.'[out]"';
        }
        //右上
        if( $this->cfgs['mark_ys'] ){
            $mar_arr = explode(':', $markspace);
            $mar1 = intval($mar_arr[0]);
            $mar2 = intval($mar_arr[1]);
            $wm = 'overlay=main_w-overlay_w-'.$mar1.':'.$mar2;
            if(empty($cmd)){
                $cmd = '-vf "movie='.$sypath.'.png[wm2];[in][wm2]'.$wm.'[out]"';
            }else{
                $cmd = str_replace('[in]', 'movie='.$sypath.'.png[wm2];[in][wm2]'.$wm.'[a];[a]', $cmd);
            }
        }
        //左下
        if( $this->cfgs['mark_zx'] ){
            $mar_arr = explode(':', $markspace);
            $mar1 = intval($mar_arr[0]);
            $mar2 = intval($mar_arr[1]);
            $wm = 'overlay='.$mar1.':main_h-overlay_h-'.$mar2;
            if(empty($cmd)){
                $cmd = '-vf "movie='.$sypath.'.png[wm3];[in][wm3]'.$wm.'[out]"';
            }else{
                $cmd = str_replace('[in]', 'movie='.$sypath.'.png[wm3];[in][wm3]'.$wm.'[b];[b]', $cmd);
            }
        }
        //右下
        if( $this->cfgs['mark_yx'] ){
            $mar_arr = explode(':', $markspace);
            $mar1 = intval($mar_arr[0]);
            $mar2 = intval($mar_arr[1]);
            $wm = 'overlay=main_w-overlay_w-'.$mar1.':main_h-overlay_h-'.$mar2;
            if(empty($cmd)){
                $cmd = '-vf "movie='.$sypath.'.png[wm4];[in][wm4]'.$wm.'[out]"';
            }else{
                $cmd = str_replace('[in]', 'movie='.$sypath.'.png[wm4];[in][wm4]'.$wm.'[c];[c]', $cmd);
            }
        }
        return $cmd;
    }
    function cutvideo($src_path, $obj_path, $stime, $etime = 20)
    {
        $cmd = $this->ffmpeg."  -ss $stime -t $etime -y -i $src_path -vcodec copy -acodec copy $obj_path";
        exec($cmd,$arr,$log);
        if($log == 0){
            return 'ok';
        }else{
            return $arr;
        }
    }
    function generategif($src_path, $obj_path, $stime, $etime, $ra = 1){
        $gif_pos = $stime;//开始位置
        $gif_len = $etime-$stime;//截取时长
        $gif_command = $this->ffmpeg.' -y -ss '.$gif_pos.' -t '.$gif_len.' -y -i '.$src_path.' -f gif -r '.$ra.' '.$obj_path;
        
        exec($gif_command,$arr,$log);
        if($log==0){
            return 'ok';
        }else{
            return '';
        }
    }
    /**
     * 按秒取图片
     */
    function generateimages($src_path, $obj_path, $image_pre, $stime, $etime, $ra = 1)
    {
        $ra = $this->cfgs['shot_gif_space'] ? $this->cfgs['shot_gif_space'] : $ra;
        $command = $this->ffmpeg." -y -i $src_path -f image2 -r $ra -ss $stime -to $etime  $obj_path".$image_pre."%3d.jpg";
        exec($command,$arr,$log);
        if($log==0){
            return 'ok';
        }else{
            return '';
        }
    }
    /**
     * 将图片合成gif
     */
    function imagestogif($src_path, $obj_path)
    {
        $command = $this->ffmpeg." -f image2 -framerate 1 -y -i $src_path $obj_path";
        exec($command,$arr,$log);
        if($log==0){
            return 'ok';
        }else{
            return '';
        }
    }
    function transm3u8($src_path, $obj_path, $space=180)
    {
        $space = $this->cfgs['trans_ts_space'] ? $this->cfgs['trans_ts_space'] : $space;
        $ts_path = dirname($obj_path);
        $aes = $this->m3u8aes($ts_path);
        
        if($this->gpu) {
			$command = $this->ffmpeg.' -hwaccel cuvid -y -i '.$src_path.' -vcodec copy -acodec copy -hls_time '.$space.' '.$aes.' -hls_segment_filename '.$ts_path.'/%04d.ts -hls_list_size 0 '.$obj_path;
        } else {
        	$command = $this->ffmpeg.' -y -i '.$src_path.'  -codec copy -vbsf h264_mp4toannexb -map 0 -f segment -segment_list '.$obj_path.' -segment_time '.$space.' '.$ts_path.'/%04d.ts';
        }
		exec($command,$arr,$log);

        if($log==0){
            return 'success';
        }else{
            return 'fail';
        }
    }
    //m3u8加密
    function m3u8aes($path){
        if(!$this->cfgs['trans_secret_on']) return '';
        $src_str = '0123456789abcdefghijklmnopqrstuvwxyz';
        $aes_key = substr(str_shuffle($src_str), 0, 16);
        if(substr($path, -1)!='/') $path .= '/';
        if(!is_dir($path)) mkdir($path,0777,true);
        $fp =fopen($path.'key.key', 'w');
        fwrite($fp, $aes_key);
        fclose($fp);
        $keyinfo = "key.key\n".$path."key.key";
        $fp = fopen($path.'key_info', 'w');
        fwrite($fp, $keyinfo);
        fclose($fp);
        return '-hls_key_info_file '.$path.'key_info';
    }
}
