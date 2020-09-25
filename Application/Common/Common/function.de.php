<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
function http_get($url, $timeout = 180)
{
	$header[] = 'Accept-Language: zh-cn ';
	$header[] = 'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727) ';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function array_sort($arr, $keys, $type = 'asc')
{
	$keysvalue = $new_array = array();

	foreach ($arr as $k => $v) {
		$keysvalue[$k] = $v[$keys];
	}

	if ($type == 'asc') {
		asort($keysvalue);
	}
	else {
		arsort($keysvalue);
	}

	reset($keysvalue);

	foreach ($keysvalue as $k => $v) {
		$new_array[$k] = $arr[$k];
	}

	return $new_array;
}

function getIPaddress()
{
	$IPaddress = '';

	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$IPaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$IPaddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		else {
			$IPaddress = $_SERVER['REMOTE_ADDR'];
		}
	}
	else if (getenv('HTTP_X_FORWARDED_FOR')) {
		$IPaddress = getenv('HTTP_X_FORWARDED_FOR');
	}
	else if (getenv('HTTP_CLIENT_IP')) {
		$IPaddress = getenv('HTTP_CLIENT_IP');
	}
	else {
		$IPaddress = getenv('REMOTE_ADDR');
	}

	return $IPaddress;
}

function request_post($url = '', $param = '', $header = '')
{
	if (empty($url) || empty($param)) {
		return false;
	}

	$curlPost = http_build_query($param);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);

	if (!empty($header)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	}

	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
 
function sendJpushMsg($_appkeys, $_masterSecret, $sendno = 0, $receiver_type = 1, $receiver_value = '', $msg_type = 1, $msg_content = '', $platform = 'android,ios')
{
	$url = 'http://api.jpush.cn:8800/sendmsg/v2/sendmsg';
	$param = '';
	$param .= '&sendno=' . $sendno;
	$param .= '&app_key=' . $_appkeys;
	$param .= '&receiver_type=' . $receiver_type;
	$param .= '&receiver_value=' . $receiver_value;
	$verification_code = md5($sendno . $receiver_type . $receiver_value . $_masterSecret);
	$param .= '&verification_code=' . $verification_code;
	$param .= '&msg_type=' . $msg_type;
	$param .= '&msg_content=' . $msg_content;
	$param .= '&platform=' . $platform;
	$res = request_post($url, $param);

	if ($res === false) {
		return false;
	}

	$res_arr = json_decode($res, true);
	return $res_arr;
}

function ob_gzip($content)
{
	return $content;
}

function returnJson($param)
{
	if (!isset($param['msg_code'])) {
		$param['msg_code'] = '0';
	}

	if (!isset($param['data'])) {
		$param['data'] = array();
	}

	$param['msg'] = $param['msg'] ? $param['msg'] : c('code_' . $param['msg_code']);
	$param['server_time'] = $param['static_time'] == 1 ? 1413475200 + rand(10000, 76390) : time();
	unset($param['static_time']);
	return ob_gzip(str_replace('null', '""', my_json_encode($param)));
}

