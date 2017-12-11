<?php
/**
 * 百科知识内容发布服务逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class WikiPublishLogic {
	protected $id = 0;
	protected $action_type = 'publish';		// publish 发布 | save 草稿
	protected $data = null;
	protected $_city_dict = null;		// 城市字典 对应cric的城市转换使用
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
	protected $model_type = 'wiki';
	protected $model_allowed = array('wiki');
	// 错误信息
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
			$this->msg = '请确认要发布的百科数据！';
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
		$data['city_en'] = $this->fix_city_en_code(trim($data['city']));
		$record = $this->getModel('origin')->convertFields($data);
		// 新建 发布
		if ( $this->act=='insert' ) {
			$ret = $model->data($record)->add();
			$record['id'] = $data['id'] = $this->id = intval($model->getLastInsID());
		}
		// 更新 发布
		if ( $this->act=='update' ) {
			$_origin = $model->find($this->id);
			$record = array_merge($_origin, $record);
			$data['ctime'] = intval($record['ctime']);
			$where = array('id'=>$this->id);
			$ret = $model->where($where)->data($record)->save();
		}

		if ( intval($data['status'])==9 ) {
			$this->singlePublish($data);
		}

		return true;
	}


	public function singlePublish( $data ) {
		// 向标签系统推送
		$ret = D('Tags', 'Logic', 'Common')->syncToTag($data);
		$data['unique_tag_id'] = intval($ret);

		// 1. 搜索服务接口发布
		$lSearch = D('Search', 'Logic', 'Common');
		// $push = $this->pushWiki($data);
		$ret = $lSearch->createWiki( array( $data ) );
		if ( $ret ) {
			// 保存历史数据
			$history = $this->getModel('history');
			$record = $this->getModel('origin')->convertFields($data);
			$ret = $history->data($record)->add();
		} else {
			$model = $this->getModel('origin');
			$status['status'] = 2;
			$model->where(array('id'=>$this->id))->save($status);
		}

		// 2. 新闻池接口发布
		$lInfos = D('Infos','Logic','Common');
		$info = $this->getModel('origin')->convertFields($data);
		$info['id'] = $this->id;
		$ret = $lInfos->pushNewsPool($info, $lInfos::TYPE_WIKI);

		// 3. 百度接口发布
		$urls = array(
			url('show', array('id'=>$this->id, $data['cateid']), 'pc', 'wiki'),
			url('show', array('id'=>$this->id, $data['cateid']), 'touch', 'wiki'),
		);
		$lSearch->pushToBaidu($urls);

		return true;
	}

	public function batchPublish ( $datalist=array() ) {
		if ( empty($datalist) ) {
			return false;
		}

		// 2. 百度接口发布
		$urls = array();
		foreach ( $datalist as $i => $item ) {
			// $list[$i] = $this->pushWiki($item);
			array_push($urls, url('show', array('id'=>$item['id'], $item['cateid']), 'pc', 'wiki'));
			array_push($urls, url('show', array('id'=>$item['id'], $item['cateid']), 'touch', 'wiki'));
			$datalist[$i] = $this->getModel('origin')->convertFields($item);
		}
		$lSearch->pushToBaidu($urls);

		// 1. 搜索服务接口发布
		$lSearch = D('Search', 'Logic', 'Common');
		$ret = $lSearch->createWiki($datalist);
		$lInfos = D('Infos','Logic','Common');
		$ret = $lInfos->batchPushNewsPool($datalist, $lInfos::TYPE_WIKI);
		return true;
	}

	public function batchFlush ( $datalist=array() ) {
		if ( empty($datalist) ) {
			return false;
		}
		// 1. 搜索服务接口发布
		$lSearch = D('Search', 'Logic', 'Common');
		$ret = $lSearch->removeWiki($datalist);
		$lInfos = D('Infos','Logic','Common');
		$ret = $lInfos->batchPushNewsPool($datalist, $lInfos::TYPE_WIKI, true);
		return true;
	}

	/**
	 * 保存草稿
	 * 可以产生 status = 1 的数据
	 */
	public function Save ( $data=array() ) {
		if ( !is_array($data) || empty($data) ) {
			$this->msg = '保存百科草稿，需要提供百科的数据表单';
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

		$data['city_en'] = $this->fix_city_en_code(trim($data['city']));
		$record = $this->getModel('origin')->convertFields($data);
		// 新建 发布
		if ( $this->act=='insert' ) {
			$ret = $model->data($record)->add();
			$this->id = intval($model->getLastInsID());
		}

		// 更新 发布
		if ( $this->act=='update' ) {
			$_origin = $model->find($this->id);
			$record = array_merge($_origin,$record);
			$where = array('id'=>$this->id);
			$ret = $model->where($where)->data($record)->save();
		}
		// 对原数据对应的搜索服务与新闻池数据进行数据逻辑删除
		$this->MarkDelete($data);
		return true;
	}

	/**
	 * 当百科词条状态不再为已发布状态时，清理搜索服务与新闻池中的数据，标记删除
	 */
	public function MarkDelete( $data ) {
		$id = intval($data['id']);
		if ( $id<=0 ) {
			return false;
		}
		// 向标签系统推送
		$delete = true;
		$ret = D('Tags', 'Logic', 'Common')->syncToTag($data, $delete);
		// 1. 搜索服务接口发布
		$lSearch = D('Search', 'Logic', 'Common');
		$ret = $lSearch->removeWiki( array( $data ) );
		// 2. 新闻池接口发布 将草稿状态的数据从新闻池标记删除
		$lInfos = D('Infos','Logic','Common');
		$ret = $lInfos->pushNewsPool($data, $lInfos::TYPE_WIKI, true);
		return true;
	}

	/**
	 * 修正发布状态
	 */
	protected function fix_publish_status ( $action_type='save' ) {
		$data = & $this->data;
		if ( $action_type=='publish' ) {
			$ptime = intval($data['ptime']);	// 发布者设定的发布时间
			$utime = intval($data['utime']);	// 最后提交的更新时间
			// if ( $ptime>0 && $ptime>$utime && $ptime<=NOW_TIME ) {
			if ( $ptime<=NOW_TIME ) {
				// 发布时，未设置 ptime 的使用最后提交更新的时间；否则使用用户定义的时间
				if ( $ptime==0 ) {
					$data['ptime'] = intval($data['utime']);
				}
				$data['status'] = 9;
			} else {
				$data['status'] = 2;
			}
			// echo 'fixed status: ', $data['status'], PHP_EOL;
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
			$this->act = 'update';
		}
		return true;
	}

	/**
	 * 指定企业词条设置的城市中文名，返回对应的city_en
	 */
	protected function fix_city_en_code ( $city='' ) {
		$cities = $this->get_city_dict();
		if ( array_key_exists($city, $cities) ) {
			return $cities[$city];
		}
		return '';
	}
	protected function get_city_dict() {
		if ( is_null($this->_city_dict) ) {
			$cities = C('CITIES.ALL');
			$dict = [];
			foreach ( $cities as $i => $city ) {
				$city_cn = trim($city['cn']);
				if ( $city_cn!='' ) {
					$dict[$city_cn] = strtolower(trim($city['en']));
				}
			}
			$this->_city_dict = $dict;
		}
		return $this->_city_dict;

	}

	public function getId() { return $this->id; }

}