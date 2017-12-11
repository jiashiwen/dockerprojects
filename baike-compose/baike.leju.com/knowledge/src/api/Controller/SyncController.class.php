<?php
/**
 * 同步数据
 * @author Robert <yongliang1@leju.com>
 */
namespace api\Controller;
use Think\Controller;

class SyncController extends Controller {

	// public function __construct() {
	// 	send_http_status(404);
	// 	exit;
	// }

	/**
	 * 同步新闻池标签库的标签
	 * <定时任务>
	 */
	public function infoTags () {
		$mTags = D('Tags', 'Model', 'Common');
		$lInfos = D('Infos', 'Logic', 'Common');
		$last = false; // timestamp
		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 1000, 'intval');
		$list = true;
		while ( $list !== false ) {
			$list = $lInfos->getTags($page, $pagesize, $last);
			if ( $list === false ) {
				break;
			}
			echo '[INFO] 正在同步第 ', $page, ' 页！', PHP_EOL;
			$data = array();
			$mTags->bulkAdd($list['list'], 1, true);

			$page += 1;
		}
		echo '[INFO] 同步新闻池标签完成', PHP_EOL;
	}

}