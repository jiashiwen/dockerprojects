<?php
/**
 * 会员中心
 * @changelog
 * 20170706 为问答 v1.1 版本 更新逻辑
 */
namespace ask\Controller;
use Think\Controller;
class ProfileController extends BaseController {

	protected $counts = array();
	protected $userdata = array();
	protected $professor = false;

	public function __construct() {
		parent::__construct();
		if ( !$this->_islogined ) {
			// 需要用户登录，否则不允许进入
			$this->error('请登录系统后再进行查看');
		}

		// 当前用户是否是专家号
		$lPage = D('Page', 'Logic');
		$professors = $lPage->load_professors();
		$this->professor = false;
		if ( array_key_exists($this->_userid, $professors) ) {
			$this->professor = $professors[$this->_userid];
		}
		// 获取用户数据
		$mMembers = D('Members', 'Model', 'Common');
		$this->userdata = $mMembers->where(array('uid'=>$this->_userid))->find();
		if ( !$this->userdata ) {
			$this->error('请登录系统后再进行查看');
			$this->userdata = array('t_visited'=>0, 'i_updated'=>0, 'i_needanswer'=>0);
		}
		if ( !$this->professor ) {
			$this->userdata['i_needanswer'] = 0;
		}
		$this->assign('userdata', $this->userdata);
	}

	public function index() {
		$this->current_tpl = ( $this->_device=='pc' ) ? 'recommends' : 'index';
		$this->column = 'recommends';
		$this->_showlist($this->column);
	}
	public function loadmore() {
		$page = I('get.page', 2, 'intval');
		$pagesize = I('get.pagesize', 10, 'intval');
		$columnid = I('get.column', '', 'strtolower,trim');

		$this->column = $columnid;
		$binds = $this->columns($columnid);
		$result = array(
			'status' => true,
			'current' => $binds['current'],
		);
		$total = intval($binds['total']);
		$result['pager'] = array(
			'page' => $page,
			'pagesize' => $pagesize,
			'pagecount' => ceil($total/$pagesize),
			'total' => $total,
		);
		$result['pager']['is_last'] = ( $page >= $binds['pager']['pagecount'] ) ? 1 : 0;
		$this->assign_all($binds);
		layout(false);
		$html = $this->fetch('Public:list');
		$result['html'] = $html;
		$this->ajax_return($result);
	}
	public function recommends() {
		$this->current_tpl = $this->column = 'recommends';
		$this->_showlist($this->column);
	}
	public function questions() {
		$this->current_tpl = $this->column = 'questions';
		$this->_showlist($this->column);
	}
	public function answers() {
		$this->current_tpl = $this->column = 'answers';
		$this->_showlist($this->column);
	}
	public function attentions() {
		// 查看我的关注的时候，就要去掉红点
		if ( $this->userdata['i_updated'] > 0 ) {
			$mMembers = D('Members', 'Model', 'Common');
			$where = array('uid'=>$this->_userid);
			$data = array('t_visited'=>NEW_TIME, 'i_updated'=>0);
			$mMembers->where($where)->data($data)->save();
		}
		$this->current_tpl = $this->column = 'attentions';
		$this->_showlist($this->column);
	}
	public function todo() {
		// 在回答时去掉红点计数，为 0 时无红点
		$this->current_tpl = $this->column = 'todo';
		$this->_showlist($this->column);
	}

	protected function _showlist( $column ) {
		$binds = $this->columns($this->column);
		$this->assign_all($binds);
		if ( $this->_device == 'pc' ) {
			$this->current_tpl = 'index';
		}

		$lPage = D('Page', 'Logic');
		$this->setStatsCode($lPage->_getStatsConfig('profile', $this->column));
		$this->display($this->current_tpl);
	}

	/**
	 * 移动端下拉获取下一页的操作
	 */
	public function page () {
		$this->column = I('get.column', '', 'strtolower,trim');
		$binds = $this->columns($this->column);
		// $pager = &$binds['pager'];
		// $filter = array('page'=>'', 'pagesize'=>'', 'total'=>'', 'count'=>'', );
		// $pager = array_intersect_key($pager, $filter);
		// $pager['hasnext'] = ( $pager['count'] > $pager['page'] ) ? 1 : 0;
		$list = &$binds['list'];
		foreach ( $list as $i => &$item ) {
			$item['dsp_time'] = date('m-d', $item['ctime']);
			$item['url'] = url('show', array($item['id']), 'touch', 'ask');
			foreach ( $item['tagsinfo'] as $k => $tag ) {
				$item['tagsinfo'][$k]['url'] = url('agg', array($tag['id']), 'touch', 'ask');
				unset($item['tagsinfo'][$k]['i_total']);
				unset($item['tagsinfo'][$k]['source']);
				unset($item['tagsinfo'][$k]['status']);
			}
		}
		$this->ajax_return($binds);
	}

