<?php
/**
 * 人物页面核心逻辑基础类
 * @author Robert <yongliang1@leju.com>
 */

namespace ask\Logic;

class PersonPageLogic {
	protected $data = array();		// 数据集合
	protected $_device = 'pc';		// 默认访问设备类型
	protected $_type = 'page';		// 
	protected $_flush = false;		// 是否清除数据缓存
	protected $_userid = 0;			// 访问者会员编号

	protected $lUserCenter = null;

	public function __construct() {
		$lUserCenter = D('UserCenter', 'Logic', 'Common');
		$lUserCenter->setSource($lUserCenter::UC_SOURCE_PN);
		$this->lUserCenter = $lUserCenter;
	}

	public function setType( $type='api' ) {
		if ( in_array($type, array('api', 'page')) ) {
			$this->_type = $type;
		}
		return $this;
	}

	protected function getDefault() {
		return [
			'cjlogo' => 'http://res.leju.com/resources/app/touch/newsleju/v1/images/%E8%B4%A2%E7%BB%8Flogo.png',
			'avatar' => '//src.house.sina.com.cn/imp/imp/deal/17/43/6/d6dd6705ee15ef8e7687a7b4c75_p10_mk10.png',
		];
	}

	/**
	 * 人物问答 人物聚合页 逻辑
	 */
	public function LogicTouchPerson( $person_id ) {
		// 人物详情
		$person = $this->getPersonInfo($person_id);
		if ( !$person ) {
			return false;
		}
		$binds = [];
		$binds['person'] = $person;
		$binds['hits'] = $this->countPersonHits($person_id);
		$binds['default'] = $this->getDefault();

		// 人物的问答数据统计
		$stats = [
			'questions' => intval($person['cntq']),
			'answers' => intval($person['cnta']),
		];
		// $stats = $this->getPersonQAStats($person_id);
		$binds['stats'] = $stats;
		$binds['next'] = intval($stats['questions']) > 10;
		// 最新问题列表
		$latest = $this->getPersonQuestions($person_id, 1, 10, 'latest');

		// 整合数据
		// 单独获取用户是否关注当前人物状态
		$this->lUserCenter->Reset();
		$ret = $this->lUserCenter->UserWikiIsFocus($this->_userid, $person_id);
		if ( $ret ) {
			$result = $this->lUserCenter->getData();
			$binds['cfocus'] = $result;
			if ( !empty($result['data']) && $result['data']['status'] == '关注' ) {
				$binds['person']['is_focus'] = true;
			} else {
				$binds['person']['is_focus'] = false;
			}
		}
		// 批量获取问题相关的数据
		$opts = ['qid'=>[]];
		foreach ( $latest as $i => $item ) {
			$qid = intval($item['id']);
			array_push($opts['qid'], $qid);
		}
		$this->lUserCenter->Reset();
		$ret = $this->lUserCenter->SearchStatus($this->_userid, $opts);
		if ( $ret ) {
			$data = $this->lUserCenter->getData();
			$result = $data['data'];
			// $binds['opts'] = $opts;
			// $binds['attentions'] = $result;
			foreach ( $latest as $i => &$item ) {
				if ( isset($result['q']) && !empty($result['q']) && array_key_exists($item['id'], $result['q']) ) {
					$item['i_attention'] = $result['q'][$item['id']]['total'];
					$item['is_focus'] = $result['q'][$item['id']]['focus'] ? 1 : 0;
				} else {
					$item['i_attention'] = 0;
					$item['is_focus'] = 0;
				}
			}
		}
		$this->convertQuestionsDatalist($latest);
		$binds['latest'] = $latest;

		return $binds;
	}
	/*
	 * 获取指定人物的基本信息
	 */
	protected function getPersonInfo( $person_id ) {
		$person = D('Persons', 'Model', 'Common')->getPersonInfo($person_id);
		if ( $person ) {
			$person['url'] = url('PNPerson', [$person['id']], 'touch', 'ask');
		}
		return $person;
	}
	/*
	 * 获取指定人物的问答数据量
	 */
	protected function getPersonQAStats( $person_id ) {
		$result = [];
		$where = [
			'person_id' => $person_id,
			'status' => 2,
		];
		$result['questions'] = intval(D('PersonQuestions', 'Model', 'Common')->where($where)->count());
		$result['answers'] = intval(D('PersonAnswers', 'Model', 'Common')->where($where)->count());
		return $result;
	}
	/**
	 * 获取指定人物的问题列表
	 */
	protected function getPersonQuestions( $person_id, $page=1, $pagesize=10, $order='latest', &$total=null ) {
		$orders = [
			'latest' => [ '`ontop` DESC', '`ctime` DESC', ],	// 最新
			'essence' => [ '`essence` DESC', '`ctime` DESC', ],	// 精华
		];
		$fields = [
			'id', 'ontop', 'essence', 'title', 'desc', 'ctime', 'utime', 'userid', 
			'i_images', 'i_attention', 'i_hits', 'i_replies', 'extra'
		];
		$where = [
			'person_id' => $person_id,
			'status' => 2,
		];
		if ( $order=='essence' ) {
			$where['essence'] = ['gt', 0];
		}
		$_ln_ext = ( $order == 'latest' ) ? 'touch_rw_qlist_zuixin' : 'touch_rw_qlist_tuijian';
		$order = $orders[$order];
		$mPersonQuestions = D('PersonQuestions', 'Model', 'Common');
		if ( !is_null($total) ) {
			$total = $mPersonQuestions->where($where)->count();
		}
		$list = $mPersonQuestions->field($fields)->where($where)->order($order)->page($page, $pagesize)->select();
		foreach ( $list as $i => &$item ) {
			$this->__convertExtra($item['extra'], []);
			$item['_ln_ext'] = &$_ln_ext;
		}
		return $list;
	}
	/*
	 * 对人物聚合页访问请求进行访问计数 针对人物计数
	 */
	protected function countPersonHits( $person_id, $hits=0 ) {
		$where = ['wiki_id'=>$person_id];
		$mPersons = D('Persons', 'Model', 'Common');
		$ret = $mPersons->where($where)->setInc('hits', 1);
		// $mPersons->field('hits')->find($person_id);
		// var_dump($mPersons->getLastSql(), $count);
		if ( $ret ) {
			$hits += 1;
		}
		return $hits;
	}

