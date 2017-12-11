<?php
/**
 * 标签逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class TagsLogic {

	protected $_cacher = null;
	static $data = array();

	public function __construct() {
		$this->_cacher = S(C('REDIS'));
	}

	public function getAllData() {
		return self::$data;
	}
	/**
	 * 指定一组 标签编号 返回标签名称
	 */
	public function getTagnamesByTagids ( $tagids = array(), $device='' ) {
		$result = array();
		if ( is_string($tagids) ) {
			$tagids = explode(',', trim($tagids, ','));
		}
		if ( !is_array($tagids) || empty($tagids) ) {
			return $result;
		}

		$devices = array('pc'=>'pc', 'mobile'=>'touch', 'touch'=>'touch');
		$device = !array_key_exists($device, $devices) ? '' : $devices[$device];

		$_tagids = array_combine($tagids, $tagids);
		$_tagkey = array();
		// echo 'init -- <pre>', var_export(self::$data, true), '</pre>';
		// echo '0 -- <pre>', var_export($_tagids, true), '</pre>';
		foreach ( $_tagids as $_inx => $tagid ) {
			$tagid = intval($tagid);
			if ( $tagid==0 ) {
				unset($_tagids[$_inx]);
				continue;
			}
			// 进程缓存判断
			if ( array_key_exists($tagid, self::$data) && self::$data ) {
				$_tag = self::$data[$tagid];
				array_push($result, $_tag);
				unset($_tagids[$tagid]);
			} else {
				array_push($_tagkey, 'TAG:'.$tagid.':INFO');
			}
		}
		// echo '1 -- <pre>', var_export($_tagids, true), var_export($_tagkey, true), '</pre>';
		if ( !empty($_tagkey) ) {
			$ret = $this->_cacher->mget($_tagkey);
			foreach ( $ret as $_inx => $_tag ) {
				if ( $_tag ) {
					$tagid = intval(str_replace('TAG:', '', str_replace(':INFO', '', $_tagkey[$_inx])));
					$_tag = json_decode($_tag, true);
					// var_dump('1.1',$tagid);
					self::$data[$tagid] = $_tag;
					array_push($result, $_tag);
					unset($_tagids[$tagid]);
				}
			}
			// echo '2 -- <pre>', var_export($_tagkey, true), var_export($ret, true), var_export($_tagids, true), '</pre>';
		}

		if ( !empty($_tagids) ) {
			$lInfos = D('Infos', 'Logic', 'Common');
			$ret = $lInfos->getTagsByIds($_tagids);
			if ( count($ret['list']) > 0 ) {
				foreach ( $ret['list'] as $i => $tag ) {
					$tagid = intval($tag['tag_id']);
					$_tag = array(
						'id' => $tagid,
						'name' => $tag['word'],
					);
					// var_dump(3, $tagid, $_tag);
					$this->_cacheTagInfo($tagid, $_tag);
					array_push($result, $_tag);
				}
			}
			// echo '3 -- <pre>', var_export($_tagids, true), var_export($ret, true), var_export($result, true), '</pre>';
		}

		if ( $device!='' ) {
			foreach ( $result as $i => &$tag ) {
				$tag['url'] = url('agg', array($tag['id']), $device, 'ask');
			}
		}
		// echo '<hr>';
		return $result;
	}

	/**
	 * 指定一组 标签编号 返回标签名称
	 */
	public function getTagnamesByTags ( $tags = array(), $device='' ) {
		$result = array();
		if ( is_string($tags) ) {
			$tags = str_replace(' ', ',', trim($tags));
			$tags = explode(',', trim($tags, ','));
		}
		if ( !is_array($tags) || empty($tags) ) {
			return $result;
		}

		$devices = array(''=>'pc', 'pc'=>'pc', 'mobile'=>'touch', 'touch'=>'touch');
		$device = !array_key_exists($device, $devices) ? '' : $devices[$device];

		foreach ( $tags as $i => &$tag ) {
			$tag = trim($tag);
		}
		$_tags = implode(',', array_values($tags));
		$api = 'http://admin.tag.leju.com/api/api/getTagIdByName';
		$api = $api . '?tag_names=' . $_tags;
		// $params = array(
		// 	'tag_names' => $_tags,
		// );
		$ret = curl_get($api, [], [], 10);
		if ( $ret['status'] ) {
			$ret = json_decode($ret['result'], true);
			$ret = $ret['data'];
		}
		foreach ( $ret as $tagname => $tagid ) {
			$_tag = array('id'=>$tagid, 'name'=>$tagname, 'url'=>'#',);
			if ( $device!='' ) {
				$_tag['url'] = url('agg', array($tag['id']), $device, 'ask');
			}
			array_push($result, $_tag);
		}

		return $result;
	}

	public function getTagnameByTagid ( $tagid=0, $flush=false ) {
		$tagid = intval($tagid);
		if ( $tagid<=0 ) {
			return false;
		}
		if ( array_key_exists($tagid, self::$data) ) {
			return self::$data[$tagid];
		}
		$key = 'TAG:'.$tagid.':INFO';
		$info = $this->_cacher->get($key);
		if ( !$info || $flush ) {
			$lInfos = D('Infos', 'Logic', 'Common');
			$ret = $lInfos->getTagsByIds(array($tagid));
			if ( $ret && count($ret['list'])==1 ) {
				$ret = $ret['list'][0];
				$info = array('id'=>$tagid, 'name'=>$ret['word']);
			}
			$this->_cacheTagInfo($tagid, $info);
			// $expire = intval(rand(400, 600));
			// $this->_cacher->setEx($key, $expire, json_encode($info));
		}
		// self::$data[$tagid] = $info;
		return $info;
	}

	protected function _cacheTagInfo( $tagid, $taginfo, $expire=true ) {
		$key = 'TAG:'.$tagid.':INFO';
		if ( $expire===false ) {
			$this->_cacher->set($key, json_encode($taginfo));
		} else {
			if ( is_numeric($expire) ) {
				$expire = intval($expire);
			} else if ( $expire === true ) {
				$expire = intval(rand(400, 600));
			}
			$this->_cacher->setEx($key, $expire, json_encode($taginfo));
		}
		// var_dump('DEBUG::'.$tagid.' Cache to Redis!');
		self::$data[$tagid] = $taginfo;
		return true;
	}

	/**
	 * 向标签系统推送标签数据
	 * @param $taginfo array 标签数据
	 * @return bool 是否推送成功
	 */
	public function syncToTag ( $wiki_info, $clean=false ) {
		$auths = array(
			// 公司
			/*
				- 集团公司推 @赵珊
				6335727634643202339  集团公司
				6326599165296554274  公司
				- 子公司推 @赵珊
				6335727675864822676  地方公司	
				6326599165296554274  公司
				- 状态为清除时，标签分类使用 1
				删除的话 推 1 @黄路
			*/
			1 => array(
				'key'=>'c0d6597f5103d6e3e3b140b1be7b2a03',
				'category'=>[
					'1'=>['6335727634643202339','6255676087012471749'],
					'2'=>['6335727675864822676','6255676087012471749'],
				],
				'category_id'=>'',
			),
			// 人物
			2 => array(
				'key'=>'c0d6597f5103d6e3e3b140b1be7b2a03',
				'category_id'=>['6255676066699457476'],
			),
		);

		$info = [];
		$cateid = 0;
		if ( $wiki_info ) {
			$cateid = intval($wiki_info['cateid']);
			if ( !array_key_exists($cateid, $auths) ) {
				return false;
			}
			$info['id'] = intval($wiki_info['id']);
			if ( $cateid==1 ) {	// 公司使用企业简称名 即 stname 推送
				$info['tag'] = trim($wiki_info['stname']);
			}
			if ( $cateid==2 ) {	// 人物使用中文词条名 即 title 推送
				$info['tag'] = trim($wiki_info['title']);
			}
			$info['remark'] = strip_tags($wiki_info['summary']);
			$info['pic'] = trim($wiki_info['cover']);
			$info['hot'] = 0;
		}
		if ( empty($info) || $cateid==0 ) {
			return false;
		}

		$auth = $auths[$cateid];
		$tag_categories = [];
		if ( $clean!==false ) {
			// 删除的情况下，使用标签分类1
			$tag_categories = ['1'];
		} else {
			if ( $cateid == 1 ) {
				// 公司词条父id是0的，表示本身是一级公司
				if ( intval($wiki_info['company_parent_id'])==0 ) {
					$tag_categories = $auth['category']['1'];
				} else {
					// 不为0的，表示本身是一个子公司
					$tag_categories = $auth['category']['2'];
				}
				unset($auth['category']);
			}
			if ( $cateid==2 ) {
				$tag_categories = $auth['category_id'];
			}
			if ( isset($auth['category_id']) ) {
				unset($auth['category_id']);
			}
		}
		$tagid = false;
		// 多次推送
		$api = 'http://admin.tag.leju.com/api/api/add';
		$params = array_merge($info, $auth);
		if ( isset($params['category']) ) {
			unset($params['category']);
		}
		foreach ( $tag_categories as $i => $category_id ) {
			$category_id = intval($category_id);
			if ( $category_id>0 ) {
				$params['category_id'] = $category_id;
				$ret = curl_post($api, $params);
				$result = false;
				if ( $ret['status']==true ) {
					$ret = json_decode($ret['result'], true);
					$result = $tagid = intval($ret['data']);
					$dbg = [
						'api'=>$api,
						'params'=>$params,
						'ret'=>$ret,
					];
					debug('标签同步接口接口调用', $dbg, false, true);
				}
			}
		}
		if ( $tagid !== false ) {
			$where = ['id'=>$info['id']];
			$data = ['unique_tag_id'=>$tagid];
			$mWiki = D('Wiki', 'Model', 'Common');
			$ret = $mWiki->where($where)->data($data)->save();
			$dbg = [
				'where'=>$where,
				'data'=>$data,
				'ret'=>$ret,
				'sql'=>$mWiki->getLastSql(),
			];
			debug('标签同步后更新数据', $dbg, false, true);
		}
		return $result;
	}
}