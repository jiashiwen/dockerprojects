
# 移动应用 口袋乐居 的接口调用说明


### 接口地址

`http://api.baike.leju.com/App`

### 可传参数

- city : string city_en 城市代码，如果 city 未传值，则城市从 Touch 页端的 cooke B_CITY 中获取；如果传入的城市不属于乐居 city_en 集合，则默认以 bj 为默认城市输出信息
- flush : int 是否刷新缓存，如果设置 flush 参数为当天的时间戳，则强制重新生成缓存。否则使用缓存的数据。接口部分的数据缓存时间为 1 分钟。
### 返回结果
正常情况
```
{
  "cached": false,  // 表示是否使用了缓存
  "cates": [  // 栏目信息
    {
      "name": "新房知识", // 栏目名称
      "url": "http://ld.m.baike.leju.com/cate-sh-1.html"  // 栏目访问入口地址 url
    },
    {
      "name": "二手房知识",
      "url": "http://ld.m.baike.leju.com/cate-sh-2.html"
    },
    {
      "name": "装修知识",
      "url": "http://ld.m.baike.leju.com/cate-sh-116.html"
    },
    {
      "name": "ufoguan",
      "url": "http://ld.m.baike.leju.com/cate-sh-121.html"
    }
  ],
  "focus": {  // 焦点图信息 仅返回最新的 1 条
    "title": "贷款流程 北京购房者平均年龄不断攀升已从30岁推延到33岁", // 焦点图标题
    "cover": "http://src.leju.com/imp/imp/deal/b0/ce/f/a7f6bd9b1cfd5a4a0be7c30a262_p58_mk61_sX0_rt0_c540X405X130X51_cm690X264.jpg", // 焦点图图片地址 url
    "cate": { // 焦点信息所在栏目
      "name": "买房", // 栏目名称
      "url": "http://ld.m.baike.leju.com/cate-sh-64.html" // 栏目访问地址 url
    },
    "tags": [ // 焦点信息设置的标签
      {
        "tag": "北京",  // 标签名称
        "link": "http://ld.m.baike.leju.com/agg-sh-北京.html" // 此标签聚合列表页访问地址 url
      },
      {
        "tag": "房价",
        "link": "http://ld.m.baike.leju.com/agg-sh-房价.html"
      }
    ],
    "url": "http://ld.m.baike.leju.com/show-420604.html"  // 此焦点信息的访问地址 url
  },
  "list": [ // 栏目信息列表 一并返回 14 条，由 App 端控制信息流输出 4,5,5 方式分页
    {
      "title": "了解长沙购房政策  轻松开启买房之旅", // 信息标题
      "cover": "http://src.leju.com/imp/imp/deal/03/60/9/0c9591c7d3fe87c12ad716b3cce_p58_mk61_sX0_rt0_c428X321X0X0_cm222X166.gif", // 信息题图
      "cate": {
        "name": "准备买房", // 信息栏目名称
        "url": "http://ld.m.baike.leju.com/cate-sh-5.html"
      },
      "tags": [ // 信息设置的标签
        {
          "tag": "限购",
          "link": "http://ld.m.baike.leju.com/agg-sh-限购.html"
        },
        {
          "tag": "政策",
          "link": "http://ld.m.baike.leju.com/agg-sh-政策.html"
        },
        {
          "tag": "买房",
          "link": "http://ld.m.baike.leju.com/agg-sh-买房.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-60.html" // 信息访问地址 url
    },
    {
      "title": "标签 远洋 家居知识",
      "cover": "http://src.leju.com/imp/imp/deal/a0/f9/f/cf5b244a2413a0862df963a014a_p58_mk61_sX0_rt0_c540X405X130X51_cm222X166.jpg",
      "cate": {
        "name": "主材",
        "url": "http://ld.m.baike.leju.com/cate-sh-41.html"
      },
      "tags": [
        {
          "tag": "远洋",
          "link": "http://ld.m.baike.leju.com/agg-sh-远洋.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-420618.html"
    },
    {
      "title": "标签聚合 楼市 房产 恒大 远洋山水 地产 家居知识",
      "cover": "http://src.leju.com/imp/imp/deal/b5/03/2/ad59b1e612c127f52c60c207d90_p58_mk61_sX0_rt0_c192X144X24X18_cm222X166.jpg",
      "cate": {
        "name": "设计",
        "url": "http://ld.m.baike.leju.com/cate-sh-37.html"
      },
      "tags": [
        {
          "tag": "楼市",
          "link": "http://ld.m.baike.leju.com/agg-sh-楼市.html"
        },
        {
          "tag": "房产",
          "link": "http://ld.m.baike.leju.com/agg-sh-房产.html"
        },
        {
          "tag": "恒大",
          "link": "http://ld.m.baike.leju.com/agg-sh-恒大.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-420615.html"
    },
    {
      "title": "双方审核 北京二手房成交占比首次超8成 新房房源减少 定时",
      "cover": "http://src.leju.com/imp/imp/deal/b6/1f/d/30e88e5d7701dc4e57b21a86d43_p58_mk61_sX0_rt0_c192X144X24X18_cm222X166.jpg",
      "cate": {
        "name": "买房",
        "url": "http://ld.m.baike.leju.com/cate-sh-64.html"
      },
      "tags": [
        {
          "tag": "二手房",
          "link": "http://ld.m.baike.leju.com/agg-sh-二手房.html"
        },
        {
          "tag": "土地供应",
          "link": "http://ld.m.baike.leju.com/agg-sh-土地供应.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-420602.html"
    },
    {
      "title": "入住 1万亿住房维修资金难言投资 入市遥遥无期",
      "cover": "http://src.leju.com/imp/imp/deal/ea/57/1/28717f0521ceb812353ad7ef3ef_p58_mk61_sX0_rt0_c603X452X99X56_cm222X166.jpg",
      "cate": {
        "name": "买房",
        "url": "http://ld.m.baike.leju.com/cate-sh-64.html"
      },
      "tags": [
        {
          "tag": "资金",
          "link": "http://ld.m.baike.leju.com/agg-sh-资金.html"
        },
        {
          "tag": "投资",
          "link": "http://ld.m.baike.leju.com/agg-sh-投资.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-420606.html"
    },
    {
      "title": "贷款流程 北京购房者平均年龄不断攀升已从30岁推延到33岁",
      "cover": "http://src.leju.com/imp/imp/deal/b0/ce/f/a7f6bd9b1cfd5a4a0be7c30a262_p58_mk61_sX0_rt0_c540X405X130X51_cm222X166.jpg",
      "cate": {
        "name": "买房",
        "url": "http://ld.m.baike.leju.com/cate-sh-64.html"
      },
      "tags": [
        {
          "tag": "北京",
          "link": "http://ld.m.baike.leju.com/agg-sh-北京.html"
        },
        {
          "tag": "房价",
          "link": "http://ld.m.baike.leju.com/agg-sh-房价.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-420604.html"
    },
    {
      "title": "准备买房 全国楼市成交量将继续下滑 房价下调或将蔓延更多城市",
      "cover": "http://src.leju.com/imp/imp/deal/df/0f/f/94ce3271cd6e0de59b391a64cd7_p58_mk61_sX0_rt0_c234X176X29X22_cm222X166.gif",
      "cate": {
        "name": "买房",
        "url": "http://ld.m.baike.leju.com/cate-sh-64.html"
      },
      "tags": [
        {
          "tag": "楼市政策",
          "link": "http://ld.m.baike.leju.com/agg-sh-楼市政策.html"
        },
        {
          "tag": "房价",
          "link": "http://ld.m.baike.leju.com/agg-sh-房价.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-420601.html"
    },
    {
      "title": "测试推荐002",
      "cover": "http://src.leju.com/imp/imp/deal/08/53/3/db384193e2f9602524db2ab0690_p58_mk61_sX0_rt0_c320X240X40X30_cm222X166.jpg",
      "cate": {
        "name": "交房事项",
        "url": "http://ld.m.baike.leju.com/cate-sh-26.html"
      },
      "tags": [
        {
          "tag": "新浪房产\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t",
          "link": "http://ld.m.baike.leju.com/agg-sh-新浪房产\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-74.html"
    },
    {
      "title": "再接再厉",
      "cover": "http://src.leju.com/imp/imp/deal/55/b3/9/d23b1d74a018d92adf4bdc9e9ec_p58_mk61_sX0_rt0_c819X614X102X77_cm222X166.jpg",
      "cate": {
        "name": "交房事项",
        "url": "http://ld.m.baike.leju.com/cate-sh-26.html"
      },
      "tags": [
        {
          "tag": "房产\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t",
          "link": "http://ld.m.baike.leju.com/agg-sh-房产\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t\n\t\t\t\t\t\t删除\n\t\t\t.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-40.html"
    },
    {
      "title": "新一代的后台编辑工具123456",
      "cover": "",
      "cate": {
        "name": "准备买房",
        "url": "http://ld.m.baike.leju.com/cate-sh-5.html"
      },
      "tags": [
        {
          "tag": "房产",
          "link": "http://ld.m.baike.leju.com/agg-sh-房产.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-368953.html"
    },
    {
      "title": "韩国小萝莉晒豪宅 11图呈现完美现代别墅(图)(5)",
      "cover": "",
      "cate": {
        "name": "准备买房",
        "url": "http://ld.m.baike.leju.com/cate-sh-5.html"
      },
      "tags": [
        {
          "tag": "家居",
          "link": "http://ld.m.baike.leju.com/agg-sh-家居.html"
        },
        {
          "tag": "设计",
          "link": "http://ld.m.baike.leju.com/agg-sh-设计.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-368947.html"
    },
    {
      "title": "拼音分词器研究23",
      "cover": "http://src.leju.com/imp/imp/deal/f0/d5/c/7a7a0a80dc7e0daf699cf235ab3_p58_mk61_sX0_rt0_c419X315X93X0_cm222X166.jpg",
      "cate": {
        "name": "面积户型",
        "url": "http://ld.m.baike.leju.com/cate-sh-13.html"
      },
      "tags": [
        {
          "tag": "房产",
          "link": "http://ld.m.baike.leju.com/agg-sh-房产.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-98.html"
    },
    {
      "title": " 我是购房者 我是卖房者",
      "cover": "http://src.leju.com/imp/imp/deal/63/19/f/bc5beea06e4932a1bdcc890beb0_p58_mk61_sX0_rt0_c320X240X40X30_cm222X166.jpg",
      "cate": {
        "name": false,
        "url": "http://ld.m.baike.leju.com/cate-sh-4.html"
      },
      "tags": [
        {
          "tag": "房地产",
          "link": "http://ld.m.baike.leju.com/agg-sh-房地产.html"
        },
        {
          "tag": "设计",
          "link": "http://ld.m.baike.leju.com/agg-sh-设计.html"
        },
        {
          "tag": "市场",
          "link": "http://ld.m.baike.leju.com/agg-sh-市场.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-123.html"
    },
    {
      "title": "测试00000000008",
      "cover": "http://src.leju.com/imp/imp/deal/6d/ae/4/7556c059087465df152bfa45ae4_p58_mk61_sX0_rt0_c320X240X40X30_cm222X166.jpg",
      "cate": {
        "name": "交房事项",
        "url": "http://ld.m.baike.leju.com/cate-sh-26.html"
      },
      "tags": [
        {
          "tag": "新浪房产",
          "link": "http://ld.m.baike.leju.com/agg-sh-新浪房产.html"
        }
      ],
      "url": "http://ld.m.baike.leju.com/show-73.html"
    }
  ],
  "status": true,
  "city": {
    "l": "S",
    "en": "sh",
    "cn": "上海",
    "py": "shanghai",
    "code": "sh"
  }
}
```

