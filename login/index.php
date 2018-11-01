<?php error_reporting(0); include("../lib/settings.php"); ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Login-FunnySQL</title>
	<link rel="shortcut icon" href="<?php echo $domain.$path;?>res/favicon.png">
    <link href="https://fonts.googleapis.com/css?family=Spicy+Rice" rel="stylesheet">

</head>
<?php  unset($_COOKIE['funnysql']); setcookie('funnysql',null,-1,$path)?>
<style>
    *{
        box-sizing:border-box
    }
    body{
        background:url("<?php echo $domain.$path;
    ?>res/bgImage.jpg") no-repeat fixed center ;
        background-size:cover;
        width:100%
    }
    .main{
        position:absolute;
        margin:auto;
        top:0;
        left:0;
        bottom:0;
        right:0;
        height:500px;
        text-align:center
    }
    a{
        text-decoration:none;
        color:black
    }
    header{
        width:100%;
        text-align:center;
        font-size:65px;
        font-family: 'Spicy Rice', cursive;;
        margin-bottom:90px
    }
    form{
        max-width:360px;
        margin:0 auto
    }
    .input{
        width:100%;
    }
    input{
        height:40px;
        width:100%;
        border:0;
        font-size:12px;
        background:rgba(255,255,255,.7);
        padding:8px 12px;
        margin-bottom:10px
    }
    input[type='submit']{
        cursor: pointer;
        background:#f5bc22;
        font-size: 16px;
        outline: none;
    }
    input[type='submit']:hover {
        background: #ff7300;
    }
    input[type='submit']:active{
        background:red
    }
    input[type='focus']:focus{
        background:blue
    }
    #msg{
        text-align:center;
        color: #ffffff;
        font-size:14px;
        z-index:999;
        position: fixed;
        padding: 10px;
        top:0;
        left: 0;
        right: 0;
        margin: 0 auto;
        width: 500px;
        display: none;
    }
    #msg-close {
        float: right;
        color: #eaeaea;
        cursor: pointer;
    }
    #msg-close:hover {
        color: #dedede;
    }
    #msg-close:active {
        color: #ffffff;
    }

    .msgShow{
        animation-name:fadeInUp;
        animation-duration:1s;
    }
    @keyframes fadeInUp{
        0%{
            opacity:0;
            transform:translateY(20px)
        }
        100%{
            opacity:1;
            transform:translateY(0)
        }
    }


</style>
<body>
    <div id="msg"><span id="msg-body"></span><span id="msg-close" style="cursor: pointer;">X</span></div>
	<div class="main">
		<header class="header"><a href="<?php echo $domain.$path; ?>">FunnySQL</a></header>
		<form onsubmit="return false">
			<input type="text" name="host" placeholder="IP地址" class="input">
			<input type="number" name="port" placeholder="端口" value="3306" class="input">
			<input type="text" name="userName" placeholder="用户名" class="input">
			<input type="password" name="password" placeholder="密码" class="input">
			<input type="submit" class="submit" value="连接">
		</form>
	</div>
	<script src="<?php echo $path?>lib/jquery.min.js"></script>
	<script>
		$(document).ready(function(){
            function showMsg(Message, type) {
                let color = 'red';
                if(type === undefined || type === 'error')
                    color = "red";
                else if (type === 'success')
                    color = "#00ff2b";
                if(Message != null) {
                    $("#msg").css('background',color).addClass("msgShow").find('#msg-body').text(Message).parent('#msg').show();
                    setTimeout(function(){
                        $("#msg").removeClass("msgShow").find('#msg-body').text('').parent('#msg').hide();
                    }, 3333)
                }
            }
            $("#msg-close").click(function () {
                $("#msg").removeClass("msgShow").hide().find('#msg-body').text('');
            });
            $("input[name='host']").focus();
			$(".submit").click(function(){
                const $host = $("input[name='host']");
                const $port = $("input[name='port']");
                const $userName = $("input[name='userName']");
                const $password = $("input[name='password']");
                let host = $host.val();
                let port = $port.val();
                let userName = $userName.val();
                let password = $password.val();
				if(host === '') {
				    showMsg("IP地址不能为空！");
				    $host.focus().select();
                } else{
					const patternHost = /^(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)$/;
                    if(!patternHost.test(host)) {
					    showMsg("IP地址错误，请重新输入!");
					    $host.focus().select();
                    } else {
                        if(port === '') {
                            showMsg("端口不能为空！");
                            $port.focus().select();
                        } else {
                            const patternPort = /^(\d|[1-9]\d{1,3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/g;
                            if(!patternPort.test(port)) {
                                showMsg("端口错误，请重新输入！");
                                $port.focus().select();
                            } else {
                                if(userName === '') {
                                    showMsg('用户名不能为空！');
                                    $userName.focus().select();
                                } else {
                                    $.ajax({
                                        url: "../lib/Processing.php",
                                        type: "post",
                                        dataType: "json",
                                        timeout: 3000,
                                        data: {'type': '1','host':host,'port':port,'userName':userName,'password':password},
                                        success:function(data){
                                            if(data.success) {
                                                window.location.href = "<?php echo $path;?>";
                                            } else {
                                                showMsg(data.msg);
                                            }
                                        },
                                        error:  function(){
                                            showMsg('连接超时，请稍后重试！');
                                        }
                                    });
                                }
                            }
                        }
                    }
                }
			});
		});
	</script>
</body>
</html>