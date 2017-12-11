<?php
/**
 * 百科知识内容发布服务逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class BaikePublishLogic {
	protected $id = 0;
	protected $action_type = 'publish';		// publish 发布 | save 草稿
	protected $data = null;
	/**
	 * 操作类型
	 * insert 创建
	 * update 更新
	 * delete 逻辑删除
	 * destory 物理删除
	 * restore 逻辑恢复
	 */
	protected $act = 'update';

	protected $model = array('origin'=>null, 'history'=>null);
	protected $model_type = 'knowledge';
	protected $model_allowed = array('knowledge');

	protected $msg = '';

	/**
	 * 初始化并获取数据模型
	 */
	protected function getModel ( $type='origin' ) {
		if ( !array_key_exists($type, $this->model) ) {
			return false;
		}

		if ( is_null($this->model[$type]) ) {
			$model = array(
				'origin' => ucfirst($this->model_type),
				'history' => ucfirst($this->model_type).'History',
			);
			$this->model[$type] = D($model[$type], 'Model', 'Common');
		}

		return $this->model[$type];
	}

	public function getError() {
		return $this->msg;
	}

	/**
	 * 发布信息
	 * 可以产生 status = 2/9 的数据
	 */
	public function Publish ( $data=array() ) {
		if ( !is_array($data) || empty($data) ) {
			$this->msg = '请确认要发布的知识数据！';
			return false;
		}
		$this->data = $data;

		// 判断主键，判断知识是否是新建数据
		$ret = $this->fix_primary_key();
		// 根据发布时间判断发布状态
		$ret = $this->fix_publish_status( 'publish' );

		$data = &$this->data;

		$model = $this->getModel('origin');
		$ret = $model->isRepeat($data);
		if ( $ret ) {
			$this->msg = '存在重复数据，请查验。重复数据编号为 #'.$ret['id'];
			return false;
		}

		// 新建 发布
		if ( $this->act=='insert' ) {
			$ret = $model->data($data)->add();
			$this->id = $model->getLastInsID();
		}
		// 更新 发布
		if ( $this->act=='update' ) {
			$_origin = $model->find($this->id);
			$data = array_merge($_origin,$data);
			$where = array('id'=>$this->id);
			$ret = $model->where($where)->data($data)->save();
		}

		if ( $data['status']==9 ) {
			$this->singlePublish($data);
		}

		return true;
	}


	public function singlePublish( $data ) {
		// 1. 搜索服务接口发布
		$lSearch = D('Search', 'Logic', 'Common');
		$push = $this->pushKonwledge($data);
		$ret = $lSearch->createKnowledge(array($push));
		if ( $ret ) {
			// 保存历史数据
			$history = $this->getModel('history');
			$ret = $history->data($data)->add();
		} else {
			$model = $this->getModel('origin');
			$status['status'] = 2;
			$model->where(array('id'=>$this->id))->save($status);
		}

		// 2. 新闻池接口发布
		$lInfos = D('Infos','Logic','Common');
		$info = $data;
		$info['id'] = $this->id;
		$ret = $lInfos->pushNewsPool($info);

		// 3. 百度接口发布
		$urls = array(
			url('show', array('id'=>$this->id), 'pc', 'baike'),
			url('show', array('id'=>$this->id), 'touch', 'baike'),
		);
		$lSearch->pushToBaidu($urls);

		return true;
	}

	/**
	 * 保存草稿
	 * 可以产生 status = 1 的数据
	 */
	public function Save ( $data=array() ) {
		if ( !is_array($data) || empty($data) ) {
			$this->msg = '保存知识草稿，需要提供知识的数据表单';
			return false;
		}
		$this->data = $data;

		// 判断主键，判断知识是否是新建数据
		$ret = $this->fix_primary_key();
		// 根据发布时间判断发布状态
		$ret = $this->fix_publish_status();

		$data = & $this->data;
		$model = $this->getModel('origin');
		$ret = $model->isRepeat($data);
		if ( $ret ) {
			$this->msg = '存在重复数据，请查验。重复数据编号为 #'.$ret['id'];
			return false;
		}

		// 新建 发布
		if ( $this->act=='insert' ) {
			$ret = $model->data($data)->add();
			$this->id = $model->getLastInsID();
		}

		// 更新 发布
		if ( $this->act=='update' ) {
			$_origin = $model->find($this->id);
			$data = array_merge($_origin,$data);
			$where = array('id'=>$this->id);
			$ret = $model->where($where)->data($data)->save();
		}

		return true;
	}

	protected function pushKonwledge($record)
	{
		$filter = array (
			'id'=>'','title'=>'','content'=>'','scope'=>'','cover'=>'','coverinfo'=>'','editorid'=>'','cateid'=>'','catepath'=>'','src_url'=>'','ptime'=>'','top_title'=>'','top_time'=>0,'top_cover'=>'','top_coverinfo'=>'','rcmd_time'=>0,'rcmd_title'=>'','rcmd_cover'=>'','rcmd_coverinfo'=>'','status'=>'','utime'=>'','ctime'=>'','version'=>'','src_type'=>'','top_coverinfo'=>'','rel_news'=>'','rel_house'=>'','tags'=>'','editor'=>'',
		);
		$record = array_intersect_key($record, $filter);
		$lPinyin = D('Pinyin', 'Logic', 'Common');
		$py = $lPinyin->get_pinyin($record['title']);
		$str = ucfirst($py);
		$record['content'] = clear_all($record['content']);
		$record['rel_news'] && $record['rel_news'] = json_decode($record['rel_news'],true);
		$record['rel_house'] && $record['rel_house'] = json_decode($record['rel_house'],true);
		$record['title_firstletter'] = substr($str, 0,1);
		$record['title_pinyin'] = $py;
		$record['url'] = url('show', array('id'=>$this->id), 'pc', 'baike');
		$record['id'] = strval($this->id);
		return $record;

	}
	/**
	 * 修正发布状态
	 */
	protected function fix_publish_status ( $action_type='save' ) {
		$data = & $this->data;

		if ( $action_type=='publish' ) {
			$ptime = &$data['ptime'];	// 设定的发布时间
			$utime = &$data['utime'];	// 最后提交时间
			if ( $ptime > $utime ) {
				$data['status'] = 2;
			} else {
				$data['status'] = 9;
			}
		}
		if ( $action_type=='save' ) {
			$data['status'] = 1;
		}

		return true;
	}

	/**
	 * 修正主键
	 */
	protected function fix_primary_key () {
		$data = & $this->data;
		if ( !isset($data['id']) || intval($data['id'])==0 ) {
			$this->act = 'insert';
			$data['ctime'] = $data['utime'];
		} else {
			$this->id = intval($data['id']);
			// unset($data['id']);
			$this->act = 'update';
		}
		return true;
	}



}