	protected function stats($columns) {
		$mQuestion = D('Question', 'Model', 'Common');
		$stats = array();
		foreach ( $columns as $column => $cfg ) {
			if ( $cfg['atstats']===true ) {
				$method = 'count'.ucfirst($column);
				// var_dump($method);
				if ( method_exists($this, $method) ) {
					$stats[$column] = call_user_func_array(array($this, $method), array());
				} else {
					$stats[$column] = 0;
				}
			}
		}
		$this->assign('stats', $stats);
	}
	protected function columns($current='') {
		$binds = array();
		$columns = array(
			'recommends' => array('title'=>'我的推荐', 'atstats'=>false, 'orderbar'=>0, 'hasNew'=>0),
			'questions' => array('title'=>'我的问题', 'atstats'=>true, 'orderbar'=>0, 'hasNew'=>0),
			'answers' => array('title'=>'我的回答', 'atstats'=>true, 'orderbar'=>0, 'hasNew'=>0),
			'attentions' => array('title'=>'我的关注', 'atstats'=>true, 'orderbar'=>0, 'hasNew'=>0),
			'todo' => array('title'=>'待我解决', 'atstats'=>false, 'orderbar'=>0, 'hasNew'=>0),
		);
		if ( !array_key_exists($this->column, $columns) ) {
			$this->column = 'recommends';
		}
		$binds['columns'] = $columns;
		$binds['current'] = $this->column;
		$binds['userinfo'] = $this->_userinfo;
		// $this->assign('columns', $columns);
		// $this->assign('current', $this->column);
		// $this->assign('userinfo', $this->_userinfo);

		$page = I('get.page', 1, 'intval');
		$page = ( $page < 1 ) ? 1 : $page;
		// $pagesize = 5;
		$pagesize = 10;

		$config = &$columns[$this->column];
		$binds['config'] = $config;
		// $this->assign('config', $config);

		$this->stats($columns);
		$method = 'list'.ucfirst($this->column);
		if ( method_exists($this, $method) ) {
			$ret = call_user_func_array(array($this, $method), array('page'=>$page, 'pagesize'=>$pagesize));
			$total = intval($ret['total']);
			$list = $ret['list'];
		} else {
			$total = 0;
			$list = array();
		}
		$binds['total'] = $total;

		// $lPage = D('Page', 'Logic');
		// $lPage->compatible($list);

		$pager = array(
			'next_api_url' => '/profile/loadmore?column='.$binds['current'],
			'page' => $page,
			'pagesize' => $pagesize,
			'pagecount' => intval(ceil($total/$pagesize)),
			'total' => $total,
		);
		$pager['is_last'] = ( $page >= $pager['pagecount'] ) ? 1 : 0;

		$binds['pager'] = $pager;
		$binds['list'] = $list;

		if ( $this->column=='todo' ) {
			$binds['todo'] = $ret['todo'];
			$binds['todo_total'] = $ret['todo_total'];
		}
		// $this->assign('pager', $pager);
		// $this->assign('list', $list);
		return $binds;
	}

