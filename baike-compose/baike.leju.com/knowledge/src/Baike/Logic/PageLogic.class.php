<?php
/**
 * 页面核心逻辑基础类
 * @author Robert <yongliang1@leju.com>
 */

namespace Baike\Logic;
// use Think\Model;
class PageLogic /* extends Model */{
	protected $_flag = array(
		'flush' => false,
	);
	// protected $autoCheckFields = false;
	// protected $name = 'page';
	protected $mTags = null;

	public function __construct() {
		$this->mTags = D('Tags', 'Model', 'Common');
	}

	public function setFlag($name, $value) {
		$this->_flag[$name] = $value;
	}

	// public function __construct() {}
	/**
	 * 获取焦点列表
	 * @param $num int 指定获取焦点信息的数量
	 * @return array
	 * = 结构 =
	 *	[
	 *		{
	 *			'img' =>  'string',
	 *			'title' =>  'string',
	 *			'url' =>  'string',
	 *		}, {}...
	 *	]
	 * @changelog : 此方法原调用方为 mobile 首页，现已将此方法移至 MobilePageLogic 中 ， 后期判断是否仍有使用，无使用方，则删除此方法
	 */
	public function getForces( $city,$num=5 )
	{
		$lSearch = D('Search','Logic','Common');
		$opts = array(
			array('false', '_deleted'),
			array("{$city},全国",'_scope'),
			array('!0', '_multi.rcmd_time'),
		);
		$order = array('_multi.rcmd_time', 'desc');
		$fields = array('_id','_title','_version','_origin');
		$total = 0;
		$prefix = array();

		$lSearch->getToken(true);
		$result = $lSearch->select(1, $num, '', $opts, $prefix, $order, $fields);

		if ($result['pager']['total'] > 0)
		{
			foreach ($result['list'] as $key => $item)
			{
				$list[$key]['id'] = $item['_origin']['id'];
				$list[$key]['title'] = !empty($item['_origin']['rcmd_title']) ? $item['_origin']['rcmd_title'] : $item['_origin']['title'];
				$list[$key]['cover'] = !empty($item['_origin']['rcmd_cover']) ? $item['_origin']['rcmd_cover'] : $item['_origin']['cover'] ;
				$list[$key]['url'] = C('FRONT_URL.show'). $item['_origin']['id'];
			}
		}
		return $list;
	}

	/**
	 * @author hongwang@leju.com
	 * @desc 获取推荐列表，推荐列表不全用知识列表补全
	 * @param $cateid
	 * @param int $num
	 * @return array
	 */
	public function getRcmdKnoledgeList($cateid,$city,$num=4)
	{
		$lSearch = D('Search','Logic','Common');
		$lCate = D('Cate','Logic','Common');
		$path = $lCate->getCatePathById($cateid);
		$path = $path . '-';
		$opts = array(
			array('false', '_deleted'),
			array("{$city},全国",'_scope'),
			array('!0', '_multi.rcmd_time'),
		);
		$prefix = array(array("{$path}", '_multi.catepath'));
		$order = array('_multi.rcmd_time', 'desc');
		$fields = array('_id','_title','_origin.rcmd_title','_origin.rcmd_cover','_origin.cover');
		$result = $lSearch->select(1, $num, '', $opts, $prefix, $order, $fields);
		$total = $result['pager']['total'];
		$list = array();
		$exids = array();
		// var_dump($opts, $prefix, $order, $result);
		if ($total > 0)
		{
			$num = $total == $num ? 0 : $num - $total;
			$list = $result['list'];
			foreach ($list as $k => $item)
			{
				array_push($exids,$item['_id']);
			}
		}
		if ($num > 0)
		{
			$order = array('_docupdatetime', 'desc');
			// $fields = array('_id','_title','_origin');
			$opts = array(
				array('false', '_deleted'),
				array("{$city},全国",'_scope'),
			);
			if ($exids)
			{
				foreach ($exids as $id)
				{
					array_push($opts,array("!{$id}","_id"));
				}
			}

			$prefix = array(array("{$path}", '_multi.catepath'));
			$result = $lSearch->select(1, $num,'',$opts,$prefix, $order, $fields);
			// var_dump($num, $opts, $order, $prefix, $result);
			if ($result['pager']['total'] > 0 )
			{
				foreach ($result['list'] as $k=>$item)
				{
					array_push($list,$item);
				}
			}
		}
		return $list;
	}

