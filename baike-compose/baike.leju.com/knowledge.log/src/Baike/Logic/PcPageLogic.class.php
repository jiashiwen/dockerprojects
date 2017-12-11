<?php
/**
 * PC 端页面逻辑
 * @author Robert <yongliang1@leju.com>
 */

namespace Baike\Logic;

class PcPageLogic extends PageLogic {

	protected $cate_pagesize = 10;
	protected $agg_pagesize = 10;
	protected $search_pagesize = 10;

	protected $pages = array(
		'index' => array(),
	);

	public function initIndexPage($city,$cateid,$city_code) {
		$result = array();

		//获取知识分类
		$lCate = D('Cate','Logic','Common');
		$result['cate_all'] = $lCate->getIndexTopCategories();
		//获取二级分类和三级分类知识列表
		$result['kblist'] = $this->getIndexCateKnoledge($city,$cateid);
		//获取推荐列表，推荐列表不全用知识列表补全
		$result['rcmdlist'] = $this->getRcmdKnoledgeList($cateid,$city);
		//rank list
		$result['rank'] = $this->getHotKonwledgeList($city_code,$city);
		//hot tags
		$result['hottag'] = $this->getHotTags();
		return $result;
	}

	/**
	 * @author hongwang@leju.com
	 * @desc pc-获取所有城市
	 * @return mixed
	 */
	public function getAllCitys()
	{
		$cities = C('CITIES.CMS');
		return $cities;
	}

	public function getTopTitle($cateid)
	{
		$top = $this->getIndexTopCategories();
		return $top[$cateid]['son'];
	}


	/**
	 * @author hongwang@leju.com
	 * @desc 获取一级栏目和二级栏目
	 * @return array
	 */
	public function getIndexTopCategories()
	{
		$lCate = D('Cate','Logic','Common');
		$topCate = $lCate->getTopCate();
		$result = array();
		if ($topCate)
		{
			foreach ($topCate as $id => $name)
			{
				$result[$id]['pid'] = $id;
				$result[$id]['pname'] = $name;
				$result[$id]['son'] = $lCate->getCateChildInfoById($id);
				foreach ($result[$id]['son'] as $k => $item)
				{
					$level3 = $lCate->getCateChildInfoById($item['id']);
					$result[$id]['son'][$item['id']]['son'] = $level3;
				}
			}
		}
		return $result;
	}

	/**
	 * @author hongwang@leju.com
	 * @desc 获取二级分类和三级分类知识列表
	 * @param $city
	 * @param $cateid
	 * @param int $num
	 * @return array
	 */
	public function getIndexCateKnoledge($city,$cateid,$num=3)
	{
		$lSearch = D('Search','Logic','Common');
		$lCate = D('Cate','Logic','Common');
		$topCate = $lCate->getTopCate();
		$topids = array_keys($topCate);
		$cateid = in_array($cateid,$topids) ? $cateid : $topids['0'];
		$catename = $topCate[$cateid];

		$list = array();
		if ($cateid)
		{
			$level2 = $lCate->getCateChildInfoById($cateid);
			foreach ($level2 as $id => $v)
			{
				$path = $v['path'].'-';
				$list[$id]['name'] = $v['name'];
				$list[$id]['id'] = $id;
				$result = $this->getKnowledgeListByPath($lSearch,$city,$path);
				if ($result['list'])
				{
					$_list = array();
					foreach($result['list'] as $k=>$item)
					{
						$_list[$k]['id'] = $item['_id'];
						$_list[$k]['title'] = $item['_title'];
						$_list[$k]['cover'] = $item['_origin']['cover'];
						$_list[$k]['content'] = mystrcut(clear_all($item['_origin']['content']),80);
						$_list[$k]['tags'] = explode(' ',$item['_origin']['tags']);
					}
					// $list[$id]['list'] = $result['pager']['total'] > 0 ? $_list : null;
					$list[$id]['list'] = $_list;
				} else {
					$list[$id]['list'] = array();
				}
				$level3 = $lCate->getCateChildInfoById($v['id']);
				if ($level3)
				{
					foreach ($level3 as $sid => $si)
					{
						$spath = $si['path'];
						$result = $this->getKnowledgeListByCateid($lSearch,$city,$sid);
						$list[$id]['son'][$sid]['name'] = $si['name'];
						$list[$id]['son'][$sid]['id'] = $si['id'];
						if ($result['list'])
						{
							$ls = array();
							foreach($result['list'] as $k=>$item)
							{
								$ls[$k]['id'] = $item['_id'];
								$ls[$k]['title'] = $item['_title'];
								$ls[$k]['cover'] = $item['_origin']['cover'];
								$ls[$k]['content'] = mystrcut(clear_all($item['_origin']['content']),80);
								$ls[$k]['tags'] = explode(' ',$item['_origin']['tags']);
							}
							// $list[$id]['son'][$sid]['list'] = $result['pager']['total'] > 0 ? $ls : null;
							$list[$id]['son'][$sid]['list'] = $ls;
						} else {
							$list[$id]['son'][$sid]['list'] = array();
						}
					}
				}
			}
		}
		return $list;
	}


