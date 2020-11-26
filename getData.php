<?php
require_once 'service.php';
error_reporting(0);

$token = $_GET['token'];
$action = $_GET['action'];
$params = $_GET['params'] ? $_GET['params'] : null;

if($params){
    $params = json_decode(urldecode($params),true);
}

$service = new service();

switch ($action){
    case 'list':
        //问卷列表
        $url = $service->getListUrl();
        break;
    case 'status':
        //问卷详情
        $url = $service->getFormStatusUrl();
        break;
    case 'submit':
        //提交问卷
        $url = $service->getSubmitUrl();
        break;
}
$result = $service->getData($url,$token,$params);
if($action === 'list'){
    $service->log($result);
}
$result = json_decode($result);

die(json_encode([
    'status' => 0,
    'data' => $result ? $result : []
]));