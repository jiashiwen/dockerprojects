<?php
/**
 * 知识百科系统的定时任务接口
 * @author Robert <yongliang1@leju.com>
 */
namespace api\Controller;

class CronController extends BaseController {

	protected $_redis = null;
	protected $_begin = null;
	protected $_end = null;
	protected $_lock = 'kb:lock';
	protected $_days = null;

	protected function _init() {
		$this->_redis = S(C('REDIS'));
		$todyTime = getDayTime();
		$this->_begin = $todyTime['begin'];
		$this->_end = $todyTime['end'];
		$this->_days = $this->_beforetime();
	}

	public function __construct() {
		$this->_init();
	}

	public function index() {
		$this->systemHalt();
	}

	/**
	 * 问答定时任务
	 * 统计问答数据量
	 */
	public function question() {
		$day = I('get.day', '', 'strtotime');
		if ( !$day ) {
			$day = strtotime('yesterday');
		}
		$start = strtotime(date('Y-m-d 00:00:00', $day));
		$end = strtotime(date('Y-m-d 23:59:59', $day));

		$mQuestion = D('Question', 'Model', 'Common');
		$where = array(
			'ctime'=>array('between', array($start, $end)),
		);
		$ret = array( '0' => 0, '11' => 0, '12' => 0, '21' => 0, '22' => 0, '23' => 0, );
		$sql = "SELECT `status`, count(id) as 'cnt' FROM question WHERE `ctime` BETWEEN '{$start}' AND '{$end}' GROUP BY `status`";
		$list = $mQuestion->query($sql);
		foreach ( $list as $i => $item ) {
			if ( array_key_exists($item['status'], $ret) ) {
				$ret[$item['status']] = intval($item['cnt']);
			}
		}
		$total = array_sum($ret);
		$mapping = array('0'=>'deleted', '11'=>'unconfirm', '12'=>'unverified', '21'=>'unsolved', '22'=>'answered', '23'=>'best');
		$result = array();
		foreach ( $mapping as $status_id => $field ) {
			$result[$field] = $ret[$status_id];
		}
		$result['all'] = $total;

		$record = array(
			'city' => '_',
			'reltype' => 'qa',
			'ctime' => $start,
			'chartid' => 'qaecharts',
			'data' => json_encode(array(
				'day' => date('Y-m-d', $start),
				'stats' => $result,
			)),
		);
		$mDataStatistics = D('DataStatistics', 'Model', 'Common');
		$where = array(
			'ctime'=>array('between', array($start, $end)),
		);
		$mDataStatistics->where($where)->delete();
		$mDataStatistics->data($record)->add();

		$to = strtotime(date('Y-m-d 00:00:00', NOW_TIME));
		$from = strtotime('-15 day', $to);

		$where = array(
			'reltype' => 'qa',
			'chartid' => 'qaecharts',
			'ctime' => array('between', array($from, $to)),
		);
		$list = $mDataStatistics->where($where)->order('ctime asc')->page(1, 15)->select();

		$return = array();
		$fields = array('all'=>'全部', 'answered'=>'已回答', 'best'=>'已采纳', 'unsolved'=>'未解决', 'unverified'=>'待审核');
		$_list = array();
		foreach ( $list as $i => $item ) {
			$_list[$item['ctime']] = json_decode($item['data'], true);
		} unset($list);

		foreach ( $fields as $field => $name ) {
			$return[$field] = array();
			$return[$field]['title'] = $name;
			$return[$field]['list'] = array();
			foreach ( $_list as $ctime => $item ) {
				array_push($return[$field]['list'], array('day'=>$item['day'], 'total'=>$item['stats'][$field]));
			}
		}
		// echo '<pre>', PHP_EOL, var_export($return, true), PHP_EOL, '</pre>', PHP_EOL;
		$key = 'QA:ADMIN:STATS:CHART';
		$expire = 86400;
		$this->_redis = S(C('REDIS'));
		$this->_redis->set($key, $return);
		$this->_redis->expire($key, $expire);

		$result = array('status'=>true, 'day'=>date('Y-m-d', $start), 'list'=>$return);
		$this->showResult($result);
	}

