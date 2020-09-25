<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class GameController extends \Home\Controller\BaseController
{
	static private $face_symbol = array('2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A');
	static private $suit_symbols = array('♠', '♥', '♣', '♦');

	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	static public function convert_cardno_tostring($data)
	{
		if (strpos($data, ',') !== false) {
			$card_nos = explode(',', $data);
			$result = array();

			foreach ($card_nos as $v) {
				$card = '';
				$i = floor($v / 13);
				$card .= self::$suit_symbols[$i];
				$j = $v % 13;
				$card .= self::$face_symbol[$j];
				$result[] = $card;
			}

			return implode(' ', $result);
		}

		$result = '';
		$i = floor($data / 13);
		$result .= self::$suit_symbols[$i];
		$j = $data % 13;
		$result .= self::$face_symbol[$j];
		return $result;
	}

	public function showError()
	{
		$model_card_record = m('card_record');
		$model_cardrecord_error = m('cardrecord_error');
		$count = $model_cardrecord_error->count();
		$max_round = $model_cardrecord_error->max('record_id');
		$Page = getpage($count, 10);
		$show = $Page->show();
		$result = $model_cardrecord_error->field('record_id')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$record_ids = array_column($result, 'record_id');
		$map = array(
			'id' => array('in', $record_ids)
			);
		$fields = "id,p1_1,p1_2,p2_1,p2_2,p3_1,p3_2,p4_1,p4_2,p5_1,\r\n\t\t\tp5_2,p6_1,p6_2,p7_1,p7_2,p8_1,p8_2,\r\n\t\t\tp9_1,p9_2,flop,turn,river,winlist,winlist_1";
		$list = $model_card_record->field($fields)->where($map)->select();

		foreach ($list as $k => $v) {
			$list[$k]['p1_1'] = self::convert_cardno_tostring($list[$k]['p1_1']);
			$list[$k]['p1_2'] = self::convert_cardno_tostring($list[$k]['p1_2']);
			$list[$k]['p2_1'] = self::convert_cardno_tostring($list[$k]['p2_1']);
			$list[$k]['p2_2'] = self::convert_cardno_tostring($list[$k]['p2_2']);
			$list[$k]['p3_1'] = self::convert_cardno_tostring($list[$k]['p3_1']);
			$list[$k]['p3_2'] = self::convert_cardno_tostring($list[$k]['p3_2']);
			$list[$k]['p4_1'] = self::convert_cardno_tostring($list[$k]['p4_1']);
			$list[$k]['p4_2'] = self::convert_cardno_tostring($list[$k]['p4_2']);
			$list[$k]['p5_1'] = self::convert_cardno_tostring($list[$k]['p5_1']);
			$list[$k]['p5_2'] = self::convert_cardno_tostring($list[$k]['p5_2']);
			$list[$k]['p6_1'] = self::convert_cardno_tostring($list[$k]['p6_1']);
			$list[$k]['p6_2'] = self::convert_cardno_tostring($list[$k]['p6_2']);
			$list[$k]['p7_1'] = self::convert_cardno_tostring($list[$k]['p7_1']);
			$list[$k]['p7_2'] = self::convert_cardno_tostring($list[$k]['p7_2']);
			$list[$k]['p8_1'] = self::convert_cardno_tostring($list[$k]['p8_1']);
			$list[$k]['p8_2'] = self::convert_cardno_tostring($list[$k]['p8_2']);
			$list[$k]['p9_1'] = self::convert_cardno_tostring($list[$k]['p9_1']);
			$list[$k]['p9_2'] = self::convert_cardno_tostring($list[$k]['p9_2']);
			$list[$k]['flop'] = self::convert_cardno_tostring($list[$k]['flop']);
			$list[$k]['turn'] = self::convert_cardno_tostring($list[$k]['turn']);
			$list[$k]['river'] = self::convert_cardno_tostring($list[$k]['river']);
		}

		$run_info = array('cnt_error' => $count, 'cnt_round' => $max_round, 'chance' => round((($count * 1) / $max_round) * 100, 2));
		$this->assign('run_info', $run_info);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function compaireCard()
	{
		$model_card_record = m('card_record');
		$model_cardrecord_error = m('cardrecord_error');
		$max_round = $model_cardrecord_error->max('record_id');

		for ($i = $max_round; $i < 15000000; $i++) {
			$map = array('id' => $i + 1);
			$fields = 'winlist,winlist_1';
			$result = $model_card_record->where($map)->field($fields)->find();
			$res = $model_cardrecord_error->where(array('record_id' => $i + 1))->find();

			if (!empty($res)) {
				continue;
			}

			if (empty($result['winlist_1'])) {
				exit();
			}

			if (($result['winlist'] !== $result['winlist_1']) || (intval($result['winlist']) !== intval($result['winlist_1']))) {
				if (strlen($result['winlist']) !== strlen($result['winlist_1'])) {
					$data = array('record_id' => $i + 1);
					$record_id = $model_cardrecord_error->add($data);

					if (empty($record_id)) {
						$log = '写入数据库失败, 写入的行id为：' + strval($i + 1);
						\Think\Log::write($log);
					}
				}
				else {
					$winlist = explode(',', $result['winlist']);
					$winlist_1 = explode(',', $result['winlist_1']);
					sort($winlist);
					sort($winlist_1);
					$winlist_str = implode(',', $winlist);
					$winlist1_str = implode(',', $winlist_1);

					if ($winlist_str !== $winlist1_str) {
						$data = array('record_id' => $i + 1);
						$record_id = $model_cardrecord_error->add($data);

						if (empty($record_id)) {
							$log = '写入数据库失败, 写入的行id为：' + strval($i + 1);
							\Think\Log::write($log);
						}
					}
				}
			}

			file_put_contents('index.log', 'count:' . strval($i + 1) . "\n");
		}
	}

	public function addGame()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$game_type = intval($data['game_type']);
		$game_name = trim(strval($data['game_name']));
		$max_player = intval($data['max_player']);
		$small_blind = intval($data['small_blind']);
		$big_blind = intval($data['big_blind']);
		$into_chips = intval($data['into_chips']);
		$up_bind_time = intval($data['up_bind_time']);
		$match_type = intval($data['match_type']);
		$ctrl_into = intval($data['ctrl_into']);
		$record_fee = intval($data['record_fee']);
		$playtime_cnt = intval($data['playtime_cnt']);
		$into_times = strval($data['into_times']);
		$creater_id = intval($data['creater_id']);
		$game_club_id = intval($data['game_club_id']);
		if (($game_type != 1) && ($game_type != 2)) {
			$result['msg_code'] = '100127';
			exit(returnjson($result));
		}

		if (16 < ((strlen($game_name) + mb_strlen($game_name, 'utf-8')) / 2)) {
			$result['msg_code'] = '100128';
			exit(returnjson($result));
		}

		if ($max_player < 2) {
			$result['msg_code'] = '100130';
			exit(returnjson($result));
		}

		if (($ctrl_into != 1) && ($ctrl_into != 2)) {
			$result['msg_code'] = '100133';
			exit(returnjson($result));
		}

		if ($record_fee <= 0) {
			$result['msg_code'] = '100134';
			exit(returnjson($result));
		}

		if ($creater_id <= 0) {
			$result['msg_code'] = '100136';
			exit(returnjson($result));
		}

		if ($game_type == 1) {
			$data = array('game_type' => $game_type, 'game_name' => $game_name, 'max_player' => $max_player, 'small_blind' => $small_blind, 'big_blind' => $big_blind, 'into_chips' => $into_chips, 'ctrl_into' => $ctrl_into, 'record_fee' => $record_fee, 'playtime_cnt' => $playtime_cnt, 'into_times' => $into_times, 'creater_id' => $creater_id, 'create_time' => time());

			if (!empty($game_club_id)) {
				$data['game_club_id'] = $game_club_id;
			}
		}

		if ($game_type == 2) {
			$map = array('max_player' => $max_player, 'match_type' => $match_type);
			$res = m('sng_template')->where($map)->find();
			$first_reward = $res['first_reward'];
			$second_reward = $res['second_reward'];
			$third_reward = $res['third_reward'];
			$data = array('game_type' => $game_type, 'game_name' => $game_name, 'max_player' => $max_player, 'join_fee' => $res['join_fee'], 'into_chips' => $res['into_chips'], 'up_bind_time' => $res['up_bind_time'], 'first_reward' => $res['first_reward'], 'second_reward' => $res['second_reward'], 'third_reward' => $res['third_reward'], 'ctrl_into' => $ctrl_into, 'record_fee' => $record_fee, 'creater_id' => $creater_id, 'create_time' => time());

			if (!empty($game_club_id)) {
				$data['game_club_id'] = $game_club_id;
			}
		}

		$res = m('game_record')->add($data);

		if (!empty($res)) {
			$result['msg'] = '牌局创建成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
	}
}

?>
