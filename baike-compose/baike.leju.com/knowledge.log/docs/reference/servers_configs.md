# 服务配置

## 开发和内网测试环境

### 缓存配置
fastcgi_param			REDIS_HOST			"10.204.12.34";
fastcgi_param			REDIS_PORT			"6379";
fastcgi_param			REDIS_DB			"2";
fastcgi_param			REDIS_AUTH			"2EDI5R3d1S";

### 数据库配置
fastcgi_param			DB_HOST				"10.204.12.34";
fastcgi_param			DB_PORT				"3306";
fastcgi_param			DB_NAME				"knowledge";
fastcgi_param			DB_USER				"root";
fastcgi_param			DB_PASS				"123456";

### 服务调用
#### 知识问答 ( 开发和内网测试环境 )
fastcgi_param			JAVA_SEARCH_SERVICE			"10.204.12.34:9999";
fastcgi_param			JAVA_SEARCH_USER			"dev";
fastcgi_param			JAVA_SEARCH_PASS			"123456";
#### 百科词条服务接口 ( 开发和内网测试环境 )
fastcgi_param			WIKI_SERVICE				"10.207.2.24:8080";

### 知识问答服务的用户配置

user : dev
pass : 123456


----

## 正式生产环境

### 缓存配置
fastcgi_param			REDIS_HOST			"10.204.12.29";
fastcgi_param			REDIS_PORT			"6381";
fastcgi_param			REDIS_DB			"2";
fastcgi_param			REDIS_AUTH			"2EDI5R3d1S";

### 数据库配置
fastcgi_param			DB_HOST				"10.204.12.29";
fastcgi_param			DB_PORT				"3308";
fastcgi_param			DB_NAME				"knowledge";
fastcgi_param			DB_USER				"root";
fastcgi_param			DB_PASS				"123456";


### 服务调用
#### 知识问答 ( 生产环境 正式 )
fastcgi_param			JAVA_SEARCH_SERVICE			"10.204.2.21:8100";
fastcgi_param			JAVA_SEARCH_USER			"baike";
fastcgi_param			JAVA_SEARCH_PASS			"4vLE4wwy6Otcoup9Bc";
#### 百科词条服务接口 ( 生产环境 正式 )
fastcgi_param			WIKI_SERVICE				"10.204.2.21:8080";


### 知识问答服务的用户配置

user : baike
pass : 4vLE4wwy6Otcoup9Bc


----

## 周边公用服务

@ Common/Conf/config.php
// 乐居图库服务密钥
'PHOTOLIB' => array(
	'PKEY' => 'd874a2b11f1a3e436df9369fe8412e0f',
	'MKEY' => '462a76239321e4f12606d7cea81918c8',
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
	'HOUSE' => array(
		'api'	=> 'http://info.leju.com/search/default/index',
		'key'	=> '8d6c1ff07d1e74d24ae7b4fb27a8ed86',
		'appid'	=> '2016102823',
		'type'	=> 'house',
		'DOMAIN' => 'http://house.leju.com',
		'M_DOMAIN' => 'http://m.leju.com',
	),
	'TAGS' => array(
		'api'	=> 'http://info.leju.com/search/default/index',
		'key'	=> 'd943f2450814a06d24d329ba8799d0ed',
		'appid'	=> '2016101744',
		'type'	=> 'news_tags',
	),
),
