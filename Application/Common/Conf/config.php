<?php
/**
 * 系统全局配置文件
 * @author tangzhilin
 * @date 2014-06-16
 */
return array(
    

    /* 应用配置 */
    'MODULE_DENY_LIST' => array('Common','Home','Runtime'), //拒绝浏览器直接访问的模块列表
    'SHOW_PAGE_TRACE' => false,

    /* URL设置 */
    'URL_MODEL' => 1,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
    
	//链接后缀
	'URL_HTML_SUFFIX' => '',

    'URL_CASE_INSENSITIVE' => false,
    // 'TMPL_TEMPLATE_SUFFIX' => 'de.php',
    // 'TMPL_TEMPLATE_SUFFIX' => 'de.php',

	/* 数据库配置 */
    'DB_TYPE'   => 'mysqli',     // 数据库类型
	//'DB_HOST'   => 'localhost',  // 服务器地址
	//'DB_HOST'   => '10.96.227.123',  // 服务器地址
	'DB_HOST'   => '18.162.141.65',  // 服务器地址
// 	'DB_HOST'   => '18.166.62.205',  // 服务器地址
// 	'DB_HOST'   => '127.0.0.1',  // 服务器地址
	'DB_NAME'   => 'wf_db_texaspoker',       // 数据库名
	'DB_USER'   => 'root',       // 用户名
	// 'DB_PWD'    => 'csmm.321',       // 密码
	'DB_PWD'    => 'Qwe123Lkj#@!',       // 密码
	//'DB_PWD'    => 'csmm.123',       // 密码
    'DB_PORT'   => '3306',       // 端口
    'DB_PREFIX' => 'dtb_',       // 数据库表前缀

	/*redis配置*/
	'REDIS_HOST'		    => '127.0.0.1', 		//redis主机
	'REDIS_PORT'			=> '6379',		   		//redis 默认端口号
	'REDIS_EXPIRE_TIME'		=> 7200,			//redis过期时间 
	
	/*mongodb 配置*/
//	'MONGO_HOST' => '127.0.0.1',
//	'MONGO_PORT' => '27017',
//	'MONGO_DB' => 'wifisdk',

	/* 盐值  */
	'salt_code' => 'easy2015hospital',
	/* 载入其他配置文件 */
	'LOAD_EXT_CONFIG' => 'map,code,WxPay,msg,didi',
	
	/* 腾讯云COS */
	//'BUCKETNAME' => 'testeh',
	/* 分页 */
	//'PAGE_SIZE' 	=> '15',  //每页显示数据条数
	/* 腾讯云COS Host Url*/
	//'COS_FILE_URL'	=> 'img.easyhospital.net', //测试
	//'COS_FILE_URL'	=> 'img.easyhospital.com.cn', //正式

    /* 默认路由设置 */
//    'DEFAULT_MODULE'        =>  'Video',  // 默认模块
//    'DEFAULT_CONTROLLER'    =>  'Index',  // 默认控制器名称
//    'DEFAULT_ACTION'        =>  'index',  // 默认操作名称

	//七牛图片域名
	'QINIU' => array(
		'domain' => '7xvbq3.com1.z0.glb.clouddn.com',
		'default_clb_img' => 'default_clb_img.jpg',
		'default_clb_thumb' => 'default_clb_thumb.jpg',
		'default_circle_img' => 'default_circle_img.jpg',
		'default_circle_thumb' => 'default_circle_thumb.jpg',
	),
//	'SERVER_DOMAIN' => 'app.yuepoker.com',
	'SERVER_DOMAIN' => '18.163.119.104',
	'EVENT_REPORT_URL' => 'http://139.196.215.75:4243/',
	'USER_INIT' => array(
		'diamond_num' => 0,
		'user_gold' => 500,
		'card_type' => 2,
		'card_expire' => 2592000,
		'games_max' => 3,
		'clubs_max' => 3,
		'current_games' => 0,
		'current_clubs' => 0,
	),
	
	'CLUB_INFO' => array(
		'member_upper' => array(
			'1' => 40,
			'2' => 60,
			'3' => 80,
			'4' => 100,
			'5' => 120,
			'6' => 150,
			'7' => 200	
		),
		'admin_upper' => array(
			'1' => 1,
			'2' => 2,
			'3' => 3,
			'4' => 4,
			'5' => 5,
			'6' => 6,
			'7' => 7			
		),
		'club_expire_time' => 3600 * 24 * 30,
	),
	
	//钻石与人民币兑换比率
	'EXCH_RATE1' => 0.001,
	
	//游戏币与钻石兑换比率
	'EXCH_RATE2' => 0.1,
	
	//AES秘钥
	'AES_KEY' => '19CYpm7N8Kue7JD8',
//    /* 图片上传相关配置 */
//    'UPLOAD' => array(
//        'maxSize'  => 4*1024*1024,             //上传的文件大小限制 (0-不做限制)
//        'exts'     => 'jpg,gif,png,jpeg',      //允许上传的文件后缀
//        'autoSub'  => true,                    //自动子目录保存文件
//        'subName'  => array('date', 'Ymd'),    //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
//        'rootPath' => './Uploads/',            //保存根路径',
//    ),
//    // 支持最多二级子目录 ./根目录/一级子目录/二级子目录/文件名
//    'UPLOAD_IMG_PATH' => array(
//    	'news' => './Uploads/News/$catid/{}',
//    	'video_upload' => './Uploads/Video/main/{}',
//    	'video_catch' => './Uploads/Video/thumb/{:md5($vid)}', // 使用imagick的save方法，其实没有使用这项
//    	'video_rcmd' => './Uploads/rcmd/$posid/{}',
//    	'member' => './Uploads/Members/$uid/{}',
//    ),
//
//    /* 后台管理员设置 */
//    'USER_ADMINISTRATOR' => 1, //超级管理员用户ID
//
//    /*域名配置*/
//    'DOMAIN' => 'http://zhuiju.demo.fkwork.com',
//
    /* 分页配置 */
    'LIST_ROWS' => 20,
//
//    /* 加载标签库 */
//    'TAGLIB_BUILD_IN' => 'cx,zj,html' ,
//
//    /* 模版常量配置 */
//    'TMPL_PARSE_STRING'  => array(
//      '{CSS_PATH}' => '/Public/Css/',
//    	'{JS_PATH}' => '/Public/Js/',
//    	'{IMG_PATH}' => '/Public/Img/',
//    	'{PLUGIN_PATH}' => '/Public/Plugin/',
//    ),
//    
//    //最大用户缓存
//    //'USER_MAX_CACHE' => 1000,
//    
//    'COOKIE_PATH' => '/',
//    
//    //加密密钥
//    'UC_AUTH_KEY' => '17zhuiju',
//    
//    //错误页面
//    'ERROR_PAGE' => '/404.php',

//     版本号
    'APP_VERSION' => '1.0.2',
);
