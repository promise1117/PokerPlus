<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
require_once __DIR__ . '/../qiniu_sdk/autoload.php';
$club_id = intval($_POST['club_id']);
$file_type = intval($_POST['file_type']);
$file_ext_name = trim(strval($_POST['file_ext_name']));
if (empty($club_id) || empty($file_type) || ($club_id < 0) || ($file_type < 0) || empty($file_ext_name)) {
	exit();
}

if ($file_type == 1) {
	$img_type = 'club_img';
	$file_name = strval($club_id) . '_c_b.' . $file_ext_name;
}
else if ($file_type == 2) {
	$img_type = 'club_thumb';
	$file_name = strval($club_id) . '_c_s.' . $file_ext_name;
}
else if ($file_type == 3) {
	$img_type = 'circle_img';
	$file_name = strval($club_id) . '_c_b.' . $file_ext_name;
}
else if ($file_type == 4) {
	$img_type = 'circle_thumb';
	$file_name = strval($club_id) . '_c_s.' . $file_ext_name;
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
