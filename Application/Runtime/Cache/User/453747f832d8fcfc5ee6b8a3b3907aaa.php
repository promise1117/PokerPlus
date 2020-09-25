<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head profile="http://gmpg.org/xfn/11">
<meta name="renderer" content="webkit">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
<title>比牌错误报告</title>
<link href="/Public/Css/mypage.css" rel="stylesheet" type="text/css"/> 
 
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
}

h1 {
	font-size: 0.16rem;
	color: #039;
	width: 60%;
	margin: 0.10rem auto 0;
	padding-left: 0.14rem;
}

h1 span.run {
	color: #090;
}

h1 span.error {
	color: #f00;
}

hr {
	margin-top: 0.10rem;
	border: 0.01rem solid #ff4901;
}

div.data {
	font-size: 0.12rem;
	color: #494949;
	width: 60%;
	margin: 0.15rem auto 0;
	padding-left: 0.16rem;
}

div.data p {
	color: #6a3906;
}

div.pages {
	font-size: 0.12rem;
}

span.err_res {
	font-size: 0.12rem;
	color: #f00;
}

span.ok_res {
	font-size: 0.12rem;
	color: #090;
}

</style>
  
</head>
<body>
  
<div class="content">
	<h1>
		<span class="run">已运行回合数：<?php echo ($run_info['cnt_round']); ?>手</span><br />
		<span class="error">错误回合数：<?php echo ($run_info['cnt_error']); ?>手</span><br />
		错误概率：<?php echo ($run_info['chance']); ?>%
	</h1>
	<hr />

    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="data">
    		<p>回合编号：<?php echo ($vo["id"]); ?></p>
	    	玩家1：<?php echo ($vo["p1_1"]); ?>&nbsp;<?php echo ($vo["p1_2"]); ?>&nbsp;<?php echo ($vo["flop"]); ?>&nbsp;<?php echo ($vo["turn"]); ?>&nbsp;<?php echo ($vo["river"]); ?> <br/>
	    	玩家2：<?php echo ($vo["p2_1"]); ?>&nbsp;<?php echo ($vo["p2_2"]); ?>&nbsp;<?php echo ($vo["flop"]); ?>&nbsp;<?php echo ($vo["turn"]); ?>&nbsp;<?php echo ($vo["river"]); ?> <br/>
	    	玩家3：<?php echo ($vo["p3_1"]); ?>&nbsp;<?php echo ($vo["p3_2"]); ?>&nbsp;<?php echo ($vo["flop"]); ?>&nbsp;<?php echo ($vo["turn"]); ?>&nbsp;<?php echo ($vo["river"]); ?> <br/>
	    	玩家4：<?php echo ($vo["p4_1"]); ?>&nbsp;<?php echo ($vo["p4_2"]); ?>&nbsp;<?php echo ($vo["flop"]); ?>&nbsp;<?php echo ($vo["turn"]); ?>&nbsp;<?php echo ($vo["river"]); ?> <br/>
	    	玩家5：<?php echo ($vo["p5_1"]); ?>&nbsp;<?php echo ($vo["p5_2"]); ?>&nbsp;<?php echo ($vo["flop"]); ?>&nbsp;<?php echo ($vo["turn"]); ?>&nbsp;<?php echo ($vo["river"]); ?> <br/>
	    	玩家6：<?php echo ($vo["p6_1"]); ?>&nbsp;<?php echo ($vo["p6_2"]); ?>&nbsp;<?php echo ($vo["flop"]); ?>&nbsp;<?php echo ($vo["turn"]); ?>&nbsp;<?php echo ($vo["river"]); ?> <br/>
	    	玩家7：<?php echo ($vo["p7_1"]); ?>&nbsp;<?php echo ($vo["p7_2"]); ?>&nbsp;<?php echo ($vo["flop"]); ?>&nbsp;<?php echo ($vo["turn"]); ?>&nbsp;<?php echo ($vo["river"]); ?> <br/>
	    	玩家8：<?php echo ($vo["p8_1"]); ?>&nbsp;<?php echo ($vo["p8_2"]); ?>&nbsp;<?php echo ($vo["flop"]); ?>&nbsp;<?php echo ($vo["turn"]); ?>&nbsp;<?php echo ($vo["river"]); ?> <br/>
	    	玩家9：<?php echo ($vo["p9_1"]); ?>&nbsp;<?php echo ($vo["p9_2"]); ?>&nbsp;<?php echo ($vo["flop"]); ?>&nbsp;<?php echo ($vo["turn"]); ?>&nbsp;<?php echo ($vo["river"]); ?> <br/>
    		<span class="err_res">错误结果：玩家<?php echo ($vo["winlist"]); ?>获胜</span><br />
    		<span class="ok_res">正确结果：玩家<?php echo ($vo["winlist_1"]); ?>获胜</span>
    	</div><?php endforeach; endif; else: echo "" ;endif; ?><br />
    
   	<div class="pages"><?php echo ($page); ?></div>
	
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

</body>
</html>