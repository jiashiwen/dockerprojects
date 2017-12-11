<?php
/**
 * 乐道问答回答数据模型
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class CompanyAnswersModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'company_answers';

	/**
	 * 获取后台管理列表
	 */
	public function getAdminList ( $page=1, $pagesize=20, $opts=[] ) {
		$page = $page <= 1 ? 1 : $page;
		$from = ( $page - 1 ) * $pagesize;
		$order = "a.`ctime` DESC";
		$fields = [
			'a.`id`', 'a.`reply`', 'a.`question_id`', 'q.`title`', 'a.`company_id`', 'a.`userid`', 'a.`city`', 
			'a.`ctime`', 'a.`utime`', 'a.`source`', 'a.`status`', 'a.`i_attention`', 'a.`i_hits`', 'a.`i_good`', 'a.`extra`',
		];
		$fields = implode(", ", $fields);
		$list_sql = "SELECT {$fields} FROM `companies` c ".
				    "INNER JOIN `company_answers` a ON c.`wiki_id`=a.`company_id` ".
					"LEFT JOIN `company_questions` q ON a.`question_id`=q.`id` ";
		$where = [];
		// var_dump($where);
		if ( isset($opts['company_id']) ) {
			$where[] = "a.`company_id`='{$opts['company_id']}'";
		} else {
			if ( isset($opts['city']) ) {
				$where[] = "a.`city`='{$opts['city']}'";
			}
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
			$countsql = "SELECT COUNT(a.`id`) as 'cnt' FROM `companies` c ".
				   "INNER JOIN `company_answers` a ON c.`wiki_id`=a.`company_id` ".
				   "LEFT JOIN `company_questions` q ON a.`question_id`=q.`id` ".
				   "WHERE " . $where . " LIMIT 1;";
		} else {
			$list_sql .= " ORDER BY " . $order . " LIMIT {$from}, {$pagesize};";
			$countsql = "SELECT COUNT(a.`id`) as 'cnt' FROM `companies` c ".
				   "INNER JOIN `company_answers` a ON c.`wiki_id`=a.`company_id` ".
				   "LEFT JOIN `company_questions` q ON a.`question_id`=q.`id` LIMIT 1;";
		}
		// var_dump($list_sql, $countsql);
		$ret = $this->query($countsql);
		$total = intval($ret[0]['cnt']);
		if ( $total > 0 ) {
			$list = $this->query($list_sql);
			$companies_ids = [];
			foreach ( $list as $i => &$item ) {
				if ( substr($item['extra'], 0, 1)==='{' ) {
					$item['extra'] = json_decode($item['extra'], true);
				} else {
					$item['extra'] = [];
				}
				if ( !in_array($item['company_id'], $companies_ids) ) {
					array_push($companies_ids, $item['company_id']);
				}
			}
			if ( !empty($companies_ids) ) {
				$_fields = ['id', 'title'];
				$_where = [
					'cateid' => 1, // 只查企业
					'id' => ['in', $companies_ids],
				];
				$ret = D('Wiki', 'Model', 'Common')->field($_fields)->where($_where)->select();
				$companies = [];
				foreach ( $ret as $i => &$item ) {
					$companies[$item['id']] = $item['title'];
				}
				foreach ( $list as $i => &$item ) {
					$item['company_name'] = $companies[$item['company_id']];
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
			  ."FROM `company_answers` a LEFT JOIN `company_questions` q "
			  ."ON a.`question_id`=q.`id` "
			  ."WHERE a.`id` in (".$aids.")";
		$list = $this->query($sql);
		return $list;
	}
}