	/**
	 * 人物问答 人物聚合页 加载更多问题 逻辑
	 */
	public function LogicMoreQuestions( $person_id, $page=1, $pagesize=10, $order='latest') {
		$binds = [];
		$binds['opts'] = ['person_id'=>$person_id, 'page'=>$page, 'pagesize'=>$pagesize, 'order'=>$order];
		$binds['stats'] = $this->getPersonQAStats($person_id);
		$total = 0;
		$list = $this->getPersonQuestions($person_id, $page, $pagesize, $order, $total);
		// 整合数据
		// 批量获取问题相关的数据
		$opts = ['qid'=>[]];
		foreach ( $list as $i => $item ) {
			$qid = intval($item['id']);
			array_push($opts['qid'], $qid);
		}
		$ret = $this->lUserCenter->SearchStatus($this->_userid, $opts);
		if ( $ret ) {
			$data = $this->lUserCenter->getData();
			$result = $data['data'];
			// $binds['aopts'] = $opts;
			// $binds['attentions'] = $result;
			foreach ( $list as $i => &$item ) {
				$item['i_attention'] = $result['q'][$item['id']]['total'];
				$item['is_focus'] = $result['q'][$item['id']]['focus'] ? 1 : 0;
			}
		}
		$this->convertQuestionsDatalist($list);
		$binds['list'] = $list;
		$binds['next'] = ( ( intval($total) - ( $page * $pagesize ) ) > 0 );
		return $binds;
	}
	protected function convertQuestionsDatalist( &$list ) {
		foreach ( $list as $i => &$item ) {
			$item['id'] = intval($item['id']);
			$item['url'] = url('PNQuestion', [$item['id']], 'touch', 'ask');
			$item['usernick'] = $item['extra']['usernick'];
			$item['avatar'] = $item['extra']['avatar'];
			if ( !isset($item['extra']['images']) ) {
				$images = [];
			} else {
				$images = $item['extra']['images'];
			}
			$item['images'] = $images;
			$item['i_images'] = count($images);
			$item['i_attention'] = intval($item['i_attention']);
			$item['i_hits'] = intval($item['i_hits']);
			$item['i_replies'] = intval($item['i_replies']);
			$item['userid'] = intval($item['userid']);
			$item['ctime'] = intval($item['ctime']);
			$item['utime'] = intval($item['utime']);
			$item['ontop'] = intval($item['ontop']) > 0;
			$item['essence'] = intval($item['essence']) > 0;
			$item['showtime'] = formatQATimer($item['ctime']);
			unset($item['extra']);
			unset($images);
		}
		return true;
	}

