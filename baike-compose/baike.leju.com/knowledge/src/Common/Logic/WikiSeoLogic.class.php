<?php
/**
 * 百科词条的 SEO 服务逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class WikiSeoLogic extends SeoLogic {

	//百科&词条首页
	public function index($type="baike") {
		$seo = array();
		$seo['seo_title'] = $seo['title'] = "房产词条-{$this->_tag}";
		$seo['keywords'] = '房产词条,房产百科,房产知识,购房指南';
		$seo['description'] = '乐居房产词条包含丰富的房产各类词条知识，包括房产最热词条，房产最热词条，房产名人百科，房产机构百科。';

		return $seo;
	}
	// 动态调用
	public function format( $type = 'index' ) {
		if ( method_exists($this, $type) ) {
			$this->$type();
		}
		return $this;
	}

	//词条列表
	//传值是名人或百科,不传是全部
	public function llist($cateid = '')
	{
		$seo = array();

		if($cateid !== '')
		{
			$name = array(0=>'名人',1=>'机构');

			$seo['title'] = "房产{$name[$cateid]}-房产百科-{$this->_tag}";
			$seo['keywords'] = "房产{$name[$cateid]},房产百科,房产知识,购房指南";
		}
		else
		{
			$seo['title'] = "乐居房产百科，房产词条-{$this->_tag}";
			$seo['keywords'] = '乐居房产百科,房产词条,房产知识,买房注意事项,购房指南';
		}
		$seo['description'] = '乐居房产百科包含丰富的房地产知识，专业权威的房地产名词解释，房地产名人介绍，为您提供最新的买房新闻，房产政策，买房流程等购房知识最新信息，为业主购房支招，轻松了解房产专业内容。';

		$seo['seo_title'] = &$seo['title'];
		return $seo;
	}

	// 词条详情
	public function show() {
		$seo = $this->seo_info;
		$seo['title'] = "{$seo['title']}-房产词条-{$this->_tag}";
		$seo['seo_description'] = mystrcut($seo['seo_description'], 100);
		if ( !$seo['alt_url'] ) {
			$alt_device = $this->device=='pc' ? 'touch' : 'pc';
			$seo['alt_url'] = url('show', array('id'=>$this->ref['id'], $this->ref['cateid']), $alt_device, 'wiki');
		}
		$this->seo_info = $seo;
		return $seo;
	}

	//搜索结果页
	public function search($keyword)
	{
		$seo = array();

		$seo['seo_title'] = $seo['title'] = "{$keyword}-房产百科-{$this->_tag}";
		$seo['keywords'] = "{$keyword},房产百科,房产知识,购房指南";
		$seo['description'] = '乐居房产百科包含丰富的房地产知识，专业权威的房地产名词解释，房地产名人介绍，为您提供最新的买房新闻，房产政策，买房流程等购房知识最新信息，为业主购房支招，轻松了解房产专业内容。';

		return $seo;
	}

	//知识&词条标签页
	public function tag($name)
	{
		$seo = array();

		$seo['seo_title'] = $seo['title'] = "{$name}-房产百科-{$this->_tag}";
		$seo['keywords'] = "{$name},房产百科,房产知识,购房指南";
		$seo['description'] = '乐居房产百科包含丰富的房地产知识，专业权威的房地产名词解释，房地产名人介绍，为您提供最新的买房新闻，房产政策，买房流程等购房知识最新信息，为业主购房支招，轻松了解房产专业内容。';

		return $seo;
	}


}