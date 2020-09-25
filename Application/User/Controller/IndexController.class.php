<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class IndexController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function getMsgList()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$hospital_id = intval($data['hospital_id']);
		$n_page = intval($data['n_page']);
		$result = array();
		$common_msg = m('common_msg');
		$order_express = m('order_express');
		$order_canteen = m('order_canteen');
		$order_washing = m('order_washing');
		$helper_news = m('helper_news');
		$data = array(
			'msg'  => array(),
			'news' => array()
			);

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if ($n_page <= 0) {
			$result['msg_code'] = '0071';
			exit(returnjson($result));
		}

		$page_arr = $this->getCurrentPage($n_page);
		$year = date('Y');
		$month = date('m');
		$day = date('d');
		$dayBegin = mktime(0, 0, 0, $month, $day, $year);
		$dayEnd = mktime(23, 59, 59, $month, $day, $year);
		$map = array(
			'u_id'         => array('eq', $user_id),
			'msg_type'     => array('eq', 1),
			'service_type' => array('neq', 0),
			'addtime'      => array('between', $dayBegin . ',' . $dayEnd),
			'active'       => array('eq', 1),
			'is_delete'    => array('eq', 0)
			);
		$res = $common_msg->field('max(id) as id')->where($map)->group('order_sn')->limit($page_arr['current_page'] . ',' . $page_arr['page_size'])->select();

		if (!empty($res)) {
			$ids = array();

			foreach ($res as $v) {
				$ids[] = intval($v['id']);
			}

			$map = array(
				'id' => array('in', $ids)
				);
			$msg_arr = $common_msg->field('order_sn,msg,service_type,addtime')->where($map)->select();
			$msg_list = array();

			foreach ($msg_arr as $v) {
				$msg_list[$v['order_sn']] = $v;
			}

			$order_sns = array();

			foreach ($msg_arr as $v) {
				$order_sns[] = $v['order_sn'];
			}

			$map = array(
				'order_sn' => array('in', $order_sns)
				);
			$express_arr = $order_express->field("order_sn,amount,express_type,\n\t\t\t\ttracking_num,pay_status,flow_status,addtime as order_time")->where($map)->select();
			$express_order_list = array();

			if (!empty($express_arr)) {
				foreach ($express_arr as $v) {
					$express_order_list[$v['order_sn']] = $v;
					unset($express_order_list[$v['order_sn']]['order_sn']);
				}
			}

			$canteen_arr = $order_canteen->field("order_sn,amount,money_paid,pay_status,\n\t\t\t\tflow_status,addtime as order_time")->where($map)->select();
			$canteen_order_list = array();

			if (!empty($canteen_arr)) {
				foreach ($canteen_arr as $v) {
					$canteen_order_list[$v['order_sn']] = $v;
					unset($canteen_order_list[$v['order_sn']]['order_sn']);
				}
			}

			$washing_arr = $order_washing->field("order_sn,amount,money_paid,pay_status,\n\t\t\t\tflow_status,addtime as order_time")->where($map)->select();
			$washing_order_list = array();

			if (!empty($washing_arr)) {
				foreach ($washing_arr as $v) {
					$washing_order_list[$v['order_sn']] = $v;
					unset($washing_order_list[$v['order_sn']]['order_sn']);
				}
			}

			if (!empty($express_order_list)) {
				$msg_list = array_merge_recursive_new($msg_list, $express_order_list);
			}

			if (!empty($canteen_order_list)) {
				$msg_list = array_merge_recursive_new($msg_list, $canteen_order_list);
			}

			if (!empty($washing_order_list)) {
				$msg_list = array_merge_recursive_new($msg_list, $washing_order_list);
			}

			$msg_list = array_values($msg_list);
			$msg_data = array();

			foreach ($msg_list as $v) {
				$msg_data[$v['order_time']] = $v;
				unset($msg_data[$v['order_time']]['order_time']);
			}

			krsort($msg_data, SORT_NUMERIC);
			$data['msg'] = array_values($msg_data);
		}

		$map = array(
			'hospital_id' => $hospital_id,
			'times'       => array('between', $dayBegin . ',' . $dayEnd),
			'is_open'     => 1,
			'is_delete'   => 0
			);
		$res = $helper_news->field("id,title,hospital_id,\n\t\tauthor,brief,img_thumb,hits")->where($map)->select();

		if (!empty($res)) {
			$data['news'] = $res;
		}

		$result['data'] = $data;
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}

	public function getMsgStatusList()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$order_sn = strval($data['order_sn']);
		$service_type = intval($data['service_type']);
		$result = array();
		$common_msg = m('common_msg');

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		$map = array(
			'u_id'         => array('eq', $user_id),
			'order_sn'     => array('eq', $order_sn),
			'msg_type'     => array('eq', 1),
			'service_type' => array('eq', $service_type),
			'active'       => array('eq', 1),
			'is_delete'    => array('eq', 0)
			);
		$res = $common_msg->field('order_sn,msg,service_type,addtime')->where($map)->order('addtime desc')->select();
		$result['data'] = empty($res) ? (object) null : $res;
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}

	public function getWeatherByCity()
	{
		$data = self::get_param();
		$hospital_id = intval($data['hospital_id']);
		$hospital_info = $this->hgetallValueFromCache('hospital_id:' . $hospital_id);

		if (empty($hospital_info)) {
			$hospital_list = m('hospital')->select();
			$this->hmsetListToCache('hospital_id:', $hospital_list, 'id');
			$hospital_info = $this->hgetallValueFromCache('hospital_id:' . $hospital_id);
		}

		$city_id = intval($hospital_info['city_id']);
		$city_info = $this->hgetallValueFromCache('city_id:' . $city_id);

		if (empty($city_info)) {
			$city_list = m('city')->select();
			$this->hmsetListToCache('city_id:', $city_list, 'code');
			$city_info = $this->hgetallValueFromCache('city_id:' . $city_id);
		}

		$city_name = $city_info['name'];
		$city_code = intval($city_info['code']);
		$city_weather_info = $this->hgetallValueFromCache('city_weather_id:' . $city_code);

		if (empty($city_weather_info)) {
			$city_weather_list = m('city_weather')->select();
			$this->hmsetListToCache('city_weather_id:', $city_weather_list, 'city_code');
			$city_weather_info = $this->hgetallValueFromCache('city_weather_id:' . $city_code);
		}

		$sina_code = $city_weather_info['sina_code'];
		$param = array('city' => $sina_code, 'password' => 'DJOYnieT8234jlsK', 'day' => 0);
		$re = request_post('http://php.weather.sina.com.cn/xml.php?city=' . $sina_code . '&password=DJOYnieT8234jlsK&day=0', $param);
		$xml = new \SimpleXMLElement($re);
		$date1 = date('Y-m-d', time());
		$date1 = strtotime($date1 . ' 11:00:00');
		$data2 = date(time());

		if ($date1 < $data2) {
			$img = '/res/images/weather/' . $xml->Weather->figure2 . '_1.png';
			$weat_title = strval($xml->Weather->status2);
		}
		else {
			$img = '/res/images/weather/' . $xml->Weather->figure1 . '_0.png';
			$weat_title = strval($xml->Weather->status1);
		}

		$weather['city_name'] = $city_name;
		$weather['wash_title'] = strval($xml->Weather->xcz_l);
		$weather['degree_s'] = strval($xml->Weather->temperature1);
		$weather['degree_e'] = strval($xml->Weather->temperature2);
		$weather['img'] = $img;
		$weather['weat_title'] = $weat_title;
		$result['data'] = empty($weather) ? (object) null : $weather;
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}

	public function getHospitalInfo()
	{
		$data = self::get_param();
		$hospital_id = intval($data['hospital_id']);
		$hospital_info = $this->hgetallValueFromCache('hospital_id:' . $hospital_id);

		if (empty($hospital_info)) {
			$hospital_list = m('hospital')->select();
			$this->hmsetListToCache('hospital_id:', $hospital_list, 'id');
			$hospital_info = $this->hgetallValueFromCache('hospital_id:' . $hospital_id);
		}

		$hospital_info['parking_areas'] = explode(',', $hospital_info['parking_areas']);
		$hospital_info['parking_image'] = c('COS_FILE_URL') . '/' . $hospital_info['parking_image'];
		$result['data'] = $hospital_info;
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}
}

?>
