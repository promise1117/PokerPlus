<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head profile="http://gmpg.org/xfn/11">
<meta name="renderer" content="webkit">
<meta  charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
<title>扑克圈官网</title>
  
<style>
  
*{margin: 0; padding: 0}
  
html {
    background: #fff;
    font-size: 100px;
    color: #424242;
    font-family: "Microsoft Yahei","Hiragino Sans GB","Helvetica Neue",Helvetica,Arial,sans-serif;
}
 
body {
    min-width: 320px;
    max-width: 640px;
    font-size: 0.16rem;
    margin: 0 auto;
    color: #5f646d;
    text-rendering: optimizeLegibility;
}
  
ol, ul {
  list-style: outside none none;
}
 
img {
    border:0;
    width: 100%;
    max-width:100%;
}
  
div.content {
    width: 100%;
	max-height: 2580px;
	background:url(/Public/Img/web.jpg) no-repeat;
	background-size: 100% auto;
	padding-top: 665%;	
}

div.menu_btn {
	width: 0.21rem;
	height: 0.2rem;
	position: absolute;
	right: 0.1rem;
	top: 0.1rem;
}

nav {
	font-family: 'Microsoft YaHei';
	font-size: 0.14rem;
	font-weight: normal;
	background: #1ca9e9;
	position: absolute;
	right: 0;
	top: 0.30rem;
	display: none;
}

nav ul li {
	list-style: none;
}

nav ul li a {
	display: block;
	height: 0.28rem;
	line-height: 0.28rem;
	width: 0.6rem;
	color: #fff;
	text-decoration: none;
	padding-left: 0.08rem;
}

div.download_btn a {
	position: absolute;
	left: 0.72rem;
	top: 1.26rem;
	display: block;
	width: 1.76rem;
	height: 0.29rem;
}

</style>
  
</head>
<body>
  
<div class="content">
	<div class="download_btn"><a href="http://download.yuepoker.com/PokerMate.apk"></a></div>	
	<div class="menu_btn"></div>
	<nav class="off">
		<ul> 
			<li><a href="http://download.yuepoker.com/PokerMate.apk">安卓下载</a></li> 
			<!--<li><a href="http://www.sina.com">关于我们</a></li>-->
		</ul>
	</nav>
</div>
  
<script src="http://cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
 
<script>
    //初始化屏幕适配
    var handlerOrientationChange = function(){
        var width = (window.innerWidth <= 320) ? 320 : ((window.innerWidth >= 640) ? 640 : window.innerWidth);
        var fontSize = 100 * (width / 320);
        document.documentElement.style.fontSize = fontSize + "px";
    };
    window.onresize = handlerOrientationChange;
    setTimeout(function(){
        handlerOrientationChange();
    },0)
</script>

<script>
	$('div.menu_btn').bind("click", function(e){
		$('nav').slideDown();
		$('nav').attr('class', 'on');
		event.stopPropagation();
	});
	
	$('div.content').click(function(){
		if( $('nav').attr('class') == 'on' ) {
			$('nav').slideUp();
			$('nav').attr('class', 'off');
		}
	});
	
</script> 
 
</body>
</html>