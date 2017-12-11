<?php
/**
 * 乐道问答问题数据模型
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class CompanyQuestionsModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'company_questions';
	/**
	 * 获取后台管理列表
	 */
	public function getAdminList ( $page=1, $pagesize=20, $opts=[] ) {
		$page = $page <= 1 ? 1 : $page;
		$from = ( $page - 1 ) * $pagesize;
		$order = "q.`ctime` DESC";
		$fields = [
			'q.`id`', 'q.`title`', 'q.`company_id`', 'q.`userid`', 'q.`city`', 'q.`ctime`', 'q.`utime`',
			'q.`source`', 'q.`status`', 'q.`i_attention`', 'q.`i_hits`', 'q.`i_replies`', 
			'q.`ontop`', 'q.`essence`', 'q.`extra`',
		];
		$fields = implode(", ", $fields);
		$list_sql = "SELECT {$fields} FROM `companies` c ".
				    "INNER JOIN `company_questions` q ON c.`wiki_id`=q.`company_id` ";
		$where = [];
		if ( isset($opts['company_id']) && intval($opts['company_id'])>0 ) {
			$where[] = "q.`company_id`='{$opts['company_id']}'";
		} else {
			if ( isset($opts['city']) && trim($opts['city'])!='' ) {
				$where[] = "q.`city`='{$opts['city']}'";
			}
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
			$countsql = "SELECT COUNT(q.`id`) as 'cnt' FROM `companies` c ".
				   "LEFT JOIN `company_questions` q ON c.`wiki_id`=q.`company_id` ".
				   "WHERE " . $where . " LIMIT 1;";
		} else {
			$list_sql .= " ORDER BY " . $order . " LIMIT {$from}, {$pagesize};";
			$countsql = "SELECT COUNT(q.`id`) as 'cnt' FROM `companies` c ".
				   "LEFT JOIN `company_questions` q ON c.`wiki_id`=q.`company_id` LIMIT 1;";
		}
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

	/**
	 * 对创建了一条新回答的问题进行计数更新
	 */
	public function updateQuestionNewAnswer( $question_id ) {
		$sql = "UPDATE `company_questions` SET "
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
			  ."FROM `company_questions` "
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
		$cnt_sql  = "SELECT count(q.`id`) as 'cnt' FROM `companies` c LEFT JOIN `company_questions` q ON c.`wiki_id`=q.`company_id` WHERE {$where}";
		$list_sql = "SELECT {$fields} FROM `companies` c LEFT JOIN `company_questions` q ON c.`wiki_id`=q.`company_id` WHERE {$where} ORDER BY {$order} LIMIT {$limit};";
		$ret = $this->query($cnt_sql);
		$result['total'] = $ret[0]['cnt'];
		$result['list'] = $this->query($list_sql);
		return $result;
	}
}