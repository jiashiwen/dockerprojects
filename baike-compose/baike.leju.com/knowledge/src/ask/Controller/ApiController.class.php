<?php
namespace ask\Controller;
use Think\Controller;
class ApiController extends BaseController {

	public function index() {
		if ( !$this->_debug ) {
			die('Access Deny');
		}
		echo 'Api index page';
		var_dump(I());

		$url = D('Url', 'Logic', 'Common');
		$url->setBase('pc','ask');
		$host = $url->getPath();
		var_dump($host);

		echo '<h3>接口地址参考</h3><hr><br>', PHP_EOL;
		$apis = array(
			'猜你喜欢数据接口@首页,提问页,列表页' => $host.'api/guess/',
			'联想词数据接口(正常)@所有搜索框,提问页标题框' => $host.'api/suggest/?k=测试',
			'联想词数据接口(异常)@所有搜索框,提问页标题框' => $host.'api/suggest/?k=',
			'----',
			'访问统计(正常)@正文页' => $host.'api/visit/?id=3',
			'访问统计(异常)@正文页1' => $host.'api/visit/?id=',
			'访问统计(异常)@正文页2' => $host.'api/visit/?act=123&id=',
			'----',
			'相关知识数据接口(正常)@正文页' => $host.'api/relations/?tag=16014',
			'相关知识数据接口(异常)@正文页' => $host.'api/relations/?tag=',
			'关注问题操作接口 (正常)@正文页' => $host.'api/attention/?qid=1',
			'关注问题操作接口 (异常)@正文页' => $host.'api/attention/?qid=',
			'答案点赞操作接口 (正常)@正文页' => $host.'api/good/?aid=1',
			'答案点赞操作接口 (异常)@正文页' => $host.'api/good/?aid=',
			'设置最佳答案操作接口 (正常)@正文页' => $host.'api/best/?aid=1',
			'设置最佳答案操作接口 (异常)@正文页' => $host.'api/best/?aid=',
			'获取问题的回复结果 默认排序(正常)@正文页' => $host.'api/answers/?qid=42034',
			'获取问题的回复结果 最多排序(正常)@正文页' => $host.'api/answers/?qid=42034&order=hot&page=1',
			'回复问题 @正文页' => $host.'api/reply/?qid=42034&reply=内容',
			'----',
			'标签联想数据接口(正常)@提问页' => $host.'api/tag/?k=词',
			'标签联想数据接口(异常)@提问页' => $host.'api/tag/?k=',
			'----',
			'热门问题获取更多接口@移动端首页' => $host.'api/hotq/?page=2',
		);
		foreach ( $apis as $name => $item ) {
			if ( $item == '----' ) {
				echo '<hr>', PHP_EOL;
			} else {
				echo '<a href="', $item, '" target="_blank">', $name, '</a> : ', $item, '<br>', PHP_EOL;
			}
		}
	}
	// 给符鑫导数据字典
	public function getWikiDict() {
		$m = D('Wiki', 'Model', 'Common');
		$where = [];
		$fields = ['id', 'title', 'stname', 'short'];
		$list = $m->where($where)->field($fields)->select();
		$result = [];
		foreach ( $list as $i => $item ) {
			$k = trim($item['stname']);
			if ( $k!='' ) {
				$result[$k] = intval($item['id']);
			}
		}
		$this->ajax_return($result);
	}

	/**
	 * 开发 - 发送邮件测试
	 */
	public function dev_sendmail() {
		if ( !$this->_debug ) { die(); }
		$question_url = 'http://ask.leju.com/';
		$title = '有人回答了您的问题';
		$content = '有人回答了您的问题。点击链接查看答案 '.$question_url;
		$mail_info = array(
			'title' => $title,
			'truename' => '',
			'content' => $content,
			'datetime' => date('Y年m月d日'),
		);
		layout(false);
		$this->assign('mail', $mail_info);
		$html = $this->theme('public')->fetch('./mail.notice');
		echo $html;
	}
	/**
	 * 开发 - 发送短信
	 */
	public function dev_sendmsg() {
		if ( !$this->_debug ) { die(); }
		$result = array('status'=>false);
		$to = I('get.t', '', 'trim');
		$ctx = I('get.body', '', 'trim');

		$is_mobile = false;
		if ( strlen($to)==11 && preg_match('/\d{11}/i', $to) ) {
			$is_mobile = true;
		}
		if ( !$is_mobile ) {
			$this->ajax_error('调用失败，参数错误');
		}

		$lEMS = D('Ems', 'Logic', 'Common');
		$ret = $lEMS->sendSMS($to, $ctx);
		if ( !$ret['status'] ) {
			$this->ajax_error('网络不通，请重试');
		}

		$ret = json_decode($ret['result'], true);
		if ( $this->_debug ) {
			$result['_debug'] = array('to'=>$to, 'content'=>$ctx);
			$result['_ret'] = $ret;
		}
		if ( $ret['status'] ) {
			$result['status'] = true;
			$result['reason'] = $ret['msg'];
		} else {
			$result['reason'] = $ret['error'];
		}
		$result['id'] = $ret['id'];
		$this->ajax_return($result);
	}
	/**
	 * 开发 - 测试敏感词处理结果
	 */
	public function dev_ban() {
		$ctx = I('request.text', '', 'trim');
		$lSensitive = D('Sensitive', 'Logic', 'Common');
		$ret = $lSensitive->debug(true)->detect($ctx, 0);
		print_r($ret);
	}
	/**
	 * 开发 - 从标签系统中获取热门标签列表
	 */
	public function dev_hot_tags() {
		if ( !$this->_debug ) { die(); }
		$num = I('get.n', 20, 'intval');
		$num = ( $num<5 ) ? 5 : ( ( $num > 100 ) ? 100 : $num );

		$day = I('get.d', 7, 'intval');
		$day = ( $day<7 ) ? 7 : ( ( $day > 30 ) ? 30 : $day );

		$tags = getHotTags($num, $day);
		$result = array(
			'day' => $day,
			'num' => $num,
			'ret' => $tags,
		);
		$this->ajax_return($result);
	}

	public function dev_getalltags() {
		if ( !$this->_debug ) { die('Access Denied!'); }

		$page = I('get.p', 1, 'intval');
		$page = ( $page<1 ) ? 1 : $page;

		$pagesize = I('get.ps', 100, 'intval');

		$lInfos = D('Infos', 'Logic', 'Common');
		$tags = $lInfos->getAllTags($page, $pagesize);
		$result = $tags;
		if ( $tags!=false && I('get.cache', 0, 'intval')==1 ) {
			$redis = S(C('REDIS'));
			$key = 'DICT:TAGS:ALLTAGS';
			foreach ( $tags['list'] as $i => $tag ) {
				$score = intval($tag['tag_id']);
				$member = trim($tag['word']);
				$ret = $redis->zAdd($key, $score, $member);
				$result['list'][$i]['cache'] = !!$ret;
			}
		}
		$this->ajax_return($result);
	}

	// 开发：用于查询指定标签是否存在标签系统中
	public function dev_searchtag() {
		if ( !$this->_debug ) { die('Access Denied!'); }
		$tags = I('get.tags', '', 'trim');
		$words = array();
		$tags = explode(',', $tags);
		foreach ( $tags as $i => $tag ) {
			$tag = trim($tag);
			$words[$tag] = $tag;
		}

		$lInfos = D('Infos', 'Logic', 'Common');
		$ret = $lInfos->searchTags($words);
		$list = $ret['list'];
		foreach ( $list as $i => $tag ) {
			$tagname = trim($tag['word']);
			if ( array_key_exists($tagname, $words) ) {
				unset($words[$tagname]);
			}
		}
		$result = array(
			'search' => $tags,
			'list' => $list,
			'notexists' => $words,
		);

		$this->ajax_return($result);

	}
	// 对比问答已绑定标签与乐居标签系统中数据的差异
	public function dev_diff_tags() {
		if ( !$this->_debug ) { die('Access Denied!'); }
		$dev = array();

		echo '<h1>1. 从问答数据中获取所有已经设定的标签</h1>', PHP_EOL;
		G('db_start');
		$mQuestion = D('Question', 'Model', 'Common');
		$sql = "select distinct tags from question;";
		$list = $mQuestion->query($sql);
		$old = array();
		foreach ( $list as $i => $item ) {
			$tags = explode(' ', trim($item['tags']));
			foreach ( $tags as $_i => $tag ) {
				$tag = trim($tag);
				if ( $tag=='' ) {
					continue;
				}
				$old[$tag] = $tag;
			}
		}
		unset($list);
		G('db_end');
		$dev['db'] = array(
			'cost' => G('db_start', 'db_end', 3),
			'mem' => G('db_start', 'db_end', 'm'),
		);

		echo '<h1>2. 接口获取全量标签数据</h1>', PHP_EOL;
		G('api_start');
		$lInfos = D('Infos', 'Logic', 'Common');
		$page = 1;
		$pagesize = 10000;
		$ret = $lInfos->getAllTags($page, $pagesize);
		$tags = array();
		foreach ( $ret['list'] as $i => $tag ) {
			$tags[$tag['word']] = $tag['tag_id'];
		}
		G('api_end');
		$dev['api'] = array(
			'cost' => G('api_start', 'api_end', 3),
			'mem' => G('api_start', 'api_end', 'm'),
		);

		echo '<h1>3. 计算问答中设定的标签，不在乐居标签系统的标签数据</h1>', PHP_EOL;
		$diff = array();
		G('diff_start');
		foreach ( $old as $tag => $tagname ) {
			if ( !array_key_exists($tag, $tags) ) {
				$diff[$tag] = $tag;
			}
		}
		G('diff_end');
		$dev['diff'] = array(
			'cost' => G('diff_start', 'diff_end', 3),
			'mem' => G('diff_start', 'diff_end', 'm'),
		);

		echo '<h1>4. 结果输出</h1>', PHP_EOL;
		echo '<b>问答绑定的标签(', count($old), ')</b><hr><pre>', PHP_EOL, var_export($old, true), PHP_EOL, '</pre>', PHP_EOL;
		echo '<b>不在标签系统的标签(', count($diff), ')</b><hr><pre>', PHP_EOL, var_export($diff, true), PHP_EOL, '</pre>', PHP_EOL;
		// echo '<b>标签系统中的数据(', count($ret['list']), ')</b><hr><pre>', PHP_EOL, var_export($ret['list'], true), PHP_EOL, '</pre>', PHP_EOL;
		echo '<!--', PHP_EOL, var_export($dev, true), PHP_EOL, '-->', PHP_EOL;
	}

