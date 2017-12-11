<?php
namespace ask\Controller;
use Think\Controller;
class ShowController extends BaseController {

	public function index(){
		$lPage = D('Page', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->pc_show_logic(0, $this->_userid);
		$this->setPageInfo($lPage->_getPageinfo('show', $binds));
		$this->setStatsCode($lPage->_getStatsConfig('show', $binds));
		$this->assign_all($binds);
		$this->display();
	}

	// // // // // // // // // // // // // // // // // // // // 
	/**
	 * 乐道问答迭代 - 公司问答详情页
	 * @date: 2017-10-16
	 */
	public function ldq() {
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		$qid = I('get.id', 0, 'intval');
		$lPage = D('CompanyPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setUserid($this->_userid)
				->LogicTouchQuestion($qid);
		if ( !$binds ) {
			$this->error('您访问的问题不存在');
		}
		$this->setPageInfo($lPage->_getPageinfo('question', $binds));
		$this->setStatsCode($lPage->_getStatsConfig('question', $binds));
		$binds['islogined'] = $this->_islogined;
		// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$this->assign_all($binds);
		$this->display('company_question');
	}
	/**
	 * 乐道问答 - 公司聚合页中加载更多的数据接口
	 */
	public function more_lda() {
		if ( $this->_device=='pc' ) {
			$this->ajax_error('暂时仅支持移动端访问');
		}
		$qid = I('get.id', 0, 'intval');
		if ( $qid<=0 ) {
			$this->ajax_error('请指定问题');
		}
		$page = I('get.page', 1, 'intval');
		$page = $page <= 0 ? 1 : $page;
		$pagesize = I('get.pagesize', 10, 'intval');
		if ( $pagesize < 1 || $pagesize >50 ) {
			$pagesize = 10;
		}

		$lPage = D('CompanyPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setUserid($this->_userid)
				->LogicMoreAnswers($qid, $page, $pagesize);
		// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL; exit;
		$result = ['status'=>true] + $binds;
		$this->ajax_return($result);
	}

	/**
	 * 乐道问答迭代 - 公司回答详情页
	 * @date: 2017-10-16
	 */
	public function lda() {
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		$qid = I('get.qid', 0, 'intval');
		$aid = I('get.id', 0, 'intval');
		$lPage = D('CompanyPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setUserid($this->_userid)
				->LogicTouchAnswer($aid);
		if ( !$binds ) {
			$this->error('您访问的回答不存在');
		}
		$this->setPageInfo($lPage->_getPageinfo('answer', $binds));
		$this->setStatsCode($lPage->_getStatsConfig('answer', $binds));
		$binds['islogined'] = $this->_islogined;
		//echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$this->assign_all($binds);
		$this->display('company_answer');
	}


	/**
	 * 乐道问答迭代 - 回答详情页
	 */
	public function ldr() {
		if ( !$this->_islogined ) {
			// 需要用户登录，否则不允许进入
			$url = 'https://my.leju.com/';
			$this->error('请登录系统后再进行提问', $url, 5);
		}
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		$qid = I('get.id', 0, 'intval');
		if ( $qid<=0 ) {
			$this->error('请指定问题');
		}

		$lPage = D('CompanyPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setUserid($this->_userid)
				->LogicTouchQuestion($qid);
		$binds['succurl'] = url('LDQuestion', [$qid], 'touch', 'ask');
		$binds['islogined'] = $this->_islogined;
		$this->setStatsCode($lPage->_getStatsConfig('reply', $binds));
		// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$this->assign_all($binds);
		$this->display('company_reply');
	}

	// // // // // // // // // // // // // // // // // // // // 
	/**
	 * 人物问答迭代 - 人物问答详情页
	 * @date: 2017-11-23
	 */
	public function pnq() {
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		$qid = I('get.id', 0, 'intval');
		$lPage = D('PersonPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setUserid($this->_userid)
				->LogicTouchQuestion($qid);
		if ( !$binds ) {
			$this->error('您访问的问题不存在');
		}
		$this->setPageInfo($lPage->_getPageinfo('question', $binds));
		$this->setStatsCode($lPage->_getStatsConfig('question', $binds));
		$binds['islogined'] = $this->_islogined;
		// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$this->assign_all($binds);
		$this->display('person_question');
	}
	/**
	 * 人物问答 - 人物问答聚合页中加载更多回答的数据接口
	 */
	public function more_pna() {
		if ( $this->_device=='pc' ) {
			$this->ajax_error('暂时仅支持移动端访问');
		}
		$qid = I('get.id', 0, 'intval');
		if ( $qid<=0 ) {
			$this->ajax_error('请指定问题');
		}
		$page = I('get.page', 1, 'intval');
		$page = $page <= 0 ? 1 : $page;
		$pagesize = I('get.pagesize', 10, 'intval');
		if ( $pagesize < 1 || $pagesize >50 ) {
			$pagesize = 10;
		}

		$lPage = D('PersonPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setUserid($this->_userid)
				->LogicMoreAnswers($qid, $page, $pagesize);
		// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL; exit;
		$result = ['status'=>true] + $binds;
		$this->ajax_return($result);
	}

	/**
	 * 人物问答迭代 - 人物回答详情页
	 * @date: 2017-11-23
	 */
	public function pna() {
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		$qid = I('get.qid', 0, 'intval');
		$aid = I('get.id', 0, 'intval');
		$lPage = D('PersonPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setUserid($this->_userid)
				->LogicTouchAnswer($aid);
		if ( !$binds ) {
			$this->error('您访问的回答不存在');
		}
		$this->setPageInfo($lPage->_getPageinfo('answer', $binds));
		$this->setStatsCode($lPage->_getStatsConfig('answer', $binds));
		$binds['islogined'] = $this->_islogined;
		//echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$this->assign_all($binds);
		$this->display('person_answer');
	}


	/**
	 * 人物问答迭代 - 回答详情页
	 */
	public function pnr() {
		if ( !$this->_islogined ) {
			// 需要用户登录，否则不允许进入
			$url = 'https://my.leju.com/';
			$this->error('请登录系统后再进行提问', $url, 5);
		}
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		$qid = I('get.id', 0, 'intval');
		if ( $qid<=0 ) {
			$this->error('请指定问题');
		}

		$lPage = D('PersonPage', 'Logic');
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->setUserid($this->_userid)
				->LogicTouchQuestion($qid);
		$binds['succurl'] = url('PNQuestion', [$qid], 'touch', 'ask');
		$binds['islogined'] = $this->_islogined;
		$this->setStatsCode($lPage->_getStatsConfig('reply', $binds));
		// echo '<!--', PHP_EOL, print_r($binds, true), PHP_EOL, '-->', PHP_EOL;
		$this->assign_all($binds);
		$this->display('person_reply');
	}

}
