<?php
/**
 * 知识库 知识内容查看页面
 * @author Robert <yongliang1@leju.com>
 */
namespace Baike\Controller;
use Think\Controller;

class ShowController extends BaseController {

	/**
	 * @var $expire 缓存过期时间 单位 秒
	 * 如果为 false 表示不缓存
	 */
	protected $expire = 600;
	/**
	 * @var $commpressed 是否进行缓存数据压缩 false, 0-9
	 * 如果为 false 表示不压缩
	 */
	protected $compressed = 9;

	/*
	 * 针对详情页添加缓存支持 - 缓存读取页面结果
	 */
	protected function getKnowledgeDetail($id, $type='mobile') {
		$Cacher = S(C('REDIS'));
		$key = 'KB:DETAIL:'.$id.':'.strtoupper($type);
		if ( $this->compressed !== false ) {
			$key = 'GZ:'.$key;
		}
		$data = $Cacher->Get($key);
		if ( $data ) {
			if ( $this->compressed !== false ) {
				$data = json_decode(gzdecode($data), true);
			}
		}
		return $data;
	}
	/*
	 * 针对详情页添加缓存支持 - 保存页面结果到缓存
	 */
	protected function setKnowledgeDetail($id, $detail, $type='mobile') {
		$Cacher = S(C('REDIS'));
		$key = 'KB:DETAIL:'.$id.':'.strtoupper($type);
		if ( $this->compressed !== false ) {
			$key = 'GZ:'.$key;
			$data = gzencode($detail, $this->compressed);
		}
		return $Cacher->SetEx($key, $this->expire, $data);
	}