	public function dev_baike_tags() {
		if ( !$this->_debug ) { die('Access Denied!'); }
		$dev = array();

		G('db_start');
		$mQuestion = D('Knowledge', 'Model', 'Common');
		$sql = "select distinct tagids from knowledge where tagids not like '%,%';";
		$list = $mQuestion->query($sql);
		$key = 'TEMP:BAIKE:TAGS';
		$redis = S(C('REDIS'));
		foreach ( $list as $i => $item ) {
			$ret = $redis->rPush($key, $item['tagids']);
			$list[$i]['cache'] = !!$ret;
		}
		G('db_end');
		$dev['db'] = array(
			'cost' => G('db_start', 'db_end', 3),
			'mem' => G('db_start', 'db_end', 'm'),
		);
		$result = array(
			'status' => true,
			'list' => &$list,
			'debug' => &$dev,
		);
		$this->ajax_return($result);
	}

	public function dev_baike_updatetagids() {
		if ( !$this->_debug ) { die('Access Denied!'); }
		$redis = S(C('REDIS'));
		$listKey = 'TEMP:BAIKE:TAGS';
		$setKey = 'DICT:TAGS:ALLTAGS';

		$mQuestion = D('Knowledge', 'Model', 'Common');

		$debug = I('get.debug', false, 'intval');
		$dev = array();
		$list = array();
		G('update_start');
		$total = $redis->lLen($listKey);
		$_counter = 0;

		while ( ( $debug>$_counter || $debug===false ) && $tags=$redis->lPop($listKey) ) {
			$_counter += 1;
			$_tags = explode(' ', $tags);
			if ( empty($_tags) ) {
				// 更新为空
				$tagids = ',,';
			} else {
				$ids = array();
				foreach ( $_tags as $i => $_tag ) {
					$id = $redis->zScore($setKey, $_tags);
					array_push($ids, $id);
				}
				$tagids = ',' . implode(',', $ids) . ',';
			}
			$where = array('tagids'=>$tags);
			$data = array('tagids'=>$tagids);
			$ret = $mQuestion->where($where)->data($data)->save();
			array_push($list, array(
				't'=>$tags,
				'i'=>$tagids,
				'r'=>!!$ret,
			));
		}
		G('update_end');
		$dev['db'] = array(
			'cost' => G('update_start', 'update_end', 3),
			'mem' => G('update_start', 'update_end', 'm'),
		);

		$result = array(
			'status'=>true,
			'list' =>&$list,
			'debug'=>&$dev,
		);
		$this->ajax_return($result);
	}

	// 对比问答已绑定标签与乐居标签系统中数据的差异
	public function dev_kb_diff_tags() {
		if ( !$this->_debug ) { die('Access Denied!'); }
		$dev = array();

		echo '<h1>1. 从知识数据中获取所有已经设定的标签</h1>', PHP_EOL;
		G('db_start');
		$mQuestion = D('Knowledge', 'Model', 'Common');
		$sql = "select distinct tagids from knowledge where tagids like '% %';";
		$list = $mQuestion->query($sql);
		$old = array();
		foreach ( $list as $i => $item ) {
			$tags = explode(' ', trim($item['tags']));
			foreach ( $tags as $_i => $tag ) {
				$tag = trim($tag);
				if ( $tag=='' ) {
					continue;
				}
				$old[$tag] = $tag;
			}
		}
		unset($list);
		G('db_end');
		$dev['db'] = array(
			'cost' => G('db_start', 'db_end', 3),
			'mem' => G('db_start', 'db_end', 'm'),
		);

		echo '<h1>2. 接口获取全量标签数据</h1>', PHP_EOL;
		G('api_start');
		$lInfos = D('Infos', 'Logic', 'Common');
		$page = 1;
		$pagesize = 10000;
		$ret = $lInfos->getAllTags($page, $pagesize);
		$tags = array();
		foreach ( $ret['list'] as $i => $tag ) {
			$tags[$tag['word']] = $tag['tag_id'];
		}
		G('api_end');
		$dev['api'] = array(
			'cost' => G('api_start', 'api_end', 3),
			'mem' => G('api_start', 'api_end', 'm'),
		);

		echo '<h1>3. 计算知识中设定的标签，不在乐居标签系统的标签数据</h1>', PHP_EOL;
		$diff = array();
		G('diff_start');
		foreach ( $old as $tag => $tagname ) {
			if ( !array_key_exists($tag, $tags) ) {
				$diff[$tag] = $tag;
			}
		}
		G('diff_end');
		$dev['diff'] = array(
			'cost' => G('diff_start', 'diff_end', 3),
			'mem' => G('diff_start', 'diff_end', 'm'),
		);

		echo '<h1>4. 结果输出</h1>', PHP_EOL;
		echo '<b>知识绑定的标签(', count($old), ')</b><hr><pre>', PHP_EOL, var_export($old, true), PHP_EOL, '</pre>', PHP_EOL;
		echo '<b>不在标签系统的标签(', count($diff), ')</b><hr><pre>', PHP_EOL, var_export($diff, true), PHP_EOL, '</pre>', PHP_EOL;
		// echo '<b>标签系统中的数据(', count($ret['list']), ')</b><hr><pre>', PHP_EOL, var_export($ret['list'], true), PHP_EOL, '</pre>', PHP_EOL;
		echo '<!--', PHP_EOL, var_export($dev, true), PHP_EOL, '-->', PHP_EOL;
	}

	public function dev_getesfextra() {
		$flush = I('get.flush_data', 0, 'intval');
		$city = I('get.city', 'bj', 'trim,strtolower');
		if ( $flush==1 ) {
			// 重置缓存
		}

		$extra = D('Extra', 'Logic', 'Common')->getESF($city);
		$this->ajax_return($extra);
	}

	public function dev_test_gettagbyids () {
		$tagids = ',1335,1355,1325,1430,1885,';
		$ret = D('Tags', 'Logic', 'Common')->getTagnamesByTagids($tagids, 'pc');
		$this->ajax_return($ret);
	}

