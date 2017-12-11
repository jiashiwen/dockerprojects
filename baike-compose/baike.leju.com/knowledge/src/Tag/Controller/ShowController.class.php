<?php
/**
 * 知识库 百科词条查看
 * @author
 */
namespace Tag\Controller;

class ShowController extends BaseController {
	public function __construct() {
		parent::__construct();
		$this->dict = C('DICT.WIKI');
	}
	/**
	 * 查看词条内容页面
	 */
	public function index() {
		$id = I('get.id', 0, 'intval');
		if ( $id <= 0 ) {
			$this->error('请指定您要查看的百科词条编号');
		}

		if ( !is_numeric($id) || intval($id)!=$id ) {
			$this->error('您指定的词条不存在');
		}

		$key = "WIKI:DETAIL:{$id}:CORE";
		if ( $this->_flush['data']===true ) {
			$detail = false;
		} else {
			$detail = $this->redis->Get($key);
		}
		if ( $detail ) {
			$cateid = intval($detail['cateid']);
			// @20171206 强制企业和人物的触屏页面跳转至财经落地页
			if ( $this->_device!='pc' && in_array($cateid, [1,2]) ) {
				$url = url('show', [$id, $cateid],'touch', 'wiki');
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: '.$url);
				exit;
			}
		}
		if ( !$detail ) {
			// 使用编号逻辑模型
			$mWiki = D('Wiki', 'Model', 'Common');
			$detail = $mWiki->find($id);
			$cateid = intval($detail['cateid']);
			if ( $cateid==0 ) {
				// var_dump($cateid, $detail);
				$this->index0($detail);
				exit;
			}
			if ( !$detail || intval($detail['status'])!=9 ) {
				$this->error('词条不存在');
			}

			// @20171206 强制企业和人物的触屏页面跳转至财经落地页
			if ( $this->_device!='pc' && in_array($cateid, [1,2]) ) {
				$url = url('show', [$id, $cateid],'touch', 'wiki');
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: '.$url);
				exit;
			}

			// 添加判断是否是cric占位公司
			if ( $cateid==1 ) {
				$parent_id = intval($detail['company_parent_id']);
				$src_type = intval($detail['src_type']);
				if ( $src_type==2 && $parent_id!==0 ) {
					$this->error('公司信息不存在..');
				}
			}

			// 展开扩展字段
			$detail = $mWiki->convertFields($detail, false);
			$detail['summary'] = $this->convertContent($detail['summary']);
			$detail['content'] = $this->convertContent($detail['content']);
			// 补充并验证可显示的相关词条数据
			// G('st');
			$fields = array('id', 'title', 'cover', 'ctime');
			$rels = array_keys($detail['rel']['companies']);
			if ( !empty($rels) ) {
				$where = array('status'=>9, 'cateid'=>1, 'id'=>array('in', $rels));
				$detail['rel']['companies'] = $mWiki->field($fields)->where($where)->select();
			}
			$rels = array_keys($detail['rel']['figures']);
			if ( !empty($rels) ) {
				$where = array('status'=>9, 'cateid'=>2, 'id'=>array('in', $rels));
				$detail['rel']['figures'] = $mWiki->field($fields)->where($where)->select();
			}
			// G('et');
			// echo '<!--', PHP_EOL, 'cost', PHP_EOL, G('et', 'st', 3), PHP_EOL, 'mem', PHP_EOL, G('et', 'st', 'mem'), PHP_EOL, '-->', PHP_EOL;
			$expire = $this->_cache_time + rand(-1800, 1800);
			$this->redis->SetEx($key, $expire, json_encode($detail));
			$detail['_cached'] = false;
		} else {
			$detail['_cached'] = true;
		}
		// G('st1');
		$tagids = explode(',', trim($detail['tagids'], ','));
		$detail['rel']['news'] = $this->lPage->getRelationNews($tagids, 1);
		// G('et1');
		// echo '<!--', PHP_EOL, 'cost', PHP_EOL, G('et1', 'st1', 3), PHP_EOL, 'mem', PHP_EOL, G('et1', 'st1', 'mem'), PHP_EOL, '-->', PHP_EOL;
		// echo '<!--', PHP_EOL, 'Get From Database', PHP_EOL, print_r($detail, ture), PHP_EOL, '-->', PHP_EOL;
		$this->assign('detail', $detail);
		$dict = &$this->dict;
		$cateid = intval($detail['cateid']);
		if ( $cateid==0 ) { // 如果是普通词条，强制清除缓存
			$this->redis->Del($key);
		}
		$this->assign('cateid', $cateid);
		$dict_basic = isset($dict['BASIC'][$cateid]) ? $dict['BASIC'][$cateid] : [];
		$this->assign('dict_basic', $dict_basic);
		if ( $this->_device != 'pc' ) {
			layout(false);
		}
		$this->setPageInfo($detail);
		// 添加访问计数代码
		$this->hits_count($id);

		$this->gen_stats_code($id, $cateid, 'SHOW');
		$this->display();
	}


