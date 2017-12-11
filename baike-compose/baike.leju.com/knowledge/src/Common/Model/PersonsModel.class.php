<?php
/**
 * 人物问答人物数据模型
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class PersonsModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'persons';

	/**
	 * 获取乐道企业的公司列表
	 *
	 */
	public function getPersons($page=1, $pagesize=20, $name='', $status='') {
		$page = $page<=0 ? 1: $page;
		$from = ($page-1) * $pagesize;
		if ( $status!='' ) {
			$status = intval($status);
			if ( $status==0 ) { // 未开启的
				$list_sql = "SELECT w.`id`, w.`title`, null AS 'ctime', 0 AS 'hits', 0 AS 'favs', 0 AS 'cntq', 0 AS 'cnta' FROM wiki w ".
							"WHERE w.`cateid`='2' AND w.`status`='9' AND w.`id` not in ( SELECT `wiki_id` FROM persons ) ";
				$countsql = "SELECT count(w.`id`) AS 'cnt' FROM wiki w ".
							"WHERE w.`cateid`='2' AND w.`status`='9' AND w.`id` not in ( SELECT `wiki_id` FROM persons ) ";
				$order = "w.`id` ASC";
			} else {	// status==1 已开启的
				$list_sql = "SELECT w.`id`, w.`title`, p.`ctime`, p.`hits`, p.`favs`, p.`cntq`, p.`cnta` FROM `persons` p " .
							"LEFT JOIN `wiki` w ON w.`id`=p.`wiki_id` ".
							"WHERE w.`cateid`='2' AND w.`status`='9' ";
				$countsql = "SELECT count(w.`id`) AS 'cnt' FROM `persons` p " .
							"LEFT JOIN `wiki` w ON w.`id`=p.`wiki_id` ".
							"WHERE w.`cateid`='2' AND w.`status`='9' ";
				$order = "w.`id` ASC";
			}
		} else {
			$list_sql = "SELECT w.`id`, w.`title`, p.`ctime`, p.`hits`, p.`favs`, p.`cntq`, p.`cnta` FROM `wiki` w " .
						"LEFT JOIN `persons` p ON w.`id`=p.`wiki_id` " .
						"WHERE w.`cateid`='2' AND w.`status`='9' ";
			$countsql = "SELECT count(w.`id`) AS 'cnt' FROM `wiki` w " .
						"LEFT JOIN `persons` p ON w.`id`=p.`wiki_id` ". 
						"WHERE w.`cateid`='2' AND w.`status`='9' ";
			$order = "w.`id` ASC";
		}
		// other conditions
		if ( $name!='' ) {
			$list_sql .= " AND w.`id`='{$name}'";
			$countsql .= " AND w.`id`='{$name}'";
		}
		$ret = $this->query($countsql.' LIMIT 1');
		$list_sql .= " ORDER BY {$order} LIMIT {$from}, {$pagesize}";
		$list = $this->query($list_sql);
		$result = [
			// 'sql' => [$list_sql, $countsql],
			// 'opt' => ['pagesize'=>$page, 'page'=>$page],
			'total'=>$ret[0]['cnt'],
			'list'=>$list,
		];
		return $result;
	}

	public function getPersonInfo( $person_id ) {
		$sql = "SELECT w.`id`, w.`title`, p.`ctime`, w.`cover`, p.`hits`, p.`favs`, p.`cntq`, p.`cnta` FROM `persons` p " .
				"LEFT JOIN `wiki` w ON w.`id`=p.`wiki_id` ".
				"WHERE w.`status`='9' AND p.`wiki_id`='{$person_id}' LIMIT 1;";
		$list = $this->query($sql);
		if ( !empty($list) ) {
			$result = $list[0];
		} else {
			$result = false;
		}
		return $result;
	}

	public function checkPersonStatus($person_id) {
		return $this->getPersonInfo($person_id);
	}

	public function updateRelQuestions( $person_id, $status ) {
		$ts = 2;
		$sql = "UPDATE `persons` SET "
			  ."`cntq`=(SELECT count(`id`) FROM `person_questions` WHERE `person_id`='{$person_id}' AND `status`='{$ts}'),"
			  ."`cnta`=(SELECT count(`id`) FROM `person_answers` WHERE `person_id`='{$person_id}' AND `status`='{$ts}')"
			  ." WHERE `wiki_id`='{$person_id}'";
		return $this->execute($sql);
	}

	public function fixRelQuestions() {
		$ts = 2;
		$sql = "UPDATE `persons` SET "
			  ."`cntq`=(SELECT count(`id`) FROM `person_questions` WHERE `person_id`=`wiki_id` AND `status`='{$ts}'),"
			  ."`cnta`=(SELECT count(`id`) FROM `person_answers` WHERE `person_id`=`wiki_id` AND `status`='{$ts}')";
		return $this->execute($sql);
	}
}