	/**
	 * 乐道问答 问题详情页 逻辑
	 */
	public function LogicTouchQuestion( $question_id ) {
		// 人物详情
		$question = $this->getQuestionInfo($question_id);
		if ( !$question ) {
			return false;
		}
		$person_id = intval($question['person_id']);
		if ( $person_id<=0 ) {
			return false;
		}
		$person = $this->getPersonInfo($person_id);
		if ( !$person ) {
			return false;
		}

		$binds = [];
		$questions = [$question];
		$this->convertQuestionsDatalist($questions);
		$question = $questions[0];
		$this->countQuestionHits($question_id, $question['hits']);
		$binds['next'] = intval($question['i_replies']) > 10;
		$binds['word_count'] = $this->stringLength($question['desc']);


		$list = $this->getQuestionAnswers($question_id, 1, 10, 'latest');
		// 整合数据
		$opts = ['qid'=>[intval($question_id)], 'buid'=>[], 'aid'=>[], 'gaid'=>[]];
		foreach ( $list as $i => $item ) {
			$aid = intval($item['id']);
			$uid = intval($item['userid']);
			array_push($opts['aid'], $aid);
			array_push($opts['gaid'], $aid);
			if ( $uid > 0 ) {
				$opts['buid'][$uid] = $uid;
			}
		}
		$opts['buid'] = array_values($opts['buid']);
		$ret = $this->lUserCenter->SearchStatus($this->_userid, $opts);
		if ( $ret ) {
			$data = $this->lUserCenter->getData();
			$result = $data['data'];
			$binds['opts'] = $opts;
			$binds['opts']['_mine'] = $this->_userid;
			$binds['attentions'] = $result;
			$question['i_attention'] = $result['q'][$question_id]['total'];
			$question['is_focus'] = $result['q'][$question_id]['focus'] ? 1 : 0;

			$lComments = D('Comments', 'Logic', 'Common');
			$comments = $lComments->getCommentCount($opts['aid']);
			foreach ( $list as $i => &$item ) {
				$userid = intval($item['userid']);
				$answerid = intval($item['id']);
				if ( isset($result['u'][$userid]) ) {
					$item['is_fuser'] = $result['u'][$userid]['focus'] ? 1 : 0;
				} else {
					$item['is_fuser'] = 0;
				}
				if ( isset($result['a'][$answerid]) ) {
					$item['i_attention'] = $result['a'][$answerid]['total'];
					$item['is_focus'] = $result['a'][$answerid]['focus'] ? 1 : 0;
				} else {
					$item['is_focus'] = 0;
				}
				if ( isset($result['ga'][$answerid]) ) { 
					$item['i_good'] = $result['ga'][$answerid]['total'];
					$item['is_good'] = $result['ga'][$answerid]['focus'] ? 1 : 0;
				} else {
					$item['is_good'] = 0;
				}
				// 每个回答的评论数
				$item['i_comments'] = array_key_exists($answerid, $comments) ? $comments[$answerid] : 0;
			}
		}
		$this->convertAnswersDatalist($list);

		$binds['question'] = $question;
		$binds['person'] = $person;
		$binds['default'] = $this->getDefault();
		$binds['answers'] = $list;
		return $binds;
	}
	/*
	 * 获取指定问题的详情信息
	 */
	protected function getQuestionInfo( $question_id ) {
		$where = ['id'=>$question_id, 'status'=>2];
		$question = D('PersonQuestions', 'Model', 'Common')->where($where)->find();
		if ( $question ) {
			$question['url'] = url('PNQuestion', [$question['id']], 'touch', 'ask');
			$this->__convertExtra($question['extra'], []);
		}
		return $question;
	}
	/*
	 * 获取指定问题的回答列表
	 */
	protected function getQuestionAnswers( $question_id, $page=1, $pagesize=10, $order='latest' ) {
		$orders = ['latest'=>'ctime DESC'];
		$order = $orders[$order];
		$where = ['question_id'=>$question_id, 'status'=>2];
		$list = D('PersonAnswers', 'Model', 'Common')->where($where)->order($order)->page($page, $pagesize)->select();
		foreach ( $list as $i => &$item ) {
			$this->__convertExtra($item['extra'], []);
		}
		return $list;
	}
	/*
	 * 对问答详情页访问请求进行访问计数 针对问题计数
	 */
	protected function countQuestionHits( $question_id, $hits=0 ) {
		$where = ['id'=>$question_id];
		$mPersonQuestions = D('PersonQuestions', 'Model', 'Common');
		$ret = $mPersonQuestions->where($where)->setInc('i_hits', 1);
		if ( $ret ) {
			$hits += 1;
		}
		return $hits;
	}

