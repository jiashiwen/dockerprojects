<?php
/**
 * 乐道问答回答数据模型
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class PersonAnswersModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'person_answers';

	/**
	 * 获取后台管理列表
	 */
	public function getAdminList ( $page=1, $pagesize=20, $opts=[] ) {
		$page = $page <= 1 ? 1 : $page;
		$from = ( $page - 1 ) * $pagesize;
		$order = "a.`ctime` DESC";
		$fields = [
			'a.`id`', 'a.`reply`', 'a.`question_id`', 'q.`title`', 'a.`person_id`', 'a.`userid`', 
			'a.`ctime`', 'a.`utime`', 'a.`source`', 'a.`status`', 'a.`i_attention`', 'a.`i_hits`', 'a.`i_good`', 'a.`extra`',
		];
		$fields = implode(", ", $fields);
		$list_sql = "SELECT {$fields} FROM `persons` p ".
				    "INNER JOIN `person_answers` a ON p.`wiki_id`=a.`person_id` ".
					"LEFT JOIN `person_questions` q ON a.`question_id`=q.`id` ";
		$where = [];
		// var_dump($where);
		if ( isset($opts['person_id']) ) {
			$where[] = "a.`person_id`='{$opts['person_id']}'";
		}
		if ( isset($opts['status']) ) {
			$where[] = "a.`status`='{$opts['status']}'";
		}
		if ( isset($opts['keyword']) ) {
			$where[] = "a.`reply` LIKE '%".$opts['keyword']."%'";
		}
		// var_dump($where);
		if ( !empty($where) ) {
			$where = implode(' AND ', $where);
			$list_sql .= " WHERE " . $where . " ORDER BY " . $order . " LIMIT {$from}, {$pagesize};";
			$countsql = "SELECT COUNT(a.`id`) as 'cnt' FROM `persons` p ".
				   "INNER JOIN `person_answers` a ON p.`wiki_id`=a.`person_id` ".
				   "LEFT JOIN `person_questions` q ON a.`question_id`=q.`id` ".
				   "WHERE " . $where . " LIMIT 1;";
		} else {
			$list_sql .= " ORDER BY " . $order . " LIMIT {$from}, {$pagesize};";
			$countsql = "SELECT COUNT(a.`id`) as 'cnt' FROM `persons` p ".
				   "INNER JOIN `person_answers` a ON p.`wiki_id`=a.`person_id` ".
				   "LEFT JOIN `person_questions` q ON a.`question_id`=q.`id` LIMIT 1;";
		}
		// var_dump($list_sql, $countsql);
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

	// 用户中心接口使用
	public function getListForUserCenter( $aids=[] ) {
		if ( empty($aids) ) return [];
		$aids = "'".implode("','", $aids)."'";
		$sql = "SELECT a.`id`, a.`question_id`, q.`title`, a.`reply`, q.`status` as 'qs', a.`status` as 'as', q.`i_replies` "
			  ."FROM `person_answers` a LEFT JOIN `person_questions` q "
			  ."ON a.`question_id`=q.`id` "
			  ."WHERE a.`id` in (".$aids.")";
		$list = $this->query($sql);
		return $list;
	}
}