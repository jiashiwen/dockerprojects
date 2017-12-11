<?php
/**
 * 推荐信息获取逻辑
 * 数据取自新闻池
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class InfosLogic {

	const TYPE_KB = 1;
	const TYPE_WIKI = 2;
	const TYPE_QA = 3;
	const TYPE_LDQ = 4; // 乐道问答问题
	const TYPE_LDA = 5; // 乐道问答回答
	const TYPE_PNQ = 6; // 人物问答问题
	const TYPE_PNA = 7; // 人物问答回答

	protected $types = null;
	protected $infoInstance = null;
	protected function initInfoInstance() {
		if ( is_null($this->infoInstance) ) {
			$this->infoInstance = new \Org\Leju\Model\Info;
		}
		return $this->infoInstance;
	}

	public function __construct() {
		$this->types = array(
			'knowledge' => self::TYPE_KB,
			'wiki' => self::TYPE_WIKI,
			'question' => self::TYPE_QA,
			'ldquestion' => self::TYPE_LDQ, // 乐道问答问题
			'ldanswer' => self::TYPE_LDA, // 乐道问答回答
			'pnquestion' => self::TYPE_PNQ, // 人物问答问题
			'pnanswer' => self::TYPE_PNA, // 人物问答回答
		);
	}

	/**
	 * 获取推荐新闻
	 * @param $business string 新闻业务类型
	 * @return mixed 成功返回新闻列表集合 失败返回false
	 */
	public function selectNews ( $business='house_news', $page=1, $pagesize=100, $opts=array(), $fields=array(), $order='{id}desc' ) {
		$business = strtolower($business);
		$businesses = array(
			'house_news' => 'NEWS',
			'jiaju_news' => 'JIAJU',
			'knowledge' => 'WIKI',
		);
		if ( !array_key_exists($business, $businesses) ) {
			return false;
		}
		$this->initInfoInstance();
		$b = $businesses[$business];
		$configs = C('INFOLIB');
		if ( !array_key_exists($b, $configs) ) {
			return false;
		}
		$config = $configs[$b];
		$this->infoInstance->setConfig($config);

		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => implode('|', $fields),
			'order' => $order,
			'filters' => $opts,
			'page' => $page, 
			'pcount' => $pagesize,
		);
		return $this->infoInstance->query($where);
	}

	/**
	 * 获取推荐新闻
	 * @param $ids mixed 新闻编号列表
	 * @return mixed 成功返回新闻列表集合 失败返回false
	 */
	public function getNews ( $ids=array() ) {
		if ( is_string($ids) || is_numeric($ids) ) {
			$ids = explode(',', $ids);
		}
		if ( !is_array($ids) || empty($ids) ) {
			return false;
		}
		$this->initInfoInstance();
		$config = C('INFOLIB.NEWS');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => 'id|title|url|createtime|media|city',
			'order' => '{createtime}desc',
			'filters' => array(
				'{deleted@eq}0',
				'{id@eq}'.implode('|', $ids),
			),
		);
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret['status']>0 ) {
			$result = array();
			foreach ( $ret['data'] as $i => $item ) {
				$item['m_url'] = $config['M_DOMAIN']."/news-{$item['city']}-{$item['id']}.html"; //转成触屏新闻链接
				$item['url'] = str_replace('.house.sina.com.cn', '.leju.com', $item['url']); //转为乐居域名
				$result[$item['id']] = $item;
			}
		}
		return $result;
	}

	/**
	 * 获取楼盘数据
	 * @param $ids mixed 楼盘编号列表
	 * @return mixed 成功返回楼盘列表集合 失败返回false
	 */
	public function getHouse ( $ids=array() ) {
		$data = $this->parseHouseIDs($ids);
		if ( false===$data ) {
			return false;
		}

		$this->initInfoInstance();
		$config = C('INFOLIB.HOUSE');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => 'site|hid|name|price_display|salephone|pic_s320|phone_extension|city',
			// 'order' => '{createtime}desc',
			'filters' => array(
				'{status@eq}1',
				'{site@eq}'.implode('|', $data['city']),
				'{hid@eq}'.implode('|', $data['hid']),
			),
		);
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret['status']>0 ) {
			$result = array();
			foreach ( $ret['data'] as $i => $item ) {
				$id = $item['site'].$item['hid'];
				$item['m_url'] = $config['M_DOMAIN']."/touch/house/{$item['site']}/{$item['hid']}/";
				$item['url'] = $config['DOMAIN']."/".$item['site'].$item['hid'];
				$result[$id] = $item;
			}
			$filters = array_flip($data['ids']);
			$result = array_intersect_key($result, $filters);
		}
		return $result;
	}

	// 分析楼盘库编号，统一将city+hid格式字符串的楼盘编号分解成 city 与 hid 两个数据
	protected function parseHouseIDs ( $house_ids ) {
		if ( is_string($house_ids) ) {
			if ( $house_ids!='' || strpos($house_ids, ',') ) {
				$house_ids = explode(',', $house_ids);
			} else {
				return false;
			}
		} else if ( !is_array($house_ids) ) {
			return false;
		}
		// 过滤恶意传入的城市代码
		$cities = C('CITIES.ALL');
		$ids = array('city'=>array(),'hid'=>array(),'ids'=>array());
		foreach ( $house_ids as $i => $house_id ) {
			if ( is_string($house_id) ) {
				$ret = preg_match('/^(?P<city>[a-z]+)(?P<hid>[0-9]+)$/i', $house_id, $matches);
				if ( $ret!==0 ) {
					// $data = array_intersect_key($matches, array('city'=>'','hid'=>''));
					$city = isset($matches['city']) ? strtolower($matches['city']) : false;
					$hid = isset($matches['hid']) ? abs(intval($matches['hid'])) : 0;
					if ( $hid==0 || !$city || !array_key_exists($city, $cities) ) {
						continue;
					}
					if ( !in_array($city, $ids['city']) ) {
						array_push($ids['city'], $city);
					}
					if ( !in_array($hid, $ids['hid']) ) {
						array_push($ids['hid'], $hid);
					}
					$_id = $city.$hid;
					if ( !in_array($_id, $ids['ids']) ) {
						array_push($ids['ids'], $_id);
					}
				} else {
					continue;
				}
			} else if ( is_array($house_id) ) {
				$city = isset($house_id['city']) ? strtolower($house_id['city']) : false;
				$hid = isset($house_id['hid']) ? abs(intval($house_id['hid'])) : 0;
				if ( $hid==0 || !$city || !array_key_exists($city, $cities) ) {
					continue;
				}
				if ( !in_array($city, $ids['city']) ) {
					array_push($ids['city'], $city);
				}
				if ( !in_array($hid, $ids['hid']) ) {
					array_push($ids['hid'], $hid);
				}
				$_id = $city.$hid;
				if ( !in_array($_id, $ids['ids']) ) {
					array_push($ids['ids'], $_id);
				}
			} else {
				continue;
			}
		}
		if ( empty($ids['ids']) ) {
			return false;
		}
		return $ids;
	}

	/**
	 * 获取标签相关新闻
	 * @param $tags array|string 标签列表，1.数组，每一项对应一个标签；2.字符串，每个标签使用,分隔
	 * @param $limit int 获取数据数量，默认返回3条
	 * @return mixed 结果集合，成功返回集合数组，失败返回 false
	 */
	public function relNews ( $tags=array(), $limit=3 ) {
		$this->initInfoInstance();

		// step 1. 标签字符串转换为标签编号
		$tagids = $this->convertTagToId($tags);
		if ( !$tagids ) {
			return false;
		}

		// step 2. 获取标签对应的新闻
		$config = C('INFOLIB.NEWS');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'pcount' => $limit,
			'page' => 1,
			'field' => 'id|title|url|createtime|media|tags|city',
			'order' => '{createtime}desc',
			'filters' => array(
				'{deleted@eq}0',
				'{tags_id@eq}'.implode('|',$tagids),
			),
		);
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret['status']>0 ) {
			$result = array();
			foreach ( $ret['data'] as $i => $item ) {
				$item['m_url'] = $config['M_DOMAIN']."/news-{$item['city']}-{$item['id']}.html"; //转成触屏新闻链接
				$result[$item['id']] = $item;
			}
		}
		return $result;
	}

	/**
	 * 获取标签相关楼盘
	 * @param $tags array|string 标签列表，1.数组，每一项对应一个标签；2.字符串，每个标签使用,分隔
	 * @param $limit int 获取数据数量，默认返回3条
	 * @return mixed 结果集合，成功返回集合数组，失败返回 false
	 * @TODO: @凌雷，你确认楼盘有与新闻池对应的统一的标签数据？ @黄路 @齐峥，楼盘库的tag_id是哪个字段？
	 */
	public function relHouse ( $tags=array(), $limit=3 ) {
		$this->initInfoInstance();
		// step 1. 标签字符串转换为标签编号
		// $tagids = $this->convertTagToId($tags);
		// if ( !$tagids ) {
		// 	return false;
		// }
		// step 2. 获取标签对应的楼盘
		$config = C('INFOLIB.HOUSE');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'pcount' => $limit,
			'page' => 1,
			// 'field' => '',
			'field' => 'site|hid|name|price_display|salephone|pic_s320',
			'order' => '{createtime}desc',
			'filters' => array(
				'{status@eq}1',
				// '{tags_id@eq}'.implode('|',$tagids),
			),
		);
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret['status']>0 ) {
			$result = $ret['data'];
		}
		return $result;
	}

	/**
	 * 按指定标签返回标签对应的新闻池标签编号
	 * @param $tags array|string 标签列表，1.数组，每一项对应一个标签；2.字符串，每个标签使用,分隔
	 * @return mixed 结果列表，成功返回标签编号集合数组，失败返回 false
	 */
	public function convertTagToId( $tags=array() ) {
		if ( is_string($tags) ) {
			$tags = explode(',', $tags);
		}
		if ( empty($tags) ) {
			return false;
		}
		$this->initInfoInstance();
		$config = C('INFOLIB.TAGS');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'pcount' => $limit,
			'page' => 1,
			// 'field' => 'id|tag_id|word|first_char|pinyin|desc|pic',
			'field' => 'tag_id',
			'order' => '',
			'filters' => array(
				'{deleted@eq}0',
				'{word@eq}'.implode('|',$tags),
			),
		);
		$ret = $this->infoInstance->query($where);
		if ( $ret['message']!=='成功' || $ret['status'] <= 0 || !isset($ret['data']) ) {
			return false;
		}

		$tagids = array();
		foreach ( $ret['data'] as $i => $word ) {
			array_push($tagids, $word['tag_id']);
		}
		return $tagids;
	}


	/**
	 * 获取标签列表
	 * @param $page int 指定页码
	 * @param $pagesize int 指定每页数据量 默认返回100条
	 * @param $last mixed 指定最后查询时间 false
	 * @param $opts array 查询条件 默认使用{deleted@eq}0
	 * @param $fields string 要返回的字段 默认使用id|word
	 * @param $order string 排序规则 默认使用{updatetime}desc
	 * @return mixed 成功返回 数据集合 失败返回 false
	 */
	public function getTags ( $page, $pagesize=100, $last=false, $opts=array('{deleted@eq}0'), $fields='id|word', $order='{updatetime}desc' ) {
		$this->initInfoInstance();
		$config = C('INFOLIB.TAGS');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => $fields,
			'pcount' => $pagesize,
			'page' => $page,
			'order' => $order,
			'filters' => $opts
		);
		if ( $last!==false ) {
			$where['filters'][] = '{createtime@gt}'.$last;
		}
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret && $ret['status']>0 ) {
			$result = array(
				'total' => &$ret['status'],
				'list' => &$ret['data'],
			);
		}
		return $result;
	}


	/**
	 * 获取标签系统标签数据
	 * @param $page int 指定页码
	 * @param $pagesize int 指定每页数据量 默认返回100条
	 * @param $last mixed 指定最后查询时间 false
	 * @param $opts array 查询条件 默认使用{deleted@eq}0
	 * @param $fields string 要返回的字段 默认使用id|word
	 * @param $order string 排序规则 默认使用{updatetime}desc
	 * @return mixed 成功返回 数据集合 失败返回 false
	 */
	public function getAllTags ( $page, $pagesize=100, $last=false, $opts=array('{status@eq}0','{deleted@eq}0'), $fields='tag_id|word', $order='{updatetime}desc' ) {
		$this->initInfoInstance();
		$config = C('INFOLIB.TAG');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => $fields,
			'pcount' => $pagesize,
			'page' => $page,
			'order' => $order,
			'filters' => $opts
		);
		if ( $last!==false ) {
			$where['filters'][] = '{createtime@gt}'.$last;
		}
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret && $ret['status']>0 ) {
			$result = array(
				'total' => &$ret['status'],
				'list' => &$ret['data'],
			);
		}
		return $result;
	}

	public function getTagsByIds ( $tagids=array(), $opts=array('{status@eq}0','{deleted@eq}0'), $fields='tag_id|word', $order='' ) {
		if ( empty($tagids) ) return false;

		$this->initInfoInstance();
		$config = C('INFOLIB.TAG');
		$this->infoInstance->setConfig($config);
		array_push($opts, '{tag_id@eq}'.implode('|', $tagids));
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => $fields,
			'pcount' => 10000,
			'page' => 1,
			'filters' => $opts
		);
		if ( $order != '' ) {
			$where['order'] = $order;

		}
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret && $ret['status']>0 ) {
			$result = array(
				'total' => &$ret['status'],
				'list' => &$ret['data'],
			);
		}
		return $result;
	}

	public function searchTags ( $tags=array(), $opts=array('{status@eq}0','{deleted@eq}0'), $fields='tag_id|word', $order='{updatetime}desc' ) {
		if ( empty($tags) ) return false;

		$this->initInfoInstance();
		$config = C('INFOLIB.TAG');
		$this->infoInstance->setConfig($config);
		array_push($opts, '{word@eq}'.implode('|', $tags));
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => $fields,
			'pcount' => 10000,
			'page' => 1,
			'order' => $order,
			'filters' => $opts
		);
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret && $ret['status']>0 ) {
			$result = array(
				'total' => &$ret['status'],
				'list' => &$ret['data'],
			);
		}
		return $result;
	}


	/**
	 * 从新闻池楼盘数据中获取开发商名称
	 * @date 2017-10-10 by Robert
	 */
	public function getDeveloperName ( $keyword='', $page=1, $pagesize=10 ) {
		$fields = 'developer';
		$this->initInfoInstance();
		$config = C('INFOLIB.HOUSE');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => $fields,
			'pcount' => $pagesize,
			'page' => $page,
			'order' => $order,
			'filters' => [],
			'word' => '{developer}'.$keyword,
		);
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret && $ret['status']>0 ) {
			$result = array(
				'total' => &$ret['status'],
				'list' => &$ret['data'],
			);
		}
		return $result;
	}

	/**
	 * 获取看房专车信息
	 *
	 */
	public function getCarByCity ( $city, $order='{hid}desc', $page=1, $pagesize=10 ) {
		$fields = 'site|hid|name|district_name|price_display';
		$opts = array(
			'{support_car@eq}1',
			'{status@eq}1',
			'{site@eq}'.$city,
		);
		$this->initInfoInstance();
		$config = C('INFOLIB.HOUSE');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => $fields,
			'pcount' => $pagesize,
			'page' => $page,
			'order' => $order,
			'filters' => $opts
		);
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret && $ret['status']>0 ) {
			$result = array(
				'total' => &$ret['status'],
				'list' => &$ret['data'],
			);
		}
		return $result;
	}

	/**
	 * 向新闻池中推送单条数据
	 */
	public function pushNewsPool ( $data, $type=self::TYPE_KB, $delete=false ) {
		// 如果不是生产环境，就不再向新闻池推送数据了
		if ( defined('APP_DEPLOY') && APP_DEPLOY!='prd' ) {
			return true;
		}

		$data = $this->_converPushNewsPoolData($data, $type, $delete);
		$this->initInfoInstance();
		$config = C('INFOLIB.WIKI');
		$this->infoInstance->setConfig($config);

		$data = array(
			'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
			'count' => 1,
		);
		$result = $this->infoInstance->pushData($data);

		return $result;
	}

	/**
	 * 向新闻池批量推送数据，仅支持创建和更新
	 *
	 */
	public function batchPushNewsPool ( $list=array(), $type=self::TYPE_KB, $delete=false ) {
		// 如果不是生产环境，就不再向新闻池推送数据了
		if ( defined('APP_DEPLOY') && APP_DEPLOY!='prd' ) {
			return true;
		}

		if ( !is_array($list) || empty($list) ) {
			return false;
		}

		$this->initInfoInstance();
		$config = C('INFOLIB.WIKI');
		$this->infoInstance->setConfig($config);
		$data = array();
		foreach ( $list as $k => $item ) {
			array_push($data, $this->_converPushNewsPoolData($item, $type, $delete));
		}
		$count = count($data);
		$data = array(
			'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
			'count' => $count,
		);

		$result = $this->infoInstance->pushData($data);
		return $result;
	}

	private function _converPushNewsPoolData($data, $type=self::TYPE_KB, $delete=false) {
		if ( $delete ) {
			$return = array (
				'id' => $data['id'],
				'wiki_id' => $data['id'],
				// 'type' => $type,
				'deleted' => 1,
				'system_sharding_id' => $this->_getShardingID($type),
				'system_sharding_flag' => $data['id'] % 4096,
			);
			return $return;
		}
		$return = false;
		if ( $type==self::TYPE_KB ) {
			$return = $this->_convertKBData($data);
		}
		if ( $type==self::TYPE_WIKI ) {
			$return = $this->_convertWIKIData($data);
		}
		if ( $type==self::TYPE_QA ) {
			$return = $this->_convertQAData($data);
		}
		if ( $type==self::TYPE_LDQ ) {
			$return = $this->_convertLDQData($data);
		}
		if ( $type==self::TYPE_LDA ) {
			$return = $this->_convertLDAData($data);
		}
		if ( $type==self::TYPE_PNQ ) {
			$return = $this->_convertPNQData($data);
		}
		if ( $type==self::TYPE_PNA ) {
			$return = $this->_convertPNAData($data);
		}
		// 如果是第一次推送或是重新推送，强制使用未删除状态字 0表示未删除
		$return['deleted'] = 0;
		/*
		// 如果数据中有id，把id干掉
		if ( isset($return['id']) ) {
			unset($return['id']);
		} // 新闻池会提示 缺少主键字段id
		*/
		return $return;
	}

	private function _getShardingID($type) {
		$return = false;
		if ( $type==self::TYPE_KB ) {
			$return = 582; // 不能变
		}
		if ( $type==self::TYPE_WIKI ) {
			$return = 584;
		}
		if ( $type==self::TYPE_QA ) {
			$return = 549; // 不能变
		}
		if ( $type==self::TYPE_LDQ ) { // 乐道问答 问题
			$return = 594; // 不能变
		}
		if ( $type==self::TYPE_LDA ) { // 乐道问答 回答
			$return = 596; // 不能变
		}
		if ( $type==self::TYPE_PNQ ) { // 人物问答 问题
			$return = 595; // 不能变
		}
		if ( $type==self::TYPE_PNA ) { // 人物问答 回答
			$return = 597; // 不能变
		}

		return $return;
	}
	/**
	 * 转换知识数据
	 */
	private function _convertKBData($data) {
		$newsids = array();
		$houseids = array();
		if (!empty($data['rel_news'])) {
			$data['rel_news'] = json_decode($data['rel_news'],true);
			foreach ($data['rel_news'] as $key=>$item) {
				array_push($newsids,$item['id']);
			}
		}
		if (!empty($data['rel_house'])) {
			$data['rel_house'] = json_decode($data['rel_house'],true);
			foreach ($data['rel_house'] as $key=>$item)
			{
				array_push($houseids,$item['hid']);
			}
		}
		// 通过数据库重新提交时使用的
		if ( empty($newsids) && isset($data['news']) ) {
			$data['news'] = json_decode($data['news'],true);
			foreach ($data['news'] as $key => $item) {
				if ( isset($item['id']) && intval($item['id']>0) ) {
					array_push($newsids, $item['id']);
				}
			}
		}
		if ( empty($houseids) && isset($data['house']) ) {
			$data['house'] = json_decode($data['house'],true);
			foreach ($data['house'] as $key=>$item) {
				if ( isset($item['hid']) && intval($item['hid']>0) ) {
					array_push($houseids, $item['hid']);
				}
			}
		}

		$path = explode('-',$data['catepath']);
		$lCate = D('Cate','Logic','Common');
		$topcolumn = $lCate->getCateName($path['1']);
		$subcolumn = $lCate->getCateName($path['2']);
		$thirdcolumn = $lCate->getCateName($path['3']);

		$return = array (
			'id' => $data['id'],
			'wiki_id' => $data['id'],
			'unique_id' => $this->getUniqueID('knowledge', $data['id']),
			'status' => 9,
			'title' => $data['title'],
			'from' => 0,
			'fromurl' => '',
			// 'content' => clear_all($data['content']),
			'content' => $data['content'],
			'pic' => $data['cover'],
			'tags' => $data['tags'],
			'city' => $data['scope'] == '_' ? 'all' : $data['scope'], // 全国使用 all
			'creator' => $data['editor'],
			'createtime' => $data['ctime'], // 使用 ctime
			'updatetime' => $data['version'], // 使用 version
			'news' => implode(',', $newsids),	// 只传 news_id 列表
			'house' => implode(',', $houseids),	// 只传 house.hid 列表
			'topcolumn' => $topcolumn,
			'subcolumn' => $subcolumn,
			'thirdcolumn' => $thirdcolumn,
			'type' => self::TYPE_KB,
			'system_sharding_id' => $this->_getShardingID(self::TYPE_KB),
			'system_sharding_flag' => $data['id'] % 4096,
		);
		return $return;		
	}

	/**
	 * 转换百科数据
	 */
	private function _convertWIKIData($data) {
		if ( !is_array($data) ) {
			return false;
		}
		// 2017-11-06 按黄路要求，将推荐字段映射到 is_recommend 上
		$data['is_recommend'] = intval($data['is_recommended']);
		// 2017-11-10 按陈文硕，赵珊要求，将扩展一个虚拟字段 company_stock_market 用于存放公司上市市场
		if ( $data['cateid']==1 ) {
			$data['company_stock_market'] = strtoupper(substr($data['company_stock_code'], 0, 2));
		}
		$data['wiki_id'] = $data['id'];
		$data['unique_id'] = $this->getUniqueID('wiki', $data['id']);
		$data['type'] = self::TYPE_WIKI;
		if ( is_array($data['album']) ) {
			$data['album'] = json_encode($data['album']);
		}
		$data['createtime'] = $data['ctime'];
		$data['updatetime'] = $data['utime'];
		$data['system_sharding_id'] = $this->_getShardingID(self::TYPE_WIKI);
		$data['system_sharding_flag'] = $data['id'] % 4096;
		return $data;
	}

	/**
	 * 转换问答数据
	 */
	private function _convertQAData($data) {
		if ( !is_array($data) ) {
			return false;
		}
		$data['wiki_id'] = $data['id'];
		$data['unique_id'] = $this->getUniqueID('question', $data['id']);
		$data['type'] = self::TYPE_QA;
		$data['createtime'] = $data['ctime'];
		$data['updatetime'] = $data['utime'];
		$data['system_sharding_id'] = $this->_getShardingID(self::TYPE_QA);
		$data['system_sharding_flag'] = $data['id'] % 4096;
		return $data;
	}

	/**
	 * 转换乐道问答问题数据
	 */
	private function _convertLDQData($data) {
		if ( !is_array($data) ) {
			return false;
		}
		// 2017-12-09 按黄路要求，将精华推荐字段映射到 is_recommend 上
		$data['is_recommend'] = intval($data['essence'])==0 ? 0 : 1;
		$data['wiki_id'] = $data['id'];
		$data['type'] = self::TYPE_LDQ;
		$data['createtime'] = $data['ctime'];
		$data['updatetime'] = $data['utime'];
		$data['system_sharding_id'] = $this->_getShardingID(self::TYPE_LDQ);
		$data['system_sharding_flag'] = $data['id'] % 4096;
		return $data;
	}
	/**
	 * 转换乐道回答回答数据
	 */
	private function _convertLDAData($data) {
		if ( !is_array($data) ) {
			return false;
		}
		$data['wiki_id'] = $data['id'];
		$data['unique_id'] = $this->getUniqueID('ldanswer', $data['id']);
		$data['type'] = self::TYPE_LDA;
		$data['createtime'] = $data['ctime'];
		$data['updatetime'] = $data['utime'];
		$data['system_sharding_id'] = $this->_getShardingID(self::TYPE_LDA);
		$data['system_sharding_flag'] = $data['id'] % 4096;
		return $data;
	}

	/**
	 * 转换人物问答问题数据
	 */
	private function _convertPNQData($data) {
		if ( !is_array($data) ) {
			return false;
		}
		// 2017-12-09 按黄路要求，将精华推荐字段映射到 is_recommend 上
		$data['is_recommend'] = intval($data['essence'])==0 ? 0 : 1;
		$data['wiki_id'] = $data['id'];
		$data['type'] = self::TYPE_PNQ;
		$data['createtime'] = $data['ctime'];
		$data['updatetime'] = $data['utime'];
		$data['system_sharding_id'] = $this->_getShardingID(self::TYPE_PNQ);
		$data['system_sharding_flag'] = $data['id'] % 4096;
		return $data;
	}
	/**
	 * 转换人物问答回答数据
	 */
	private function _convertPNAData($data) {
		if ( !is_array($data) ) {
			return false;
		}
		$data['wiki_id'] = $data['id'];
		$data['unique_id'] = $this->getUniqueID('pnanswer', $data['id']);
		$data['type'] = self::TYPE_PNA;
		$data['createtime'] = $data['ctime'];
		$data['updatetime'] = $data['utime'];
		$data['system_sharding_id'] = $this->_getShardingID(self::TYPE_PNA);
		$data['system_sharding_flag'] = $data['id'] % 4096;
		return $data;
	}
	public function getUniqueID($type, $id) {
		return D('Comments', 'Logic', 'Common')->getUniqueID($type, $id);
	}

	/**
	 * 获取知识系统指定类型推送总量
	 */
	public function getPushedInfoCount( $opts=array(), $type='' ) {
		$type = array_key_exists($type, $this->types) ? $this->types[$type] : 0;
		$this->initInfoInstance();
		$config = C('INFOLIB.WIKI');
		$this->infoInstance->setConfig($config);
		if ( intval($type)>0 ) {
			array_push($opts, '{type@eq}'.$type);
		}
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => '',
			'pcount' => 1,
			'page' => 1,
			'filters' => $opts
		);
		$ret = $this->infoInstance->query($where);
		return intval($ret['total']);
	}

	/**
	 * 获取已推送的数据列表
	 */
	public function getPushedInfoList ( $type='', $page=1, $pagesize=100, $field='', $order='', $opts=array() ) {
		$type = array_key_exists($type, $this->types) ? $this->types[$type] : 0;
		$this->initInfoInstance();
		$config = C('INFOLIB.WIKI');
		$this->infoInstance->setConfig($config);
		if ( intval($type)>0 ) {
			array_push($opts, '{type@eq}'.$type);
		}
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => $field,
			'pcount' => $pagesize,
			'page' => $page,
			'order' => $order,
			'filters' => $opts
		);
		$ret = $this->infoInstance->query($where);
		$result = false;
		if ( $ret && $ret['status']>0 ) {
			$result = array(
				'total' => &$ret['status'],
				'list' => &$ret['data'],
			);
		}
		return $result;
	}

	public function batchPushQuestions ( $documents=array() ) {
		if ( !is_array($documents) || empty($documents) ) {
			return false;
		}

		$this->initInfoInstance();
		$config = C('INFOLIB.WIKI');
		$this->infoInstance->setConfig($config);

		$data = array_values($documents);

		$data = array(
			'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
			'count' => count($data),
		);

		$result = $this->infoInstance->pushData($data);
		return $result;
	}

}