	/**
	 * 乐道问答 问题详情页 加载更多 逻辑
	 */
	public function LogicMoreAnswers( $question_id, $page=1, $pagesize=10, $order='latest') {
		// 人物详情
		$question = $this->getQuestionInfo($question_id);
		if ( !$question ) {
			return false;
		}
		$person_id = intval($question['person_id']);
		if ( $person_id<=0 ) {
			return false;
		}
		$person = $this->getPersonInfo($person_id);
		if ( !$person ) {
			return false;
		}

		$binds = [];
		$binds['opts'] = ['question_id'=>$question_id,'page'=>$page,'pagesize'=>$pagesize];
		$list = $this->getQuestionAnswers($question_id, $page, $pagesize, 'latest');
		// 整合数据
		$opts = ['buid'=>[],'aid'=>[],'gaid'=>[]];
		foreach ( $list as $i => $item ) {
			$aid = intval($item['id']);
			$uid = intval($item['userid']);
			array_push($opts['aid'], $aid);
			array_push($opts['gaid'], $aid);
			if ( $uid > 0 ) {
				$opts['buid'][$uid] = $uid;
			}
		}
		$opts['buid'] = array_values($opts['buid']);
		$ret = $this->lUserCenter->SearchStatus($this->_userid, $opts);
		if ( $ret ) {
			$data = $this->lUserCenter->getData();
			$result = $data['data'];

			$comments = D('Comments', 'Logic', 'Common')->getCommentCount($opts['aid']);
			foreach ( $list as $i => &$item ) {
				$item['is_fuser'] = isset($result['u'][$item['userid']]) && $result['u'][$item['userid']]['focus'] ? 1 : 0;
				if ( isset($result['a'][$item['id']]) ) {
					$item['i_attention'] = $result['a'][$item['id']]['total'];
					$item['is_focus'] = $result['a'][$item['id']]['focus'] ? 1 : 0;
				} else {
					$item['is_focus'] = 0;
				}
				if ( isset($result['ga'][$item['id']]) ) { 
					$item['i_good'] = $result['ga'][$item['id']]['total'];
					$item['is_good'] = $result['ga'][$item['id']]['focus'] ? 1 : 0;
				} else {
					$item['is_good'] = 0;
				}
				// 每个回答的评论数
				$item['i_comments'] = array_key_exists($item['id'], $comments) ? $comments[$item['id']] : 0;
			}
		}
		if ( $list ) {
			$this->convertAnswersDatalist($list);
		}
		$binds['list'] = $list;
		$binds['total'] = intval($question['i_replies']);
		$binds['pager'] = [
			'page'=>$page, 'pagesize'=>$pagesize, 'total'=>$binds['total'],
			'count'=>intval(ceil($binds['total']/$pagesize)),
		];
		$binds['next'] = ($binds['pager']['page'] < $binds['pager']['count']);
		return $binds;
	}
	protected function convertAnswersDatalist( &$list ) {
		foreach ( $list as $i => &$item ) {
			$item['id'] = intval($item['id']);
			$item['person_id'] = intval($item['person_id']);
			$item['question_id'] = intval($item['question_id']);
			$item['url'] = url('PNAnswer', [$question['id'], $item['id']], 'touch', 'ask');
			$item['usernick'] = $item['extra']['usernick'];
			$item['avatar'] = $item['extra']['avatar'];
			if ( !isset($item['extra']['images']) ) {
				$images = [];
			} else {
				$images = $item['extra']['images'];
			}
			$item['images'] = $images;
			$item['i_images'] = count($images);
			$item['i_attention'] = intval($item['i_attention']);
			$item['i_hits'] = intval($item['i_hits']);
			$item['i_good'] = intval($item['i_good']);
			$item['userid'] = intval($item['userid']);
			$item['ctime'] = intval($item['ctime']);
			$item['utime'] = intval($item['utime']);
			$item['showtime'] = formatQATimer($item['ctime']);
			unset($item['extra']);
			unset($images);
			unset($item['status']);
			unset($item['source']);
		}
		return true;
	}


