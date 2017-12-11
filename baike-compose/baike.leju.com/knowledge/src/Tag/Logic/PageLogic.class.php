<?php
/**
 * 页面核心逻辑基础类
 * @author Robert <yongliang1@leju.com>
 */

namespace Tag\Logic;

class PageLogic{
	protected $redis = null;
	protected $_cache_time = 86400;
	protected $mTags = null;
	protected $_flush = false;

	public function __construct() {
		$this->redis = S(C('REDIS'));
		$this->mTags = D('Tags', 'Model', 'Common');
	}

	public function setFlush($flush=false) {
		if ( !!$flush ) {
			$this->_flush = true;
		}
		return $this;
	}
	/**
	 * 词条首页焦点图
	 */
	public function getFocus( $page=1, $pagesize=5 ) {
		$key = 'WIKI:INDEX:FOCUS';
		$recommends = $this->redis->get($key);
		if ( !$recommends || $this->_flush ) {
			$ret = D('Recommend', 'Logic', 'Common')->getRecommends('wiki', false, 1);
			$ids = array();
			$recommends = array();
			foreach ( $ret as $i => $item ) {
				$id = intval($item['relid']);
				array_push($ids, $id);
				$recommends[$id] = array(
					'id' => $id,
					'cover' => $item['extra']['img'],
					'title' => trim($item['extra']['title']),
					'ctime' => $item['ctime'],
				);
			}
			if ( empty($ids) ) {
				return array();
			}
			$fields = array('id', 'cateid');
			$where = array(
				'status' => 9,
				'id' => array('in', $ids),
			);
			$mWiki = D('Wiki', 'Model', 'Common');
			$result = $mWiki->field($fields)->where($where)->select();
			$exists = false;
			$_ids = [];
			foreach ( $result as $i => $item ) {
				$id = intval($item['id']);
				if ( array_key_exists($id, $recommends) ) {
					$recommends[$id]['flag'] = true;
					$recommends[$id]['cateid'] = $item['cateid'];
					$exists = true;
					array_push($_ids, $id);
				}
			}
			$recommends = array_intersect_key($recommends, array_flip($_ids));
			if ( $exists ) {
				$this->redis->Set($key, $recommends, $this->_cache_time);
			}
		}
		return $recommends;
		// var_dump($result, $recommends, $ids, $mWiki->getLastSql());exit;
	}


	/*
	 * 词条首页名人
	 */
	public function getPersons ( $page=1, $pagesize=6 ) {
		$key = 'WIKI:INDEX:PERSONS';
		$result = $this->redis->get($key);
		if ( !$result || $this->_flush ) {
			// 首页推荐的人物数据
			$where = array('status'=>9, 'cateid'=>2);
			$order = 'hits DESC';
			$fields = array('id', 'title', 'cateid', 'hits', 'cover');
			$result = D('Wiki', 'Model', 'Common')->field($fields)->where($where)->order($order)->page($page, $pagesize)->select();
			if ( $result ) {
				$this->redis->set($key, $result, $this->_cache_time);
			}
		}
		return $result;
	}

	/*
	 * 词条首页房产机构
	 */
	public function getCompanies ( $page=1, $pagesize=6 ) {
		$key = 'WIKI:INDEX:COMPANIES';
		$result = $this->redis->get($key);
		if ( !$result || $this->_flush ) {
			// 首页推荐的人物数据
			$where = array('status'=>9, 'cateid'=>1);
			$order = 'hits DESC';
			$fields = array('id', 'title', 'cateid', 'hits', 'cover');
			$result = D('Wiki', 'Model', 'Common')->field($fields)->where($where)->order($order)->page($page, $pagesize)->select();
			if ( $result ) {
				$this->redis->set($key, $result, $this->_cache_time);
			}
		}
		return $result;
	}

	/*
	 * 首页最新词条
	 */
	public function getLatest ( $page=1, $pagesize=12 ) {
		$key = 'WIKI:LIST:LATEST';
		$result = $this->redis->get($key);
		if ( !$result || $this->_flush ) {
			// 首页推荐的人物数据
			$where = array('status'=>9, 'cateid'=>1);
			$order = 'ptime DESC';
			$fields = array('id', 'title', 'cateid', 'hits', 'cover');
			$result = D('Wiki', 'Model', 'Common')->field($fields)->where($where)->order($order)->page($page, $pagesize)->select();
			if ( $result ) {
				$this->redis->set($key, $result, $this->_cache_time);
			}
		}
		return $result;
	}

