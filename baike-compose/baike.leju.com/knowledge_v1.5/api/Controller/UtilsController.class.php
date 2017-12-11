<?php
/**
 * 工具集合
 */
namespace api\Controller;
use Think\Controller\RestController;

// 执行时忽略执行时间限制
set_time_limit(0);

class UtilsController extends BaseController {
	// 原始数据 csv 半角逗号分隔
	protected $file_source = 'fang.words.csv';
	// 中间结果，排重，并过滤无效数据之后的数组格式
	protected $file_middle = 'fang.words.php';
	// 转换操作结果日志文档
	protected $file_result = 'result.txt';
	// 分词字典数据
	protected $file_dict = 'wiki.dict.php';
	// 联想词业务索引数据集合
	protected $file_suggest = 'wiki.suggest.php';
	// 业务索引数据集合
	protected $file_logic = 'wiki.logic.php';
	// 数据表数据导入
	protected $file_mysql = 'wiki.datatable.sql';

	protected $flush = false;	// 是否强制更新缓存
	protected $engine = null;	// 接口对象
	protected $model = null;	// 数据模型对象
	protected $pinyin = null;	// 拼音转换对象
	protected $data = array();	// 数据中间结果

	public function __construct () {
		parent::__construct();
		// 是否强制更新缓存
		$this->flush = I('flush', 0, 'intval') == 1 ? true : false;
		$this->engine = D('Search', 'Logic', 'Common');
		// $this->model = D('Wiki', 'Model', 'Common');
		$this->pinyin = D('Pinyin', 'Logic', 'Common');
	}
	/**
	 * 1. 将 csv 原始数据
	 *	=== Pass ===
	 */
	public function importSource() {
		echo '<h2>从 csv 获取原始数据</h2>', PHP_EOL;
		// 获取源数据
		$this->getSource($this->flush);
		echo '>>> 共有 ', count($this->data), ' 条数据', PHP_EOL;
	}

	/**
	 * Feature 1:
	 *	Import the data to Table
	 * 	and export the Import SQL script to file
	 */
	public function importToTable() {
		$this->getSource($this->flush);
		echo '<h2>源数据导入数据表(`wiki`)</h2>', PHP_EOL;
		echo '<p>命令行下通过 _documents/utils/import_words/run.php 进行导入</p>', PHP_EOL;
	}

	/**
	 * Feature 2:
	 *	Import the words to Dict for analyze parse
	 *	and export the Dict array files 
	 *	=== Pass ===
	 */
	public function importToDict() {
		$this->getSource($this->flush);
		echo '<h2>向分词字典导入百科词条</h2>', PHP_EOL;

		$list = & $this->data;
		if ( !empty($list) ) {
			$dict_words = array();
			foreach ( $list as $md5 => $item ) {
				$word = trim($item[0]);
				array_push($dict_words, $word);
			}
			// 通过服务接口向字典中追回词条
			$ret = $this->engine->appendDictWords($dict_words, 'dict_wiki');
		} else {
			$ret = false;
		}
		$msg = ($ret!==false) ?
				'导入到 [百科分词字典] 成功。数据源中词条共有 '.count($list).' 个，导入字典共 '.$ret.' 个。'
				:
				'导入到 [百科分词字典] 失败。源数据集合为空。';
		echo '>>> ', $msg, PHP_EOL;
	}

	/**
	 * Feature 3:
	 *	Import the words to Suggest Indeces
	 *	and export the Suggest array data
	 *	=== Pass ===
	 */
	public function importToSuggest() {
		$this->getSource($this->flush);
		echo '<h2>源数据导入联想词索引(suggest.wiki)</h2>', PHP_EOL;

		$list = & $this->data;
		if ( !empty($list) ) {
			$docs = array();
			foreach ( $list as $md5 => $item ) {
				$word = trim($item[0]);
				$doc = array(
					'_id' => $word,
					'word' => $word,
					'hits' => 0,
					'score' => 0,
				);
				array_push($docs, $doc);
			}
			// 通过服务向联想搜索词条索引中添加新词条索引
			$ret = $this->engine->create($docs, 'suggest.wiki', 'word');
		} else {
			$ret = false;
		}
		$msg = ($ret!==false) ?
				'导入到 [联想词索引] 成功。数据源中词条共有 '.count($list).' 个。'
				:
				'导入到 [联想词索引] 失败。源数据集合为空。';
		echo '>>> ', $msg, PHP_EOL;
	}

