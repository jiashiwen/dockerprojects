<?php
/**
 * 用于测试单元逻辑使用
 */
namespace Baike\Controller;

class DemoController extends BaseController {

	public function __construct() {
		parent::__construct();
		// 在 url 中添加 ?dbg=35940 进行调试模式，展示测试用例
		if ( !$this->_debug ) {
			exit();
		}
	}

	public function getEsfExtra () {
		$city = I('get.city', 'gz', 'strtolower,trim');
		$lExtra = D('Extra', 'Logic', 'Common');
		$result = $lExtra->getESF($city);
		print_r($result);
	}

	public function getHouseExtra () {
		$city = I('get.city', 'bj', 'strtolower,trim');
		$result = D('Extra', 'Logic', 'Common')->getHouse($city);
		// $lExtra = D('Extra', 'Logic', 'Common');
		// $result = $lExtra->getHouse($city);
		print_r($result);
	}

	/**
	 * 演示索引
	 */
	public function index() {

		$list = array();
		// 常用方法
		$list[] = array(
			'title' => 'Redis测试 [redis]',
			'url' => U('Baike/Demo/redis'),
		);

		$list[] = array(
			'title' => '获取乐居PC版标准头和标准尾',
			'url' => U('Baike/Demo/pcTpl'),
		);
		// 常用方法
		$list[] = array(
			'title' => '基本信息 [server]',
			'url' => U('Baike/Demo/server'),
		);


		// 向百度主动推送新链接
		$list[] = array(
			'title' => '向百度主动推送新链接 [pushToBaid]',
			'url' => U('Baike/Demo/testPushToBaidu'),
		);
		// 移动端与Web端自动适配检测
		$list[] = array(
			'title' => '移动端与Web端自动适配检测 [testAutofit]',
			'url' => U('Baike/Demo/testAutofit'),
		);


		$list[] = array(
			'title' => '测试分页器 [pager]',
			'url' => U('Baike/Demo/pager'),
		);
		$list[] = array(
			'title' => '浏览器用户城市 [getUserIP]',
			'url' => U('Baike/Demo/getUserIP'),
		);
		$list[] = array(
			'title' => '测试图片剪裁 [changeImage]',
			'url' => U('Baike/Demo/changeImage'),
		);

		$list[] = array(
			'title' => '伪静态路由设置规则 [makeUrl]',
			'url' => U('Baike/Demo/makeUrl'),
		);
		$list[] = array(
			'title' => '重置栏目树缓存 [flushCateTree]',
			'url' => U('Baike/Demo/flushCateTree'),
		);
		// 新闻池部份
		$list[] = array(
			'title' => '测试新闻池接口 [info]',
			'url' => U('Baike/Demo/info'),
		);
		$list[] = array(
			'title' => '用标签获取新闻池数据的演示 [reltags]',
			'url' => U('Baike/Demo/reltags'),
		);
		$list[] = array(
			'title' => '批量导入标签到数据库、suggest索引及分词字典 [syncTags]',
			'url' => U('Baike/Demo/syncTags'),
		);

		// 引擎服务部份
		$list[] = array(
			'title' => '获取登录认证 [login]',
			'url' => U('Baike/Demo/login'),
		);
		$list[] = array(
			'title' => '联想词搜索乐居标签词 [tagSuggest]',
			'url' => U('Baike/Demo/tagSuggest'),
		);
		// @TODO: 更新所有接口的调用
		// $list[] = array(
		// 	'title' => '@TODO: Wiki百科联想词 [wikiSuggest]',
		// 	'url' => U('Baike/Demo/wikiSuggest'),
		// );
		// $list[] = array(
		// 	'title' => '@TODO: 知识标题联想词 [kbSuggest]',
		// 	'url' => U('Baike/Demo/kbSuggest'),
		// );
		$list[] = array(
			'title' => '从正文中分析词条服务演示 [parseWords]',
			'url' => U('Baike/Demo/parseWords'),
		);
		$list[] = array(
			'title' => '新.创建文档 [createDocuments]',
			'url' => U('Baike/Demo/createDocuments'),
		);
		$list[] = array(
			'title' => '新.更新文档 [updateDocuments]',
			'url' => U('Baike/Demo/updateDocuments'),
		);
		$list[] = array(
			'title' => '新.删除文档 [removeDocuments]',
			'url' => U('Baike/Demo/removeDocuments'),
		);
		$list[] = array(
			'title' => '新.批量条件更新文档 [batchUpdate]',
			'url' => U('Baike/Demo/batchUpdate'),
		);
		$list[] = array(
			'title' => '新.批量条件删除文档 [batchRemove]',
			'url' => U('Baike/Demo/batchRemove'),
		);
		$list[] = array(
			'title' => '查询列表使用演示 [select]',
			'url' => U('Baike/Demo/select'),
		);
		$list[] = array(
			'title' => '搜索功能使用演示 [search]',
			'url' => U('Baike/Demo/search'),
		);

		echo '<h1>功能演示索引列表</h1><hr>', PHP_EOL;
		foreach ( $list as $i => &$item ) {
			// $item['url'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].str_replace('/index.php','',$item['url']);
			echo '<li><a href="',$item['url'],'" target="_blank">',$item['title'],'</a></li>',PHP_EOL;
		}
	}

	// 主动向百度推送网站新链接
	public function testPushToBaidu() {
		$urls = array(
			'http://baike.leju.com/show-585845.html',
			// 'http://m.baike.leju.com/show-585845.html',
		);
		$lSearch = D('Search', 'Logic', 'Common');
		$ret = $lSearch->pushToBaidu($urls);
		var_dump($ret);
	}

	// 移动端与Web端自动适配检测
	public function testAutofit() {
		autofit(false);
	}

