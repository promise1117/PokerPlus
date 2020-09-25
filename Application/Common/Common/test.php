<?php
 
function send_report_post($url = '', $param = '', $header = '')
{
	if (empty($url) || empty($param)) {
		return false;
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

	if (!empty($header)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	}

	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function my_json_encode($arr)
{
	array_walk_recursive($arr, function(&$item, $key) {
		if (is_string($item)) {
			$item = mb_encode_numericentity($item, array(128, 65535, 0, 65535), 'UTF-8');
		}
	});
	return mb_decode_numericentity(json_encode($arr), array(128, 65535, 0, 65535), 'UTF-8');
}



function send_currency_report($url,$uid,$mobile,$channel, $changeAmount,$param1,$param2,$param3,$desc)
{
	$arr = array(
		'Id' => 0,
		'ChangeTime' => time(),
		'Uid' => $uid,
		'ClubId' => 0,
		'RoomId' => 0,
		'RoleName' => $mobile,
		'ChangeChannel' => $channel,
		'CurrencyType' => 1,
		'ChangeAmount' => $changeAmount,
		'Param1' => $param1,
		'Param2' => $param2,
		'Param3' => $param3,
		'Desc' => $desc
	);
	$s = my_json_encode($arr);
	send_report_post($url, $s);
} 
$url="http://139.196.215.75:4243/currency";
$order = "this is orderid";
$old_diamond = 10;
$new_diamond = 50;
$change = 40;
$uid = 1234;
$mobile = "190000";
$paytype="paytype";
$channel=12;
$remark ="remark";
$desc = "orderid:".$order."|paytype:".$paytype."|".$remark;
send_currency_report($url,$uid,$mobile,$channel,$change,$old_diamond,$new_diamond,0,$desc);
?>
