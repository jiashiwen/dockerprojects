<?php
/**
 * 从新闻池同步数据
 * @author Robert <yongliang1@leju.com>
 */
namespace api\Controller;
use Think\Controller;

/*
# 新房部门同步栏目
	楼市新闻 - 购房知识
	地产新闻 - 置业讲堂
# 家居部门同步栏目
	正文分类 风水
	设计
	家装

	行业分类 图秀家居
	装修100件事
	风格家
	收纳女王
	好色家居
# 二手房 iCMS
北京上海天津后台 : ?? 看不清
# (数据源属于新房) 二手房 购房知识
*/
class SyncController extends Controller {
	/*
	protected $tasks = array(
		'楼市新闻.购房知识' => array(
			'business' => 'house_news',
			'opts' => array('{topcolumn@eq}楼市新闻','{subcolumn@eq}购房知识'),
			'keys' => array('TASK:SYNC:NEWS:INFO','TASK:SYNC:NEWS:DONE','TASK:SYNC:NEWS:LOCK'),
			'mapping' => array(
				'version' => ':NOWTIME',
				'title' => '@title',
				'content' => '@content',
				'cover' => '@picurl',
				'coverinfo' => '@shorttitle',
				'editorid' => '@creator',
				'editor' => '@author',
				'ctime' => '@createtime',
				'utime' => '@updatetime',
				'ptime' => 0,
				'scope' => '@city|全国',
				'src_type' => 1,
				'src_url' => '@url',
				'tags' => '二手房 购房知识',
			),
		),
		'地产新闻.置业讲堂' => array(
			'business' => 'house_news',
			'opts' => array('{topcolumn@eq}地产新闻','{subcolumn@eq}置业讲堂'),
			'keys' => array('TASK:SYNC:NEWS2:INFO','TASK:SYNC:NEWS2:DONE','TASK:SYNC:NEWS2:LOCK'),
			'mapping' => array(
				'version' => ':NOWTIME',
				'title' => '@title',
				'content' => '@content',
				'cover' => '@picurl',
				'coverinfo' => '@shorttitle',
				'editorid' => '@creator',
				'editor' => '@author',
				'ctime' => '@createtime',
				'utime' => '@updatetime',
				'ptime' => 0,
				'scope' => '@city|全国',
				'src_type' => 1,
				'src_url' => '@url',
				'tags' => '二手房 购房知识',
			),
		),
		'二手房.购房知识' => array(
			'business' => 'house_news',
			'opts' => array('{topcolumn@eq}二手房','{subcolumn@eq}购房知识'),
			'keys' => array('TASK:SYNC:ESF:INFO','TASK:SYNC:ESF:DONE','TASK:SYNC:ESF:LOCK'),
			'mapping' => array(
				'version' => ':NOWTIME',
				'title' => '@title',
				'content' => '@content',
				'cover' => '@picurl',
				'coverinfo' => '@shorttitle',
				'editorid' => '@creator',
				'editor' => '@author',
				'ctime' => '@createtime',
				'utime' => '@updatetime',
				'ptime' => 0,
				'scope' => '@city|全国',
				'src_type' => 1,
				'src_url' => '@url',
				'tags' => '二手房 购房知识',
			),
		),
	);
	*/
	public function index(){
		echo '<h1>这是 API - Sync 页面</h1>';
	}