	/**
	 * 知识内容查看页面
	 */
	public function index() {
		$id = I('get.id', 0, 'intval');
		if ($id <= 0) {
			$this->error('知识ID错误');
			exit;
		}

		if ( isset($this->_flush['data']) && $this->_flush['data']===true ) {
			echo '<!-- flush cache ', date('Y-m-d H:i:s'), ' -->', PHP_EOL;
			$pagedata = false;
		} else {
			$pagedata = $this->getKnowledgeDetail($id, $this->_device);
		}
		
		// 读取缓存并判断，如果缓存不存在，重新生成缓存
		if ( !$pagedata ) {
			$pagedata = array();

			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$where = array('id' => $id);
			$detail = $mKnowledge->where($where)->find();

			if ( $detail ) {
				$status = intval($detail['status']);
				// var_dump($status, $detail);exit;
				if ( !in_array( $status, array(1, 9) ) ) {
					$this->error('知识不存在');
					exit;
				}

				// 如果是草稿状态的数据，去获取最后一次版本发布的数据
				if ( $status==1 ) {
					$mKnowledgeHistory = D('KnowledgeHistory', 'Model', 'Common');
					$_where = array(
						'id' => $id,
					);
					$detail = $mKnowledgeHistory->where($_where)->order('pkid desc')->limit(1)->find();
					if ( !$detail ) {
						$this->error('知识不存在');
						exit;
					}
				}

				$content = $detail['content'];

				// 移动版
				if($this->_device == 'mobile') {
					// 相关新闻，相关楼盘
					$detail['rel_news'] = json_decode($detail['rel_news'],true);
					$detail['rel_house'] = json_decode($detail['rel_house'],true);
					$detail['url'] = url('show', array('id'=>$detail['id']), 'touch', 'baike');
					$detail['tags'] = explode(' ',$detail['tags']);
					$detail['tagids'] = explode(' ',$detail['tagids']);
					$detail['ctime'] = date('Y-m-d H:i',$detail['version']);
					$detail = $this->getNewsAndHouse($detail);
					$detail['catepath'] = $detail['catepath'];

					// 替换内容中出现的词条
					if ( !empty($detail['content']) ) {
						$s = D('Search', 'Logic', 'Common');
						$detail['content'] = $s->renderingContent($detail['content'], 0, null, 'touch');
					}

					// 是否显示大纲
					// $pagedata['show_title_nav'] = ( strpos($detail['content'],'<generalize>') !== false );
					if ( strpos($detail['content'],'<generalize>') !== false ) {
						$pagedata['show_title_nav'] = true;
					 	// $this->assign('show_title_nav', true);
					}
					// 移动端页面的更多链接
					$more = getMore($city);
					$pagedata['more'] = &$more;
					// $this->assign('more',$more);

					// 当前内容指定的城市如果为全国，则以 cookie 中的城市为默认参数
					// @important : 只有移动端使用
					$city = $detail['scope']=='_' ? $this->_city['en'] : $detail['scope'];
					// $binds['register'] = 0;
					// $this->assign('city_en', $city_en);
					// $this->assign('binds',$binds);
					$pagedata['binds'] = array('register' => 0);
				}

				// PC版
				if($this->_device == 'pc') {
					$detail['tags'] = explode(' ',$detail['tags']);

					// 替换内容中出现的词条
					if ( !empty($detail['content']) ) {
						$s = D('Search', 'Logic', 'Common');
						$detail['content'] = $s->renderingContent($detail['content'], 0, null, 'pc');
					}

					$lC = D('Cate','Logic','Common');
					$bread = $lC->crumbs($detail['catepath'],$this->_city['en'],false);

					$catepath = explode('-', $detail['catepath']);
					$nav = $lC->getCategoriesById($catepath[1]);
					$nav = $nav[$catepath[1]]['son'][$catepath[2]];

					$arr = explode("<sectiontitle>", $detail['content']);
					$new_array = array();
					foreach($arr as $k => $v) {
						if(preg_match_all("/\<\/sectiontitle\>/", $v)) {
							$new_array[$k] = explode("</sectiontitle>", $v);
						} else {
							$new_array[$k] = array(1 => $v);
						}
					}
					$detail['content'] = $new_array;

					$num_arr = array(
						1=>"一", 2=>"二", 3=>"三", 4=>"四", 5=>"五",
						6=>"六", 7=>"七", 8=>"八", 9=>"九", 10=>"十",
						11=>"十一", 12=>"十二", 13=>"十三", 14=>"十四", 15=>"十五",
						16=>"十六", 17=>"十七", 18=>"十八", 19=>"十九", 20=>"二十",
					);
					$lFront = D('Front','Logic','Common');
					if ( !empty($detail['tags']) ) {
						$num = 6;
						$scope = $this->_city['cn'];
						$relakb = $lFront->getKnowledgeByTags($detail['tags'], $scope, $detail['id'], $num, false);
						$pagedata['relakb'] = $relakb;
						$this->assign('relakb', $relakb);
					}
					// Rank 热门百科知识排行
					$rank = $lFront->getHotSearchList($this->_city['code'],$this->_city['cn']);
					if ( $rank ) {
						foreach ($rank as $k=>$item) {
							$rank[$k]['title'] = mystrcut($item['title'],14);
							$rank[$k]['url'] = url('show', array('id'=>$item['id']), 'pc', 'baike');
						}
						$pagedata['rank'] = $rank;
						// $this->assign('rank',$rank);
					}

					$cate_all = $lC->getIndexTopCategories();
					$pagedata['cate_all'] = $cate_all;
					$pagedata['_city'] = $this->_city['en'];
					$pagedata['cateid'] = $catepath['1'];
					$pagedata['curcateid'] = end($catepath);

					//assign
					$pagedata['num_arr'] = $num_arr;
					$pagedata['bread'] = $bread;
					$pagedata['nav'] = $nav;
				}

				if ( $detail['tagids'] != '' ) {
					$lTags = D('Tags', 'Logic', 'Common');
					$detail['tagsinfo'] = $lTags->getTagnamesByTagids($detail['tagids']);
				} else {
					$detail['tagsinfo'] = array();
				}
				$pagedata['info'] = $detail;

				//SEO
				$lCate = D('Cate','Logic','Common');
				$pathname = $lCate->pathname($detail['catepath']);
				$seoLogic = D('Seo','Logic','Common');
				$seo = $seoLogic->knowledge_detail($detail['title'], $pathname, $tags, $content);
				$alt_device = $this->_device=='pc' ? 'touch' : 'pc';
				$seo['alt_url'] = url('show', array('id'=>$id), $alt_device, 'baike');
				$pagedata['seo'] = $seo;

				//统计代码
				$count_code = C('FRONT_BAIKE_COUNT_CODE');
				$path = explode('-', $detail['catepath']);
				$level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
				$level2_page = ($this->_device == 'pc') ? $count_code['PC_ALL'][$path['1']] : $count_code['TOUCH_INFO'][$path['1']];
				$level3_page = ($this->_device == 'pc') ? 'info' : '';
				$news_source = $detail['scope'] == '_' ? '全国' : '';

				// $this->assign('level1_page', $level1_page);
				// $this->assign('level2_page', $level2_page);
				// $this->assign('level3_page', $level3_page);
				// $this->assign('news_source', $news_source);
				// $this->assign('custom_id', $id);
				// $this->assign('kd_info_xgzx', '#ln=kd_info_xgzx');
				// $this->assign('kd_info_xglp', '#ln=kd_info_xglp');
				// $this->assign('jsflag', 'kb_show');

				$pagedata['level1_page'] = $level1_page;
				$pagedata['level2_page'] = $level2_page;
				$pagedata['level3_page'] = $level3_page;
				$pagedata['news_source'] = $news_source;
				$pagedata['custom_id'] = $id;
				$pagedata['kd_info_xgzx'] = '#ln=kd_info_xgzx';
				$pagedata['kd_info_xglp'] = '#ln=kd_info_xglp';
				$pagedata['jsflag'] = 'kb_show';

				// 缓存知识正文数据
				$this->setKnowledgeDetail($id, json_encode($pagedata), $this->_device);
			} else {
				$this->error('知识不存在');
				exit;
			}

		}

		$detail = &$pagedata['info'];
		if ( $this->_debug == true ) {
			echo '<!--', PHP_EOL,
				 // var_export($detail, true), PHP_EOL, PHP_EOL, 
				 // var_export($seo, true), PHP_EOL, PHP_EOL,
				 var_export($pagedata, true), PHP_EOL, PHP_EOL,
				 '-->', PHP_EOL;
		}

		// 访问计数器累加
		$this->kbcount($detail, $this->_city['code']);
		// 为 PC 版页面添加新逻辑，猜你喜欢，6条，同分类
		if($this->_device == 'pc') {
			$randoms = $this->lFront->guestRandom(6, $detail['catepath']);
			$this->assign('randoms', $randoms['list']);
		}
		// 设置 SEO 关键词数据
		$this->setPageInfo($pagedata['seo']);
		// @changelog : 2017-4-27: Mantis#89174 1、知识后台发往地方，城市代码为地方站代码 2、知识后台发往全国，城市代码统一用quanguo 3、词条城市代码统一用quanguo
		$this->_city['stat'] = $detail['scope']=='_' ? 'quanguo' : $detail['scope'];
		$this->assign('city', $this->_city);

		foreach ( $pagedata as $name => $value ) {
			$this->assign($name, $value);
		}
		$this->display();
	}


