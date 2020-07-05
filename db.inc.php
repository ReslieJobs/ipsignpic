<?php
/*
Name: 数据库配置文件
Author: Reslie
Version: 1.0.0
*/

//数据库主机
$host = "localhost";

//数据库用户名
$user = "username";

//数据库密码
$pwd = "password";

//数据库名
$dbname = "dbname";

$conn = @mysql_connect($host,$user,$pwd);
if (!$conn){
	die('数据库出错，错误返回: ' . mysql_error());
	exit();
}
mysql_query("set names 'utf8mb4'");
mysql_select_db($dbname,$conn);
global $conn;
// echo 'test';