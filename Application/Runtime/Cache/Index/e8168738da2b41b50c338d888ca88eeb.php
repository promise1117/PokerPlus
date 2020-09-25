<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head profile="http://gmpg.org/xfn/11">
<meta name="renderer" content="webkit">
<meta  charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
<title>loading</title>
  
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
	padding-top: 178%;
	background: url("/Public/Img/bg.png") no-repeat ;
    background-size:cover;
    background-position:center;	
}
  
</style>
  
</head>
<body>
  
<div class="content"></div>
  
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
 
</body>
</html>