<?php
/**
 * 搜索服务逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class SearchLogic {

	protected $_token_key = 'ENGINE:GLOBAL:TOKEN';
	protected $_configs = array();
	protected $_token = false;

	public function __construct() {
		$this->_configs = C('ENGINE');
	}

	protected function checkToken($flush=false) {
		$token = false;
		if ( $this->_token===false ) {
			$cacher = S(C('REDIS'));
			$token = $cacher->get($this->_token_key);
			if ( $flush || !$token ) {
				$token = $this->getToken();
			} else {
				$this->_token = $token;
			}
		}
		return !!($token);
	}

	/**
	 * 获取访问认证
	 * @return mixed 成功返回token字符串 失败时返回 false
	 * @logs
	 * 1. 更新 /ch/login 接口的返回结构，添加过期时间逻辑
	 */
	public function getToken() {
		$api = $this->_configs['GET_TOKEN'];
		$user = $this->_configs['AUTH_USER'];
		$pass = $this->_configs['AUTH_PASS'];

		$params = array(
			'username' => $user,
			'password' => $pass,
		);

		$result = false;
		$ret = curl_post($api, $params);
		if ( $ret['status'] ) {
			$result = json_decode($ret['result'], true);
			if ( $result['status'] ) {
				// $expire = intval($result['expire']) - 30;
				$expire = 300;
				$result = $result['token'];
				$cacher = S(C('REDIS'));
				$cacher->setex($this->_token_key, $expire, $result);
			}
		} else {
			debug('get token 接口异常', $ret, 'lite');
		}
		$this->_token = $result;
		return $result;
	}

	/* -------------- 字典和分析类操作 -------------- */
		/**
		 * 指定正文数据，分析正文内容中出现的乐居通用标签值
		 * @param $content string 正文纯文本内容
		 * @param $stats 是否进行词频统计 true返回词频 false不返回词频
		 * @param $limit 返回数据量 默认仅返回前5条
		 * @return mixed 成功返回 array 失败返回 false
		 */
		public function analyze ( $content, $stats=true, $limit=0, $dict='' ) {
			$this->checkToken();
			$api = $this->_configs['PARSETAGS_API'];
			$dict = $this->setParseDict($dict);

			$data = array(
				'k' => $content, // 文本正文，去掉 html 标签
				'id' => $dict, // 使用的业务编号
				'iscombine' => 'true', // 合并重复词
				'usesmart' => 'true', // 使用智能分词
				'needtf' => $stats ? 'true' : 'false', // 是否统计词频
			);
			if ( intval($limit) > 0 ) {
				$data['limit'] = $limit; // 返回词的数量

			}
			$headers = array(
				'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_post($api, http_build_query($data), $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				// debug('analyze 接口日志', $ret, 'lite');
				if ( $ret['status']==true ) {
					$result = $ret['result'];
				}
			} else {
				// debug('analyze 接口异常', $ret, 'lite');
			}

			return $result;
		}

		/**
		 * 获取字典中所有的词条
		 * @return mixed 成功返回 array 失败返回 false
		 */
		public function getDictWords($dict='') {
			$this->checkToken();
			$api = $this->_configs['DICT_GETALL'];
			$dict = $this->setParseDict($dict);

			$params = array(
				'id' => $dict,
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_get($api, $params, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('get dict 接口日志', $ret, 'lite');
				if ( $ret['status']==true ) {
					$result = $ret['exists']['words'];
				}
			} else {
				debug('get dict 接口异常', $ret, 'lite');
			}
			return $result;
		}

		/**
		 * 向字典中批量设置词条
		 * @param $words array 指定要存入的词条
		 * @param $dict string 指定字典 默认使用 dict_tags
		 * @return mixed 成功返回 int 添加的词条数量 失败返回 false
		 */
		public function setDictWords($words=array(), $dict='') {
			if ( empty($words) ) {
				return false;
			}
			$this->checkToken();
			$api = $this->_configs['DICT_SETALL'];
			$dict = $this->setParseDict($dict);

			$params = array(
				'id' => $dict,
				'words' => implode(',', $words),
			);
			$headers = array(
				'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_post($api, $params, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('set dict 接口日志', $ret, 'lite');
				if ( $ret['status']==true ) {
					$result = intval($ret['count']);
				}
			} else {
				debug('set dict 接口异常', $ret, 'lite');
			}
			return $result;
		}

		/**
		 * 向字典中批量添加词条
		 * @param $words array 指定要添加的词条
		 * @param $dict string 指定字典 默认使用 dict_tags
		 * @return mixed 成功返回 字典中的总词条数量 失败返回 false
		 */
		public function appendDictWords($words=array(), $dict='') {
			if ( empty($words) ) {
				return false;
			}
			$this->checkToken();
			$api = $this->_configs['DICT_APPEND'];
			$dict = $this->setParseDict($dict);

			$params = array(
				'id' => $dict,
				'words' => implode(',', $words),
			);
			$headers = array(
				// 'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_post($api, $params, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('append dict 接口日志', array('params'=>$params,'ret'=>$ret), 'lite');
				if ( $ret['status']==true ) {
					$result = intval($ret['count']);
				}
			} else {
				debug('append dict 接口异常', $ret, 'lite');
			}
			return $result;
		}

		/**
		 * 从字典中批量删除词条
		 * @param $words array 指定要删除的词条
		 * @param $dict string 指定字典 默认使用 dict_tags
		 * @return mixed 成功返回 字典中的总词条数量 失败返回 false
		 */
		public function removeDictWords($words=array(), $dict='') {
			if ( empty($words) ) {
				return false;
			}
			$this->checkToken();
			$api = $this->_configs['DICT_REMOVE'];
			$dict = $this->setParseDict($dict);

			$params = array(
				'id' => $dict,
				'words' => implode(',', $words),
			);
			$headers = array(
				//'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_post($api, $params, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('remove dict 接口日志', $ret, 'lite');
				if ( $ret['status']==true ) {
					$result = intval($ret['count']);
				}
			} else {
				debug('remove dict 接口异常', $ret, 'lite');
			}
			return $result;
		}

		/**
		 * 验证字典是否存在指定词条
		 * @param $word array 指定要验证的词条
		 * @param $dict string 指定字典 默认使用 dict_tags
		 * @return mixed 成功表示存在返回 true 失败表示不存在返回 false
		 */
		public function existsDictWord($word='', $dict='') {
			if ( $word=='' ) {
				return false;
			}
			$this->checkToken();
			$api = $this->_configs['DICT_EXISTS'];
			$dict = $this->setParseDict($dict);

			$params = array(
				'id' => $dict,
				'words' => implode(',', $words),
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_get($api, $params, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('exists dict 接口日志', $ret, 'lite');
				if ( $ret['status']==true ) {
					$exists = count($ret['exists']['words']);
					$result = $exists > 0 ? true : false;
				}
			} else {
				debug('exists dict 接口异常', $ret, 'lite');
			}
			return $result;
		}

		/**
		 * 获取字典中词条数量
		 * @param $dict string 指定字典 默认使用 dict_tags
		 * @return mixed 成功返回 int 词条数量 失败返回 false
		 */
		public function countDictWord($dict='') {
			$this->checkToken();
			$api = $this->_configs['DICT_COUNT'];
			$dict = $this->setParseDict($dict);

			$params = array(
				'id' => $dict,
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_get($api, $params, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('count dict 接口日志', $ret, 'lite');
				if ( $ret['status']==true ) {
					$result = intval($ret['count']);
				}
			} else {
				debug('count dict 接口异常', $ret, 'lite');
			}
			return $result;
		}

		/**
		 * 获取字典中所有的标签词条
		 * @param $dict string 分词的业务字典代码，默认为空使用乐居标签字典
		 * @return mixed 成功返回 array 失败返回 false
		 */
		protected function setParseDict($dict) {
			$allowed = &$this->_configs['PARSE_DICTS'];
			if ( !in_array($dict, $allowed) ) {
				$dict = ( $dict=='' ) ? $this->_configs['PARSETAGS_ID'] : $dict;
			}

			return $dict;
		}
	/* -------------- 字典和分析类操作 结束 -------------- */

	/* -------------- 搜索查询类操作 -------------- */
		/**
		 * 单业务高级搜索功能接口
		 * @param $business string 要查询的业务
		 * @param $page int 指定页码
		 * @param $pagesize int 指定每页数据量
		 * @param $ds int 是否使用精确查询 0只查_title 1查_title和_content 2查_content
		 * @param $fields array 要返回的字段
		 * @param $prefix array 前缀查询条件
		 * @param $order array 结果集合排序规则
		 * @param $opts array 查询条件
		 * @param $keyword string 为关键词
		 * @return array 返回的数据结果集合
		 */
		public function select(
			$page=1, $pagesize=10,
			$keyword='',
			$opts=array(), $prefix=array(),
			$order=array('_doccreatetime', 'desc'),
			$fields=array(), $ds=0, $business='knowledge'
		) {
			$this->checkToken();
			$api = $this->_configs['MULTI_API'];

			$page = intval($page);
			$order = implode('@@', $order);
			$opts = $this->parseWhere($opts);
			$prefix = $this->parseWhere($prefix);
			$fields = implode(',', $fields);

			$dss = array(
				0 => '_title',
				1 => '_title,_content',
				2 => '_content',
			);
			$ds = array_key_exists($ds, $dss) ? $ds : 0;

			$params = array(
				'b' => $business, // 要查询的业务
				'p' => $page, // 指定页码
				'ps' => $pagesize, // 指定每页数据量
				'ds' => $dss[$ds], // 是否使用精确查询 0为只在_title中查询关键词 1则在_title与_content中查询关键词 2在_content中搜索
				'sf' => $fields, // 要返回的字段
				'so' => $order, // 结果集合排序规则
				'f' => $opts, // 查询条件
				'prefix' => $prefix, // 前缀查询
				'k' => $keyword, // 为关键词 可以为空
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);
			$ret = curl_get($api, $params, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				// debug('mutil 接口日志', $ret, 'lite');
				$result = array(
					'pager' => $ret['pager'],
					'list' => $ret['result'],
				);
			} else {
				// debug('mutil 接口异常', $ret, 'lite');
			}
			// print_r($api);
			// print_r($params);
			// print_r($headers);
			// print_r($ret);
			return $result;
		}

		/**
		 * 以关键字进行搜索的功能方法
		 * @param $business string 要查询的业务
		 * @param $page int 指定页码
		 * @param $pagesize int 指定每页数据量
		 * @param $ds int 是否使用精确查询 0只查_title 1查_title和_content
		 * @param $fields array 要返回的字段
		 * @param $prefix array 前缀查询条件
		 * @param $opts array 查询条件
		 * @param $keyword string 为关键词
		 * @return array 返回的数据结果集合
		 */
		public function search(
			$page=1, $pagesize=10,
			$keyword='',
			$opts=array(), $prefix=array(),
			$fileds=array(), $ds=0, $business=array('knowledge')
		) {
			$this->checkToken();
			$api = $this->_configs['SEARCH_API'];

			$page = intval($page);
			$opts = $this->parseWhere($opts);
			$prefix = $this->parseWhere($prefix);
			$fields = implode(',', $fields);

			$params = array(
				'b' => implode(',', $business), // 要查询的业务
				'p' => $page, // 指定页码
				'ps' => $pagesize, // 指定每页数据量
				'ds' => $ds, // 是否使用精确查询 0为只在_title中查询关键词 1则在_title与_content中查询关键词
				'sf' => $fields, // 要返回的字段
				'f' => $opts, // 查询条件
				'prefix' => $prefix, // 前缀查询
				'k' => $keyword, // 为关键词 可以为空
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);
			$ret = curl_get($api, $params, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('search 接口日志', $ret, 'lite');
				$result = array(
					'pager' => $ret['pager'],
					'list' => $ret['result'],
				);
			} else {
				debug('search 接口异常', $ret, 'lite');
			}
			return $result;
		}

		/**
		 * 分析查询条件，并返回查询条件字符串
		 * [from,to]@@_id => $opts = array(array('[1000,1005]', '_id'))
		 */
		protected function parseWhere($opts=array()) {
			$result = array();
			if ( empty($opts) ) {
				return '';
			}
			foreach ( $opts as $i => $opt ) {
				$result[] = $item = implode('@@', $opt);
			}
			return implode('<>', $result);
		}

		/**
		 * 联想词查询
		 * @param $word string 待前缀匹配的字符串 不能为空
		 * @param $limit int 返回数据个数限制
		 * @return mixed 成功返回数据集合 失败返回 false
		 */
		public function suggest ($word='', $limit=5) {
			if ( empty($word) ) {
				return false;
			}
			$this->checkToken();
			$api = $this->_configs['SUGGEST_API'];
			$business = 'lejutag';
			$params = array(
				'k' => $word, // 为关键词 不能为空
				'b' => $business, // 要查询的业务 默认为 lejutag
				's' => $limit,	// 返回的数据个数
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_get($api, $params, $headers);
			if ( $ret['status'] ) {
				$result = json_decode($ret['result'], true);
			}
			return $result;
		}
	/* -------------- 搜索查询类操作 结束 -------------- */


	/* -------------- 文档管理类操作 -------------- */

		/**
		 * 批量创建文档
		 */
		public function createKnowledge( $list=array() ) {
			$records = array();
			foreach ( $list as $i => &$item ) {
				$record = $this->knowledgeDocConvert($item, true);
				if ( !$record ) {
					continue;
				}
				array_push($records, $record);
			}
			return $this->_doc($records, 'knowledge', '', 'create');
		}

		/**
		 * 批量修改文档
		 * 单条是报fail
		 */
		public function updateKnowledge( $list=array() ) {
			$records = array();
			foreach ( $list as $i => &$item ) {
				$record = $this->knowledgeDocConvert($item, false);
				if ( !$record ) {
					continue;
				}
				array_push($records, $record);
			}
			return $this->_doc($records, 'knowledge', '', 'update');
		}

		/**
		 * 批量删除文档
		 */
		public function removeKnowledge( $list=array() ) {
			$doc = array();
			foreach ( $list as $i => &$id ) {
				$id = $this->validDocID($id);
				if ( !$id ) {
					continue;
				}
				array_push($doc, array('_id'=>$id));
			}
			return $this->_doc($doc, 'knowledge', '', 'remove');
		}

		/**
		 * 验证业务中的文档编号
		 * 文档编号与数据表主键相同，使用数字编号，但对接口操作时，须将数值编号转换为字符串类型操作
		 * @param $pkid int 业务主键
		 * @return mixed 返回文档编号，验证失败时返回 false
		 */
		protected function validDocID ( $pkid ) {
			$pkid = intval($pkid);
			if ( $pkid <= 0 ) {
				return false;
			}
			return strval($pkid);
		}

		// 仅限在了解文档结构时使用
		public function create( $doc=array(), $index='', $type='' ) {
			return $this->_doc($doc, $index, $type, 'create');
		}
		// 仅限在了解文档结构时使用
		public function update( $doc=array(), $index='', $type='' ) {
			return $this->_doc($doc, $index, $type, 'update');
		}
		// 仅限在了解文档结构时使用
		public function remove( $doc=array(), $index='', $type='' ) {
			return $this->_doc($doc, $index, $type, 'remove');
		}

		// index 命名 业务直接写业务号 knowledge 联想词使用全名 suggest.lejutag
		protected function _doc( $doc=array(), $index='', $type='', $method='create' ) {
			if ( !$doc ) {
				debug('文档接口 主键或文档内容缺失');
				return false;
			}
			$this->checkToken();

			// 验证操作是否允许
			$method = trim(strtolower($method));
			$methods = array(
				'create' => 'CUSTOM_CREATE',
				'update' => 'CUSTOM_UPDATE',
				'remove' => 'CUSTOM_REMOVE',
			);
			if ( !array_key_exists($method, $methods) ) {
				debug('文档接口 操作方法 错误');
				return false;
			}
			// 验证业务索引是否允许
			$allowed = array(
				'knowledge' => array(),
				'question' => array(),
				'wiki' => array(),
				'suggest.lejutag' => array('word'),
				'suggest.wiki' => array('word'),
			);
			$index = trim(strtolower($index));
			if ( !array_key_exists($index, $allowed) ) {
				debug('文档接口 Indeces 索引 错误');
				return false;
			}
			// 验证业务类型是否允许
			$type = trim($type);
			if ( $type=='' ) {
				$type = $index;
			} else if ( !in_array($type, $allowed[$index]) ) {
				debug('文档接口 Type 类型 错误');
				return false;
			} // in_array allowd

			// 访问接口
			$api = $this->_configs[$methods[$method]];
			$data = array(
				'index' => $index,
				'type' => $type,
				'doc' => json_encode($doc),
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_post($api, $data, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('doc 文档接口日志', $ret, 'lite');
				$result = $ret['status'];
			} else {
				debug('doc 文档接口异常', $ret, 'lite');
			}
			return $result;
		}

		/**
		 * 知识内容文档转换
		 * @param $record array 数据库记录数组
		 * @param $create bool 是否是创建数据 true 为创建新数据文档 false 为更新或删除文档
		 * @return array 保存文档时的数据结构
		 */
		public function knowledgeDocConvert( $record=array(), $create=false, $batch=false ) {
			if ( empty($record) || !is_array($record) ) {
				debug('请传入要转换文档的数据记录');
				return false;
			}
			$must_fields = array(
				'scope'=>'','title'=>'','content'=>'','tags'=>'','status'=>'',
				'ctime'=>'','cateid'=>'','top_time'=>'','rcmd_time'=>'','src_type'=>'',
				'catepath'=>'', 'id'=>'',
			);
			// 扩展
			$remove_fields = array(
				'title_firstletter'=>'', 'title_pinyin'=>'', 'url'=>'',
			);
			// 必传字段
			$must_fields = array_merge($must_fields, $remove_fields);
			$tmp = array_intersect_key($record, $must_fields);
			if ( $create ) {
				// 创建文档时，需将必传数据传入
				if ( count($tmp)!=count($must_fields) ) {
					debug('请填写必填字段,包括('.implode(',', array_keys($must_fields)).')');
					return false;
				}
			}

			// 批量操作时，忽略 id 或 _id
			if ( !$batch ) {
				// 更新或删除时，不验证完整数据结构，部份更新
				// 但 id 作为操作文档的标识必须存在
				if ( !isset($record['id']) ) {
					debug('请指定主键参数');
					return false;
				} else {
					// 验证文档编号
					$_id = $this->validDocID($record['id']);
					if ( !$_id ) {
						return false;
					}
				}
			} else {
				$_id = false;
			}


			$result = array();
			$_multi = array();

			if ( isset($record['scope']) ) {
				// 城市信息使用 city_en 与 _ 标识
				$cities = C('CITIES.ALL');
				$cities['_'] = array(
					'l' => '_',
					'en' => 'all',
					'cn' => '全国',
					'py' => 'all',
				);
				if ( !array_key_exists($record['scope'], $cities) ) {
					debug('没有指定的城市');
					return false;
				}
				$result['_scope'] = $cities[$record['scope']]['cn'];
			}

			$create && $result['_hits'] = 0;
			$create && $result['_category'] = '知识';
			$_id && $result['_id'] = $_id;
			isset($record['title']) && $result['_title'] = $record['title'];
			isset($record['content']) && $result['_content'] = $record['content'];
			isset($record['tags']) && $result['_tags'] = $record['tags'];
			isset($record['status']) && $result['_deleted'] = $record['status']==9 ? false : true;
			isset($record['ctime']) && $result['_doccreatetime'] = intval($record['ctime'])*1000;
			$ptime = isset($record['ptime']) ? intval($record['ptime']) : NOW_TIME;
			$result['_docupdatetime'] = $ptime*1000;
			isset($record['cateid']) && $_multi['cateid'] = $record['cateid'];
			isset($record['catepath']) && $_multi['catepath'] = $record['catepath'];
			isset($record['top_time']) && $_multi['top_time'] = $record['top_time'];
			isset($record['rcmd_time']) && $_multi['rcmd_time'] = $record['rcmd_time'];
			isset($record['src_type']) && $_multi['src_type'] = $record['src_type'];
			isset($record['editor']) && $_multi['editor'] = $record['editor'];
			isset($record['title']) && $_multi['title_prefix'] = $record['title'];
			isset($record['title_firstletter']) && $_multi['title_firstletter'] = $record['title_firstletter'];
			isset($record['title_pinyin']) && $_multi['title_pinyin'] = $record['title_pinyin'];
			isset($record['url']) && $result['_url'] = $record['url'];
			!empty($_multi) && $result['_multi'] = $_multi;
			foreach ( $remove_fields as $field => $item ) {
				if ( isset($record[$field]) ) {
					unset($record[$field]);
				}
			}
			$result['_origin'] = $record;

			return $result;
		}

		/**
		 * 百科内容文档转换
		 * @param $record array 数据库记录数组
		 * @param $create bool 是否是创建数据 true 为创建新数据文档 false 为更新或删除文档
		 * @return array 保存文档时的数据结构
		 */
		public function wikiDocConvert( $record=array(), $create=false, $batch=false ) {
			if ( empty($record) || !is_array($record) ) {
				debug('请传入要转换文档的数据记录');
				return false;
			}
			$must_fields = array(
				'scope'=>'','title'=>'','content'=>'','tags'=>'','status'=>'',
				'ctime'=>'','cateid'=>'','src_type'=>'',
				'focus_time'=>'','celebrity_time'=>'','company_time'=>'',
				'id'=>'','editor'=>'','editorid'=>'',
				'firstletter'=>'', 'pinyin'=>'',
			);
			// 扩展
			$remove_fields = array(
				'url'=>'',
			);
			// 必传字段
			$must_fields = array_merge($must_fields, $remove_fields);
			$tmp = array_intersect_key($record, $must_fields);
			if ( $create ) {
				// 创建文档时，需将必传数据传入
				if ( count($tmp)!=count($must_fields) ) {
					debug('请填写必填字段,包括('.implode(',', array_keys($must_fields)).')');
					return false;
				}
			}

			// 批量操作时，忽略 id 或 _id
			if ( !$batch ) {
				// 更新或删除时，不验证完整数据结构，部份更新
				// 但 id 作为操作文档的标识必须存在
				if ( !isset($record['id']) ) {
					debug('请指定主键参数');
					return false;
				} else {
					// 验证文档编号
					$_id = $this->validDocID($record['id']);
					if ( !$_id ) {
						return false;
					}
				}
			} else {
				$_id = false;
			}


			$result = array();
			$_multi = array();

			$result['_scope'] = '全部';
			$create && $result['_hits'] = 0;
			$create && $result['_category'] = '百科词条';
			$_id && $result['_id'] = $_id;
			isset($record['title']) && $result['_title'] = $record['title'];
			isset($record['content']) && $result['_content'] = $record['content'];
			isset($record['tags']) && $result['_tags'] = $record['tags'];
			isset($record['status']) && $result['_deleted'] = $record['status']!=9 ? true : false;
			isset($record['ctime']) && $result['_doccreatetime'] = intval($record['ctime'])*1000;
			$ptime = isset($record['ptime']) ? intval($record['ptime']) : NOW_TIME;
			$result['_docupdatetime'] = $ptime*1000;
			isset($record['cateid']) && $_multi['cateid'] = $record['cateid'];
			isset($record['editorid']) && $_multi['editorid'] = $record['editorid'];
			isset($record['focus_time']) && $_multi['focus_time'] = $record['focus_time'];
			isset($record['celebrity_time']) && $_multi['celebrity_time'] = $record['celebrity_time'];
			isset($record['company_time']) && $_multi['company_time'] = $record['company_time'];
			isset($record['src_type']) && $_multi['src_type'] = $record['src_type'];
			isset($record['editor']) && $_multi['editor'] = $record['editor'];
			isset($record['title']) && $_multi['title_prefix'] = $record['title'];
			isset($record['firstletter']) && $_multi['title_firstletter'] = strtoupper($record['firstletter']);
			isset($record['pinyin']) && $_multi['title_pinyin'] = $record['pinyin'];
			isset($record['url']) && $result['_url'] = $record['url'];
			!empty($_multi) && $result['_multi'] = $_multi;
			// 清理多余字段
			foreach ( $remove_fields as $field => $item ) {
				if ( isset($record[$field]) ) {
					unset($record[$field]);
				}
			}
			$result['_origin'] = $record;

			return $result;
		}

		/**
		 * 按条件更新文档
		 * @param $opts array 条件参数
		 * @param $doc array 更新记录的部份内容
		 * @param $index string 指定操作业务
		 * @return bool 成功返回 true 失败返回 false
		 */
		public function batchesUpdate( $opts=array(), $prefix=array(), $doc=array(), $index='knowledge' ) {
			if ( empty($opts) || empty($doc) ) {
				return false;
			}
			$this->checkToken();
			// 验证业务索引是否允许
			$allowed = array(
				'knowledge' => array(),
				'question' => array(),
				'wiki' => array(),
			);
			$index = trim(strtolower($index));
			if ( !array_key_exists($index, $allowed) ) {
				debug('文档接口 Indeces 索引 错误');
				return false;
			}
			$type = '';

			// 访问接口
			$api = $this->_configs['BATCHES_UPDATE'];
			$opts = $this->parseWhere($opts);
			$doc = $this->knowledgeDocConvert($doc, false, true);

			$data = array(
				'index' => $index,
				'type' => $type,
				'f' => $opts,	// 查询条件
				'prefix' => $prefix, // 前缀查询
				'doc' => json_encode($doc),
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);

			$result = false;
			// var_dump('updatebyquery', $api, $opts, $doc, $data, $headers); exit;
			$ret = curl_post($api, $data, $headers);
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('updatebyquery 接口日志', $ret, 'lite');
				$result = $ret['status'];
			} else {
				debug('updatebyquery 接口异常', $ret, 'lite');
			}
			return $result;
		}

		/**
		 * 按条件删除文档
		 * @param $opts array 条件参数
		 * @param $index string 指定操作业务
		 * @return bool 成功返回 true 失败返回 false
		 */
		public function batchesRemove( $opts=array(), $prefix=array(), $index='knowledge' ) {
			if ( empty($opts) && empty($prefix) ) {
				return false;
			}
			$this->checkToken();
			// 验证业务索引是否允许
			$allowed = array(
				'knowledge' => array(),
				'question' => array(),
				'wiki' => array(),
			);
			$index = trim(strtolower($index));
			if ( !array_key_exists($index, $allowed) ) {
				debug('文档接口 Indeces 索引 错误');
				return false;
			}
			$type = '';

			// 访问接口
			$api = $this->_configs['BATCHES_REMOVE'];
			$opts = $this->parseWhere($opts);
			$prefix = $this->parseWhere($prefix);
			$data = array(
				'index' => $index,
				'type' => $type,
				'f' => $opts,
				'prefix' => $prefix, // 前缀查询
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);

			$result = false;
			$results = &$this->_status;
			$ret = curl_post($api, $data, $headers);
			// var_dump('removebyquery', $api, $opts, $data, $headers, $ret); exit;
			if ( $ret['status'] ) {
				$ret = json_decode($ret['result'], true);
				debug('removebyquery 接口日志', $ret, 'lite');
				$result = $ret['status'];
			} else {
				debug('removebyquery 接口异常', $ret, 'lite');
			}
			return $result;
		}
		/**
		 * 批量创建文档
		 */
		public function createWiki( $list=array() ) {
			$records = array();
			foreach ( $list as $i => &$item ) {
				$record = $this->wikiDocConvert($item, true);
				if ( !$record ) {
					continue;
				}
				array_push($records, $record);
			}
			return $this->_doc($records, 'wiki', '', 'create');
		}
		/**
		 * 批量修改文档
		 * 单条是报fail
		 */
		public function updateWiki( $list=array() ) {
			$records = array();
			foreach ( $list as $i => &$item ) {
				$record = $this->wikiDocConvert($item, false);
				if ( !$record ) {
					continue;
				}
				array_push($records, $record);
			}
			return $this->_doc($records, 'wiki', '', 'update');
		}
		/**
		 * 批量删除文档
		 */
		public function removeWiki( $list=array() ) {
			$doc = array();
			foreach ( $list as $i => &$id ) {
				$id = $this->validDocID($id);
				if ( !$id ) {
					continue;
				}
				array_push($doc, array('_id'=>$id));
			}
			return $this->_doc($doc, 'wiki', '', 'remove');
		}

		/**
		 * 随机返回指定条件范围的知识列表
		 *
		 */
		public function getRecommendBaike ( $total=5, $prefix=array(), $opts=array(), $fields=array(), $model='random' ) {
			$total = intval($total);
			$total = ( $total <= 0 ) ? 5 : $total;
			$total = ( $total > 100 ) ? 100 : $total;

			$models = array('random');
			if ( !in_array($model, $models) ) {
				$model = 'random';
			}
			$this->checkToken();
			$api = $this->_configs['RECOMMEND_'.strtoupper($model)];
			$business = 'knowledge';
			$opts = $this->parseWhere($opts);
			$prefix = $this->parseWhere($prefix);
			$fields = empty($fields) ? array('_id','_tags','_title','_multi.catepath','_origin.scope') : $fields;

			$params = array(
				'ps' => $total, // 为关键词 不能为空
				'b' => $business, // 要查询的业务 默认为 knowledge
				'sf' => implode(',', $fields),	// 返回的数据个数
				'f' => $opts, // 查询条件
				'prefix' => $prefix, // 前缀查询
			);
			$headers = array(
				'Authorization: '.$this->_token,
			);

			$result = false;
			$ret = curl_get($api, $params, $headers);
			if ( $ret['status'] ) {
				$result = json_decode($ret['result'], true);
			}
			// var_dump($api, $params, $headers, $ret);
			return $result;			
		}
	/* -------------- 文档管理类操作 结束 -------------- */


	/* -------------- 寰宇 提供的接口操作 -------------- */
		/**
		 * 根据正文内容查询出现的词条
		 * api from 寰宇
		 */
		public function cutWord($content, $n = 0) {
			$data = array();
			$content && $data['c'] = strip_tags($content);
			$n && $data['n'] = intval($n);

			$api = curl_post(C('DATA_TRANSFER_API_URL').'api/item/_analyze', http_build_query($data));
			$result = json_decode($api['result'], true);
			if(!empty($result))
			{
				return $result;
			}
			return false;
		}

		/**
		 * 为词条&百科正文内容中的词库词加超链接
		 */
		public function renderingContent($content, $n=0, $plant=null, $type='touch') {
			$types = array('touch', 'pc');
			if ( !in_array($type, $types) ) {
				$type = 'touch';
			}
			if($plant === 'b')
			{
				$cutwords = $this->analyze(strip_tags($content), true, 10, 'dict_wiki');
				foreach($cutwords as $k=>$v)
				{
					$cutwords[$k]['name'] = $cutwords[$k]['id'] = $v['word'];
				}
			}
			else
			{
				$cutwords = $this->cutWord($content, $n);
			}

			if($cutwords)
			{
				//排除字母数字组合的关键词
				/*
				foreach($cutwords as $k=>$v)
				{
					if(preg_match('/[0-9a-zA-Z]+/u', $v['name']))
					{
						unset($cutwords[$k]);
					}
				}*/

				//1.排序
				$cutwords = array_values($cutwords);
				//冒泡排序,长词排前面,短词排后面
				for ($i = 0, $k = count($cutwords); $i < $k; $i++) {
					for ($j = $i + 1; $j < $k; $j++) {
						if (strlen($cutwords[$i]['name']) < strlen($cutwords[$j]['name'])) {
							$temp = $cutwords[$j];
							$cutwords[$j] = $cutwords[$i];
							$cutwords[$i] = $temp;
						}
					}
				}

				//2.将关键词替换为带超链接形式
				$md5_dict = C('MD5_DICT');
				$temp_word = $temp_word2 = array();
				foreach($cutwords as $v)
				{
					$md5 = strtr('__'.md5($v['name'])."__",$md5_dict);
					$temp_word[$v['name']] = $md5;
					$temp_word2[$md5] = "<a href='".url('show', array('word'=>base64_encode($v['id'])), $type, 'wiki')."'>{$v['name']}</a>";
				}

				//3.被此标签包住的内容不替换
				$temp_unreplaced = array();
				preg_match_all('/<(sectiontitle|a)[^>]*>(.*)<\/(sectiontitle|a)>/iU', $content, $un);
				$un = array_values(array_unique($un[0]));
				foreach($un as $u)
				{
					$md5 = strtr('##'.md5($u)."##",$md5_dict);
					$temp_unreplaced[$md5] = $u;
					$content = str_replace($u, $md5, $content);
				}

				//4.将所有标签替换为临时md5
				$temp_tags = $temp_tags2 = array();
				preg_match_all('/<\/?[a-z]+[^>]*>/i', $content, $tags);
				$tg = array_values(array_unique($tags[0]));
				foreach($tg as $t)
				{
					$md5 = strtr('**'.md5($t).'**',$md5_dict);
					$temp_tags[$t] = $md5;
					$temp_tags2[$md5] = $t;
				}
				$content = strtr($content,$temp_tags);

				//5.替换关键词为md5
				foreach($temp_word as $k=>$v)
				{
					$k = addslashes($k);
					$content = preg_replace("/$k/", $v, $content, 1);
				}

				//6.替换关键词md5为超链接
				$content = strtr($content,$temp_word2);

				//7.恢复所有标签
				$content = strtr($content,$temp_tags2);

				//8.恢复所有不替换的标签
				$content = strtr($content, $temp_unreplaced);
			}

			return $content;
		}
	/* -------------- 寰宇 提供的接口操作 结束 -------------- */


	/**
	 * 为优化 百度SEO 提供接口，主动将发布数据推送给 Baidu
	 * @requirement by : 闫学坤
	 * @reference : http://zhanzhang.baidu.com/linksubmit/index?site=http%3A%2F%2Fbaike.leju.com%2F
	 */
	public function pushToBaidu( $urls=array() ) {
		if ( empty($urls) ) {
			return false;
		}
		$api = C('BAIDU_PUSH');
		$headers = array('Content-Type: text/plain');
		$result = curl_post($api, implode(PHP_EOL, $urls), $headers);
		return $result;
	}
}