	/**
	 * 批量清理运营需要过滤的信息和数据
	 * 生成处理脚本， 不直接操作删除数据库 @2017-06-09
	 */
	public function dev_cleandata () {
		if ( !$this->_debug ) { die('Access Denied!'); }

		$result = array();

		// 1. 按竞品词数据清洗
		$filters = array('链家','安居客','搜房','房天下','Q房网','搜狐焦点','焦点','楼盘网','吉屋','365淘房','我爱我家','淘房','房产网','房网',);
		// $mQuestion = D('Question', 'Model', 'Common');
		// 问题相关
		$sqls = array();
		foreach ( $filters as $i => $keyword ) {
			$sql = "SELECT count(id) as cnt, '{$keyword}' as 'keyword' FROM `question` WHERE ( `title` LIKE '%{$keyword}%' OR `desc` LIKE '%{$keyword}%' ) AND `status` NOT IN (21,22,23)";
			array_push($sqls, $sql);
		}
		echo implode(PHP_EOL.'UNION ALL'.PHP_EOL, $sqls), PHP_EOL, PHP_EOL;
		// 答案相关
		$sqls = array();
		foreach ( $filters as $i => $keyword ) {
			$sql = "SELECT count(id) as cnt, '{$keyword}' as 'keyword' FROM `answers` WHERE `reply` LIKE '%{$keyword}%' AND `status` NOT IN (21,22,23)";
			array_push($sqls, $sql);
		}
		echo implode(PHP_EOL.'UNION ALL'.PHP_EOL, $sqls), PHP_EOL, PHP_EOL;
		/*
		1187	链家
		80	安居客
		43	搜房
		14	房天下
		4	Q房网
		1	搜狐焦点
		11	焦点
		0	楼盘网
		11	吉屋
		2	365淘房
		105	我爱我家
		3	淘房
		38	房产网
		143	房网
		*/

		// 2. 按广告特征清洗
		/*
		8位及以上连续数字（QQ，电话，手机号）(保留400开头的数字)
		weixin:
		VX:
		微信:
		禁止出现域名（.com .cn .net .org .me .info .mobi等）
		*/
		$sqls = "SELECT count(id) as 'cnt', '手机号' as 'feature' FROM `question` WHERE ( (`title` REGEXP '[[:digit:]]{8,11}' AND `title` NOT REGEXP '400[[:digit:]]{7}') OR (`desc` REGEXP '[[:digit:]]{8,11}' AND `desc` NOT REGEXP '400[[:digit:]]{7}') ) AND `status` NOT IN (21,22,23)".PHP_EOL
			  . "UNION ALL".PHP_EOL
			  . "SELECT count(id) as 'cnt', '微信' as 'feature' FROM `question` WHERE ( `title` REGEXP 'weixin|微信|VX' OR `desc` REGEXP 'weixin|微信|VX' )  AND `status` NOT IN (21,22,23)".PHP_EOL
			  . "UNION ALL".PHP_EOL
			  . "SELECT count(id) as 'cnt', '网址' as 'feature' FROM `question` WHERE ( `title` REGEXP '\.com|\.cn|\.net|\.org|\.me|\.info|\.mobi' OR `desc` REGEXP '\.com|\.cn|\.net|\.org|\.me|\.info|\.mobi' ) AND `status` NOT IN (21,22,23)".PHP_EOL;
		echo $sqls, PHP_EOL, PHP_EOL;
		$sqls = "SELECT count(id) as 'cnt', '手机号' as 'feature' FROM `answers` WHERE (`reply` REGEXP '[[:digit:]]{8,11}' AND `reply` NOT REGEXP '400[[:digit:]]{7}') AND `status` NOT IN (21,22,23)".PHP_EOL
			  . "UNION ALL".PHP_EOL
			  . "SELECT count(id) as 'cnt', '微信' as 'feature' FROM `answers` WHERE `reply` REGEXP 'weixin|微信|VX' AND `status` NOT IN (21,22,23)".PHP_EOL
			  . "UNION ALL".PHP_EOL
			  . "SELECT count(id) as 'cnt', '网址' as 'feature' FROM `answers` WHERE `reply` REGEXP '\.com|\.cn|\.net|\.org|\.me|\.info|\.mobi' AND `status` NOT IN (21,22,23)".PHP_EOL;
		echo $sqls, PHP_EOL, PHP_EOL;
		/*
		1497	手机号
		338	微信
		1362	网址
		*/
	}

	/**
	 * 测试，查看待推送的问答集合
	 */
	public function dev_getPushList() {
		$autoclean = !I('get.autoclean');
		$questions = D('Question', 'Logic', 'Common')->getAllFromPushSet($autoclean);
		$this->ajax_return($questions);
	}

	/**
	 * 推送一条数据到第三方服务
	 * 包括 新闻池 和 搜索服务
	 */
	public function dev_pushQuestion () {
		if ( !$this->_debug ) { die('Access Denied!'); }

		$id = I('get.id', 0, 'intval');
		$_ids = I('get.ids', '', 'trim');
		$_ids = explode(',', $_ids);
		$ids = array();
		// if ( !empty($_ids) ) {
		foreach ( $_ids as $i => &$id ) {
			$id = intval(trim($id));
			if ( $id>0 ) {
				$ids[$id] = $id;
			}
		}
		// }
		if ( $id > 0 ) {
			$ids[$id] = $id;
		}
		// var_dump($id, $_ids, $ids);
		$method = I('get.m', 'id', 'strtolower,trim');

		$methods = array('id'=>'以编号为内容推送', 'entity'=>'以实体为内容推送');
		$method = array_key_exists($method, $methods) ? $method : 'id';
		$method_name = $methods[$method];
		if ( empty($ids) ) {
			echo '<h1>请指定推送数据的编号</h1>', PHP_EOL;
			exit();
		} else {
			echo '<p>要推送的数据为 #<b>', implode(',', $ids), '</b>，推送方式为 : <b>', $method_name, '</b></p>', PHP_EOL;
		}

		$dev = array();
		echo '<h2>0. 待处理的数据提取</h2>', PHP_EOL;
		G('read_start');
		$mQuestion = D('Question', 'Model', 'Common');
		$where = array(
			'status' => array('in', array(21,22,23)), 
			'id' => array('in', array_values($ids)),
		);
		$questions = $mQuestion->where($where)->select();
		echo '<p><pre>', PHP_EOL, var_export($questions, true), PHP_EOL, '</pre></p>', PHP_EOL;
		G('read_end');
		$dev['read'] = array(
			'cost' => G('read_start', 'read_end', 3),
			'mem' => G('read_start', 'read_end', 'm'),
		);

		echo '<h2>1. 推送数据</h2>', PHP_EOL;
		G('push_start');
		$lQuestionPublish = D('QuestionPublish', 'Logic', 'Common');
		if ( $method=='id' ) {
			$ret = $lQuestionPublish->confirmQuestions($ids);
		} else {
			$ret = $lQuestionPublish->confirmQuestions($questions);
		}
		
		$ret2 = $lQuestionPublish->Publish();
		$list = $lQuestionPublish->getData();
		$result = $lQuestionPublish->getError();
		echo '<p><pre>', PHP_EOL, var_export($ret, true), PHP_EOL, '</pre></p>', PHP_EOL;
		echo '<p><pre>', PHP_EOL, var_export($list, true), PHP_EOL, '</pre></p>', PHP_EOL;
		echo '<p><pre>', PHP_EOL, var_export($result, true), PHP_EOL, '</pre></p>', PHP_EOL;
		echo '<p><pre>', PHP_EOL, var_export($ret2, true), PHP_EOL, '</pre></p>', PHP_EOL;
		G('push_end');
		$dev['push'] = array(
			'cost' => G('push_start', 'push_end', 3),
			'mem' => G('push_start', 'push_end', 'm'),
		);

	}

	/**
	 * 测试
	 * 包括 新闻池 和 搜索服务
	 */
	public function dev_pushQuestionAsDelete () {
		if ( !$this->_debug ) { die('Access Denied!'); }

		$ids = array(42175,42128);
		$method = 'id';
		$methods = array('id'=>'以编号为内容推送', 'entity'=>'以实体为内容推送');
		$method = array_key_exists($method, $methods) ? $method : 'id';
		$method_name = $methods[$method];
		echo '<p>要推送的数据为 #<b>', implode(',', $ids), '</b>，推送方式为 : <b>', $method_name, '</b></p>', PHP_EOL;

		$dev = array();
		echo '<h2>0. 待处理的数据提取</h2>', PHP_EOL;
		G('read_start');
		$mQuestion = D('Question', 'Model', 'Common');
		$where = array(
			'id' => array('in', array_values($ids)),
		);
		$questions = $mQuestion->where($where)->select();
		echo '<p><pre>', PHP_EOL, var_export($questions, true), PHP_EOL, '</pre></p>', PHP_EOL;
		G('read_end');
		$dev['read'] = array(
			'cost' => G('read_start', 'read_end', 3),
			'mem' => G('read_start', 'read_end', 'm'),
		);

		echo '<h2>1. 推送数据</h2>', PHP_EOL;
		G('push_start');
		$lQuestionPublish = D('QuestionPublish', 'Logic', 'Common');
		$lQuestionPublish->setAction('delete');
		if ( $method=='id' ) {
			$ret = $lQuestionPublish->confirmQuestions($ids);
		} else {
			$ret = $lQuestionPublish->confirmQuestions($questions);
		}
		
		$lQuestionPublish->Delete();
		$list = $lQuestionPublish->getData();
		$result = $lQuestionPublish->getError();
		echo '<p><pre>', PHP_EOL, var_export($ret, true), PHP_EOL, '</pre></p>', PHP_EOL;
		echo '<p><pre>', PHP_EOL, var_export($list, true), PHP_EOL, '</pre></p>', PHP_EOL;
		echo '<p><pre>', PHP_EOL, var_export($result, true), PHP_EOL, '</pre></p>', PHP_EOL;
		G('push_end');
		$dev['push'] = array(
			'cost' => G('push_start', 'push_end', 3),
			'mem' => G('push_start', 'push_end', 'm'),
		);
	}

