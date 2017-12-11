<?php
/**
 * 问答内容发布服务逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class QuestionPublishLogic {
	protected $id = 0;
	// 操作类型 默认为新增 add / 其它可忽略完整性，但必须存在 id
	protected $action = 'add';
	protected $actions = array('add', 'update', 'delete');
	// 待处理的数据集合
	protected $data = array();
	protected $target = array();

	// 数据模型对象
	protected $model = null;

	protected $msg = array();
	protected $err = 0;

	protected $field_map = array(
		'info' => array(
			'id'		=>	array(
				'key' => 'wiki_id',					// 问题编号 业务主键
				'handler' => array(						// 一致性处理函数
					array('act'=>'intval', 'p'=>array('#self#')),
				),
			),
			'title'		=>	array(
				'key' => 'title',						// varchar(250) 问题标题
				'handler' => array(						// 一致性处理函数
					array('act'=>'trim', 'p'=>array('#self#', ','))
				),
			),
			'desc'		=>	array(
				'key' => 'desc',						// mediumtext 问题的详细说明描述内容
				'handler' => array(						// 一致性处理函数
					array('act'=>'trim', 'p'=>array('#self#', ','))
				),
			),
			'ctime'		=>	array(
				'key' => 'createtime',					// 整型 int(11) 问题创建时间
				'handler' => array(						// 一致性处理函数
					array('act'=>'intval', 'p'=>array('#self#')),
				),
			),
			'utime'		=>	array(
				'key' => 'updatetime',					// 整型 int(11) 问题最后一次更新时间
				'handler' => array(						// 一致性处理函数
					array('act'=>'intval', 'p'=>array('#self#')),
				),
			),
			'status'	=>	array(
				'key' => 'status',						// 型型 tinyint(4) 问题状态
				'handler' => array(						// 一致性处理函数
					array('act'=>'intval', 'p'=>array('#self#')),
				),
			),
			'i_hits'	=>	array(
				'key' => 'hits',						// 型型 int(11) 访问次数
				'handler' => array(						// 一致性处理函数
					array('act'=>'intval', 'p'=>array('#self#')),
				),
			),
			'tagids'	=>	array(
				'key' => 'tags_id',						// 字符串 varchar(250) 关联的标签编号列表
				'handler' => array(						// 一致性处理函数
					array('act'=>'trim', 'p'=>array('#self#', ',')),
				),
			),
			'i_replies'	=>	array(
				'key' => 'replies',						// 整型 int(6) 问题有效的回答数量
				'handler' => array(						// 一致性处理函数
					array('act'=>'intval', 'p'=>array('#self#')),
				),
			),
			'last_best'	=>	array(
				'key' => 'best_id',						// 整型 int(11) 是否是已采纳答案的问题， 0为未采纳 >0为采纳答案的id
				'handler' => array(						// 一致性处理函数
					array('act'=>'intval', 'p'=>array('#self#')),
				),
			),
			'catepath'	=>	array(
				'key' => 'catepath',						// 整型 int(11) 是否是已采纳答案的问题， 0为未采纳 >0为采纳答案的id
				'handler' => array(						// 一致性处理函数
					array('act'=>'trim', 'p'=>array('#self#')),
				),
			),
		),
	);

	/**
	 * 初始化并获取数据模型
	 */
	protected function getModel () {
		if ( is_null($this->model) ) {
			$this->model = D('Question', 'Model', 'Common');
		}
		return $this->model;
	}

	/**
	 * 指定特定的操作
	 */
	public function setAction( $action='delete' ) {
		$action = strtolower(trim($action));
		if ( !in_array($action, $this->actions) ) {
			array_push($this->msg, '无法识别指定的操作');
			$this->err += 1;
			return false;
		}
		$this->action = $action;
		return true;
	}

	/**
	 * 批量确认问题格式
	 *
	 */
	public function confirmQuestions ( $questions ) {
		if ( is_numeric($questions) ) {
			$this->confirmQuestion($questions);
		}
		if ( is_array($questions) ) {
			foreach ( $questions as $i => $question ) {
				// $i 必须为要推送的数据数组的索引下标
				if ( is_numeric($i) ) {
					if ( is_numeric($question) ) {
						$this->confirmQuestion($question);
					}
					if ( is_array($question) ) {
						$this->confirmQuestion($question);
					}
				}
			}
		}
		// var_dump('confirmQuestions', $questions, $this->data);
		return ( $this->err == 0 );
	}
	/**
	 * 确认一个问题格式
	 *
	 */
	public function confirmQuestion ( $question ) {
		// 验证时，只接收 问题主键 或 问题实体
		if ( !is_numeric($question) && !is_array($question) ) {
			array_push($this->msg, '传入的待发布的问题数据错误');
			$this->err += 1;
			return false;
		}

		// var_dump('confirmQuestion', $question);
		if ( is_array($question) ) {
			// $data = $this->verifyQuestionEntityForInfo($question);
			// if ( !$data ) {
			// 	// 实体完整性验证失败
			// 	array_push($this->msg, '问题数据完整性验证失败');
			// 	$this->err += 1;
			// 	return false;
			// }
			// $question_id = $data['wiki_id'];
			// $this->data[$question_id] = $data;
			$question_id = $question['id'];
			$this->data[$question_id] = $question;
		}

		if ( is_numeric($question) ) {
			// 传入的值当做 question_id 处理
			$where = array(
				'id' => intval($question),
			);
			$data = $this->getModel()->where($where)->find();
			if ( !$data ) {
				// 数据不存在
				array_push($this->msg, '问题数据不存在或异常');
				$this->err += 1;
				return false;
			}
			$question_id = $question;
			// $question = $data;
			$this->data[$question_id] = $data;
		}
		
		return ( $this->err == 0 );
	}

	/**
	 * 验证问题实体数据完整性
	 * 主要使用场景在新增推送使用
	 */
	protected function verifyQuestionEntityForInfo ( $record=array() ) {
		$filters = &$this->field_map['info'];
		$record = array_intersect_key($record, $filters);

		if ( $this->action=='add' ) {
			if ( count($filters)!=count($record) ) {
				return false;
			}
		}
		$document = array();
		foreach ( $filters as $field => $key_conf ) {
			if ( array_key_exists($field, $record) ) {
				$key = $key_conf['key'];
				$handlers = $key_conf['handler'];
				if ( !empty($handlers) ) {
					foreach ( $handlers as $_i => $handle_conf ) {
						$action = $handle_conf['act'];
						$param = $handle_conf['p'];
						foreach ( $param as $_pi => $val ) {
							if ( $val=='#self#' ) {
								$param[$_pi] = $record[$field];
							}
						}
						// 对每一个自字义的操作执行一次格式处理
						$record[$field] = call_user_func_array($action, $param);
					}
				}
				$document[$key] = $record[$field];
			}
		}
		if ( !in_array(intval($document['status']), array(21,22,23)) ) {
			$document['deleted'] = 1;
		} else {
			$catepath = explode('-', trim($document['catepath'], '0-'));
			$keys = array('topcolumn', 'subcolumn');
			$lCate = D('Cate', 'Logic', 'Common');
			foreach ( $keys as $inx => $key ) {
				if ( isset($catepath[$inx]) && $catepath[$inx]!='' ) {
					$document[$key] = $lCate->getCateName($catepath[$inx], 'qa');
				}
			}
		}
		// var_dump($document);exit;
		return $document;
	}


	public function getError( $withMsg=true ) {
		$result = array(
			'status' => ( $this->err > 0 ),
			'errors' => $this->err,
		);
		if ( $withMsg ) {
			$result['messages'] = $this->msg;
		}
		return $result;
	}

	public function getData( $withConverted=true ) {
		$result = array('origin'=>$this->data);
		if ( $withConverted ) {
			foreach ( $this->target as $type => &$data ) {
				$result[$type] = $data;
			}
		}
		return $result;
	}

	/**
	 * 发布并推送到相关服务
	 * 业务上，status in (21,22,23) 的数据，将推送到外部业务
	 */
	public function Publish ( $questions=array() ) {
		$stat = array('status'=>false);

		if ( !empty($questions) ) {
			$status = $this->confirmQuestions($questions);
		} else {
			$status = ( $this->err == 0 );
		}
		if ( !$status ) {
			// 确认要发布的问题异常
			return $stat;
		}

		
		// 新闻池推送
		$stat['info'] = $this->convertToInfo()->pushToInfo();
		// 搜索服务推送
		$stat['service'] = $this->convertToService()->pushToService();
		$stat['status'] = true;
		return $stat;
	}

	/**
	 * 从服务中删除数据
	 */
	public function Delete ( $questions=array() ) {
		$stat = array('status'=>false);
		if ( !empty($questions) ) {
			$status = $this->confirmQuestions($questions);
		} else {
			$status = ( $this->err == 0 );
		}
		if ( !$status ) {
			// 确认要发布的问题异常
			return false;
		}

		// 新闻池推送
		$stat['info'] = $this->convertToInfo()->pushToInfo();
		// 搜索服务推送
		$stat['service'] = $this->convertToService()->pushToService();
		$stat['status'] = true;
		return $stat;
	}

	/**
	 * 将数据转换为新闻池数据结构
	 */
	public function convertToInfo() {
		if ( !isset($this->target['info']) ) {
			$this->target['info'] = array();
		}
		$data = &$this->data;
		foreach ( $data as $id => $item ) {
			$_item = $this->verifyQuestionEntityForInfo( $item );
			if ( $_item ) {
				// id 必须存在
				$_item['id'] = $_item['wiki_id'];
				$status = intval($item['status']);
				if ( $this->action!='delete' && in_array($status, array(21,22,23)) ) {
					$_item['deleted'] = 0;	// 未删除
				} else {
					$_item['deleted'] = 1;	// 已删除
				}
				$_item['status'] = 0; // 审核通过
				// 以下 3 项为固定值
				$_item['type'] = 3;
				$_item['system_sharding_id'] = 549;
				$_item['system_sharding_flag'] = $_item['id'] % 4095;
				$this->target['info'][$id] = $_item;
			}
		}
		return $this;
	}


	// 推送到新闻池 支持多条
	protected function pushToInfo() {
		if ( !isset($this->target['info']) || empty($this->target['info']) ) {
			array_push($this->msg, '向服务推送的数据为空');
			$this->err += 1;
			return false;
		}
		$lInfos = D('Infos', 'Logic', 'Common');
		// 强制重试
		$try_max = 3;
		$try_cnt = 0;
		$status = -1;
		while ( $status < 0 ) {
			if ( $try_cnt >= $try_max ) {
				array_push($this->msg, '向新闻池推送 3 次均未成功');
				$this->err += 1;
				return false;
			}
			$ret = $lInfos->batchPushQuestions($this->target['info']);
			$status = intval($ret['status']);
			$try_cnt ++;
		}
		return true;
	}



	/**
	 * 将数据转换为新闻池数据结构
	 */
	public function convertToService() {
		if ( !isset($this->target['service']) ) {
			$this->target['service'] = array();
		}
		$lSearch = D('Search', 'Logic', 'Common');

		$data = &$this->data;
		foreach ( $data as $id => $item ) {
			$_item = $lSearch->questionDocConvert( $item );
			if ( $_item ) {
				if ( $this->action=='delete' ) {
					$_item['_deleted'] = true;	// 已删除
				}
				$this->target['service'][$id] = $_item;
			}
		}
		return $this;
	}


	// 推送到搜索服务 支持多条
	protected function pushToService() {
		if ( !isset($this->target['service']) || empty($this->target['service']) ) {
			array_push($this->msg, '向服务推送的数据为空');
			$this->err += 1;
			return false;
		}
		$lSearch = D('Search', 'Logic', 'Common');

		// 强制重试
		$try_max = 3;
		$try_cnt = 0;
		$status = false;
		while ( !$status ) {
			if ( $try_cnt >= $try_max ) {
				array_push($this->msg, '向搜索服务推送 3 次均未成功');
				$this->err += 1;
				return false;
			}
			$status = $lSearch->create($this->target['service'], 'question');
			$try_cnt ++;
		}

		return true;
	}



}