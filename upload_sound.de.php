<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
require_once __DIR__ . '/../qiniu_sdk/autoload.php';
$user_id = intval($_POST['user_id']);
$file_type = intval($_POST['file_type']);
$file_ext_name = trim(strval($_POST['file_ext_name']));
$file_create_time = trim(strval($_POST['file_create_time']));
if (empty($user_id) || empty($file_type) || ($user_id < 0) || ($file_type < 0) || empty($file_ext_name)) {
	exit();
}

if ($file_type == 1) {
	$img_type = 'avatar';
	$file_name = strval($user_id) . '_a_b.' . $file_ext_name;
}
else if ($file_type == 2) {
	$img_type = 'avatar_thumb';
	$file_name = strval($user_id) . '_a_s.' . $file_ext_name;
}
else if ($file_type == 3) {
	$img_type = 'sound';
	list($usec, $sec) = explode(' ', microtime());
	$file_name = strval($user_id) . '_s_s_' . $file_create_time . '.' . $file_ext_name;
}
else {
	exit();
}

$accessKey = 'q35Kt71IU2xmmO9mIpKw1t1koA3XQeExDa0GNluP';
$secretKey = 'hk4MhVKGAp2tKKeugTZ6ot-MglB756nDEFUYjjEm';
$bucket = 'images';
$auth = new \Qiniu\Auth($accessKey, $secretKey);
$bucketMgr = new \Qiniu\Storage\BucketManager($auth);
$uploadMgr = new \Qiniu\Storage\UploadManager();
$token = $auth->uploadToken($bucket);
$filePath = './Uploads/' . $img_type . '/' . $file_name;
$key = $file_name;
list($ret, $err) = $bucketMgr->stat($bucket, $key);

if (!empty($ret)) {
	$bucketMgr->delete($bucket, $key);
}

if (!file_exists($filePath)) {
	exit();
}

list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
echo json_encode($ret);
exit();

?>
