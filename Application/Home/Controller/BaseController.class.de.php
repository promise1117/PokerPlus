<?php

//dezend by http://www.yunlu99.com/ QQ:270656184
namespace Home\Controller;

class BaseController extends \Think\Controller
{
	private static $_sms_send_interface = 'http://dx.ipyy.net/smsJson.aspx';
	private static $_sms_verify_interface = 'http://dx.ipyy.net/statusJsonApi.aspx';
	private static $_sms_account = 'AC00224';
	private static $_sms_password = 'AC0022465';
	private static $_sms_prefix = '【扑克圈】';
	private static $_appkey = '585b98501c5dd005a000159e';
	private static $_appMasterSecret = '5u23ubtbtv8htr04gh0pzxp68xftxnhm';
	protected function sendAndroidCustomizedcast($uid, $ticker, $title, $text)
	{
		try {
			import('Vendor.Upush.android.AndroidCustomizedcast');
			$customizedcast = new \AndroidCustomizedcast();
			$customizedcast->setAppMasterSecret(self::$_appMasterSecret);
			$customizedcast->setPredefinedKeyValue('appkey', self::$_appkey);
			$customizedcast->setPredefinedKeyValue('timestamp', time());
			$customizedcast->setPredefinedKeyValue('alias', strval($uid));
			$customizedcast->setPredefinedKeyValue('alias_type', 'SINA_WEIBO');
			$customizedcast->setPredefinedKeyValue('ticker', $ticker);
			$customizedcast->setPredefinedKeyValue('title', $title);
			$customizedcast->setPredefinedKeyValue('text', $text);
			$customizedcast->setPredefinedKeyValue('after_open', 'go_app');
			$customizedcast->send();
		} catch (Exception $e) {
			print 'Caught exception: ' . $e->getMessage();
		}
	}
	protected function sendIOSCustomizedcast($uid, $alert)
	{
		try {
			import('Vendor.Upush.ios.IOSCustomizedcast');
			$customizedcast = new \IOSCustomizedcast();
			$customizedcast->setAppMasterSecret(self::$_appMasterSecret);
			$customizedcast->setPredefinedKeyValue('appkey', self::$_appkey);
			$customizedcast->setPredefinedKeyValue('timestamp', time());
			$customizedcast->setPredefinedKeyValue('alias', strval($uid));
			$customizedcast->setPredefinedKeyValue('alias_type', 'SINA_WEIBO');
			$customizedcast->setPredefinedKeyValue('alert', $alert);
			$customizedcast->setPredefinedKeyValue('badge', 0);
			$customizedcast->setPredefinedKeyValue('sound', 'default');
			$customizedcast->setPredefinedKeyValue('production_mode', 'false');
			$customizedcast->send();
		} catch (Exception $e) {
			print 'Caught exception: ' . $e->getMessage();
		}
	}
	protected function sendSms($mobile, $msg)
	{
		$data = array();
		$data['action'] = 'send';
		$data['account'] = self::$_sms_account;
		$data['password'] = self::$_sms_password;
		$data['mobile'] = $mobile;
		$data['content'] = self::$_sms_prefix . $msg;
		$response = request_post(self::$_sms_send_interface, $data);
		$res = json_decode($response, true);
		if ($res['returnstatus'] == 'Success' && $res['message'] == '操作成功') {
			$data['action'] = 'query';
			$data['taskid'] = $res['taskID'];
			$response = request_post(self::$_sms_verify_interface, $data);
			$res = json_decode($response, true);
		}
		if (intval($res['error']) == 1) {
			return 0;
		} else {
			return 1;
		}
	}
	protected function getRandStrToSn($suffix)
	{
		mt_srand((double) microtime() * 1000000);
		$order_sn = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
		$order_sn .= $suffix;
		return $order_sn;
	}
	protected function getCurrentPage($n_page)
	{
		$page_size = c('LIST_ROWS');
		$current_page = ($n_page - 1) * $page_size;
		$result = array('current_page' => $current_page, 'page_size' => $page_size);
		return $result;
	}
	protected function uploadFile($action)
	{
		$result = array('data' => array());
		$file_type = $_FILES['file_base64']['type'];
		$file_size = $_FILES['file_base64']['size'];
		$file_name = $_FILES['file_base64']['name'];
		$file_ext = $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
		if (!in_array($file_type, array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'))) {
			$result['msg_code'] = '0301';
			exit(returnjson($result));
		}
		if (10000000 < $file_size) {
			$result['msg_code'] = '0303';
			exit(returnjson($result));
		}
		if (0 < $_FILES['file_base64']['error']) {
			\Think\Log::write($_FILES['file_base64']['error']);
			$result['msg_code'] = '0302';
			exit(returnjson($result));
		}
		vendor('QcloudCos/include');
		$bucketName = c('BUCKETNAME');
		$dayFolder = strval(date('Ymd', strtotime('now')));
		if (in_array($action, array('helper_user', 'helper_gallery', 'canteen_image', 'canteen_menu_img'))) {
			$dayFolder = strval(date('Ym', strtotime('now')));
		}
		$width = 640;
		$wide = 320;
		$thumbwidth = 320;
		$thumbwide = 160;
		if ('user' == $action) {
			$width = 640;
			$wide = 420;
			$thumbwidth = 320;
			$thumbwide = 260;
		}
		$result = array();
		$image_dir = '/images/' . $action . '/' . $dayFolder . '/';
		$image_path = getimage_savepath($action);
		@make_dir($image_path);
		$image_name = reformat_image_name('original', $file_name);
		$image_file = $image_path . $image_name;
		if (!move_uploaded_file($_FILES['file_base64']['tmp_name'], $image_file)) {
			return false;
		}
		$avatar = $image_name;
		$image = new \Think\Image();
		$image->open($image_file);
		if ($width < $image->width()) {
			$avatar = reformat_image_name('image', $image_name);
			$image->thumb($width, $wide)->save($image_path . $avatar);
			@unlink($image_file);
			$image_file = $image_path . $avatar;
		}
		$uploadRet = Cosapi::upload($image_file, $bucketName, $image_dir . $avatar);
		if ($uploadRet['code'] != 0) {
			return false;
		}
		$avatar_thumb = reformat_image_name('thumb', $image_name);
		$image->thumb($thumbwidth, $thumbwide)->save($image_path . $avatar_thumb);
		$uploadRet = Cosapi::upload($image_path . $avatar_thumb, $bucketName, $image_dir . $avatar_thumb);
		if ($uploadRet['code'] != 0) {
			return false;
		}
		$result['avatar'] = $image_dir . $avatar;
		$result['avatar_thumb'] = $image_dir . $avatar_thumb;
		@unlink($image_file);
		@unlink($image_path . $avatar_thumb);
		return $result;
	}
	public function search_array($obj, $array_data, $cloumn_name = 'id')
	{
		if (empty($obj)) {
			return false;
		}
		if (empty($array_data)) {
			return false;
		}
		$result = array();
		foreach ($array_data as $key => $value) {
			if ($value[$cloumn_name] == $obj) {
				array_push($result, $array_data[$key]);
			}
		}
		return $result;
	}
	protected function setValueToCache($key, $data)
	{
		$redis = new \Redis();
		$redis->connect(c('REDIS_HOST'), c('REDIS_PORT'));
		$redis->auth(c('REDIS_AUTH'));
		$redis->set($key, json_encode($data));
		$redis->close();
	}
	protected function getValueFromCache($key)
	{
		$redis = new \Redis();
		$redis->connect(c('REDIS_HOST'), c('REDIS_PORT'));
		$redis->auth(c('REDIS_AUTH'));
		$value = $redis->get($key);
		$redis->close();
		return json_decode($value);
	}
	protected function hmsetValueToCache($key, $data)
	{
		if (empty($key)) {
			return false;
		}
		$redis = new \Redis();
		$redis->connect(c('REDIS_HOST'), c('REDIS_PORT'));
		$redis->auth(c('REDIS_AUTH'));
		$redis->hmset($key, $data);
		$redis->close();
	}
	protected function hmsetListToCache($key, $data, $cloumn_name)
	{
		if (empty($key)) {
			return false;
		}
		$redis = new \Redis();
		$redis->connect(c('REDIS_HOST'), c('REDIS_PORT'));
		$redis->auth(c('REDIS_AUTH'));
		foreach ($data as $k => $v) {
			$redis->hmset($key . $v[$cloumn_name], $v);
		}
		$redis->close();
	}
	protected function hgetallValueFromCache($key)
	{
		if (empty($key)) {
			return false;
		}
		$redis = new \Redis();
		$redis->connect(c('REDIS_HOST'), c('REDIS_PORT'));
		$redis->auth(c('REDIS_AUTH'));
		$data = $redis->hgetall($key);
		$redis->close();
		return $data;
	}
	protected function delValueFromCache($key)
	{
		if (empty($key)) {
			return false;
		}
		$redis = new \Redis();
		$redis->connect(c('REDIS_HOST'), c('REDIS_PORT'));
		$redis->auth(c('REDIS_AUTH'));
		$result = $redis->del($key);
		$redis->close();
		return $result;
	}
	protected function checkLogin($token)
	{
		if (empty($token)) {
			return false;
		}
		$user_main = m('user_main');
		$map = array('token' => $token, 'is_online' => 1);
		if ($user_main->where($map)->find()) {
			return true;
		} else {
			return false;
		}
	}
}