	/**
	 * 测试
	 * 包括 新闻池 和 搜索服务
	 */
	public function dev_pushQuestionAsUpdate () {
		if ( !$this->_debug ) { die('Access Denied!'); }

		$ids = array(42175,42128);
		$method = 'entity';
		$methods = array('id'=>'以编号为内容推送', 'entity'=>'以实体为内容推送');
		$method = array_key_exists($method, $methods) ? $method : 'id';
		$method_name = $methods[$method];
		echo '<p>要推送的数据为 #<b>', implode(',', $ids), '</b>，推送方式为 : <b>', $method_name, '</b></p>', PHP_EOL;

		$dev = array();
		echo '<h2>0. 待处理的数据提取</h2>', PHP_EOL;
		G('read_start');
		$mQuestion = D('Question', 'Model', 'Common');
		$where = array(
			'status' => array('in', array(21,22,23)), 
			'id' => array('in', array_values($ids)),
		);
		$questions = $mQuestion->where($where)->select();
		echo '<h3>Origin</h3><p><pre>', PHP_EOL, var_export($questions, true), PHP_EOL, '</pre></p>', PHP_EOL;
		foreach ( $questions as $i => &$question ) {
			$question['i_hits'] = rand(5, 100);
		}
		echo '<h3>Fixxed</h3><p><pre>', PHP_EOL, var_export($questions, true), PHP_EOL, '</pre></p>', PHP_EOL;
		G('read_end');
		$dev['read'] = array(
			'cost' => G('read_start', 'read_end', 3),
			'mem' => G('read_start', 'read_end', 'm'),
		);

		echo '<h2>1. 推送数据</h2>', PHP_EOL;
		G('push_start');
		$lQuestionPublish = D('QuestionPublish', 'Logic', 'Common');
		$lQuestionPublish->setAction('update');
		if ( $method=='id' ) {
			$ret = $lQuestionPublish->confirmQuestions($ids);
		} else {
			$ret = $lQuestionPublish->confirmQuestions($questions);
		}
		
		$lQuestionPublish->Publish();
		$list = $lQuestionPublish->getData();
		$result = $lQuestionPublish->getError();
		echo '<p><pre>', PHP_EOL, var_export($ret, true), PHP_EOL, '</pre></p>', PHP_EOL;
		echo '<p><pre>', PHP_EOL, var_export($list, true), PHP_EOL, '</pre></p>', PHP_EOL;
		echo '<p><pre>', PHP_EOL, var_export($result, true), PHP_EOL, '</pre></p>', PHP_EOL;
		G('push_end');
		$dev['push'] = array(
			'cost' => G('push_start', 'push_end', 3),
			'mem' => G('push_start', 'push_end', 'm'),
		);
	}


	// ==== --- === --- === --- ====

	/**
	 * 指定问题编号，修复问题参数数据
	 */
	public function fixQuestion() {
		$qid = I('get.id', 0, 'intval');
		if ( $qid <= 0 ) {
			$this->ajax_error('参数错误');
		}

		// 清理缓存
		$lQuestion = D('Question', 'Logic', 'Common');
		$question = $lQuestion->cleanAnswersIfQuestionNotExists($qid);
		// 自动修正问题数据
		$lQuestion->fixQuestionData($qid, true);
		// 清理问题详情缓存
		$lQuestion->flushCache($qid);
		$result = array(
			'status' => true,
			'reason' => '问题数据修复成功',
			'id' => $qid,
		);
		$this->ajax_return($result);
	}

	/**
	 * 修复新闻池数据
	 */
	public function fixQuestionInfo() {
		// 1. 从新闻池查询问答数据的总量
		$lInfos = D('Infos', 'Logic', 'Common');
		$total = $lInfos->getPushedInfoCount(array(), 'question');

		// 2. 处理当前页码的数据集合，进行数据修复
		$pagesize = I('get.pagesize', 1000, 'intval');;
		$page = I('get.page', 1, 'intval');
		$pagecount = ceil($total/$pagesize);
		if ( $page < 1 || $page > $pagecount ) {
			$this->ajax_error('没有指定数据');
		}

		$logic_pk_field = 'wiki_id';
		$opts = array();
		$ret = $lInfos->getPushedInfoList ( 'question', $page, $pagesize, $logic_pk_field, '{id}desc', $opts );
		if ( !$ret ) {
			$this->ajax_error('数据获取失败');
		}
		$question_ids = array();
		foreach ( $ret['list'] as $i => $item ) {
			array_push($question_ids, $item[$logic_pk_field]);
		}
		$lQuestionPublish = D('QuestionPublish', 'Logic', 'Common');
		$ret = $lQuestionPublish->Publish($question_ids);
		$err = $lQuestionPublish->getError();
		if ( !$ret ) {
			$this->ajax_error($err['messages'][0]);
		}

		// 3. 返回处理结果
		$result = array('status'=>true);
		$result['pager'] = array(
			'page' => $page, 
			'pagesize' => $pagesize,
			'total' => $total,
			'pagecount' => $pagecount,
		);
		$this->ajax_return($result);
	}

	public function cacheAllTags() {
		$lInfos = D('Infos', 'Logic', 'Common');
		$redis = S(C('REDIS'));
		$page = 1;
		$pagesize = 5000;
		do {
			$tags = $lInfos->getAllTags($page, $pagesize);
			$page += 1;
		} while ( $tags['total']>0 );
	}

	/**
	 * 修正问答标签数据
	 *
	 */
	public function updateAskTags() {
		if ( !$this->_debug ) { die('Access Denied!'); }
		$dev = array();

		echo '<h1>1. 从问答数据中获取所有已经设定的标签</h1>', PHP_EOL;
		G('db_start');
		$mQuestion = D('Question', 'Model', 'Common');
		$sql = "select distinct tags from question;";
		$list = $mQuestion->query($sql);
		$old = array();
		foreach ( $list as $i => $item ) {
			$tags = explode(' ', trim($item['tags']));
			foreach ( $tags as $_i => $tag ) {
				$tag = trim($tag);
				if ( $tag=='' ) {
					continue;
				}
				$old[$tag] = $tag;
			}
		}
		G('db_end');
		$dev['db'] = array(
			'cost' => G('db_start', 'db_end', 3),
			'mem' => G('db_start', 'db_end', 'm'),
		);

		echo '<h1>2. 接口获取全量标签数据</h1>', PHP_EOL;
		G('api_start');
		$lInfos = D('Infos', 'Logic', 'Common');
		$page = 1;
		$pagesize = 10000;
		$ret = $lInfos->getAllTags($page, $pagesize);
		if ( !$ret ) {
			echo '<h1>ERROR: 接口获取操作失败</h1>', PHP_EOL;
			echo '<p>更新操作将不会继续执行!!!</p>', PHP_EOL;
			exit;
		} 
		$tags = array();
		foreach ( $ret['list'] as $i => $tag ) {
			$tags[$tag['word']] = $tag['tag_id'];
		}
		G('api_end');
		$dev['api'] = array(
			'cost' => G('api_start', 'api_end', 3),
			'mem' => G('api_start', 'api_end', 'm'),
		);

		echo '<h1>3. 计算问答中设定的标签，获取在乐居标签系统的标签数据</h1>', PHP_EOL;
		$exists = array();
		G('exists_start');
		foreach ( $old as $tag => $tagname ) {
			if ( array_key_exists($tag, $tags) ) {
				$exists[$tag] = intval($tags[$tag]);
			}
			// echo '<p> ', $tag, ' exists( ', array_key_exists($tag, $tags), ' )</p>', PHP_EOL;
		}
		G('exists_end');
		$dev['exists'] = array(
			'cost' => G('exists_start', 'exists_end', 3),
			'mem' => G('exists_start', 'exists_end', 'm'),
		);

		echo '<h1>4. 计算问答中原有标签对应的tagid，并进行替换</h1>', PHP_EOL;
		G('replace_start');
		$replaced = array();
		foreach ( $list as $i => $tags ) {
			$tagids = array();
			$ref = array();
			$_tags = explode(' ', trim($tags['tags']));
			foreach ( $_tags as $_i => &$_tag ) {
				$_tag = trim($_tag);
				if ( array_key_exists($_tag, $exists) ) {
					array_push($tagids, $exists[$_tag]);
					$ref[$exists[$_tag]] = $_tag;
				}
			}
			array_push($replaced, array(
				'old'=>$tags['tags'], 
				'new'=>','.implode(',', $tagids).',',
				'ref'=>$ref,
			));
		}
		// 生成 SQL 更新脚本
		$sqls = array();
		foreach ( $replaced as $i => $item ) {
			$sql = "UPDATE `question` SET `tagids`='{$item['new']}' WHERE `tags`='{$item['old']}';";
			array_push($sqls, $sql);
		}
		echo '<b>更新脚本</b><hr><pre>', PHP_EOL, implode(PHP_EOL, $sqls), PHP_EOL, '</pre>', PHP_EOL;
		G('replace_end');
		$dev['replace'] = array(
			'cost' => G('replace_start', 'replace_end', 3),
			'mem' => G('replace_start', 'replace_end', 'm'),
		);

		echo '<h1>5. 结果输出</h1>', PHP_EOL;
		echo '<b>问答绑定的标签(', count($old), ')</b><hr><pre>', PHP_EOL, var_export($old, true), PHP_EOL, '</pre>', PHP_EOL;
		echo '<b>在标签系统的标签(', count($exists), ')</b><hr><pre>', PHP_EOL, var_export($exists, true), PHP_EOL, '</pre>', PHP_EOL;
		echo '<b>待替换的标签数据(', count($replaced), ')</b><hr><pre>', PHP_EOL, var_export($replaced, true), PHP_EOL, '</pre>', PHP_EOL;
		// echo '<b>标签系统中的数据(', count($ret['list']), ')</b><hr><pre>', PHP_EOL, var_export($ret['list'], true), PHP_EOL, '</pre>', PHP_EOL;
		echo '<!--', PHP_EOL, var_export($dev, true), PHP_EOL, '-->', PHP_EOL;
	}


