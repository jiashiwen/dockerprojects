<?php
/**
 * 数据升级功能逻辑模块
 */
namespace admin\Logic;

class UpgradeLogic {

	protected $tagsinfo = array();
	protected $lTag = null;

	public function init() {
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set('memory_limit', '512M'); // 设置内存限制
		ini_set('default_socket_timeout', -1);

		$this->lSearch = D('Search', 'Logic', 'Common');
		$this->lTag = D('Tags', 'Logic', 'Common');
		$this->mWiki = D('Wiki', 'Model', 'Common');
		$this->mNewWiki = D('NewWiki', 'Model', 'Common');
		$this->mWikiHistory = D('WikiHistory', 'Model', 'Common');
		$this->mNewWikiHistory = D('NewWikiHistory', 'Model', 'Common');

		// $this->test();
		// exit;
		// 根据数据库中词中已经设置的标签，获取标签对应的标签系统编号
		G('start_init_tag');
		$this->initTags();
		G('end_init_tag');
		var_dump(array(
			'Init Tags',
			G('start_init_tag', 'end_init_tag', 'm'),
			G('start_init_tag', 'end_init_tag', 3),
		));
		// 清理搜索服务中已经存在的词条数据
		G('start_flush_engine');
		$this->flushEngine();
		G('end_flush_engine');
		var_dump(array(
			'Init Tags',
			G('start_flush_engine', 'end_flush_engine', 'm'),
			G('start_flush_engine', 'end_flush_engine', 3),
		));
	}

	protected function initTags() {
		$sql = "SELECT tags FROM ".
			   "(".
			   "	SELECT tags FROM wiki".
			   "	UNION ALL".
			   "	SELECT tags FROM wiki_history".
			   ") t GROUP BY t.tags";
		$list = $this->mWiki->query($sql);
		$_tags = array();
		foreach ( $list as $i => $row ) {
			$tags = explode(' ', trim($row['tags']));
			foreach ( $tags as $t => $tag ) {
				$tag = trim($tag);
				if ( $tag == '' ) continue;
				if ( !in_array($tag, $_tags) ) {
					array_push($_tags, $tag);
				}
			}
		}
		$ret = $this->lTag->getTagnamesByTags($_tags);
		foreach ( $ret as $i => $tag ) {
			$this->tagsinfo[$tag['name']] = $tag['id'];
		}
		return count($this->tagsinfo);
	}

	protected function flushEngine() {
		$page=1;
		$pagesize=10000;
		$keyword='';
		$opts=array();
		$prefix=array();
		$order=array('_id', 'asc');
		$fields=array('_id', '_title');
		$ds=0;
		$business='wiki';

		$list = $this->lSearch->select($page, $pagesize, $keyword, $opts, $prefix, $order, $fields, $ds, $business);
		var_dump($list);
		$ids = array();
		foreach ( $list['list'] as $i => $item ) {
			$id = trim($item['_id']);
			if ( !in_array($id, $ids) ) {
				array_push($ids, $id);
			}
		}
		$ret = $this->lSearch->removeWiki($ids);
		return $ret;
	}

	protected function test() {
		$page=1;
		$pagesize=10000;
		$keyword='';
		$opts=array();
		$prefix=array();
		$order=array('_id', 'asc');
		$fields=array();
		$ds=0;
		$business='wiki';

		$list = $this->lSearch->select($page, $pagesize, $keyword, $opts, $prefix, $order, $fields, $ds, $business);
		$result = array(
			'引擎中的 Wiki 数据' => $list,
			'新模型 Wiki 数据量' => $this->mNewWiki->count(),
			'字典中的数据' => $this->lSearch->getDictWords(C('ENGINE.PARSEWORDS_ID')),
		);
		var_export($result);
	}

