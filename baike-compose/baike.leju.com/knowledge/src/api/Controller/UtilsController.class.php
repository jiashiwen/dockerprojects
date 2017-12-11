<?php
/**
 * 工具集合
 */
namespace api\Controller;
use Think\Controller\RestController;

// 执行时忽略执行时间限制
set_time_limit(0);

class UtilsController extends BaseController {
	protected $flush = false;	// 是否强制更新缓存
	protected $engine = null;	// 接口对象
	protected $model = null;	// 数据模型对象
	protected $pinyin = null;	// 拼音转换对象
	protected $data = array();	// 数据中间结果

	public function __construct () {
		parent::__construct();
		// 是否强制清除原有数据
		$this->flush = I('flush', 0, 'intval') == 1 ? true : false;
		$this->conn = array(
			'db_type'    =>   'mysql',
			'db_host'    =>   '10.204.12.29',
			'db_user'    =>   'lejuuser',
			'db_pwd'     =>   'r%fCwhs@XCW0nlI!',
			'db_port'    =>    3308,
			'db_name'    =>    'knowledge', 
		);
	}

	public function getWikis() {
		$mWiki = M('\Common\Model\WikiModel:Wiki','',$this->conn);
		var_dump($mWiki);
		$list = $mWiki->field(['id', 'title', 'ctime'])->where(['cateid'=>2])->order('id DESC')->select();
		var_dump($list);
		exit;
			// 强制删除已存在的同名词条
			$mWiki = D('Wiki', 'Model', 'Common');
			$where = ['cateid'=>2];
			$names = []; foreach ( $data as $i=>$item ) { $name=trim($item[0]); $names[$name]=$name; }
			$where['title'] = ['in', array_values($names)];
			$fields = ['id', 'title'];
			$list = $mWiki->field($fields)->where($where)->select();
			$count_wiki = 0;
			$count_history = 0;
			if ( $list ) {
				$count_wiki = count($list);
				$ids = []; foreach ( $list as $i=>$item ) { array_push($ids,intval($item['id'])); }
				$mHistory = D('WikiHistory', 'Model', 'Common');
				$count_history = $mHistory->where(['id'=>['in', $ids]])->count();
				$mHistory->where(['id'=>['in', $ids]])->delete();
				$list = $mWiki->where($where)->delete();
				echo '>>> 共有 ', $count_wiki, ' 条词条数据, ', $count_history, ' 条历史数据 清理完成', PHP_EOL;
				echo '<pre>', print_r($list, true), '</pre>', PHP_EOL;
			} else {
				echo '>>> 没有需要清理的词条', PHP_EOL;
			}

	}

	public function importCricCompanies() {
		echo '<h2>从 数据文件中 获取原始数据</h2>', PHP_EOL;
		// 获取源数据
		$filename = '20171031_cric_companies.txt';
		$file_source = WEB_ROOT . 'p/data/' . $filename;
		if ( !file_exists($file_source) ) {
			echo '原始数据文件不存在', PHP_EOL;
			exit;
		}
		$data = explode(PHP_EOL, file_get_contents($file_source));

		$mWiki = D('Wiki', 'Model', 'Common');
		if ( $this->flush ) {
			// 强制删除已存在的同名词条
			$where = ['src_type'=>2];
			$fields = ['id', 'title', 'company_cric_id'];
			$list = $mWiki->field($fields)->where($where)->select();
			if ( $list ) {
				$ret = $mWiki->where($where)->delete();
				echo '>>> 共有 ', count($list), ' 条公司数据 清理完成', PHP_EOL;
				echo '<pre>', print_r($list, true), var_export($ret, true), '</pre>', PHP_EOL;
			} else {
				echo '>>> 没有需要清理的词条', PHP_EOL;
			}
		}

		$company = [];	// 整理后的公司树
		$_cric = '';	// 前一个cricid
		$_p = [];	// 前一个cric对应的公司信息
		$inx = 0;
		$lPinyin = D('Pinyin', 'Logic', 'Common');
		$all_childs = [];
		foreach ( $data as $i => &$line ) {
			$line = explode(",", trim($line));
			$cricid = trim($line[0]);
			if ( $cricid !== $_cric ) {
				$_p = []; $_cric = $cricid;
				$title = $_p['title'] = trim($line[1]);
				$stname = $_p['stname'] = trim($line[3]);
				$short = $_p['short'] = trim($line[5]);
				$city = '';
				$pinyin = $lPinyin->get_pinyin($title);
				$firstletter = strtoupper(substr($pinyin, 0, 1));
			} else {
				$parent_id = $_p['id'];
				$short = $_p['short'];
				$city = trim($line[4]);
				$title = $city . $short;
				$stname = $short . $city;
				$pinyin = $lPinyin->get_pinyin($title);
				$firstletter = strtoupper(substr($pinyin, 0, 1));
			}
			if ( !array_key_exists($cricid, $company) ) {
				$record = [
					'status'=>1,	// 状态 : 草稿
					'cateid'=>1,	// 类别 : 公司
					'src_type'=>2,	// 来源 : cric 导入
					'title'=>$title,
					'stname'=>$stname,
					'short'=>$short,
					'pinyin'=>$pinyin,
					'firstletter'=>$firstletter,
					'city'=>$city,
					'company_cric_id'=>$cricid,	// CRIC ID
					'company_parent_id'=>0,	// 开发商无上级公司
					'ctime'=>NOW_TIME,
					'utime'=>NOW_TIME,
					'version'=>NOW_TIME,
				];
				// add top level company
				$id = $mWiki->data($record)->add();
				$company[$cricid] = [
					'id'=>$id,
					'title'=>$title,
					'stname'=>$stname,
					'short'=>$short,
					'city'=>$city,
					'company_cric_id'=>$cricid,
					'company_parent_id'=>0,
					'child'=>[],
				];
				$inx += 1;
			} else {
				array_push($all_childs, [
					'status'=>1,	// 状态 : 草稿
					'cateid'=>1,	// 类别 : 公司
					'src_type'=>2,	// 来源 : cric 导入
					'title'=>$title,
					'stname'=>$stname,
					'short'=>$short,
					'pinyin'=>$pinyin,
					'firstletter'=>$firstletter,
					'city'=>$city,
					'company_cric_id'=>$cricid,	// CRIC ID
					'company_parent_id'=>$company[$cricid]['id'],
					'ctime'=>NOW_TIME,
					'utime'=>NOW_TIME,
					'version'=>NOW_TIME,
				]);
				array_push($company[$cricid]['child'], [
					'title'=>$title,
					'stname'=>$stname,
					'short'=>$short,
					'city'=>$city,
					'company_cric_id'=>$cricid,
					'company_parent_id'=>$company[$cricid]['id'],
				]);
				$inx += 1;
			}
		}
		if ( !empty($all_childs) ) {
			$mWiki->addAll($all_childs);
		}
		echo '>>> 共有 ', count($data), ' 条数据', PHP_EOL;
		echo '>>> 共有 ', count($company), ' 条数据', PHP_EOL;
		echo '>>> 验证 ', $inx, ' 条数据', PHP_EOL;
		echo '<pre>', print_r($company, true), '</pre>', PHP_EOL;
	}