	/**
	 * 知识内容预览
	 * 结束，必须提供key，并且key有效时间仅存在10分钟
	 */
	public function preview () {
		$id = I('get.id', 0, 'intval');
		if ( $id <= 0 ) {
			$this->error('知识ID错误');
			exit;
		}

		$outpoint = intval( intval(date('i', NOW_TIME)) / 10 );
		$deadline = strtolower( date('Y-m-d H:'.($outpoint*10).':00', NOW_TIME) );
		$key = substr(md5($deadline), 0, 6);
		$token = I('get.token', '', 'trim,strtolower');
		if ( $key!==$token ) {
			// 返回报错
			$this->error('访问错误');
			exit;
		}
		// 获取数据
		$mKnowledge = D('Knowledge', 'Model', 'Common');
		$where = array('id' => $id);
		// $where['status'] = 9;
		$detail = $mKnowledge->where($where)->find();
		if ($detail) {
			$content = $detail['content'];
			//移动版
			if($this->_device == 'mobile')
			{
				$detail['rel_news'] = json_decode($detail['rel_news'],true);
				$detail['rel_house'] = json_decode($detail['rel_house'],true);
				$detail['url'] = C('FRONT_URL.show'). $detail['id'];
				$detail['tags'] = explode(' ',$detail['tags']);
				$detail['ctime'] = date('Y-m-d H:i',$detail['version']);
				$detail = $this->getNewsAndHouse($detail);
				$detail['catepath'] = $detail['catepath'];

				//替换内容中出现的词条
				if(!empty($detail['content'])) {
					$s = D('Search', 'Logic', 'Common');
					$detail['content'] = $s->renderingContent($detail['content'], 0, null, 'touch');
				}

				//是否显示大纲
				if(strpos($detail['content'],'<generalize>') !== false) {
					$this->assign('show_title_nav',true);
				}
			}

			//PC版
			if ( $this->_device == 'pc') {
				$detail['tags'] = explode(' ',$detail['tags']);

				//替换内容中出现的词条
				if(!empty($detail['content'])) {
					$s = D('Search', 'Logic', 'Common');
					$detail['content'] = $s->renderingContent($detail['content'], 0, null, 'pc');
				}

				$lC = D('Cate','Logic','Common');
				$bread = $lC->crumbs($detail['catepath'],$this->_city['en'],false);

				$catepath = explode('-', $detail['catepath']);
				$nav = $lC->getCategoriesById($catepath[1]);
				$nav = $nav[$catepath[1]]['son'][$catepath[2]];

				$arr = explode("<sectiontitle>", $detail['content']);
				$new_array = array();
				foreach($arr as $k => $v) {
					if(preg_match_all("/\<\/sectiontitle\>/", $v)) {
						$new_array[$k] = explode("</sectiontitle>", $v);
					} else {
						$new_array[$k] = array(1 => $v);
					}
				}
				$detail['content'] = $new_array;

				$num_arr = array(
					1=>"一", 2=>"二", 3=>"三", 4=>"四", 5=>"五",
					6=>"六", 7=>"七", 8=>"八", 9=>"九", 10=>"十",
					11=>"十一", 12=>"十二", 13=>"十三", 14=>"十四", 15=>"十五",
					16=>"十六", 17=>"十七", 18=>"十八", 19=>"十九", 20=>"二十",
				);
				if ( !empty($detail['tags']) ) {
					$lF = D('Front','Logic','Common');
					$relakb = $lF->getKnowledgeByTags($detail['tags'], $this->_city['cn'], $detail['id'], 6);
					$this->assign('relakb',$relakb);
				}
				// rank
				$lFront = D('Front','Logic','Common');
				$rank = $lFront->getHotSearchList($this->_city['code'],$this->_city['cn']);
				if ( $rank ) {
					foreach ( $rank as $k=>$item ) {
						$rank[$k]['title'] = mystrcut($item['title'],14);
						$rank[$k]['url'] = url('show', array('id'=>$item['id']), 'pc', 'baike');
					}
					$this->assign('rank',$rank);
				}

				$cate_all = $lC->getIndexTopCategories();
				$this->assign('cate_all',$cate_all);
				$this->assign('_city',$this->_city['en']);
				$this->assign('cateid',$catepath['1']);
				$this->assign('curcateid',end($catepath));

				//assign
				$this->assign('num_arr', $num_arr);
				$this->assign('bread',$bread);
				$this->assign('nav',$nav);
			}
			// 预览不计数
			// $this->kbcount($detail,$this->_city['code']);
		} else {
			$this->error('知识不存在');
			exit;
		}


		// 当前内容指定的城市如果为全国，则以 cookie 中的城市为默认参数
		// @important : 只有移动端使用
		$city = $detail['scope']=='_' ? $this->_city['en'] : $detail['scope'];
		$binds['register'] = 0;
		$this->assign('info', $detail);
		// $this->assign('city_en', $city_en);
		$this->assign('more',getMore($city));
		$this->assign('binds',$binds);

		//SEO
		$lCate = D('Cate','Logic','Common');
		$pathname = $lCate->pathname($detail['catepath']);
		$seoLogic = D('Seo','Logic','Common');
		$column = $pathname; //栏目名称 -分割
		$tags = '';
		if (!empty($detail['tags'])) {
			$tags = implode(',', $detail['tags']);
		}
		$seo = $seoLogic->knowledge_detail($detail['title'], $column, $tags, $content);
		$this->setPageInfo($seo);

		$this->display('index');
	}

