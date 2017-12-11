<?php
/**
 * 乐道问答企业数据模型
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class CompaniesModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'companies';

	/**
	 * 获取乐道企业的公司列表
	 *
	 */
	public function getCompanies($page=1, $pagesize=20, $city='', $name='', $status='') {
		$page = $page<=0 ? 1: $page;
		$from = ($page-1) * $pagesize;
		if ( $status!='' ) {
			$status = intval($status);
			if ( $status==0 ) { // 未开启的
				$list_sql = "SELECT w.`id`, w.`city`, w.`title`, w.`stname`, null AS 'ctime', 0 AS 'hits', 0 AS 'favs', 0 AS 'cntq', 0 AS 'cnta' FROM wiki w ".
							"WHERE w.`cateid`='1' AND w.`status`='9' AND w.`id` not in ( SELECT `wiki_id` FROM companies ) ";
				$countsql = "SELECT count(w.`id`) AS 'cnt' FROM wiki w ".
							"WHERE w.`cateid`='1' AND w.`status`='9' AND w.`id` not in ( SELECT `wiki_id` FROM companies ) ";
				$order = "w.`id` ASC";
			} else {	// status==1 已开启的
				$list_sql = "SELECT w.`id`, w.`city`, w.`title`, w.`stname`, c.`ctime`, c.`hits`, c.`favs`, c.`cntq`, c.`cnta` FROM `companies` c " .
							"LEFT JOIN `wiki` w ON w.`id`=c.`wiki_id` ".
							"WHERE w.`cateid`='1' AND w.`status`='9' ";
				$countsql = "SELECT count(w.`id`) AS 'cnt' FROM `companies` c " .
							"LEFT JOIN `wiki` w ON w.`id`=c.`wiki_id` ".
							"WHERE w.`cateid`='1' AND w.`status`='9' ";
				$order = "w.`id` ASC";
			}
		} else {
			$list_sql = "SELECT w.`id`, w.`city`, w.`title`, w.`stname`, c.`ctime`, c.`hits`, c.`favs`, c.`cntq`, c.`cnta` FROM `wiki` w " .
						"LEFT JOIN `companies` c ON w.`id`=c.`wiki_id` " .
						"WHERE w.`cateid`='1' AND w.`status`='9' ";
			$countsql = "SELECT count(w.`id`) AS 'cnt' FROM `wiki` w " .
						"LEFT JOIN `companies` c ON w.`id`=c.`wiki_id` ". 
						"WHERE w.`cateid`='1' AND w.`status`='9' ";
			$order = "w.`id` ASC";
		}
		// other conditions
		if ( $city!='' ) {
			$list_sql .= " AND w.`city`='{$city}'";
			$countsql .= " AND w.`city`='{$city}'";
		}
		if ( $name!='' ) {
			$list_sql .= " AND w.`id`='{$name}'";
			$countsql .= " AND w.`id`='{$name}'";
		}
		$ret = $this->query($countsql.' LIMIT 1');
		$list_sql .= " ORDER BY {$order}" . " LIMIT {$from}, {$pagesize}";
		$list = $this->query($list_sql);
		$result = [
			// 'sql' => [$list_sql, $countsql],
			// 'opt' => ['pagesize'=>$page, 'page'=>$page],
			'total'=>$ret[0]['cnt'],
			'list'=>$list,
		];
		return $result;
	}

	public function getCompanyInfo( $company_id ) {
		$sql = "SELECT w.`id`, w.`city`, w.`title`, w.`stname`, c.`ctime`, w.`cover`, c.`hits`, c.`favs`, c.`cntq`, c.`cnta` FROM `companies` c " .
				"LEFT JOIN `wiki` w ON w.`id`=c.`wiki_id` ".
				"WHERE w.`status`='9' AND c.`wiki_id`='{$company_id}' LIMIT 1;";
		$list = $this->query($sql);
		if ( !empty($list) ) {
			$result = $list[0];
		} else {
			$result = false;
		}
		return $result;
	}

	public function checkCompanyStatus($company_id) {
		return $this->getCompanyInfo($company_id);
	}

	public function updateRelQuestions( $company_id, $status ) {
		$ts = 2;
		$sql = "UPDATE `companies` SET "
			  ."`cntq`=(SELECT count(`id`) FROM `company_questions` WHERE `company_id`='{$company_id}' AND `status`='{$ts}'),"
			  ."`cnta`=(SELECT count(`id`) FROM `company_answers` WHERE `company_id`='{$company_id}' AND `status`='{$ts}')"
			  ." WHERE `wiki_id`='{$company_id}'";
		return $this->execute($sql);
	}

	public function fixRelQuestions() {
		$ts = 2;
		$sql = "UPDATE `companies` SET "
			  ."`cntq`=(SELECT count(`id`) FROM `company_questions` WHERE `company_id`=`wiki_id` AND `status`='{$ts}'),"
			  ."`cnta`=(SELECT count(`id`) FROM `company_answers` WHERE `company_id`=`wiki_id` AND `status`='{$ts}')";
		return $this->execute($sql);
	}
}