	/**
	 * 乐道问答 回答详情页 逻辑
	 */
	public function LogicTouchAnswer( $answer_id ) {
		// 人物详情
		$answer = $this->getAnswerInfo($answer_id);
		if ( !$answer ) {
			return false;
		}
		$question_id = intval($answer['question_id']);
		$question = $this->getQuestionInfo($question_id);
		if ( !$question ) {
			return false;
		}
		$person_id = intval($question['person_id']);
		if ( $person_id<=0 ) {
			return false;
		}
		$person = $this->getPersonInfo($person_id);
		if ( !$person ) {
			return false;
		}

		$binds = [];
		$binds['answer'] = $answer;
		$binds['question'] = $question;
		$binds['person'] = $person;
		$binds['default'] = $this->getDefault();
		$binds['next'] = $this->getNextAnswer($question_id, $answer);
		// 评论系统参数
		$lComments = D('Comments', 'Logic', 'Common');
		$binds['comments'] = [
			'appkey' => $lComments->getAppkey('pnanswer'),
			'unique_id' => $lComments->getUniqueID('pnanswer', intval($answer_id)),
		];
		// 整合数据
		$opts = [];
		$userid = intval($answer['userid']);
		if ( $userid>0 ) {
			$opts['buid'] = $userid;
		}
		$opts['qid'] = intval($question_id);
		$opts['aid'] = $answer_id;
		$opts['gaid'] = $answer_id;
		$ret = $this->lUserCenter->SearchStatus($this->_userid, $opts);
		if ( $ret ) {
			$data = $this->lUserCenter->getData();
			$result = $data['data'];
			// $binds['opts'] = $opts;
			// $binds['attentions'] = $result;
			$binds['question']['i_attention'] = $result['q'][$question_id]['total'];
			$binds['question']['is_attention'] = $result['q'][$question_id]['focus'] ? 1 : 0;
			$binds['answer']['is_fuser'] = isset($result['u'][$answer['userid']]) && $result['u'][$answer['userid']]['focus'] ? 1 : 0;
			if ( isset($result['a'][$answer_id]) ) {
				$binds['answer']['i_attention'] = $result['a'][$answer_id]['total'];
				$binds['answer']['is_focus'] = $result['a'][$answer_id]['focus'] ? 1 : 0;
			} else {
				$binds['answer']['is_focus'] = 0;
			}
			if ( isset($result['ga'][$answer_id]) ) { 
				$binds['answer']['i_good'] = $result['ga'][$answer_id]['total'];
				$binds['answer']['is_good'] = $result['ga'][$answer_id]['focus'] ? 1 : 0;
			} else {
				$binds['answer']['is_good'] = 0;
			}
		}
		$this->countAnswerHits($answer_id, $answer['hits']);
		return $binds;
	}
	/*
	 * 获取指定问题的详情信息
	 */
	protected function getAnswerInfo( $answer_id ) {
		$where = ['id'=>$answer_id, 'status'=>2];
		$answer = D('PersonAnswers', 'Model', 'Common')->where($where)->find();
		if ( $answer ) {
			$this->__convertExtra($answer['extra'], []);
		}
		return $answer;
	}
	/*
	 * 获取当前问题的下一个回答
	 */
	protected function getNextAnswer( $question_id, $answer ) {
		$where = ['question_id'=>$question_id, 'status'=>2, 'ctime'=>['lt', $answer['ctime']]];
		$order = 'ctime DESC';
		$fields = ['id'];
		$next = D('PersonAnswers', 'Model', 'Common')->field($fields)->where($where)->order($order)->find();
		if ( !$next ) {
			$next = ['id'=>0, 'url'=>''];
		} else {
			$next = ['id'=>$next['id'], 'url'=>url('PNAnswer', [$question_id, $next['id']], 'touch', 'ask')];
		}
		return $next;
	}
	/*
	 * 对回答详情页访问请求进行访问计数 针对回答计数
	 */
	protected function countAnswerHits( $answer_id, $hits=0 ) {
		$where = ['id'=>$answer_id];
		$mPersonAnswers = D('PersonAnswers', 'Model', 'Common');
		$ret = $mPersonAnswers->where($where)->setInc('i_hits', 1);
		if ( $ret ) {
			$hits += 1;
		}
		return $hits;
	}

