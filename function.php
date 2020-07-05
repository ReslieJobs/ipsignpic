<?php
/*
 *https://github.com/lionsoul2014/ip2region
 */


header("Content-type:text/json;charset=UTF-8");

//获取用户ip
function getIP()
{
    static $realip;
    if (isset($_SERVER)){
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")){
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }
    return $realip;
}

//根据ip查找位置
$result = array(
    'time' => Null,//微秒时间戳
    'info' => Null,
    'data' => Null,
    'status' => '1'
);
//封装一个使用ip2region数据库的方法
function findIp($fip){
    $t1 = microtime(true);
    if (filter_var($fip, FILTER_VALIDATE_IP)) {
        $dbFile = dirname(__FILE__) . '/ip2region.db';
        require dirname(__FILE__) . '/Ip2Region.class.php';
        $ip2regionObj = new Ip2Region($dbFile);
        $method = 'btreeSearch';
        $algorithm = 'B-tree';
        $data = $ip2regionObj->{$method}($fip);
        $result['info'] = 'success';
        $result['status'] = '0';
        $result['data'] = $data;
    } else {
        $result['info'] = 'ip不合法';
        $result['status'] = '1';
    }
    $result['time'] = microtime(true) - $t1;
    return $result;
}
//使用上面的方法查找并处理数据
function getCity($nip,$conn)
{
        $cip=explode("|",findIp($nip)['data']['region']);
        if((string)$cip->status=='1'){
          return false;
        }
        $country = $cip[0];
        $region = $cip[2];
        $city = $cip[3];
        $mars='火星';
    if($country==''||$country=='0'){
        return $mars;
    }else{
        $cityn = $country.$region.preg_replace('/0/','',$city);
          if($city != '0'){
            $citynz = preg_replace('/市/','',$city);
            $sql = "SELECT * FROM `citcode` WHERE `cityname` = '$citynz'";
            $query = mysql_query($sql,$conn);
            $row = mysql_fetch_row($query);
            $code = $row[1];
          }else{
            if($region != '0'){
              if(strstr($region, '台湾')){
                $code = '101340101';
              }else if($region == '香港'||$region == '澳门'){
                $sql = "SELECT * FROM `citcode` WHERE `cityname` = '$region'";
                $query = mysql_query($sql,$conn);
                $row = mysql_fetch_row($query);
                $code = $row[1];
              }else{
                $code = $region;
              }
            }else{
              $code = $country;
            }
          }
        $out = array();
        array_push($out,$cityn,$code);
        return $out;
    }
}

// 获取天气
function getWeather($ctna)
{
    $url="http://t.weather.sojson.com/api/weather/city/".$ctna;
    $cht = curl_init();
        //设置选项，包括URL
        //伪装useragent，可用可不用，遇到401错误的时候可以尝试用一下 curl_setopt($cht, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36');
        curl_setopt($cht, CURLOPT_URL, $url);
        curl_setopt($cht, CURLOPT_HEADER, 0);
        curl_setopt($cht, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cht, CURLOPT_SSL_VERIFYPEER, false);  
        curl_setopt($cht, CURLOPT_SSL_VERIFYHOST, false);  
        curl_setopt($cht, CURLOPT_ENCODING, '');  
        curl_setopt($cht, CURLOPT_FOLLOWLOCATION, 1);
        //执行并获取HTML文档内容
        $output = curl_exec($cht);
        curl_close($cht);
        $wip=json_decode($output);   
        if((string)$wip->status=='1'){
          return false;
        }
        $wdata=$wip->data->forecast;
        $marsw='不时会有沙尘暴，注意安全哦！';
        if($wip->cityInfo->city==''){
            return $marsw;
        }else{
            return $wdata;
        }
}