	public function pushData() {
		$info = D('Infos', 'Logic', 'Common');
		$data = array (
			'id' => 388800,
			'status' => 9,
			'title' => '社科院35城存住房估值过高风险 深圳第一北京第四',
			'content' => '&nbsp;&nbsp; [摘要] 昨日，中国社会科学院财经战略研究院编写的《中国住房发展报告（2016-2017）》发布。报告通过建立中国住房市场风险监测体系测度发现，全国35个大中城市普遍存在估值过高的风险。深圳、厦门、上海、北京、南京、天津、郑州、合肥、石家庄、福州成为住房估值风险最高的10个城市。前9月商品房销量同比增27.14%报告从房价收入指数比、房价租金指数比、住房使用成本等几个维度衡量了35个大中城市住房的估值情况。分析称，估值过高的住房市场有较大概率出现房价增速下滑或房价下跌，其估值指标可能长期向着均值恢复。报告指出，2015-2016年，中国住房市场逐步进入上升的周期，全国商品住房销量快速增长，2016年1-9月份同比增长27.14%，比2015年全年高出了20.27个百分点。报告认为，本轮楼市运行形势集中体现为是市场回暖过程中的局部过热。热点城市房价上涨过速过猛，部分城市的本地及外地投资投机需求旺盛，总量上库存连续减少，总库存比2015年底减少了1.51亿平方米，去库存取得了一定成效。但空间错配持续加剧，一二线城市库存小、销售快，三四线城市库存大、销售慢。明年楼市总体平稳回落报告预测，2017年中国楼市将迎来一个短期调整期，总体将平稳回落，但具有不确定性。空间上，市场调整也将继续呈现差异化。在具体指标上，全国房价整体增幅将收窄，个别月份或将绝对下降，其中，一二线城市中先前房价上涨快的城市的房价增幅回落会更大，三四线城市分化；房地产开发投资将放缓，增幅或将低于2016年；总体库存将会进一步下降，降幅会有所收窄，且城市间分化严重，三四线城市去库存任务仍然艰巨；销售开工方面，2017年销售或将有较大幅度下降，开工面积也将会下降，同时分化还会持续，一二线新开工或将增加。看点1本轮楼市风险仍然可控报告称，当前住房市场风险整体高于2010年，估值过高的住房市场将极有可能出现房价增速放缓甚至是房价下跌的情形。本轮楼市过热主要集中于一线城市与部分二线城市等热点城市，无论是热点城市房价的上涨幅度还是风险的积累程度，均已超过2009-2010时期。当年的统计显示，2009年10月份北京商品住宅的成交均价达到15891元/平方米，比9月份环比上涨了1514元/平方米，涨幅达10.5％；与2008年同期同比上涨了3563元/平方米，涨幅达28.9％。而且在房价快速上涨的同时，当年仍然出现供不应求，许多楼盘开盘即售罄，甚至出现托门子、找关系还买不到房的现象。报告主编、中国社会科学院城市与竞争力研究中心主任倪鹏飞认为，全国楼市总体风险仍然处在可控的范围，主要指标没有超越风险控制线。尽管必须高度关注房贷存量年均31%的超高增长，但中国的总体房贷杠杆率及全民负担能力目前还未超出合理范围。如果考虑到中国收入差距，投资和投机中多是高净值人群，这个风险应该更低一些。另外，本轮过热是局部不是全局性的。主要集中在一、二线城市和大都市周边区域三、四线城市。11月29日，万达集团董事长王健林在印尼接受外媒采访时也表示，中国的房地产有泡沫，但不会崩盘。看点2应引导楼市“软着陆”报告指出，基于当前房地产市场与宏观经济环境及未来走势，房地产调控的目标：总体上引导市场实现温和调整，迫使一、二线城市楼市降温实现“软着陆”，促进三、四线及以下城市继续去库存。除了完善后续补调准备，报告还提到，要完善“分城施策”、“协同作战”。因为在房价合理增长条件下，一、二线城市楼市对三、四线城市具有风向标意义，能带动三、四线及以下城市去库存。但在过快增长状态下，两者存在零和关系，一、二线城市价格高涨将导致三、四线需求向一、二线转移，从而增加其城市的空置和库存。中国社会科学院金融所国际经济与金融研究室副主任蔡真认为，防风险并非“保房价”，防风险情况下，对房市要做的是使波动的水变成平静的水，导向实体经济，金融支持供给性结构性改革，让它找到新兴行业有回报的地方，才能改回来。他建议，采取资本项目管制，让房产资金导向实体经济。',
			'cover' => 'http://src.leju.com/imp/imp/deal/e1/da/0/654f006ee47f3336c2cc2d8bab2_p58_mk61.jpg',
			'tags' => '房价 回落 金融 一二线城市',
			'city' => 'bj',	// 全国使用 all
			'editor' => '王永亮',
			'editorid' => 84,
			'createtime' => 1480588056,	// 使用 ctime
			'updatetime' => 1480588056,	// 使用 version
			'news' => array (),
			'house' => array (),
			'catepath' => '0-1-5-6',
			'cateid' => 6,
		);
		$ret = $info->pushNewsPool($data);
		var_dump($ret);


		$opts = array('{id@eq}388800');
		$result = $info->selectNews('knowledge', 1, 1, $opts);
		var_dump($result);
	}

	public function pcTpl() {
		$d = D('Front', 'Logic', 'Common');
		$data = $d->getPCPublicTemplate();
		var_dump($data);
	}

	public function getUserIP() {
		$ip = get_client_ip();
		$city = getIPLocation($ip);
		$result = array(
			'ip' => $ip,
			'city' => $city,
		);
		$this->_display($result, '获取浏览器用户地理位置');
	}


