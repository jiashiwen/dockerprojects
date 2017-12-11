<?php
/**
 * 问题数据模型
 * 
 */
namespace baike\Model;
use Think\Model;

class QuestionsModel extends Model {

	/**
	 * 创建问题
	 * @param $data array 问题数据
	 * @return string 问题查看编号
	 */
	public function createQuestion($data) {
		if ( empty($data) ) {
			return false;
		}

		/*
			- data struct -
			scope		// 问题区域
			title		// 问题标题
			desc		// 问题描述
			tags		// 问题标签
			uid			// 提问者用户编号
		*/

		// // 确认问题域
		// $cities = C('CITIES');
		// $cities = $cities['news'];
		// $scope = array_key_exists($data['scope'], $cities) ? $cities[$data['scope']] : '';

		$data['ctime'] = NOW_TIME;
		$data['utime'] = 0;
		$data['status'] = 0;
		$data['source'] = 0;
		$data['is_crawl'] = 0;
		$data['i_attention'] = 0;
		$data['i_hits'] = 0;
		$data['i_replies'] = 0;
		$data['tags'] = str_replace(',',' ',$data['tags']);
		$Qid = md5('http://kb.leju.com/view/'.$data['ctime'].$data['uid'].'.html');
		$data['_id'] = $Qid;
		$id = $this->data($data)->add();

		$result = $id > 0 ? $Qid : false;
		if ( !!$result ) {
			// 同时将创建的问题同步至 ES
			$sync_status = $this->esCreateDocument($data);
			// @TODO: 是否需要保证粒子操作
		}

		return $result;
	}

	/**
	 * 按唯一编号(Engine中的文档编号)查询数据
	 * @param $_id string 问题维一编号 32bit md5 sign 
	 */
	public function getByUniqID ( $_id ) {
		$where = array("`_id`='{$_id}'");
		$result = $this->where($where)->find();
		return $result;
	}

	public function getByID($id) {
		$where = "`id`='{$id}'";
		return $this->where($where)->find();
	}

	/**
	 * 对访问问题进行访问计数
	 * @param $uid int 访问者编号
	 * @param $_id string 问题维一编号 32bit md5 sign 
	 * @param $question array 问题数据记录
	 * 
	 */
	public function countHits( $uid, $_id, &$question ) {
		if ( $uid > 0 ) {
			// 用户行为计数
		}
		$key = "CACHE:QUESTIONS:HITS";
		$this->redis = S(C('REDIS'));
		$hits = $this->redis->zIncrBy($key, 1, $_id);
		$question['i_hits'] = intval($question['i_hits']) + $hits;
		if ( $hits >= 1 ) {
			$ret = $this->updateHitsCount($_id, $question);
		} else {
			$ret = false;
		}
		// var_dump($ret);
		return $ret;
	}

	/**
	 * 更新访问计数
	 *
	 */
	public function updateHitsCount( $_id, $question ) {
		if ( !is_array($question) || empty($question) ) {
			return false;
		}
		// 从缓存中读取统计并清理相关缓存
		$key = "CACHE:QUESTIONS:HITS";
		$this->redis = S(C('REDIS'));
		$hits = $this->redis->zScore($key, $_id);
		$this->redis->zRem($key, $_id);
		var_dump('updateHitsCount', $hits, $question);
		// 更新写入数据库
		$data = array(
			'i_hits' => $question['i_hits'],
		);
		$where = "`_id`='{$_id}'";
		$ret = $this->where($where)->data($data)->save();
		var_dump('MySQL Update', $ret);
		// 更新写入接口
		$data = array(
			'_hits' => $question['i_hits'],
			'_origin' => array(
				'i_hits' => $question['i_hits'],
			)
		);
		$ret = $this->esUpdateDocument($_id, $data);
		var_dump('ES Update', $ret);
	}