	/*
	 * 全部词条
	 */
	public function getList( $page=1, $pagesize=16, $cateid=false ) {
		$where = array();
		if ( $cateid ) {
			$where['cateid'] = intval($cateid);
		}
		$order = 'utime DESC';
		$result = $this->_getList($where, $order, $page, $pagesize);
		return $result;
	}

	/**
	 * 按条件获取词条列表
	 */
	public function _getList( $conditions=[], $order='id DESC', $page=1, $pagesize=20, $fields=array() ) {
		$conditions['status'] = 9;
		if ( empty($fields) ) {
			$fields = array('id', 'title', 'cateid', 'cover', 'firstletter', 'hits', 'utime', 'tags', 'tagids', 'editor');
		}
		$result = [];
		$mWiki = D('Wiki', 'Model', 'Common');
		$total = $mWiki->where($conditions)->count();
		$result['list'] = $mWiki->field($fields)
					  ->where($conditions)
					  ->order($order)
					  ->page($page, $pagesize)
					  ->select();
		// echo '<!--', PHP_EOL, $mWiki->getLastSql(), PHP_EOL, 'Total', $total, PHP_EOL, '-->', PHP_EOL;
		$result['pager'] = array(
			'total' => $total,
			'page' => $page,
			'pagesize' => $pagesize,
			'pagecount' => ceil($total/$pagesize),
		);
		return $result;
	}

	/**
	 * 获取词条搜索搜索结果列表数据
	 */
	public function getSearchList( $keyword, $page=1, $pagesize=16 ) {
		$keyword = trim($keyword);
		$keylength = mb_strlen($keyword, 'utf8');
		if ( $keylength == 0 ) {
			return array('total'=>0,'result'=>array());
		}

		$lSearch = D('Search','Logic','Common');
		$opts = array(array('false', '_deleted'));
		if ( $cateid!==false ) { $opts[] = array($cateid, '_multi.cateid'); }

		$list = array();
		if ( $keylength<2 ) {
			// 搜索关键词内容少于2个字的时候，使用数据库模糊查询
			$where = array('status'=>9, 'title'=>array('like', '%'.$keyword.'%'));
			$fields = array('id','title','cateid','cover','firstletter');
			$mWiki = D('Wiki', 'Model', 'Common');
			$total = $mWiki->where($where)->count();
			if ( $total > 0 ) {
				$ret = $mWiki->field($fields)->where($where)->page($page, $pagesize)->select();
				foreach ( $ret as $i => $item ) {
					array_push($list, array(
						'id' => $item['id'],
						'title' => $item['title'],
						'cateid' => $item['cateid'],
						'cover' => $item['cover'],
						'firstletter' => $item['firstletter'],
					));
				}
			}
		} else {
			// 搜索关键词内容大于等于 2 个字的时候，使用服务接口查询
			// $order = array('_multi.title_pinyin', 'asc');
			$order = array('_multi.title_prefix', 'asc');
			$fields = array('_id', '_origin', '_multi');
			$ret = $lSearch->select($page, $pagesize, $keyword, $opts, array(), $order, $fields, 0, 'wiki');
			$total = intval($ret['pager']['total']);
			if ( $total > 0 ) {
				foreach ( $ret['list'] as $i => $item ) {
					array_push($list, array(
						'id' => intval($item['_id']),
						'title' => $item['_origin']['title'],
						'cateid' => $item['_origin']['cateid'],
						'cover' => $item['_origin']['cover'],
						'firstletter' => $item['_multi']['firstletter'],
					));
				}

			}
		}
		return array('total'=>$total, 'result'=>$list);
	}


	/*
	 * 相关资讯
	 */
	public function getNews($detail)
	{
		//百科相关咨询
		$recommends = D('Infos', 'Logic', 'Common');
		$news = array();
		if(!empty($detail['news']))
		{
			//拼接ids
			$temp_news = $temp_push = array();
			foreach($detail['news'] as $v)
			{
				$temp_news[$v['id']] = $v;
				array_push($temp_push, $v['id']);
			}

			$info = $recommends->getNews($temp_push);

			//过滤已删除的新闻
			foreach($temp_news as $k=>$v)
			{
				if(array_key_exists($k, $info))
				{
					$temp_news[$k]['title'] || $temp_news[$k]['title'] = $info[$k]['title'];
					$temp_news[$k]['media'] = $info[$k]['media'];
					$temp_news[$k]['createtime'] = $info[$k]['createtime'];
					$temp_news[$k]['m_url'] = $info[$k]['m_url'];
				}
				else
				{
					unset($temp_news[$k]);
				}
			}

			$news = array_values($temp_news);
		}
		else
		{
			//没有词条的话需要解析标签
			$tags = $recommends->relNews($detail['tags'], 5);
			$news = array_values($tags);
		}
		return $news;
	}

