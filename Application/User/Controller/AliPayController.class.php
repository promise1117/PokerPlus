<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class AliPayController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function _initialize()
	{
		import('Vendor.Alipay.lib.AlipayNotify');
	}

	public function createOrder()
	{
		$data = self::get_param();
		$result = array();
		$mobile = intval($data['mobile']);
		$diamond = intval($data['diamond']);
		$amount = 0;
		$pay_type = intval($data['pay_type']);
		$pay_type = 1;
		$sale_code = trim(strval($data['sale_code']));
		$openid = trim(strval($data['openid']));

		if ($diamond <= 0) {
			$result['msg_code'] = '100109';
			exit(returnjson($result));
		}

		if ($pay_type <= 0) {
			$result['msg_code'] = '100111';
			exit(returnjson($result));
		}

		$user_main_model = m('user_main');
		$user_info = $user_main_model->field('id,mobile')->where(array('mobile' => $mobile))->find();

		if (empty($user_info)) {
			$result['msg_code'] = '100013';
			exit(returnjson($result));
		}

		$discount = null;
		$recommend_id = 0;

		if (!empty($sale_code)) {
			$map = array('sale_code' => $sale_code);
			$res = $user_main_model->field('id,sale_code')->where($map)->find();

			if (!empty($res)) {
				$discount = 0.9;
				$recommend_id = intval($res['id']);
			}
		}

		$discount = ($discount === null ? 1 : $discount);
		$price = intval((($diamond * 1) / 10) * $discount * 100);
		$amount = $price;
		$order_sn = getrandstrtosn('60');
		$data = array('order_sn' => $order_sn, 'diamond' => $diamond, 'user_id' => $user_info['id'], 'mobile' => $mobile, 'recommend_id' => $recommend_id, 'amount' => $amount * 100, 'pay_type' => $pay_type, 'pay_status' => 0, 'remark' => '购买钻石' . $diamond . '颗', 'pay_time' => 0, 'add_time' => time());
		$param = array('openid' => $openid, 'total_fee' => $amount, 'out_trade_no' => $order_sn);
		$jsApiParameters = null;
		m()->startTrans();
		$jsApiParameters = request_post('http://' . c('SERVER_DOMAIN') . '/User/WxJsAPI/unifiedorder', $param);
		$res = json_decode($jsApiParameters, true);
		$res_arr = explode('=', $res['package']);
		$prepay_id = $res_arr[1];
		if (!empty($prepay_id) && m('user_order')->add($data)) {
			m()->commit();
			$result['data'] = $data;
			$result['jsApiParameters'] = $jsApiParameters;
			$result['discount'] = $discount;
			$result['msg'] = '充值订单创建成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$result['data'] = $data;
			$result['jsApiParameters'] = $jsApiParameters;
			$result['msg'] = '充值订单创建失败！';
			$result['msg_code'] = '100060';
			exit(returnjson($result));
		}
	}

	public function alipay_callback()
	{
		$out_trade_no = i('out_trade_no', '', 'trim');
		$param_arr = explode('-', $out_trade_no);
		$order_sn = $param_arr[0];
		$pay_third = intval($param_arr[1]);
		$pay_status = i('trade_status', '', 'trim');
		$trade_no = i('trade_no', '', 'trim');
		$total_fee = i('total_fee', '', 'trim');
		$total_fee = floatval($total_fee);
		$data = array('out_trade_no' => $order_sn, 'trade_status' => $pay_status, 'trade_no' => $trade_no, 'total_fee' => $total_fee, 'pay_third' => $pay_third);
		$order_canteen = m('order_canteen');
		$map = array('order_sn' => $order_sn, 'pay_status' => 1);

		if ($order_canteen->where($map)->find()) {
			exit('success');
		}

		$map = array('order_sn' => $order_sn, 'pay_status' => 0);
		$res = $order_canteen->where($map)->find();

		if (!empty($res)) {
			$money_paid = floatval($res['money_paid']);
		}

		if (($total_fee == $money_paid) && (($pay_status == 'TRADE_SUCCESS') || ($pay_status == 'TRADE_FINISHED'))) {
			$ali_path = VENDOR_PATH . 'Alipay/';
			$alipay_config = array('partner' => '2088021217583913', 'private_key_path' => $ali_path . 'key/rsa_private_key.pem', 'ali_public_key_path' => $ali_path . 'key/alipay_public_key.pem', 'sign_type' => strtoupper('MD5'), 'input_charset' => strtolower('utf-8'), 'cacert' => $ali_path . 'cacert.pem', 'transport' => 'http');
			$alipayNotify = new \AlipayNotify($alipay_config);
			$verify_result = $alipayNotify->verifyNotify();

			if ($verify_result) {
				echo $this->orderAlipayDo($data);
				exit();
			}
			else {
				exit('fail');
			}
		}
	}

	protected function orderAlipayDo($paydata)
	{
		$out_trade_no = trim($paydata['out_trade_no']);
		$trade_status = trim($paydata['trade_status']);
		$trade_no = trim($paydata['trade_no']);
		$total_fee = floatval($paydata['total_fee']);
		$pay_third = intval($paydata['pay_third']);
		$order_canteen = m('order_canteen');
		$order_task = m('order_task');
		$order_canteen_flow_status = m('order_canteen_flow_status');
		$service_type = 3;
		$map = array('order_sn' => $out_trade_no, 'pay_status' => 0);
		$res = $order_canteen->where($map)->find();

		if (!$res) {
			exit('fail');
		}

		$user_id = $res['user_id'];
		$amount = $res['amount'];
		$helper_id = intval($res['helper_id']);
		$money_paid = floatval($res['money_paid']);
		$canteen_type = intval($res['canteen_type']);
		$hospital_id = intval($res['hospital_id']);

		if ($total_fee != $money_paid) {
			$alipay_anomaly = m('alipay_anomaly');
			$data = array('user_id' => $user_id, 'order_no' => $out_trade_no, 'total_fee' => $total_fee, 'order_money' => $money_paid, 'addtime' => time());

			if (!$alipay_anomaly->add($data)) {
				exit('fail');
			}
		}

		$order_canteen->pay_type = 1;
		$order_canteen->pay_status = 1;
		$order_canteen->third_no = $trade_no;
		$order_canteen->pay_time = time();
		$order_canteen->pay_third = $pay_third;
		$order_canteen->flow_status = 2;
		$map = array('order_sn' => $out_trade_no, 'user_id' => $user_id, 'pay_status' => 0);
		$res = $order_canteen->where($map)->save();

		if (!$res) {
			exit('fail');
		}

		$map = array('order_sn' => $out_trade_no, 'order_type' => 1);
		$order_task->where($map)->delete();
		$data = array('u_id' => $user_id, 'order_sn' => $out_trade_no, 'flow_status' => 2, 'canteen_type' => $canteen_type, 'addtime' => time(), 'active' => 1, 'is_delete' => 0);
		$order_canteen_flow_status->add($data);
		$msg_meal = c('meal');
		$msg = $msg_meal[4];
		$common_msg = m('common_msg');
		$data = array('u_id' => $user_id, 'order_sn' => $out_trade_no, 'msg' => $msg, 'service_type' => $service_type, 'msg_type' => 1, 'return_code' => -1, 'res_log' => 'ok', 'addtime' => time(), 'active' => 1, 'is_delete' => 0);
		$res = $common_msg->add($data);

		if (!$res) {
			$result['msg_code'] = '0069';
			exit(returnjson($result));
		}

		return 'success';
	}
}

?>