function nulltoempty($arr)
{
	foreach ($arr as $key => &$value) {
		$value = (is_null($value) ? '' : $value);
	}

	return $arr;
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

function getPost($param)
{
	return i('post.' . $param, '', 'trim');
}

function checkEmailMobile($user_input)
{
	$mobile_preg = '/^(1(([357][0-9])|(47)|[8][012356789]))\\d{8}$/';
	$email_preg = '/^([a-zA-Z0-9]+[_|\\_|\\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\\_|\\.]?)*[a-zA-Z0-9]+\\.[a-zA-Z]{2,3}$/';

	if (preg_match($mobile_preg, $user_input)) {
		$flag = 1;
	}
	else if (preg_match($email_preg, $user_input)) {
		$flag = 2;
	}
	else {
		$flag = 0;
	}

	return $flag;
}

function checkWechatId($user_input)
{
	$wechat_preg = '/^[a-zA-z][a-z|A-z|0-9|_|-]{5,19}$/';
	return preg_match($wechat_preg, $user_input);
}

function randStrCode($len = 6, $format = 'ALL')
{
	switch ($format) {
	case 'ALL':
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		break;

	case 'CHAR':
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		break;

	case 'NUMBER':
		$chars = '0123456789';
		break;

	default:
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		break;
	}

	mt_srand((double) microtime() * 1000000 * getmypid());
	$password = '';

	while (strlen($password) < $len) {
		$password .= substr($chars, mt_rand() % strlen($chars), 1);
	}

	return $password;
}

function get_salt_key()
{
	$salt_key = 'MmLPCVTQepQPRGME%$sadA#$@!ESAD78';
	return $salt_key;
}

function salt_sign()
{
	$encrypt_str = get_salt_key();
	$day = date('j');
	$len = strlen($encrypt_str);

	for ($i = 0; $i < $len; $i++) {
		$encrypt_arr[] = ord($encrypt_str[$i]);
	}

	$encrypt_arr2 = array();

	for ($i = 0; $i < $len; $i++) {
		if (($i % 2) != 0) {
			$encrypt_arr2[] = ($encrypt_arr[$i] - ($i * 2)) + $day;
		}
		else {
			$encrypt_arr2[] = $encrypt_arr[$i];
		}
	}

	$t = NULL;

	for ($i = 0; $i < $len; $i++) {
		$t = $encrypt_arr2[$i];
		$t = $t ^ ($i % 3);
		$t = $t >> 3;

		if (($i % 3) == 0) {
			$t = $t << 2;
		}
		else {
			$t = $t ^ $encrypt_arr2[$i];
		}

		$encrypt_arr3[] = $t;
	}

	$enkey = md5(implode('', $encrypt_arr3));
	return $enkey;
}

function checkSign(&$data, &$client_sign)
{
	$salt = c('salt_code');
	$salt_new = salt_sign();
	$server_sign = md5($salt . $data . $salt);
	$server_sign_new = md5($salt_new . $data . $salt_new);
	if (empty($client_sign) || empty($server_sign) || empty($server_sign_new)) {
		$result['msg_code'] = '100011';
		exit(returnjson($result));
	}

	if (($client_sign != $server_sign) && ($client_sign != $server_sign_new)) {
		$result['msg_code'] = '100011';
		exit(returnjson($result));
	}

	return json_decode($data, true);
}

function unpackAndDecrypt(&$data, &$aes_key)
{
	return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $aes_key, gzuncompress($data), MCRYPT_MODE_ECB);
}

function msubstr($str, $start = 0, $length, $charset = 'utf-8', $suffix = true)
{
	$charset = strtolower($charset);

	if (function_exists('mb_substr')) {
		$slice = mb_substr($str, $start, $length, $charset);
	}
	else if (function_exists('iconv_substr')) {
		$slice = iconv_substr($str, $start, $length, $charset);

		if (false === $slice) {
			$slice = '';
		}
	}
	else {
		$re['utf-8'] = '/[\\x01-\\x7f]|[\\xc2-\\xdf][\\x80-\\xbf]|[\\xe0-\\xef][\\x80-\\xbf]{2}|[\\xf0-\\xff][\\x80-\\xbf]{3}/';
		$re['gb2312'] = '/[\\x01-\\x7f]|[\\xb0-\\xf7][\\xa0-\\xfe]/';
		$re['gbk'] = '/[\\x01-\\x7f]|[\\x81-\\xfe][\\x40-\\xfe]/';
		$re['big5'] = '/[\\x01-\\x7f]|[\\x81-\\xfe]([\\x40-\\x7e]|\\xa1-\\xfe])/';
		preg_match_all($re[$charset], $str, $match);
		$slice = join('', array_slice($match[0], $start, $length));
	}

	return $suffix ? $slice . '...' : $slice;
}

