<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace Index\Controller;

class IndexController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function old_index()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$n_page = intval($data['n_page']);
		$result = array();
		$order_canteen_flow_status = m('order_canteen_flow_status');
		$order_washing_flow_status = m('order_washing_flow_status');
		$order_canteen_menu = m('order_canteen_menu');
		$canteen_menu = m('canteen_menu');
		$order_washing = m('order_washing');
		$hospital_news = m('hospital_news');
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
			'u_id'        => $user_id,
			'flow_status' => 1,
			'addtime'     => array('between', $dayBegin . ',' . $dayEnd),
			'active'      => 1,
			'is_delete'   => 0
			);
		$res1 = $order_canteen_flow_status->field('order_sn')->where($map)->order('addtime desc')->select();
		$res2 = $order_washing_flow_status->field('order_sn')->where($map)->order('addtime desc')->select();
		$res = array_merge(empty($res1) ? array() : $res1, empty($res2) ? array() : $res2);

		if (!empty($res)) {
			$order_sns = array();

			foreach ($res as $v) {
				$order_sns[] = $v['order_sn'];
			}

			$map = array(
				'order_sn' => array('in', $order_sns)
				);
			$ofs_arr1 = array();
			$ofs_arr2 = array();
			$ofs_arr = array();
			$ofs_arr1 = $order_canteen_flow_status->field("id,u_id,order_sn,\n\t\t\t\tflow_status,3 as service_type,canteen_type,addtime")->where($map)->limit($page_arr['current_page'] . ',' . $page_arr['page_size'])->select();
			$ofs_arr2 = $order_washing_flow_status->field("id,u_id,order_sn,\n\t\t\t\tflow_status,1 as service_type,canteen_type,addtime")->where($map)->limit($page_arr['current_page'] . ',' . $page_arr['page_size'])->select();
			$ofs_arr = array_merge(empty($ofs_arr1) ? array() : $ofs_arr1, empty($ofs_arr2) ? array() : $ofs_arr2);

			if ($page_arr['page_size'] < count($ofs_arr)) {
				$tmp_arr = array();

				foreach ($ofs_arr as $v) {
					$tmp_arr[] = $v;

					if (count($tmp_arr) == $page_arr['page_size']) {
						break;
					}
				}

				$ofs_arr = $tmp_arr;
			}

			$ofs_list = array();

			foreach ($ofs_arr as $v) {
				$ofs_list[$v['order_sn']]['order_sn'] = $v['order_sn'];
				$ofs_list[$v['order_sn']]['flow_list'][] = array('flow_status' => intval($v['flow_status']), 'addtime' => intval($v['addtime']));
				$ofs_list[$v['order_sn']]['service_type'] = intval($v['service_type']);
				$ofs_list[$v['order_sn']]['canteen_type'] = intval($v['canteen_type']);

				if (intval($v['flow_status']) == 1) {
					$ofs_list[$v['order_sn']]['order_time'] = intval($v['addtime']);
				}
			}

			$ocm_arr = array();
			$ocm_arr = $order_canteen_menu->field('order_sn,menu_id,number,canteen_type')->where($map)->select();
			$canteen_order_list = array();

			if (!empty($ocm_arr)) {
				foreach ($ocm_arr as $k => $v) {
					$ocm_arr[intval($v['menu_id'])] = $v;
					unset($ocm_arr[intval($v['menu_id'])]['menu_id']);
					unset($ocm_arr[$k]);
				}

				$menu_ids = array();
				$menu_ids = array_keys($ocm_arr);
				$cm_arr = array();
				$cm_arr = $canteen_menu->field('id,menu_name')->where(array(
	'id' => array('in', $menu_ids)
	))->select();

				foreach ($cm_arr as $k => $v) {
					$cm_arr[intval($v['id'])] = $v;
					unset($cm_arr[intval($v['id'])]['id']);
					unset($cm_arr[$k]);
				}

				$ocm_arr = array_merge_recursive_new($ocm_arr, $cm_arr);

				foreach ($ocm_arr as $k => $v) {
					$order_sn = $v['order_sn'];
					unset($ocm_arr[$k]['order_sn']);
					$canteen_order_list[$order_sn]['menu_list'][] = $ocm_arr[$k];
				}
			}

			$washing_arr = $order_washing->field('order_sn,car_position,take_time')->where($map)->select();
			$washing_order_list = array();

			if (!empty($washing_arr)) {
				foreach ($washing_arr as $v) {
					$washing_order_list[$v['order_sn']] = $v;
					unset($washing_order_list[$v['order_sn']]['order_sn']);
				}
			}

			if (!empty($canteen_order_list)) {
				$ofs_list = array_merge_recursive_new($ofs_list, $canteen_order_list);
			}

			if (!empty($washing_order_list)) {
				$ofs_list = array_merge_recursive_new($ofs_list, $washing_order_list);
			}

			$ofs_list = array_values($ofs_list);
			$idx_data = array();

			foreach ($ofs_list as $v) {
				$idx_data[intval($v['order_time'])] = $v;
			}

			krsort($idx_data, SORT_NUMERIC);
			$data['msg'] = array_values($idx_data);
		}

		$map = array(
			'times'     => array('between', $dayBegin . ',' . $dayEnd),
			'is_open'   => 1,
			'is_delete' => 0
			);
		$res = $hospital_news->field("id,title,hospital_id,\n\t\tauthor,brief,img_thumb,hits,2 as service_type")->where($map)->select();

		if (!empty($res)) {
			$data['news'] = $res;
		}

		$result['data'] = $data;
		$result['msg_code'] = '9004';
		exit(returnjson($result));
	}

	public function index()
	{
		$this->display('index');
	}

	public function getCouponList()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$type = (empty($data['type']) ? 1 : intval($data['type']));
		$page = (intval($data['page']) <= 0 ? '1' : strval($data['page']));
		$page_size = (intval($data['page_size']) <= 0 ? c('PAGE_SIZE') : strval($data['page_size']));

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		$result = array();
		$user_coupon = m('user_coupon');
		$current_date = strtotime(date('Y-m-d', time()));
		$map_where = array();

		if ($type == 2) {
			$map_where = array(
				'_complex' => array(
					'overdue_date' => array('EGT', $current_date)
					),
				'_logic'   => 'and'
				);
		}
		else if ($type == 3) {
			$map_where = array(
				'_complex' => array('is_use' => 1),
				0          => array(
					'overdue_date' => array('LT', $current_date)
					),
				'_logic'   => 'or'
				);
		}

		$map = array(
			'_complex' => $map_where,
			0          => array('user_id' => $user_id, 'is_use' => $type == 2 ? 0 : array(
	'in',
	array(0, 1)
	), 'dtb_user_coupon.active' => 1, 'dtb_user_coupon.is_delete' => 0),
			'_logic'   => 'and'
			);
		$res = $user_coupon->field('dtb_user_coupon.id,is_use,coupon_name,amount,valid_date,overdue_date,use_type,remark,image')->join(' dtb_coupon dc on dc.id = dtb_user_coupon.coupon_id', 'left')->where($map)->page($page . ',' . $page_size)->order('overdue_date asc, amount desc')->select();
		$count = $user_coupon->join(' dtb_coupon dc on dc.id = dtb_user_coupon.coupon_id', 'left')->where($map)->count();

		if (!empty($res)) {
			foreach ($res as $k => $v) {
				$remain_date = intval((intval($v['overdue_date']) - $current_date) / (24 * 3600));
				$res[$k]['remain_date'] = $remain_date;

				if (0 == $remain_date) {
					$res[$k]['remain_date'] = 1;
				}

				if (($remain_date < 0) || ($v['is_use'] == 1)) {
					$res[$k]['image'] = 'res/coupongray.png';
				}
			}
		}

		$data = array('coupon_info' => empty($res) ? array() : $res, 'page' => $page, 'page_size' => $page_size, 'page_count' => getpaginationtotal($count, $page_size), 'record_count' => $count);
		$data['msg_code'] = '9004';
		exit(returnjson($data));
	}

	public function saveUserMoreService()
	{
		$data = self::get_param();
		$result = array();
		$user_id = intval($data['user_id']);
		$content = strval($data['content']);

		if ($user_id <= 0) {
			$result['msg_code'] = '0055';
			exit(returnjson($result));
		}

		if (empty($content)) {
			$result['msg_code'] = '0213';
			exit(returnjson($result));
		}

		$data = array('user_id' => $user_id, 'content' => $content, 'addtime' => strtotime('now'), 'active' => 1, 'is_delete' => 0);
		$user_more_service = m('user_more_service');

		if (!$user_more_service->add($data)) {
			$result['msg_code'] = '0012';
			exit(returnjson($result));
		}

		exit(returnjson($result));
	}

	public function getManualCoupons()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$user_type = intval($data['user_type']);
		$coupon_code = c('NC_WYYHQ');
		$result = array(
			'data' => array()
			);
		$date = strtotime(date('Y-m-d', time()));
		$coupon = m('coupon');
		$map = array('receive_mode' => 2, 'active' => 1, 'is_delete' => 0, 'coupon_code' => $coupon_code, 'overdue_date' => $date);
		$info = $coupon->field("id,coupon_name,amount,valid_date,\n\t\t\toverdue_date,use_type,remark,image,auto_type,auto_value")->where($map)->find();

		if (empty($info)) {
			$result['msg_code'] = '0221';
			exit(returnjson($result));
		}

		if (($info['user_type'] != '2') && ($info['user_type'] != $user_type)) {
			$result['msg_code'] = '0222';
			exit(returnjson($result));
		}

		$auto_type = $info['auto_type'];
		$map = array('dtb_user_main.id' => $user_id, 'dtb_user_main.active' => 1, 'dtb_user_main.is_delete' => 0);

		if ($auto_type == 2) {
			$hospital_tmp = explode('_', $info['auto_value']);
			$map['hospital_id'] = $hospital_tmp[1];
		}
		else if ($auto_type == 3) {
			$municipal = explode('|', $info['auto_value']);
			$province_id = explode('_', $municipal[0]);
			$city_id = explode('_', $municipal[1]);
			$area_id = explode('_', $municipal[2]);
			$map['h.province_id'] = $province_id[1];
			$map['h.city_id'] = $city_id[1];
			$map['h.area_id'] = $area_id[1];
		}

		$user_main = m('user_main')->field('dtb_user_main.id,mobile,real_name,hospital_id')->join('dtb_hospital as h on h.id = dtb_user_main.hospital_id ', 'left')->where($map)->find();

		if (empty($user_main)) {
			$result['msg_code'] = '0222';
			exit(returnjson($result));
		}

		unset($info['auto_type']);
		unset($info['auto_value']);
		$info['image'] = c('COS_FILE_URL') . '/res/images/Prom-ad-01-1.png';
		$prom_ad_image = c('COS_FILE_URL') . '/res/images/Prom-ad-02-2.png';
		$user_coupon = m('user_coupon');
		$map = array('user_id' => $user_id, 'coupon_id' => $info['id'], 'get_time' => $date, 'active' => 1, 'is_delete' => 0);

		if (0 < $user_coupon->where($map)->count()) {
			$info['image'] = $prom_ad_image;
			$result['data'] = array($info);
			$result['msg_code'] = '0223';
			exit(returnjson($result));
		}

		$result['data'] = array($info);
		exit(returnjson($result));
	}

	public function saveManualCoupon()
	{
		$data = self::get_param();
		$user_id = intval($data['user_id']);
		$coupon_code = c('NC_WYYHQ');
		$result = array(
			'data' => array()
			);
		$user_info = $this->hgetallValueFromCache('user_id:' . $user_id);

		if (empty($user_info)) {
			$result['msg_code'] = '0087';
			exit(returnjson($result));
		}

		$coupon = m('coupon');
		$date = strtotime(date('Y-m-d', time()));
		$map = array(
			'receive_mode' => 2,
			'active'       => 1,
			'is_delete'    => 0,
			'user_type'    => array(
				'in',
				array($user_info['user_type'], '2')
				),
			'coupon_code'  => $coupon_code,
			'overdue_date' => $date
			);
		$info = $coupon->field('id,coupon_name,amount,coupon_amount,valid_date,overdue_date,use_type,remark,image')->where($map)->find();

		if (empty($info)) {
			$result['msg_code'] = '0221';
			exit(returnjson($result));
		}

		$user_coupon = m('user_coupon');
		$map = array('coupon_id' => $info['id'], 'get_time' => $date, 'active' => 1, 'is_delete' => 0);
		$amount = $user_coupon->where($map)->count();
		$info['image'] = c('COS_FILE_URL') . '/res/images/Prom-ad-02.png';

		if (intval($info['coupon_amount']) <= $amount) {
			$result['data'] = array($info);
			$result['msg_code'] = '0224';
			exit(returnjson($result));
		}

		$map['user_id'] = $user_id;

		if (0 < $user_coupon->where($map)->count()) {
			$result['data'] = array($info);
			$result['msg_code'] = '0223';
			exit(returnjson($result));
		}

		$data = array('coupon_id' => $info['id'], 'user_id' => $user_id, 'action_type' => 1, 'get_time' => $date, 'is_use' => 0, 'active' => 1, 'is_delete' => 0);
		$user_coupon->add($data);
		$result['data'] = array($info);
		exit(returnjson($result));
	}
}

?>