	/**
	 * 修正知识标签数据
	 *
	 */
	public function updateKnowledgeTags() {
		if ( !$this->_debug ) { die('Access Denied!'); }
		$dev = array();

		echo '<h1>1. 从问答数据中获取所有已经设定的标签</h1>', PHP_EOL;
		G('db_start');
		$mQuestion = D('Question', 'Model', 'Common');
		$sql = "select distinct tags from knowledge;";
		$list = $mQuestion->query($sql);
		$old = array();
		foreach ( $list as $i => $item ) {
			$tags = explode(' ', trim($item['tags']));
			foreach ( $tags as $_i => $tag ) {
				$tag = trim($tag);
				if ( $tag=='' ) {
					continue;
				}
				$old[$tag] = $tag;
			}
		}
		G('db_end');
		$dev['db'] = array(
			'cost' => G('db_start', 'db_end', 3),
			'mem' => G('db_start', 'db_end', 'm'),
		);

		echo '<h1>2. 接口获取全量标签数据</h1>', PHP_EOL;
		G('api_start');
		$lInfos = D('Infos', 'Logic', 'Common');
		$page = 1;
		$pagesize = 10000;
		$ret = $lInfos->getAllTags($page, $pagesize);
		if ( !$ret ) {
			echo '<h1>ERROR: 接口获取操作失败</h1>', PHP_EOL;
			echo '<p>更新操作将不会继续执行!!!</p>', PHP_EOL;
			exit;
		} 
		$tags = array();
		foreach ( $ret['list'] as $i => $tag ) {
			$tags[$tag['word']] = $tag['tag_id'];
		}
		G('api_end');
		$dev['api'] = array(
			'cost' => G('api_start', 'api_end', 3),
			'mem' => G('api_start', 'api_end', 'm'),
		);

		echo '<h1>3. 计算知识中设定的标签，获取在乐居标签系统的标签数据</h1>', PHP_EOL;
		$exists = array();
		G('exists_start');
		foreach ( $old as $tag => $tagname ) {
			if ( array_key_exists($tag, $tags) ) {
				$exists[$tag] = intval($tags[$tag]);
			}
			// echo '<p> ', $tag, ' exists( ', array_key_exists($tag, $tags), ' )</p>', PHP_EOL;
		}
		G('exists_end');
		$dev['exists'] = array(
			'cost' => G('exists_start', 'exists_end', 3),
			'mem' => G('exists_start', 'exists_end', 'm'),
		);

		echo '<h1>4. 计算知识中原有标签对应的tagid，并进行替换</h1>', PHP_EOL;
		G('replace_start');
		$replaced = array();
		foreach ( $list as $i => $tags ) {
			$tagids = array();
			$ref = array();
			$_tags = explode(' ', trim($tags['tags']));
			foreach ( $_tags as $_i => &$_tag ) {
				$_tag = trim($_tag);
				if ( array_key_exists($_tag, $exists) ) {
					array_push($tagids, $exists[$_tag]);
					$ref[$exists[$_tag]] = $_tag;
				}
			}
			array_push($replaced, array(
				'old'=>$tags['tags'], 
				'new'=>','.implode(',', $tagids).',',
				'ref'=>$ref,
			));
		}
		// 生成 SQL 更新脚本
		$sqls = array();
		foreach ( $replaced as $i => $item ) {
			$sql = "UPDATE `knowledge` SET `tagids`='{$item['new']}' WHERE `tags`='{$item['old']}' AND `tagids` not like ',%';";
			array_push($sqls, $sql);
		}
		echo '<b>更新脚本</b><hr><pre>', PHP_EOL, implode(PHP_EOL, $sqls), PHP_EOL, '</pre>', PHP_EOL;
		G('replace_end');
		$dev['replace'] = array(
			'cost' => G('replace_start', 'replace_end', 3),
			'mem' => G('replace_start', 'replace_end', 'm'),
		);

		echo '<h1>5. 结果输出</h1>', PHP_EOL;
		echo '<b>知识绑定的标签(', count($old), ')</b><hr>', PHP_EOL;
		echo '<b>在标签系统的标签(', count($exists), ')</b><hr>', PHP_EOL;
		echo '<b>待替换的标签数据(', count($replaced), ')</b><hr>', PHP_EOL;
		// echo '<b>知识绑定的标签(', count($old), ')</b><hr><pre>', PHP_EOL, var_export($old, true), PHP_EOL, '</pre>', PHP_EOL;
		// echo '<b>在标签系统的标签(', count($exists), ')</b><hr><pre>', PHP_EOL, var_export($exists, true), PHP_EOL, '</pre>', PHP_EOL;
		// echo '<b>待替换的标签数据(', count($replaced), ')</b><hr><pre>', PHP_EOL, var_export($replaced, true), PHP_EOL, '</pre>', PHP_EOL;
		// echo '<b>标签系统中的数据(', count($ret['list']), ')</b><hr><pre>', PHP_EOL, var_export($ret['list'], true), PHP_EOL, '</pre>', PHP_EOL;
		echo '<!--', PHP_EOL, var_export($dev, true), PHP_EOL, '-->', PHP_EOL;
	}


	public function syncWikiToDB() {
		$step = I('get.step', 1, 'intval');
		$dev = array();

		$sqls = array();
		$mWiki = D('Wiki', 'Model', 'Common');
		G('read_start');
		// 1. read wiki data from service (huanyu)
		$api = C('DATA_TRANSFER_API_URL').'api/item';
		$params = array(
			'mode' => 1,
			'search' => 1,
			'check' => 2,
			'page' => 1,
			'pagesize' => 5,
			'state' => 3,
		);
		$result = curl_get($api, $params);
		if ( !$result['status'] ) {
			$this->ajax_error('error: read wiki data');
		}
		$result = json_decode($result['result'], true);
		$list = $result['result'];
		$wiki = array();
		foreach ( $list as $i => $item ) {
			$id = $item['checkid']!='' ? $item['checkid'] : $item['id'];
			$info_api = C('DATA_TRANSFER_API_URL') . 'api/item';
			$info_params = array('id'=>$id, 'mode'=>1);
			$info = curl_get($info_api, $info_params);
			$info = json_decode($info['result'], true);
			$list[$i] = array_merge($list[$i], $info['result'][0]);

			$_cur = &$list[$i];
			$exists = $mWiki->field('id')
					->where(array('title'=>trim($_cur['entry'])))
					->find();
			if ( $exists ) {
				continue;
			}

			$wiki[$i] = array();
			$wiki[$i]['version'] = intval($_cur['updatetime']);
			$wiki[$i]['status'] = intval($_cur['state']);
			// switch ( intval($_cur['state']) ) {
			// 	case 0:		$wiki[$i]['status'] = 0; break; // 未发布
			// 	case 1:		$wiki[$i]['status'] = 1; break; // 草稿
			// 	case 2:		$wiki[$i]['status'] = 2; break; // 定时发布
			// 	case 3:		$wiki[$i]['status'] = 9; break; // 已发布
			// 	case 4:		$wiki[$i]['status'] = -1; break; // 已删除
			// 	default:	$wiki[$i]['status'] = 0; break; // 默认 未发布
			// }
			$wiki[$i]['hits'] = intval($_cur['hits']);
			$wiki[$i]['title'] = trim($_cur['entry']);
			$wiki[$i]['pinyin'] = strtolower(trim($_cur['pinyin']));
			$wiki[$i]['firstletter'] = strtoupper(substr(trim($_cur['pinyin']), 0, 1));
			$wiki[$i]['content'] = trim($_cur['content']);
			$wiki[$i]['cateid'] = intval($_cur['category']);
			$wiki[$i]['cover'] = trim($_cur['pic']);
			$wiki[$i]['coverinfo'] = '';

			$wiki[$i]['editorid'] = intval($_cur['creatorid']);
			$wiki[$i]['editor'] = isset($_cur['creator']) ? trim($_cur['creator']) : '';
			$wiki[$i]['meida'] = isset($_cur['media']) ? trim($_cur['media']) : '';
			// $wiki[$i]['summary'] = intval($_cur['brief']);
			// $wiki[$i]['seo_description'] = intval($_cur['description']);
			// $wiki[$i]['seo_keywords'] = implode(',', $_cur['keywords']);
			
			if ( !empty($_cur['recommend'][0]) ) { // focus 首页
				$wiki[$i]['focus_title'] = trim($_cur['recommend'][0]['title']);
				$wiki[$i]['focus_pic'] = trim($_cur['recommend'][0]['pic']);
				$wiki[$i]['focus_time'] = intval($_cur['updatetime']);
			}
			if ( !empty($_cur['recommend'][1]) ) { // celebrity 首页名人
				$wiki[$i]['celebrity_title'] = trim($_cur['recommend'][1]['title']);
				$wiki[$i]['celebrity_pic'] = trim($_cur['recommend'][1]['pic']);
				$wiki[$i]['celebrity_time'] = intval($_cur['updatetime']);
			}
			if ( !empty($_cur['recommend'][2]) ) { // company 首页名企
				$wiki[$i]['company_title'] = trim($_cur['recommend'][2]['title']);
				$wiki[$i]['company_pic'] = trim($_cur['recommend'][2]['pic']);
				$wiki[$i]['company_time'] = intval($_cur['updatetime']);
			}
			$wiki[$i]['ctime'] = intval($_cur['category']);
			$wiki[$i]['utime'] = intval($_cur['updatetime']);
			$wiki[$i]['ptime'] = intval($_cur['releasetime']);
			
			$list[$i]['tags'] = isset($_cur['tags']) ? $_cur['tags'] : array();
			$wiki[$i]['tags'] = implode(' ', $_cur['tags']);
			$wiki[$i]['tagids'] = implode(' ', $_cur['tags']);

			$wiki[$i]['src_type'] = intval($_cur['from']);
 			$wiki[$i]['src_url'] = trim($_cur['fromurl']);
			$wiki[$i]['rel_news'] = json_encode(isset($_cur['news'])?$_cur['news']:array());
			$wiki[$i]['rel_house'] = json_encode(isset($_cur['house'])?$_cur['house']:array());

			var_dump($wiki[$i], $_cur);
			echo '<hr>';
		}
		G('read_end');

		$dev['read'] = array(
			'cost' => G('read_start', 'read_end', 3),
			'mem' => G('read_start', 'read_end', 'm'),
		);

		echo '<!--', PHP_EOL, var_export($dev, true), PHP_EOL, '-->', PHP_EOL;
		// 2. save wiki data to db
		// 3. push published wiki data to service and info
	}