	/**
	 * 重置栏目树缓存
	 */
	public function flushCateTree() {
		$type = I('get.t', 'kb', 'trim,strtolower');
		$types = array('kb', 'qa');
		if ( !in_array($type, $types) ) {
			die('类型不符合要求');
		}
		$d = D('Cate', 'Logic', 'Common');
		$d->init(1, $type);
		$l = $d->toTree(0, $type);
		$this->_display($l, '栏目树');
	}

	/**
	 * 服务器基本信息
	 */
	public function server() {
		$info = array(
			'server' => $_SERVER,
			'env' => $_ENV,
			'cookie' => $_COOKIE,
		);

		$this->_display($info, '服务器基本信息');
	}

	/**
	 * 业务入口 伪静态 url 地址拼装
	 */
	public function makeUrl() {
		// $page='index', $opts=array(), $type='touch', $mod='baike'
		$l = D('Url', 'Logic', 'Common');
		$result = array(
			'mode'=>$l->getMode(),
			'descript'=>$l->getMode(true),
		);
		$this->_display($result, '当前部署模式');

		/*
		$list = array(
			array(
				'title' => '触屏 - 百科词条 - 首页',
				'opts' => array('index', array(), 'touch', 'wiki'),
			),
			array(
				'title' => '触屏 - 百科词条 - 词条列表 - 全部词条',
				'opts' => array('listall', array(), 'touch', 'wiki'),
			),
			array(
				'title' => '触屏 - 百科词条 - 词条列表 - 指定类型的词条',
				'opts' => array('list', array(0), 'touch', 'wiki'),
			),
			array(
				'title' => '触屏 - 百科词条 - 详情',
				'opts' => array('show', array('AViF38RLwTXKqEtxI1NW'), 'touch', 'wiki'),
			),
			array(
				'title' => '触屏 - 百科词条 - 搜索',
				'opts' => array('search', array('AViF38RLwTXKqEtxI1NW'), 'touch', 'wiki'),
			),

			array(
				'title' => '触屏 - 百科知识 - 首页',
				'opts' => array('index', array(), 'touch', 'baike'),
			),
			array(
				'title' => '触屏 - 百科知识 - 地图页',
				'opts' => array('map', array(), 'touch'),
			),
			array(
				'title' => '触屏 - 百科知识 - 分类列表页',
				'opts' => array('cate', array('id'=>2, 'bj')),
			),
			array(
				'title' => '触屏 - 百科知识 - 知识列表页',
				'opts' => array('list', array('id'=>21, 'bj'), 'touch', 'baike'),
			),
			array(
				'title' => '触屏 - 百科知识 - 知识列表页 - 异步接口:加载更多',
				'opts' => array('listmore', array('id'=>21, 'page'=>1), 'touch', 'baike'),
			),
			array(
				'title' => '触屏 - 百科知识 - 标签聚合页',
				'opts' => array('agg', array('房产'), 'touch', 'baike'),
			),
			array(
				'title' => '触屏 - 百科知识 - 标签聚合页 - 异步接口:加载更多',
				'opts' => array('aggmore', array('房产', 1), 'touch', 'baike'),
			),
			array(
				'title' => '触屏 - 百科知识 - 知识详情页',
				'opts' => array('show', array(95)),
			),
			array(
				'title' => '触屏 - 百科知识 - 搜索结果页',
				'opts' => array('search', array('keyword'=>'V'), 'touch', 'baike'),
			),
			array(
				'title' => '触屏 - 百科知识 - 搜索结果页 - 异步接口:加载更多',
				'opts' => array('result', array('keyword'=>'V', 'page'=>1), 'touch', 'baike'),
			),
		);

		foreach ( $list as $i => $item ) {
			$url = call_user_func_array('url', $item['opts']);
			$this->_display($result, '当前模式');
			$this->_display(false, $item['title']);
			$this->_display($item['opts'], '参数形式');
			$this->_display(array('url'=>$url), '伪静态地址');
		}

		return true;
		*/
		var_dump(
			'触屏 - 百科词条 - 首页',
			url('index', array(), 'touch', 'wiki'));
		var_dump(
			'触屏 - 百科词条 - 词条列表 - 全部词条',
			url('listall', array(), 'touch', 'wiki'));
		var_dump(
			'触屏 - 百科词条 - 词条列表 - 指定类型的词条',
			url('list', array(0), 'touch', 'wiki'));
		var_dump(
			'PC版 - 百科词条 - 首页',
			url('index', array(), 'pc', 'wiki'));
		var_dump(
			'PC版 - 百科词条 - 词条列表 - 默认入口(第1页)',
			url('listall', array(), 'pc', 'wiki'));
		var_dump(
			'PC版 - 百科词条 - 词条列表 - 指定页码',
			url('listall', array('page'=>1), 'pc', 'wiki'));
		var_dump(
			'PC版 - 百科词条 - 搜索',
			url('search', array('word'=>'词条', 'page'=>1), 'pc', 'wiki'));

		echo '<hr>';
		var_dump(
			'触屏 - 百科知识 - 首页',
			url('index', array(), 'touch', 'baike'));
		var_dump(
			'触屏 - 百科知识 - 地图页',
			url('map', array(), 'touch'));
		var_dump(
			'触屏 - 百科知识 - 分类列表页',
			url('cate', array('id'=>2, 'bj')));
		var_dump(
			'触屏 - 百科知识 - 知识列表页',
			url('list', array('id'=>21, 'bj'), 'touch', 'baike'));
		var_dump(
			'触屏 - 百科知识 - 知识列表页 - 异步接口:加载更多',
			url('listmore', array('id'=>21, 'page'=>1), 'touch', 'baike'));
		var_dump(
			'触屏 - 百科知识 - 标签聚合页',
			url('agg', array('房产'), 'touch', 'baike'));
		var_dump(
			'触屏 - 百科知识 - 标签聚合页 - 异步接口:加载更多',
			url('aggmore', array('房产', 1), 'touch', 'baike'));
		var_dump(
			'触屏 - 百科知识 - 知识详情页',
			url('show', array(95)));
		var_dump(
			'触屏 - 百科知识 - 搜索结果页',
			url('search', array('keyword'=>'V'), 'touch', 'baike'));
		var_dump(
			'触屏 - 百科知识 - 搜索结果页 - 异步接口:加载更多',
			url('result', array('keyword'=>'V', 'page'=>1), 'touch', 'baike'));

		var_dump(
			'PC版 - 百科知识 - 首页',
			url('index', array('city'=>'bj', 'cid'=>2), 'pc', 'baike'));
		var_dump(
			'PC版 - 百科知识 - 首页 (无城市)',
			url('index', array('cid'=>2), 'pc', 'baike'));
		var_dump(
			'PC版 - 百科知识 - 分类列表页',
			url('cate', array('id'=>18, 'city'=>'bj', 'page'=>1), 'pc', 'baike'));
		var_dump(
			'PC版 - 百科知识 - 标签聚合页',
			url('agg', array('tag'=>'房产', 'city'=>'bj', 'id'=>2, 'page'=>1), 'pc', 'baike'));
		var_dump(
			'PC版 - 百科知识 - 知识详情页',
			url('show', array('id'=>2313), 'pc', 'baike'));
		var_dump(
			'PC版 - 百科知识 - 知识搜索页',
			url('search', array(), 'pc', 'baike'));

		echo '<hr>';
	}

