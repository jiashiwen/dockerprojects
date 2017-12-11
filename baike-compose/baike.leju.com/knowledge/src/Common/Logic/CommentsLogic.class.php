<?php
/**
 * 评论系统业务服务
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class CommentsLogic {

	protected $key = '';
	protected $api = [];
	protected $types = []; // 所有已定义的业务及业务appkey

	public function __construct() {
		$configs = C('LEJUCOMMENTS');
		$this->api = $configs['api'];
		$this->key = $configs['key'];
		// echo '<!--', PHP_EOL, print_r($configs, true), PHP_EOL, var_export($this, true), PHP_EOL, '-->', PHP_EOL;
		$this->types = [
			'knowledge' => 'b955607ce606d77dda8b16059278752b', // 知识
			'wiki' 		=> '8755205200a05103b0534c08b62b88d7', // 词条
			'question' 	=> 'c60ac31f20c7172ed6e73cb07efa4af9', // 问答
			'ldanswer' 	=> '87856df5e23ebc3e1785fb6cefc4505a', // 乐道问答
			'pnanswer' 	=> '705ffe1298da85ad3bb35d06d2fa7fa5', // 人物问答
		];
	}

	/* 获取评论系统应用key */
	public function getAppkey($type) {
		$types = $this->types;
		if ( !array_key_exists($type, $types) ) {
			return false;
		}
		$appkey = $types[$type];
		return $appkey;
	}

	/* 将业务primary key 转换为新闻池unique_id */
	public function getUniqueID($type, $id) {
		$appkey = $this->getAppkey($type);
		if ( $appkey === false ) {
			return false;
		}
		return md5( $appkey . $id );
	}

	public function getCommentCount( $ids=[] ) {
		$result = [];
		if ( empty($ids) ) {
			return $result;
		}
		$api = trim($this->api['getCommentCount']);
		// echo '<!--', PHP_EOL, print_r($ids, true), PHP_EOL, var_export($this->api, true), PHP_EOL, '-->', PHP_EOL;
		if ( $api=='' ) {
			return $result;
		}
		$query = [
			'key' => $this->key,
			'unique_id' => implode(',', $ids),
		];
		$ret = curl_get($api, $query);
		if ( $ret['status'] ) {
			$list = json_decode($ret['result'], true);
			if ( isset($list['data']) && is_array($list['data']) && !empty($list['data']) ) {
				$list = $list['data'];
				foreach ( $list as $i => $item ) {
					$id = intval($item['unique_id']);
					$count = intval($item['comment_count']);
					$result[$id] = $count;
				}
			}
		}
		// echo '<!--', PHP_EOL, print_r($ret, true), PHP_EOL, 
		// print_r($api, true), PHP_EOL, 
		// print_r($list, true), PHP_EOL, 
		// print_r($query, true), PHP_EOL, '-->', PHP_EOL;
		return $result;
	}
}