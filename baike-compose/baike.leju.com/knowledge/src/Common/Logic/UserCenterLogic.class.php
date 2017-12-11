<?php
/**
 * 会员中心相关业务逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class UserCenterLogic {
	static $data = array();
	protected $_deploy = 'dev';
	protected $_error = false;		// 保存最后一次异常错误信息
	protected $_data = false;		// 保存最后一次操作结果
	protected $_result = false;		// 保存最后一次操作结果状态
	protected $_cacher = null;
	protected $_configs = [];

	protected $_source = 0; 		// 数据的业务来源 1 表示公司, 2 表示人物

	const UC_SOURCE_LD = 1;			// 数据的业务来源 1 表示公司
	const UC_SOURCE_PN = 2;			// 数据的业务来源 2 表示人物

	/*
	// const LD_FAV_COMPANY = 61;
	// const LD_UNFAV_COMPANY = 62;
	const LD_FAV_WIKI = 61;
	const LD_UNFAV_WIKI = 62;
	const LD_FAV_USER = 71;
	const LD_UNFAV_USER = 72;
	const LD_FAV_QUESTION = 31;
	const LD_UNFAV_QUESTION = 32;
	const LD_FAV_ANSWER = 41;
	const LD_UNFAV_ANSWER = 42;
	const LD_GOOD_ANSWER = 51;
	const LD_UNGOOD_ANSWER = 52;

	const LD_ADD_QUESTION = 11;
	const LD_DEL_QUESTION = 12;
	const LD_ADD_ANSWER = 21;
	const LD_DEL_ANSWER = 22;
	*/

	public function __construct() {
		$this->_source = self::UC_SOURCE_LD;	// 默认表示当前数据来源为公司问答
		$this->_cacher = S(C('REDIS'));
		$this->_configs['LD'] = [
			'api'=>'http://my.leju.com/api/wenda/WendaCollect',	// 基本操作
			'exp'=>'http://my.leju.com/api/wenda/delWendaCollect',	// 物理删除
			// 'batch'=>'http://my.leju.com/api/user/userinfo2',	// 批量获取用户信息的接口
			'batch'=>'http://udc.leju.com/api/user/userinfoLj',	// 批量获取用户信息的接口
			'get'=>'http://my.leju.com/api/wenda/getWenda', // 查询状态接口
			'dns'=>'my.leju.com',
			'dev'=>'10.207.0.186',
			'key'=>'9a48969246d4b56a8351ac870dcd69b2',
		];
		if ( defined('APP_DEPLOY') ) {
			$this->setDeploy(APP_DEPLOY);
		}
	}

	// 会员中心提供的相关数据接口
	/**
	 * 1.1 用户提问问题的接口
	 */
	public function DoQuestion( $userid, $question_id ) {
		$params = [
			'uid'=>$userid,
			'qid'=>$question_id,
			'source'=>$this->_source,
			'type'=>1,
			'operate'=>1,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}
	/**
	 * 1.2 取消用户提问问题的接口
	 */
	public function UndoQuestion( $userid, $question_id ) {
		$params = [
			'uid'=>$userid,
			'qid'=>$question_id,
			'source'=>$this->_source,
			'type'=>1,
			'operate'=>2,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}

	/**
	 * 2.1 用户回答问题的接口
	 */
	public function DoAnswer( $userid, $question_id, $answer_id ) {
		$params = [
			'uid'=>$userid,
			'qid'=>$question_id,
			'aid'=>$answer_id,
			'type'=>2,
			'source'=>$this->_source,
			'operate'=>1,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}
	/**
	 * 2.2 取消用户回答问题的接口
	 */
	public function UndoAnswer( $userid, $question_id, $answer_id ) {
		$params = [
			'uid'=>$userid,
			'qid'=>$question_id,
			'aid'=>$answer_id,
			'type'=>2,
			'source'=>$this->_source,
			'operate'=>2,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}

	/**
	 * 3.1 关注问题的接口
	 */
	public function AttentionQuestion( $userid, $question_id ) {
		$params = [
			'uid'=>$userid,
			'qid'=>$question_id,
			'type'=>3,
			'source'=>$this->_source,
			'operate'=>1,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}
	/**
	 * 3.2 取消关注问题的接口
	 */
	public function UnAttentionQuestion( $userid, $question_id ) {
		$params = [
			'uid'=>$userid,
			'qid'=>$question_id,
			'type'=>3,
			'source'=>$this->_source,
			'operate'=>2,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}

	/**
	 * 4.1 关注回答的接口
	 */
	public function AttentionAnswer( $userid, $answer_id ) {
		$params = [
			'uid'=>$userid,
			'aid'=>$answer_id,
			'type'=>4,
			'source'=>$this->_source,
			'operate'=>1,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}
	/**
	 * 4.2 取消关注回答的接口
	 */
	public function UnAttentionAnswer( $userid, $answer_id ) {
		$params = [
			'uid'=>$userid,
			'aid'=>$answer_id,
			'type'=>4,
			'source'=>$this->_source,
			'operate'=>2,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}

	/**
	 * 5.1 回答点赞的接口
	 */
	public function GoodAnswer( $userid, $answer_id ) {
		$params = [
			'uid'=>$userid,
			'aid'=>$answer_id,
			'type'=>5,
			'source'=>$this->_source,
			'operate'=>1,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}
	/**
	 * 5.2 取消回答点赞的接口
	 */
	public function UnGoodAnswer( $userid, $answer_id ) {
		$params = [
			'uid'=>$userid,
			'aid'=>$answer_id,
			'type'=>5,
			'source'=>$this->_source,
			'operate'=>2,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}

	/**
	 * 6.1 关注公司的接口
	 * @TODO
	 * AttentionCompany => AttentionWiki
	 */
	public function AttentionWiki( $userid, $wiki_id, $title ) {
		$params = [
			'uid'=>$userid,
			'cid'=>$wiki_id,
			'cname'=>$title,
			'type'=>6,
			'source'=>$this->_source,
			'operate'=>1,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}
	/**
	 * 6.2 取消关注公司的接口
	 * UnAttentionCompany => UnAttentionWiki
	 */
	public function UnAttentionWiki( $userid, $wiki_id, $title ) {
		$params = [
			'uid'=>$userid,
			'cid'=>$wiki_id,
			'cname'=>$title,
			'type'=>6,
			'source'=>$this->_source,
			'operate'=>2,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}

	/**
	 * 7.1 关注用户的接口
	 */
	public function AttentionUser( $userid, $fansid ) {
		$params = [
			'uid'=>$userid,
			'fansid'=>$fansid,
			'type'=>7,
			'operate'=>1,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}
	/**
	 * 7.2 取消关注用户的接口
	 */
	public function UnAttentionUser( $userid, $fansid ) {
		$params = [
			'uid'=>$userid,
			'fansid'=>$fansid,
			'type'=>7,
			'operate'=>2,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['api'], $params, [], 'get');
		return !!$ret;
	}

	/**
	 * exp 物理删除问题和回答的接口
	 */
	public function RemoveAsk($userid, $question_id, $answer_id, $type='question') {
		$types = ['question'=>1, 'answer'=>2];
		$type = strtolower($type);
		if ( !array_key_exists($type, $types) ) {
			$this->Reset();
			$this->_error = ['msg'=>'请指定操作类型'];
			return false;
		}
		$params = [
			'uid'=>$userid,
			'qid'=>$question_id,
			'aid'=>$answer_id,
			'type'=>$types[$type],
			'source'=>$this->_source,
		];
		$config = &$this->_configs['LD'];
		$ret = $this->sendLDRequest($config['exp'], $params, [], 'get');
		return !!$ret;
	}

	/**
	 * 批量获取用户基本信息
	 */
	public function SearchUserinfo($users=[]) {
		if ( empty($users) ) {
			$this->Reset();
			$this->_result = false;
			$this->_error = ['msg'=>'请指定要查询的用户编号'];
			$this->_data = [];
			return false;
		}
		$uids = implode(',', $users);
		$code = $this->__usercenter_encode($uids);
		$params = [
			'uid'=>$code,
		];
		$config = &$this->_configs['LD'];
		$api = $config['batch'];
		$headers = [];
		$params['sign'] = $this->__usercenter_sign($params);
		if ( $this->_deploy == 'dev' ) {
			$api = str_replace('udc.leju.com', '10.207.0.186', $api);
			$headers[] = 'Host: '.$config['dns'];
		}
		$ret = curl_get($api, $params, $headers);
		$dbg = [
			'method' => 'GET',
			'api' => $api,
			'params' => $params,
			'headers' => $headers,
			'return' => $ret,
		];
		debug('会员中心接口(批量获取用户基本信息)调用', $dbg, false, true);
		// echo PHP_EOL, '<pre>', PHP_EOL;
		// var_dump(['===debug===', 'api'=>$api, 'params'=>$params, 'headers'=>$headers, 'ret'=>$ret]);
		// echo PHP_EOL, '</pre>', PHP_EOL;
		if ( $ret['status'] ) {
			$result = json_decode($ret['result'], true);
			if ( $result['code']!=0 ) {
				$this->_error = $result;
			} else {
				$this->_data = $result;
				$this->_result = true;
			}
			$result = true;
		} else {
			$this->_error = $ret;
			$result = false;
		}
		return !!$ret;
	}

	/**
	 * 查询指定用户对指定公司的关注状态
	 */
	public function UserWikiIsFocus($userid, $wiki_id) {
		$userid = intval($userid);
		$wiki_id = intval($wiki_id);
		if ( $userid==0 || $wiki_id==0 ) { return false; }
		$config = &$this->_configs['LD'];
		$api = $config['get'];
		$headers = [];
		$params = [];
		$params['uid'] = $userid;
		$params['cid'] = $wiki_id;
		$params['type'] = 6;
		$params['source'] = $this->_source;
		$params['sign'] = $this->__usercenter_sign($params);
		if ( $this->_deploy == 'dev' ) {
			$api = str_replace($config['dns'], $config['dev'], $api);
			$headers[] = 'Host: '.$config['dns'];
		}
		$ret = curl_get($api, $params, $headers);
		$dbg = [
			'method' => 'GET',
			'api' => $api,
			'params' => $params,
			'headers' => $headers,
			'return' => $ret,
		];
		debug('会员中心接口(查询指定用户对指定公司的关注状态)调用', $dbg, false, true);
		if ( $ret['status'] ) {
			$result = json_decode($ret['result'], true);
			if ( $result['code']!=0 ) {
				$this->_error = $result;
			} else {
				$this->_data = $result;
				$this->_result = true;
			}
			$result = true;
		} else {
			$this->_error = $ret;
			$result = false;
		}
		return $result;
	}

	/**
	 * 查询数据状态接口
	 */
	public function SearchStatus($uid=0, $opts=[], $type=12) {

		$params = ['uid'=>$uid];
		$params['buid'] = $this->fixIdList($opts['buid']);
		$params['qid'] = $this->fixIdList($opts['qid']);
		$params['aid'] = $this->fixIdList($opts['aid']);
		$params['gaid'] = $this->fixIdList($opts['gaid']);
		$params['type'] = 13;
		$params['source'] = $this->_source;
		$config = &$this->_configs['LD'];
		$api = $config['get'];
		$headers = [];
		$params['sign'] = $this->__usercenter_sign($params);
		if ( $this->_deploy == 'dev' ) {
			$api = str_replace($config['dns'], $config['dev'], $api);
			$headers[] = 'Host: '.$config['dns'];
		}
		$ret = curl_get($api, $params, $headers);
		$dbg = [
			'method' => 'GET',
			'api' => $api,
			'params' => $params,
			'headers' => $headers,
			'return' => $ret,
		];
		debug('会员中心接口(查询数据状态)调用', $dbg, false, true);
		// echo PHP_EOL, '<!-- <pre>', PHP_EOL;
		// var_dump(['===debug===', 'api'=>$api, 'params'=>$params, 'headers'=>$headers, 'ret'=>json_decode($ret['result'], true)]);
		// echo PHP_EOL, '</pre> -->', PHP_EOL; // exit;
		if ( $ret['status'] ) {
			$result = json_decode($ret['result'], true);
			if ( $result['code']!=0 ) {
				$this->_error = $result;
				$this->_result = false;
			} else {
				$this->_data = $result;
				$this->_result = true;
			}
			$result = true;
		} else {
			$this->_error = $ret;
			$result = false;
		}
		return !!$ret;
	}
	protected function fixIdList( $list ) {
		$result = '';
		if ( !isset($list) ) {
			return $result;
		}
		if ( is_numeric($list) ) {
			if ( $list > 0 ) {
				$result = strval($list);
			}
			return $result;
		}
		if ( is_string($list) ) {
			$list = explode(',', $list);
		}
		if ( isset($list) && is_array($list) && count($list)>0 ) {
			foreach ( $list as $i => &$opt ) {
				$opt = intval($opt);
				if ( $opt<=0 ) { unset($list[$i]); }
			}
			$result = implode(',', $list);
		}
		return $result;
	}
	protected function sendLDRequest($api, $params=[], $headers=[], $method='get') {
		if ( strtolower($method)==='get' ) {
			$function_name = 'curl_get';
		} else {
			$function_name = 'curl_post';
		}
		$config = &$this->_configs['LD'];
		$params['sign'] = $this->__usercenter_sign($params);
		if ( $this->_deploy == 'dev' ) {
			$api = str_replace($config['dns'], $config['dev'], $api);
			$headers[] = 'Host: '.$config['dns'];
		}
		$params['appkey'] = $config['key'];
		$this->Reset();
		$ret = $function_name($api, $params, $headers);
		$dbg = [
			'method' => ($function_name == 'curl_post' ? 'POST' : 'GET'),
			'api' => $api,
			'params' => $params,
			'headers' => $headers,
			'return' => $ret,
		];
		debug('会员中心接口调用', $dbg, false, true);
		// echo PHP_EOL, '<pre>', PHP_EOL;
		// var_dump(['===debug===', 'api'=>$api, 'params'=>$params, 'headers'=>$headers, 'ret'=>$ret]);
		// echo PHP_EOL, '</pre>', PHP_EOL; exit;
		if ( $ret['status'] ) {
			$result = json_decode($ret['result'], true);
			if ( $result['code']!=0 ) {
				$this->_error = $result;
			} else {
				$this->_data = $result;
				$this->_result = true;
			}
		} else {
			$this->_error = $ret;
			$this->_error['msg'] = $ret['error'];
			$result = false;
		}
		// var_dump($this);
		return $result;
	}
	public function getResult() {
		return $this->_result;
	}
	public function getData() {
		return $this->_data;
	}
	public function getError() {
		return $this->_error;
	}
	public function Reset() {
		$this->_result = false;
		$this->_error = false;
		$this->_data = false;
		return $this;
	}

	/*
	 * 会员中心提供的数据接口签名生成算法
	 * 参考 : http://10.207.0.186:8080/doku.php?id=leju:user:%E7%94%A8%E6%88%B7%E4%BD%93%E7%B3%BB:%E9%97%AE%E7%AD%94%E6%8E%A5%E5%8F%A3%E6%96%87%E6%A1%A3%E8%AF%B4%E6%98%8E
	 * 技术支持 : 王珺 <wangjun11@leju.com>
	 * 提供时间 : 2017-10-23
	 */
	private function __usercenter_sign( $params=[] ) {
		if ( empty($params) ) {
			return '';
		}
		$sKey = "+#~Cn8KN"; // 加密因子
		ksort($params); // 升序排序
		$str = '';
		foreach ($params as $val) {
			$str .= $val; // 参数拼接
		}
		return md5($str.$sKey);// 再拼接加密因子 MD5加密
	}
	/**
	 * 会员中心提供的数据验证密钥算法
	 * 参考 : http://10.207.0.186:8080/doku.php?id=leju:user:%E7%94%A8%E6%88%B7%E4%BD%93%E7%B3%BB:%E5%85%AC%E5%85%B1%E5%8A%A0%E5%AF%86_%E8%A7%A3%E5%AF%86_%E7%AD%BE%E5%90%8D
	 * 技术支持 : 王珺 <wangjun11@leju.com>
	 * 提供时间 : 2017-10-25
	 */
	private function __usercenter_encode( $str='' ) {
		return base64_encode(substr(md5($str),0,8).base64_encode($str).substr(md5($str),10,4));
	}
	public function setDeploy($mode='dev') {
		if ( defined('APP_DEPLOY') ) {
			$this->_deploy = APP_DEPLOY;
		} else {
			$this->_deploy = $mode;
		}
		return $this;
	}

	public function setSource($source=self::UC_SOURCE_LD) {
		$allow_sources = [self::UC_SOURCE_LD, self::UC_SOURCE_PN];
		if ( in_array($source, $allow_sources) ) {
			$this->_source = $source;
		}
		return $this;
	}

}