	/**
	 * 推送问答数据到新闻池
	 */
	public function syncDataToInfo() {
		if ( !$this->_debug ) { die('Access Denied!'); }

		$page = I('get.page', 0, 'intval');
		$pagesize = I('get.pagesize', 500, 'trim');

		$id = I('get.id', 0, 'intval');

		$result = array(
			'status'=>false,
			'msg' => array(),
		);
		array_push($result['msg'], '0. 待处理的数据提取');
		G('read_start');
		$mQuestion = D('Question', 'Model', 'Common');
		$fields = array('id');
		if ( $id > 0 ) {
			$where = array('id'=>$id);
			$questions = $mQuestion->field($fields)->where($where)->page($page, $pagesize)->select();
			$count = empty($questions) ? 0 : 1;
			$pager = false;
		} else {
			$where = array(
				'id'=>array('gt', 0),
				'status'=>array('in', array(21,22,23)),
			);
			$order = 'id desc';
			$count = $mQuestion->where($where)->count();
			$questions = $mQuestion->field($fields)->where($where)->order($order)->page($page, $pagesize)->select();
			$pager = array(
				'page' => $page,
				'size' => $pagesize,
				'total' => $count,
				'count' => ceil($count / $pagesize),
			);
		}

		$result['pager'] = &$pager;
		$ids = array();
		foreach ( $questions as $inx => $question ) {
			array_push($ids, $question['id']);
		}
		array_push($result['msg'], implode(',', $ids));
		// echo '<p><pre>', PHP_EOL, var_export(implode(',', $ids), true), PHP_EOL, '</pre></p>', PHP_EOL;
		// echo '<p><pre>', PHP_EOL, var_export($pager, true), PHP_EOL, '</pre></p>', PHP_EOL;
		G('read_end');
		$dev['read'] = array(
			'cost' => G('read_start', 'read_end', 3),
			'mem' => G('read_start', 'read_end', 'm'),
		);

		array_push($result['msg'], '1. 推送数据');
		G('push_start');
		$lQuestionPublish = D('QuestionPublish', 'Logic', 'Common');
		$lQuestionPublish->confirmQuestions($ids);
		$lQuestionPublish->Publish();
		$list = $lQuestionPublish->getData();
		$ret = $lQuestionPublish->getError();
		if ( !$result['status'] ) {
			$result['status'] = true;
		}
		array_push($result['msg'], array('origin'=>count($list['origin']), 'info'=>count($list['info'])) );
		// echo '<p><pre>', PHP_EOL, var_export($ret, true), PHP_EOL, '</pre></p>', PHP_EOL;
		// echo '<p><pre>', PHP_EOL, var_export(array('origin'=>count($list['origin']), 'info'=>count($list['info'])), true), PHP_EOL, '</pre></p>', PHP_EOL;
		// echo '<p><pre>', PHP_EOL, var_export($result, true), PHP_EOL, '</pre></p>', PHP_EOL;
		G('push_end');
		$dev['push'] = array(
			'cost' => G('push_start', 'push_end', 3),
			'mem' => G('push_start', 'push_end', 'm'),
		);
		$result['dev'] = $dev;
		// echo '<!--', PHP_EOL, var_export($dev, true), PHP_EOL, '-->', PHP_EOL;
		$this->ajax_return($result);
	}

	// ==== --- === --- === --- ====

	/**
	 * 联想词数据接口
	 * @@action - ajax
	 */
	public function suggest() {
		$keyword = I('get.k', '', 'trim');
		// $keyword = filterInput($keyword);
		if ( $keyword=='' ) {
			$this->ajax_error('请指定联想关键词');
		}

		$_device = $this->_device == 'pc' ? 'pc' : 'touch';

		$limit = I('get.n', 5, 'intval');
		$engine = D('Search', 'Logic', 'Common');

		// 获取匹配知识标题前缀的数据
		$opts = array(
			array('false', '_deleted'),
			// array("{$this->_city['cn']},全国",'_scope'),
		);
		// $prefix = array(array($keyword, "_multi.title_prefix"));
		$page = 1;
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_scope','_origin.content');
		$search = $engine->select($page, $limit, $keyword, $opts, $prefix, $order, $fields, 0, 'question');
		// var_dump($page, $limit, $opts, $prefix, $order, $fields, $search);
		$ask_list = array();
		foreach ($search['list'] as $key => $value) {
			array_push($ask_list, array(
				'id'=>$value['_id'],
				// 'title'=>str_replace($keyword, "<em>{$keyword}</em>", $value['_title']),
				'title'=>$value['_title'],
				'url' => url('show', array($value['_id']), $_device, 'ask'),
			));
		}
		
		// 获取匹配知识标题前缀的数据
		$opts = array(
			array('false', '_deleted'),
			// array("{$this->_city['cn']},全国",'_scope'),
		);
		// $prefix = array(array($keyword, "_multi.title_prefix"));
		$page = 1;
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_scope','_origin.content');
		$search = $engine->select($page, $limit, $keyword, $opts, $prefix, $order, $fields, 0, 'knowledge');
		$kb_list = array();
		foreach ($search['list'] as $key => $value) {
			array_push($kb_list, array(
				'id'=>$value['_id'],
				// 'title'=>str_replace($keyword, "<em>{$keyword}</em>", $value['_title']),
				'title'=>$value['_title'],
				'url' => url('show', array($value['_id']), $_device, 'baike'),
			));
		}
		$result = array(
			'status' => true,
			'list' => array(
				'ask' => $ask_list,
				'baike' => $kb_list,
			),
		);
		$this->ajax_return($result);
	}

	/**
	 * 猜你喜欢数据接口
	 * @@action - ajax
	 */
	public function guess() {
		$max_tag = 4;
		$lPage = D('Page', 'Logic');
		$result = $lPage
					->setFlush($this->_flush['data'])
					->hot_tags($max_tag);

		$this->ajax_return($result);
	}

	/**
	 * 相关知识数据接口
	 * @@action - ajax
	 */
	public function relations() {
		$tag = I('get.tag', 0, 'intval');
		if ( $tag<=0 ) {
			$this->ajax_error('请指定标签');
		}
		$lTags = D('Tags', 'Logic', 'Common');
		$info = $lTags->getTagnameByTagid($tag);
		if ( !$info ) {
			$this->ajax_error('没有指定标签');
		}

		$result = array(
			'status' => true,
		);

		$tag = $info['name'];
		$pagesize = 8;
		$lPage = D('Page', 'Logic');
		$ret = $lPage->relations($tag, $pagesize);
		if ( !$ret ) {
			// todo: 获取垫底数据
		}
		$result['list'] = $ret;
		$this->ajax_return($result);
	}


	/**
	 * 标签联想数据接口
	 * @@action - ajax
	 */
	public function tag() {
		$keyword = I('get.k', '', 'trim');
		$pagesize = I('get.ps', 5, 'intval');
		if ( $keyword=='' ) {
			$this->ajax_error('请指关键词');
		}

		$result = array(
			'status'=>false,
			'msg'=>'失败',
			'list'=>array(),
		);
		$engine = D('Search', 'Logic', 'Common');
		$list = $engine->suggest($keyword, $pagesize);

		if ($list) {
			$result['status'] = true;
			$result['msg'] = '成功';
			$result['list'] = $list;
		}
		$this->ajax_return($result);
	}

