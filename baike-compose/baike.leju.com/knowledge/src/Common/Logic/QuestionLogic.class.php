<?php
/**
 * 问答逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class QuestionLogic {
	protected $_cacher = null;

	protected $_counter_key = 'QUESTION:COUNTERS';
	protected $_counters = array();

	public function __construct() {
		$this->_cacher = S(C('REDIS'));
	}

	/**
	 * 获取统计计数
	 */
	public function getCounters() {
		if ( !empty($this->_counters) ) {
			return $this->_counters;
		}

		// 默认值
		$result = array(
			'all' => 0,	// 所有问题
			'unsolved' => 0, // 未解决问题
			'answered' => 0,	// 已回复问题
			'best' => 0,	// 已采纳问题
			'unverified' => 0,	// 未审核问题
			'lastweek' => 0,	// 近7天新增问题
		);

		$countMethodName = 'count';
		// 获取所有
		$ret = $this->_cacher->hgetall($this->_counter_key);
		if ( !$ret ) {
			$mQuestion = D('Question', 'Model', 'Common');
			$countMethodName = $countMethodName.'All';
			if ( method_exists($mQuestion, $countMethodName) ) {
				$ret = call_user_func_array(array($mQuestion, $countMethodName), array());
				// 保存至缓存
				$this->_cacher->hMSet($this->_counter_key, $ret);
				// 每 5 分钟重新计算一次
				$this->_cacher->expire($this->_counter_key, 300);
			} else {
				return $result;
			}
		} else {
		}
		// 写入程序逻辑缓存，在一次执行时，最多从 redis 服务中获取一次
		$this->_counters = $ret;
		return $ret;
	}

	/**
	 * 更新被关注者的消息通知
	 * 当用户关注的问题被有效回答时，执行更新
	 */
	public function updateAttentionNotice( $questionid ) {
		if ( intval($questionid)==0 ) {
			return false;
		}
		$mQuestion = D('Question', 'Model', 'Common');
		$sql = "UPDATE `members` SET `i_updated`=`i_updated`+1 "
			 . "WHERE `uid` IN ( "
			 . "  SELECT DISTINCT `uid` FROM `oplogs` "
			 . "  WHERE `act`='11' AND `relid`='{$questionid}'"
			 . ")";
		// $sql = str_replace(PHP_EOL, ' ', $sql);
		$ret = $mQuestion->execute($sql);
		return true;
	}
	/**
	 * 清理问题详情页缓存
	 */
	public function flushCache( $questionid ) {
		if ( intval($questionid)==0 ) {
			return false;
		}
		// 清理被回答问题的详情缓存
		$key = 'QA:DETAIL:'.$questionid;
		$this->_cacher->del($key);
		return true;
	}

	/**
	 * 更新问题对应的答案相关数据
	 */
	public function fixQuestionData( $questionid, $update_question_status=true ) {
		if ( intval($questionid)==0 ) {
			return '问题编号错误';
		}

		$mQuestion = D('Question', 'Model', 'Common');
		$question = $mQuestion->find($questionid);
		if ( !$question ) {
			return '问题不存在';
		}

		$mAnswers = D('Answer', 'Model', 'Common');
		// 有效回答条数
		$where = array('qid'=>$questionid, 'status'=>array('in', array(21,22,23)));
		$i_replies = $mAnswers->where($where)->count();
		// 所有回答条数
		$where = array('qid'=>$questionid);
		$a_replies = $mAnswers->where($where)->count();
		/* 因为同时还要修正被采纳的数据，就不通过下面的方法进行更新了
		$sql = "UPDATE `question` q " 
			 . "SET "
			 . "q.`a_replies`=(SELECT COUNT(a.`id`) FROM `answers` a WHERE a.`qid`=q.`id`), "
			 . "q.`i_replies`=(SELECT COUNT(a.`id`) FROM `answers` a WHERE a.`qid`=q.`id` AND a.`status` IN ('21','22','23') ) "
			 . "WHERE q.`id`='{$questionid}'";
		$ret = $mQuestion->execute($sql);
		*/
		// 采纳数据
		$last_best = 0;
		$where = array('qid'=>$questionid, 'is_best'=>array('gt', 0));
		$order = 'is_best desc';
		$limit = 1;
		$best = $mAnswers->where($where)->order($order)->limit($limit)->find();
		// echo $mAnswers->getLastSql(), PHP_EOL, print_r($best, true), PHP_EOL;
		if ( $best ) {
			$last_best = intval($best['id']);
			// 其它采纳清零，只保留最后一条被采纳数据
			$where = array('qid'=>$questionid, 'id'=>array('neq', $last_best));
			$data = array('is_best'=>0);
			$mAnswers->where($where)->data($data)->save();
			// echo $mAnswers->getLastSql(), PHP_EOL;
		}

		// 重新判断并处理问题的状态
		$status = intval($question['status']);
		if ( in_array($status, array(21,22,23)) ) {
			if ( $i_replies > 0 ) {
				if ( $last_best > 0 ) {
					$status = 23; // 已采纳
				} else {
					$status = 22; // 已回复
				}
			} else { // i_replies == 0 要恢复回待解决状态
				$status = 21; // 待解决
			}
		}
		$where = array('id'=>$questionid);
		$data = array(
			'i_replies' => $i_replies,
			'a_replies' => $a_replies,
			'last_best' => $last_best,
			'utime' => NOW_TIME,
		);
		if ( $update_question_status!==false ) {
			$data['status'] = $status;
		}
		$mQuestion->where($where)->data($data)->save();
		return true;
	}

	/**
	 * 当问题不存在时，自动清理不存在的问题回答
	 */
	public function cleanAnswersIfQuestionNotExists ( $questionid ) {
		if ( intval($questionid)==0 ) {
			return '问题编号错误';
		}

		$mQuestion = D('Question', 'Model', 'Common');
		$question = $mQuestion->find($questionid);

		if ( !$question ) {
			$mAnswers = D('Answer', 'Model', 'Common');
			$where = array('qid'=>$questionid);
			$ret = $mAnswers->where($where)->delete();
			return true;
		}
		return $question;
	}


	/**
	 *  (生产者) / 当问题的 回复数 或 访问数变化时，将问题 id 记录在一个集合队列中
	 * 使用集合来过滤重复数据
	 */
	public function appendToPushSet( $questionid=0 ) {
		$questionid = intval($questionid);
		if ( $questionid == 0 ) {
			return false;
		}
		$key = 'QA:PUSH:SET';
		$this->_cacher->sAdd($key, $questionid);

		return true;
	}

	/**
	 *  (消费者) / 后台定义一个定时任务，从集合中取出数据，并清空原集合，将取出的数据进行数据推送
	 */
	public function getAllFromPushSet( $autoclean=true ) {
		$key = 'QA:PUSH:SET';
		$result = $this->_cacher->sMembers($key);
		if ( $autoclean===true ) {
			$this->_cacher->Delete($key);
		}
		return $result;
	}
}