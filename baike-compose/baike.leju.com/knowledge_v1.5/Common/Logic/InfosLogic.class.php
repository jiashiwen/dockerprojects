<?php
/**
 * 推荐信息获取逻辑
 * 数据取自新闻池
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class InfosLogic {

	protected $infoInstance = null;

	protected function initInfoInstance() {
		if ( is_null($this->infoInstance) ) {
			$this->infoInstance = new \Org\Leju\Model\Info;
		}
		return $this->infoInstance;
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
		if ( $ret['total']>0 ) {
			$result = array();
			foreach ( $ret['data'] as $i => $item ) {
				$item['m_url'] = $config['M_DOMAIN']."/news-{$item['city']}-{$item['id']}.html"; //转成触屏新闻链接
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
		if ( $ret['total']>0 ) {
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
		if ( $ret['total']>0 ) {
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
		// var_dump($ret);
		$result = false;
		if ( $ret['total']>0 ) {
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
	 * @param $fileds string 要返回的字段 默认使用id|word
	 * @param $order string 排序规则 默认使用{updatetime}desc
	 * @return mixed 成功返回 数据集合 失败返回 false
	 */
	public function getTags ( $page, $pagesize=100, $last=false, $opts=array('{deleted@eq}0'), $fileds='id|word', $order='{updatetime}desc' ) {
		$this->initInfoInstance();
		$config = C('INFOLIB.TAGS');
		$this->infoInstance->setConfig($config);
		$where = array(
			'ver' => '2.0',
			'count' => 1,
			'field' => $fileds,
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
		if ( $ret && $ret['total'] > 0 ) {
			$result = array(
				'total' => &$ret['total'],
				'list' => &$ret['data'],
			);
		}
		return $result;
	}

	/**
	 * 向新闻池中推送单条数据
	 */
	public function pushNewsPool ( $data, $delete=false ) {
        $data = $this->_converPushNewsPoolData($data,$delete);

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

    private function _converPushNewsPoolData($data,$delete)
    {
        if ($delete)
        {
            $return = array (
                'id' => $data['id'],
                'wiki_id' => $data['id'],
                'deleted' => 1,
                'system_sharding_id' => '582',
                'system_sharding_flag' => $data['id'] % 4096,
            );
            return $return;
        }
        $newsids = array();
        $houseids = array();
        if (!empty($data['rel_news']))
        {
            $data['rel_news'] = json_decode($data['rel_news'],true);
            foreach ($data['rel_news'] as $key=>$item)
            {
               array_push($newsids,$item['id']);
            }
        }
        if (!empty($data['rel_house']))
        {
            $data['rel_house'] = json_decode($data['rel_house'],true);
            foreach ($data['rel_house'] as $key=>$item)
            {
                array_push($houseids,$item['hid']);
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
            'status' => 9,
            'title' => $data['title'],
            'from' => 0,
            'fromurl' => '',
            'content' => clear_all($data['content']),
            'pic' => $data['cover'],
            'tags' => $data['tags'],
            'city' => $data['scope'] == '_' ? 'all' : $data['scope'], // 全国使用 all
            'creator' => $data['editor'],
            'createtime' => $data['ctime'], // 使用 ctime
            'updatetime' => $data['version'], // 使用 version
            'news' => $newsids,
            'house' => $houseids,
            'topcolumn' => $topcolumn,
            'subcolumn' => $subcolumn,
            'thirdcolumn' => $thirdcolumn,
            'type' => 1, // 知识使用 1
            'system_sharding_id' => '582',
            'system_sharding_flag' => $data['id'] % 4096,
        );
        return $return;
    }


}