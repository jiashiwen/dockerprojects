<?php
/**
 * pc页面核心逻辑基础类
 * @author Robert <yongliang1@leju.com>
 */

namespace Tag\Logic;

class PcPageLogic extends PageLogic{

	public function initIndexFocus() {
		return false;
	}

	public function initIndexHot() {
		$result = D('Front', 'Logic','Common')->getHotWords();
		return $result;
		//return $this->getHot($redis_key);
	}

	public function initIndexHuman()
	{
		return $this->getPersons();
	}

	public function initIndexOrganization()
	{
		return $this->getCompanies();
	}

	public function initIndexFresh()
	{
		return $this->getLatest();
	}

	public function initListAll($redis_key, $page, $pagesize)
	{
		return $this->getPCList($redis_key, $page, $pagesize);
	}

	public function initDetailNews($detail)
	{
		return $this->getNews($detail);
	}

	public function initDetailHouse($detail)
	{
		return $this->getHouse($detail);
	}


	/**
	 * 获取相关新闻 PC版
	 * 规则 按当前百科词条设置的标签，提取新闻池近期新闻
	 * 4、相关新闻
	 * 调取该词条关联的标签在发布系统中的新闻按照时间倒序排序，若为空则，调取标签房产下的新闻，最多显示4条
	 * // array (size=63)
	 * //   'id' => string '6222968018784549849' (length=19)
	 * //   'deleted' => string '1' (length=1)
	 * //   'createtime' => string '1514688524' (length=10)
	 * //   'newsid' => string '6222968018784549849' (length=19)
	 * //   'city_md5' => string '' (length=0)
	 * //   'hid' => string '' (length=0)
	 * //   'ishomed' => string '1' (length=1)
	 * //   'updatetime' => string '1487905452' (length=10)
	 * //   'c_id' => string '5892892285301238595' (length=19)
	 * //   'p_id' => string '5894623460461979958' (length=19)
	 * //   't_id' => string '5897527456981920030' (length=19)
	 * //   'd_id' => string '6222968018784549849' (length=19)
	 * //   'is_sendto_360' => string '0' (length=1)
	 * //   'vid' => string '0' (length=1)
	 * //   'is_original' => string '0' (length=1)
	 * //   'is_original_mapsuffix' => string '0' (length=1)
	 * //   'leju_member_id' => string '0' (length=1)
	 * //   'land_id' => string '0' (length=1)
	 * //   'system_create_time' => string '1483671196' (length=10)
	 * //   'system_update_time' => string '1502369799' (length=10)
	 * //   'service_type' => string '' (length=0)
	 * //   'title' => string '老康的淘宝故事：一入收藏深似海' (length=45)
	 * //   'show_roles' => string '' (length=0)
	 * //   'mobile_activity_type' => string '' (length=0)
	 * //   'shorttitle' => string '老康的淘宝故事一入收藏深' (length=36)
	 * //   'author' => string '' (length=0)
	 * //   'content' => string '<p>　　话说老康在不惑之年的一次出差时淘得一把银壶，想作为礼物送给生意伙伴。回程的路上，坐在火车上，看着窗外地域变换，把玩着这把工艺精湛、花纹特别的银壶，突然觉得它变得厚重起来，如同岁月一般，跨越百年时空亦然愈陈愈醇。</p><p>　　而后，老康开始关注银器的收藏，并且一发不可收拾。十余年来，从乡野到国外，从中国回流古董银器到西洋银器到各国银器，他收�'... (length=5127)
	 * //   'photo_manage' => string '[]' (length=2)
	 * //   'zhaiyao' => string '谈历史。老康如同遇到了知音，遇到了学者，让他对银器收藏有了更多的认知和感受。就如中国回流银器，二人的观点如出一辙：这些在漫长岁月沧桑流离海外的精品文物，有其特殊的内涵和重大意义，它们是中国历史发展的印记，是中国灿烂文化的缩影，需要有一些懂收藏有情怀的国人去珍藏。' (length=393)
	 * //   'other_media' => string '' (length=0)
	 * //   'media' => string '' (length=0)
	 * //   'topcolumn' => string '市场' (length=6)
	 * //   'subcolumn' => string '' (length=0)
	 * //   'tags' => string '经济,中国,创客' (length=20) 
	 * //   'tags_id' => string '1953,3119,2630' (length=14)	<<
	 * //   'relate_news' => string '' (length=0)
	 * //   'hname' => string '' (length=0)
	 * //   'land_ids' => string '' (length=0)
	 * //   'push_to_toutiao' => string '' (length=0)
	 * //   'toutiao_style' => string '0' (length=1)
	 * //   'picurl' => string '' (length=0)
	 * //   'picurl2' => string '' (length=0)
	 * //   'picdescription2' => string '' (length=0)
	 * //   'picurl20' => string '' (length=0)
	 * //   'price_off' => string '' (length=0)
	 * //   '720_aerial_cloud' => string '' (length=0)
	 * //   'aerial_video_tencent_flash' => string '' (length=0)
	 * //   'is_add_msg_board' => string '1' (length=1)
	 * //   'platform' => string '1|2' (length=3)
	 * //   'creator' => string '7266' (length=4)
	 * //   'last_editor' => string '0' (length=1)
	 * //   'url' => string 'http://heze.leju.com/news/2017-12-31/10486222968018784549849.shtml' (length=66)
	 * //   'news_source_type' => string '' (length=0)
	 * //   'news_source_id' => string '' (length=0)
	 * //   'city' => string 'eh' (length=2)
	 * //   'system_type' => string 'new_news' (length=8)
	 * //   'click_count' => string '22' (length=2)
	 * //   'attention' => string '' (length=0)
	 * //   'vr_newsid' => string '' (length=0)
	 * //   'sinaapp_toupiao_id' => string '' (length=0)
	 * //   'card_json' => string '' (length=0)
	 * //   'new_attribute' => string '' (length=0)
	 * //   'hids' => string '' (length=0)
	 */
	public function getRelationNews( $tagids=array(), $page=1, $pagesize=4 ) {
		$list = parent::getRelationNews($tagids, 'touch');
		$count = count($list);
		$_list = array_chunk($list, $pagesize);
		$page = $page<=1 ? 0 : $page-1;
		if ( array_key_exists($page, $_list) ) {
			$list = $_list[$page];
		} else {
			$list = array();
		}
		return $list;
	}

	/*
	 * 按标签查询词条
	*/
	public function getTagList($key, $tag, $page=1, $pagesize=16)
	{
		// $result = $this->cacher->get($key);
		if ( !$result ) {
			$tag = trim(implode(',', $tag));
			$lSearch = D('Search','Logic','Common');
			$opts = array( array($tag,'_tags'), array('false', '_deleted') );
			if ( $cateid!==false ) { $opts[] = array($cateid, '_multi.cateid'); }
			// print_r($opts);
			// $order = array('_multi.title_pinyin', 'asc');
			$order = array('_multi.title_prefix', 'asc');
			$fields = array('_id', '_origin', '_multi');
			$result = $lSearch->select($page, $pagesize, '', $opts, array(), $order, $fields, 0, 'wiki');
			// $url = C('DATA_TRANSFER_API_URL') . "api/item?sort=time&client=1&search=1&tag={$tag}&page={$page}&pagesize={$pagesize}";
			// $result = curl_get($url);
			// $list = array();
			// if($result['status'] == true)
			// {
			// 	$list = json_decode($result['result'], true);
			// 	$this->cacher->set($key, $list, $this->_cache_time);
			// }
		}

		return $result;
	}

}
