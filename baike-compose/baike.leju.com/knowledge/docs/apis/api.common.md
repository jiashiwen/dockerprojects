
# 后台可管理通用接口初始版本


### 接口地址

`http://api.baike.leju.com/Data/api`

### 可传参数

- id : 1	必传，且必须为 1
- token : c5606494c4b7cd98246a2a21c5d9450b 必传且必须为此字符串
- city : city_en 城市代码

- device : {pc|touch} URL生成规则
- debug : 35940 使用此参数用于调试

### 返回结果
正常情况
```
{
  "status": true,
  "list": [
    {
      "title": "在售楼处该看些什么",
      "url": "http://ld.baike.leju.com/show-423085.html"
    },
    {
      "title": "标签聚合 恒大 二手房",
      "url": "http://ld.baike.leju.com/show-420617.html"
    },
    {
      "title": "买房必看宝典 7\"种房一出手就贬值",
      "url": "http://ld.baike.leju.com/show-420590.html"
    }
  ],
  "_prof": {
    "cost": "0.400",
    "mem": "465"
  },
  "home": "http://ld.baike.leju.com/",
  "tags": [
    {
      "tag": "二手房",
      "url": "http://ld.baike.leju.com/agg-bj-二手房-1-1.html"
    },
    {
      "tag": "恒大",
      "url": "http://ld.baike.leju.com/agg-bj-恒大-1-1.html"
    },
    {
      "tag": "买房",
      "url": "http://ld.baike.leju.com/agg-bj-买房-1-1.html"
    },
    {
      "tag": "宝典",
      "url": "http://ld.baike.leju.com/agg-bj-宝典-1-1.html"
    },
    {
      "tag": "贬值",
      "url": "http://ld.baike.leju.com/agg-bj-贬值-1-1.html"
    }
  ]
}
```


异常情况一
```
{
  "status": false,
  "list": [],
  "_prof": {
    "cost": "0.000",
    "mem": "3"
  },
  "pager": [],
  "msg": "没有指定的接口设定!"
}
```

异常情况二
```
{
  "status": false,
  "list": [],
  "_prof": {
    "cost": "0.000",
    "mem": "3"
  },
  "pager": [],
  "msg": "没有权限调用此接口!"
}
```


### 一些调用演示


#### Curl Demo

```shell
curl -X "GET" "http://api.baike.leju.com/Data/api?id=1&token=c5606494c4b7cd98246a2a21c5d9450b1&city=%E5%8C%97%E4%BA%AC" \
     -H "Content-Type: application/x-www-form-urlencoded; charset=utf-8"
```


#### jQuery Demo

```javascript
// Request (GET http://api.baike.leju.com/Data/api)
jQuery.ajax({
    url: "http://ld.api.baike.leju.com/Data/api",
    type: "GET",
    data: {
        "id": "1",
        "token": "c5606494c4b7cd98246a2a21c5d9450b1",
        "city": "北京",
    },
    headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=utf-8",
    },
})
.done(function(data, textStatus, jqXHR) {
    console.log("HTTP Request Succeeded: " + jqXHR.status);
    console.log(data);
})
.fail(function(jqXHR, textStatus, errorThrown) {
    console.log("HTTP Request Failed");
})
.always(function() {
    /* ... */
});
```



#### PHP Demo

```php
<?php
// Get cURL resource
$ch = curl_init();
// Set url
curl_setopt($ch, CURLOPT_URL, 'http://api.baike.leju.com/Data/api?id=1&token=c5606494c4b7cd98246a2a21c5d9450b1&city=%E5%8C%97%E4%BA%AC');
// Set method
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
// Set options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// Set headers
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
 ]
);
// Create body
$body = [
  ];
$body = http_build_query($body);
// Set body
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
// Send the request & save response to $resp
$resp = curl_exec($ch);
if(!$resp) {
  die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
} else {
  echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "\nResponse HTTP Body : " . $resp;
}
// Close request to clear up some resources
curl_close($ch);
```