	/**
	 * 获取知识顶级分类
	 * @return array
	 *	= 结构 =
	 *	[
	 *		{
	 *			'icon': 'string',
	 *			'title': 'string',
	 *			'url': 'string',
	 *		}, {}...
	 *	]
	 */
	public function getTopCategories ()
	{
		$icon = 'l_0';//css class
		$maxnum = 4; //每行显示最多个数
		$result = array();
		$all = array(
			'title'=>'全部知识',
			'icon'=>'l_05 all',
			'url'=>C('FRONT_URL.map'),
			'href'=>url('map', array(), 'touch'),
		);
		$lCate = D('Cate','Logic','Common');
		$topcate = $lCate->getTopCate();
		if (!$topcate)
		{
			//容错，查库
			$mCategories = D('Categories','Model','Common');
			$list = $mCategories->frontTopList($maxnum);
			$ids = array();

			foreach ($list as $key => $item)
			{
				$ids[] = $item['id'];
				$result[$key]['id'] = $item['id'];
				$result[$key]['title'] = $item['name'];
				$result[$key]['icon'] = $icon . ($key+1);
				$result[$key]['href'] = 'javascript:;';
			}
		}
		else
		{
			$i = 0;
			foreach ($topcate as $key => $value) {
				$ids[] = $key;
				$result[$i]['id'] = $key;
				$result[$i]['title'] = $value;
				$result[$i]['icon'] = $icon.($i+1);
				$result[$i]['href'] = 'javascript:;';
				$i++;
				if ($i==$maxnum)
					break;
			}
		}
		if (!empty($result))
			$result[] = $all;

		return array('list'=>$result,'ids'=>$ids);
	}

