<?php
/**
 * 前台数据逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class FrontLogic {

	protected $redis = null;
	protected $_cache_time = 86400;
	protected $_flush = false;
	protected $_public_tpl = array();

	public function __construct() {
		$this->redis = S(C('REDIS'));
	}

	public function setFlush($flush=false) {
		if ( !!$flush ) {
			$this->_flush = true;
		}
		return $this;
	}
	public function getPCPublicTemplate( $flush=false ) {
		$key_h = 'TPL:PC:COMMON:HEADER';
		$key_f = 'TPL:PC:COMMON:FOOTER';
		$key_l = 'TPL:PC:COMMON:LINKS';
		$keys = array('h'=>$key_h, 'f'=>$key_f, 'l'=>$key_l);
		if ( $flush == true ) {
			$this->redis->del($keys);
		}

		$result = array();
		$list = $this->redis->mget($keys);
		// $hurl = 'http://bj.leju.com/include/leju/pc/2016/topnav.shtml';
		// $furl = 'http://bj.leju.com/include/leju/pc/2016/footer.shtml';
		$hurl = 'http://bj.leju.com/include/leju/pc/2017/topnav.shtml';
		$furl = 'http://bj.leju.com/include/leju/pc/2017/footer.shtml';
		$lurl = 'http://bj.leju.com/include/baike/links.html';
		if ( !$list[0] ) {
			$ret = curl_get($hurl);
			if ( $ret['status'] ) {
				$list[0] = $ret['result'];
				$this->redis->setex($key_h, $this->_cache_time, $ret['result']);
			}
		}
		if ( !$list[1] ) {
			$ret = curl_get($furl);
			if ( $ret['status'] ) {
				$list[1] = $ret['result'];
				$this->redis->setex($key_f, $this->_cache_time, $ret['result']);
			}
		}
		if ( !$list[2] ) {
			$ret = curl_get($lurl);
			if ( $ret['status'] ) {
				$list[2] = $ret['result'];
				$this->redis->setex($key_l, $this->_cache_time, $ret['result']);
			}
		}

		$result['header'] = &$list[0];
		$result['footer'] = &$list[1];
		$result['links'] = &$list[2];
		return $result;
	}

	/**
	 * 首页热门词条数据获取
	 */
	public function getHotWords() {
		$key = 'WIKI:INDEX:HOT';
		$result = $this->redis->Get($key);
		if ( !$result || $this->_flush ) {
			$where = array('status'=>9);
			$order = 'hits DESC';
			$page = 1; $pagesize = 12;
			$fields = array('id', 'title', 'cateid', 'hits');
			$result = D('Wiki', 'Model', 'Common')->field($fields)->where($where)->order($order)->page($page, $pagesize)->select();
			if ( $result ) {
				$this->redis->set($key, $result, $this->_cache_time);
			}
		}
		return $result;
	}

	/**
	 * @author hongwang@leju.com
	 * @desc 获取热门知识20条
	 * @return bool|mixed
	 */
	public function getHotSearchList($city_code,$city_cn)
	{
		$rkey = 'kb:rank:'.$city_code;
		$data = $this->redis->get($rkey);
		if (!$data)
		{
		   $data = $this->getknowledgerank($city_code,$rkey);
		}
		if ($data)
		{
			$lSearch = D('Search','Logic','Common');
			$order = array('_docupdatetime', 'desc');
			$fields = array('_id','_title');
			$prefix = array();
			$opts = array(array('false', '_deleted'),array("{$city_cn},全国",'_scope'));
			$_ids = array();
			foreach ($data as $k=>$item) {
				// @TODO: ??? 可以优化？逻辑看上去是有问题的。
			// 	if ( intval($item['id']) > 0 ) {
			// 		array_push($_ids, $item['id']);
			// 	}
			// if ( !empty($_ids) ) {
			// 	array_push($opts, array(implode(',', $_ids), '_id'));
			// 	var_dump($opts);
			// 	$result = $lSearch->select(1, 1,'',$opts, $prefix, $order, $fields);
			// 	var_dump($result);
			// }
				if ( intval($item['id']) <= 0 ) {
					unset($data[$k]);
				}
				$opts = array(array('false', '_deleted'),array("{$item['id']}",'_id'),array("{$city_cn},全国",'_scope'));
				$result = $lSearch->select(1, 1,'',$opts, $prefix, $order, $fields);
				if ($result['pager']['total'] <= 0) {
					unset($data[$k]);
				}
			}
		}
		return $data;
	}


	public function getSuggest( $keyword='', $city_cn='北京', $city_code='bj') {
		// $cities = C('CITIES.ALL');
		// $city_cn = isset($cities[$city]) ? $cities[$city]['cn'] : '';
		if ( !$keyword ) {
			//输出静态数据
			$result = array(
				'kb' => array(),
				'tag' => array(),
			);
			$kblist = $this->getHotSearchList($city_code, $city_cn);
			if ( $kblist ) {
				foreach ( $kblist as $k => $item ) {
					array_push($result['kb'], array(
						'id' => $item['id'],
						'title' => $item['title'],
						'url' => url('show', array('id'=>$item['id']), 'pc', 'baike') . '#wt_source=pc_baike_rszs',
					));
				}
			}
			$where = array('status'=>9);
			$order = 'hits desc';
			$fields = array('id', 'title', 'cateid');
			$page = 1;
			$pagesize = 20;
			$tags = D('Wiki','Model','Common')->field($fields)->where($where)->order($order)->page($page, $pagesize)->select();
			if ( $tags ) {
				foreach ( $tags as $k => $item ) {
					array_push($result['tag'], array(
						'title' => $item['title'],
						'id' => $item['id'],
						'url' => url('show', array($item['id'], $item['cateid']), 'pc', 'wiki') . '#wt_source=pc_baike_gzjd',
					));
				}
			}
		} else {
			// 通过关键词进行查找
			$result['kb'] = $this->getKonwledgeSuggest($keyword,$city_cn);
			$result['tag'] = $this->getTagsSuggest($keyword);
		}
		return $result;
	}

	/**
	 * 知识数据的联想词搜索，根据指定的关键词进行联想数据筛选搜索
	 *
	 */
	private function getKonwledgeSuggest ( $keyword, $city ) {
		$engine = D('Search', 'Logic', 'Common');
		$opts = array(
			array('false', '_deleted'),
			array("{$city},全国",'_scope'),
		);
		$result = array();
		$prefix = array(array($keyword, "_multi.title_prefix"));
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_scope','_origin.content');
		$search = $engine->select(1, 20, '', $opts, $prefix, $order, $fields);
		if ($search && $search['pager']['total'] > 0) {
			foreach ($search['list'] as $key => $value) {
				array_push($result, array(
					'id' => $value['_id'],
					'title' => $value['_title'],
					'url' => url('show', array('id'=>$value['_id']), 'pc', 'baike') . '#wt_source=pc_baike_rszs',
				));
			}
		}
		return $result;
	}

	/**
	 * 百科词条的联想词搜索，根据指定的关键词进行联想数据筛选搜索
	 *
	 */
	private function getTagsSuggest($keyword) {
		$result = array();
		$page = 1;
		$pagesize = 20;
		$lSearch = D('Search','Logic','Common');
		$opts = array(array('false', '_deleted'));
		// $order = array('_multi.title_pinyin', 'asc');
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id', '_title', '_origin.cateid');
		$prefix = array(array($keyword, "_multi.title_prefix"));
		$ret = $lSearch->select($page, $pagesize, '', $opts, $prefix, $order, $fields, 0, 'wiki');
		if ( $ret['list'] ) {
			foreach ( $ret['list'] as $i => $item ) {
				$cateid = intval($item['_origin']['cateid']);
				array_push($result, array(
					'title' => $item['_title'],
					'id' => $item['_id'],
					'url' => url('show', array($item['_id'], $cateid), 'pc', 'wiki') . '#wt_source=pc_baike_gzjd',
				));
			}
		}
		return $result;
	}

	public function getknowledgerank($city,$rkey,$limit = 20) {

		$DataStatistics = D('DataStatistics','Model','Common');
		$result = $DataStatistics->where(array('chartid'=>$rkey))->order('ctime desc')->find();
		if ($result)
		{
			$data = json_decode($result['data'],true);
		}
		else
		{
			$data = array();
			$mVisitStats = D('VisitStats', 'Model', 'Common');
			$_days = $this->_beforetime();
			$begin = $_days['7']['begin'];
			$end = $_days['1']['end'];
			$ttl = 24*3600*2;

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
		}
		if ($data)
		{
			$this->redis->setex($rkey,$ttl,json_encode($data));
		}

		return $data;
	}

	protected function _beforetime($days=7)
	{
		$todyTime = getDayTime();
		$difftime = 86400;
		$date = array();
		for ( $i=$days; $i > 0; $i-- )
		{
			$date[$i]['date'] = date('m-d',($todyTime['begin'] - ($difftime * ($i-1))));
			$date[$i]['begin'] = $todyTime['begin'] - ($difftime * ($i-1));
			$date[$i]['end'] = $date[$i]['begin'] + 86399;
		}
		return $date;
	}

	public function getKnowledgeByTags($tags=array(), $city_cn='', $id=0, $num=5, $random=true)
	{
		$list = array();
		if (!empty($tags) && is_array($tags))
		{
			$scopes = array('全国');
			if ( $city_cn!='' && $city_cn!='全国' ) {
				array_push($scopes, $city_cn);
			}
			$lSearch = D('Search','Logic','Common');
			$order = array('_doccreatetime', 'desc');
			$fields = array('_id','_title','_origin.cover','_origin.ptime','_tags');
			$ids = array();

			// 新的，添加了随机查询
			$opts = array(
				array(implode(',', $tags), '_tags'),
				array('false', '_deleted'),
				array(implode(',', $scopes), '_scope'),
			);
			if ( $id > 0 ) {
				array_push($opts, array("!{$id}", '_id'));
				array_push($ids, $id);
			}

			/**
			 * select 与 getRecommendBaike 的返回结构不一致，会出问题
			 */
			$random = false;
			if ( $random!==true ) {
				$result = $lSearch->select(1, $num, '', $opts, $prefix=array(), $order, $fields);
			} else {
				$result = $lSearch->getRecommendBaike($num, $prefix=array(), $opts);
				$result['list'] = &$result['result'];
			}
			// var_dump($random, $num, $opts, $result);
			if ( $result['pager']['total'] > 0 ) {
				foreach ( $result['list'] as $k => $item ) {
					if ( !in_array($item['_id'], $ids)) {
						array_push($ids, $item['_id']);
						$list[$k]['title'] = $item['_title'];
						$list[$k]['id'] = $item['_id'];
						$list[$k]['cover'] = $item['_origin']['cover'];
						$list[$k]['ptime'] = date('Y-m-d H:i',$item['_origin']['ptime']);
						$list[$k]['tags'] = str_replace(' ', ',', $item['_tags']);
						$list[$k]['url'] = url('show', array('id'=>$item['_id']), 'pc', 'baike');
					}
				}
			}
			// end 新版本
		}

		return $list;
	}

	
}