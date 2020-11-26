<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>一键上报健康信息</title>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        body{
            max-width: 500px;
            height: auto;
            margin: 0 auto;
            padding: 20px;
            font-family: "SF Pro SC","SF Pro Display","SF Pro Icons","PingFang SC","Helvetica Neue","Helvetica","Arial",sans-serif;

            --themeColor: #61b15a
        }
        p{
            margin: 10px 0;
        }
        button{
            padding: 6px 12px;
            background: var(--themeColor);
            color: #fff;
            border: 0;
            border-radius: 4px;
        }
        .object-title{
            font-weight: 400;
            text-align: center;
            font-size: 26px;
            color: var(--themeColor);
            letter-spacing: 0.2em;
        }
        .section{
            padding: 10px 0;
        }
        .section .title{
            position: relative;
            color: var(--themeColor);
            font-size: 20px;
            margin: 10px 0;
        }
        .section .title::before{
            content: '';
            position: absolute;
            top: 0;
            left: -10px;
            width: 4px;
            height: 100%;
            background: var(--themeColor);
        }
        .display{
            display: block;
        }
        .hidden{
            display: none;
        }
        #qr-code{
            width: 100px;
            height: 100px;
        }
    </style>
</head>
<body>
<div>
    <div class="object-title">一键上报健康信息</div>
    <div class="section">
        <p class="title">登录状态</p>
        <div class="content">
            <div id="un-login">
                <p>未登录，请扫描二维码登录（长按识别无效）</p>
                <img src="" id="qr-code">
            </div>
            <div id="login" class="hidden">
                <p>
                    <span>已登录</span>
                    <button id="logout">退出登录</button>
                </p>
            </div>
        </div>
    </div>
    <div class="section list">
        <p class="title">问卷列表</p>
        <div class="content">
            <div id="lists"><p>登陆后即可获取</p></div>
            <!-- <div><button id="getList">刷新列表</button></div> -->
        </div>
    </div>
    <div class="section">
        <p class="title">填写状态</p>
        <div class="content">
            <p id="form-status">无</p>
            <button id="submit-form" class="hidden">一键提交问卷</button>
        </div>
    </div>