	public function syncNews1Knowledge() {
		// (数据源属于新房) 楼市新闻 - 购房知识; 	地产新闻 - 置业讲堂
		$key = 'TASK:SYNC:NEWS:INFO';
		$done_key = 'TASK:SYNC:NEWS:DONE';
		$lock_key = 'TASK:SYNC:NEWS:LOCK';

		$_msg = array(
			'status' => false,
			'msg' => '',
			'debug' => array(),
		);
		$cacher = S(C('REDIS'));
		// $cacher->select(1);
		$flush = I('get.flush', 0, 'intval');
		if ( $flush == 1 ) {
			$_msg['debug'][] = '重新同步数据';
			$cacher->del(array($key, $done_key, $lock_key));
		}

		if ( $cacher->get($done_key) ) {
			$_msg['msg'] = '数据同步已经完成';
			die(json_encode($_msg).PHP_EOL);
		}

		$locked = $cacher->get($lock_key);
		if ( $locked ) {
			$_msg['msg'] = '已经有一个任务正在进行同步！';
			die(json_encode($_msg).PHP_EOL);
		}
		$cacher->setEx($lock_key, 15, true);

		$page = intval($cacher->hGet($key, 'page'));
		$page = $page<=0 ? 1 : $page + 1;
		$pagesize = 100;

		$info = D('Infos', 'Logic', 'Common');
		$business = 'house_news';
		$fields = array();
		$order = '{createtime}asc';
		$opts = array('{topcolumn@eq}楼市新闻','{subcolumn@eq}购房知识');

		$result = $info->selectNews($business, $page, $pagesize, $opts, $fields, $order);
		$total = $result['total'];
		$pagecount = ceil($total/$pagesize);

		if ( $total>0 && $page<=$pagecount ) {
			$cities = C('CITIES.CMS');
			$list = array();
			foreach ( $result['data'] as $i => $item ) {
				if ( trim($item['title'])=='' ) {
					continue;
				}
				$_item = array(
					'version' => NOW_TIME,
					'title' => $item['title'],
					'content' => $item['content'],
					'cover' => $item['picurl'],
					'coverinfo' => $item['shorttitle'],
					'editorid' => intval($item['creator']),
					'editor' => $item['author'],
					'ctime' => intval($item['createtime']),
					'utime' => intval($item['updatetime']),
					'ptime' => 0,
					'scope' => isset($cities[$item['city']]) ? $cities[$item['city']] : '全国',
					'src_type' => 1,
					'src_url' => $item['url'],
					'tags' => '楼市新闻 购房知识',
				);
				array_push($list, $_item);
			}
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$ret = $mKnowledge->addAll($list);

			$data = array();
			if ( $page==1 ) {
				$data['create'] = NOW_TIME;
			}
			$data['page'] = $page;
			$data['pagesize'] = $pagesize;
			$data['total'] = $total;
			$data['count'] = $pagecount;
			$data['end'] = NOW_TIME;
			$cacher->hMSet($key, $data);

			// 添加同步完成锁
			if ( $page==$pagecount ) {
				$cacher->set($done_key, NOW_TIME);
				$_msg['status'] = true;
				$_msg['msg'] = 'done';
				die(json_encode($_msg).PHP_EOL);
			} else {
				$cacher->del($lock_key);
				$_msg['status'] = true;
				$_msg['msg'] = 'succ';
				$_msg['page'] = $page;
				$_msg['count'] = $pagecount;
				die(json_encode($_msg).PHP_EOL);
			}

		} else {
			$_msg['status'] = true;
			$_msg['msg'] = '同步任务已经结束！';
			die(json_encode($_msg).PHP_EOL);
		}
	}
	public function syncNews2Knowledge() {
		// (数据源属于新房) 楼市新闻 - 购房知识; 	地产新闻 - 置业讲堂
		$key = 'TASK:SYNC:NEWS2:INFO';
		$done_key = 'TASK:SYNC:NEWS2:DONE';
		$lock_key = 'TASK:SYNC:NEWS2:LOCK';

		$_msg = array(
			'status' => false,
			'msg' => '',
			'debug' => array(),
		);
		$cacher = S(C('REDIS'));
		// $cacher->select(1);
		$flush = I('get.flush', 0, 'intval');
		if ( $flush == 1 ) {
			$_msg['debug'][] = '重新同步数据';
			$cacher->del(array($key, $done_key, $lock_key));
		}

		if ( $cacher->get($done_key) ) {
			$_msg['msg'] = '数据同步已经完成';
			die(json_encode($_msg).PHP_EOL);
		}

		$locked = $cacher->get($lock_key);
		if ( $locked ) {
			$_msg['msg'] = '已经有一个任务正在进行同步！';
			die(json_encode($_msg).PHP_EOL);
		}
		$cacher->setEx($lock_key, 15, true);

		$page = intval($cacher->hGet($key, 'page'));
		$page = $page<=0 ? 1 : $page + 1;
		$pagesize = 100;

		$info = D('Infos', 'Logic', 'Common');
		$business = 'house_news';
		$fields = array();
		$order = '{createtime}asc';
		$opts = array('{topcolumn@eq}地产新闻','{subcolumn@eq}置业讲堂');

		$result = $info->selectNews($business, $page, $pagesize, $opts, $fields, $order);
		$total = $result['total'];
		$pagecount = ceil($total/$pagesize);

		if ( $total>0 && $page<=$pagecount ) {
			$cities = C('CITIES.CMS');
			$list = array();
			foreach ( $result['data'] as $i => $item ) {
				if ( trim($item['title'])=='' ) {
					continue;
				}
				$_item = array(
					'version' => NOW_TIME,
					'title' => $item['title'],
					'content' => $item['content'],
					'cover' => $item['picurl'],
					'coverinfo' => $item['shorttitle'],
					'editorid' => intval($item['creator']),
					'editor' => $item['author'],
					'ctime' => intval($item['createtime']),
					'utime' => intval($item['updatetime']),
					'ptime' => 0,
					'scope' => isset($cities[$item['city']]) ? $cities[$item['city']] : '全国',
					'src_type' => 1,
					'src_url' => $item['url'],
					'tags' => '地产新闻 置业讲堂',
				);
				array_push($list, $_item);
			}
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$ret = $mKnowledge->addAll($list);

			$data = array();
			if ( $page==1 ) {
				$data['create'] = NOW_TIME;
			}
			$data['page'] = $page;
			$data['pagesize'] = $pagesize;
			$data['total'] = $total;
			$data['count'] = $pagecount;
			$data['end'] = NOW_TIME;
			$cacher->hMSet($key, $data);

			// 添加同步完成锁
			if ( $page==$pagecount ) {
				$cacher->set($done_key, NOW_TIME);
				$_msg['status'] = true;
				$_msg['msg'] = 'done';
				die(json_encode($_msg).PHP_EOL);
			} else {
				$cacher->del($lock_key);
				$_msg['status'] = true;
				$_msg['msg'] = 'succ';
				$_msg['page'] = $page;
				$_msg['count'] = $pagecount;
				die(json_encode($_msg).PHP_EOL);
			}

		} else {
			$_msg['status'] = true;
			$_msg['msg'] = '同步任务已经结束！';
			die(json_encode($_msg).PHP_EOL);
		}
	}

