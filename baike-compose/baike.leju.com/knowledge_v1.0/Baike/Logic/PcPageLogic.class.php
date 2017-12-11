<?php
/**
 * PC 端页面逻辑
 * @author Robert <yongliang1@leju.com>
 */

namespace Baike\Logic;

class PcPageLogic extends PageLogic {

	protected $pages = array(
		'index' => array(),
	);

	public function initIndexPage() {
		$result = array();

		// 首页焦点信息列表
		$result['forces'] = $this->getForces(5);

		// 获取知识 1级 分类
		$result['cates'] = $this->getTopCategories();

		// 获取最新知识列表
		$result['latest'] = $this->getLatestKB(5);

		// 获取热门词条
		$result['hotwords'] = $this->getHotWords(10);

		return $result;
	}

}