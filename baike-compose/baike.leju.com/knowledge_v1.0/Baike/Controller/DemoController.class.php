<?php
/**
 * 用于测试单元逻辑使用
 */
namespace Baike\Controller;

class DemoController extends BaseController {

	public function __construct() {
		$code = 35940;
		$input = I('get.code', 0, 'intval');
		if ( $input !== $code ) {
			exit();
		}
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

		// 常用方法
		$list[] = array(
			'title' => '基本信息 [server]',
			'url' => U('Baike/Demo/server'),
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
			$item['url'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].str_replace('/index.php','',$item['url']);
			echo '<li><a href="',$item['url'],'" target="_blank">',$item['title'],'</a></li>',PHP_EOL;
		}
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
		$d = D('Cate', 'Logic', 'Common');
		$d->init(1);
		$l = $d->toTree();
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
			'触屏 - 百科词条 - 详情',
			url('show', array('AViF38RLwTXKqEtxI1NW'), 'touch', 'wiki'));
		var_dump(
			'触屏 - 百科词条 - 搜索',
			url('search', array('AViF38RLwTXKqEtxI1NW'), 'touch', 'wiki'));
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
		$this->_display($opts, '批量更新数据使用的条件参数');
		$this->_display($changes, '批量更新数据使用的文档变更数据');
		$result = $engine->batchesUpdate($opts, $changes);
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
		$this->_display($opts, '批量更新数据使用的条件参数');
		$result = $engine->batchesRemove($opts);
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