	/**
	 * @author hongwang@leju.com
	 * @desc 根据分类路径path获取知识列表
	 * @param $lSearch
	 * @param $city
	 * @param $path
	 * @param int $num
	 * @return mixed
	 */
	private function getKnowledgeListByCateid($lSearch,$city,$cateid,$num=3)
	{
		$order = array('_docupdatetime', 'desc');
		$fields = array('_id','_title','_origin');
		$prefix = array();
		$opts = array(array('false', '_deleted'),array("{$cateid}",'_multi.cateid'),array("{$city},全国",'_scope'));
		return $lSearch->select(1, $num,'',$opts, $prefix, $order, $fields);
	}



	/**
	 * @author hongwang@leju.com
	 * @desc 根据分类路径path获取知识列表
	 * @param $lSearch
	 * @param $city
	 * @param $path
	 * @param int $num
	 * @return mixed
	 */
	private function getKnowledgeListByPath($lSearch,$city,$path,$num=3)
	{
		$order = array('_docupdatetime', 'desc');
		$fields = array('_id','_title','_origin');
		$prefix = array(array("{$path}", '_multi.catepath'));
		$opts = array(array('false', '_deleted'),array("{$city},全国",'_scope'));
		return $lSearch->select(1, $num,'',$opts, $prefix, $order, $fields);
	}


	public function getHotKonwledgeList($city_code,$city)
	{
		$lFront = D('Front','Logic','Common');
		$rank = $lFront->getHotSearchList($city_code,$city);
		if ($rank)
		{
			foreach ($rank as $k=>$item)
			{
				$rank[$k]['title'] = mystrcut($item['title'],14);
				$rank[$k]['url'] = url('show', array('id'=>$item['id']), 'pc', 'baike');
			}
		}
		return $rank;
	}


	public function getCatePage($id=0,$page=1,$city_en='bj',$city_cn='北京')
	{
		$result = array();
		$result = $this->getCateKonwledgeList($id,$page,$this->cate_pagesize,$city_en,$city_cn);
		$topCate = $this->getIndexTopCategories();
		$result['nav'] = $topCate[$result['topcateid']];
		$lCate = D('Cate','Logic','Common');
		$cate_all = $lCate->getIndexTopCategories();
		$result['cate_all'] = $cate_all;
		$path = $lCate->getCatePathById($id);
		$path = explode('-',$path);
		$result['cateid'] = $path['1'];
		$result['bread'] = $lCate->crumbs($path,$city_en);
		return $result;
	}

	protected function getCateKonwledgeList($id=0,$page=1,$pagesize=10,$city_en='bj',$city_cn='北京')
	{
		$list = array();
		$lCate = D('Cate', 'Logic', 'Common');
		// 取当前指定栏目 id 的信息和子集
		$current = $lCate->getCateInfo($id);
		if (!$current)
			exit('分类ID错误');
		$path = $current['path'];
		$topcateid = explode('-',$path);
		$topcateid = $topcateid['1'];
		if (in_array($current['level'],array(1,2)))
		{
			$path .= '-';
		}
		$lSearch = D('Search','Logic','Common');
		$order = array('_docupdatetime', 'desc');
		$fields = array('_id','_title','_origin');
		$prefix = array(array("{$path}", '_multi.catepath'));
		$opts = array(array('false', '_deleted'),array("{$city_cn},全国",'_scope'));
		$result = $lSearch->select($page, $pagesize,'',$opts,$prefix, $order, $fields);

		if ($result['pager']['total'] > 0)
		{
			foreach ($result['list'] as $k => $ii)
			{
				$list[$k]['title'] = $ii['_title'];
				$list[$k]['id'] = $ii['_origin']['id'];
				$list[$k]['cover'] = $ii['_origin']['cover'];
				$list[$k]['content'] = mystrcut(clear_all($ii['_origin']['content']),80);
				$list[$k]['tags'] = explode(' ',$ii['_origin']['tags']);
				$list[$k]['ctime'] = date('Y-m-d H:i:s',$ii['_origin']['ptime']);
			}
		}
		$form['id'] = $id;
		$form['page'] = $page;
		$form['city'] = $city_en;
		$binds['pager'] = $this->linkopts($form,$result['pager']['total'],'cate');
		$binds['cate'] = $current['name'];
		$binds['cid'] = $current['pid'];
		$binds['list'] = $list;
		$binds['topcateid'] = $topcateid;
		$binds['total'] = ceil($result['pager']['total'] / $pagesize);
		$lCate = D('Cate','Logic','Common');
		$cate_all = $lCate->getIndexTopCategories();
		$binds['cate_all'] = $cate_all;
		return $binds;
	}