	/**
	 * 获取当前用户有效的提问的总次数
	 */
	protected function countQuestions () {
		if ( isset($this->counts['questions']) ) {
			return $this->counts['questions'];
		}
		$mQuestion = D('Question', 'Model', 'Common');
		$ret = $mQuestion->where(array('status'=>array('in', array(21,22,23)), 'userid'=>$this->_userid))->count();
		$this->counts['questions'] = $ret;
		return $ret;
	}
	/**
	 * 获取当前用户有效的回复的总次数
	 */
	protected function countAnswers () {
		if ( isset($this->counts['answers']) ) {
			return $this->counts['answers'];
		}
		$mAnswers = D('Answer', 'Model', 'Common');
		$sql = "SELECT COUNT(q.`id`) AS cnt FROM `answers` a "
			 . "INNER JOIN `question` q "
			 . "ON a.`qid`=q.`id` "
			 . "WHERE q.`status` IN (21,22,23) AND a.`status` IN (21,22,23) AND a.`userid`='{$this->_userid}' "
			 . "LIMIT 1";
		$ret = $mAnswers->query($sql);
		$ret = intval($ret[0]['cnt']);
		$this->counts['answers'] = $ret;
		// var_dump($mAnswers->getLastSql(), $ret);
		return $ret;
	}
	/**
	 * 获取用户关注的问题总数
	 */
	protected function countAttentions () {
		if ( isset($this->counts['attentions']) ) {
			return $this->counts['attentions'];
		}
		$mOplogs = D('Oplogs', 'Model', 'Common');

		// $ret = $mOplogs->where(array('uid'=>$this->_userid, 'act'=>11))->count();
		$sql = "SELECT COUNT(*) AS cnt FROM `oplogs` o "
			 . "INNER JOIN `question` q "
			 . "ON o.`relid`= q.`id` "
			 . "WHERE o.`uid`='{$this->_userid}' AND o.`act`='11' AND q.status in (21,22,23) "
			 . "LIMIT 1";
		$ret = $mOplogs->query($sql);
		$ret = $ret[0]['cnt'];
		$this->counts['attentions'] = $ret;
		return $ret;
	}