function sock_post($url, $query)
{
	$data = '';
	$info = parse_url($url);
	$fp = fsockopen($info['host'], 80, $errno, $errstr, 30);

	if (!$fp) {
		return $data;
	}

	$head = 'POST ' . $info['path'] . " HTTP/1.0\r\n";
	$head .= 'Host: ' . $info['host'] . "\r\n";
	$head .= 'Referer: http://' . $info['host'] . $info['path'] . "\r\n";
	$head .= "Content-type: application/x-www-form-urlencoded\r\n";
	$head .= 'Content-Length: ' . strlen(trim($query)) . "\r\n";
	$head .= "\r\n";
	$head .= trim($query);
	$write = fputs($fp, $head);
	$header = '';

	while ($str = trim(fgets($fp, 4096))) {
		$header .= $str;
	}

	while (!feof($fp)) {
		$data .= fgets($fp, 4096);
	}

	return $data;
}

function send_sms_new($text, $mobile)
{
	$apikey = '30df7b2f08eab810dd99486fca9234ea';
	$url = 'http://yunpian.com/v1/sms/send.json';
	$encoded_text = urlencode($text);
	$post_string = 'apikey=' . $apikey . '&text=' . $encoded_text . '&mobile=' . $mobile;
	return sock_post($url, $post_string);
}

function WxShareSdk()
{
	$jssdk = new \Org\Util\JSSDK('wx138697ef383a9167', '1450a218c8b14fa36b27a0872327de54');
	$signPackage = $jssdk->GetSignPackage();
	return $signPackage;
}

function upload($file)
{
	$path = 'images/comment/' . date('ymd') . '/';
	$url = 'http://engapp.huanhuba.com:81/admin/get_stream.php';
	$exp = '.jpg';
	$random_name = sha1(time() . mt_rand(111111, 999999)) . $exp;

	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		$tmp = './tmp/' . $random_name;
		file_put_contents($tmp, $file);
		$post_data['u_file'] = '@' . realpath('./tmp/' . $random_name);
	}
	else {
		$tmp = '/tmp/' . $random_name;
		file_put_contents($tmp, $file);
		$post_data['u_file'] = '@' . realpath('/tmp/' . $random_name);
	}

	$post_data['path'] = $path;
	$post_data['rand'] = rand();
	$post_data['filename'] = $random_name;
	$sign = sha1(http_build_query($post_data));
	$post_data['sign'] = $sign;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible;)');
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$response = curl_exec($ch);
	curl_close($ch);

	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		@unlink(realpath('./tmp/' . $random_name));
	}
	else {
		@unlink(realpath('/tmp/' . $random_name));
	}

	if ($response) {
		return 'http://sapp.huanhuba.com/' . $path . $response;
	}
	else {
		return NULL;
	}
}

function time2Units($date)
{
	$now = time();
	$time = $now - $date;
	$year = floor($time / 60 / 60 / 24 / 365);
	$time -= $year * 60 * 60 * 24 * 365;
	$month = floor($time / 60 / 60 / 24 / 30);
	$time -= $month * 60 * 60 * 24 * 30;
	$week = floor($time / 60 / 60 / 24 / 7);
	$time -= $week * 60 * 60 * 24 * 7;
	$day = floor($time / 60 / 60 / 24);
	$time -= $day * 60 * 60 * 24;
	$hour = floor($time / 60 / 60);
	$time -= $hour * 60 * 60;
	$minute = floor($time / 60);
	$time -= $minute * 60;
	$second = $time;
	$elapse = '';
	$unitArr = array('年' => 'year', '个月' => 'month', '周' => 'week', '天' => 'day', '小时' => 'hour', '分钟' => 'minute', '秒' => 'second');

	foreach ($unitArr as $cn => $u) {
		if (0 < $$u) {
			$elapse = $$u . $cn;
			break;
		}
	}

	return $elapse . '前';
}

function createNonceStr($length = 16)
{
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$str = '';

	for ($i = 0; $i < $length; $i++) {
		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}

	return $str;
}

function array_merge_recursive_new()
{
	$arrays = func_get_args();
	$base = array_shift($arrays);

	foreach ($arrays as $array) {
		reset($base);

		while (list($key, $value) = @each($array)) {
			if (is_array($value) && @is_array($base[$key])) {
				$base[$key] = array_merge_recursive_new($base[$key], $value);
			}
			else {
				$base[$key] = $value;
			}
		}
	}

	return $base;
}

