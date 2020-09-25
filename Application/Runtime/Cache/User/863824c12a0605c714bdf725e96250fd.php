<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
	<title>微信安全支付</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="/Public/Css/weui.min.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Css/jquery-weui.css" />
	
	<style>
	body, html {
	  height: 100%;
	  -webkit-tap-highlight-color: transparent;
	}
	
	div.reset {
		height: 1.5rem;
		line-height: 1.5rem;
	}
	
	div.reset a {
		display: none;
		padding: 0 0.5rem 0 1.5rem;
		height: 1.5rem;
		line-height: 1.5rem;
	}
	
	div.weui_cells_tips {
		padding-bottom: 0.6rem;
	}
	
	div.weui_btn_area {
		padding-top: 0.6rem;
	}
	
	.demos-title {
		text-align: center;
		font-size: 0.9rem;
		color: #505F80;
		font-weight: 600;
		margin: 0 15%;
	}

	.demos-header {
		padding-top: 0.8rem;
	}
	
	span.notice {
		color: #FD2615;
	}
	</style>
</head>

<body ontouchstart>

    <header class='demos-header'>
      <h1 class="demos-title">钻石充值</h1>
    </header>

    <div class="weui_cells weui_cells_form">
      <div class="weui_cell">
        <div class="weui_cell_hd"><label class="weui_label">玩家账号</label></div>
        <div class="weui_cell_bd weui_cell_primary">
			<input class="weui_input" type="tel" name="account" placeholder="请输入玩家账号">
        </div>
		<div class="reset">
			<a href="javascript:;" class="weui_icon_clear"></a>
		</div>
      </div>
	  
      <div class="weui_cell">
        <div class="weui_cell_hd"><label class="weui_label">钻石数量</label></div>
        <div class="weui_cell_bd weui_cell_primary">
			<input class="weui_input" type="tel" name="diamond_num" placeholder="请输入钻石数量">
        </div>
		<div class="reset">
			<a href="javascript:;" class="weui_icon_clear"></a>
		</div>
      </div>
	  <div class="weui_cells_tips">
			原价：￥<span class="original">0.00</span>（元）<br />
			9折价格：￥<span class="price">0.00</span>（元）<br />
			已优惠：￥<span class="credits">0.00</span>（元）
	  </div>
	  <div class="weui_cell">
        <div class="weui_cell_hd"><label class="weui_label">充值折扣码</label></div>
        <div class="weui_cell_bd weui_cell_primary">
          <input class="weui_input" type="tel" name="sale_code" value="<?php echo ($sale_code); ?>" placeholder="请输入折扣码">
        </div>
		<div class="reset">
			<a href="javascript:;" class="weui_icon_clear"></a>
		</div>
      </div>
	  <div class="weui_cells_tips">
			商家发放给您的充值折扣码(优惠9折^_^)<br />
			<span class="notice">注意：折扣码填错或不填无法享受折扣！</span>
	  </div>
    </div>
	
    <div class="weui_btn_area">
		<a class="weui_btn weui_btn_plain_primary" href="javascript:;" id="showTooltips">充值</a>
    </div>	
<script type="text/javascript" src="/Public/Js/jquery-2.1.4.js"></script>
<script type="text/javascript" src="/Public/Js/fastclick.js"></script>
<script type="text/javascript" src="/Public/Js/md5.js"></script>
<script>
  $(function() {
    FastClick.attach(document.body);
  });
</script>
<script type="text/javascript" src="/Public/Js/jquery-weui.js"></script>

