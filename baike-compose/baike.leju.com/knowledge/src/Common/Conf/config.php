<?php
/**
 * 核心公用配置文件
 */
if ( IS_CLI ) {
	require CONF_PATH.'fix.env.vars.php';
} else {
	// Web 模式下使用调试插件
	require CONF_PATH.'debug.plugin.php';
}

$city_mapping_config = CONF_PATH.'city.mapping.php';
$default_dict = CONF_PATH.'default.dicts.php';
$auth_route = CONF_PATH.'auth.route.php';
$md5_dict = CONF_PATH.'md5.dict.php';

$base_config = array(
	// ==== 框架配 ====
		'MULTI_MODULE' => true,
		// 不要随意改动 Module 的大小写，在不同操作系统下会出问题!!!
		'DEFAULT_MODULE' => 'Baike',
		'DEFAULT_CONTROLLER' => 'index',
		'DEFAULT_ACTION' => 'index',
		'MODULE_DENY_LIST' => array('_documents','configs','Common'),
		// 不要随意改动 Module 的大小写，在不同操作系统下会出问题!!!
		'MODULE_ALLOW_LIST' => array('Baike','Tag',/*'Ask'*/),
		// 开启全局伪静态模式
		'URL_ROUTER_ON' => true,
		// 默认输入过滤器
		'DEFAULT_FILTER' => 'trim,strip_tags,stripslashes,htmlspecialchars',
		'SHOW_ERROR_MSG' => APP_DEBUG,
	// ==== 环境配置 ====
		// Redis 配置
		'REDIS' => array(
			'type' => 'redis',
			'persistent' => 1,
			'host' => isset($_SERVER['REDIS_HOST']) ? $_SERVER['REDIS_HOST'] : '127.0.0.1',
			'port' => isset($_SERVER['REDIS_PORT']) ? $_SERVER['REDIS_PORT'] : '6379',
			'auth' => isset($_SERVER['REDIS_AUTH']) ? $_SERVER['REDIS_AUTH'] : false,
			'prefix' => '',
			'dbnum' => isset($_SERVER['REDIS_DB']) ? $_SERVER['REDIS_DB'] : '0',
		),
		// 数据库配置
		'DB_TYPE' => 'mysql', // 数据库类型
		'DB_HOST' => $_SERVER['DB_HOST'], // 服务器地址
		'DB_NAME' => $_SERVER['DB_NAME'], // 数据库名
		'DB_USER' => $_SERVER['DB_USER'], // 用户名
		'DB_PWD' => $_SERVER['DB_PASS'], // 密码
		'DB_PORT' => $_SERVER['DB_PORT'], // 端口
		'DB_PREFIX' => '', // 数据库表前缀
		'DB_CHARSET' => 'utf8', // 字符集
		'DB_DEBUG' => APP_DEBUG, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增

	// ==== 依赖服务配置 ====
		// Search Engine API 定义
		'ENGINE' => array(
			// 通用搜索接口
			'SEARCH_API'	=> 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/search',
			// 高级搜索接口
			'MULTI_API'		=> 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/multi',
			// 获取权限
			'GET_TOKEN'		=> 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/login',
			'AUTH_USER'		=> $_SERVER['JAVA_SEARCH_USER'],	// 获取权限使用的用户
			'AUTH_PASS'		=> $_SERVER['JAVA_SEARCH_PASS'],	// 获取权限使用的密码
			// 分词接口
			'PARSETAGS_API'	=> 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/analyze',
			// 分词接口允许使用的字典集合
			'PARSE_DICTS' => array('dict_tags', 'dict_wiki'),
			// 分词使用，编辑知识和百科时对内容进行标签识别使用
			'PARSETAGS_ID' => 'dict_tags',
			// 分词使用，百科词条的词条集合，在接口服务中为其它正文进行百科词条的识别使用
			'PARSEWORDS_ID' => 'dict_wiki',
			'SUGGEST_API' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/suggest',
			// 获取所有字典词条的接口
			'DICT_GETALL' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/dict/getwordset',
			// 给字典设置所有词条的接口
			'DICT_SETALL' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/dict/setwordset',
			// 给字典添加(追回)词条的接口
			'DICT_APPEND' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/dict/appendword',
			// 给字典添加(追回)词条的接口
			'DICT_REMOVE' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/dict/removeword',
			// 查看词条是否在字典中存在的接口
			'DICT_EXISTS' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/dict/existsword',
			// 统计字典中词条数量的接口
			'DICT_COUNT' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/dict/wordcount',
			// 创建文档
			'CUSTOM_CREATE' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/index/create',
			// 更新文档
			'CUSTOM_UPDATE' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/index/update',
			// 删除文档
			'CUSTOM_REMOVE' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/index/remove',
			// 批量更新文档
			'BATCHES_UPDATE' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/index/updatebyquery',
			// 批量删除文档
			'BATCHES_REMOVE' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/admin/index/removebyquery',
			// 推荐逻辑
			'RECOMMEND_RANDOM' => 'http://'.$_SERVER['JAVA_SEARCH_SERVICE'].'/ch/randomsearch',
		),

		// 敏感分类服务
		'SENSITIVE' => array(
			// 10.204.12.34:9998
			'DETECT' => 'http://'.$_SERVER['SENSITIVE_SERVICE'].'/ch/sensitive/worddetect',
		),

		// 与寰宇的数据交互接口(是1个IP)
		'DATA_TRANSFER_API_URL' => 'http://'.$_SERVER['WIKI_SERVICE'].'/',

		// 百度 SEO 推送接口
		'BAIDU_PUSH' => array(
			'baike' => 'http://data.zz.baidu.com/urls?site=baike.leju.com&token=qNDqC3t6CAZTectq',
			'ask' => 'http://data.zz.baidu.com/urls?site=ask.leju.com&token=qNDqC3t6CAZTectq',
		),

		// 乐居图库服务密钥
		'PHOTOLIB' => array(
			'PKEY' => 'd874a2b11f1a3e436df9369fe8412e0f',
			'MKEY' => '462a76239321e4f12606d7cea81918c8',
		),

		// EMS 邮件通知服务配置
		'EMS' => array(
			'EMAIL_NOTICE' => array(
				'name' => '知识百科邮件通知',
				'type' => 'email',
				'appid' => '2016122073',
				'key' => 'd77e3c079697355d7d20cbc262ef03b0',
			),
			'SMS_NOTICE' => array(
				'name' => '乐居房产百科短信通知',
				'type' => 'sms',
				'appid' => '2017042732',
				'key' => 'cfa7d4e37944b57b3ca2d67f9133aa7f',
			),
		),

		// 新闻池接口配置
		'INFOLIB' => array(
			'NEWS' => array(
				'api'	=> 'http://info.leju.com/search/default/index',
				'key'	=> 'b7da020e5140547a09e1298734105a01',
				'appid'	=> '2016102866',
				'type'	=> 'new_news',
				'M_DOMAIN' => 'http://m.leju.com',
			),
			'JIAJU' => array(
				'api'	=> 'http://info.leju.com/search/default/index',
				'key'	=> 'd24145f26bb9fb38a8367e03f181a86b',
				'appid'	=> '2016112821',
				'type'	=> 'jiaju_new_news',
			),
			'HOUSE' => array(
				'api'	=> 'http://info.leju.com/search/default/index',
				'key'	=> '8d6c1ff07d1e74d24ae7b4fb27a8ed86',
				'appid'	=> '2016102823',
				'type'	=> 'house',
				'DOMAIN' => 'http://house.leju.com',
				'M_DOMAIN' => 'http://m.leju.com',
			),
			'TAG' => array(	// 标签系统
				'api'	=> 'http://info.leju.com/search/default/index',
				'key'	=> '03f3dc086a2c80a483795c29b53daa01',
				'appid'	=> '2017060612',
				'type'	=> 'tags',
			),
			'TAGS' => array(
				'api'	=> 'http://info.leju.com/search/default/index',
				'key'	=> 'd943f2450814a06d24d329ba8799d0ed',
				'appid'	=> '2016101744',
				'type'	=> 'news_tags',
			),
			'WIKI' => array(
				'push'	=> 'http://info.leju.com/accept/accept/index',
				'api'	=> 'http://info.leju.com/search/default/index',
				'key'	=> '05845c4bce10f7e6d3a47554e08328da',
				'appid'	=> '2016120163',
				'type'	=> 'wiki',
			),
		),

		// 评论系统配置
		'LEJUCOMMENTS' => array(
			'key' => 'c60ac31f20c7172ed6e73cb07efa4af9',
			'api' => array(
				'getCommentCount'=>'http://c.leju.com/api/comment/getArchiveCommentCount',
			),
		),
		// 访问统计系统 王学良
		'STAT' => array(
			'key' => 'cdf8101c8230bfdaf62c9fff0224579d',
			'GETRANK' => array(
				'api' => 'http://stat.leju.com/api/data/getrank',
			),
		),
		// 统一登录系统配置
		'ADMINLOGIN' => array(
			'DEVELOPMENT' => array(
				'app_key' => '95d7d0a55d897beec95aa36ac9c1a64e',
				'api_url' => 'http://10.207.0.186',
				'headers' => array('Host:test.admin.house.sina.com.cn'),
			),
			'PRODUCT' => array(
				'app_key' => 'ae09afeec9200bc513892514a6263127',
				'api_url' => 'http://admin.house.sina.com.cn',
				'headers' => array(),
			),
			'APIs' => array(
				'getUser' => '/welcome/getuser',	// 获取用户信息
			),
		),
	// ==== 字典配置 ====
		// 城市中文名与对应的业务城市代码
		'CITIES' => file_exists($city_mapping_config) ? include($city_mapping_config) : array(),
		// 后台使用的状态字典
		'DICT' => file_exists($default_dict) ? include($default_dict) : array(),
		// 权限字典
		'AUTH_ROUTE' => file_exists($auth_route) ? include($auth_route) : array(),
		// MD5转换字典
		// @TODO : add to default dict
		'MD5_DICT' => file_exists($md5_dict) ? include($md5_dict) : array(),

		'RECOMMEND' => array(
			'QA' => array(
				'acts' => array(
					'focus' => array( 'name'=>'首页图片', 'max'=>10, 'flag'=>1, 'method'=>'setRecommend', ),
					'unfocus' => array( 'name'=>'取消首页图片', 'flag'=>1, 'method'=>'delRecommend', ),
				),
			),
		),

	// ===== End
	// 域名
	'DOMAINS' => array(
		'PC' => 'http://baike.leju.com/',
		'TOUCH' => 'http://m.baike.leju.com/',
		'ADMIN' => 'http://admin.baike.leju.com/',
		'API' => 'http://api.baike.leju.com/',
	),

	// 会员缓存时间设置
	'MEMBER_CACHE' => array(
		'DETAIL_EXPIRE' => 3600,	// 用户详细信息缓存 1 小时
		'INFO_EXPIRE' => 86400,		// 用户基本信息缓存 1 天
	),
	// ==== 配置参数结束 ====

);

return $base_config;