	public function changeImage() {
		$img = 'http://src.leju.com/imp/imp/deal/b7/53/1/c99206fb4f1bf2fc8bbb0c1aa40_p58_mk61_sX0_rt0_c819X614X102X77.jpg';
		echo '原始图片: ', $img, '<br><img src="',$img,'"><hr>', PHP_EOL;
		$newimg = changeImageSize($img, 100, 100);
		echo '智能剪裁后: ', $newimg, '<br><img src="',$newimg,'"><hr>', PHP_EOL;
		$newimg = changeImageSize($img, 100, 100, 'scale');
		echo '缩放转换后: ', $newimg, '<br><img src="',$newimg,'"><hr>', PHP_EOL;

		echo '<hr>', PHP_EOL;


		$content = '<p class="pic"><img src="http://src.leju.com/imp/imp/deal/72/1a/e/6f8bc31b37aba600c712caa14af_p58_mk61_cm380X286.jpg" alt=""></p>				<p style="text-align: center"><img src="http://src.leju.com/imp/imp/deal/0a/c8/2/e084ebec0e763d91bdd6fefeeb1_p24_mk24.jpg"/></p><generalize>一</generalize><p>中战略烛破哦俄罗斯；&nbsp;</p><p><br/></p><generalize>二</generalize><p>；田顶号;lsdjg;sdlg;sdfls;dkfjpos房产房产中介楼市任志强</p>			<p class="pic"><img src="http://src.leju.com/imp/imp/deal/72/1a/e/6f8bc31b37aba600c712caa14af_p58_mk61_cm380X286.jpg" alt=""></p>				<p style="text-align: center"><img src="http://src.leju.com/imp/imp/deal/0a/c8/2/e084ebec0e763d91bdd6fefeeb1_p24_mk24.jpg"/></p><generalize>一</generalize><p>中战略烛破哦俄罗斯；&nbsp;</p><p><br/></p><generalize>二</generalize><p>；田顶号;lsdjg;sdlg;sdfls;dkfjpos房产房产中介楼市任志强</p>			<p class="pic"><img src="http://src.leju.com/imp/imp/deal/72/1a/e/6f8bc31b37aba600c712caa14af_p58_mk61_cm380X286.jpg" alt=""></p>				<p style="text-align: center"><img src="http://src.leju.com/imp/imp/deal/0a/c8/2/e084ebec0e763d91bdd6fefeeb1_p24_mk24.jpg"/></p><generalize>一</generalize><p>中战略烛破哦俄罗斯；&nbsp;</p><p><br/></p><generalize>二</generalize><p>；田顶号;lsdjg;sdlg;sdfls;dkfjpos房产房产中介楼市任志强</p>			';
		echo '<code style="margin-left:50px; width:414px; overflow:hidden; float:left; border:1px solid red;"><pre>', $content, '</pre></code>', PHP_EOL;
		$new = changeImagesSize($content, 320, 286);
		echo '<code style="margin-left:50px; width:414px; overflow:hidden; float:left; border:1px solid green;"><pre>', $new, '</pre></code>', PHP_EOL;


	}

	/**
	 * Redis 测试
	 */
	public function redis() {
		echo '<h3>测试Reids</h3>', PHP_EOL;
		echo '<pre>', var_export(C('REDIS'), true), '</pre>', PHP_EOL;
		$r = S(C('REDIS'));
		$r->set('test1', '123');
		$v = $r->get('test1');
		var_dump($r, $v);
		$r->delete('test1');
		$v = $r->get('test1');
		var_dump($v);
	}

	/**
	 * 分页器方法演示
	 */
	public function pager() {
		echo '<h3>测试分页器</h3>', PHP_EOL;
		$list = array(
			1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,
		);
		$total = count($list);
		$pagesize = 2;
		$page = 10;
		$linkopts = array();
		$opts = array(
			'first' => false,
			'last' => false,
			'prev' => true,
			'next' => true,
			'number' => 5,
			'linkstring' => '/search.html?page=#&'.implode('&',$linkopts),
		);
		$pager = \pager($page, $total, $pagesize, $opts);
		echo '<pre>', var_export($pager, true), '</pre>', PHP_EOL;
		layout(false);
		$this->assign('pager', $pager);
		$this->display('info');
		exit;
	}