	/**
	 * 关注问题操作接口
	 * @@action - ajax
	 */
	public function attention() {
		if ( !$this->_islogined ) {
			$this->ajax_error('请登录后再进行关注操作');
		}
		// $act = I('get.act', 1, 'intval');
		// $act = $act > 1 ? 1 : $act;
		// $act = $act < 0 ? 0 : $act;
		// $msg = $act==1 ? '关注' : '取消关注';
		$id = I('get.qid', 0, 'intval');
		if ( $id<=0 ) {
			$this->ajax_error('请指定要'.$msg.'的问题编号');
		}
		$userid = $this->_userid;
		$uuid = $this->_browserid;
		$mOplogs = D('Oplogs', 'Model', 'Common');
		if ( $userid > 0 ) {
			$where = array('uid'=>$userid, 'act'=>11, 'relid'=>$id);
		} else {
			$where = array('uuid'=>$uuid, 'act'=>11, 'relid'=>$id);
		}
		$exists = $mOplogs->where($where)->count();
		$mQuestion = D('Question', 'Model', 'Common');

		if ( $exists>0 ) {
			$msg = '取消关注';
			// if ( $act==1 ) {
			// 	$this->ajax_error('您已经关注过此问题，不能再次关注了');
			// }
			$ret = $mOplogs->where($where)->delete();
			$mQuestion->where(array('id'=>$id))->setDec('i_attention', 1);
		} else {
			$msg = '关注';
			// if ( $act!=1 ) {
			// 	$this->ajax_error('您还没有关注过此问题，不能取消关注');
			// }
			$ret = $mOplogs->data(array('uid'=>$userid, 'uuid'=>$uuid, 'act'=>11, 'relid'=>$id, 'ctime'=>NOW_TIME))->add();
			$mQuestion->where(array('id'=>$id))->setInc('i_attention', 1);
		}
		if ( !$ret ) {
			$this->ajax_error($msg.'失败');
		}

		// 清理缓存
		$lQuestion = D('Question', 'Logic', 'Common');
		$lQuestion->flushCache($id);

		$result = array(
			'status' => true,
			'reason' => $msg.'成功',
		);
		$this->ajax_return($result);
	}

	/**
	 * 回复点赞操作接口
	 * @@action - ajax
	 */
	public function good() {
		$id = I('get.aid', 0, 'intval');
		if ( $id<=0 ) {
			$this->ajax_error('请指定要点赞的答案编号');
		}

		$mAnswers = D('Answer', 'Model', 'Common');
		$answer = $mAnswers->find($id);
		if ( !$answer ) {
			$this->ajax_error('请指定正常的回复编号');
		}
		if ( !in_array(intval($answer['status']), array(21,22,23)) ) { // 回复为正常、置顶状态或已采纳
			$this->ajax_error('请指定正常的回复编号.');
		}

		/*
		// 未登录，只累计统计数据
		if ( !$this->_islogined ) {
			$ret = $mAnswers->where(array('id'=>$id))->setInc('i_good', 1);
			if ( $ret ) {
				$result = array(
					'status' => true,
					'reason' => '点赞成功',
				);
				$this->ajax_return($result);
			} else {
				$this->ajax_error('点赞失败');
			}
			// $this->ajax_error('请登录后再进行点赞操作');
		}
		*/

		$qid = intval($answer['qid']);

		$userid = $this->_userid;
		$uuid = $this->_browserid;
		$mOplogs = D('Oplogs', 'Model', 'Common');
		$where = array('uid'=>$userid, 'uuid'=>$uuid, 'act'=>31, 'relid'=>$id);
		$exists = $mOplogs->where($where)->count();
		if ( $exists>0 ) {
			$act_name = '取消点赞';
			// $this->ajax_error('您对此回复，您已经点过赞了');
			$ret = $mOplogs->where($where)->delete();
			$ret = $mAnswers->where(array('id'=>$id))->setDec('i_good', 1);

			// 清理缓存
			$lQuestion = D('Question', 'Logic', 'Common');
			$lQuestion->flushCache($qid);

			$result = array(
				'status' => true,
				'reason' => $act_name.'成功',
			);
			$this->ajax_return($result);
		} else {
			$act_name = '点赞';
			$ret = $mOplogs->data(array('uid'=>$userid, 'uuid'=>$uuid, 'act'=>31, 'relid'=>$id, 'ctime'=>NOW_TIME))->add();
			$ret = $mAnswers->where(array('id'=>$id))->setInc('i_good', 1);

			// 清理缓存
			$lQuestion = D('Question', 'Logic', 'Common');
			$lQuestion->flushCache($qid);

			$result = array(
				'status' => true,
				'reason' => $act_name.'成功',
			);
			$this->ajax_return($result);
		}
		$this->ajax_error($act_name.'失败');
	}

	/**
	 * 设置最佳答案操作接口
	 * @@action - ajax
	 */
	public function best() {
		if ( !$this->_islogined ) {
			$this->ajax_error('请登录后再进行最佳答案设置操作');
		}
		$id = I('get.aid', 0, 'intval');
		if ( $id<=0 ) {
			$this->ajax_error('请指定最佳答案的编号');
		}
		$mAnswers = D('Answer', 'Model', 'Common');
		$answer = $mAnswers->find($id);
		if ( !$answer ) {
			$this->ajax_error('请指定正常的回复编号');
		}
		$mQuestion = D('Question', 'Model', 'Common');
		$where = array('id'=>$answer['qid'], 'status'=>array('in', array(21,22,23)));
		$question = $mQuestion->where($where)->find();
		if ( !$question ) {
			$this->ajax_error('回复信息的提问存在异常，操作失败');
		}
		$userid = $this->_userid;
		$question['userid'] = intval($question['userid']);
		if ( $question['userid']!=$userid ) {
			$this->ajax_error('采纳答案只能由提问者进行操作');
		}
		if ( $question['last_best']>0 || $question['status']==23 ) {
			$this->ajax_error('此回复已经被问题采纳');
		}
		if ( $answer['is_best']>0 || intval($answer['status'])==23 ) {
			$this->ajax_error('当前回复已经被采纳');
		}
		if ( !in_array(intval($answer['status']), array(21,22)) ) { // 回复为正常或置顶状态
			$this->ajax_error('请指定正常的回复编号');
		}
		if ( $question['last_best']!=0 && $question['last_best']!=$id ) {
			$this->ajax_error('此问题已经采纳了其它回答');
		}

		// $mOplogs = D('Oplogs', 'Model', 'Common');
		// $where = array('uid'=>$userid, 'act'=>21, 'relid'=>$id);
		// $exists = $mOplogs->where($where)->count();
		// if ( $exists>0 ) {
		// 	$this->ajax_error('您对此回复，您已经点过赞了');
		// } else {
		//	 $ret = $mOplogs->data(array('uid'=>$userid, 'act'=>21, 'relid'=>$id, 'ctime'=>NOW_TIME))->add();
		//	 if ( $ret ) {
				$ret = $mAnswers->where(array('id'=>$id))->data(array('utime'=>NOW_TIME, 'is_best'=>NOW_TIME, 'status'=>23))->save();
				if ( $ret ) {
					$answer_uid = intval($answer['userid']);
					$lPage = D('Page', 'Logic');
					$professors = $lPage->load_professors();
					$professor = false;
					if ( array_key_exists($answer_uid, $professors) ) {
						$professor = $professors[$answer_uid];
					}

					$ret = $mQuestion->where(array('id'=>$answer['qid']))->data(array('status'=>23, 'last_best'=>$id))->save();

					// 清理缓存
					$lQuestion = D('Question', 'Logic', 'Common');
					$lQuestion->flushCache($answer['qid']);

					if ( $ret ) {
						$result = array(
							'status' => true,
							'reason' => '最佳答案设置成功',
							'professor' => $professor,
						);
						$this->ajax_return($result);
					}
				}
		//	 }
		// }
		$this->ajax_error('最佳答案设置失败');
	}

	/**
	 * 获取问题答案列表操作接口
	 * @@action - ajax
	 * ~~废弃~~
	 */
	public function answers() {
		$this->ajax_error('此接口不再使用');
		$id = I('get.qid', 0, 'intval');
		if ( $id <= 0 ) {
			$this->ajax_error('请指定问题编号');
		}

		$page = I('get.page', 1, 'intval');
		if ( $page<=1 ) { $page = 1; }
		$pagesize = I('get.ps', 10, 'intval');

		$order = I('get.order', '', 'strtolower,trim');
		$orders = array(''=>'ctime desc', 'hot'=>'i_good desc');
		if ( !array_key_exists($order, $orders) ) {
			$order = '';
		}
		$order = $orders[$order];

		$mAnswers = D('Answer', 'Model', 'Common');
		$where = array(
			'qid' => $id,
			'status' => array('in', array(21,22)),
		);
		$order = array('status desc', $order);
		$total = $mAnswers->where($where)->count();
		$ret = $mAnswers->where($where)->order($order)->page($page, $pagesize)->select();
		$answers = array();
		foreach ( $ret as $i => $_item ) {
			$item = array(
				'aid' => intval($_item['id']),
				'usernick' => $_item['usernick'],
				'ctime' => date('Y年m月d日', $_item['ctime']),
				'reply' => $_item['reply'],
				'anonymous' => intval($_item['anonymous']),
				'i_good' => intval($_item['i_good']),
				'status' => intval($_item['status']),
			);
			array_push($answers, $item);
		}

		$pager = array(
			'total'=>$total,
			'pagesize'=>$pagesize,
			'page'=>$page,
			'pagecount'=>ceil($total/$pagesize),
		);
		$result = array('status'=>true, 'reason'=>'success', 'pager'=>$pager, 'list'=>$answers);
		$this->ajax_return($result);
	}