	/**
	 * 问答数据定时推送至新闻池与搜索服务
	 * 在后台定时每分钟执行一次
	 */
	public function pushquestions() {
		$questions = D('Question', 'Logic', 'Common')->getAllFromPushSet();
		$result = array('status'=>false, 'msg'=>'没有要推送的问答');
		if ( $questions ) {
			G('push_start');
			$lQuestionPush = D('QuestionPublish', 'Logic', 'Common');
			// $lQuestionPush->confirmQuestions($questions);
			$result = $lQuestionPush->Publish($questions);
			G('push_end');

			$result['_debug'] = array(
				// 'data' => $lQuestionPush->getData(),
				'error' => $lQuestionPush->getError(),
				'cost' => array(
					'cost' => G('push_start', 'push_end', 3),
					'mem' => G('push_start', 'push_end', 'm'),
				),
			);
		}
		$result['ids'] = $questions;
		$this->ajax_return($result);
	}

	/**
	 * 自动修复问答数据状态
	 *
	 */
	public function fixQuestions() {
		// todo
	}

	/**
	 * 后台管理员信息数据同步
	 * by 羊阳
	 */
	public function syncAdminers() {
		// exit;
		// 如果不为 35940，则自动将失效管理员设置为禁用状态
		$auto_disable = I('get.disable', 0, 'intval');
		$auto_disable = $auto_disable == 35940 ? true : false;

		$page = 1;
		$pagesize = 10;

		// 判断内外网环境，并获取统一管理系统的配置信息
		$lUrl = D('Url', 'Logic', 'Common');
		$mode = ( $lUrl->getMode()==='' ) ? 'PRODUCT' : 'DEVELOPMENT';
		$admin_config = C('ADMINLOGIN');
		$admin_api_cfg = $admin_config[$mode];

		G('stats_start');
		$mAdmins = D('Admins', 'Model', 'Common');
		// 待同步的管理员列表 查询条件
		$where = array();
		$where['city'] = '';
		$where['status'] = 1;
		$time = strtotime('-1 day', strtotime(date('Y-m-d', NOW_TIME)));
		$where['update_time'] = array('ELT', $time);
		// 数据查询
		$total = intval($mAdmins->where($where)->count());
		$list = $mAdmins->where($where)->page($page, $pagesize)->order('id asc')->select();
		// var_dump($list);

		// 统一登录系统数据查询
		$api = $admin_api_cfg['api_url'].$admin_config['APIs']['getUser'];
		$headers = $admin_api_cfg['headers'];
		// var_dump('api', $api, 'headers', $headers);
		$cnt = 0;
		foreach ( $list as $i => $item ) {
			$data = array('key'=>$admin_api_cfg['app_key'], 'uid'=>$item['passport_id']);
			// var_dump('data', $data, 'userinfo', $item['truename']);
			$userinfo = curl_post($api, $data, $headers);
			// var_dump('return', $userinfo);
			if ( $userinfo['status']==false ) {
				continue;
			} else {
				$userinfo = json_decode($userinfo['result'], true);

				$needUpdate = false;	// 是否进行管理员用户信息更新
				if ( $userinfo['result']==false ) {
					$new_data = array(
						'status' => 0,
						'update_time' => NOW_TIME,
					);	// 失效或无用户，标记为删除，待清理
					// 如果接口disable传参为35940时，自动禁用管理帐号，否则不自动禁用失效用户时则不更新
					$needUpdate = $auto_disable;
					// var_dump('需要自动禁用帐号？', $needUpdate);
				} else {
					$userinfo = $userinfo['userinfo'];
					$city = isset($userinfo['city_en']) ? strtolower($userinfo['city_en']) : '';
					if ( $city == 'all' ) {
						$city = '_';
					}
					$new_data = array(
						'passport_name'=>$userinfo['passport_name'],
						'truename'=>$userinfo['truename'],
						'em_email'=>$userinfo['employee_email'],
						'em_sn'=>$userinfo['employee_number'],
						'em_tel'=>$userinfo['telephone'],
						'mobile'=>$userinfo['mobile'],
						'city'=> $city,
						'update_time'=>NOW_TIME,
					);
					$needUpdate = true;
				}
			}
			// var_dump('update', $where, $new_data);
			// 如果需要更新用户信息时
			if ( $needUpdate == true ) {
				$where = array('passport_id'=>$item['passport_id']);
				$ret = $mAdmins->where($where)->data($new_data)->save();
			}
			if ( $ret ) {
				$cnt ++;
			}
			// var_dump('ret', $ret);
		}
		G('stats_end');
		$cost = array(
			'sec' => G('stats_start', 'stats_end', 3),
			'mem' => G('stats_start', 'stats_end', 'm'),
		);
		$result = array(
			'status' => true,
			'pager' => array(
				'page' => $page,
				'pagesize' => $pagesize,
				'total' => $total,
				'count' => ceil($total/$pagesize),
			),
			'list' => array(
				'count'=>count($list),
				'done' => $cnt,
			), 
			'success' => $cnt==count($list),
			'cost' => $cost,
		);
		$this->showResult($result);
	}