	public function index0( $detail=array() ) {
		$_type = ( $this->_device=='mobile' ) ? 'touch' : 'pc';
		$id = I('get.id', 0, 'intval');
		$origin_id = $id;
		if ( $id==0 ) {
			//不传词条ID引导回首页
			redirect('/tag/');
		}

		if ( !$detail ) {
 
			if ( empty($detail) ) {
				$this->error('词条不存在');
			}
			//百科相关咨询
			$detail['news'] = $this->lFront->initDetailNews($detail);
			//百科相关楼盘
			$detail['house'] = $this->lFront->initDetailHouse($detail);
			//替换内容中出现的词条
			if ( !empty($detail['content']) ) {
				$s = D('Search', 'Logic', 'Common');
				$detail['content'] = $s->renderingContent($detail['content'], 0, null, $_type);
			}
			$r->set($this->_cache_keys['detail'].$id.$_type, $detail, 86400);
		}
		//是否显示大纲
		if ( strpos($detail['content'],'<sectiontitle>') !== false ) {
			// $this->assign('show_title_nav', true);
		}
		$content = $detail['content'];
		// $tags = $detail['tags'];
		$tagids = explode(',', trim($detail['tagids'], ','));
		$detail['tagsinfo'] = D('Tags', 'Logic', 'Common')->getTagnamesByTagids($tagids);
		// $detail['tagsinfo'] = $this->lFront->convertTagToTagid($tags);
		// 展开扩展字段
		$detail = D('Wiki', 'Model', 'Common')->convertFields($detail, false);

		if($this->_device != 'mobile') {
			$detail['content'] = preg_replace("/\<p\>\<br\/\>\<\/p\>/Ui", "", $detail['content']);
			$arr = explode("<sectiontitle>", $detail['content']);
			$new_array = array();
			foreach($arr as $k => $v)
			{
				if(preg_match_all("/\<\/sectiontitle\>/", $v))
				{
					$new_array[$k] = explode("</sectiontitle>", $v);
				}
				else
				{
					$v = str_replace('sectiontitle2', 'h4', $v);
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
			$this->assign('num_arr', $num_arr);
			//热门词条api
			$hot = $this->lPage->initIndexHot();
			$hot = array_slice($hot, 0, 6);
			$this->assign('hot', $hot);
			//获取热门百科知识
			$knowledge = D('Front', 'Logic','Common');
			$hot_know = $knowledge->getHotSearchList($this->_city['code'],$this->_city['cn']);
			$hot_know = array_slice($hot_know, 0, 8);
			$this->assign('hot_know', $hot_know);
			//标签获取相关知识
			$tag_know = $knowledge->getKnowledgeByTags($detail['tags'], $this->_city['cn']);
			$tag_know = array_slice($tag_know, 0, 6);
			$this->assign('tag_know', $tag_know);
			if ( isset($detail['rel']['news']) && is_array($detail['rel']['news']) && !empty($detail['rel']['news']) ) {
				foreach ( $detail['rel']['news'] as $i => &$news ) {
					// 'http://fj.house.sina.com.cn/news/2016-10-06/23356189820216189714230.shtml';
					$_url = explode('.', $news['url']);
					$_city = str_replace('http://','',$_url[0]);
					$news['m_url'] = 'http://m.leju.com/news-'.$_city.'-'.$news['id'].'.html';
					$_url = explode('/', $news['url']);
					$_time = $_url[4];
					$news['createtime'] = strtotime($_time);
				}
			}
		}
		$this->assign('detail', $detail);
		//统计代码
		$level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
		$level2_page = ($this->_device == 'pc') ? 'pc_ct_info' : 'wd_info';
		$this->assign('level1_page', $level1_page);
		$this->assign('level2_page', $level2_page);
		$this->assign('custom_id', $id);
		$this->assign('more', getMore());
		//SEO
		if ( isset($detail['seo']) ) {
			$this->setPageInfo($detail);
		} else {
			$seoLogic = D('Seo','Logic','Common');
			$seo = $seoLogic->wiki_detail($detail['title'], $detail['tags'], $content);
			$alt_device = $this->_device=='pc' ? 'touch' : 'pc';
			$seo['alt_url'] = url('show', array('id'=>$origin_id, $detail['cateid']), $alt_device, 'wiki');
			$this->setPageInfo($seo);
		}
		$this->display('index0');
	}

	/**
	 * 添加页面统计代码
	 */
	protected function gen_stats_code( $id=0, $cateid=0, $action='SHOW' ) {
		if ( $id==0 ) { return false; }
		//统计代码
		$dict = &$this->dict;
		$stats_code = $dict['STATS_CODE'];
		$device = $this->_device=='pc' ? 'PC' : 'TOUCH';
		$stats = array();
		$stats['city'] = 'quanguo';
		$stats['level1'] = $stats_code[$action][$device]['level1'];
		$stats['level2'] = $stats_code[$action][$device]['level2'][$cateid];
		$stats['level3'] = $stats_code[$action][$device]['level3'][$cateid];
		$stats['custom_id'] = $id;
		$stats['news_source'] = '';
		$stats['_rel'] = $stats_code[$action][$device]['_rel'];
		// var_dump($stats_code, $action, $device, $cateid, $id, $stats);
		$this->assign('stats', $stats);
	}
	protected function convertContent( $html ) {
		$r = ['o'=>[], 'n'=>[]];
		// 一级标题
		$r['o'][0] = '<sectiontitle>';
		$r['n'][0] = '<h2 class="js_title js_main_title">';
		$r['o'][1] = '</sectiontitle>';
		$r['n'][1] = '</h2>';
		// 二级标题
		$r['o'][2] = '<sectiontitle2>';
		$r['n'][2] = '<h5 class="wt_nian wt_sjl js_sub_title">';
		$r['o'][3] = '</sectiontitle2>';
		$r['n'][3] = '</h5>';
		return str_replace($r['o'], $r['n'], $html);
	}

	// 统计计数
	protected function hits_count( $id ) {
		$id = intval($id);
		if ( $id > 0 ) {
			$data = array();
			$data['uid'] = intval(cookie('M_UID'));
			$data['relid'] = $id;
			$data['reltype'] = 'wiki';
			$data['ctime'] = NOW_TIME;
			D('VisitStats', 'Model', 'Common')->add($data);
			D('Wiki', 'Model', 'Common')->where(array('id'=>$id))->setInc('hits', 1);
			return true;
		}
		return false;
	}


	/**
	 * 知识内容预览
	 * 结束，必须提供key，并且key有效时间仅存在10分钟
	 */
	public function preview () {
		C('ERROR_PAGE', WEB_ROOT.'/p/err/error.html');
		$_type = ( $this->_device=='mobile' ) ? 'touch' : 'pc';
		$id = I('get.id', '', 'intval');
		if ( $id<=0 ) {
			$this->error('百科词条编号错误');
			// 不传词条ID引导回首页
			// redirect('/tag/');
		}
		// 所有页面 url 中传递的百科词条id均为 base64_encode 转换后的数据，在展示之前，反转换回正常的接口 id

		$outpoint = intval( intval(date('i', NOW_TIME)) / 10 );
		$deadline = strtolower( date('Y-m-d H:'.($outpoint*10).':00', NOW_TIME) );
		$key = substr(md5($deadline), 0, 6);
		$token = I('get.token', '', 'trim,strtolower');
		// var_dump($outpoint, $deadline, $key, $token);
		if ( $key!==$token ) {
			// 返回报错
			$this->error('访问验证错误');
			exit;
		}

		// 使用编号逻辑模型
		$mWiki = D('Wiki', 'Model', 'Common');
		$detail = $mWiki->find($id);
		if ( !$detail ) {
			$this->error('词条不存在');
		}
		// 展开扩展字段
		$detail = $mWiki->convertFields($detail, false);
		$detail['summary'] = $this->convertContent($detail['summary']);
		$detail['content'] = $this->convertContent($detail['content']);
		// 补充并验证可显示的相关词条数据
		// G('st');
		$fields = array('id', 'title', 'cover', 'ctime');
		$rels = array_keys($detail['rel']['companies']);
		if ( !empty($rels) ) {
			$where = array('status'=>9, 'cateid'=>1, 'id'=>array('in', $rels));
			$detail['rel']['companies'] = $mWiki->field($fields)->where($where)->select();
		}
		$rels = array_keys($detail['rel']['figures']);
		if ( !empty($rels) ) {
			$where = array('status'=>9, 'cateid'=>2, 'id'=>array('in', $rels));
			$detail['rel']['figures'] = $mWiki->field($fields)->where($where)->select();
		}
		// G('et');
		// echo '<!--', PHP_EOL, 'cost', PHP_EOL, G('et', 'st', 3), PHP_EOL, 'mem', PHP_EOL, G('et', 'st', 'mem'), PHP_EOL, '-->', PHP_EOL;
		$tagids = explode(',', trim($detail['tagids'], ','));
		$detail['rel']['news'] = $this->lPage->getRelationNews($tagids, 1);
		// G('et1');
		// echo '<!--', PHP_EOL, 'cost', PHP_EOL, G('et1', 'st1', 3), PHP_EOL, 'mem', PHP_EOL, G('et1', 'st1', 'mem'), PHP_EOL, '-->', PHP_EOL;
		// echo '<!--', PHP_EOL, 'Get From Database', PHP_EOL, print_r($detail, ture), PHP_EOL, '-->', PHP_EOL;
		$this->assign('detail', $detail);
		$dict = C('DICT.WIKI');
		$cateid = intval($detail['cateid']);
		$dict_basic = isset($dict['BASIC'][$cateid]) ? $dict['BASIC'][$cateid] : [];
		$this->assign('dict_basic', $dict_basic);
		if ( $this->_device != 'pc' ) {
			layout(false);
		}
		$this->setPageInfo($detail);
		$this->display('index');
	}

	/**
	 * 展示相册
	 */
	public function album() {
		$id = I('get.id', 0, 'intval');
		if ( $id <= 0 ) {
			$this->error('请指定您要查看的百科词条相册编号');
		}

		if ( !is_numeric($id) || intval($id)!=$id ) {
			$this->error('您指定的词条相册不存在');
		}

		// 使用编号逻辑模型
		$mWiki = D('Wiki', 'Model', 'Common');
		$where = array(
			'id' => $id,
			'status' => 9,
		);
		$fields = array('id', 'title', 'cateid', 'ctime', 'extra', 'seo', 'basic', 'rel', 'album');
		$detail = $mWiki->field($fields)->where($where)->find();
		if ( !$detail ) {
			$this->error('词条相册不存在');
		}
		// 展开扩展字段
		$extras = array('extra', 'seo', 'basic', 'rel', 'album');
		foreach ( $extras as $i => $field ) {
			if ( isset($detail[$field]) ) {
				$detail[$field] = json_decode($detail[$field], true);
			}
		}
		if ( count($detail['album']['list'])==0 ) {
			$this->error('词条相册不存在');
		}
		$this->assign('detail', $detail);
		layout(false);
		$cateid = intval($detail['cateid']);
		$this->gen_stats_code($id, $cateid, 'ALBUM');
		$this->display();
	}

	public function morenews() {
		$id = I('get.id', 0, 'intval');
		$page = I('get.page', 2, 'intval');
		if ( $id<=0 ) {
			$this->ajax_error('请指定百科编号');
		}
		$key = "WIKI:DETAIL:{$id}:CORE";
		if ( $this->_flush['data']===true ) {
			$detail = false;
		} else {
			$detail = $this->redis->Get($key);
		}
		if ( !$detail ) {
			// 使用编号逻辑模型
			$mWiki = D('Wiki', 'Model', 'Common');
			$detail = $mWiki->find($id);
			if ( !$detail || intval($detail['status'])!=9 ) {
				$this->ajax_error('指定的百科词条不存在');
			}
		}

		// 统计参数代码
		$cateid = intval($detail['cateid']);
		$dict = &$this->dict;
		$stats_code = $dict['STATS_CODE'];
		$device = $this->_device=='pc' ? 'PC' : 'TOUCH';
		$_rel = $stats_code['SHOW'][$device]['_rel']['news'][$cateid];

		$list = array();
		$tagids = explode(',', trim($detail['tagids'], ','));
		if ( $tagids && $page<=5 ) {
			$ret = $this->lPage->getRelationNews($tagids, $page);
			foreach ( $ret as $i => $news ) {
				array_push($list, array(
					'title' => $news['title'],
					'zhaiyao' => $news['zhaiyao'],
					'time' => formatQATimer($news['createtime']),
					'hits' => $news['click_count'],
					'img' => $news['picurl'],
					'url' => $news['url'].$_rel,
					'tagsinfo' => $news['tagsinfo'],
				));
			}
		}
		$result =  array('status'=>true, 'list'=>$list);
		$this->ajax_return($result);
	}

}
