# 业务逻辑接口

## 基本方法

### function pager

1. 模版块定义 根据实际模版样式进行替换

```html
<!-- page bar -->
<div class="row-fluid">
  <div class="span12">
    <div class="pagination pagination-centered">
      <ul>
      <notempty name="pager.first">
        <li><a href="{$pager.first}">首页</a></li>
      </notempty>
      <notempty name="pager.prev">
        <li><a href="{$pager.prev}">上一页</a></li>
      </notempty>
        <if condition="$pager.sp_before eq true"><li class="disabled"><a>{$pager.spline}</a></li></if>
      <foreach name="pager.list" item="vo" key="k" >
      <eq name="pager.page" value="$vo.num">
        <li class="active disabled"><a href="{$vo.url}">{$vo.num}</a></li>
      <else/>
        <li><a href="{$vo.url}">{$vo.num}</a></li>
      </eq>
      </foreach>
        <if condition="$pager.sp_after eq true"><li class="disabled"><a>{$pager.spline}</a></li></if>
      <notempty name="pager.next">
        <li><a href="{$pager.next}">下一页</a></li>
      </notempty>
      <notempty name="pager.last">
        <li><a href="{$pager.last}">尾页</a></li>
      </notempty>
      </ul>
    </div>
  </div>
</div>
```

2. 在页面中引用分页条模版块

```html
<include file="Public:pager" />
```

3. 配置及使用

```php
$list = array();  // 某数据集合
$total = count($list);  // 数据集合的数量总数
$pagesize = 2;  // 每页数据量
$page = 5;  // 当前页码
// 在搜索时使用的参数配置
$linkopts = array(
  'keyword' => '测试',
  'cate' => 3,
);
// 分页配置
$opts = array(
  'first' => false,
  'last' => false,
  'prev' => true,
  'next' => true,
  'number' => 9,  // 分页条中显示的页码列表数量
  'linkstring' => '/search.html?page=#&'.implode('&',$linkopts),  // 配置页码链接参数，#将被替换为对应的页码号
);
$pager = \pager($page, $total, $pagesize, $opts); // 调用分页处理
$this->assign('pager', $pager);
```

### 能用 debug 日志方法

/**
 * 通用调试方法
 * @param $msg string 问题点描述
 * @param $ret array 问题信息列表
 * @param $mode string 调试信息详细模式 lite精简模式 full完整模式
 * @return bool 返回 true
 */
function debug($msg, $ret, $mode);

在需要输出日志调试的点添加
$msg = '调试信息说明文字';
$data = array(); // 要调试输出的数据结构
$mode = 'lite'; // 不传即为默认使用full模式，将会把运行栈一并输出
debug($msg, $data, $mode);
执行代码时，不会在页面输出内容，但会在运行环境的日志文件中记录数据信息，以便于查看调试信息。


----

## 知识和百科词条通用接口

### 内部

#### 标签分析接口

>	在__知识和百科词条编辑页面__中，对文章正文中内容进行乐居新闻池标准标签进行提取的功能接口

```php
echo '<h3>分词测试</h3>', PHP_EOL;
$content = '蓝天苍天好好大地测试一下蓝天苍天大地一下测试一下苍天分词词条1蓝天效果如何蓝天的好好的还是不好';
$engine = D('Search', 'Logic', 'Common');
$result = $engine->analyze($content);
echo var_export($result, true), PHP_EOL, '<hr>', PHP_EOL;
```


#### 乐居标签数据更新接口系列

>	PHP页面不需要使用，此系列接口，用于将新闻池乐居通用标签与业务中的标签同步。

```php
echo '<h3>批量导入标签</h3>', PHP_EOL;
$source = 1;  // 代表从新闻池获取的乐居标签库
$page = 1;
$pagesize = 3000;
$last_createtime = 0;
$recommender = D('Infos', 'Logic', 'Common');
$result = $recommender->getAllTags($page, $pagesize, $last_createtime);
$m = D('Tags', 'Model', 'Common');
// 批量写入数据库
$ret = $m->bulkAdd($result['list'], $source, true);
// 统计数据库中的乐居标签数量
$count = $m->countSource($source);
```

#### 知识列表查询

>	在__知识列表__和__知识搜索页面__中使用

```php
echo '<h3>获取列表</h3>', PHP_EOL;
$engine = D('Search', 'Logic', 'Common');
$result = $engine->select();
echo var_export($result, true), PHP_EOL, '<hr>', PHP_EOL;
```