	/**
	 * 对问题进行回复
	 * @@action - ajax
	 */
	public function reply() {
		if ( !$this->_islogined ) {
			$this->ajax_error('请登录后再进行回复操作');
		}

		$result = array(
			'status'=>true, 'info'=>'success', 'reason'=>'回答成功', 'aid'=>false,
			'debug'=>array(),
		);
		$result['debug']['cost'] = array();

		G('checking_start');
		$_device = $this->_device == 'pc' ? 'pc' : 'touch';
		$userid = $this->_userid;

		$form = I('request.');
		$fields = array('qid'=>'', 'reply'=>'', 'anonymous'=>'');
		$form = array_intersect_key($form, $fields);

		$qid = intval($form['qid']);
		if ( $qid == 0 ) {
			$this->ajax_error('请指定要回答的问题编号 qid');
		}
		$mQuestion = D('Question', 'Model', 'Common');
		$info = $mQuestion->find($qid);
		if ( !$info ) {
			$this->ajax_error('您回复的问题不存在');
		}
		$info['status'] = intval($info['status']);
		if ( !in_array($info['status'], array(21,22,23)) ) {
			$this->ajax_error('您回复的问题不存在.');
		}

		$mAnswers = D('Answer', 'Model', 'Common');
		$where = array('qid'=>$qid, 'userid'=>$userid);
		if ( $mAnswers->where($where)->count()>0 ) {
			$this->ajax_error('您已经回答过此问题，请不重复回答');
		}
		G('checking_end');
		$result['debug']['cost']['checking'] = array(
			'time' => G('checking_start', 'checking_end', 3),
			'mem' => G('checking_start', 'checking_end', 'm'),

		);

		G('handledata_start');
		$form['question_id'] = $info['_id'];
		$form['userid'] = $userid;
		$form['usernick'] = $this->_userinfo['username'];
		$form['ctime'] = NOW_TIME;
		if ( $this->_device=='pc' ) {
			$source = 51;
		} else {
			if ( $this->_isapp ) {
				$source = 101;
			} else {
				$source = 151;
			}
		}
		$form['source'] = $source;
		$form['status'] = 21; // 默认直接发布

		// 扩展字段处理
		$form['data'] = array();
		// $ip = get_client_ip();
		// $location = getIPLocation($ip, array('city_cn'=>'', 'city_en'=>''));
		$location = getCookieLocation($this->_device);
		$form['data']['ip'] = $location['ip'];
		$form['data']['scope'] = $location;

		// 敏感词过滤
		G('sensitive_start');
		// 如果为待审核信息，存储敏感词审核信息 后台使用
		$lSensitive = D('Sensitive', 'Logic', 'Common');
		$content = $form['reply'];
		$ret = $lSensitive->detect($content, 0);
		if ( $ret && $ret['status'] ) {
			$form['data']['sensitive'] = $ret;
			if ( in_array($ret['type'], array('政治','色情','推销')) ) {
				$form['status'] = 12; // 需要审核
			} else {
				$form['status'] = 21; // 直接发布
			}
		}
		G('sensitive_end');
		// 将扩展数据字段进行编码存储
		$form['data'] = json_encode($form['data']);
		G('handledata_end');
		$result['debug']['cost']['handledata'] = array(
			'time' => G('handledata_start', 'handledata_end', 3),
			'mem' => G('handledata_start', 'handledata_end', 'm'),

		);
		$result['debug']['cost']['sensitive'] = array(
			'time' => G('sensitive_start', 'sensitive_end', 3),
			'mem' => G('sensitive_start', 'sensitive_end', 'm'),

		);

		$id = $mAnswers->data($form)->add();
		// echo '<!--', PHP_EOL,
		// 	 print_r($form, true), PHP_EOL,
		// 	 var_export($id, true), PHP_EOL,
		// 	 $mAnswers->getLastSql(), PHP_EOL,
		// 	 '-->', PHP_EOL;
		if ( $id ) {
			G('business_start');
			$result['aid'] = $id;


			$lQuestion = D('Question', 'Logic', 'Common');
			// 状态如果为21，表示发布回复成功
			if ( $form['status']==21 ) {
				// 当前被回答的问题的关注者，得到更新通知
				$result['debug']['updateAttentionNotice'] = $lQuestion->updateAttentionNotice($qid);
				// 清理缓存
				$result['debug']['flushCache'] = $lQuestion->flushCache($qid);
				// 添加到待推送集合
				$result['debug']['appendToPushSet'] = $lQuestion->appendToPushSet($qid);

				// 当前回复是否为专家回答
				$lPage = D('Page', 'Logic');
				$professors = $lPage->load_professors();
				$professor = false;
				if ( array_key_exists($userid, $professors) ) {
					$professor = $professors[$userid];
				}
				// 当前回答者如果是专家，关判断当前问题是否是请当前专家回答的问题
				$result['expert'] = array('status'=>!!$professor);
				if ( $professor!=false ) {
					$_where = array(
						'uid' => $userid,
						'act' => 41,
						'relid' => $qid,
					);
					$result['debug']['expert'] = array();
					// 当问题被指定专家回答后，日志删除，计数减 1
					$result['debug']['expert']['ret_op'] = D('Oplogs', 'Model', 'Common')
							->where($where)
							->data(array('act'=>42))
							->save();
					$result['debug']['expert']['ret_cnt'] = D('Members', 'Model', 'Common')
							->where(array('uid'=>$userid))
							->setDec('i_needanswer', 1);
				}

				// 回答成功后，回复数 +1
				$extra = $info['data'];
				if ( substr($extra, 0, 1)=='{' ) {
					$extra = json_decode($extra, true);
				} else {
					$extra = array(
						'notice'=>array('contact'=>'', 'type'=>'none'),
					);
				}
				$notice_type = strtolower(trim($extra['notice']['type']));
				$result['notice'] = array('type'=>$notice_type, 'status'=>false);
				if ( in_array($notice_type, array('sms', 'email')) ) {
					$to = trim($extra['notice']['contact']);
					$question_url = url('show', array($qid), $_device, 'ask');
					$lEMS = D('Ems', 'Logic', 'Common');
					if ( $notice_type=='sms' && strlen($to)==11 && preg_match('/\d{11}/i', $to) ) {
						$content = '有人回答了您的问题。点击链接查看答案 '.$question_url;
						$ret = $lEMS->sendSMS($to, $content);
					}
					if ( $notice_type=='email' && preg_match('/([\w\-]+\@[\w\-]+\.[\w\-]+)/', $to) ) {
						$title = '有人回答了您的问题';
						$content = '有人回答了您的问题。点击链接查看答案 '.$question_url;
						$mail_info = array(
							'title' => $title,
							'truename' => '',
							'content' => $content,
							'datetime' => date('Y年m月d日'),
						);
						layout(false);
						$this->assign('mail', $mail_info);
						$html = $this->theme('public')->fetch('./mail.notice');
						$ret = $lEMS->sendMail($to, $title, $html);
					}
					if ( $ret['status'] ) {
						$ret = json_decode($ret['result'], true);
						$result['notice']['status'] = $ret['status'];
						$result['debug']['notice'] = $ret;
					}
				}
			} // 回复状态正常的情况下完成回复操作
			if ( $form['status']==12 ) {
				$result['info'] = 'hidden';
				$result['reason'] = '您发布的回答含有 '.implode(', ', $form['data']['sensitive']['words']).
					'等敏感词汇，疑似 '.$form['data']['sensitive']['type'].' 类型的回复。已经发往后台管理员处进行审核。';
			} // 回复状态异常的情况下，完成回复操作，需要管理员后台审核回复内容
			// 自动修正问题数据
			$result['debug']['fixQuestionData'] = $lQuestion->fixQuestionData($qid);
			G('business_end');
			$result['debug']['cost']['business'] = array(
				'time' => G('business_start', 'business_end', 3),
				'mem' => G('business_start', 'business_end', 'm'),

			);
			$this->ajax_return($result);
		} else {
			$this->ajax_error('提交错误，请重试');
		}
	}

	/**
	 * 统计用户访问
	 * @@action - ajax
	 */
	public function visit() {
		$type = I('get.act', 'show', 'strtolower,trim');
		$id = I('get.id', 0, 'intval');
		if ( !$this->_islogined ) {
			$this->ajax_error('guest, ignore');
		}
		$types = array('show'=>51, 'cate'=>52, 'tag'=>53);
		if ( !array_key_exists($type, $types) ) {
			$this->ajax_error('no act, ignore');
		}

		$userid = $this->_userid;
		$lPage = D('Page', 'Logic');
		$ret = false;
		if ( $type == 'show' ) {
			$ret = $lPage->_visit_question($id, $userid, $this->_browserid);
		}
		if ( $ret['status'] ) {
			$ret['reason'] = 'success';
			$this->ajax_return($ret);
		} else {
			$this->ajax_error('ignore');
		}

	}


	/**
	 * 移动端 - 热门问题列表
	 */
	public function hotq() {
		$page = I('get.page', 1, 'intval');
		if ( $page < 1 ) { $page = 1; }
		$result = array('status'=>true,'reason'=>'获取成功');

		$pagesize = 10;
		$lPage = D('Page', 'Logic');
		$result = $lPage->hot_questions($page, $pagesize);
		if ( $result['status'] ) {
			$this->ajax_return($result);
		} else {
			$this->ajax_error($result['reason']);
		}
	}
}