	public function syncJiaju1Knowledge() {
		// (数据源属于家居) 风水 设计 家装
		$key = 'TASK:SYNC:JIAJU1:INFO';
		$done_key = 'TASK:SYNC:JIAJU1:DONE';
		$lock_key = 'TASK:SYNC:JIAJU1:LOCK';

		$_msg = array(
			'status' => false,
			'msg' => '',
			'debug' => array(),
		);
		$cacher = S(C('REDIS'));
		// $cacher->select(1);
		$flush = I('get.flush', 0, 'intval');
		if ( $flush == 1 ) {
			$_msg['debug'][] = '重新同步数据';
			$cacher->del(array($key, $done_key, $lock_key));
		}

		if ( $cacher->get($done_key) ) {
			$_msg['msg'] = '数据同步已经完成';
			die(json_encode($_msg).PHP_EOL);
		}

		$locked = $cacher->get($lock_key);
		if ( $locked ) {
			$_msg['msg'] = '已经有一个任务正在进行同步！';
			die(json_encode($_msg).PHP_EOL);
		}
		$cacher->setEx($lock_key, 15, true);

		$page = intval($cacher->hGet($key, 'page'));
		$page = $page<=0 ? 1 : $page + 1;
		$pagesize = 100;

		$info = D('Infos', 'Logic', 'Common');
		$business = 'house_news';
		$fields = array();
		$order = '{createtime}asc';
		$opts = array('{topcolumn@eq}风水|设计|家装');

		$result = $info->selectNews($business, $page, $pagesize, $opts, $fields, $order);
		$total = $result['total'];
		$pagecount = ceil($total/$pagesize);

		if ( $total>0 && $page<=$pagecount ) {
			$cities = C('CITIES.CMS');
			$list = array();
			foreach ( $result['data'] as $i => $item ) {
				if ( trim($item['title'])=='' ) {
					continue;
				}
				$_item = array(
					'version' => NOW_TIME,
					'title' => $item['title'],
					'content' => $item['content'],
					'cover' => $item['picurl'],
					'coverinfo' => $item['shorttitle'],
					'editorid' => intval($item['creator']),
					'editor' => $item['author'],
					'ctime' => intval($item['createtime']),
					'utime' => intval($item['updatetime']),
					'ptime' => 0,
					'scope' => isset($cities[$item['city']]) ? $cities[$item['city']] : '全国',
					'src_type' => 1,
					'src_url' => $item['url'],
					'tags' => $item['topcolumn'],
				);
				array_push($list, $_item);
			}
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$ret = $mKnowledge->addAll($list);

			$data = array();
			if ( $page==1 ) {
				$data['create'] = NOW_TIME;
			}
			$data['page'] = $page;
			$data['pagesize'] = $pagesize;
			$data['total'] = $total;
			$data['count'] = $pagecount;
			$data['end'] = NOW_TIME;
			$cacher->hMSet($key, $data);

			// 添加同步完成锁
			if ( $page==$pagecount ) {
				$cacher->set($done_key, NOW_TIME);
				$_msg['status'] = true;
				$_msg['msg'] = 'done';
				die(json_encode($_msg).PHP_EOL);
			} else {
				$cacher->del($lock_key);
				$_msg['status'] = true;
				$_msg['msg'] = 'succ';
				$_msg['page'] = $page;
				$_msg['count'] = $pagecount;
				die(json_encode($_msg).PHP_EOL);
			}

		} else {
			$_msg['status'] = true;
			$_msg['msg'] = '同步任务已经结束！';
			die(json_encode($_msg).PHP_EOL);
		}
	}
	public function syncJiaju2Knowledge() {
		// (数据源属于新房) 楼市新闻 - 购房知识; 	地产新闻 - 置业讲堂
		$key = 'TASK:SYNC:JIAJU2:INFO';
		$done_key = 'TASK:SYNC:JIAJU2:DONE';
		$lock_key = 'TASK:SYNC:JIAJU2:LOCK';

		$_msg = array(
			'status' => false,
			'msg' => '',
			'debug' => array(),
		);
		$cacher = S(C('REDIS'));
		// $cacher->select(1);
		$flush = I('get.flush', 0, 'intval');
		if ( $flush == 1 ) {
			$_msg['debug'][] = '重新同步数据';
			$cacher->del(array($key, $done_key, $lock_key));
		}

		if ( $cacher->get($done_key) ) {
			$_msg['msg'] = '数据同步已经完成';
			die(json_encode($_msg).PHP_EOL);
		}

		$locked = $cacher->get($lock_key);
		if ( $locked ) {
			$_msg['msg'] = '已经有一个任务正在进行同步！';
			die(json_encode($_msg).PHP_EOL);
		}
		$cacher->setEx($lock_key, 15, true);

		$page = intval($cacher->hGet($key, 'page'));
		$page = $page<=0 ? 1 : $page + 1;
		$pagesize = 100;

		$info = D('Infos', 'Logic', 'Common');
		$business = 'house_news';
		$fields = array();
		$order = '{createtime}asc';
		$opts = array('{topindustry@eq}图秀家居|装修100件事|风格家|收纳女王|好色家居');

		$result = $info->selectNews($business, $page, $pagesize, $opts, $fields, $order);
		$total = $result['total'];
		$pagecount = ceil($total/$pagesize);

		if ( $total>0 && $page<=$pagecount ) {
			$cities = C('CITIES.CMS');
			$list = array();
			foreach ( $result['data'] as $i => $item ) {
				if ( trim($item['title'])=='' ) {
					continue;
				}
				$_item = array(
					'version' => NOW_TIME,
					'title' => $item['title'],
					'content' => $item['content'],
					'cover' => $item['picurl'],
					'coverinfo' => $item['shorttitle'],
					'editorid' => intval($item['creator']),
					'editor' => $item['author'],
					'ctime' => intval($item['createtime']),
					'utime' => intval($item['updatetime']),
					'ptime' => 0,
					'scope' => isset($cities[$item['city']]) ? $cities[$item['city']] : '全国',
					'src_type' => 1,
					'src_url' => $item['url'],
					'tags' => '地产新闻 置业讲堂',
				);
				array_push($list, $_item);
			}
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$ret = $mKnowledge->addAll($list);

			$data = array();
			if ( $page==1 ) {
				$data['create'] = NOW_TIME;
			}
			$data['page'] = $page;
			$data['pagesize'] = $pagesize;
			$data['total'] = $total;
			$data['count'] = $pagecount;
			$data['end'] = NOW_TIME;
			$cacher->hMSet($key, $data);

			// 添加同步完成锁
			if ( $page==$pagecount ) {
				$cacher->set($done_key, NOW_TIME);
				$_msg['status'] = true;
				$_msg['msg'] = 'done';
				die(json_encode($_msg).PHP_EOL);
			} else {
				$cacher->del($lock_key);
				$_msg['status'] = true;
				$_msg['msg'] = 'succ';
				$_msg['page'] = $page;
				$_msg['count'] = $pagecount;
				die(json_encode($_msg).PHP_EOL);
			}

		} else {
			$_msg['status'] = true;
			$_msg['msg'] = '同步任务已经结束！';
			die(json_encode($_msg).PHP_EOL);
		}
	}


