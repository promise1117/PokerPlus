<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
namespace Index\Controller;

class GzhController extends \Home\Controller\BaseController
{
	static public function get_param()
	{
		$data = i('data', '', 'trim');
		$sign = i('sign', '', 'trim');
		return checksign($data, $sign);
	}

	public function loading()
	{
		$this->display('loading');
	}
}

?>
