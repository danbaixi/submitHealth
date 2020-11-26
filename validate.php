<?php
/**
 * 验证码图片
 * Created By xiaoxi
 * Date: 2020/11/25
 * Time: 14:13
 */
session_start();
error_reporting(0);
require_once 'service.php';
$service = new service();
$serverUID = $_GET['serverUID'];
//获取验证码cookie
$cookie = $service->getCookie($serverUID);

if(!$cookie){
    die('获取cookie失败');
}
$_SESSION['cookie'] = $cookie;
$url = $service->getValidateUrl();

header("content-type: image/jpeg");
$header[] = "accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8";
$header[] = "accept-language: zh-CN,zh;q=0.9";
$header[] = "referer: https://byu.educationgroup.cn/sso/auth";
$header[] = "cookie: ".$cookie;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_COOKIE, $cookie);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$content = curl_exec($ch);
curl_close($ch);
echo $content;