	/**
	 * Feature 4:
	 *	Import the words to Logic Indeces
	 *	and export the Logic array data
	 *	>>> 使用 run 生成的 中间文件导入
	 *	=== Pass ===
	 */
	public function importToLogic() {
		echo '<h2>源数据导入业务索引(wiki_logic)</h2>', PHP_EOL;
		$file_logic = WEB_ROOT . '_documents/utils/import_words/' . $this->file_logic;

		if ( file_exists($file_logic) ) {
			$batches = 100;
			$list = include($file_logic);
			echo 'total ', count($list), PHP_EOL;
			if ( !empty($list) ) {
				G('start');
				$lists = array_chunk($list, $batches);
				foreach ( $lists as $i => &$list ) {
					foreach ( $list as $_i => &$doc ) {
						$doc['_origin']['id'] = intval($doc['_id']);
						$doc['_id'] = trim($doc['_title']);
					}
					// 通过服务向联想搜索词条索引中添加新词条索引
					$ret = $this->engine->create($list, 'wiki');
				}
				G('end');
				$cost = G('start', 'end', 3);
				$mem = G('start', 'end', 'm');
			} else {
				$ret = false;
			}
			$msg = ($ret!==false) ?
					'导入到 [业务索引] 成功。数据源中词条共有 '.count($list).' 个。'.PHP_EOL.
					'耗时 '.$cost.' 秒。使用内存 '.$mem.' KB。'.PHP_EOL
					:
					'导入到 [业务索引] 失败。源数据集合为空。';
			echo '>>> ', $msg, PHP_EOL;
		} else {
			$ret = false;
			echo '原数据文件未找到，请先在命令行下运行 run 来生成导入文件。', PHP_EOL;
		}
	}


	/* 作废
	protected function convertDataToRecord( $item ) {
		$status = 9;	// 9 已发布
		$cateid = 0;
		$src_type = 1;
		$src_url = 'http://zhishi.fang.com/fcchkc.html';

		$result = array();

		$result['status'] = $status;
		$result['cateid'] = $cateid;
		$result['src_type'] = $src_type;
		$result['src_url'] = $src_url;
		$title = $result['title'] = $item[0];
		$content = $result['content'] = $item[1];
		$version = $result['version'] = NOW_TIME;
		$ctime = $result['ctime'] = NOW_TIME;
		$ptime = $result['ptime'] = NOW_TIME;
		$utime = $result['utime'] = NOW_TIME;
		$rel_news = $result['rel_news'] = _EMPTY;
		$rel_house = $result['rel_house'] = _EMPTY;
		$pinyin = $result['pinyin'] = $this->pinyin->get_pinyin($item[0]);
		$firstletter = $result['firstletter'] = strtoupper(substr(trim($pinyin), 0, 1));
		// 解标签
		$_tags = $this->engine->analyze($content, true, 5, 'dict_tags');
		$tags = array();
		foreach ( $_tags as $i => $tag ) {
			array_push($tags, $tag['word']);
		}
		$tags = $result['tags'] = implode(' ', $tags);

		return $result;
	}
	*/

	protected function getSource( $flush=false ) {
		$result = false;

		$file_source = WEB_ROOT . '_documents/utils/import_words/' . $this->file_source;
		$file_middle = DATA_PATH . $this->file_middle;
		$file_result = DATA_PATH . $this->file_result;

		if ( $flush==true || !file_exists($file_middle) ) {
			echo '处理数据', PHP_EOL;
			G('get_source_start');
			$data = explode(PHP_EOL, file_get_contents($file_source));
			array_shift($data);

			$dups = array();	// 重复数据
			$fail = array();	// 失败的数据
			$list = array();	// 数据集合
			$words = array();	// 分词字典数据
			foreach ( $data as $i => &$item ) {
				$p = strpos($item, ',');
				$word = substr($item, 0, $p);
				$content = substr($item, $p+1);
				$md5 = md5(trim($word));
				$content = trim($content, '"');
				$content = str_replace(array('?',','), array(' ','，'), $content);
				if ( !$word ) {
					continue;
				}
				if ( array_key_exists($md5, $list) ) {
					$dups[] = array('key'=>$md5, 'word'=>$word);
				} else {
					// 字典添加
					array_push($words, $word);
				}
				if ( trim($content)!='' ) {
					$list[$md5] = array($word, $content);
				} else {
					$fail[] = $item;
				}
			}
			$this->data = &$list;
			$result = true;

			G('get_source_end');
			// 中间结果数据写入文件
			file_put_contents($file_middle, '<?php'.PHP_EOL.'return '.var_export($list, true).';');
			// 日志数据输出
			$log_info = '====== 基本转换 ======'. PHP_EOL.
				 print_r($dups, true). PHP_EOL.
				 '-- 数据源数据量 : '. count($data). ' 条'. PHP_EOL.
				 '-- 成功转换的数据量 : '. count($list). ' 条'. PHP_EOL.
				 '-- 词条数量 : '. count($words). ' 条'. PHP_EOL.
				 '-- 基本转换操作消耗 : '. G('get_source_start', 'get_source_end', 3).
				 ' seconds, '. G('get_source_start', 'get_source_end', 'm'). ' KB' . PHP_EOL;
			file_put_contents($file_result, $log_info);
			echo '>>> 处理源数据日志 <<<', PHP_EOL, $log_info;
			unset($data);
		} else {
			if ( file_exists($file_result) ) {
				echo '>>> 缓存获取日志 <<<', PHP_EOL, file_get_contents($file_result.$_file_end);
				$result = true;
			} else {
				echo '前一次的操作记录丢失！', PHP_EOL;
			}
			if ( empty($this->data) ) {
				$list = include($file_middle);
				$this->data = &$list;
				$result = true;
			}
		}
		return $result;
	}
}