#### 知识推送到搜索服务

>	仅在知识正常发布时，推送知识内容到搜索服务。

```php
echo '<h3>创建文档</h3>', PHP_EOL;
$engine = D('Search', 'Logic', 'Common');
// 模拟从数据库中获取的一条知识数据
$record = array(
  'id' => 3,
  'cateid' => 111,
  'catepath' => '0-1-11-111',
  'version' => NOW_TIME,
  'status' => 1,
  'title' => '这是一条测试知识文档',
  'content' => '这是一条测试知识文档的内容部分',
  'cover' => '',
  'coverinfo' => '',
  'editorid' => 1,
  'ctime' => NOW_TIME,
  'ptime' => NOW_TIME, // 发布时间
  'utime' => NOW_TIME, // 发布时间
  'scope' => '_', // 城市代码
  'src_type' => 0,
  'src_url' => '',
  'top_time' => 0,
  'top_title' => '',
  'top_cover' => '',
  'top_coverinfo' => '',
  'rcmd_time' => 0,
  'rcmd_title' => '',
  'rcmd_cover' => '',
  'rcmd_coverinfo' => '',
  'tags' => '北京 公积金 新房',
  'rel_news' => array(
    array('id'=>1,'title'=>'新闻1','url'=>'http://baidu.com'),
  ),
  'rel_house' => array(
    array('city'=>'bj', 'hid'=>'1', 'name'=>'某楼盘', 'url'=>'http://data.house.sina.com.cn/bj1'),
  ),
);
// 需要手工补全的部份
$record['title_firstletter'] = 'Z';
$record['title_pinyin'] = 'zheshiyitiaoceshizhishiwendang';
$record['url'] = 'http://ld.m.baike.leju.com/show/?id='.$record['id'];
$doc = $engine->knowledgeDocConvert($record);
$result = $engine->create($record['id'], $doc, 'knowledge');
echo '<pre>', var_export($result, true), '</pre>', PHP_EOL, '<hr>', PHP_EOL;
```


权限设置，为 knowledge 创建权限用户和权限参数
允许访问
knowledge_logic 对应知识索引 使用 knowledge
question_logic 对应问答索引 使用 question
wiki_logic 对应词条百科索引 使用 wiki

接口均使用 POST 提交

添加文档 `ch/admin/index/create`
参数结构不变
index 接收 业务名称，即去掉参数值中的 _logic 字符串
type 可不传，默认即为 index参数的业务名称


删除文档 `ch/admin/index/remove`
参数: 接收 id
效果: 将文档 直接删除

更新文档 `ch/admin/index/`
index 接收 业务名称，即去掉参数值中的 _logic 字符串
type 可不传，默认即为 index参数的业务名称
其它参数结构可不变

已经提开发任务 @常利伟

----

### 外部 - 新闻池封 < 装接口 >

#### 按标签列表获取与标签相关的新闻列表

> 指定一组乐居标签，获取与此乐居标签相关的新闻列表

```php
$tags = array('壹房产','柿子树下','地产与远方','投资');
echo '<h3>读取与标签相关的新闻列表的接口</h3>', PHP_EOL;
$info = D('Infos', 'Logic', 'Common');
$result = $info->relNews($tags, 3);
var_dump($result);
echo '<hr>', PHP_EOL;
```

#### 推荐新闻读取

>	指定一条乐居新闻 `id`，获取新闻的 `标题` 和 `访问地址url`。

```php
// 要读取的新闻编号
$newsid = '6072948702421776591';

// 在控制器中添加下面两行代码
// 如果正常读取到结果，$result 为一个数组
// 如果读取接口失败，$result 为 false
$infos = D('Infos', 'Logic', 'Common');
$result = $infos->getNews($newsid);
```

#### 推荐楼盘读取

>	指定一条乐居楼盘库的编号 `city+hid`，获取楼盘的 `楼盘名称` 和 `访问地址url`。

```php
// 要读取的楼盘编号
$house_id = 'bj136014';
// 或 $house_id = array('city'=>'bj', 'hid'=>'136014');

// 在控制器中添加下面两行代码
// 如果正常读取到结果，$result 为一个数组
// 如果读取接口失败，$result 为 false
$infos = D('Infos', 'Logic', 'Common');
$result = $infos->getHouse($house_id);

# 接口返回数据

```