	/**
	 * 更新回复数据
	 *
	 */
	public function updateReply( $_id, $question ) {
		if ( !is_array($question) || empty($question) ) {
			return false;
		}
		// 更新问题信息数据库
		$data = array(
			'utime' => $question['utime'],
			'i_replies' => $question['i_replies'],
		);
		$where = "`_id`='{$_id}'";
		$ret = $this->where($where)->data($data)->save();
		// var_dump('MySQL Update', $ret);
		// 更新写入接口
		$data = array(
			'_docupdatetime' => $data['utime']*1000,
			// '_multi' => array('i_replies'=>$data['i_replies']),
			'_origin' => $data,
		);
		$ret = $this->esUpdateDocument($_id, $data);
		// var_dump('ES Update', $ret);
		return true;
	}


	/**
	 * 更新问题关注数据
	 *
	 */
	public function updateAttention( $_id, $question ) {
		if ( !is_array($question) || empty($question) ) {
			return false;
		}
		// 更新问题信息数据库
		$data = array(
			'i_attention' => $question['i_attention'],
		);
		$where = "`_id`='{$_id}'";
		$ret = $this->where($where)->data($data)->save();
		// 更新写入接口
		$data = array(
			// '_multi' => array('i_attention'=>$data['i_attention']),
			'_origin' => $data,
		);
		$ret = $this->esUpdateDocument($_id, $data);
		// var_dump('ES Update', $ret);
		return true;
	}

	/**
	 * 将新创建的问题提交到 Engine 中
	 * @param $_id string 问题维一编号 32bit md5 sign
	 * @param $question array 问题数据
	 * @return bool 成功返回 true
	 */
	public function esCreateDocument( $question ) {
		// 数据处理 ES 中不需要保留 HTML 标签
		$question['desc'] = strip_tags(trim($question['desc']));
		// 组织 Engine Document 结构
		$es_question = array(
			'_scope' => $question['scope'],
			'_tags' => $question['tags'],
			'_title' => $question['title'],
			'_content' => $question['desc'],
			'_category' => '乐居',
			'_doccreatetime' => $question['ctime']*1000,
			'_docupdatetime' => $question['utime']*1000,
			'_deleted' => $question['status'] == 0 ? false : true,
			'_hits' => 0,
			'_multi' => array(
				'uid' => $question['uid'],
				'status' => $question['status'],
			),
			'_origin' => $question,
		);

		// 将用于同步与更新 异常
		$data = json_encode($es_question);
		$data = str_replace('\r\n', '\n', $data);
		$data = str_replace('\b', '', $data);

		// 接口调用，创建文档
		$api = 'http://10.204.12.34:9998/api/admin/putquestion';
		$querys = array(
			'index' => 'question_logic',
			'type' => 'question',
			'id' => $question['_id'],
		);
		$api = $api.'?'.http_build_query($querys);

		$return = curl_post($api, $data, array("Content-Type: text/plain"));
		return $return['status'];
	}


	/**
	 * 更新文档数据内容
	 * @description 允许部份修改
	 * @param $_id string 问题唯一编号 32bit md5 sign
	 * @param $changed array 问题变更内容
	 * @return bool 成功返回 true
	 */
	public function esUpdateDocument( $_id, $changed ) {
		// 组织 Engine Document 结构
		// $es_question = array(
		// 	'_scope' => $question['scope'],
		// 	'_tags' => $question['tags'],
		// 	'_title' => $question['title'],
		// 	'_content' => $question['desc'],
		// 	'_category' => '乐居',
		// 	'_doccreatetime' => $question['ctime']*1000,
		// 	'_docupdatetime' => $question['utime']*1000,
		// 	'_deleted' => $question['status'] == 0 ? false : true,
		// 	'_hits' => 0,
		// 	'_multi' => array(
		// 		'uid' => $question['uid'],
		// 		'status' => $question['status'],
		// 	),
		// 	'_origin' => $question,
		// );
		$data = json_encode($changed);
		// 接口调用，创建文档
		$api = 'http://10.204.12.34:9998/api/admin/updatequestion';
		$querys = array(
			'index' => 'question_logic',
			'type' => 'question',
			'id' => $_id,
		);
		$api = $api.'?'.http_build_query($querys);
		// var_dump($api, $changed, $data);
		$return = curl_post($api, $data, array("Content-Type: text/plain"));
		var_dump($return);
		return $return['status'];
	}
}