</div>
</body>
</html>
<?php
require_once 'service.php';
$service = new service();
?>
<script>
    var url = "wss://" + "<?php echo $service::DOMAIN ?>" +"/sso/qrAuth.ws";
    var ws = null;
    var dxcount = 0;
    var auth_code = "<?php echo $service->getAuthCode() ?>";
    var serverUID = ""
    var token = ""
    var storageName = 'authToken' //缓存
    var form = {}
    var formData = {}
    var tbInfo = null

    init()

    //初始化
    function init(){
        var local = JSON.parse(localStorage.getItem(storageName))
        var time = parseInt(new Date().getTime() / 1000)
        var loginDom = document.getElementById('login')
        var unLoginDom = document.getElementById('un-login')
        if(local && local.time + local.expired > time){
            token = local.value
            unLoginDom.className = 'hidden'
            loginDom.className = 'display'
            getList()
        }else{
            unLoginDom.className = 'display'
            loginDom.className = 'hidden'
            document.getElementById('lists').innerHTML = '<p>请先登录</p>'
            createws();
        }
    }

    function onOpen(){
        var msg = {};
        msg.auth_code = auth_code;
        msg.method = "register";
        if(ws&&ws.readyState==1){
            ws.send(JSON.stringify(msg));
        }else{
            window.setTimeout("onOpen()",200);
        }

    }

    function createws(){
        if(ws==null){
            if('WebSocket' in window){
                ws = new WebSocket(url);
                ws.onclose = function(evt) {
                    dxcount ++;
                    console.log("websocket掉线："+dxcount+"次数");
                    ws = new WebSocket(url);
                    console.log("重连"+dxcount+"次数");
                }
                ws.onopen = onOpen();
            }else if('MOzWebSocket' in window){
                ws = new MozWebSocket(url);
                ws.onclose = function(evt) {
                    dxcount ++;
                    console.log("websocket掉线："+dxcount+"次数");
                    ws = new WebSocket(url);
                    console.log("重连"+dxcount+"次数");
                }
                ws.onopen = onOpen();
            }else{
                console.log("浏览器太旧，不支持");
            }
        }

        ws.onmessage = function(event){
            console.log("receive message:"+event.data);
            var data = JSON.parse(event.data)
            if(data.errcode==0){
                if(data.method=="register"){
                    serverUID = data.serverUID;
                    refreshQrCode();
                }else if(data.method == "auth"){
                    token = data.data.auth_token
                    localStorage.setItem(storageName,JSON.stringify({
                        time: parseInt(new Date().getTime() / 1000),
                        expired: 30 * 60 - 10,
                        value: token
                    }))
                    init()
                }else if(data.method=="handshake"){
                    console.log("接收心跳成功！");
                }

            }else{
            }
        };
        return ws;
    }

    function refreshQrCode(){
        var time = new Date().getTime();
        document.getElementById('qr-code').setAttribute('src',"<?php echo $service->getQrCodeUrl() ?>"+auth_code+"&serverUID="+serverUID+"&time="+time)
    }

    //document.getElementById('getList').addEventListener('click',getList)
    document.getElementById('logout').addEventListener('click',logout)
    document.getElementById('submit-form').addEventListener('click',submitForm)

    //重置token过期时间
    //每次请求，都重置30分钟
    function resetExpired(){
        var local = JSON.parse(localStorage.getItem(storageName))
        if(local){
            localStorage.setItem(storageName,JSON.stringify({
                time: parseInt(new Date().getTime() / 1000),
                expired: 30 * 60 - 10,
                value: local.value
            }))
        }
    }

    //获取申报项目
    function getList(){
        if(token === ''){
            alert('请先登录')
            return
        }
        axios.get('./getData.php',{
            params:{
                'action': 'list',
                'token': token
            }
        }).then((res) => {
            resetExpired()
            var dom = document.getElementById('lists')
            if(res.data.data.length === 0){
                dom.innerHTML = '<p>暂无可用问卷</p>'
                return
            }
            form = res.data.data[0]
            dom.innerHTML = '<p>' + form.name + '</p>'
            getStatus()
        })
    }

    //获取填写状态
    function getStatus(){
        if(token === ''){
            alert('请先登录')
            return
        }
        if(!form){
            alert('没有问卷')
            return
        }
        axios.get('./getData.php',{
            params:{
                'action': 'status',
                'token': token,
                'params': decodeURIComponent(JSON.stringify({set_id: form.id}))
            }
        }).then((res) => {
            resetExpired()
            var data = res.data.data.data
            var dom = document.getElementById('form-status')
            if(data.tbInfo){
                tbInfo = data.tbInfo
                dom.innerText = '已填写，填写时间：' + data.tbInfo.scrq
                document.getElementById('submit-form').className = 'hidden'
            }else{
                dom.innerText = '未填写'
                document.getElementById('submit-form').className = 'display'
            }
            var nr = data.nr
            var items = []
            for(var index in nr){
                var option = nr[index]
                items.push(option['step_id'])
                formData[option['step_id']] = option['det_ids'] ? option['det_ids'] : option['det_names']
            }
            formData['items'] = items.join(',')
            formData['set_id'] = form.id
        })
    }

    function submitForm(){
        if(token === ''){
            alert('请先登录')
            return
        }
        if(!form){
            alert('没有问卷可提交')
            return
        }
        if(tbInfo){
            alert('已提交，请勿重复提交')
            return
        }
        axios.get('./getData.php',{
            params:{
                'action': 'submit',
                'token': token,
                'params': decodeURIComponent(JSON.stringify(formData))
            }
        }).then((res) => {
            if(Number(res.data.data.status) === 1){
                alert("提交成功")
                getStatus()
                return
            }
            alert('提交失败，请重试')
        })
    }

    //退出登录
    function logout(){
        localStorage.removeItem(storageName)
        init()
    }

</script>