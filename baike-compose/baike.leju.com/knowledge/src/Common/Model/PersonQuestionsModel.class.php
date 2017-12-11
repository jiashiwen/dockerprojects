<?php
/**
 * 人物问答问题数据模型
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class PersonQuestionsModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'person_questions';
	/**
	 * 获取后台管理列表
	 */
	public function getAdminList ( $page=1, $pagesize=20, $opts=[] ) {
		$page = $page <= 1 ? 1 : $page;
		$from = ( $page - 1 ) * $pagesize;
		$order = "q.`ctime` DESC";
		$fields = [
			'q.`id`', 'q.`title`', 'q.`person_id`', 'q.`userid`', 'q.`ctime`', 'q.`utime`',
			'q.`source`', 'q.`status`', 'q.`i_attention`', 'q.`i_hits`', 'q.`i_replies`', 
			'q.`ontop`', 'q.`essence`', 'q.`extra`',
		];
		$fields = implode(", ", $fields);
		$list_sql = "SELECT {$fields} FROM `persons` p ".
				    "INNER JOIN `person_questions` q ON p.`wiki_id`=q.`person_id` ";
		$where = [];
		if ( isset($opts['person_id']) && intval($opts['person_id'])>0 ) {
			$where[] = "q.`person_id`='{$opts['person_id']}'";
		}
		if ( isset($opts['status']) && trim($opts['status'])!=='' ) {
			$where[] = "q.`status`='{$opts['status']}'";
		}
		if ( isset($opts['essence']) && trim($opts['essence'])!=='' ) {
			$where[] = intval($opts['essence'])===0 ? "q.`essence`='0'" : "q.`essence`>'0'";
		}
		if ( isset($opts['ontop']) && trim($opts['ontop'])!=='' ) {
			$where[] = intval($opts['ontop'])===0 ? "q.`ontop`='0'" : "q.`ontop`>'0'";
		}
		if ( isset($opts['keyword']) && trim($opts['keyword'])!=='' ) {
			$where[] = "q.`title` LIKE '%".$opts['keyword']."%'";
		}
		if ( !empty($where) ) {
			$where = implode(' AND ', $where);
			$list_sql .= " WHERE " . $where . " ORDER BY " . $order . " LIMIT {$from}, {$pagesize};";
			$countsql = "SELECT COUNT(q.`id`) as 'cnt' FROM `persons` p ".
				   "LEFT JOIN `person_questions` q ON p.`wiki_id`=q.`person_id` ".
				   "WHERE " . $where . " LIMIT 1;";
		} else {
			$list_sql .= " ORDER BY " . $order . " LIMIT {$from}, {$pagesize};";
			$countsql = "SELECT COUNT(q.`id`) as 'cnt' FROM `persons` p ".
				   "LEFT JOIN `person_questions` q ON p.`wiki_id`=q.`person_id` LIMIT 1;";
		}
		$ret = $this->query($countsql);
		$total = intval($ret[0]['cnt']);
		if ( $total > 0 ) {
			$list = $this->query($list_sql);
			$persons_ids = [];
			foreach ( $list as $i => &$item ) {
				if ( substr($item['extra'], 0, 1)==='{' ) {
					$item['extra'] = json_decode($item['extra'], true);
				} else {
					$item['extra'] = [];
				}
				if ( !in_array($item['person_id'], $persons_ids) ) {
					array_push($persons_ids, $item['person_id']);
				}
			}
			if ( !empty($persons_ids) ) {
				$_fields = ['id', 'title'];
				$_where = [
					'cateid' => 2, // 只查人物
					'id' => ['in', $persons_ids],
				];
				$ret = D('Wiki', 'Model', 'Common')->field($_fields)->where($_where)->select();
				$persons = [];
				foreach ( $ret as $i => &$item ) {
					$persons[$item['id']] = $item['title'];
				}
				foreach ( $list as $i => &$item ) {
					$item['person_name'] = $persons[$item['person_id']];
				}
			}
		} else {
			$list = [];
		}
		$result = [
			'total' => $total,
			'list' => $list,
		];
		return $result;
	}

	/**
	 * 对创建了一条新回答的问题进行计数更新
	 */
	public function updateQuestionNewAnswer( $question_id ) {
		$sql = "UPDATE `person_questions` SET "
			   ."`utime`='".NOW_TIME."', "
			   ."`i_replies`=`i_replies`+1, "
			   ."`a_replies`=`i_replies`+1 "
			   ."WHERE `id`='".$question_id."';";
		$ret = $this->execute($sql);
		return $ret;
	}

	// 用户中心接口使用
	public function getListForUserCenter( $qids=[] ) {
		if ( empty($qids) ) return [];
		$qids = "'".implode("','", $qids)."'";
		$sql = "SELECT `id`, `title`, `status` as 'qs', `i_replies` "
			  ."FROM `person_questions` "
			  ."WHERE `id` in (".$qids.")";
		$list = $this->query($sql);
		return $list;
	}

	// 前台搜索，过滤已禁用掉的公司
	public function searchQuestions( $keyword='', $page=1, $pagesize=10 ) {
		$result = ['total'=>0, 'list'=>[]];
		$keyword = trim($keyword);
		if ( $keyword == '' ) return $result;

		$fields = implode(',',['q.`id`','q.`title`', 'q.`desc`','q.`userid`','q.`i_replies`','q.`i_images`','q.`extra`']);
		$where = implode(' AND ', ["q.`status`='2'", "q.`title` LIKE '%{$keyword}%'"]);
		$order = 'utime DESC';
		$offset = ($page - 1) * $pagesize;
		$limit = "{$offset}, {$pagesize}";
		$cnt_sql  = "SELECT count(q.`id`) as 'cnt' FROM `persons` p LEFT JOIN `person_questions` q ON p.`wiki_id`=q.`person_id` WHERE {$where}";
		$list_sql = "SELECT {$fields} FROM `persons` p LEFT JOIN `person_questions` q ON p.`wiki_id`=q.`person_id` WHERE {$where} ORDER BY {$order} LIMIT {$limit};";
		$ret = $this->query($cnt_sql);
		$result['total'] = $ret[0]['cnt'];
		$result['list'] = $this->query($list_sql);
		return $result;
	}
}