<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class ClubController extends \Home\Controller\BaseController
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

	public function clubAdd()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$club_name = trim(strval($data['club_name']));
		$area_id = intval($data['area_id']);
		$is_private = intval($data['is_private']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if (20 < ((strlen($club_name) + mb_strlen($club_name, 'utf-8')) / 2)) {
			$result['msg_code'] = '100094';
			exit(returnjson($result));
		}

		if ($area_id <= 0) {
			$result['msg_code'] = '100095';
			exit(returnjson($result));
		}

		$model_user_main = m('user_main');
		$user_info = $model_user_main->where(array('id' => $user_id))->field('id,clubs_max,current_clubs')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		if (intval($user_info['clubs_max']) <= intval($user_info['current_clubs'])) {
			$result['msg_code'] = '100107';
			exit(returnjson($result));
		}

		$map = array('id' => $area_id);
		$area_name = m('area')->where($map)->getfield('area_name');
		$goods_info = m('goods')->where(array('id' => 7, 'status' => 1))->find();
		$qiniu = c('QINIU');
		$data = array('club_name' => $club_name, 'club_level' => $goods_info['club_level'], 'club_img' => $qiniu['default_clb_img'], 'club_thumb' => $qiniu['default_clb_thumb'], 'members_max' => $goods_info['members_max'], 'admins_max' => $goods_info['admins_max'], 'current_members' => 1, 'current_admins' => 0, 'is_private' => $is_private, 'creater_id' => $user_id, 'area_id' => $area_id, 'create_time' => time());
		m()->startTrans();
		$club_id = m('club')->add($data);
		$data = array('club_id' => $club_id, 'member_id' => $user_id, 'add_time' => time());
		$res1 = m('club_member')->add($data);
		$res2 = $model_user_main->where(array('id' => $user_id))->setInc('current_clubs');
		if (!empty($club_id) && !empty($res1) && !empty($res2)) {
			m()->commit();
			$ret_data = array('club_id' => $club_id, 'club_name' => $club_name, 'club_level' => $goods_info['club_level'], 'club_introduce' => '', 'club_img' => 'http://' . $qiniu['domain'] . '/' . $qiniu['default_clb_img'], 'club_thumb' => 'http://' . $qiniu['domain'] . '/' . $qiniu['default_clb_thumb'], 'club_gold' => 0, 'members_max' => $goods_info['members_max'], 'admins_max' => $goods_info['admins_max'], 'current_members' => 1, 'current_admins' => 0, 'is_private' => $is_private, 'creater_id' => $user_id, 'area_id' => $area_id, 'area_name' => $area_name, 'create_time' => time());
			$result['data'] = $ret_data;
			$result['msg'] = '创建俱乐部成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$ret_data = array('club_name' => $club_name, 'creater_id' => $user_id, 'area_id' => $area_id, 'create_time' => time());
			$result['data'] = $ret_data;
			$result['msg_code'] = '100093';
			exit(returnjson($result));
		}
	}

	public function modifyClubInfo()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$club_id = intval($data['club_id']);
		$club_name = trim(strval($data['club_name']));
		$club_introduce = trim(strval($data['club_introduce']));
		$img_ext = strtolower($data['img_ext']);
		$club_img = trim(strval($data['club_img']));
		$club_thumb = trim(strval($data['club_thumb']));
		$is_private = intval($data['is_private']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ((10 < mb_strlen($club_name, 'utf-8')) && (30 < strlen($club_name))) {
			$result['msg_code'] = '100094';
			exit(returnjson($result));
		}

		if (512 < mb_strlen($club_introduce, 'utf-8')) {
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

		$model_club = m('club');
		$res = $model_club->where(array('id' => $club_id))->field('id,creater_id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		if (intval($res['creater_id']) !== $user_id) {
			$result['msg_code'] = '100129';
			exit(returnjson($result));
		}

		if (!empty($club_img) && !empty($img_ext)) {
			$file_data = base64_urlsafedecode($club_img);
			$file_name = './Uploads/club_img/' . $club_id . '_c_b.' . $img_ext;
			file_put_contents($file_name, $file_data);
			$param = array('club_id' => $club_id, 'file_type' => 1, 'file_ext_name' => $img_ext);
			$response = request_post('http://' . c('SERVER_DOMAIN') . '/upload_club.php', $param);
			$ret = json_decode($response, true);

			if (empty($ret['key'])) {
				$result['msg_code'] = '100092';
				exit(returnjson($result));
			}

			$club_img_name = $ret['key'];
		}

		if (!empty($club_thumb) && !empty($img_ext)) {
			$file_data = base64_urlsafedecode($club_thumb);
			$file_name = './Uploads/club_thumb/' . $club_id . '_c_s.' . $img_ext;
			file_put_contents($file_name, $file_data);
			$param = array('club_id' => $club_id, 'file_type' => 2, 'file_ext_name' => $img_ext);
			$response = request_post('http://' . c('SERVER_DOMAIN') . '/upload_club.php', $param);
			$ret = json_decode($response, true);

			if (empty($ret['key'])) {
				$result['msg_code'] = '100092';
				exit(returnjson($result));
			}

			$club_thumb_name = $ret['key'];
		}

		$data = array();

		if (!empty($club_name)) {
			$data['club_name'] = $club_name;
		}

		if (!empty($club_introduce)) {
			$data['club_introduce'] = $club_introduce;
		}

		if (!empty($club_img) && !empty($club_thumb)) {
			$data['club_img'] = $club_img_name;
			$data['club_thumb'] = $club_thumb_name;
		}

		$model_club->where(array('id' => $club_id))->save($data);
		$qiniu = c('QINIU');
		$data['club_id'] = $club_id;
		$data['creater_id'] = $user_id;
		$data['is_private'] = $is_private;
		$data['club_img'] = 'http://' . $qiniu['domain'] . '/' . $club_img_name;
		$data['club_thumb'] = 'http://' . $qiniu['domain'] . '/' . $club_thumb_name;
		$result['data'] = $data;
		$result['msg'] = '俱乐部信息修改成功！';
		$result['msg_code'] = '0';
		exit(returnjson($result));
	}

	public function getClubDetail()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$club_id = intval($data['club_id']);

		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		$res_club = m('club')->where(array('id' => $club_id))->find();

		if (empty($res_club)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$res_member = m('club_member')->field('member_id')->where(array('club_id' => $club_id))->select();
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

		$area_name = m('area')->where(array('id' => intval($res_club['area_id'])))->getfield('area_name');
		$creater_info = $model_user_main->field('id,nick_name,avatar,avatar_thumb')->where(array('id' => intval($res_club['creater_id'])))->find();
		$data = array('club_id' => intval($club_id), 'club_name' => $res_club['club_name'], 'club_introduce' => $res_club['club_introduce'], 'club_img' => 'http://' . $qiniu['domain'] . '/' . $res_club['club_img'], 'club_thumb' => 'http://' . $qiniu['domain'] . '/' . $res_club['club_thumb'], 'member_info' => $member_info, 'club_gold' => intval($res_club['club_gold']), 'members_max' => intval($res_club['members_max']), 'current_members' => intval($res_club['current_members']), 'admins_max' => intval($res_club['admins_max']), 'current_admins' => intval($res_club['current_admins']), 'is_private' => intval($res_club['is_private']), 'creater_id' => intval($creater_info['id']), 'creater_nick_name' => $creater_info['nick_name'], 'creater_avatar' => 'http://' . $qiniu['domain'] . '/' . $creater_info['avatar'], 'creater_avatar_thumb' => 'http://' . $qiniu['domain'] . '/' . $creater_info['avatar_thumb'], 'area_id' => intval($res_club['area_id']), 'area_name' => $area_name, 'create_time' => intval($res_club['create_time']));
		$result['data'] = $data;
		exit(returnjson($result));
	}

	public function delClub()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$club_id = intval($data['club_id']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		$model_club = m('club');
		$model_club_member = m('club_member');
		$model_club_admin = m('club_admin');
		$res = $model_club->where(array('id' => $club_id))->field('id,creater_id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$map = array(
			'club_id'   => $club_id,
			'member_id' => array('NEQ', intval($res['creater_id']))
			);
		$res = $model_club_member->where($map)->field('id')->find();

		if (!empty($res)) {
			$result['msg_code'] = '100100';
			exit(returnjson($result));
		}

		m()->startTrans();
		$res1 = $model_club_member->where(array('club_id' => $club_id))->delete();
		$res2 = $model_club->where(array('id' => $club_id))->delete();
		$res3 = m('user_main')->where(array('id' => $user_id))->setDec('current_clubs');
		if (!empty($res1) && !empty($res2) && !empty($res3)) {
			m()->commit();
			$model_club_admin->where(array('club_id' => $club_id))->delete();
			$result['msg'] = '删除俱乐部成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['msg_code'] = '100101';
			exit(returnjson($result));
		}
	}

	public function applyJoinClub()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$club_id = intval($data['club_id']);
		$user_id = intval($data['user_id']);
		$node_id = intval($data['node_id']);
		$apply_msg = trim(strval($data['apply_msg']));

		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		$model_club = m('club');
		$model_club_member = m('club_member');
		$model_club_apply = m('club_apply');
		$club_info = $model_club->where(array('id' => $club_id))->field('id,members_max,current_members,creater_id')->find();

		if (empty($club_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$user_info = m('user_main')->where(array('id' => $user_id))->field('id,nick_name,avatar,avatar_thumb')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('club_id' => $club_id, 'member_id' => $user_id);
		$res = $model_club_member->where($map)->field('id')->find();

		if (!empty($res)) {
			$result['msg_code'] = '100099';
			exit(returnjson($result));
		}

		if (intval($club_info['members_max']) <= intval($club_info['current_members'])) {
			$result['msg_code'] = '100137';
			exit(returnjson($result));
		}

		$map = array('user_id' => $user_id, 'club_id' => $club_id);
		$res = $model_club_apply->where($map)->field('id')->find();

		if (!empty($res)) {
			$result['msg_code'] = '100140';
			exit(returnjson($result));
		}

		$data = array('user_id' => $user_id, 'avatar' => $user_info['avatar'], 'avatar_thumb' => $user_info['avatar_thumb'], 'nick_name' => $user_info['nick_name'], 'club_id' => $club_id, 'creater_id' => $club_info['creater_id'], 'apply_msg' => $apply_msg, 'chk_status' => 0, 'add_time' => time(), 'chk_time' => 0);
		$res1 = $model_club_apply->add($data);
		$qiniu = c('QINIU');
		$param = array('user_id' => $user_id, 'avatar' => 'http://' . $qiniu['domain'] . '/' . $user_info['avatar'], 'avatar_thumb' => 'http://' . $qiniu['domain'] . '/' . $user_info['avatar_thumb'], 'nick_name' => urlencode($user_info['nick_name']), 'club_id' => $club_id, 'creater_id' => $club_info['creater_id'], 'apply_msg' => urlencode($apply_msg), 'chk_status' => 0, 'add_time' => time());
		$client = new \Org\Util\ClientSocket('114.55.148.229', 5277);
		$res2 = $client->send_data($param, 7, $node_id, 0, 0, $club_info['creater_id'], false);

		if (!empty($res1)) {
			$result['data'] = $data;
			$result['msg_code'] = '0';
			$result['msg'] = '加入俱乐部申请发送成功！';
			exit(returnjson($result));
		}
		else {
			$result['data'] = $data;
			$result['msg_code'] = '100138';
			exit(returnjson($result));
		}
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

		$club_id = intval($data['club_id']);
		$member_id = intval($data['member_id']);

		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ($member_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		$model_club = m('club');
		$model_club_member = m('club_member');
		$model_club_apply = m('club_apply');
		$map = array('user_id' => $member_id, 'club_id' => $club_id);
		$res = $model_club_apply->where($map)->find();

		if (empty($res)) {
			$result['msg_code'] = '100139';
			exit(returnjson($result));
		}

		$club_info = $model_club->where(array('id' => $club_id))->field('id,members_max,current_members')->find();

		if (empty($club_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$user_info = m('user_main')->where(array('id' => $member_id))->field('id')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$res = $model_club_member->where(array('member_id' => $member_id))->field('member_id')->find();

		if (!empty($res)) {
			$result['msg_code'] = '100099';
			exit(returnjson($result));
		}

		if (intval($club_info['members_max']) <= intval($club_info['current_members'])) {
			$result['msg_code'] = '100108';
			exit(returnjson($result));
		}

		$data = array('club_id' => $club_id, 'member_id' => $member_id, 'add_time' => time());
		m()->startTrans();
		$res1 = $model_club_member->add($data);
		$res2 = $model_club->where(array('id' => $club_id))->setInc('current_members');
		$data = array('chk_status' => 1, 'chk_time' => time());
		$res3 = $model_club_apply->where($map)->save($data);
		if (!empty($res1) && !empty($res2) && !empty($res3)) {
			m()->commit();
			$result['data'] = $data;
			$result['msg_code'] = '0';
			$result['msg'] = '添加俱乐部成员成功！';
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

		$club_id = intval($data['club_id']);
		$member_id = intval($data['member_id']);

		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ($member_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		$model_club = m('club');
		$model_club_member = m('club_member');
		$res = $model_club->where(array('id' => $club_id))->field('id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$res = m('user_main')->where(array('id' => $member_id))->field('id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('club_id' => $club_id, 'member_id' => $member_id);
		$res = $model_club_member->where($map)->find();

		if (empty($res)) {
			$result['msg_code'] = '100106';
			exit(returnjson($result));
		}

		m()->startTrans();
		$res1 = $model_club_member->where($map)->delete();
		$res2 = $model_club->where(array('id' => $club_id))->setDec('current_members');
		$ret_data = array('club_id' => $club_id, 'member_id' => $member_id);
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$result['data'] = $ret_data;
			$result['msg'] = '删除俱乐部成员成功！';
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

	public function getMyClubList()
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

		$res = m('club_member')->field('club_id')->where(array('member_id' => $user_id))->select();
		$club_ids = array_column($res, 'club_id');
		$map = array(
			'id' => array('in', $club_ids)
			);
		$club_list = m('club')->field("id as club_id,club_img,\r\n\t\t\tclub_thumb,club_name,club_introduce,club_level,club_gold,\r\n\t\t\tmembers_max,current_members,admins_max,current_admins,area_id,create_time")->where($map)->select();
		$qiniu = c('QINIU');

		foreach ($club_list as $k => $v) {
			$club_list[$k]['club_img'] = 'http://' . $qiniu['domain'] . '/' . $v['club_img'];
			$club_list[$k]['club_thumb'] = 'http://' . $qiniu['domain'] . '/' . $v['club_thumb'];
		}

		$res = m('area')->field('id as area_id,area_name')->select();
		$area_info = array_column($res, 'area_name', 'area_id');

		foreach ($club_list as $k => $v) {
			$club_list[$k]['area_name'] = $area_info[intval($v['area_id'])];
			unset($club_list[$k]['area_id']);
		}

		if (!empty($club_list)) {
			$result['data'] = $club_list;
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

		$club_id = intval($data['club_id']);
		$admin_id = intval($data['admin_id']);
		$operator_id = intval($data['operator_id']);

		if ($club_id <= 0) {
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

		$model_club = m('club');
		$model_club_admin = m('club_admin');
		$club_info = $model_club->where(array('id' => $club_id))->field('id,admins_max,current_admins,creater_id')->find();

		if (empty($club_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		if ($operator_id !== intval($club_info['creater_id'])) {
			$result['msg_code'] = '100129';
			exit(returnjson($result));
		}

		$user_info = m('user_main')->where(array('id' => $admin_id))->field('id')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('member_id' => $admin_id, 'club_id' => $club_id);
		$res = m('club_member')->where($map)->find();

		if (empty($res)) {
			$result['msg_code'] = '100149';
			exit(returnjson($result));
		}

		$map = array('admin_id' => $admin_id, 'club_id' => $club_id);
		$res = $model_club_admin->where($map)->field('admin_id')->find();

		if (!empty($res)) {
			$result['msg_code'] = '100141';
			exit(returnjson($result));
		}

		if (intval($club_info['admins_max']) <= intval($club_info['current_admins'])) {
			$result['msg_code'] = '100142';
			exit(returnjson($result));
		}

		$data = array('club_id' => $club_id, 'admin_id' => $admin_id, 'add_time' => time());
		m()->startTrans();
		$res1 = $model_club_admin->add($data);
		$res2 = $model_club->where(array('id' => $club_id))->setInc('current_admins');
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$result['data'] = $data;
			$result['msg_code'] = '0';
			$result['msg'] = '添加俱乐部管理员成功！';
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

		$club_id = intval($data['club_id']);
		$admin_id = intval($data['admin_id']);
		$operator_id = intval($data['operator_id']);

		if ($club_id <= 0) {
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

		$model_club = m('club');
		$model_club_admin = m('club_admin');
		$club_info = $model_club->where(array('id' => $club_id))->field('id,admins_max,current_admins,creater_id')->find();

		if (empty($club_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		if ($operator_id !== intval($club_info['creater_id'])) {
			$result['msg_code'] = '100129';
			exit(returnjson($result));
		}

		$user_info = m('user_main')->where(array('id' => $admin_id))->field('id')->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('admin_id' => $admin_id, 'club_id' => $club_id);
		$res = $model_club_admin->where($map)->field('admin_id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100143';
			exit(returnjson($result));
		}

		m()->startTrans();
		$res1 = $model_club_admin->where($map)->delete();
		$res2 = $model_club->where(array('id' => $club_id))->setDec('current_admins');
		$ret_data = array('club_id' => $club_id, 'admin_id' => $admin_id);
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$result['data'] = $ret_data;
			$result['msg'] = '删除俱乐部管理员成功！';
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

	public function searchClub()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$club_id = intval($data['club_id']);
		$club_name = strval($data['club_name']);
		$area_id = intval($data['area_id']);

		if (empty($club_id)) {
			$club_id = null;
		}

		if (empty($club_name)) {
			$club_name = null;
		}

		if (!empty($club_id) && ($club_id < 0)) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if (!empty($club_name) && (20 < ((strlen($club_name) + mb_strlen($club_name, 'utf-8')) / 2))) {
			$result['msg_code'] = '100094';
			exit(returnjson($result));
		}

		$map = array();

		if (!empty($club_id)) {
			$map['id'] = $club_id;
		}

		if (!empty($club_name)) {
			$map['club_name'] = $club_name;
		}

		if (!empty($area_id)) {
			$map['area_id'] = $area_id;
		}

		if (!empty($area_id) && empty($club_id) && empty($club_name)) {
			$map['is_private'] = 0;
		}

		$data = array();

		if (!empty($map)) {
			$data = m('club')->where($map)->select();
		}

		$result['data'] = $data;
		$result['msg_code'] = '0';
		exit(returnjson($result));
	}

	public function uploadClubAvatar()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$club_id = intval($data['club_id']);
		$img_ext = strtolower($data['img_ext']);
		$club_img = trim(strval($data['club_img']));
		$club_thumb = trim(strval($data['club_thumb']));

		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if (($img_ext != 'jpg') && ($img_ext != 'gif') && ($img_ext != 'png') && ($img_ext != 'jpeg') && ($img_ext != '')) {
			$result['msg_code'] = '100091';
			exit(returnjson($result));
		}

		$model_club = m('club');
		$res = $model_club->where(array('id' => $club_id))->field('id,creater_id')->find();

		if (empty($res)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		if (!empty($club_img) && !empty($img_ext)) {
			$file_data = base64_urlsafedecode($club_img);
			$file_name = './Uploads/club_img/' . $club_id . '_c_b.' . $img_ext;
			file_put_contents($file_name, $file_data);
			$param = array('club_id' => $club_id, 'file_type' => 1, 'file_ext_name' => $img_ext);
			$response = request_post('http://' . c('SERVER_DOMAIN') . '/upload_club.php', $param);
			$ret = json_decode($response, true);

			if (empty($ret['key'])) {
				$result['msg_code'] = '100092';
				exit(returnjson($result));
			}

			$club_img_name = $ret['key'];
		}

		if (!empty($club_thumb) && !empty($img_ext)) {
			$file_data = base64_urlsafedecode($club_thumb);
			$file_name = './Uploads/club_thumb/' . $club_id . '_c_s.' . $img_ext;
			file_put_contents($file_name, $file_data);
			$param = array('club_id' => $club_id, 'file_type' => 2, 'file_ext_name' => $img_ext);
			$response = request_post('http://' . c('SERVER_DOMAIN') . '/upload_club.php', $param);
			$ret = json_decode($response, true);

			if (empty($ret['key'])) {
				$result['msg_code'] = '100092';
				exit(returnjson($result));
			}

			$club_thumb_name = $ret['key'];
		}

		$data = array();
		if (!empty($club_img) && !empty($club_thumb)) {
			$data['club_img'] = $club_img_name;
			$data['club_thumb'] = $club_thumb_name;
		}

		$model_club->where(array('id' => $club_id))->save($data);
		$qiniu = c('QINIU');
		$data['club_id'] = $club_id;
		$data['club_img'] = 'http://' . $qiniu['domain'] . '/' . $club_img_name;
		$data['club_thumb'] = 'http://' . $qiniu['domain'] . '/' . $club_thumb_name;
		$result['data'] = $data;
		$result['msg'] = '俱乐部头像上传成功！';
		$result['msg_code'] = '0';
		exit(returnjson($result));
	}
}

?>
