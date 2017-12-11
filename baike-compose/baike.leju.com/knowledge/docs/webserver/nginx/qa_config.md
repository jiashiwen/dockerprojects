# Questions Answers 配置参考

nginx 虚拟主机配置
-- fastcgi_params --
```
# 静态资源文件域名配置
fastcgi_param			PS_URL				"leju-knowledge.b0.upaiyun.com";
# ThinkPHP 库配置
fastcgi_param			PATH_THINKPHP		"/Volumes/Data/Dev/vmshare/ubuntu1510/vhosts/knowledge/knowledge/lib";
# 定义基本变量
fastcgi_param			PATH_RUNTIME		"/Volumes/Data/Dev/vmshare/ubuntu1510/vhosts/knowledge/.tmp";

# 缓存配置
fastcgi_param			REDIS_HOST			"10.204.12.34";
fastcgi_param			REDIS_PORT			"6379";
fastcgi_param			REDIS_DB			"2";
fastcgi_param			REDIS_AUTH			"2EDI5R3d1S";
## 如果使用认证密码时，填写 REDIS_AUTH 参数
#fastcgi_param			REDIS_AUTH			"";

# 数据库配置
fastcgi_param			DB_HOST				"10.204.12.34";
fastcgi_param			DB_PORT				"3306";
fastcgi_param			DB_NAME				"knowledge";
fastcgi_param			DB_USER				"root";
fastcgi_param			DB_PASS				"123456";

# ES Server 配置
## 集群形式
#fastcgi_param			ES_SERVERS			"10.204.12.31:9200 10.204.12.32:9200 10.204.12.33:9200";
## 单机形式
fastcgi_param			ES_SERVERS			"10.204.12.31:9200";
```

-- vhosts config --
```
server {
	# qa.leju.com 用于正式环境使用
	# dev.qa.leju.com 用于测试环境使用
	# ld.qa.leju.com 用于本地开发环境使用
	server_name ld.qa.leju.com qa.leju.com dev.qa.leju.com;
	listen 80;

	root /Volumes/Data/Dev/vmshare/ubuntu1510/vhosts/knowledge/qa;

	fastcgi_buffer_size 1024k;
	fastcgi_buffers 32 1024k;
	fastcgi_busy_buffers_size 2048k;
	
	location / {
		index index.php index.html index.htm;
		if (!-e $request_filename) {
			rewrite ^/index.php(.*)$ /index.php?s=$1 last;
			rewrite ^(.*)$ /index.php?s=$1 last;
			break;
		}
	}

	location ~ .+\.php($|/) {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass		127.0.0.1:9000;
		fastcgi_index		index.php;
		fastcgi_param		SCRIPT_FILENAME		$document_root$fastcgi_script_name;
		fastcgi_param		SCRIPT_NAME			$fastcgi_script_name;
		include				fastcgi_params;
		include				/Volumes/Data/Dev/vmshare/ubuntu1510/vhosts/knowledge/etc/fastcgi_params;
	}

	location ~ .*\.(gif|jpg|jpeg|png|bmp|swf|ttf|woff|svg)$ {
		#expires 30d;
		expires off;
	}

	location ~ .*\.(js|css)?$ {
		#expires 12h;
		expires off;
	}
} # end of qa frontend domain : [ qa.leju.com ]
```