	protected function lock($key,$ttl=600)
	{
		$rkey = $this->_lock . $key;
		return $this->_redis->setex($rkey,$ttl,1);
	}

	protected function unlock($key)
	{
		$rkey = $this->_lock . $key;
		return $this->_redis->delete($rkey);
	}

	protected function exists_lock($key)
	{
		$rkey = $this->_lock . $key;
		return $this->_redis->exists();
	}

	protected function _beforetime($days=7)
	{
		$difftime = 86400;
		$date = array();

		for ( $i=$days; $i > 0; $i-- )
		{
			$date[$i]['date'] = date('m-d',($this->_begin - ($difftime * ($i-1))));
			$date[$i]['begin'] = $this->_begin - ($difftime * ($i-1));
			$date[$i]['end'] = $date[$i]['begin'] + 86399;
		}
		return $date;
	}

	public function trendchart()
	{
		$proccess = $this->_lock . ':trendchart';
		if ($this->_redis->exists($proccess))
		{
			exit('进程正在进行，请稍后再试！');
		}

		$this->lock($proccess);
		$rkey = 'kb:chart:'. $this->_begin;
		$ttl = 24*3600*2;
		$data['count'] = $this->syncknowledgecount();
		$data['hot'] = $this->syncknowledgehot();
		$data['rank'] = $this->syncknowledgerank();
		$data['cate'] = $this->syncknowledgecate();
		$trend = json_encode($data);
		$DataStatistics = D('DataStatistics','Model','Common');
		$insert = array(
			'reltype'=>'kb',
			'ctime'=>NOW_TIME,
			'data'=>$trend,
			'chartid'=>$rkey,
			);
		if ($DataStatistics->add($insert))
		{
			echo 'insert succ' . date('Y-m-d H:i:s',NOW_TIME);
		}
		else
		{
			echo 'insert fail' . date('Y-m-d H:i:s',NOW_TIME);
		}

		$this->_redis->setex($rkey,$ttl,$trend);

		//pc端热门排行，取前20条
		$cities = C('CITIES.CMS');
		foreach ($cities as $city_code => $city)
		{
			$rank = $this->syncFrontknowledgerank($city_code,20);
			if ($rank)
			{
				$rank = json_encode($rank);
				$rkeys = 'kb:rank:'.$city_code;
				$rankinsert = array(
					'reltype'=>'kb',
					'ctime'=>NOW_TIME,
					'data'=>$rank,
					'chartid'=>$rkeys,
					'city'=>$city_code,
				);
				if ($DataStatistics->add($rankinsert))
				{
					echo 'insert rank '.$city_code.' succ' . date('Y-m-d H:i:s',NOW_TIME);
				}
				else
				{
					echo 'insert rank '.$city_code.' fail' . date('Y-m-d H:i:s',NOW_TIME);
				}
				$this->_redis->setex($rkeys,$ttl,$rank);
			}
		}

		$this->unlock($proccess);

	}