	/**
	 * 展现相关新闻和相关楼盘
	 * 暂时只用于移动端页面渲染
	 */
	private function getNewsAndHouse($info) {
		$lInfos = D('Infos', 'Logic', 'Common');
		if ($info['rel_news']) {
			$rel_news = $info['rel_news'];
			$newsids = array();
			foreach ($rel_news as $key => $news) {
				$newsids[] = $news['id'];
				$tmp_news[$news['id']] = $news;
			}
			$newslist = $lInfos->getNews($newsids);
			if ($newslist) {
				foreach ($newslist as $k => $value) {
					if(array_key_exists($k, $tmp_news)) {
						$newslist[$k]['title'] = empty($tmp_news[$k]['title']) ? $newslist[$k]['title'] : $tmp_news[$k]['title'];
					} else {
						unset($tmp_news[$k]);
					}
				}
				$info['rel_news'] = $newslist;
			}
		} else {
			$result = $lInfos->relNews($info['tags'], 5);
			if ($result) {
				$info['rel_news'] = $result;
			}
		}

		if ($info['rel_house']) {
			$rel_house = $info['rel_house'];
			$houseids = array();
			foreach ( $rel_house as $k => $house ) {
				$houseids[] = $house['site'].$house['hid'];
				$tmp_house[$house['site'].$house['hid']] = $house;
			}
			$houselist = $lInfos->getHouse($houseids);
			if ($houselist) {
				foreach ($houselist as $key => $value) {
					if (array_key_exists($key, $tmp_house)) {
						$houselist[$key]['salephone'] = $houselist[$key]['phone_extension'] ? "4006108616,2{$houselist[$key]['phone_extension']}" : '4006108616';
						$houselist[$key]['name'] = !empty($tmp_house[$key]['name']) ? $tmp_house[$key]['name'] : $houselist[$key]['name'];
						//$houselist[$k]['price_display'] = $tmp_house[$k]['price_display'];
						//$houselist[$k]['pic_s320'] = $tmp_house[$k]['pic_s320'];
					} else {
						unset($tmp_house[$key]);
					}
				}
				$info['rel_house'] = $houselist;
			}
		}
		return $info;
	}

	/**
	 * 访问计数统计
	 */
	private function kbcount( $info, $city ) {
		if ( $info ) {
			$mVisitStats = D('VisitStats', 'Model', 'Common');
			$data['uid'] = 1;
			$data['relid'] = $info['id'];
			$data['reltype'] = 'kb';
			$data['relcateid'] = $info['catepath'];
			$data['ctime'] = NOW_TIME;
			$data['city'] = $city;
			return $mVisitStats->add($data);
		}
		return false;
	}
}