	/**
	 * 乐道问答 搜索结果页 逻辑
	 */
	public function LogicTouchSearch( $keyword, $page=1, $pagesize=10 ) {

		// 搜索问题数据
		$binds = $this->getQuestionByKeyword($keyword, $page, $pagesize);
		$binds['default'] = $this->getDefault();

		return $binds;
	}
	protected function getQuestionByKeyword( $keyword, $page=1, $pagesize=10 ) {
		$result = [];
		$mPersonQuestions = D('PersonQuestions', 'Model', 'Common');
		$result = $mPersonQuestions->searchQuestions($keyword, $page, $pagesize);
		// $result['sql'] = $mPersonQuestions->getLastSql();
		$list = &$result['list'];
		$qids = [];
		foreach ( $list as $i => $item ) {
			array_push($qids, intval($item['id']));
		}
		if ( !empty($qids) ) {
			$mPersonAnswers = D('PersonAnswers', 'Model', 'Common');
			$sql = "SELECT `question_id`, SUM(`i_good`) as 'total' FROM `person_answers` "
				  ."WHERE `question_id` IN ('".implode("','", $qids)."') "
				  ."GROUP BY `question_id`";
			$_list = $mPersonAnswers->query($sql);
			$q_ext = [];
			foreach ( $_list as $i => $item ) {
				$q_ext[ intval($item['question_id']) ] = intval($item['total']);
			}
		}
		foreach ( $list as $i => &$item ) {
			$qid = $item['id'] = intval($item['id']);
			$item['i_replies'] = intval($item['i_replies']);
			if ( array_key_exists($qid, $q_ext) ) {
				$item['i_good'] = $q_ext[$qid];
			} else {
				$item['i_good'] = 0;
			}
			$this->__convertExtra($item['extra']);
			$item['userid'] = intval($item['userid']);
			$item['usernick'] = $item['extra']['usernick'];
			$item['avatar'] = $item['extra']['avatar'];
			$item['images'] = isset($item['extra']['images'])?$item['extra']['images']:[];
			$item['url'] = url('PNQuestion', [$qid], 'touch', 'ask');
			unset($item['extra']);
		}
		// $result['list'] = $list;
		$result['pager'] = [
			'page' => $page,
			'pagesize' => $pagesize,
			'total' => $result['total'],
			'count' => intval(ceil($result['total']/$pagesize)),
		];
		$result['next'] = $page < $result['pager']['count'];
		return $result;
	}

	/**
	 * 乐道问答 搜索页联想搜索 逻辑
	 */
	public function LogicTouchSuggest( $keyword, $page=1, $pagesize=10 ) {
		// 搜索问题数据
		$binds = $this->suggestQuestionByKeyword($keyword, $page, $pagesize);
		// $binds['default'] = $this->getDefault();
		return $binds;
	}
	protected function suggestQuestionByKeyword( $keyword, $page=1, $pagesize=10 ) {
		$result = [];
		$mPersonQuestions = D('PersonQuestions', 'Model', 'Common');
		$result = $mPersonQuestions->searchQuestions($keyword, $page, $pagesize);
		$list = &$result['list'];
		foreach ( $list as $i => &$item ) {
			$qid = $item['id'] = intval($item['id']);
			$item['url'] = url('PNQuestion',[$qid], 'touch', 'ask');
			$item['i_replies'] = intval($item['i_replies']);
		}
		return $result;
	}


	protected function stringLength( $str, $charset='utf-8' ){  
		if($charset=='utf-8') {
			$str = iconv('utf-8','gb2312//TRANSLIT', $str);  
		}
		$num = strlen($str);
		$cnNum = 0;
		for ( $i=0; $i<$num; $i++ ) {
			if ( ord(substr($str,$i+1,1))>127 ){
				$cnNum++;
				$i++;
			}
		}
		$enNum = $num-($cnNum*2);
		$number = ($enNum/2)+$cnNum;
		return ceil($number);
	}