	/**
	 * 每天同步一次知识统计，缓存到reids，缓存时间7×24小时
	 */
	public function syncknowledgecount() {

		$data = array();
		$mKnowledge = D('Knowledge', 'Model', 'Common');
		foreach ($this->_days as $key => $item)
		{
			$where['type'] = 'kb';
			$where['ptime'] = array('between',"{$item['begin']},{$item['end']}");
			$count = $mKnowledge->where($where)->count();
			$data[$key]['day'] = $item['date'];
			$data[$key]['total'] = (int)$count;
		}
		return array_values($data);
	}

	public function syncknowledgehot() {

		$data = array();
		$mVisitStats = D('VisitStats', 'Model', 'Common');
		foreach ($this->_days as $key => $item) {
			$where['reltype'] = 'kb';
			$where['ctime'] = array('between',"{$item['begin']},{$item['end']}");
			$count = $mVisitStats->where($where)->count();
			$data[$key]['day'] = $item['date'];
			$data[$key]['total'] = (int)$count;
		}
		return array_values($data);
	}

	public function syncknowledgecate() {
		/**
		$mCategories = D('Categories', 'Model', 'Common');
		$catelist = $mCategories->getCateList(0);
		$mVisitStats = D('VisitStats', 'Model', 'Common');
		$data = array();
		$total = array();

		if ($catelist)
		{
			foreach ($this->_days as $k => $item)
			{
				foreach ($catelist as $key => $cate)
				{
					$where['reltype'] = 'kb';
					$where['relcateid'] = array('like',"%{$cate['path']}-%");
					$where['ctime'] = array('between',"{$item['begin']},{$item['end']}");
					$count = $mVisitStats->where($where)->count();
					$data['list'][$k][$cate['id']]['date'] = $item['date'];
					$data['list'][$k][$cate['id']]['count'] = $count;
					$total[] = $count;
				}
			}
			$data['total'] = array_sum($total);
		}
		return $data;
		**/

		$data = array();
		$mVisitStats = D('VisitStats', 'Model', 'Common');
		foreach ($this->_days as $key => $item) {
			$where['reltype'] = 'kb';
			$where['ctime'] = array('between',"{$item['begin']},{$item['end']}");
			$count = $mVisitStats->where($where)->count();
			$data[$key]['day'] = $item['date'];
			$data[$key]['total'] = (int)$count;
		}
		return array_values($data);
	}

	public function syncknowledgerank($limit = 8) {

		$data = array();
		$mVisitStats = D('VisitStats', 'Model', 'Common');
		$begin = $this->_days['7']['begin'];
		$end = $this->_days['1']['end'];

		$list = $mVisitStats->todayClick($begin,$end,$limit);
		if ($list)
		{
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			foreach ($list as $key => $value) {
				$info = $mKnowledge->field('id,title')->find($value['relid']);
				$data[$key]['total'] = $value['total'];
				$data[$key]['id'] = $info['id'];
				$data[$key]['title'] = $info['title'];
			}
		}
		return $data;
	}

	/**
	 * @for front
	 * @param int $limit
	 * @return array
	 */
	public function syncFrontknowledgerank($city,$limit = 30) {

		$data = array();
		$mVisitStats = D('VisitStats', 'Model', 'Common');
		$begin = $this->_days['7']['begin'];
		$end = $this->_days['1']['end'];

		$list = $mVisitStats->todayClickByCity($begin,$end,$limit,$city);
		if ($list)
		{
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			foreach ($list as $key => $value) {
				$info = $mKnowledge->field('id,title')->find($value['relid']);
				$data[$key]['total'] = $value['total'];
				$data[$key]['id'] = $info['id'];
				$data[$key]['title'] = $info['title'];
			}
		}
		return $data;
	}


