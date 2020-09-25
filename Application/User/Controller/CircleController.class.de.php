<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class CircleController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function getRegions()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$res = m('area')->select();

		if (!empty($res)) {
			$result['data'] = $res;
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
	}

	public function circleApply()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$circle_name = trim(strval($data['circle_name']));
		$area_id = intval($data['area_id']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if (20 < ((strlen($circle_name) + mb_strlen($circle_name, 'utf-8')) / 2)) {
			$result['msg_code'] = '100094';
			exit(returnjson($result));
		}

		if ($area_id <= 0) {
			$result['msg_code'] = '100095';
			exit(returnjson($result));
		}

		$model_user_main = m('user_main');
		$user_info = $model_user_main->where(array('id' => $user_id))->field('id')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('id' => $area_id);
		$area_name = m('area')->where($map)->getfield('area_name');
		$qiniu = c('QINIU');
		$apply_time = time();
		$data = array('circle_name' => $circle_name, 'circle_img' => $qiniu['default_circle_img'], 'circle_thumb' => $qiniu['default_circle_thumb'], 'members_max' => 200, 'admins_max' => 7, 'current_members' => 1, 'current_admins' => 0, 'creater_id' => $user_id, 'area_id' => $area_id, 'add_time' => $apply_time, 'chk_status' => 0);
		m()->startTrans();
		$circle_id = m('circle')->add($data);
		$data = array('circle_id' => $circle_id, 'member_id' => $user_id, 'add_time' => time());
		$res1 = m('circle_member')->add($data);
		if (!empty($res1) && !empty($circle_id)) {
			m()->commit();
			$ret_data = array('circle_id' => $circle_id, 'circle_name' => $circle_name, 'circle_introduce' => '', 'circle_img' => 'http://' . $qiniu['domain'] . '/' . $qiniu['default_circle_img'], 'circle_thumb' => 'http://' . $qiniu['domain'] . '/' . $qiniu['default_circle_thumb'], 'members_max' => 200, 'admins_max' => 7, 'current_members' => 1, 'current_admins' => 0, 'creater_id' => $user_id, 'area_id' => $area_id, 'area_name' => $area_name, 'add_time' => $apply_time);
			$result['data'] = $ret_data;
			$result['msg'] = '申请扑克圈成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$ret_data = array('circle_name' => $circle_name, 'creater_id' => $user_id, 'area_id' => $area_id, 'add_time' => $apply_time);
			$result['data'] = $ret_data;
			$result['msg_code'] = '100148';
			exit(returnjson($result));
		}
	}

	public function modifyCircleInfo()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$circle_id = intval($data['circle_id']);
		$circle_name = trim(strval($data['circle_name']));
		$circle_introduce = trim(strval($data['circle_introduce']));
		$img_ext = strtolower($data['img_ext']);
		$circle_img = trim(strval($data['circle_img']));
		$circle_thumb = trim(strval($data['circle_thumb']));

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($circle_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ((10 < mb_strlen($circle_name, 'utf-8')) && (30 < strlen($circle_name))) {
			$result['msg_code'] = '100094';
			exit(returnjson($result));
		}

		if (512 < mb_strlen($circle_introduce, 'utf-8')) {
			$result['msg_code'] = '100097';
			exit(returnjson($result));
		}

		if (($img_ext != 'jpg') && ($img_ext != 'gif') && ($img_ext != 'png') && ($img_ext != 'jpeg') && ($img_ext != '')) {
			$result['msg_code'] = '100091';
			exit(returnjson($result));
		}

		$res = m('user_main')->where(array('id' => $user_id))->field('id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$model_circle = m('circle');
		$map = array('id' => $circle_id, 'chk_status' => 1);
		$res = $model_circle->where($map)->field('id,creater_id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		if (intval($res['creater_id']) !== $user_id) {
			$result['msg_code'] = '100129';
			exit(returnjson($result));
		}

		if (!empty($circle_img) && !empty($img_ext)) {
			$file_data = base64_urlsafedecode($circle_img);
			$file_name = './Uploads/circle_img/' . $circle_id . '_c_b.' . $img_ext;
			file_put_contents($file_name, $file_data);
			$param = array('club_id' => $circle_id, 'file_type' => 3, 'file_ext_name' => $img_ext);
			$response = request_post('http://' . c('SERVER_DOMAIN') . '/upload_club.php', $param);
			$ret = json_decode($response, true);

			if (empty($ret['key'])) {
				$result['msg_code'] = '100092';
				exit(returnjson($result));
			}

			$circle_img_name = $ret['key'];
		}

		if (!empty($circle_thumb) && !empty($img_ext)) {
			$file_data = base64_urlsafedecode($circle_thumb);
			$file_name = './Uploads/circle_thumb/' . $circle_id . '_c_s.' . $img_ext;
			file_put_contents($file_name, $file_data);
			$param = array('club_id' => $circle_id, 'file_type' => 4, 'file_ext_name' => $img_ext);
			$response = request_post('http://' . c('SERVER_DOMAIN') . '/upload_club.php', $param);
			$ret = json_decode($response, true);

			if (empty($ret['key'])) {
				$result['msg_code'] = '100092';
				exit(returnjson($result));
			}

			$circle_thumb_name = $ret['key'];
		}

		$data = array();

		if (!empty($circle_name)) {
			$data['circle_name'] = $circle_name;
		}

		if (!empty($circle_introduce)) {
			$data['circle_introduce'] = $circle_introduce;
		}

		if (!empty($circle_img) && !empty($circle_thumb)) {
			$data['circle_img'] = $circle_img_name;
			$data['circle_thumb'] = $circle_thumb_name;
		}

		$model_circle->where($map)->save($data);
		$qiniu = c('QINIU');
		$data['circle_id'] = $circle_id;
		$data['creater_id'] = $user_id;
		$data['circle_img'] = 'http://' . $qiniu['domain'] . '/' . $circle_img_name;
		$data['circle_thumb'] = 'http://' . $qiniu['domain'] . '/' . $circle_thumb_name;
		$result['data'] = $data;
		$result['msg'] = '扑克圈信息修改成功！';
		$result['msg_code'] = '0';
		exit(returnjson($result));
	}

	public function getCircleDetail()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$circle_id = intval($data['circle_id']);

		if ($circle_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		$map = array('id' => $circle_id, 'chk_status' => 1);
		$res_circle = m('circle')->where($map)->find();

		if (empty($res_circle)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$res_member = m('circle_member')->field('member_id')->where(array('circle_id' => $circle_id))->select();
		$uids = array_column($res_member, 'member_id');
		$map = array(
			'id' => array('in', $uids)
			);
		$model_user_main = m('user_main');
		$member_info = $model_user_main->field("id as user_id,\r\n\t\t\tnick_name,avatar,avatar_thumb")->where($map)->select();
		$qiniu = c('QINIU');

		foreach ($member_info as $k => $v) {
			$member_info[$k]['avatar'] = 'http://' . $qiniu['domain'] . '/' . $v['avatar'];
			$member_info[$k]['avatar_thumb'] = 'http://' . $qiniu['domain'] . '/' . $v['avatar_thumb'];
		}

		$area_name = m('area')->where(array('id' => intval($res_circle['area_id'])))->getfield('area_name');
		$creater_info = $model_user_main->field('id,nick_name,avatar,avatar_thumb')->where(array('id' => intval($res_circle['creater_id'])))->find();
		$data = array('circle_id' => intval($circle_id), 'circle_name' => $res_circle['circle_name'], 'circle_introduce' => $res_circle['circle_introduce'], 'circle_img' => 'http://' . $qiniu['domain'] . '/' . $res_circle['circle_img'], 'circle_thumb' => 'http://' . $qiniu['domain'] . '/' . $res_circle['circle_thumb'], 'member_info' => $member_info, 'members_max' => intval($res_circle['members_max']), 'current_members' => intval($res_circle['current_members']), 'admins_max' => intval($res_circle['admins_max']), 'current_admins' => intval($res_circle['current_admins']), 'creater_id' => intval($creater_info['id']), 'creater_nick_name' => $creater_info['nick_name'], 'creater_avatar' => 'http://' . $qiniu['domain'] . '/' . $creater_info['avatar'], 'creater_avatar_thumb' => 'http://' . $qiniu['domain'] . '/' . $creater_info['avatar_thumb'], 'area_id' => intval($res_circle['area_id']), 'area_name' => $area_name, 'add_time' => intval($res_circle['add_time']));
		$result['data'] = $data;
		exit(returnjson($result));
	}

	public function addMember()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$circle_id = intval($data['circle_id']);
		$member_id = intval($data['member_id']);

		if ($circle_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ($member_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		$model_circle = m('circle');
		$model_circle_member = m('circle_member');
		$model_circle_apply = m('circle_apply');
		$map = array('user_id' => $member_id, 'club_id' => $circle_id);
		$res = $model_circle_apply->where($map)->find();

		if (empty($res)) {
			$result['msg_code'] = '100139';
			exit(returnjson($result));
		}

		$circle_info = $model_circle->where(array('id' => $circle_id))->field('id,members_max,current_members')->find();

		if (empty($circle_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$user_info = m('user_main')->where(array('id' => $member_id))->field('id')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$res = $model_circle_member->where(array('member_id' => $member_id))->field('member_id')->find();

		if (!empty($res)) {
			$result['msg_code'] = '100099';
			exit(returnjson($result));
		}

		if (intval($circle_info['members_max']) <= intval($circle_info['current_members'])) {
			$result['msg_code'] = '100108';
			exit(returnjson($result));
		}

		$data = array('circle_id' => $circle_id, 'member_id' => $member_id, 'add_time' => time());
		m()->startTrans();
		$res1 = $model_circle_member->add($data);
		$res2 = $model_circle->where(array('id' => $circle_id))->setInc('current_members');
		$data = array('chk_status' => 1, 'chk_time' => time());
		$res3 = $model_circle_apply->where($map)->save($data);
		if (!empty($res1) && !empty($res2) && !empty($res3)) {
			m()->commit();
			$result['data'] = $data;
			$result['msg_code'] = '0';
			$result['msg'] = '添加扑克圈成员成功！';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['data'] = $data;
			$result['msg_code'] = '100103';
			exit(returnjson($result));
		}
	}

	public function delMember()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$circle_id = intval($data['circle_id']);
		$member_id = intval($data['member_id']);

		if ($circle_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ($member_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		$model_circle = m('circle');
		$model_circle_member = m('circle_member');
		$res = $model_circle->where(array('id' => $circle_id))->field('id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$res = m('user_main')->where(array('id' => $member_id))->field('id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('circle_id' => $circle_id, 'member_id' => $member_id);
		$res = $model_circle_member->where($map)->find();

		if (empty($res)) {
			$result['msg_code'] = '100106';
			exit(returnjson($result));
		}

		m()->startTrans();
		$res1 = $model_circle_member->where($map)->delete();
		$res2 = $model_circle->where(array('id' => $circle_id))->setDec('current_members');
		$ret_data = array('circle_id' => $circle_id, 'member_id' => $member_id);
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$result['data'] = $ret_data;
			$result['msg'] = '删除扑克圈成员成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['data'] = $ret_data;
			$result['msg_code'] = '100102';
			exit(returnjson($result));
		}
	}

	public function getMyCircleList()
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

		$res = m('circle_member')->field('circle_id')->where(array('member_id' => $user_id))->select();
		$circle_ids = array_column($res, 'circle_id');
		$map = array(
			'id'         => array('in', $circle_ids),
			'chk_status' => 1
			);
		$circle_list = m('circle')->field("id as circle_id,circle_img,\r\n\t\t\tcircle_thumb,circle_name,circle_introduce,members_max,current_members,\r\n\t\t\tadmins_max,current_admins,area_id,add_time,chk_time,chk_status")->where($map)->select();
		$qiniu = c('QINIU');

		foreach ($circle_list as $k => $v) {
			$circle_list[$k]['circle_img'] = 'http://' . $qiniu['domain'] . '/' . $v['circle_img'];
			$circle_list[$k]['circle_thumb'] = 'http://' . $qiniu['domain'] . '/' . $v['circle_thumb'];
		}

		$res = m('area')->field('id as area_id,area_name')->select();
		$area_info = array_column($res, 'area_name', 'area_id');

		foreach ($circle_list as $k => $v) {
			$circle_list[$k]['area_name'] = $area_info[intval($v['area_id'])];
			unset($circle_list[$k]['area_id']);
		}

		if (!empty($circle_list)) {
			$result['data'] = $circle_list;
			exit(returnjson($result));
		}
	}

	public function addAdmin()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$circle_id = intval($data['circle_id']);
		$admin_id = intval($data['admin_id']);
		$operator_id = intval($data['operator_id']);

		if ($circle_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ($admin_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($operator_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($admin_id === $operator_id) {
			$result['msg_code'] = '100146';
			exit(returnjson($result));
		}

		$model_circle = m('circle');
		$model_circle_admin = m('circle_admin');
		$circle_info = $model_circle->where(array('id' => $circle_id))->field('id,admins_max,current_admins,creater_id')->find();

		if (empty($circle_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		if ($operator_id !== intval($circle_info['creater_id'])) {
			$result['msg_code'] = '100129';
			exit(returnjson($result));
		}

		$user_info = m('user_main')->where(array('id' => $admin_id))->field('id')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('member_id' => $admin_id, 'circle_id' => $circle_id);
		$res = m('circle_member')->where($map)->find();

		if (empty($res)) {
			$result['msg_code'] = '100150';
			exit(returnjson($result));
		}

		$map = array('admin_id' => $admin_id, 'circle_id' => $circle_id);
		$res = $model_circle_admin->where($map)->field('admin_id')->find();

		if (!empty($res)) {
			$result['msg_code'] = '100141';
			exit(returnjson($result));
		}

		if (intval($circle_info['admins_max']) <= intval($circle_info['current_admins'])) {
			$result['msg_code'] = '100142';
			exit(returnjson($result));
		}

		$data = array('circle_id' => $circle_id, 'admin_id' => $admin_id, 'add_time' => time());
		m()->startTrans();
		$res1 = $model_circle_admin->add($data);
		$res2 = $model_circle->where(array('id' => $circle_id))->setInc('current_admins');
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$result['data'] = $data;
			$result['msg_code'] = '0';
			$result['msg'] = '添加扑克圈管理员成功！';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['data'] = $data;
			$result['msg_code'] = '100103';
			exit(returnjson($result));
		}
	}

	public function delAdmin()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$circle_id = intval($data['circle_id']);
		$admin_id = intval($data['admin_id']);
		$operator_id = intval($data['operator_id']);

		if ($circle_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ($admin_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($operator_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($admin_id === $operator_id) {
			$result['msg_code'] = '100146';
			exit(returnjson($result));
		}

		$model_circle = m('circle');
		$model_circle_admin = m('circle_admin');
		$circle_info = $model_circle->where(array('id' => $circle_id))->field('id,admins_max,current_admins,creater_id')->find();

		if (empty($circle_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		if ($operator_id !== intval($circle_info['creater_id'])) {
			$result['msg_code'] = '100129';
			exit(returnjson($result));
		}

		$user_info = m('user_main')->where(array('id' => $admin_id))->field('id')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('admin_id' => $admin_id, 'circle_id' => $circle_id);
		$res = $model_circle_admin->where($map)->field('admin_id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100143';
			exit(returnjson($result));
		}

		m()->startTrans();
		$res1 = $model_circle_admin->where($map)->delete();
		$res2 = $model_circle->where(array('id' => $circle_id))->setDec('current_admins');
		$ret_data = array('circle_id' => $circle_id, 'admin_id' => $admin_id);
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$result['data'] = $ret_data;
			$result['msg'] = '删除扑克圈管理员成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['data'] = $ret_data;
			$result['msg_code'] = '100145';
			exit(returnjson($result));
		}
	}

	public function searchCircle()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$circle_id = intval($data['circle_id']);
		$circle_name = strval($data['circle_name']);
		$area_id = intval($data['area_id']);

		if (empty($circle_id)) {
			$circle_id = null;
		}

		if (empty($circle_name)) {
			$circle_name = null;
		}

		if (!empty($circle_id) && ($circle_id < 0)) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if (!empty($circle_name) && (20 < ((strlen($circle_name) + mb_strlen($circle_name, 'utf-8')) / 2))) {
			$result['msg_code'] = '100094';
			exit(returnjson($result));
		}

		$map = array();

		if (!empty($circle_id)) {
			$map['id'] = $circle_id;
		}

		if (!empty($circle_name)) {
			$map['circle_name'] = $circle_name;
		}

		if (!empty($area_id)) {
			$map['area_id'] = $area_id;
		}

		$data = array();

		if (!empty($map)) {
			$map['chk_status'] = 1;
			$data = m('circle')->where($map)->select();
		}

		$result['data'] = $data;
		$result['msg_code'] = '0';
		exit(returnjson($result));
	}

	public function applyJoinCircle()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$circle_id = intval($data['circle_id']);
		$user_id = intval($data['user_id']);
		$node_id = intval($data['node_id']);
		$apply_msg = trim(strval($data['apply_msg']));

		if ($circle_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		$model_circle = m('circle');
		$model_circle_member = m('circle_member');
		$model_circle_apply = m('circle_apply');
		$circle_info = $model_circle->where(array('id' => $circle_id))->field('id,members_max,current_members,creater_id')->find();

		if (empty($circle_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$user_info = m('user_main')->where(array('id' => $user_id))->field('id,nick_name,avatar,avatar_thumb')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('circle_id' => $circle_id, 'member_id' => $user_id);
		$res = $model_circle_member->where($map)->field('id')->find();

		if (!empty($res)) {
			$result['msg_code'] = '100099';
			exit(returnjson($result));
		}

		if (intval($circle_info['members_max']) <= intval($circle_info['current_members'])) {
			$result['msg_code'] = '100137';
			exit(returnjson($result));
		}

		$map = array('user_id' => $user_id, 'circle_id' => $circle_id);
		$res = $model_circle_apply->where($map)->field('id')->find();

		if (!empty($res)) {
			$result['msg_code'] = '100140';
			exit(returnjson($result));
		}

		$data = array('user_id' => $user_id, 'avatar' => $user_info['avatar'], 'avatar_thumb' => $user_info['avatar_thumb'], 'nick_name' => $user_info['nick_name'], 'circle_id' => $circle_id, 'creater_id' => $circle_info['creater_id'], 'apply_msg' => $apply_msg, 'chk_status' => 0, 'add_time' => time(), 'chk_time' => 0);
		$res1 = $model_circle_apply->add($data);
		$qiniu = c('QINIU');
		$param = array('user_id' => $user_id, 'avatar' => 'http://' . $qiniu['domain'] . '/' . $user_info['avatar'], 'avatar_thumb' => 'http://' . $qiniu['domain'] . '/' . $user_info['avatar_thumb'], 'nick_name' => urlencode($user_info['nick_name']), 'circle_id' => $circle_id, 'creater_id' => $circle_info['creater_id'], 'apply_msg' => urlencode($apply_msg), 'chk_status' => 0, 'add_time' => time());
		$client = new \Org\Util\ClientSocket('114.55.148.229', 7277);
		$res2 = $client->send_data($param, 7, $node_id, 0, 0, $circle_info['creater_id'], false);

		if (!empty($res1)) {
			$result['data'] = $data;
			$result['msg_code'] = '0';
			$result['msg'] = '加入扑克圈申请发送成功！';
			exit(returnjson($result));
		}
		else {
			$result['data'] = $data;
			$result['msg_code'] = '100138';
			exit(returnjson($result));
		}
	}
}

?>
