# 业务页面的异步请求接口

> 以下接口由 PHP 开发工程师开发、维护




### 1. 搜索关键词联想接口

>	页面搜索关键词时，逐字联想词条的数据接口

- 应用业务 : 知识系统 - 百科词条 (前台)
- 接口负责人 : 张晓辉
- 接口url : http://dev.m.baike.leju.com/tag/suggest/
- 调用样式 : http://dev.m.baike.leju.com/tag/suggest/{$word}/{$pagesize}/
- 请求方式 : GET
- 参数说明 : 
	- word 搜索的关键字
	- pagesize 指定返回的相关联想词数据集合的词条数，不传时使用默认值，默认每页 5 条数据
- 返回结果结构 : 
```
{
	"status": true, //成功返回true 失败返回false
	"msg":'', // 当接口请求失败时返回此字段，用于描述错误信息
	"api": "suggest",
	"list": [{
		"id": 词条id，
		"url": "http://ld.m.baike.leju.com/tag/show?id=2",//词条介绍页地址
		 "entry": "恒大地产" //词条名称
		},
		{
			"id":词条id，
			"url":"http://ld.m.baike.leju.com/tag/show?id=2",
			"entry": "恒大公园"
		},
		{
			"id":词条id，
			"url":"http://ld.m.baike.leju.com/tag/show?id=2",
			"entry": "恒大开发"
		}
			], //返回的联想词数据
	"pager": {
		"keyword": "dd",
		"pagesize": 5
	 } // 返回请求的参数
}
```

### 2. 搜索接口?

>	搜索未找到相关词条时，搜索的内容中带有相关词条关键字的接口

- 应用业务 : 知识系统 - 百科词条 (前台)
- 接口负责人 : 张晓辉
- 接口url : http://dev.m.baike.leju.com/tag/result/
- 调用样式 : http://dev.m.baike.leju.com/tag/result/{$word}/{$pagesize}
- 请求方式 : GET
- 参数说明 : 
	- word=搜索的关键字
	- page=页数
- 返回结果结构
```
{
	"status": true,//成功返回true 失败返回false
	"msg":'',// 当接口请求失败时返回此字段，用于描述错误信息
	"api": "search",
	"list": [{
			"id":词条id，
			"url":"http://ld.m.baike.leju.com/tag/show?id=2",//词条介绍页地址
			"entry": "恒大地产",//词条名称
			"content":"匹配到的内容"
		},
		{
			"id":词条id，
			"url":"http://ld.m.baike.leju.com/tag/show?id=2",
			"entry": "恒大公园",
			"content":"匹配到的内容"
		},
		{
			"id":词条id，
			"url":"http://ld.m.baike.leju.com/tag/show?id=2",
			"entry": "恒大开发",
			"content":"匹配到的内容"
		}
	], //返回内容中有此关键字的数据
	"pager": {
		"page": 1,
		"pagesize": 10,
		"keyword": "ddd",
		"total": null,
		"pagecount": 0
	} //请求的参数
}
```

### 3. 搜索请求页面

>	搜索按回车之后跳转的页面

- 应用业务 : 知识系统 - 百科词条 (前台)
- 接口负责人 : 张晓辉
- 请求入口 : http://dev.m.baike.leju.com/tag/search/{$word}
- 请求方式 : `GET`
- 参数说明 : word=搜索的关键字

知识系统

### 1. 分类接口

- 应用业务 : 知识系统 - 知识系统 (前台)
- 接口负责人 : 李红旺
- 测试地址 : http://dev.m.baike.leju.com/baike/cate-loadmore
- 请求方式 : `GET`
- 参数说明:
	- city 城市编码
	- cateid 分类ID
	- page 页码
	- pagesize 每页条数
- 返回类型 : JSON
- 错误代码参考表 :
	- 1 数据为空
	- 2 参数错误
	- 3 请求成功
- 返回结果结构:
```
	{
		"status": true,//成功返回true 失败返回false
		"msg":'',// 当接口请求失败时返回此字段，用于描述错误信息
		"api": "suggest",
		"list": [{
			}
		],//返回的联想词数据
		"total": <int>, // 数据总数
		"maxpage": <int> // 最大分页数
	}
```


### 2. 联想词接口

- 应用业务 : 知识系统 - 知识系统 (前台)
- 接口负责人 : 李红旺
- 测试地址 : http://dev.m.baike.leju.com/baike/search-suggest
- 请求方式 : `GET`
- 参数说明 :
	- city 城市编码
	- keyword 关键字
	- pagesize 条数
- 返回类型 : JSON
- 返回结果结构 :
```
{
	"status": <bool>, // false|true 失败|成功
	"list": [
		{},
	]
}
```

### 3. 搜索查询结果页面

- 应用业务 : 知识系统 - 知识系统 (前台)
- 接口负责人 : 李红旺
- 接口地址 : http://dev.m.baike.leju.com/baike/search-result
- 调用样式 : http://dev.m.baike.leju.com/baike/search-result
- 请求方式 : GET
- 参数说明 :
	- city 城市编码
	- keyword 关键字
	- page 页码
- 返回类型 : JSON
- 返回数据:
```
{
	"status": <bool>, // false|true 失败|成功
	"list": [
		{},
	],
	"pager": {
		// ? 搜索结果的分页信息
	}
}
```
