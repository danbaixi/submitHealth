<?php
/**
 * Created By xiaoxi
 * Date: 2020/11/25
 * Time: 15:22
 */
error_reporting(0);
require_once 'service.php';

$service = new service();

$stuId = $_POST['stu_id'];
$password = $_POST['password'];
$validate = $_POST['validate'];
$cookie = $_SESSION['cookie'];

$data = [
    'loginType' => 'account',
    'username' => base64_encode($stuId),
    'password' => base64_encode($password),
    'code' => $validate,
    'usertel' => '',
    'usertel_code' => ''
];

$url = $service->getLoginUrl();
$ch = curl_init();
$header = [
    "Host: byu.educationgroup.cn",
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:84.0) Gecko/20100101 Firefox/84.0",
    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
    "Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2",
    "Content-Type: application/x-www-form-urlencoded",
    "Origin: https://byu.educationgroup.cn",
    "Connection: keep-alive",
    "Referer: https://byu.educationgroup.cn/sso/auth",
    "Upgrade-Insecure-Requests: 1",
];
curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_VERBOSE,1);
curl_setopt($ch, CURLOPT_HEADER,true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_COOKIE, $cookie);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

$content = curl_exec($ch);
curl_close($ch);

if(preg_match('/验证码输入错误/',$content)){
    //验证码错误
}
die($content);