<script>
$(function(){
	  var account = null;
	  var diamond_num = null;
	  var sale_code = null;
	  var sale_falg = null;
	  
	  if( $.trim($('input[name="sale_code"]').val()) != "" ) {
			sale_code = $.trim($('input[name="sale_code"]').val());
			$('input[name="sale_code"]').attr("disabled", true);
	  }
	  
	  $('input[name="account"]').blur(function() {
			$(this).parent().next().children().css("display", "none");
			account = $.trim($(this).val());
			if( account == "" ) {
				account = null;
				return false;
			}
			
			if( !/^1[345789]\d{9}$/.test(account) && !/^999\d{5}$/.test(account) ) {
				$.toptip('账号格式不正确', 'error');
				account = null;
				return false;
			}
	  });
	  
	  $('input[name="diamond_num"]').blur(function(){
			$(this).parent().next().children().css("display", "none");
			diamond_num = $.trim($(this).val());
			
			if( diamond_num == "" ) {
				diamond_num = null;
				return false;
			}
			
			if( !/^[1-9]{1}\d{0,7}$/.test(diamond_num)) {
				$.toptip('钻石数量格式不正确', 'error');
				diamond_num = null;
				return false;
			}
			
			if( $.trim($(this).val()) != "" ) {
				var old_price = (parseInt($.trim($(this).val()))*1.0/10);
				var new_price = (parseInt($.trim($(this).val()))*1.0/10*0.9);
				$('span.original').text(old_price.toFixed(2));
				$('span.price').text(new_price.toFixed(2));
				$('span.credits').text((old_price-new_price).toFixed(2));
			}else {
				$('span.original').text("0.00");
				$('span.price').text("0.00");
				$('span.credits').text("0.00");			
			}
	  });

	  $('input[name="sale_code"]').blur(function(){
			$(this).parent().next().children().css("display", "none");
			sale_code = $.trim($(this).val());

			if( sale_code == "" ) {
				sale_code = null;
				return false;
			}
			
			if( !/^\d{6}$/.test(sale_code) ) {
				$.toptip('折扣码格式不正确', 'error');
				sale_code = null;
				return false;
			}
	  });
	  
	  $("div.reset a").bind("click", function(e) {
			$(this).parent().prev().children().val("");
			$(this).css("display", "none");

			if( $(this).parent().prev().children().attr("name") == "diamond_num" ) {
				$('span.original').text("0.00");
				$('span.price').text("0.00");
				$('span.credits').text("0.00");				
			}
			
			$('#showTooltips').removeClass('weui_btn_primary').addClass('weui_btn_plain_primary');
			e.preventDefault();
			e.stopPropagation();
			return false;
	  });
	  
	  $('input[class="weui_input"]').keyup(function(e) {
			var str = $.trim($(this).val());
			
			if (str[str.length-1] == '*' || str[str.length-1] == '+' 
				|| str[str.length-1] == '#' || str[str.length-1] == ','
				|| str[str.length-1] == ';' ) {
				$(this).val(str.substring(0, str.length-1));
				return false;
			}
			
			if( $(this).attr("name") == "diamond_num" 
				&& $.trim($(this).val()) != "" ) {
				var old_price = (parseInt($.trim($(this).val()))*1.0/10);
				var new_price = (parseInt($.trim($(this).val()))*1.0/10*0.9);
				$('span.original').text(old_price.toFixed(2));
				$('span.price').text(new_price.toFixed(2));
				$('span.credits').text((old_price-new_price).toFixed(2));
			}
	  });
	  
	  $('input[class="weui_input"][name!="sale_code"]').keydown(function(e) {
			var str = $.trim($(this).val());
			var input_name = $(this).attr("name");
			
			if( input_name == "diamond_num" || input_name == "account" ) {
				if( e.keyCode == 48 && str == "" ) {
					return false;
				}
			}
			
			$(this).parent().next().children().css("display", "block");
			if( input_name == "account" 
				&& /^[1-9]{1}\d{0,7}$/.test($.trim($('input[name="diamond_num"]').val()))
				&& (/^1[345789]\d{9}$/.test($.trim($('input[name="account"]').val())) 
				|| /^999\d{5}$/.test($.trim($('input[name="account"]').val()))) ) {
				$('#showTooltips').removeClass('weui_btn_plain_primary').addClass('weui_btn_primary');
			}
			
			if( input_name == "diamond_num" 
				&& (/^1[345789]\d{9}$/.test($.trim($('input[name="account"]').val()))
				|| /^999\d{5}$/.test($.trim($('input[name="account"]').val())))
				&& $.trim($(this).val()) == "" ) {
				$('#showTooltips').removeClass('weui_btn_plain_primary').addClass('weui_btn_primary');
			}
			
			if( e.keyCode == 8 && str.length == 1 ) {
				$(this).parent().next().children().css("display", "none");
				$('span.original').text("0.00");
				$('span.price').text("0.00");
				$('span.credits').text("0.00");
			}
			
			if( e.keyCode == 8 ) {
				$('#showTooltips').removeClass('weui_btn_primary').addClass('weui_btn_plain_primary');
				return true;
			}			
			
			if( input_name == "account" && str.length >= 11 ) {
				return false;
			}
			if( input_name == "diamond_num" && str.length >= 8 ) {
				return false;
			}
	  });
	  
	  $('input[class="weui_input"][name="sale_code"]').keydown(function(e) {
			var str = $.trim($(this).val());
			$(this).parent().next().children().css("display", "block");
			
			if( e.keyCode == 8 && str.length == 1 ) {
				$(this).parent().next().children().css("display", "none");
			}	
			
	  		if( /^\d{6}$/.test(str) && e.keyCode != 8 ) {
				return false;
			}
	  });
	  
	  
	  $("input.weui_input").focus(function() {
			if( $(this).val() != "" )
				$(this).parent().next().children().css("display", "block");			
				
			if( (/^1[345789]\d{9}$/.test($.trim($('input[name="account"]').val())) 
				|| /^999\d{5}$/.test($.trim($('input[name="account"]').val()))) 
				&&  /^[1-9]{1}\d{0,7}$/.test($.trim($('input[name="diamond_num"]').val())) ) {
				$('#showTooltips').removeClass('weui_btn_plain_primary').addClass('weui_btn_primary');
			}else {
				$('#showTooltips').removeClass('weui_btn_primary').addClass('weui_btn_plain_primary');
			}
	  });

	  $("#showTooltips").click(function() {
		if( account == null || diamond_num == null ) {
			$.toptip('填写信息不完整或格式错误', 'error');
			return false;
		}
		
		if( $('input[name="account"]').val() == "" 
			|| $('input[name="diamond_num"]').val() == "" ) {
			//$.toptip('填写信息不完整或格式错误', 'error');
			return false;
		}
		
		if( (/^1[345789]\d{9}$/.test($.trim($('input[name="account"]').val())) || 
			/^999\d{5}$/.test($.trim($('input[name="account"]').val()))) 
			&&  /^[1-9]{1}\d{0,7}$/.test($.trim($('input[name="diamond_num"]').val())) )	{
			$('#showTooltips').removeClass('weui_btn_plain_primary').addClass('weui_btn_primary');
		}else {
			$('#showTooltips').removeClass('weui_btn_primary').addClass('weui_btn_plain_primary');
		}

		sale_code = (sale_code === null) ? "" : sale_code;
		account = (account === null) ? "" : account;

		var data = {
			"sale_code" : sale_code,
			"mobile" : account,
			"diamond" : parseInt(diamond_num),
			"openid" : "<?php echo ($openid); ?>"	
		};

		$.ajax({
			type:'POST',
			url:'/index.php/User/WxJsAPI/createOrder',
			//async: false,
			dataType:'json',
			data:{
				"data" : JSON.stringify(data),
				"sign" : set_sign(JSON.stringify(data))
			},
			success:function(data) {
				if( parseInt(data['msg_code']) == 0 ) {
					//alert(data['jsApiParameters']);
					//alert(JSON.stringify(data['data']));
					if( parseInt(data['discount']) !== 1 ) {
						callpay($.parseJSON(data['jsApiParameters']));
					}else {
						$.confirm({
							title: '确认支付',
							text: '折扣码错误,本次交易无折扣优惠,是否继续?',
							onOK: function () {
								callpay($.parseJSON(data['jsApiParameters']));
							},
							onCancel: function () {
								return;
							}
						});	
					}
				}else {
					alert(data['msg']);
					return false;
				}
			}
		})
		
		/*
		alert(JSON.stringify(data)); return;
		alert('账号:' + parseInt(account) + "\n" 
			+ '钻石数量:' + parseInt(diamond_num) + "\n"
			+ '折扣码:' + sale_code.toString());
		*/
	  });
	  
	  
	  function salt_sign() {
			var encrypt_str = 'MmLPCVTQepQPRGME%$sadA#$@!ESAD78';
			var day = new Date().getDate();
			var len = encrypt_str.length;
			
			var encrypt_arr = [], encrypt_arr2 = [], encrypt_arr3 = [];
			for(var i=0; i<len; i++) {
				encrypt_arr[i] = encrypt_str.charCodeAt(i);
			}	
			
			for(var i=0; i<len; i++) {
				if( i%2 != 0 ) {
					encrypt_arr2.push(encrypt_arr[i] - i * 2 + day);
				}else{
					encrypt_arr2.push(encrypt_arr[i]);
				}
			}
			
			var t = null;
			for(var i=0; i<len; i++) {
				t = encrypt_arr2[i];
				t = t^(i%3);
				t = t>>3;
				if(i%3==0) {
					t = t << 2;
				}else{
					t = t ^ encrypt_arr2[i];
				}
				encrypt_arr3.push(t);
			}
			return hex_md5(encrypt_arr3.join(''));  
	  }
	  
	  function set_sign(data) {
			var salt_new = salt_sign();
			return hex_md5(salt_new + data + salt_new);
	  }  
	  
	 //调用微信JS api 支付
	  function jsApiCall(request_data) {
			WeixinJSBridge.invoke(
				'getBrandWCPayRequest',
				request_data,
				function(res){
					//WeixinJSBridge.log(res.err_msg);
					//alert(res.err_code+res.err_desc+res.err_msg);  //这里是信息提示。可以加判断做跳转，支付成功后也都会回到这里提示信息。具体你可以参考手册里面的信息。
					
					if(res.err_msg == "get_brand_wcpay_request:ok") {
						return true;
					}else if(res.err_msg == "get_brand_wcpay_request:cancel")  {
						 return false;
					 }else{
						//支付失败
						 alert(res.err_desc);
						 return false;
					 }
					//成功页面跳转
					//window.location.href = '/index.php/User/WxJsAPI/msg';
				}
			);
	  }
	  
	  function callpay(request_data) {
		if (typeof WeixinJSBridge == "undefined"){
			alert('nul');
			if( document.addEventListener ){
				document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			}else if (document.attachEvent){
				document.attachEvent('WeixinJSBridgeReady', jsApiCall);
				document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			}
		}else{
			jsApiCall(request_data);
		}
	  }
	  
});
</script>	
</body>
</html>