	/*
	 * 将指定json字段进行转编码
	 */
	private function __convertExtra(&$data, $def=[]) {
		if ( !is_string($data) ) {
			return false;
		}
		$_p = substr($data,0,1);
		$_a = substr($data,-1,1);
		if ( ($_p=='{' && $_a=='}') || ($_p=='['&&$_a==']') ) {
			$data = json_decode($data, true);
			if ( is_null($data) ) {
				$data = $def;
			}
			return true;
		}
		return false;
	}

	/**
	 * 设置当前访问页面的用户编号
	 */
	public function setUserid( $userid = 0 ) {
		$userid = intval($userid);
		if ( $userid > 0 ) {
			$this->_userid = $userid;
		}
		return $this;
	}

	public function setDevice( $device ) {
		$device = strtolower(trim($device));
		if ( $device=='pc' ) {
			$this->_device = 'pc';
		} else {
			$this->_device = 'touch';
		}
		return $this;
	}

	public function setFlush( $flush ) {
		$flush = !!$flush;
		$this->_flush = $flush;
		return $this;
	}

	/**
	 * 用于 SEO 信息提取和展现
	 */
	public function _getPageinfo($page, $data=array()) {
		$_device = $this->_device;
		$seo_configs = array(
			'touch' => array(
				'person' => array(
					'seo_title' => "{$data['person']['title']}_热门问答-乐居问答",
					'keywords' => "{$data['person']['title']},{$data['person']['title']}热门问答,{$data['person']['title']}乐居",
					'description' => "乐居问答提供{$data['person']['title']}的专业解答，汇集各类{$data['person']['title']}知识问答，这里有专业房产从业人员为您解决{$data['person']['title']}相关问题。乐居问答，专业的房产问答平台。",
					'params' => array(),
				),
				'question' => array(
					'seo_title' => "{$data['question']['title']}-乐居问答",
					'keywords' => "{$data['person']['title']}",
					'description' => "",
				),
				'answer' => array(
					'seo_title' => "{$data['question']['title']}-乐居问答",
					'keywords' => "{$data['person']['title']}",
					'description' => "",
				),
			),
		);
		$seo = $seo_configs[$_device][$page];
		if ( in_array($page, ['question', 'answer']) ) {
			$desc = [];
			if ( trim($data['question']['desc'])!='' ) {
				array_push($desc, $data['question']['desc']);
			}
			if ( $page == 'answer' ) {
				array_push($desc, $data['answer']['reply']);
			} else {
				// array_push($desc, $data['answers'][0]['reply']);
			}
			$seo['description'] = mystrcut( implode(' ', $desc), 150);
		}
		$alt_device = $_device == 'pc' ? 'touch' : 'pc';
		// $seo['alt_url'] = url($page, $seo['params'], $alt_device, 'ask');
		$seo['alt_url'] = '';
		return $seo;
	}
	/**
	 * 用于 乐居数据部统计使用
	 */
	public function _getStatsConfig($page, $data=array()) {
		$_device = $this->_device;
		$stats_configs = array(
			'touch' => array(
				'person' => array(
					'city' => 'quanguo',
					'level1_page' => 'ask',
					'level2_page' => 'rw_gsjh',
					'level3_page' => '',
					'custom_id' => $data['person']['id'],
					'news_source' => '',
				),
				'question' => array(
					'city' => 'quanguo',
					'level1_page' => 'ask',
					'level2_page' => 'rw_qinfo',
					'level3_page' => '',
					'custom_id' => $data['question']['id'],
					'news_source' => '',
				),
				'answer' => array(
					'city' => 'quanguo',
					'level1_page' => 'ask',
					'level2_page' => 'rw_ainfo',
					'level3_page' => '',
					'custom_id' => $data['answer']['id'],
					'news_source' => '',
				),
				'search' => array(
					'city' => 'quanguo',
					'level1_page' => 'ask',
					'level2_page' => 'rw_search',
					'level3_page' => '',
					'custom_id' => '',
					'news_source' => '',
				),
				'ask' => array(
					'city' => 'quanguo',
					'level1_page' => 'ask',
					'level2_page' => 'rw_tiwen',
					'level3_page' => '',
					'custom_id' => $data['person_id'],
					'news_source' => '',
				),
				'reply' => array(
					'city' => 'quanguo',
					'level1_page' => 'ask',
					'level2_page' => 'rw_huida',
					'level3_page' => '',
					'custom_id' => $data['question']['id'],
					'news_source' => '', //$data,
				),
			),
		);
		$stats = $stats_configs[$_device][$page];
		return $stats;
	}

}
