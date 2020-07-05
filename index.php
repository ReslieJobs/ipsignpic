<?php
header("Content-type: text/html; charset=utf-8");
include 'db.inc.php';//引入数据库连接配置
include 'function.php';//引入封装的方法.

$rip=getIP();

$cityname=getCity($rip,$conn);
if(strstr($cityname[0], '0')){
    $lsarea = preg_replace('/0/','',$cityname[0]);
    $nowarea = $lsarea;
}else{
    $nowarea = $cityname[0];
}

$weather=getWeather($cityname[1]);

$nowagent=getSys();
//创建画布
$width=495;
$height=300;
$img=imagecreatefromjpeg("itn.jpg");
$color=imagecolorallocate($img,255,255,255);
//设置透明颜色
$color_alpha=imagecolorallocatealpha($img,143,115,117,0);
$redcolor=imagecolorallocate($img,247,115,115);
$sycolor=imagecolorallocatealpha($img,208,170,173,0);
//设置水印文字
$font=15;
$syfont=8;
$str='Hello world!';
$syfontwidth=imagefontwidth($syfont);
$syfontheight=imagefontheight($syfont);
$fontwidth=imagefontwidth($font);
$fontheight=imagefontheight($font)+25;
$x=38;
$y=$fontheight+$font-5;
$sy=$fontheight+$font*2+20-5;
$ty=$fontheight+$font*3+40-5;
$fy=$fontheight+$font*4+60-5;
$siy=$fontheight+$font*5+80-5;
$sey=$fontheight+$font*6+100-5;
$ey=$fontheight+$font*7+120-5;
$ny=$fontheight+$font*8+140-5;
$syx=$width-$syfontwidth-38;
$syy=$y-8;
imagettftext($img,$font,0,$x,$y,$color_alpha,'./msyh.ttf','欢迎您，来自'.$nowarea.'的网友');
if($weather=='不时会有沙尘暴，注意安全哦！'){
    if($nowarea==''){
        imagettftext($img,$font,0,$x,$sy,$color_alpha,'./msyh.ttf','火星上'.$weather);
    }else{
        imagettftext($img,$font,0,$x,$sy,$color_alpha,'./msyh.ttf','抱歉，获取不到天气哟');
    }
    imagettftext($img,$font,0,$x,$ty,$color_alpha,'./msyh.ttf','您的ip为：'.$rip);
    imagettftext($img,$font,0,$x,$fy,$color_alpha,'./msyh.ttf','您的系统为：'.$nowagent);
    imagettftext($img,$font,0,$x,$siy,$color_alpha,'./msyh.ttf','生成于'.date("Y-m-d H:i:s"));
    if($nowarea==''){
        imagettftext($img,$font,0,$x,$sey,$redcolor,'./msyh.ttf','玄隐铺路局祝您探火愉快！');
    }else{
        imagettftext($img,$font,0,$x,$sey,$redcolor,'./msyh.ttf','玄隐铺路局祝您潜水愉快！');
    }
}else{
    imagettftext($img,$font,0,$x,$sy,$color_alpha,'./msyh.ttf','今天'.$weather[0]->type.' '.$weather[0]->low.' '.$weather[0]->high);
    imagettftext($img,$font,0,$x,$ty,$color_alpha,'./msyh.ttf','明天'.$weather[1]->type.' '.$weather[1]->low.' '.$weather[1]->high);
    imagettftext($img,$font,0,$x,$fy,$color_alpha,'./msyh.ttf','您的ip为：'.$rip);
    imagettftext($img,$font,0,$x,$siy,$color_alpha,'./msyh.ttf','您的系统为：'.$nowagent);
    imagettftext($img,$font,0,$x,$sey,$color_alpha,'./msyh.ttf','生成于'.date("Y-m-d H:i:s"));
    if(strstr($nowarea, '香港')){
      imagettftext($img,$font,0,$x,$ey,$redcolor,'./msyh.ttf','国家安全，香港平安！');
    }else{
      imagettftext($img,$font,0,$x,$ey,$redcolor,'./msyh.ttf','玄隐铺路局祝您潜水愉快！');
    }
}
imagettftext($img,$syfont,0,$syx,$syy,$sycolor,'./zkklt.ttf','图
：
@
藤
子
菌');
//输出画布图像
header("content-type:image/gif");
imagegif($img);
imagedestroy($img);