	protected function listRecommends ($page=1, $pagesize=10) {
		$result = array('total'=>0, 'list'=>array());

		$mQuestion = D('Question', 'Model', 'Common');
		// 获取当前用户近期关注的 5 个问题
		$key = 'RCMD:USR:'.$this->_userid.':CARETAGS:SET'; // set
		$expire = 3600; // 缓存 3600秒
		// 从缓存加载用户关注的标签

		$mOplogs = D('Oplogs', 'Model', 'Common');
		$limit = 5;
		$sql = "SELECT q.`id`, q.`tags`, q.`tagids` "
			 . "FROM `oplogs` l "
			 . "INNER JOIN `question` q "
			 . "ON l.`relid`=q.`id` "
			 . "WHERE l.`act`='11' AND l.`uid`='{$this->_userid}' AND q.`status` IN ('21','22','23') "
			 . "ORDER BY l.`id` DESC "
			 . "LIMIT {$limit}";
		$ret = $mOplogs->query($sql);
		// $qids = array();
		$tags = array();
		foreach ( $ret as $i => $q ) {
			array_push($qids, $q['id']);
			$_tags = explode(',', trim($q['tagids'], ','));
			foreach ( $_tags as $t_i => $tag ) {
				$tag = trim($tag);
				if ( $tag!='' && !in_array($tag, $tags) ) {
					array_push($tags, $tag);
				}
			}
		}

		// 计算数据获取数量
		$pagesize = 10; // 每页显示10条数据

		$order = 'i_hits desc';
		$field = 'id, title, desc, usernick, anonymous, status, tags, tagids, cateid, catepath, ctime, utime, i_hits, i_attention, i_replies';
		$where = array(
			'status'=>array('in', array(22,23)),
		);
		$where_tagids = array();
		foreach ( $tags as $i => $tag ) {
			array_push($where_tagids, array('like', ",$tag,"));
		}
		if ( !empty($where_tagids) ) {
			array_push($where_tagids, 'OR');
			$where['tagids'] = $where_tagids;
		}
		$lPage = D('Page', 'Logic');
		$result = $lPage->_getlist($where, $order, $page, $pagesize, $field);
		return $result;
	}
	protected function listQuestions ($page=1, $pagesize=10) {
		$field = 'id, title, desc, usernick, anonymous, status, tags, tagids, cateid, catepath, ctime, utime, i_hits, i_attention, i_replies';
		$where = array('status'=>array('in', array(21,22,23)), 'userid'=>$this->_userid);
		$order = 'id desc';
		$lPage = D('Page', 'Logic');
		$result = $lPage->_getlist($where, $order, $page, $pagesize, $field);
		return $result;
	}
	protected function listAnswers ($page=1, $pagesize=10) {
		$result = array();
		$mAnswers = D('Answer', 'Model', 'Common');
		$result['total'] = $this->countAnswers();
		$from = ( $page - 1 ) * $pagesize;
		$sql = "SELECT q.`id`, q.`title`, q.`desc`, q.`usernick`, q.`anonymous`, q.`status`, q.`tags`, q.`tagids`, q.`cateid`, q.`catepath`, q.`ctime`, q.`utime`, q.`i_hits`, q.`i_attention`, q.`i_replies` "
			 . "FROM `answers` a "
			 . "INNER JOIN `question` q ON a.`qid`=q.`id` "
			 . "WHERE q.`status` in (21,22,23) AND a.`status` IN (21,22,23) AND a.`userid`='{$this->_userid}' "
			 . "ORDER BY q.`ctime` DESC "
			 . "LIMIT {$from}, {$pagesize}";
		$ret = $mAnswers->query($sql);
		$lPage = D('Page', 'Logic');
		$lPage->compatible($ret);
		$result['list'] = $ret;
		return $result;
	}
	protected function listAttentions ($page=1, $pagesize=10) {
		$result = array();
		$result['total'] = $this->countAttentions();

		$mOplogs = D('Oplogs', 'Model', 'Common');
		$sql = "SELECT count(q.`id`) as cnt FROM `oplogs` o INNER JOIN `question` q ON o.`relid`=q.`id` WHERE o.`uid`='{$this->_userid}' AND o.`act`='11' AND q.`status` in (21,22,23) ";
		$ret = $mOplogs->query($sql);
		$total = intval($ret[0]['cnt']);

		$from = ( $page - 1 ) * $pagesize;
		$sql = "SELECT q.`id`, q.`title`, q.`desc`, q.`usernick`, q.`anonymous`, q.`status`, q.`tags`, q.`tagids`, q.`cateid`, q.`catepath`, q.`ctime`, q.`utime`, q.`i_hits`, q.`i_attention`, q.`i_replies` "
			 . "FROM `oplogs` o "
			 . "INNER JOIN `question` q ON o.`relid`=q.`id` "
			 . "WHERE o.`uid`='{$this->_userid}' AND o.`act`='11' AND q.`status` in (21,22,23) "
			 . "ORDER BY o.`ctime` DESC "
			 . "LIMIT {$from}, {$pagesize}";
		$ret = $mOplogs->query($sql);
		$lPage = D('Page', 'Logic');
		$lPage->compatible($ret);
		$result['list'] = $ret;
		return $result;
	}
	protected function listTodo ($page=1, $pagesize=10) {
		$lPage = D('Page', 'Logic');
		$exp = array();
		if ( $this->professor ) {
			$mOplogs = D('Oplogs', 'Model', 'Common');
			$sql = "SELECT count(q.`id`) as cnt FROM `oplogs` o INNER JOIN `question` q ON o.`relid`=q.`id` WHERE o.`uid`='{$this->_userid}' AND o.`act`='41' AND q.`status` in (21,22,23) ";
			$ret = $mOplogs->query($sql);
			$total = intval($ret[0]['cnt']);

			$from = ( $page - 1 ) * $pagesize;
			$sql = "SELECT q.`id`, q.`title`, q.`desc`, q.`usernick`, q.`anonymous`, q.`status`, q.`tags`, q.`tagids`, q.`cateid`, q.`catepath`, q.`ctime`, q.`utime`, q.`i_hits`, q.`i_attention`, q.`i_replies` "
				 . "FROM `oplogs` o "
				 . "INNER JOIN `question` q ON o.`relid`=q.`id` "
				 . "WHERE o.`uid`='{$this->_userid}' AND o.`act`='41' AND q.`status` in (21,22,23) "
				 . "ORDER BY o.`ctime` DESC "
				 . "LIMIT {$from}, {$pagesize}";
			$todo = $mOplogs->query($sql);
			$lPage->compatible($todo);
			foreach ( $todo as $i => $item ) {
				array_push($exp, $item['id']);
			}
		}


		$field = 'id, title, desc, usernick, anonymous, status, tags, tagids, cateid, catepath, ctime, utime, i_hits, i_attention, i_replies';
		$where = array('status'=>21, 'userid'=>array('neq', $this->_userid));
		if ( !empty($exp) ) {
			$where['id'] = array('not in', $exp);
		}
		$order = 'id desc';
		$result = $lPage->_getlist($where, $order, $page, $pagesize, $field);

		$result['professor'] = $professor;
		$result['todo'] = $todo;
		$result['todo_total'] = $total;
		return $result;
	}
}