	/**
	 * 定时发布
	 */
	public function timerpublish()
	{
		//lock
		$proccess = $this->_lock . ':timerpublish';
		if ($this->_redis->exists($proccess)) {
			exit('[INFO] 进程正在进行，请稍后再试！');
		}
		$this->lock($proccess);
		$mKnowledge = D('Knowledge', 'Model', 'Common');
		$where['status'] = 2;
		$where['ptime'] = array('elt',NOW_TIME);
		$list = $mKnowledge->where($where)->order('ptime asc')->select();

		if (!empty($list))
		{
			$ids = array();
			$urls = array();
			$push = array();
			$lInfos = D('Infos','Logic','Common');
			foreach ($list as $key => &$item) {
				array_push($ids,$item['id']);
				$item['status'] = 9;
				$push[$key] = $this->pushKonwledge($item);
				$data[$key] = $item;
				// 向新闻池推送数据
				$r = $lInfos->pushNewsPool($item);
				if ($r['status'] == 1) {
					echo '[INFO] 新闻池推送成功', PHP_EOL;
					// 更新 知识数据版本为当前发布时间
					// $item['version'] = $item['ptime'];
					$ptime = intval($item['ptime']);
					$utime = intval($item['utime']);
					$utime = $utime > 0 ? $utime : NOW_TIME;
					$item['version'] = ( $ptime > 0 && $ptime > $utime ) ? $ptime : intval($item['utime']);
					$ret = $mKnowledge->where(array('id'=>$item['id']))->data(array('version'=>$item['version']))->save();
				} else {
					echo '[ERROR] 新闻池推送失败', $item['id'],PHP_EOL;
				}
				// 添加要推送的百度链接
				array_push($urls, url('show', array('id'=>$item['id']), 'pc', 'baike'));
				array_push($urls, url('show', array('id'=>$item['id']), 'touch', 'baike'));
			}

			$update['status'] = 9;
			$upd = $mKnowledge->updateAllData($ids,$update);
			if ($upd)
			{
				$lSearch = D('Search', 'Logic', 'Common');
				if ( !empty($urls) ) {
					$ret = $lSearch->pushToBaidu($urls);
					$ret = $ret['status'] == true ? true : false;
					if ( $ret ) {
						echo '[SUCCESS] 向百度推送', PHP_EOL, print_r($urls, true), PHP_EOL;
					} else {
						echo '[ERROR] 向百度推送失败', PHP_EOL, print_r($urls, true), PHP_EOL;
					}
					unset($ret);
					unset($urls);
				}
				// 向服务接口批量推送知识内容
				$push = $lSearch->createKnowledge($push);
				if ( $push ) {
					// 向历史版本添加新版本
					$mKnowledgeHistory = D('KnowledgeHistory', 'Model', 'Common');
					$insert = $history = $mKnowledgeHistory->addAllData($data);
				}
			}
			if ( $upd ) {
				echo '[SUCCESS] 知识内容更新为已发布成功', PHP_EOL;
			} else {
				echo '[ERROR] 知识内容状态更新为已发布失败', PHP_EOL;
			}
			if ( $push ) {
				echo '[SUCCESS] 创建知识索引成功', PHP_EOL;
			} else {
				echo '[ERROR] 创建知识索引失败', PHP_EOL;
			}
			if ( $insert ) {
				echo '[SUCCESS] 创建知识历史版本成功', PHP_EOL;
			} else {
				echo '[ERROR] 创建知识历史版本失败', PHP_EOL;
			}
		} else {
			echo '[INFO] 定时发布列表为空 ', date('Y-m-d H:i:s', NOW_TIME), PHP_EOL;
		}
		$this->unlock($proccess);
	}