	public function syncESFKnowledge() {
		// (数据源属于新房) 二手房 购房知识
		$key = 'TASK:SYNC:ESF:INFO';
		$done_key = 'TASK:SYNC:ESF:DONE';
		$lock_key = 'TASK:SYNC:ESF:LOCK';

		$_msg = array(
			'status' => false,
			'msg' => '',
			'debug' => array(),
		);
		$cacher = S(C('REDIS'));
		// $cacher->select(1);
		$flush = I('get.flush', 0, 'intval');
		if ( $flush == 1 ) {
			$_msg['debug'][] = '重新同步数据';
			$cacher->del(array($key, $done_key, $lock_key));
		}

		if ( $cacher->get($done_key) ) {
			$_msg['msg'] = '数据同步已经完成';
			die(json_encode($_msg).PHP_EOL);
		}

		$locked = $cacher->get($lock_key);
		if ( $locked ) {
			$_msg['msg'] = '已经有一个任务正在进行同步！';
			die(json_encode($_msg).PHP_EOL);
		}
		$cacher->setEx($lock_key, 15, true);

		$page = intval($cacher->hGet($key, 'page'));
		$page = $page<=0 ? 1 : $page + 1;
		$pagesize = 100;

		$info = D('Infos', 'Logic', 'Common');
		$business = 'house_news';
		$fields = array();
		$order = '{createtime}asc';
		$opts = array('{topcolumn@eq}二手房','{subcolumn@eq}购房知识');

		$result = $info->selectNews($business, $page, $pagesize, $opts, $fields, $order);
		$total = $result['total'];
		$pagecount = ceil($total/$pagesize);

		if ( $total>0 && $page<=$pagecount ) {
			$cities = C('CITIES.CMS');
			$list = array();
			foreach ( $result['data'] as $i => $item ) {
				if ( trim($item['title'])=='' ) {
					continue;
				}
				$_item = array(
					'version' => NOW_TIME,
					'title' => $item['title'],
					'content' => $item['content'],
					'cover' => $item['picurl'],
					'coverinfo' => $item['shorttitle'],
					'editorid' => intval($item['creator']),
					'editor' => $item['author'],
					'ctime' => intval($item['createtime']),
					'utime' => intval($item['updatetime']),
					'ptime' => 0,
					'scope' => isset($cities[$item['city']]) ? $cities[$item['city']] : '全国',
					'src_type' => 1,
					'src_url' => $item['url'],
					'tags' => '二手房 购房知识',
				);
				array_push($list, $_item);
			}
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$ret = $mKnowledge->addAll($list);

			$data = array();
			if ( $page==1 ) {
				$data['create'] = NOW_TIME;
			}
			$data['page'] = $page;
			$data['pagesize'] = $pagesize;
			$data['total'] = $total;
			$data['count'] = $pagecount;
			$data['end'] = NOW_TIME;
			$cacher->hMSet($key, $data);

			// 添加同步完成锁
			if ( $page==$pagecount ) {
				$cacher->set($done_key, NOW_TIME);
				$_msg['status'] = true;
				$_msg['msg'] = 'done';
				die(json_encode($_msg).PHP_EOL);
			} else {
				$cacher->del($lock_key);
				$_msg['status'] = true;
				$_msg['msg'] = 'succ';
				$_msg['page'] = $page;
				$_msg['count'] = $pagecount;
				die(json_encode($_msg).PHP_EOL);
			}

		} else {
			$_msg['status'] = true;
			$_msg['msg'] = '同步任务已经结束！';
			die(json_encode($_msg).PHP_EOL);
		}
	}
}