function getSignByDidi($params, $sign_key)
{
	$params['sign_key'] = $sign_key;
	ksort($params);
	$str = '';

	foreach ($params as $k => $v) {
		if ('' == $str) {
			$str .= $k . '=' . trim($v);
		}
		else {
			$str .= '&' . $k . '=' . trim($v);
		}
	}

	$sign = md5($str);
	return $sign;
}

function getPaginationTotal($count, $page_size)
{
	return !empty($count) && (0 < $count) ? ceil($count / $page_size) : 1;
}

function getProjectReplaceStr()
{
	$sub_project = '';
	$slash = DIRECTORY_SEPARATOR;

	if (in_array($slash, array('/', '\\'))) {
		$paths = explode($slash, THINK_PATH);
		$sub_project = $paths[count($paths) - 2];
		$sub_project = trim($sub_project, '/');
		$sub_project = trim($sub_project, '\\');
	}

	return $sub_project;
}

function full_static_url($source_path)
{
	$sub_project = getprojectreplacestr();
	$path = site_url(format_static_path($source_path));

	if (!empty($sub_project)) {
		$path = str_replace($sub_project . '/', '', $path);
	}

	return $path;
}

function getImage_SavePath($image_subDir, $has_date_folder = false)
{
	$sub_project = getprojectreplacestr();
	$path = THINK_PATH;

	if (!empty($sub_project)) {
		$path = str_replace(array('ThinkPHP/'), '', $path);
	}

	$path = rtrim($path, '/');
	$path .= format_static_path('/images/' . $image_subDir . '/');

	if ($has_date_folder) {
		$path .= sprintf('%s/', date('Y-m'));
	}

	return $path;
}

function full_static_path($source_path)
{
	$sub_project = getprojectreplacestr();
	$path = THINK_PATH;

	if (!empty($sub_project)) {
		$path = str_replace(array($sub_project . '/', $sub_project . '\\'), '', $path);
	}

	$path = rtrim($path, '/');
	return $path . format_static_path($source_path);
}

function format_static_path($source_path)
{
	$static_dir = 'assets';
	$static_dir = '/' . ltrim($static_dir, '/');
	$source_path = '/' . ltrim($source_path, '/');
	return $static_dir . $source_path;
}

function reformat_image_name($image_type, $source_img)
{
	$file_ext = pathinfo($source_img, PATHINFO_EXTENSION);
	$image_type = strtolower($image_type);

	if ($image_type == 'original') {
		$file_name = substr(md5(microtime()), 4, 16);
	}
	else {
		$type_arr = array('original' => 'o', 'thumb' => 't', 'image' => 'i');
		$prefix = (isset($type_arr[$image_type]) ? $type_arr[$image_type] : 'o');
		$file_name = $source_img;
		$file_name = rtrim($file_name, '.' . $file_ext);
		$file_name .= '@' . $prefix;
	}

	$file_name .= '.' . $file_ext;
	$file_name = strtoupper($file_name);
	return $file_name;
}

function make_dir($folder)
{
	$reval = false;

	if (!file_exists($folder)) {
		@umask(0);
		preg_match_all('/([^\\/]*)\\/?/i', $folder, $atmp);
		$base = ($atmp[0][0] == '/' ? '/' : '');

		foreach ($atmp[1] as $val) {
			if ('' != $val) {
				$base .= $val;
				if (('..' == $val) || ('.' == $val)) {
					$base .= '/';
					continue;
				}
			}
			else {
				continue;
			}

			$base .= '/';

			if (!file_exists($base)) {
				if (@mkdir(rtrim($base, '/'), 511)) {
					@chmod($base, 511);
					$reval = true;
				}
			}
		}
	}
	else {
		$reval = is_dir($folder);
	}

	clearstatcache();
	return $reval;
}

function move_upload_file($file_name, $target_name = '')
{
	if (function_exists('move_uploaded_file')) {
		if (move_uploaded_file($file_name, $target_name)) {
			@chmod($target_name, 493);
			return true;
		}
		else if (copy($file_name, $target_name)) {
			@chmod($target_name, 493);
			return true;
		}
	}
	else if (copy($file_name, $target_name)) {
		@chmod($target_name, 493);
		return true;
	}

	return false;
}