	/* ----------------------- 使用搜索服务 ----------------------- */
	/**
	 * 新闻池调用接口
	 */
	public function info() {
		$type = I('get.type', '', 'trim');
		$allowed = array('','news','house','tags');
		if ( !in_array($type, $allowed) ) {
			echo '错误的测试类型，允许的有 ', implode(',', $allowed), PHP_EOL;
		}
		if ( $type=='' ) {
			echo '<h1>所有测试</h1>', PHP_EOL;
		}

		if ( $type=='' || $type=='news' ) {
			echo '<h2>读取新闻数据的接口</h2>', PHP_EOL;
			$newsid = '6072948702421776591';
			$info = D('Infos', 'Logic', 'Common');
			$result = $info->getNews($newsid);
			$this->_display(false, '单个新闻测试');
			$this->_display($newsid, '参数形式');
			$this->_display($result, '接口调用结果');

			$newsid = '6072948702421776591,6200523527419128178,6200490046207105392,6200483331688430512';
			$info = D('Infos', 'Logic', 'Common');
			$result = $info->getNews($newsid);
			$this->_display(false, '多个新闻测试1');
			$this->_display($newsid, '参数形式');
			$this->_display($result, '接口调用结果');

			$newsid = array(6072948702421776591,6200523527419128178,6200490046207105392,6200483331688430512);
			$info = D('Infos', 'Logic', 'Common');
			$result = $info->getNews($newsid);
			$this->_display(false, '多个新闻测试2');
			$this->_display($newsid, '参数形式');
			$this->_display($result, '接口调用结果');
		}

		if ( $type=='' || $type=='house' ) {
			echo '<h2>读取楼盘的数据接口</h2>', PHP_EOL;
			$house_id = 'bj92758';	// 潮白河孔雀英国宫
			$info = D('Infos', 'Logic', 'Common');
			$result = $info->getHouse($house_id);
			$this->_display(false, '单独一个楼盘测试');
			$this->_display(array($house_id), '参数形式');
			$this->_display($result, '接口调用结果');

			// bj92758 北京 - 潮白河孔雀英国宫
			// bz132329 巴中 - 凯莱国际社区
			// bx91458 本溪 - 佳兆业 · 水岸新都
			// bj54865 北京 - 君安国际
			$house_id = 'xxxbjddfe92753432432438,bz132329,bj92758,xxx3323bjddfe9275343243dsf2438,bx91458,bj92758,bj54865';
			$info = D('Infos', 'Logic', 'Common');
			$result = $info->getHouse($house_id);
			$this->_display(false, '多个楼盘测试1');
			$this->_display(array($house_id), '参数形式');
			$this->_display($result, '接口调用结果');

			$house_id = array('bj92758','bz132329','bx91458','bj54865');
			$info = D('Infos', 'Logic', 'Common');
			$this->_display(false, '多个楼盘测试2');
			$this->_display($house_id, '参数形式');
			$result = $info->getHouse($house_id);
			$this->_display($result, '接口调用结果');

			$house_id = array(
				array('city'=>'bj','hid'=>'92758'),
				array('city'=>'bz','hid'=>'132329'),
				array('city'=>'bx','hid'=>'91458'),
				array('city'=>'bj','hid'=>'54865'),
			);
			$info = D('Infos', 'Logic', 'Common');
			$result = $info->getHouse($house_id);
			$this->_display(false, '多个楼盘测试3');
			$this->_display($house_id, '参数形式');
			$this->_display($result, '接口调用结果');
		}

		if ( $type=='' || $type=='tags' ) {
			echo '<h2>读取标签库的数据接口</h2>', PHP_EOL;
			$page = 1;
			$pagesize = 100;
			$last_createtime = 0;
			$info = D('Infos', 'Logic', 'Common');
			$result = $info->getTags($page, $pagesize, $last_createtime);
			$this->_display(false, '读取标签库数据测试');
			$this->_display(array('page'=>$page,'pagesize'=>$pagesize,'last_createtime'=>$last_createtime), '参数形式');
			$this->_display($result, '接口调用结果');
		}

	}

	/**
	 * 用乐居标签获取相关新闻池数据的接口演示
	 */
	public function reltags() {
		$tags = array('壹房产','柿子树下','地产与远方','投资');
		echo '<h3>使用的标签包括</h3>', PHP_EOL,
			 '<pre>', print_r($tags, true), '</pre>', PHP_EOL;
		echo '<h3>读取与标签相关的新闻列表的接口</h3>', PHP_EOL;
		$info = D('Infos', 'Logic', 'Common');
		$result = $info->relNews($tags, 3);
		var_dump($result);
		echo '<hr>', PHP_EOL;

		// @TODO: 有问题！！！
		// echo '<h3>读取与标签相关的楼盘列表的接口</h3>', PHP_EOL;
		// $info = D('Infos', 'Logic', 'Common');
		// $result = $info->relHouse($tags, 3);
		// var_dump($result);
		// echo '<hr>', PHP_EOL;
	}


	/* ----------------------- 使用搜索服务 ----------------------- */
	/**
	 * 获取登录认证
	 */
	public function login() {
		echo '<h1>获取登录认证</h1>', PHP_EOL;

		$engine = D('Search', 'Logic', 'Common');
		$result = $engine->getToken();
		$this->_display(false, '获取登录认证');
		$this->_display(array(), '参数形式');
		$this->_display(array('token'=>$result), '接口调用结果');
	}

	/**
	 * 批量导入标签
	 */
	public function syncTags() {
		echo '<h3>批量导入标签</h3>', PHP_EOL;
		$source = 1;
		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 200, 'intval');

		$last_createtime = 0;
		// 从新闻池中获取词条
		$info = D('Infos', 'Logic', 'Common');
		$result = $info->getTags($page, $pagesize, $last_createtime);
		if ( $result ) {
			echo '<br>', PHP_EOL;
			echo ' Read API , Total Words : ', $result['total'], 
				 ' Total page : ', ceil($result['total']/$pagesize), 
				 ' Current Page : ', $page, '<br>', PHP_EOL;
		}