//根据useragent获取系统类型
function getSys(){
    $agent = $_SERVER['HTTP_USER_AGENT'];  //获取useragent
    $os = false;  
    // print_r($agent);
    if (preg_match('/win/i', $agent) && strpos($agent, '95'))  
    {
      if(preg_match('/Phone 10.0/i', $agent)){//避免关键词95和lumia95x系列设备型号冲突
        $os = 'WP10';  
      }else{
        $os = 'Windows 95';
      }
    }
    else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))  
    {  
      $os = 'Windows ME';  
    }
    else if (preg_match('/win/i', $agent) && preg_match('/Phone 10.0/i', $agent))  
    {
      $os = 'WP10';  
    }
    else if (preg_match('/win/i', $agent) && preg_match('/Phone 8.0/i', $agent))  
    {
      $os = 'WP8';  
    }
    else if (preg_match('/win/i', $agent) && preg_match('/Phone OS 7/i', $agent))  
    {
      $os = 'WP7';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))  
    {  
      $os = 'Windows 98';  
    }  
    else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))  
    {  
      $os = 'Windows Vista';  
    }  
    else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))  
    {  
      $os = 'Windows 7';  
    }  
      else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))  
    {  
      $os = 'Windows 8';  
    }else if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))  
    {  
      $os = 'Windows 10';
    }else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))  
    {  
      $os = 'Windows XP';  
    }  
    else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))  
    {  
      $os = 'Windows 2000';  
    }  
    else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))  
    {  
      $os = 'Windows NT';  
    }  
    else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))  
    {  
      $os = 'Windows 32';  
    }  
    else if (preg_match('/linux/i', $agent))  
    {  
      $os = 'Linux';  
      		if ( preg_match( '/Android.([0-9. _]+)/i', $agent, $matches ) ) {
      		$aver = explode("Android",$agent)[1];
      		$avnum = explode(";",$aver)[0];
			$os = 'Android'.$avnum;
		} elseif ( preg_match( '#Ubuntu#i', $agent ) ) {
			$os = 'Ubuntu';
		} elseif ( preg_match( '#Debian#i', $agent ) ) {
			$os = 'Debian';
		} elseif ( preg_match( '#Fedora#i', $agent ) ) {
			$os = 'Fedora';
		}
    }
    else if (preg_match('/MeeGo/i', $agent))  
    {  
      $os = 'MeeGo';  
    }
    else if (preg_match('/iPhone/i', $agent))  
    {  
      $os = 'iOS';  
    }
    else if (preg_match('/iPad/i', $agent))  
    {  
      $os = 'iPadOS';  
    }
    else if (preg_match('/KAIOS/i', $agent))  
    {  
      $os = 'KAIOS';  
    }
    else if (preg_match('/BB10/i', $agent))  
    {  
      $os = 'BB10';  
    }
    else if (preg_match('/RIM Tablet OS/i', $agent))  
    {  
      $os = 'RIM Tablet OS';  
    }
    else if (preg_match('/unix/i', $agent))  
    {  
      $os = 'Unix';  
    }  
    else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))  
    {  
      $os = 'SunOS';  
    }  
    else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))  
    {  
      $os = 'IBM OS/2';  
    }  
    else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))  
    {  
      $os = 'MacOS';  
    }  
    else if (preg_match('/PowerPC/i', $agent))  
    {  
      $os = 'PowerPC';  
    }  
    else if (preg_match('/AIX/i', $agent))  
    {  
      $os = 'AIX';  
    }  
    else if (preg_match('/HPUX/i', $agent))  
    {  
      $os = 'HPUX';  
    }  
    else if (preg_match('/NetBSD/i', $agent))  
    {  
      $os = 'NetBSD';  
    }  
    else if (preg_match('/BSD/i', $agent))  
    {  
      $os = 'BSD';  
    }  
    else if (preg_match('/OSF1/i', $agent))  
    {  
      $os = 'OSF1';  
    }  
    else if (preg_match('/IRIX/i', $agent))  
    {  
      $os = 'IRIX';  
    }  
    else if (preg_match('/FreeBSD/i', $agent))  
    {  
      $os = 'FreeBSD';  
    }  
    else if (preg_match('/teleport/i', $agent))  
    {  
      $os = 'teleport';  
    }  
    else if (preg_match('/flashget/i', $agent))  
    {  
      $os = 'flashget';  
    }  
    else if (preg_match('/webzip/i', $agent))  
    {  
      $os = 'webzip';  
    }  
    else if (preg_match('/offline/i', $agent))  
    {  
      $os = 'offline';  
    }
    else  
    {
      $os = '未知操作系统';  
    }  
    return $os;
}
?>