	/**
	 * 百科词条定时发布
	 * 每分钟执行一次
	 */
	public function crontab_publish_wiki() {
		$time = date('Y-m-d H:i:s', NOW_TIME);
		// 先判断是否还有正在进行的任务
		$lock = 'LOCK:WIKI:CRONTAB:PUBLISH';
		if ( $this->_redis->exists($lock) ) {
			exit($time.' [INFO] 进程正在进行，请稍后再试！'.PHP_EOL);
		}
		$this->lock($lock);
		$model = D('Wiki', 'Model', 'Common');
		$where['status'] = 2;
		$where['ptime'] = array('elt', NOW_TIME);
		$order = '';
		// 每次定时任务最多只批量发布 200 条可发布的定时发布词条
		$page = 1;
		$pagesize = 200;
		$total = $model->where($where)->count();
		if ( $total == 0 ) {
			$this->unlock($lock);
			exit($time.' [INFO] 没有需要发布的词条。'.PHP_EOL);
		}
		$list = $model->where($where)->order('ptime asc')->page($page, $pagesize)->select();

		$errors = [];
		$lPublish = D('WikiPublish', 'Logic', 'Common');
		$count = 0;
		foreach ( $list as $i => $item ) {
			$item = $model->convertFields($item, false);
			$ret = $lPublish->Publish($item);
			// var_export($ret);
			// echo 'id: ', $item['id'], PHP_EOL;
			// echo 'title: ', $item['title'], PHP_EOL;
			// echo 'ptime: ', date('Y-m-d H:i:s', $item['ptime']), PHP_EOL;
			// echo 'utime: ', date('Y-m-d H:i:s', $item['utime']), PHP_EOL;
			// echo 'NOW: ', date('Y-m-d H:i:s', NOW_TIME), PHP_EOL;
			if ( $ret ) {
				$count += 1;
			} else {
				array_push(
					$errors, 
					array(
						'id'=>$item['id'],
						'title'=>$item['title'],
						'err'=>$lPublish->getError(),
					)
				);
			}
		}
		$mounts = $total <= $pagesize ? $total : $pagesize;
		if ( $count < $mounts ) {
			$msg = [$time, '[WARNING]', '百科词条定时发布任务失败'];
			array_push($msg, '(共 '.$total.' 条)');
			array_push($msg, PHP_EOL.'以下原因造成的数据未正常发布:');
			foreach ( $errors as $i => $err ) {
				array_push($msg, PHP_EOL.implode('-', $err));
			}
		} else {
			$msg = [$time, '[INFO]', '百科词条定时发布任务成功'];
			if ( $total <= $pagesize ) {
				array_push($msg, '(共 '.$total.' 条)');
			} else {
				array_push($msg, '( '.$pagesize.' / '.$total.' )');
			}
		}
		echo implode(' ', $msg), PHP_EOL;
		$this->unlock($proccess);
	}

	protected function pushKonwledge($record)
	{
		$lPinyin = D('Pinyin','Logic','Common');
		$py = $lPinyin->get_pinyin($record['title']);
		$str = ucfirst($py);

		$record['title_firstletter'] = substr($str, 0,1);
		$record['title_pinyin'] = $py;
		$record['url'] = '/show/?id='.$record['id'];
		$record['id'] = strval($record['id']);
		$record['content'] = clear_all($record['content']);
		$record['rel_news'] = json_decode($record['rel_news'],true);
		$record['rel_house'] = json_decode($record['rel_house'],true);

		return $record;

	}

	/**
	 * 热门百科词条 Cron
	 * @author 羊阳 <yangyang13@leju.com>
	 */
	public function hotWords()
	{
		//查询最热词
		$mV = D('visit_stats', 'Model', 'Common');
		$reci = $mV->query("select relid,count(relid) as hits from visit_stats where reltype='wiki' group by relid order by hits desc limit 6");
		if($reci)
		{
			$time = $this->_beforetime(2);
			foreach($reci as $k=>$v)
			{
				//分别取热词昨天与前天的点击量
				$d = $mV->where(array('relid'=>$v['relid'],'ctime'=>array('BETWEEN',array($time[1]['begin'],$time[1]['end']))))->getField('count(id) as hits',1);
				$f = $mV->where(array('relid'=>$v['relid'],'ctime'=>array('BETWEEN',array($time[2]['begin'],$time[2]['end']))))->getField('count(id) as hits',1);
				$reci[$k]['t'] = strcmp(intval($d), intval($f));

				//获取词条名
				$mW = D('wiki', 'Model', 'Common');
				$reci[$k]['id'] = $mW->where(array('id'=>$v['relid']))->getField('title',1);
			}

			$this->_redis->set('wiki:tag:hot2',$reci);
			return true;
		}

		return false;
	}