		$dict_words = array();
		$engine = D('Search', 'Logic', 'Common');
		foreach ( $result['list'] as $i => $item ) {
			$_id = trim($item['word']);
			if ( strpos($_id, ',') || strpos($_id, ' ') ) {
				unset($result['list'][$i]);
				continue;
			}
			$doc = array(
				'_id' => $_id,
				'word' => $_id,
				'hits' => 0,
				'score' => 0,
			);
			// 通过服务向联想搜索词条索引中添加新词条索引
			$ret = $engine->create($doc, 'suggest.lejutag', 'word');

			array_push($dict_words, $_id);
		}
		$this->_display(false, '向联想词库中添加词条');
		$this->_display($doc, '参数形式');
		$this->_display($ret, '接口调用结果');

		// 通过服务接口向字典中追回词条
		$ret = $engine->appendDictWords($dict_words, 'dict_tags');
		$this->_display(false, '通过服务接口向字典中追回词条');
		$this->_display($dict_words, '参数形式');
		$this->_display($ret, '接口调用结果');


		$m = D('Tags', 'Model', 'Common');
		// 向数据库中添加新词条 (同步)
		$ret = $m->bulkAdd($result['list'], $source, true);
		$this->_display(false, '向数据库中添加新词条');
		$this->_display($result['list'], '参数形式');
		$this->_display($ret, '接口调用结果');
		// $count = $m->countSource($source);
		// echo '<pre>', PHP_EOL, var_export($count, true), PHP_EOL, '</pre>', PHP_EOL;
	}

	/**
	 * 联想词搜索乐居标签词
	 */
	public function tagSuggest() {
		echo '<h1>联想词搜索乐居标签词</h1>', PHP_EOL;

		$word = I('get.k', '', 'trim');
		echo '<form action="" method="GET">', PHP_EOL,
			 '<input type="text" name="k" value="', $word,'">', PHP_EOL,
			 '<input type="submit" value="查询">', PHP_EOL,
			 '</form><hr>', PHP_EOL;

		// 正常服务请求使用下面代码段
		$engine = D('Search', 'Logic', 'Common');
		$result = $engine->suggest($word, $limit);
		$this->_display(false, '联想词逐字查询标签词，用于后台输入标签时使用');
		$params = array('word'=>$word,'limit'=>$limit);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');

		// 模拟使用数据库进行数据查询 @此处可作临时使用
		// $m = D('Tags', 'Model', 'Common');
		// $list = $m->suggest($word);
		// var_dump($list);
	}

	/**
	 * 从正文中分析词条服务演示
	 */
	public function parseWords() {
		echo '<h1>从正文中分析词条服务演示</h1>', PHP_EOL;

		$content = '婚前财产是指在结婚前夫妻一方就已经取得的财产。夫妻一方的婚前财产, 不管是动产还是不动产, 是有形财产还是无形财产, 只要合法取得, 就依法受到法律保护。婚前财产公证的利弊有哪些';
		$stats = true;
		$limit = 5;
		$dict = 'dict_wiki';
		$engine = D('Search', 'Logic', 'Common');
		$result = $engine->analyze($content, $stats, 5, $dict);
		$this->_display(false, '使用乐居百科词条进行分析，提取乐居业务标签');
		$params = array('content'=>$content,'stats'=>$stats, 'limit'=>$limit, 'dict'=>$dict);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');



		$content = '雾霾苍雾霾好好测试一下词条大地碧桂园一下测试一下分词词签证条签证3效果如何蓝天的好好的还是不签证好';
		$stats = true;
		$limit = 5;
		$dict = C('ENGINE.PARSETAGS_ID');	// dict_tags
		$engine = D('Search', 'Logic', 'Common');
		$result = $engine->analyze($content, $stats, 5, $dict);
		$this->_display(false, '使用乐居标签库进行分析，提取乐居业务标签');
		$params = array('content'=>$content,'stats'=>$stats, 'limit'=>$limit, 'dict'=>$dict);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');


		$content = '蓝天苍天好好大地测试一下蓝天苍天大地一下测试一下苍天分词词条1蓝天效果如何蓝天的好好的还是不好';
		$stats = true;
		$limit = 5;
		$dict = C('ENGINE.PARSEWORDS_ID');	// dict_wiki
		$engine = D('Search', 'Logic', 'Common');
		$result = $engine->analyze($content, $stats, 5, $dict);
		$this->_display(false, '使用百科词条进行分析，提取百科词条');
		$params = array('content'=>$content,'stats'=>$stats, 'limit'=>$limit, 'dict'=>$dict);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');
	}

	/**
	 * 获取分析使用的词条
	 */
	public function getWords() {
		echo '<h1>获取分析使用的字典词条列表</h1>', PHP_EOL;

		$dict = C('ENGINE.PARSETAGS_ID');	// dict_tags
		$engine = D('Search', 'Logic', 'Common');
		$result = $engine->getDictWords($dict);
		$this->_display(false, '列表显示乐居业务标签');
		$this->_display($dict, '参数形式');
		$this->_display($result, '接口调用结果');


		$dict = C('ENGINE.PARSEWORDS_ID');	// dict_wiki
		$engine = D('Search', 'Logic', 'Common');
		$result = $engine->getDictWords($dict);
		$this->_display(false, '列表显示百科词条');
		$this->_display($dict, '参数形式');
		$this->_display($result, '接口调用结果');
	}


	// 模拟获取记录
	protected function getRecord($filters=array()) {
		// 模拟数据库记录
		$record = array(
			'id' => 1000,
			'cateid' => 111,
			'catepath' => '0-1-11-111',
			'version' => NOW_TIME,
			'status' => 1,
			'title' => '这是一条测试知识文档000',
			'content' => '这是一条测试知识文档的内容部分',
			'cover' => '',
			'coverinfo' => '',
			'editorid' => rand(1, 1000),
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

		return empty($filters) ? $record : array_intersect_key($record, $filters);
	}

	protected function getRecords( $filters=array() ) {
		$record = $this->getRecord();
		$list = array();
		for ( $i=0; $i<5; $i++ ) {
			// 模拟批量数据
			$row = $record;
			$row['id'] = intval($row['id']) + $i + 1;
			$row['title'] = $row['title'].'-'.rand(0, 100).'-'.$row['id'];
			// 需要手工补全的部份
			$row['title_firstletter'] = 'Z';
			$row['title_pinyin'] = 'zheshiyitiaoceshizhishiwendang';
			$row['url'] = 'http://ld.m.baike.leju.com/show/?id='.$row['id'];
			$row = empty($filters) ? $row : array_intersect_key($row, $filters);
			array_push($list, $row);
		}
		return $list;
	}

	/**
	 * 创建文档数据
	 *
	 */
	public function createDocuments() {
		echo '<h1>创建文档数据</h1>', PHP_EOL;

		$engine = D('Search', 'Logic', 'Common');
		$this->_display(false, '创建数据(单条)');
		$record = $this->getRecord();
		// 需要手工补全的部份
		$record['title_firstletter'] = 'Z';
		$record['title_pinyin'] = 'zheshiyitiaoceshizhishiwendang';
		$record['url'] = 'http://ld.m.baike.leju.com/show/?id='.$record['id'];
		// 因为批量操作，务必传入接口的参数为一个二维数据，即数据记录集合，不是单条数据
		$record = array($record);
		$this->_display($record, '创建数据使用的文档数据');
		$result = $engine->createKnowledge($record);
		$this->_display(array('result'=>$result), '接口调用结果');

		$this->_display(false, '创建数据(多条)');
		$list = $this->getRecords();
		$this->_display($list, '创建数据使用的文档数据');
		$result = $engine->createKnowledge($list);
		$this->_display(array('result'=>$result), '接口调用结果');
	}

	/**
	 * 支持批量的部份更新数据文档
	 */
	public function updateDocuments() {
		echo '<h1>更新文档数据</h1>', PHP_EOL;

		$filters = array(
			'id' => '',	// 必须存在，否则返回 false
			'catepath' => '',
			'version' => '',
			'status' => '',
			'content' => '',
			'cover' => '',
			'coverinfo' => '',
			'editorid' => '',
			'ctime' => '',
			'ptime' => '', // 发布时间
			'utime' => '', // 发布时间
			'scope' => '', // 城市代码
			'src_type' => '',
			'src_url' => '',
			'top_time' => '',
			'top_title' => '',
			'top_cover' => '',
			'top_coverinfo' => '',
			'rcmd_time' => '',
			'rcmd_title' => '',
			'rcmd_cover' => '',
			'rcmd_coverinfo' => '',
			'rel_news' => '',
			'rel_house' => '',
		);
		$engine = D('Search', 'Logic', 'Common');
		// 模拟数据库记录
		$record = $this->getRecord($filters);
		$this->_display(false, '更新数据(单条)');
		$record['tags'] = '测试 内容 标签 标签2';
		// 需要手工补全的部份
		$record['title_firstletter'] = 'Z';
		$record['title_pinyin'] = 'zheshiyitiaoceshizhishiwendang';
		$record['url'] = 'http://ld.m.baike.leju.com/show/?id='.$record['id'];
		$record = array($record);
		$this->_display($record, '更新数据使用的文档数据');
		$result = $engine->updateKnowledge($record);
		$this->_display(array('result'=>$result), '接口调用结果');

		$this->_display(false, '更新数据(多条)');
		$list = $this->getRecords($filters);
		$this->_display($list, '更新数据使用的文档数据');
		$result = $engine->updateKnowledge($list);
		$this->_display(array('result'=>$result), '接口调用结果');

	}

	/**
	 * 支持批量删除数据文档
	 */
	public function removeDocuments() {
		echo '<h1>删除文档数据</h1>', PHP_EOL;

		$engine = D('Search', 'Logic', 'Common');
		$list = array(1000);
		$this->_display(false, '删除数据(单条)');
		$this->_display($list, '删除数据使用的文档数据');
		$result = $engine->removeKnowledge($list);
		$this->_display(array('result'=>$result), '接口调用结果');

		$this->_display(false, '删除数据(多条)');
		$list = array(1001, 1002, 1003, 1004, 1005);
		$this->_display($list, '删除数据使用的文档数据');
		$result = $engine->removeKnowledge($list);
		$this->_display(array('result'=>$result), '接口调用结果');
	}

	/**
	 * 通过条件限制批量更新数据
	 */
	public function batchUpdate() {
		echo '<h1>使用条件批量更新文档数据</h1>', PHP_EOL;
		$engine = D('Search', 'Logic', 'Common');
		// 模拟数据库记录
		$changes = array(
			'title' => '这只是一个批量更新测试'.rand(1, 1000),
		);
		$opts = array(array('1000,1001','_id'));
		$prefix = array(array('0-1-', '_multi.catepath'));
		$this->_display($opts, '批量更新数据使用的条件参数');
		$this->_display($changes, '批量更新数据使用的文档变更数据');
		$result = $engine->batchesUpdate($opts, $prefix, $changes);
		$this->_display(array('result'=>$result), '接口调用结果');
	}

	/**
	 * 通过条件限制批量更新数据
	 * 范围条件删除 [1002,1005]，没生效
	 */
	public function batchRemove() {
		echo '<h1>使用条件批量删除文档数据</h1>', PHP_EOL;
		$engine = D('Search', 'Logic', 'Common');
		// 设置操作条件
		// $opts = array(array('[1000,1005]','_id'));
		$opts = array(array('1003,1004','_id'));
		$prefix = array(array('0-1-','_multi.catepath'));
		$this->_display($opts, '批量更新数据使用的条件参数');
		$result = $engine->batchesRemove($opts, $prefix);
		$this->_display(array('result'=>$result), '接口调用结果');
	}


	/**
	 * 查询列表使用演示
	 */
	public function select() {
		echo '<h1>查询列表使用演示</h1>', PHP_EOL;
		$engine = D('Search', 'Logic', 'Common');

		// 获取单条百科内容
		$page = 1;
		$pagesize = 1;
		$keyword = '';
		$opts = array(array('二手房中介','_id'));
		$prefix = array();
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_scope');
		$ds = 0;
		$business = 'wiki';
		$result = $engine->select($page, $pagesize, $keyword, $opts, $prefix, $order, $fields, $ds, $business);
		$this->_display(false, '获取单条百科内容');
		$params = array(
			'page'=>$page,'pagesize'=>$pagesize,'keyword'=>$keyword,
			'opts'=>$opts,'prefix'=>$prefix,'order'=>$order,'fields'=>$fields,
			'ds'=>$ds, 'business'=>$business,
		);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');


		// 获取单条知识内容
		$page = 1;
		$pagesize = 1;
		$keyword = '';
		$opts = array(array('15','_id'));
		$prefix = array();
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_deleted','_origin');
		$business = 'knowledge';
		$result = $engine->select($page, $pagesize, $keyword, $opts, $prefix, $order, $fields, $business);
		$this->_display(false, '获取单条知识内容');
		$params = array(
			'page'=>$page,'pagesize'=>$pagesize,'keyword'=>$keyword,
			'opts'=>$opts,'prefix'=>$prefix,'order'=>$order,'fields'=>$fields,
			'business'=>$business,
		);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');

		// 前缀条件获取列表 获取一级或二级栏目下的所有知识内容
		$page = 1;
		$pagesize = 10;
		$keyword = '';
		$opts = array();
		$prefix = array(array('0-2-', '_multi.catepath'));
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_scope','_multi.catepath');
		$result = $engine->select($page, $pagesize, $keyword, $opts, $prefix, $order, $fields);
		$this->_display(false, '前缀条件获取列表');
		$params = array(
			'page'=>$page,'pagesize'=>$pagesize,'keyword'=>$keyword,
			'opts'=>$opts,'prefix'=>$prefix,'order'=>$order,'fields'=>$fields,
		);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');

		// 多条件获取条件
		$page = 1;
		$pagesize = 10;
		$keyword = '';
		$opts = array(array('false','_deleted'), array('北京,上海','_scope'));
		$prefix = array();
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_deleted','_scope');
		$result = $engine->select($page, $pagesize, $keyword, $opts, $prefix, $order, $fields);
		$this->_display(false, '多条件获取条件');
		$params = array(
			'page'=>$page,'pagesize'=>$pagesize,'keyword'=>$keyword,
			'opts'=>$opts,'prefix'=>$prefix,'order'=>$order,'fields'=>$fields,
		);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');

		// 查询所有数据，并指定每页信息进行列表分页处理，按创建时间进行排序
		$page = 1;
		$pagesize = 3;
		$keyword = '';
		$opts = array();
		$prefix = array();
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_deleted','_scope','_url');
		$result = $engine->select($page, $pagesize, $keyword, $opts, $prefix, $order, $fields);
		$this->_display(false, '列表显示数据');
		$params = array(
			'page'=>$page,'pagesize'=>$pagesize,'keyword'=>$keyword,
			'opts'=>$opts,'prefix'=>$prefix,'order'=>$order,'fields'=>$fields,
		);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');

		// 按关键词搜索数据
		$page = 1;
		$pagesize = 3;
		$keyword = '真的';
		$opts = array();
		$prefix = array();
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_deleted','_scope','_url');
		$result = $engine->select($page, $pagesize, $keyword, $opts, $prefix, $order, $fields);
		$this->_display(false, '按关键词搜索数据');
		$params = array(
			'page'=>$page,'pagesize'=>$pagesize,'keyword'=>$keyword,
			'opts'=>$opts,'prefix'=>$prefix,'order'=>$order,'fields'=>$fields,
		);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');
	}

	/**
	 * 搜索功能使用演示
	 */
	public function search() {
		echo '<h1>搜索功能使用演示</h1>', PHP_EOL;
		$engine = D('Search', 'Logic', 'Common');

		// 获取单条知识内容
		$page = 1;
		$pagesize = 10;
		$keyword = '测试';
		$opts = array(array('15','_id'));
		$prefix = array();
		$fields = array('_id','_title','_scope');
		$ds = 1; // 0只搜索标题 1搜标题与内容
		$business = array('knowledge'); // 要搜索的业务
		$result = $engine->search($page, $pagesize, $keyword, $opts, $prefix, $fields, $ds);
		$this->_display(false, '搜索功能使用演示');
		$params = array(
			'page'=>$page,'pagesize'=>$pagesize,'keyword'=>$keyword,
			'opts'=>$opts,'prefix'=>$prefix,'fields'=>$fields,
			'ds'=>$ds,'business'=>$business,
		);
		$this->_display($params, '参数形式');
		$this->_display($result, '接口调用结果');
	}


	/**
	 * 通用调试接口
	 */
	protected function _display($data=array(), $title='') {
		$tt = !$data ? 'h3' : 'h4';
		if ( $title != '' ) {
			echo '<', $tt, '>', $title, '</', $tt, '>', PHP_EOL;
		}
		if ( is_array($data) && !empty($data) ) {
			echo '<pre>', PHP_EOL, var_export($data, true), PHP_EOL, '</pre>',
				 PHP_EOL, '<hr>', PHP_EOL;
		}
		return true;
	}
}