	/*
	 * 相关楼盘
	 */
	public function getHouse($detail)
	{
		$recommends = D('Infos', 'Logic', 'Common');
		if(!empty($detail['house']))
		{
			//拼接ids
			$temp_houses = $temp_push = array();
			foreach($detail['house'] as $v)
			{
				$temp_houses[$v['site'].$v['hid']] = $v;
				array_push($temp_push, $v['site'].$v['hid']);
			}

			$info = $recommends->getHouse($temp_push);

			//过滤已删除的楼盘
			foreach($temp_houses as $k=>$v)
			{
				if(array_key_exists($k, $info))
				{
					$temp_houses[$k]['salephone'] = $info[$k]['phone_extension'] ? "4006108616,2{$info[$k]['phone_extension']}" : '4006108616';
					$temp_houses[$k]['price_display'] = $info[$k]['price_display'];
					$temp_houses[$k]['pic_s320'] = $info[$k]['pic_s320'];
					$temp_houses[$k]['m_url'] = $info[$k]['m_url'];
					$temp_houses[$k]['city'] = $info[$k]['city'];
				}
				else
				{
					unset($temp_houses[$k]);
				}
			}

			return array_values($temp_houses);
		}
	}

	/**
	 * 将 tags 标签文本列表，转换为 tags id 列表
	 */
	public function convertTagToTagid( $tags=array() ) {
		$result = array();
		if ( !is_array($tags) || empty($tags) ) {
			return $result;
		}

		$tags = implode("','", $tags);
		if ( $tags != '' ) {
			$result = $this->mTags->where("name in ('{$tags}')")->select();
		}
		return $result;
	}


	/**
	 * 获取相关新闻 PC版
	 * 规则 按当前百科词条设置的标签，提取新闻池近期新闻
	 * 4、相关新闻
	 * 调取该词条关联的标签在发布系统中的新闻按照时间倒序排序，若为空则，调取标签房产下的新闻，最多显示4条
	 */
	public function getRelationNews( $tagids=array(), $device='pc' ) {
		$_ids = is_array($tagids) ? $tagids : array($tagids);
		$tagids = array();
		foreach ( $_ids as $i => $id ) {
			$id = intval($id);
			if ( $id>=0 ) array_push($tagids, $id);
		}
		if ( empty($tagids) ) {
			return array();
		}
		sort($tagids);
		$tagids = implode('|', $tagids);
		$key = "WIKI:RELNEWS:{$tagids}";
		$list = $this->redis->Get($key);
		if ( $list==false ) {
			$info = D('Infos', 'Logic', 'Common');
			$opts = array( '{deleted@eq}0', '{tags_id@eq}'.$tagids, );
			$fields=array('id', 'title', 'tags', 'tags_id', 'createtime', 'click_count', 'zhaiyao', 'picurl', 'city', 'url');
			$order='{createtime}desc';
			$list = $info->selectNews('house_news', 1, 100, $opts, $fields, $order);
			if ( $list && $list['message']=='成功' ) {
				$list = $list['data'];
				$lTags = D('Tags', 'Logic', 'Common');
				foreach ( $list as $i => &$news ) {
					$tags_id = trim(trim($news['tags_id']), ',');
					if ( $tags_id=='' ) {
						continue;
					}
					$news['tags_id'] = $tags_id;
					$tagsinfo = $lTags->getTagnamesByTagids(explode(',', $tags_id));
					$news['tagsinfo'] = $tagsinfo;
				}
			} else {
				$list = array();
			}
			$expire = rand(-1800, 1800);
			$expire = !empty($list) ? $expire + $this->_cache_time : $expire ;
			$expire = intval(abs($expire));
			$this->redis->SetEx($key, $expire, json_encode($list));
		}
		return $list;
	}

}
