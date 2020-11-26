<?php
/**
 * Created By xiaoxi
 * Date: 2020/11/25
 * Time: 14:58
 */
session_start();
error_reporting(0);

class service
{
    const DOMAIN = 'byu.educationgroup.cn'; //域名
    const PROTOCOL = 'https'; //协议

    //基本链接
    public function getBaseUrl(){
        return sprintf("%s://%s",self::PROTOCOL,self::DOMAIN);
    }

    // 验证码链接
    public function getValidateUrl(){
        return self::getBaseUrl() . '/sso/auth/genCode?random=' . self::getValidateRandom();
    }

    // 登录页面链接
    public function getAuthUrl(){
        return self::getBaseUrl() . '/sso/auth';
    }

    // 二维码链接
    public function getQrCodeUrl(){
        return self::getBaseUrl() . "/sso/qrAuth/qrcode?auth_code=";
    }

    // 提交登录
    public function getLoginUrl(){
        return self::getBaseUrl() . '/sso/auth/login';
    }

    // 问卷列表
    public function getListUrl(){
        return self::getBaseUrl() . '/wx/wxWjdc/getOpenWjdcSet';
    }

    // websocket连接
    public function getWSUrl(){
        return sprintf("wss://%s/sso/qrAuth.ws",self::DOMAIN);
    }

    // 问卷填写状态
    public function getFormStatusUrl(){
        return self::getBaseUrl() . '/wx/wxWjdc/getWjdc';
    }

    // 问卷提交
    public function getSubmitUrl(){
        return self::getBaseUrl() . '/wx/wxWjdc/save';
    }

    /**
     * 发送http请求
     * @param $url
     * @param null $data
     * @param string $method
     * @param string $http
     * @param string $cookie
     * @return bool|string
     */
    public function httpRequest($url,$data = null,$method = 'GET',$http = 'https',$cookie = ''){
        $curl = curl_init();
        if($method === 'GET' && $data){
            $params = [];
            foreach ($data as $key => $val) {
                $params [] = "$key=$val";
            }
            $url .= (!stripos($url,'?') ? '?' : '&') . join('&',$params);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        if(strtolower($method) == 'post'){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if($http == 'https'){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        if($cookie !== ''){
            curl_setopt($curl, CURLOPT_COOKIE,$cookie);
        }

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    // 验证码随机数，暂时用不上
    protected function getValidateRandom(){
        $random = bcdiv(rand(10000000000000000,99999999999999999),100000000000000000,17);
        return $random;
    }

    //获取cookies
    protected function getCookieRequest($url){
        $ch = curl_init();
        $headers = [
            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language:zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2',
            'Cache-Control:max-age=0',
            'Connection:keep-alive',
            'Host:' . self::DOMAIN,
            'Upgrade-Insecure-Requests:1',
            'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $content = curl_exec($ch);
        curl_close($ch);
        preg_match_all('/Set-Cookie:(.*);/iU', $content, $str);
        if(count($str[1]) == 0){
            //获取cookie失败
            return false;
        }
        return $str[1];
    }

    /**
     * @return bool|string
     * 从cookie中获取auth_code
     */
    public function getAuthCode(){
        $authCookie = $this->getCookieRequest(self::getAuthUrl());
        if(!$authCookie){
            return false;
        }
        foreach ($authCookie as $item){
            if(preg_match('/auth_code=/',$item)){
                return trim(explode('=',$item)[1]);
            }
        }
        return false;
    }

    // 获取cookie
    public function getCookie($serverUID){
        $cookies = [];
        $authCode = $_SESSION['authCode'];

        //获取验证码cookie
        $validateCookie = $this->getCookieRequest(self::getValidateUrl());
        foreach ($validateCookie as $item){
            $cookies [] = trim($item);
        }
        return join(';',$cookies) . ';' . $authCode . ';' . 'serverUID=' . $serverUID;
    }

    //通过token获取数据
    public function getData($url,$token,$data = []){
        $curl = curl_init();
        $cookie = 'Auth-Token='.$token;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "cookie: ".$cookie
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_COOKIE,$cookie);

        if($data){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        if(curl_error($curl)){
            $this->log(curl_error($curl));
        }
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    //记录日志
    public function log($log){
        $filename='./logs/debug.log';
        $file= fopen($filename,"a+");
        fwrite($file,Date('Y-m-d H:i:s') . " ".$log . "\n");
    }
}