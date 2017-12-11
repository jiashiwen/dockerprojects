<?php
/**
 * 推荐业务逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class RecommendLogic {

	protected $cacher = null;
	protected $model = null;
	protected $srctypes = array('qa', 'kb', 'wiki');
	protected $limit = 10;

	public function __construct() {
		$this->cacher = S(C('REDIS'));
		$this->model = D('Recommend', 'Model', 'Common');
	}

	/**
	 * 批量设定推荐内容
	 */
	public function batchRecommend($type, $id, $items) {
		$id = intval($id);
		if ( $id <= 0 ) {
			return '请指定待推荐的数据编号';
		}
		if ( !in_array($type, $this->srctypes) ) {
			return '推荐的业务类型不存在，请指定 kb,qa或wiki';
		}

		$this->batchClean($type, $id);
		// 设置指定类型的新推荐数据
		$records = array();
		foreach ( $items as $s => $item ) {
			if ( !isset($item['status']) || intval($item['status'])==0 ) {
				continue;
			}
			$s = intval($s);
			$flag = intval($item['status']);
			$extra = $item['extra'];
			$record = array(
				'srctype' => $type, 
				'relid' => $id,
				'flag' => $flag,
				'extra' => json_encode($extra),
				'ctime' => NOW_TIME,
			);
			array_push($records, $record);
		}
		$ret = $this->model->addAll($records);
		return $ret;
	}

	public function batchClean($type, $id) {
		// 先删除指定类型的所有推荐
		$where = array('srctype'=>$type, 'relid'=>$id);
		$ret = $this->model->where($where)->delete();
		return $ret;
	}


	public function setRecommend($type, $id, $flag='0', $extra=array()) {
		if ( !in_array($type, $this->srctypes) ) {
			return '推荐的业务类型不存在，请指定 kb,qa或wiki';
		}

		$where = array(
			'srctype' => $type, 
			'relid' => $id,
			'flag' => $flag,
		);
		$this->model->where($where)->limit(1)->delete();

		$record = array(
			'srctype' => $type, 
			'relid' => $id,
			'flag' => $flag,
			'extra' => json_encode($extra),
			'ctime' => NOW_TIME,
		);
		$ret = $this->model->data($record)->add();
		return !!$ret;
	}

	public function getRecommends($type, $id=false, $flag=false) {
		if ( !in_array($type, $this->srctypes) ) {
			return '推荐的业务类型不存在，请指定 kb,qa或wiki';
		}

		$where = array(
			'srctype' => $type, 
		);
		if ( intval($id)>0 ) {
			$where['relid'] = intval($id);
		}
		if ( $flag!==false ) {
			$where['flag'] = intval($flag);
		}
		$ret = $this->model
			   ->where($where)
			   ->order('ctime desc')
			   ->limit(0, $this->limit)
			   ->select();
		foreach ( $ret as $i => &$item ) {
			$item['extra'] = json_decode($item['extra'], true);
			$item['rcmd_time'] = date('Y-m-d H:i:s', $item['ctime']);
		}
		return $ret;
	}

	public function getRecommend( $type, $id, $flag=false ) {
		if ( !in_array($type, $this->srctypes) ) {
			return '推荐的业务类型不存在，请指定 kb,qa或wiki';
		}

		$where = array(
			'srctype' => $type, 
			'relid' => $id,
			'flag' => $flag,
		);
		$ret = $this->model
			   ->where($where)
			   ->find();
		$ret['extra'] = json_decode($ret['extra'], true);
		$ret['rcmd_time'] = date('Y-m-d H:i:s', $ret['ctime']);
		return $ret;
	}

	public function delRecommend($type, $id, $flag, $extra=array()) {
		if ( !in_array($type, $this->srctypes) ) {
			return '推荐的业务类型不存在，请指定 kb,qa或wiki';
		}

		$where = array(
			'srctype' => $type, 
			'relid' => $id,
			'flag' => $flag,
		);
		$ret = $this->model->where($where)->delete();
		return $ret==1 ? true : false;
	}


	public function getAskFocus ( $num=5 ) {
		$type = 'qa';
		$flag = 1;
		$num = intval($num);
		$num = $num<=0 ? 5 : $num;
		$num = $num>=20 ? 20 : $num;
		$sql = "SELECT q.`id`, q.`cateid`, q.`title`, q.`desc`, q.`tags`, q.`catepath`, q.`ctime`, r.`extra`, r.`ctime` as 'rctime'"
			 . "FROM `recommends` r "
			 . "INNER JOIN `question` q "
			 . "ON r.`relid`=q.`id` "
			 . "WHERE "
			 . "	r.`srctype`='{$type}' "
			 . "AND r.`flag`='{$flag}' "
			 . " AND q.`status` IN (21,22,23) "
			 . "ORDER BY r.`id` DESC "
			 . " LIMIT {$num}";
		$ret = $this->model->query($sql);
		foreach ( $ret as $i => &$item ) {
			$item['extra'] = json_decode($item['extra'], true);
			$item['rcmd_time'] = date('Y-m-d H:i:s', $item['rctime']);
		}
		return $ret;
	}
}