	/**
	 * 获取最新知识列表
	 * @param $num int 指定获取焦点信息的数量
	 * @return array
	 * = 结构 = (限制 => 只输出已审核数据)
	 *	[
	 *		{
	 *			'cover': 'string',
	 *			'title': 'string',
	 *			'url': 'string',
	 *			'tags': ['string','...'],
	 *			'ctime': 'Y-m-d H:i:s',
	 *		}, {}...
	 *	]
	 */
	public function getLatestKB ($city) {
		$lCate = D('Cate','Logic','Common');
		$topcate = $lCate->getTopCate();
		$ids = array_keys($topcate);
		$lSearch = D('Search','Logic','Common');
		$order = array('_docupdatetime', 'desc');
		$fields = array('_id','_title','_version','_origin');
		//array('_multi.top_time','desc');
		foreach ($topcate as $id=>$name)
		{
			$opts = array(array('false', '_deleted'),array("{$city},全国",'_scope'));
			$topcatepath = "0-{$id}-";
			$topKB = $this->getTopKB($lSearch,$city,$topcatepath);
			$num = 2;
			$tops = array();
			if ($topKB !== false)
			{
				$num -= 1;
				$exid = $topKB['_id'];
				array_push($opts, array("!{$exid}","_id"));

				$tops['id'] = $topKB['_origin']['id'];
				$tops['title'] = ($topKB['_origin']['top_time'] > 0
					&& !empty($topKB['_origin']['top_title'])) ? $topKB['_origin']['top_title'] : $topKB['_origin']['title'];
				$tops['cover'] = ($topKB['_origin']['top_time'] > 0
					&& !empty($topKB['_origin']['top_cover'])) ? $topKB['_origin']['top_cover'] : $topKB['_origin']['cover'];
				$tops['url'] = url('show', array($topKB['_origin']['id']));
				$tops['tags'] = explode(' ',$topKB['_origin']['tags']);
				// 标签转换为 id 与标签名称
				$tops['tagsinfo'] = $this->convertTagToTagid($tops['tags']);
				$tops['ctime'] = date('Y-m-d H:i:s',$topKB['_origin']['ctime']);
			}
			$list = array();
			$prefix = array(array("{$topcatepath}", '_multi.catepath'));
			$result = $lSearch->select(1, $num,'',$opts,$prefix, $order, $fields);

			if ($result['pager']['total'] > 0)
			{
				foreach ($result['list'] as $key => $item)
				{
					$list['list'][$key]['id'] = $item['_origin']['id'];
					$list['list'][$key]['title'] = ($item['_origin']['top_time'] > 0
						&& !empty($item['_origin']['top_title'])) ? $item['_origin']['top_title'] : $item['_origin']['title'];
					$list['list'][$key]['cover'] = ($item['_origin']['top_time'] > 0
						&& !empty($item['_origin']['top_cover'])) ? $item['_origin']['top_cover'] : $item['_origin']['cover'];
					$list['list'][$key]['tags'] = explode(' ',$item['_origin']['tags']);
					// 标签转换为 id 与标签名称
					$list['list'][$key]['tagsinfo'] = $this->convertTagToTagid($list['list'][$key]['tags']);
					$list['list'][$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ctime']);
				}
			}
			$list['cateid'] = $id;
			$list['name'] = $name;
			$list['topKB'] = $tops;
			$return[] = $list;
		}
		return $return;
	}

	/**
	 * 获取热门词条
	 * @param $num int 指定获取焦点信息的数量
	 * @return array
	 * = 结构 = (限制 => 只输出已审核数据)
	 *	[
	 *		{
	 *			'word': 'string',
	 *			'rank': 'up|down',
	 *		}, {}...
	 *	]
	 */
	public function getHotWords () {
		$result = D('Front', 'Logic','Common')->getHotWords();
		return array_slice($result, 0, 6);
	}

	private function getTopKB($lSearch,$city,$path)
	{
		$opts = array(
			array('false', '_deleted'),
			array("{$city},全国",'_scope'),
			array('!0', '_multi.top_time'),
		);
		$prefix = array(array("{$path}", '_multi.catepath'));
		$order = array('_multi.top_time', 'desc');
		$fields = array('_id','_title','_version','_origin');
		$result = $lSearch->select(1,1,'',$opts,$prefix, $order, $fields);
		if ($result['pager']['total'] >= 1)
		{
			return $result['list']['0'];
		}
		return false;
	}

	/**
	 * 将 tags 标签文本列表，转换为 tags id 列表
	 */
	public function convertTagToTagid( $tags=array() ) {
		$result = array();
		if ( !is_array($tags) || empty($tags) ) {
			return $result;
		}
		$result = D('Tags', 'Logic', 'Common')->getTagnamesByTags($tags);
		return $result;
	}

	/**
	 * 将 tagids 转换为 tagsinfo
	 */
	public function convertTagidsToTagsinfo( $tagids=array() ) {
		$result = D('Tags', 'Logic', 'Common')->getTagnamesByTagids($tagids);
		return $result;
	}

	/**
	 * 猜你喜欢
	 */
	public function guestRandom( $num=6, $catepath='0-', $city='', $tags='' ) {
		// 组建接口调用
		$src = D('Search', 'Logic', 'Common');
		$total = $num + 10;
		$opts = array();
		$prefix = array();
		$cates = array();

		$cities = array();
		if ( !in_array($city, array('','全国')) ) {
			array_unshift($cities, $city);
		}
		$opts['scope'] = array(implode(',', $cities), '_scope');
		$time_start = ( intval(strtotime('today')) + 86399 ); // 24 * 3600 - 1
		$time_end = strtotime('-3 months', $time_start+1) * 1000;
		$time_start = $time_start * 1000;
		$opts['range'] = array("[{$time_end},{$time_start}]", '_docupdatetime');
		if ( $tags!='' ) { $opts['tags'] = array($tags, '_tags'); }
		$prefix['catepath'] = array($catepath, '_multi.catepath');
		$ret = $src->getRecommendBaike( $total, $prefix, $opts );
		if ( $ret['result'] ) {
			$result['list'] = array();
			foreach ( $ret['result'] as $i => $_item ) {
				if ( empty($cates) ) {
					$cates = explode('-', $_item['_multi']['catepath']);
				}
				array_push($result['list'], array(
					'title' => $_item['_title'],
					// 'tags' => $_item['_tags'],
					'url' => url('show', array('id'=>$_item['_id']), 'pc', 'baike'),
				));
			}
		}
		$result['list'] = array_slice($result['list'], 0, $num);
		$result['total'] = $ret['pager']['total'];
		return $result;
	}
}