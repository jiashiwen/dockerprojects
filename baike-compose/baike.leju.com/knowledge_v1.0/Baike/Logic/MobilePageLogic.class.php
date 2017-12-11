<?php
/**
 * 移动端页面逻辑
 * @author Robert <yongliang1@leju.com>
 */

namespace Baike\Logic;

class MobilePageLogic extends PageLogic {

	protected $pages = array(
		'index' => array(),
	);

	public function initIndexPage($city) {
		$result = array();

		// 首页焦点信息列表
		$result['forces'] = $this->getForces($city,5);

		// 获取知识 1级 分类
		$result['cates'] = $this->getTopCategories();

		// 获取最新知识列表
		$result['latest'] = $this->getLatestKB($city);

		// 获取热门词条
		$result['hotwords'] = $this->getHotWords();

		return $result;
	}

}