<?php
/**
 * 扩展数据接口
 * 数据取自二手房等业务
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class ExtraLogic {

	public function getESF( $city_code='bj' ) {
		$keys = array(
			'newhousematchList140421' => 'esfhome@#&apiL*lsiflsfj[98&^)fKFs|'
		);
		$key = 'newhousematchList140421';
		$real_auth = md5(md5($keys[$key] . '_' . $key . '_' . date('Ymd')));
		$api = 'http://api.esf.sina.com.cn/leju/baike';
		$data = array(
			'citycode'=>$city_code,
			'key'=>$key,
			'auth' => $real_auth,
		);
		$ret = curl_get($api, $data);
		if ( $ret['status']!==false ) {
			$ret = json_decode($ret['result'], true);
		}
		return $ret;
	}

	public function getHouse ( $cities=array('en'=>'bj','code'=>'bj') ) {
		if ( !is_array($cities) ) {
			$cities=array('en'=>'bj','code'=>'bj');
		}

		$city_site = $cities['code'];
		$city_en = $cities['en'];

		$moreurl = 'http://house.leju.com/'.$city_site.'/search/';
		$result = array();
		// 特色新房部份为静态数据
		$result['features'] = array(
			'moreurl' => $moreurl,
			'list' => array(
				array('name'=>'本月开盘', 'url'=>'http://house.leju.com/'.$city_site.'/kaipan/'),
				array('name'=>'最新楼盘', 'url'=>'http://house.leju.com/'.$city_site.'/new/'),
				array('name'=>'热门楼盘', 'url'=>'http://house.leju.com/'.$city_site.'/hot/'),
				array('name'=>'搜索热榜', 'url'=>'http://house.leju.com/'.$city_site.'/search_top/'),
				array('name'=>'精装修', 'url'=>'http://house.leju.com/'.$city_site.'/fitment_good/'),
				array('name'=>'高性价比', 'url'=>'http://house.leju.com/'.$city_site.'/pinpai_list/'),
				array('name'=>'地铁沿线', 'url'=>'http://house.leju.com/'.$city_site.'/nearby_trackline/'),
				array('name'=>'教育地产', 'url'=>'http://house.leju.com/'.$city_site.'/edu_good/'),
				array('name'=>'本月精选', 'url'=>'http://house.leju.com/'.$city_site.'/region_listmonthly_clicks/'),
			),
		);

		$lInfo = D('Infos', 'Logic', 'Common');
		$ret = $lInfo->getCarByCity($city_site);
		if ( $ret ) {
			foreach ( $ret['list'] as $i_car => &$car_house ) {
				$car_house['url'] = 'http://house.leju.com/'.$car_house['site'].$car_house['hid'];
			}
		}
		$result['cars'] = array(
			'moreurl' => $moreurl,
			'list' => $ret['list'],
		);
		return $result;
	}

}
