<?php

/**
 * 系统全局配置文件
 * 区分测试环境与生产环境
 * @author chenwang
 * @date 2015-11-05
 */
	$uname = php_uname('n');

	if ( $uname == 'VM_58_64_centos' ) {
		//正式环境	
		return 	array(

			    'DB_TYPE'   => 'mysqli',     // 数据库类型
				/* 数据库配置 */
				'DB_HOST'   => '10.105.40.0',  // 正式服务器地址
				'DB_NAME'   => 'easy_hospital',       // 数据库名
				'DB_USER'   => 'root',       // 用户名
				'DB_PWD'    => 'csmm.123',       // 密码
			    'DB_PORT'   => '3306',       // 端口
			    'DB_PREFIX' => 'dtb_',       // 数据库表前缀

				/*redis配置*/
				'REDIS_HOST'		    => '10.247.70.146', 		//正式服务器 redis主机
				'REDIS_PORT'			=> '3838',		   		//redis 默认端口号
				'REDIS_EXPIRE_TIME'		=> 7200,			//redis过期时间 
				'REDIS_AUTH'			=> 'hospital.deploy',	//AUTH认证密码

				//零时使用地址
				// 'REDIS_HOST'		    => '10.237.168.177', 		//测试服务器 redis主机
				// 'REDIS_PORT'			=> '3839',		   		//redis 默认端口号
				// 'REDIS_EXPIRE_TIME'		=> 	7200,			//redis过期时间 
				// 'REDIS_AUTH'			=>	'eh.123',	//AUTH认证密码
				
				/* 正式 腾讯云COS */
				'BUCKETNAME' => 'eh',
				
				/* 分页 */
				'PAGE_SIZE' 	=> '15',  //每页显示数据条数
				/* 腾讯云COS Host Url*/
				'COS_FILE_URL'	=> 'http://img.easyhospital.cn', //正式
				
				/***注册送大礼包配置相关**/
				//注册送大礼包 标识
				'PACKAGE_CODE'	=> '5632e5163a55c',
				//送礼包开关 true 注册送礼包， false 注册不送礼包
				'SEND_PACKAGE_SWITCH'	=> true,
				//内侧期间 5元优惠券
				'NC_WYYHQ'	=> 'NC_WYYHQ',
				//取消订单 正式环境
				'RETURN_FLOWERNUMAND_COUPON_URL' => 'http://nr.easyhospital.cn:8090/order/order_post',

			);


	}else {  //if( $uname == 'VM_168_177_centos' )
		//测试环境
		return array(

			    'DB_TYPE'   => 'mysqli',     // 数据库类型
				/* 数据库配置 */
				'DB_HOST'   => '182.254.208.72',  // 测试服务器地址
				'DB_NAME'   => 'easy_hospital',       // 数据库名
				'DB_USER'   => 'root',       // 用户名
				'DB_PWD'    => 'isd!@#mysql',       // 密码
			    'DB_PORT'   => '3306',       // 端口
			    'DB_PREFIX' => 'dtb_',       // 数据库表前缀

				/*redis配置*/
				'REDIS_HOST'		    => '182.254.208.72', 		//测试服务器 redis主机
				'REDIS_PORT'			=> '3838',		   		//redis 默认端口号
				'REDIS_EXPIRE_TIME'		=> 	7200,			//redis过期时间 
				'REDIS_AUTH'			=>	'eh.123',	//AUTH认证密码

				
				/* 测试 腾讯云COS */
				'BUCKETNAME' => 'testeh',
				
				/* 分页 */
				'PAGE_SIZE' 	=> '15',  //每页显示数据条数
				/* 腾讯云COS Host Url*/
				'COS_FILE_URL'	=> 'http://img.easyhospital.net', //测试
				
				/***注册送大礼包配置相关**/
				//注册送大礼包 标识
				'PACKAGE_CODE'	=> '5632e5163a55c',
				//送礼包开关 true 注册送礼包， false 注册不送礼包
				'SEND_PACKAGE_SWITCH'	=> true,
				//内侧期间 5元优惠券
				'NC_WYYHQ'	=> 'NC_WYYHQ',
				//取消订单 测试环境
				'RETURN_FLOWERNUMAND_COUPON_URL' => 'http://nr.easyhospital.net:8090/order/order_post',

			);
	}

