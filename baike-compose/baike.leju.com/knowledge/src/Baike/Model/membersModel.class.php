<?php
namespace baike\Model;
use Think\Model;

class MembersModel extends Model {

	protected $tablePrefix = ''; 

	// 数据缓存
	protected $redis = null;

	// 单例模型 获取的用户信息
	protected $cache_userlist = array();

	public function getUserByID( $uid ) {
		$uid = intval($uid);
		if ( array_key_exists($uid, $this->cache_userlist) ) {
			return $this->cache_userlist[$uid];
		}
		if ( $uid == 0 ) {
			$this->cache_userlist[0] = C('DICT.GUEST');
		} else {
			$detail = $this->getUserInfo($uid);
			if ( !$detail ) {
				return false;
			}
			$this->cache_userlist[$uid] = $detail;
		}
		return $this->cache_userlist[$uid];
	}

	// 根据指定用户编号获取用户信息
	public function getUserInfo( $uid ) {
		$this->redis = S(C('REDIS'));
		// Cache Struct:
		/*
			<hash> 过期时间短
			Key :
				"M:$UID:DETAIL"
			Value :
				username
				realname
				headurl
				ctime
				sign
				score
				role
				level
			<string> 过期时间长
			Key :
				"M:$UID:INFO"
			Value :
				"{username:$username,headurl:$headurl,ctime:$ctime}"
		*/
		// 0. Redis is exists ?
		$detail_key = "M:{$uid}:DETAIL";
		$info = $this->redis->hgetall($detail_key);
		// A.1. >> (return User Info when Redis exist)
		// var_dump('From Cache: ', $detail_key, $info);
		if ( $info ) {
			return $info;
		}

		// 解决更新时效，去掉 MySQL 缓冲逻辑
		// B.1. get User Info from API (params: userinfo not in Table)
		$api_data = $this->getUserInfoFromAPI($uid);
		// var_dump('From API(UID not in Table): ', $api_data);
		// C.0. >> API Return Error, Return <False>
		if ( !$api_data ) {
			return false;
		}
		// C.1. Insert User Info to Table
		// D.1. Update Cache Info
		// { Format the API Data to Cache Data }
		$data = $this->where("`uid`='{$uid}'")->find();
		$write_mode = ( !$data ) ? 2 : 1;
		$detail = $this->UpdateUserInfo($uid, $api_data, $write_mode);
		// E.1. >> (return User Info)
		return $detail;
		/*
		// A.0. Table User exists ?
		$data = $this->where("`uid`='{$uid}'")->find();
		var_dump('From Database: ', $data);
		if ( !$data ) {
		// N => User Info not in Table
			// B.1. get User Info from API (params: userinfo not in Table)
			$api_data = $this->getUserInfoFromAPI($uid);
			var_dump('From API(UID not in Table): ', $api_data);
			// C.0. >> API Return Error, Return <False>
			if ( !$api_data ) {
				return false;
			}
			// C.1. Insert User Info to Table
			// D.1. Update Cache Info
			// { Format the API Data to Cache Data }
			$detail = $this->UpdateUserInfo($uid, $api_data, 2);
			// E.1. >> (return User Info)
			return $detail;
		} else {
		// Y => User Info in Table
			var_dump('debug', $data);
			// B.0. Table User expired ? (when Table User exist!)
			if ( $data['expire'] > NOW_TIME ) {
				// Table Data Not Expired
				$detail = $this->UpdateUserInfo($uid, $data, 0);
				return $detail;
			} else {
				// Tabal Data Expired
				$api_data = $this->getUserInfoFromAPI($uid);
				var_dump('From API(Table Expired): ', $api_data);
				// C.0. >> API Return Error, Return <False>
				if ( !$api_data ) {
					return false;
				}
				// { Format the API Data to Cache Data }
				$detail = $this->UpdateUserInfo($uid, $api_data, 1);
				// E.1. >> (return User Info)
				return $detail;
			}
		}
		*/
	}

	// 获取用户信息
	public function getUserInfoFromAPI($uid) {
		$data = getMemberInfo($uid);
		if ( $data['code']!=0 ) {
			return false;
		}
		$data['info']['uid'] = $uid;
		$filter = array(
			'uid'=>'', 'username'=>'', 'realname'=>'', 'headurl'=>'', 'ctime'=>'', 'phone'=>'',
		);
		$data = array_intersect_key($data['info'], $filter);
		ksort($data);
		$data['sign'] = md5(json_encode($data));
		return $data;
	}

	// 更新用户信息
	public function UpdateUserInfo($uid, $data, $updateType=0) {
		$uid = intval($uid);
		if ( $uid <= 0 ) {
			return false;
		}
		// 0=> Update Cache
		// 1=> Update Cache & Update Table
		// 2=> Update Cache & Insert Table
		$updateTypes = array(0, 1, 2);
		if ( !in_array($updateType, $updateTypes) ) {
			$updateType = 0;
		}

		$expires = C('MEMBER_CACHE');

		if ( $updateType > 0 ) {
			$data['expire'] = NOW_TIME + $expires['DETAIL_EXPIRE'];
			$this->create($data);
		}
		if ( $updateType == 1 ) {
			$ret = $this->save();
			// var_dump('Update Data: ', $ret, $data);
		}
		if ( $updateType == 2 ) {
			$ret = $this->add();
			// var_dump('Insert Data: ', $ret, $data);
		}


		$detail_key = "M:{$uid}:DETAIL";
		$detail_filter = array(
			'username'=>'',
			'realname'=>'',
			'headurl'=>'',
			'ctime'=>'',
			'expire'=>'',
			'sign'=>'',
			'score'=>'',
			'role'=>'',
			'level'=>'',
		);
		$detail = array_intersect_key($data, $detail_filter);
		$ret = $this->redis->hMSet($detail_key, $detail);
		$this->redis->expire($detail_key, $expires['DETAIL_EXPIRE']);
		// var_dump('Update Cache Detail: ', $ret, $data);

		$info_key = "M:{$uid}:INFO";
		$info_filter = array(
			'username'=>'',
			'headurl'=>'',
			'ctime'=>'',
		);
		$info = array_intersect_key($data, $info_filter);
		$ret = $this->redis->set($info_key, json_encode($info));
		$this->redis->expire($info_key, $expires['INFO_EXPIRE']);
		// var_dump('Update Cache Info: ', $ret, $info);

		return $detail;
	}

}