	public function importSource() {
		$filename = I('get.fn', '', 'trim');
		echo '<h2>从 数据文件中 获取原始数据</h2>', PHP_EOL;
		// 获取源数据
		$ret = $this->getSource($filename);
		if ( $ret ) {
			echo '>>> 共有 ', count($this->data), ' 条数据', PHP_EOL;
			echo '<pre>', print_r($this->data, true), '</pre>', PHP_EOL;
		} else {
			echo '原始数据文件不存在', PHP_EOL;
			exit;
		}

		$data = &$this->data;
		if ( $this->flush ) {
			// 强制删除已存在的同名词条
			$mWiki = D('Wiki', 'Model', 'Common');
			$where = ['cateid'=>2];
			$names = []; foreach ( $data as $i=>$item ) { $name=trim($item[0]); $names[$name]=$name; }
			$where['title'] = ['in', array_values($names)];
			$fields = ['id', 'title'];
			$list = $mWiki->field($fields)->where($where)->select();
			$count_wiki = 0;
			$count_history = 0;
			if ( $list ) {
				$count_wiki = count($list);
				$ids = []; foreach ( $list as $i=>$item ) { array_push($ids,intval($item['id'])); }
				$mHistory = D('WikiHistory', 'Model', 'Common');
				$count_history = $mHistory->where(['id'=>['in', $ids]])->count();
				$mHistory->where(['id'=>['in', $ids]])->delete();
				$list = $mWiki->where($where)->delete();
				echo '>>> 共有 ', $count_wiki, ' 条词条数据, ', $count_history, ' 条历史数据 清理完成', PHP_EOL;
				echo '<pre>', print_r($list, true), '</pre>', PHP_EOL;
			} else {
				echo '>>> 没有需要清理的词条', PHP_EOL;
			}
		}

		// 导入
		if ( $data ) {
			$records = [];
			foreach ( $data as $i => $item ) {
				array_push($records, $this->convertToPersonWiki($item));
			}
			$ret = $mWiki->addAll($records);
			echo '>>> 共有 ', count($records), ' 条词条数据需要导入', PHP_EOL;
			echo '<pre>', print_r($records, true), '</pre>', PHP_EOL;
			echo '<pre>', var_export($ret, true), '</pre>', PHP_EOL;
		}
	}


	protected function convertToPersonWiki( $data ) {
		$title = trim($data[0]);
		$desc = trim($data[1]);
		$content = trim($data[2]);
		$record = [];
		$record['status'] = 1; // 草稿
		$record['cateid'] = 2; // 人物
		$record['version'] = NOW_TIME;
		$record['ctime'] = NOW_TIME;
		$record['ptime'] = 0;
		$record['utime'] = NOW_TIME;
		$record['title'] = $title;
		$record['summary'] = $desc;
		$record['content'] = $content;
		$pinyin = strtolower(D('Pinyin', 'Logic', 'Common')->get_pinyin($title));
		$record['pinyin'] = $pinyin;
		$record['firstletter'] = strtoupper(substr($pinyin,0,1));
		$record['seo'] = json_encode([
			'title' => $title,
			'keywords' => '',
			'description' => $desc,
		]);
		return $record;
	}


	protected function getSource( $filename='' ) {
		$result = false;
		$file_source = WEB_ROOT . 'p/data/' . $filename;
		if ( file_exists($file_source) ) {
			echo '获取原始数据 [', $filename, ']', PHP_EOL;
			G('get_source_start');
			$data = explode(PHP_EOL, file_get_contents($file_source));
			foreach ( $data as $i => &$line ) {
				$line = explode("\t", trim($line, "\t"));
			}
			$this->data = &$data;
			G('get_source_end');
			$result = true;
		}
		return $result;
	}
}