	/**
	 * 为百科词条数据升级更新创建脚本逻辑
	 */
	public function run() {
		$this->init();

		$words = array();	// 待创建的词典
		$docs = array();	// 待创建的文档

		G('start_upgrade_wiki');
		$pagesize = 1000;	// 3094
		$pagemax = 5;
		// $pagesize = 5;
		for ( $page = 1; $page<=$pagemax; $page++ ) {
			$list = $this->mWiki->order('id asc')->page($page, $pagesize)->select();
			foreach ( $list as $i => $item ) {
				$tags = $this->updateTagids($item['tags']);
				$item['tags'] = implode(' ', array_keys($tags));
				$item['tagids'] = ','.implode(',', array_values($tags)).',';
				$record = $this->convert($item);
				$status = intval($record['status']);
				if ( $status==9 ) {
					// 追加数据文档到待追回的业务数据列表中
					array_push($docs, $record);
					// 追回到待设置的词条字典中
					array_push($words, trim($record['title']));
				}
				$ret = $this->mNewWiki->data($record)->add();
			}
		}
		// echo PHP_EOL, PHP_EOL, '<hr>', PHP_EOL, PHP_EOL;
		$ret = $this->lSearch->createWiki($docs);
		// var_dump($ret, $docs);
		// echo PHP_EOL, PHP_EOL, '<hr>', PHP_EOL, PHP_EOL;
		$ret = $this->lSearch->setDictWords($words, C('ENGINE.PARSEWORDS_ID'));
		// var_dump($ret, $words);
		// echo PHP_EOL, PHP_EOL, '<hr>', PHP_EOL, PHP_EOL;
		G('end_upgrade_wiki');
		var_dump(array(
			'Upgrade Wiki',
			G('start_upgrade_wiki', 'end_upgrade_wiki', 'm'),
			G('start_upgrade_wiki', 'end_upgrade_wiki', 3),
			'新模型 Wiki 数据量'=>$this->mNewWiki->count(),
		));

		G('start_upgrade_wikihistory');
		$pagemax = 1;
		$pagesize = 1000;
		for ( $page = 1; $page<=$pagemax; $page++ ) {
			$list = $this->mWikiHistory->order('pkid asc')->page($page, $pagesize)->select();
			foreach ( $list as $i => $item ) {
				$tags = $this->updateTagids($item['tags']);
				$item['tags'] = implode(' ', array_keys($tags));
				$item['tagids'] = ','.implode(',', array_values($tags)).',';
				$record = $this->convert($item);
				$ret = $this->mNewWikiHistory->data($record)->add();
			}
		}
		G('end_upgrade_wikihistory');
		var_dump(array(
			'Upgrade Wikihistory',
			G('start_upgrade_wikihistory', 'end_upgrade_wikihistory', 'm'),
			G('start_upgrade_wikihistory', 'end_upgrade_wikihistory', 3),
			'新模型 WikiHistory 数据量'=>$this->mNewWikiHistory->count(),
		));

	}

	// 老模型的数据 向 新模型数据结构 进行转换
	protected function convert( $item ) {
		$extra = json_encode(array(
			'coverinfo' => trim($item['coverinfo']),
			'src_url' => trim($item['src_url']),
		));
		$album = json_encode(array(
			'title' => '',
			'cover' => array('pc'=>'', 'h5'=>''),
			'list' => array(),
		));
		$seo = json_encode(array(
			'title' => trim($item['seo_title'])!='' ? trim($item['seo_title']) : '',
			'keywords' => trim($item['seo_keywords'])!='' ? trim($item['seo_keywords']) : '',
			'description' => trim($item['seo_description'])!='' ? trim($item['seo_description']) : '',
		));
		$basic = json_encode(array());
		$rel = json_encode(array(
			'news' => json_decode($item['rel_news'], true),
			'houses' => json_decode($item['rel_house'], true),
			'companies' => array(),
			'figures' => array(),
		));

		$record = array(
			'id' => $item['id'],
			'version' => $item['version'],
			'status' => $item['status'],
			'hits' => $item['hits'],
			'title' => $item['title'],
			'pinyin' => $item['pinyin'],
			'firstletter' => $item['firstletter'],
			'cateid' => $item['cateid'],
			'content' => $item['content'],
			'cover' => $item['cover'],
			'editorid' => $item['editorid'],
			'editor' => $item['editor'],
			'media' => $item['media'],
			'ctime' => $item['ctime'],
			'ptime' => $item['ptime'],
			'utime' => $item['utime'],
			'src_type' => $item['src_type'],
			'tags' => $item['tags'],
			'tagids' => $item['tagids'],
			'seo' => $seo,
			'basic' => $basic,
			'extra' => $extra,
			'album' => $album,
			'rel' => $rel,
		);
		if ( isset($item['pkid']) ) {
			$record['pkid'] = trim($item['pkid']);
		}
		return $record;
	}

	// 更新老数据模型中的标签编号
	protected function updateTagids($tagids) {
		if ( trim($tagids)=='' ) {
			return [];
		}
		$tags = explode(' ', trim($tagids));
		$_tags = array();
		foreach ( $tags as $i => $tag ) {
			$tag = trim($tag);
			if ( !array_key_exists($tag, $this->tagsinfo) ) {
				array_push($_tags, $tag);
			}
		}
		if ( !empty($_tags) ) {
			$ret = $this->lTag->getTagnamesByTags($_tags);
			foreach ( $ret as $i => $tag ) {
				$this->tagsinfo[$tag['name']] = $tag['id'];
			}
		}
		return array_intersect_key($this->tagsinfo, array_flip($tags));
	}

}