function base64_urlSafeEncode($data)
{
	$find = array('+', '/');
	$replace = array('-', '_');
	return str_replace($find, $replace, base64_encode($data));
}

function base64_urlSafeDecode($str)
{
	$find = array('-', '_');
	$replace = array('+', '/');
	return base64_decode(str_replace($find, $replace, $str));
}

function getRandStrToSn($suffix)
{
	list($usec, $sec) = explode(' ', microtime());
	$order_sn = date('YmdHis', $sec) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) . str_pad($usec * 1000000, 6, '0', STR_PAD_LEFT);
	$order_sn .= $suffix;
	return $order_sn;
}

function getpage($count, $pagesize = 10)
{
	$p = new \Think\Page($count, $pagesize);
	$p->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录 第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
	$p->setConfig('prev', '上一页');
	$p->setConfig('next', '下一页');
	$p->setConfig('last', '末页');
	$p->setConfig('first', '首页');
	$p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
	$p->lastSuffix = false;
	return $p;
}

if (!function_exists('array_column')) {
	function array_column(array $input, $columnKey, $indexKey = NULL)
	{
		$array = array();

		foreach ($input as $value) {
			if (!isset($value[$columnKey])) {
				trigger_error('Key "' . $columnKey . '" does not exist in array');
				return false;
			}

			if (is_null($indexKey)) {
				$array[] = $value[$columnKey];
			}
			else {
				if (!isset($value[$indexKey])) {
					trigger_error('Key "' . $indexKey . '" does not exist in array');
					return false;
				}

				if (!is_scalar($value[$indexKey])) {
					trigger_error('Key "' . $indexKey . '" does not contain scalar value');
					return false;
				}

				$array[$value[$indexKey]] = $value[$columnKey];
			}
		}

		return $array;
	}
}

 
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

function send_diamond_report($data, $orderid, $channel, $amount)
{
	//$uid,$mobile,$channel, $changeAmount,$param1,$param2,$param3,$desc
	$uid = $data["user_id"];
	$mobile = $data["mobile"];
	$param1 = $data["diamond_old"];
	$param2 = $data["diamond_new"];
	$param3 = $data["pay_type"];
 	$desc = "orderid:".$data["order_sn"]."|paytime:".$data["add_time"]."|".$remark;

	send_currency_report_real($uid, $orderid, $mobile, $channel, 1, $amount, $param1, $param2, $param3, $desc);
 } 

function send_currency_report_real($uid,$orderid, $mobile,$channel, $moneyType, $changeAmount,$param1,$param2,$param3,$desc)
{
	$host_url = c('EVENT_REPORT_URL');
	
 	$url=$host_url."currency";
 	
	$arr = array(
		'Id' => 0,
		'ChangeTime' => time(),
		'Uid' => $uid,
		'ClubId' => 0,
		'RoomId' => 0,
		'OrderId' => $orderid,
		'RoleName' => $mobile,
		'ChangeChannel' => $channel,
		'CurrencyType' => $moneyType,
		'ChangeAmount' => $changeAmount,
		'Param1' => $param1,
		'Param2' => $param2,
		'Param3' => $param3,
		'Desc' => $desc
	);
	$s = my_json_encode($arr);
	send_report_post($url, $s);
} 

function send_event_report_real($uid, $mobile,$event, $param1,$param2,$param3,$desc,$desc2)
{
	$host_url = c('EVENT_REPORT_URL');
	
 	$url=$host_url."event";
 	
	$arr = array(
		'Id' => 0,
		'EventTime' => time(),
		'Uid' => $uid,
		'RoomId' => 0,
		'ClubId' => 0,
		'RoleName' => $mobile,
		'EventType' => $event,
		'Param1' => $param1,
		'Param2' => $param2,
		'Param3' => $param3,
		'Desc' => $desc,
		'Desc2' => $desc2
	);
	$s = my_json_encode($arr);
	send_report_post($url, $s);
} 

?>
