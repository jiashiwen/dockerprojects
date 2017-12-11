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

	public function index() {
		$this->systemHalt();
	}


	public function __construct()
	{
		$this->_init();
	}

	protected function _init()
	{
		$this->_redis = S(C('REDIS'));
		$todyTime = getDayTime();
		$this->_begin = $todyTime['begin'];
		//$this->_end = $todyTime['end'];
		$this->_days = $this->_beforetime();
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
		if ($this->_redis->exists($proccess))
		{
			exit('进程正在进行，请稍后再试！');
		}
		$this->lock($proccess);
		$mKnowledge = D('Knowledge', 'Model', 'Common');
		$where['status'] = 1;
		$where['ptime'] = array('elt',NOW_TIME);
		$list = $mKnowledge->where($where)->order('ptime asc')->select();

		if (!empty($list))
		{
			$ids = array();
			$push = array();
            $lInfos = D('Infos','Logic','Common');
			foreach ($list as $key => $item)
			{
				array_push($ids,$item['id']);
				$item['status'] = 9;
				$push[$key] = $this->pushKonwledge($item);
				$data[$key] = $item;
                // 向新闻池推送数据
                $r = $lInfos->pushNewsPool($item);
                if ($r['status'] == 1)
                {
                    echo 'push newspool succ'.'\n';
                }
                else
                {
                    echo 'push newspool fail'.'\n';
                }
			}

			$update['status'] = 9;
			$upd = $mKnowledge->updateAllData($ids,$update);
			if ($upd)
			{
				$lSearch = D('Search', 'Logic', 'Common');
				$push = $lSearch->createKnowledge($push);
				if ($push)
				{
					$mKnowledgeHistory = D('KnowledgeHistory', 'Model', 'Common');
					$insert = $history = $mKnowledgeHistory->addAllData($data);
				}
			}
			if ($upd)
				echo 'Knowledge update status=9 succ'.'\n';
			else
				echo 'Knowledge update status=9 fail'.'\n';
			if ($push)
				echo 'createKnowledge push succ'.'\n';
			else
				echo 'createKnowledge push fail'.'\n';
			if ($insert)
				echo 'KnowledgeHistory add succ'.'\n';
			else
				echo 'KnowledgeHistory add fail'.'\n';

		}
		else
		{
			echo 'list is empty ' .date('Y-m-d H:i:s',NOW_TIME).'\n';
		}
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