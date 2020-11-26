<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>一键完成健康申报</title>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
</head>
<body>
    <form action="shenbao.php" method="post">
        <input name="stu_id" placeholder="请输入学号" value="">
        <input name="password" type="password" placeholder="请输入密码" value="">
        <input name="validate" type="text" placeholder="请输入验证码">
        <input type="hidden" id="auto_code" name="auto_code">
        <img src="" id="validate">
        <button id="login" type="submit">确定</button>
    </form>
</body>
</html>
<?php
    require_once 'service.php';
    $service = new service();
?>
<script>
    var url = "<?php echo $service->getWSUrl() ?>";
    var ws = null;
    var dxcount = 0;

    function onOpen(){
        var auth_code = "<?php echo $service->getAuthCode() ?>";
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
                    var serverUID = data.serverUID;
                    document.getElementById('auto_code').value = serverUID
                    document.getElementById('validate').setAttribute('src','validate.php?serverUID=' + serverUID)
                }

            }else{
            }
        };
        return ws;
    }
    createws();
</script>