	private function linkopts($form, $total, $listtype='cate')
	{
		//封装linkopts
		$linkopts = array();
		$form['keyword'] && array_push($linkopts, "keyword={$form['keyword']}");
		$form['city'] && array_push($linkopts, "city={$form['city']}");
		$form['id'] && array_push($linkopts, "id={$form['id']}");

		$linkstring = '#';
		if ( $listtype=='cate' ) {
			$linkstring = !empty($linkopts) ? 
							url('cate', array('id'=>$form['id'], 'city'=>$form['city'], 'page'=>'#'), 'pc', 'baike')
						  :
							url('cate', array(), 'pc', 'baike');
		}
		if ( $listtype=='agg' ) {
			$linkstring = !empty($linkopts) ? 
							url('agg', array('tag'=>$form['tag'], 'city'=>$form['city'], 'id'=>$form['id'], 'page'=>'#'), 'pc', 'baike')
						  :
							url('agg', array(), 'pc', 'baike');
		}
		if ( $listtype=='search' ) {
			$linkstring = url('search', array(), 'pc', 'baike');
			$query = $form;
			$query['page'] = '#';
			unset($query['pagesize']);
			$linkstring = $linkstring.'?'.urldecode(http_build_query($query));
		}
		$opts = array(
			'first' => true, //首页
			'last' => true,	//尾页
			'prev' => true, //上一页
			'next' => true, //下一页
			'number' => 5, //显示页码数
			'linkstring' => $linkstring
		);
		$pager = pager($form['page'], $total, $this->cate_pagesize, $opts);
		return $pager;
	}


	public function getAggPage($tag,$page,$city_cn,$cateid,$form)
	{
		$lSearch = D('Search','Logic','Common');
		$opts = array(array("$tag","_tags"),array('false', '_deleted'),array("{$city_cn},全国",'_scope'));
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_version','_origin');

		$result = $lSearch->select($page, $this->agg_pagesize, '', $opts, $prefix=array(), $order, $fields);
		$total = 0;
		$maxpage = 0;
		$pager = array();
		$list = array();

		if ($result['pager']['total'] > 0)
		{
			foreach ($result['list'] as $key => $item)
			{
				$list[$key]['id'] = $item['_origin']['id'];
				$list[$key]['title'] = $item['_origin']['title'];
				$list[$key]['cover'] = $item['_origin']['cover'];
				$list[$key]['content'] = mystrcut(clear_all($item['_origin']['content']),80);
				$list[$key]['url'] = url('show', array($item['_origin']['id']));
				$list[$key]['tags'] = explode(' ',$item['_origin']['tags']);
				$list[$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ptime']);
			}
			$total = $result['pager']['total'];
			$maxpage = ceil($total/$this->agg_pagesize);
			$pager = $this->linkopts($form, $total, 'agg');
		}
		$topCate = $this->getIndexTopCategories();
		$nav = $topCate[$cateid];
		$lCate = D('Cate','Logic','Common');
		$cate_all = $lCate->getIndexTopCategories();

		return array('nav'=>$nav,'pager'=>$pager,'total'=>$total,'maxpage'=>$maxpage,'list'=>$list,'cate_all'=>$cate_all);
	}

	public function getSearchPage($page,$keyword,$city_cn,$city_en,$id)
	{
		$lSearch = D('Search','Logic','Common');
		$opts = array(array('false', '_deleted'),array("{$city_cn},全国",'_scope'));
		$order = array('_docupdatetime', 'desc');
		//$prefix = array(array("0-{$id}-", '_multi.catepath'));
		$fields = array('_id','_title','_origin');
		$list = array();
		$topCate = $this->getIndexTopCategories();
		$nav = $topCate[$id];
		$result = $lSearch->select($page,$this->search_pagesize,$keyword,$opts,$prefix, $order, $fields);

		if ($result['pager']['total'] <= 0)
		{
			$result = $lSearch->select(1,$this->search_pagesize,$keyword,$opts,$prefix, $order, $fields,1);
		}

		if ($result['pager']['total']) {
			foreach ($result['list'] as $key => $item) {
				$list[$key]['id'] = $item['_origin']['id'];
				$list[$key]['title'] = $item['_origin']['title'];
				$list[$key]['cover'] = $item['_origin']['cover'];
				$list[$key]['content'] = mystrcut(clear_all($item['_origin']['content']),80);
				$list[$key]['url'] = url('show', array($item['_origin']['id']));
				$list[$key]['tags'] = explode(' ', $item['_origin']['tags']);
				$list[$key]['ctime'] = date('Y-m-d H:i:s', $item['_origin']['ptime']);
			}

			$form = array('id'=>$id, 'page'=>$page, 'pagesize'=>$this->search_pagesize, 'keyword'=>$keyword, 'city'=>$city_en);
			$pager = $this->linkopts($form, $result['pager']['total'], 'search');
			$maxpage = ceil($result['pager']['total']/$this->search_pagesize);
		}
		$lCate = D('Cate','Logic','Common');
		$cate_all = $lCate->getIndexTopCategories();
		return array('pager'=>$pager,'list'=>$list,'dbg'=>0,'maxpage'=>$maxpage,'nav'=>$nav,'cate_all'=>$cate_all,'cateid'=>$id);
	}

	public function getHotTags()
	{
		$lFront = D('Front','Logic','Common');
		$hot = $lFront->getHot();
		$hot = array_slice($hot, 0, 10);
		return $hot;
	}
}