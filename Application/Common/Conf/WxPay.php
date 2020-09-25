<?php
		return array(
			'WxPayConf_pub' => array(
				'WXAPPID' => 'wxa04204f5a979822d',
				'MCHID' => '1436447302',
				'KEY' => 'Wf888888888888888888888888888888',
				'APPSECRET' => 'e1f2c3ca5be40896445142ed0950f694',
				'JS_API_CALL_URL' => 'http://app.yuepoker.com/User/WxJsAPI/pay',
				'SSLCERT_PATH' => VENDOR_PATH.'WxPayPubHelper/cacert/rsa_private_key.pem',
				'SSLKEY_PATH' => VENDOR_PATH.'WxPayPubHelper/cacert/rsa_private_key.pem',		
				'NOTIFY_URL' => 'http://app.yuepoker.com/User/WxJsAPI/notify',
				'CRUL_TIMEOUT' => 30,
			)
		);