	/**
	 * 后台7天数据统计 Cron
	 * @author 羊阳 <yangyang13@leju.com>
	 */
	public function sevenDaysTJ()
	{
		$time = $this->_beforetime(7);

		$result = array(
			'updates' => array(),
			'hits' => array(),
			'words' => array()
		);

		$mW = D('wiki', 'Model', 'Common');
		$mV = D('visit_stats', 'Model', 'Common');

		foreach($time as $v)
		{
			//七天更新趋势
			$result['updates'][] = array(
				'total' => $mW->where(array('ptime'=>array('BETWEEN',array($v['begin'],$v['end']))))->getField('count(id) as total',1),
				'day' => $v['date']
			);
			//七天热度趋势
			$result['hits'][] = array(
				'total' => $mV->where(array('reltype'=>'wiki','ctime'=>array('BETWEEN',array($v['begin'],$v['end']))))->getField('count(id) as total',1),
				'day' => $v['date']
			);
		}

		//七天热词排行
		$words = $mV->query("select relid,count(relid) as hits from visit_stats where reltype='wiki' and ctime between {$time[7]['begin']} and {$time[1]['end']} group by relid order by hits desc limit 8");
		foreach($words as $v)
		{
			$temp = array();
			$temp['name'] = $temp['id'] = $mW->where(array('id'=>$v['relid']))->getField('title',1);
			$temp['count'] = $v['hits'];
			array_push($result['words'], $temp);
		}

		$this->_redis->set('wiki:admin:7days',$result);
		return true;
	}
	/*
	 * 定时推送词条数据并进入影子表
	*/
	public function wikipublish()
	{
		//lock
		$proccess = $this->_wikilock . ':wikipublish';
		if ($this->_redis->exists($proccess))
		{
			exit('进程正在进行，请稍后再试！');
		}
		$this->lock($proccess);
		$wiki = D('Wiki', 'Model', 'Common');
		$where['status'] = 1;
		$where['ptime'] = array('elt',NOW_TIME);
		$list = $wiki->where($where)->order('ptime asc')->select();
	
		if (!empty($list))
		{
			$ids = array();
			$push = array();
			$data = array();
			$titles = array();
			foreach ($list as $key => $item)
			{
				array_push($ids,$item['id']);
				array_push($titles,$item['title']);
				$item['status'] = 9;
				$push[$key] = $this->pushWiki($item);
				$data[$key] = $item;
			}
	
			$update['status'] = 9;
			//$update['version'] = NOW_TIME;
			$where['id'] = array('in',$ids);
			$upd = $wiki->where($where)->save($update);
			if ($upd)
			{
				$lSearch = D('Search', 'Logic', 'Common');
				$push = $lSearch->createWiki($push);
				if ($push)
				{
					// 通过服务接口向字典中追回词条
					$ret = $lSearch->appendDictWords($titles, 'dict_wiki');
						
					$WikiHistory = D('WikiHistory', 'Model', 'Common');
					$insert = $history = $WikiHistory->addAll($data);
				}
			}
			if ($upd)
				echo 'Wiki update status=9 succ'.'\n';
			else
				echo 'Wiki update status=9 fail'.'\n';
			if ($push)
				echo 'createWiki push succ'.'\n';
			else
				echo 'createWiki push fail'.'\n';
			if ($ret)
				echo 'appendDictWords push succ'.'\n';
			else
				echo 'appendDictWords push fail'.'\n';
			if ($insert)
				echo 'WikiHistory add succ'.'\n';
			else
				echo 'WikiHistory add fail'.'\n';
	
		}
		else
		{
			echo 'list is empty ' .date('Y-m-d H:i:s',NOW_TIME).'\n';
		}
		$this->unlock($proccess);
	}
	protected function pushWiki($record)
	{
		$record['url'] = 'show/title='.$record['title'];
		$record['id'] = strval($record['id']);
		$record['content'] = clear_all($record['content']);
		$record['rel_news'] = json_decode($record['rel_news'],true);
		$record['rel_house'] = json_decode($record['rel_house'],true);
		$record['scope'] = "全国";
	
		return $record;
	
	}
}