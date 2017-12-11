<?php
/**
 * 工具脚本控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class UtilsController extends BaseController {

	public function index() {
		$this->display('index');
	}

	/**
	 * 重新推送百科到新闻池
	 * @date: 2017-10-12
	 * @developer: Robert <yongliang1@leju.com>
	 * /Utils/rePushDataToInfos/?type=show&name=wiki&page=1&pagesize=10000
	 */
	public function rePushDataToInfos() {
		$result = array(
			'status'=>true,
		);
		$type = I('get.type', 'show', 'strtolower');	// 执行的操作类型
		$types = array('show', 'flush', 'push');	// 允许执行的操作类型
		if ( !in_array($type, $types) ) {
			$this->ajax_error('Error Type Flag');
		}
		$S = array(
			'bk' => array(
				'model'=>'Knowledge',
				'publish'=>'BaikePublish',
				'where'=>array('status'=>9),
			),
			'wiki' => array(
				'model'=>'Wiki',
				'publish'=>'WikiPublish',
				'where'=>array('status'=>9),
			),
			'qa' => array(
				'model'=>'Question',
				'publish'=>'QuestionPublish',
				'where'=>array('status'=>array('in', [21,22,23])),
			),
		);
		$name = I('get.name', 'wiki', 'strtolower'); // 要重新推送的业务名称
		if ( !array_key_exists($name, $S) || $name!=='wiki' ) {
			$this->ajax_error('Error Source Name');
		}

		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 100, 'intval');
		$result['params'] = array(
			'page' => $page,
			'pagesize' => $pagesize,
			'type' => $type,
			'name' => $name,
		);

		$model = D($S[$name]['model'], 'Model', 'Common');
		$order = 'id ASC';
		$where = $S[$name]['where'];
		$total = $model->where($where)->count();
		$list = $model->where($where)->order($order)->page($page, $pagesize)->select();

		$out = array();
		foreach ( $list as $i => $item ) {
			$out[$i] = array('id'=>$item['id'], 'title'=>$item['title']);
		}
		$result['total'] = $total;
		$result['list'] = $out;

		$logic = D($S[$name]['publish'], 'Logic', 'Common');
		if ( $type=='flush' ) {
			if ( $name==='wiki' ) {
				$ret = $logic->batchFlush($list);
			}
		}
		if ( $type=='push' ) {
			if ( $name==='wiki' ) {
				$ret = $logic->batchPublish($list);
			}
		}
		$result['result'] = $ret;
		$this->ajax_return($result);
	}

	// 词条批量向标签系统推送
	public function reSyncWikiToTags() {
		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 100, 'intval');

		$result = ['status'=>false];
		$result['params'] = [
			'page' => $page,
			'pagesize' => $pagesize,
		];

		$model = D('Wiki', 'Model', 'Common');
		$order = 'id ASC';
		$fields = ['id', 'cateid', 'summary', 'title', 'stname', 'cover'];
		$where = ['cateid'=>['in', [1,2]]];
		$total = $model->where($where)->count();
		$list = $model->field($fields)->where($where)->order($order)->page($page, $pagesize)->select();
		$result['total'] = $total;

		if ( empty($list) ) {
			$this->ajax_return($result);
		}

		$tags = D('Tags', 'Logic', 'Common');
		foreach ( $list as $i => &$item ) {
			$status = intval($item['status']);
			if ( $status==9 ) {
				$ret = $tags->syncToTag($item);
				$tagid = intval($ret);
				$item['unique_tag_id'] = $tagid;
				$model->where(['id'=>$item['id']])->data(['unique_tag_id'=>$tagid])->save();
			} else {
				$ret = $tags->syncToTag($item, true);
			}
		}
		$result['list'] = $list;
		$result['status'] = true;
		$this->ajax_return($result);
	}

	public function rePushLDQDataToInfos() {
		$result = ['status'=>true,];
		$type = I('get.type', 'show', 'strtolower');	// 执行的操作类型
		$types = array('show', 'flush', 'push');	// 允许执行的操作类型
		if ( !in_array($type, $types) ) {
			$this->ajax_error('Error Type Flag');
		}
		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 100, 'intval');
		$result['params'] = [
			'page' => $page,
			'pagesize' => $pagesize,
			'type' => $type,
		];

		$offset = ($page-1)*$pagesize;
		$limit = "LIMIT {$offset}, {$pagesize}";

		$model = D('CompanyQuestions', 'Model', 'Common');
		if ( $type=='flush' ) {
			$countsql = "SELECT count(`id`) AS 'cnt' FROM `company_questions`";
			$list_sql = "SELECT `id`, `title` FROM `company_questions` ORDER BY id ASC {$limit}";
		}
		if ( $type=='push' || $type=='show' ) {
			$where = " WHERE q.`status`='2'";
			$countsql = "SELECT count(q.`id`) as 'cnt' FROM `companies` c LEFT JOIN `company_questions` q ON c.`wiki_id`=q.`company_id` {$where}";
			$list_sql = "SELECT q.* FROM `companies` c LEFT JOIN `company_questions` q ON c.`wiki_id`=q.`company_id` {$where} ORDER BY q.`id` ASC {$limit}";
		}

		$ret = $model->query($countsql);
		$total = intval($ret[0]['cnt']);
		$list = $model->query($list_sql);

		$out = array();
		foreach ( $list as $i => $item ) {
			$out[$i] = array('id'=>$item['id'], 'title'=>$item['title']);
		}
		$result['total'] = $total;
		$result['list'] = $out;

		$lInfos = D('Infos', 'Logic', 'Common');
		if ( $type=='flush' ) {
			$ret = $lInfos->batchPushNewsPool($list, $lInfos::TYPE_LDQ, true);
		}
		if ( $type=='push' ) {
			$ret = $lInfos->batchPushNewsPool($list, $lInfos::TYPE_LDQ);
		}
		$result['result'] = $ret;
		if ( I('get.debug', 0, 'intval') == 35940 ) {
			$result['debug'] = [
				'countsql' => $countsql,
				'list_sql' => $list_sql,
			];
		}
		$this->ajax_return($result);
	}

	public function rePushLDADataToInfos() {
		$result = ['status'=>true,];
		$type = I('get.type', 'show', 'strtolower');	// 执行的操作类型
		$types = array('show', 'flush', 'push');	// 允许执行的操作类型
		if ( !in_array($type, $types) ) {
			$this->ajax_error('Error Type Flag');
		}
		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 100, 'intval');
		$result['params'] = [
			'page' => $page,
			'pagesize' => $pagesize,
			'type' => $type,
		];

		$offset = ($page-1)*$pagesize;
		$limit = "LIMIT {$offset}, {$pagesize}";

		$model = D('CompanyAnswers', 'Model', 'Common');
		if ( $type=='flush' ) {
			$countsql = "SELECT count(`id`) AS 'cnt' FROM `company_answers`";
			$list_sql = "SELECT `id`, `reply` FROM `company_answers` ORDER BY id ASC {$limit}";
		}
		if ( $type=='push' || $type=='show' ) {
			$where = " WHERE a.`status`='2'";
			$countsql = "SELECT count(a.`id`) as 'cnt' FROM `companies` c LEFT JOIN `company_answers` a ON c.`wiki_id`=a.`company_id` {$where}";
			$list_sql = "SELECT a.* FROM `companies` c LEFT JOIN `company_answers` a ON c.`wiki_id`=a.`company_id` {$where} ORDER BY a.`id` ASC {$limit}";
		}

		$ret = $model->query($countsql);
		$total = intval($ret[0]['cnt']);
		$list = $model->query($list_sql);

		$out = array();
		foreach ( $list as $i => $item ) {
			$out[$i] = array('id'=>$item['id'], 'reply'=>$item['reply']);
		}
		$result['total'] = $total;
		$result['list'] = $out;

		$lInfos = D('Infos', 'Logic', 'Common');
		if ( $type=='flush' ) {
			$ret = $lInfos->batchPushNewsPool($list, $lInfos::TYPE_LDA, true);
		}
		if ( $type=='push' ) {
			$ret = $lInfos->batchPushNewsPool($list, $lInfos::TYPE_LDA);
		}

		$result['result'] = $ret;
		if ( I('get.debug', 0, 'intval') == 35940 ) {
			$result['debug'] = [
				'countsql' => $countsql,
				'list_sql' => $list_sql,
			];
		}
		$this->ajax_return($result);
	}

	/**
	 * 导入 CRIC 的企业数据
	 */
	public function syncCRICCompanies() {
		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 1000, 'intval');

		// TODO
		$api = 'http://cric.yiju.org/SaleControl/Interface/LJCJDeveloperInfo';
		$params = array('page'=>$page, 'pagesize'=>$pagesize);

		G('start');
		$ret = curl_get($api, $params);
		G('end');

		$total = 0;
		$status = false;
		if ( $ret['status'] ) {
			$ret = json_decode($ret['result'], true);
			if ( $ret['status'] ) {
				$status = true;
				$list = $ret['data'];
				$total = intval($ret['iTotal']);
			}
		} else {
			$list = [];
		}

		$flushdb = false;
		$records = array();
		if ( $list ) {
			$model = D('Wiki', 'Model', 'Common');
			$where = array('src_type' => 2,);

			$ret = $model->where($where)->count();
			if ( $ret>0 ) {
				$model->where($where)->delete();
				$flushdb = $ret;
			}

			$lPy = D('Pinyin', 'Logic', 'Common');
			foreach ( $list as $i => $item ) {
				$_cric_id = trim($item['sDeveloperID']);
				$_stitle = trim($item['sShortName']);
				$_title = trim($item['sDeveloperName']);
				$_py = $lPy->get_pinyin($_title);
				$_fl = strtoupper(substr($_py, 0, 1));
				$record = array(
					'status' => 1, // 草稿状态
					'title' => $_title,
					'basic' => json_encode(array('stname'=>$_stitle)),
					'pinyin' => $_py,
					'firstletter' => $_fl,
					'cateid' => 1, // 导入到公司企业类别下
					'version' => NOW_TIME,
					'ctime' => NOW_TIME,
					'utime' => NOW_TIME,
					'src_type' => 2, // 来源 CRIC
					'seo' => json_encode(array('title'=>$_title)),
					'company_cric_id' => $_cric_id,
				);
				array_push($records, $record);
			}
			if ( !empty($records) ) {
				$ret = $model->addAll($records);

				$fields = array('id', 'company_cric_id', 'title', 'pinyin', 'firstletter', 'basic');
				$mounts = $model->where($where)->count();
				$review = $model->where($where)->field($fields)->page($page, $pagesize)->select();
				$_records = [];
				foreach ( $review as $i => $item ) {
					$item['basic'] = json_decode($item['basic'], true);
					array_push($_records, array(
						'id'=>$item['id'],
						'cricid'=>$item['company_cric_id'],
						'title'=>$item['title'],
						'pinyin'=>$item['pinyin'],
						'firstletter'=>$item['firstletter'],
						'stitle'=>$item['basic']['stname'],
					));
				}
			}
		}
		$result = array(
			'status' => $status,
			'params' => array('page'=>$page, 'pagesize'=>$pagesize),
			'flushdb' => $flushdb,
			'source' => array('list'=>$list, 'total'=>$total),
			'records' => array('list'=>$records, 'total'=>count($records)),
			'review' => array('list'=>$_records, 'total'=>$mounts),
			'_profile' => array(
				'cost' => G('start', 'end', 3),
				'mem' => G('start', 'end', 'm'),
			),
		);
		$this->ajax_return($result);
	}

	public function getCRICCompaniesAreas() {
		$page = 1; $pagesize = 2000;
		$model = D('Wiki', 'Model', 'Common');
		$where = array('src_type' => 2,);
		$fields = array('id', 'company_cric_id', 'title', 'pinyin', 'firstletter', 'basic');
		$mounts = $model->where($where)->count();
		$review = $model->where($where)->field($fields)->page($page, $pagesize)->select();
		// 获取区域
		$api = 'http://cric.yiju.org/SaleControl/Interface/LJCJDeveloperInCity';
		$params = array('page'=>$page, 'pagesize'=>$pagesize);
		G('start');
		$ret = curl_get($api, $params);
		G('end');
		$total = 0;
		$status = false;
		if ( $ret['status'] ) {
			$ret = json_decode($ret['result'], true);
			if ( $ret['status'] ) {
				$status = true;
				$list = $ret['data'];
				$total = intval($ret['iTotal']);
			}
		} else {
			$list = [];
		}
		if ( empty($list) ) {
			exit('没有城市占位');
		}
		$areas = [];	// 所有城市区域占位
		$_records = [];
		foreach ( $list as $i => $area ) {
			$cricid = $area['sDeveloperID'];
			if ( !array_key_exists($cricid, $areas) ) {
				$areas[$cricid] = [];
			}
			array_push($areas[$cricid], trim($area['sCityName']));
		}

		// $diff 中是 开发商有，但对应的城市占位没有
		$lds = [];
		foreach ( $review as $i => $ld ) {
			$lds[$ld['company_cric_id']] = $ld['title'];
		}
		$diff1 = array_diff_key($lds, $areas);	// 开发商没有城市占位的
		$diff2 = array_diff_key($areas, $lds);	// 有城市占位，但对应不上开发商

		$V = ['d'=>0,'a'=>0,'n'=>0,'no'=>[]];	// 数据验证
		echo '<h3> - 从 CRIC 共导入开发商数据 ', count($review), ' 条!</h3>', PHP_EOL;
		echo '<h4> - 从 CRIC 共导入开发商城市占位数据 ', $total, ' 条，包含 ', count($areas), ' 个开发商!</h3>', PHP_EOL;
		echo '<table border="1" cellspacing="0" style="border:1px solid black">', PHP_EOL,
			 '<thead><tr>', PHP_EOL,
			 '<td>乐居企业词条编号</td>', PHP_EOL,
			 '<td>CRIC开发商编号</td>', PHP_EOL,
			 '<td>首字母</td>', PHP_EOL,
			 '<td>开发商名称</td>', PHP_EOL,
			 '<td>开发商简称</td>', PHP_EOL,
			 //'<td>开发商名称拼音</td>', PHP_EOL,
			 '<td>城市占位名称</td>', PHP_EOL,
			 '</tr></thead>', PHP_EOL,
			 '<tbody>', PHP_EOL;
		foreach ( $review as $i => $item ) {
			$item['basic'] = json_decode($item['basic'], true);
			$cricid = $item['company_cric_id'];
			echo '<tr>', PHP_EOL,
				 '<td>', $item['id'], '</td>', PHP_EOL,
				 '<td>', $item['company_cric_id'], '</td>', PHP_EOL,
				 '<td>', $item['firstletter'], '</td>', PHP_EOL,
				 '<td>', $item['title'], '</td>', PHP_EOL,
				 '<td>', $item['basic']['stname'], '</td>', PHP_EOL,
				 //'<td>', $item['pinyin'] ,'</td>', PHP_EOL, 
				 '<td>', count($areas[$cricid]) ,'</td>', PHP_EOL, 
				 '</tr>', PHP_EOL;
			$V['d'] += 1;
			if ( array_key_exists($cricid, $areas) ) {
				$_areas = $areas[$cricid];
				foreach ( $_areas as $_i => $_area ) {
					echo '<tr>', PHP_EOL,
						 '<td>', $item['id'], '</td>', PHP_EOL,
						 '<td>', $item['company_cric_id'], '</td>', PHP_EOL,
						 '<td>', $item['firstletter'], '</td>', PHP_EOL,
						 '<td>', $item['title'], '</td>', PHP_EOL,
						 '<td>', $item['basic']['stname'], '</td>', PHP_EOL,
						 //'<td>', $item['pinyin'] ,'</td>', PHP_EOL, 
						 '<td>', $_area ,'</td>', PHP_EOL, 
						 '</tr>', PHP_EOL;
					$V['a'] += 1;
				}
			} else {
				$V['n'] += 1; // 62 => count($diff)
			}
			echo '<tr><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td></tr>', PHP_EOL;
		}
		echo '</tbody>', PHP_EOL,
			 '</table>', PHP_EOL;

		echo '<h3> - 其中但没有城市占位的开发商 ', count($diff1), ' 条!</h3>', PHP_EOL;
		echo '<table border="1" cellspacing="0" style="border:1px solid black">', PHP_EOL,
			 '<thead><tr>', PHP_EOL,
			 '<td>CRIC开发商编号</td>', PHP_EOL,
			 '<td>开发商名称</td>', PHP_EOL,
			 '</tr></thead>', PHP_EOL,
			 '<tbody>', PHP_EOL;
		foreach ( $diff1 as $cricid => $cricname ) {
			echo '<tr>', PHP_EOL,
				 '<td>', $cricid, '</td>', PHP_EOL,
				 '<td>', $cricname, '</td>', PHP_EOL,
				 '</tr>', PHP_EOL;
		}
		echo '</tbody>', PHP_EOL,
			 '</table>', PHP_EOL;

		echo '<h3> - 有城市占位，但对应不上开发商 ', count($diff2), ' 条!</h3>', PHP_EOL;
		echo '<table border="1" cellspacing="0" style="border:1px solid black">', PHP_EOL,
			 '<thead><tr>', PHP_EOL,
			 '<td>CRIC开发商编号</td>', PHP_EOL,
			 '<td>占位城市列表</td>', PHP_EOL,
			 '</tr></thead>', PHP_EOL,
			 '<tbody>', PHP_EOL;
		foreach ( $diff2 as $cricid => $citys ) {
			echo '<tr>', PHP_EOL,
				 '<td>', $cricid, '</td>', PHP_EOL,
				 '<td>', implode(', ', $citys), '</td>', PHP_EOL,
				 '</tr>', PHP_EOL;
		}
		echo '</tbody>', PHP_EOL,
			 '</table>', PHP_EOL;
		var_dump($V);
	}

	/**
	 * 查看已导入的 CRIC 企业数据表格
	 */
	public function tableSyncedCRICCompanies() {
		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 1000, 'intval');
		$model = D('Wiki', 'Model', 'Common');
		$where = array('src_type' => 2,);
		$fields = array('id', 'company_cric_id', 'title', 'stname', 'short', 'pinyin', 'city', 'firstletter', 'company_parent_id');
		$order = 'company_parent_id ASC';
		$mounts = $model->where($where)->count();
		$review = $model->where($where)->field($fields)->order($order)->page($page, $pagesize)->select();
		$_records = [];
		foreach ( $review as $i => $item ) {
			$parent_id = intval($item['company_parent_id']);
			$id = intval($item['id']);
			if ( $parent_id==0 ) {
				$_records[$id] = [];
				$_records[$id]['_main'] = $item;
				$_records[$id]['_child'] = [];
			} else {
				if ( !array_key_exists($parent_id, $_records) ) {
					$_record[$parent_id] = ['_child'=>[]];
				}
				array_push($_records[$parent_id]['_child'], $item);
			}
		}
		$inx = 0;
		echo '<h3> 从 CRIC 共导入开发商数据 ', $mounts, ' 条!</h3>', PHP_EOL;
		echo '<table border="1" cellspacing="0" style="border:1px solid black">', PHP_EOL,
			 '<thead><tr>', PHP_EOL,
			 '<td>#</td>', PHP_EOL,
			 '<td>wikiid(1)</td>', PHP_EOL,
			 '<td>wikiid(2)</td>', PHP_EOL,
			 '<td>CRIC编号</td>', PHP_EOL,
			 '<td>首字母</td>', PHP_EOL,
			 '<td>名称</td>', PHP_EOL,
			 '<td>简称(标签名)</td>', PHP_EOL,
			 '<td>短名</td>', PHP_EOL,
			 '<td>城市</td>', PHP_EOL,
			 '<td>拼音</td>', PHP_EOL,
			 '</tr></thead>', PHP_EOL,
			 '<tbody>', PHP_EOL;
		foreach ( $_records as $i => $item ) {
			$main = &$item['_main'];
			$inx += 1;
			echo '<tr>', PHP_EOL,
				 '<td>', $inx, '</td>', PHP_EOL,
				 '<td>', $main['id'], '</td>', PHP_EOL,
				 '<td>-</td>', PHP_EOL,
				 '<td>', $main['company_cric_id'], '</td>', PHP_EOL,
				 '<td>', $main['firstletter'], '</td>', PHP_EOL,
				 '<td>', $main['title'], '</td>', PHP_EOL,
				 '<td>', $main['stname'], '</td>', PHP_EOL,
				 '<td>', $main['short'], '</td>', PHP_EOL,
				 '<td> -总部- </td>', PHP_EOL, 
				 '<td>', $main['pinyin'] ,'</td>', PHP_EOL, 
				 '</tr>', PHP_EOL;
			foreach ( $item['_child'] as $j => $child ) {
				$inx += 1;
				echo '<tr>', PHP_EOL,
					 '<td>', $inx, '</td>', PHP_EOL,
					 '<td>-</td>', PHP_EOL,
					 '<td>', $child['id'], '</td>', PHP_EOL,
					 '<td>', $child['company_cric_id'], '</td>', PHP_EOL,
					 '<td>', $child['firstletter'], '</td>', PHP_EOL,
					 '<td>', $child['title'], '</td>', PHP_EOL,
					 '<td>', $child['stname'], '</td>', PHP_EOL,
					 '<td>', $child['short'], '</td>', PHP_EOL,
					 '<td>', $child['city'] ,'</td>', PHP_EOL, 
					 '<td>', $child['pinyin'] ,'</td>', PHP_EOL, 
					 '</tr>', PHP_EOL;
			}
		}
		echo '</tbody>', PHP_EOL,
			 '</table>', PHP_EOL;
	}

	/**
	 * 清除已导入的 CRIC 企业数据
	 */
	public function flushSyncedCRICCompines() {
		$model = D('Wiki', 'Model', 'Common');
		$where = array('src_type' => 2,);

		$ret = $model->where($where)->count();
		$deleted = false;
		if ( $ret>0 ) {
			$deleted = $model->where($where)->delete();
			$flushdb = $ret;
		}
		$result = array(
			'status' => $deleted,
			'total' => $ret,
		);
		$this->ajax_return($result);
	}

	/**
	 * 更新cric对应城市的city_en数据
	 * @2017-11-27 文硕 更新城市字典使用
	 */
	public function updateCRICCityEnCode() {
		$cities = C('CITIES.ALL');
		$dict = [];
		$mWiki = D('Wiki', 'Model', 'Common');
		$ret = $mWiki->where(['src_type'=>2])->field('city')->distinct(true)->select();
		$all_cities = [];
		echo '<pre>', PHP_EOL;
		foreach ( $ret as $i => $item ) {
			$city = trim($item['city']);
			if ( $city!='' ) {
				array_push($all_cities, $city);
			}
		}
		echo '原始数据', PHP_EOL, print_r($all_cities, true), PHP_EOL;
		foreach ( $cities as $i => $city ) {
			$city_cn = trim($city['cn']);
			if ( $city_cn!='' && in_array($city_cn, $all_cities) ) {
				$dict[$city_cn] = strtolower(trim($city['en']));
			}
		}
		echo '城市字典', PHP_EOL, print_r($dict, true), PHP_EOL;
		$total = ['wiki'=>0, 'history'=>0];
		if ( !empty($dict) ) {
			$mWikiHistory = D('WikiHistory', 'Model', 'Common');
			foreach ( $dict as $city_cn => $city_en ) {
				$ret1 = $mWiki->where(['src_type'=>2, 'city'=>$city_cn])->data(['city_en'=>$city_en])->save();
				$total['wiki'] += $ret1;
				$ret2 = $mWikiHistory->where(['src_type'=>2, 'city'=>$city_cn])->data(['city_en'=>$city_en])->save();
				$total['history'] += $ret2;
				echo '更新 [', $city_cn, '] 城市公司数据，词条: ', $ret1, '；历史: ', $ret2, PHP_EOL;
			}
		}
		echo '结果统计: ', PHP_EOL, print_r($total, true), PHP_EOL;
		echo '</pre>', PHP_EOL;
	}

	public function syncKnowledgeToInfo() {
		$type = I('get.type', 'all', 'trim');

		$id = I('get.id', 0, 'intval');
		$ds = I('get.start', '', 'trim');
		$de = I('get.end', '', 'trim');

		$pagesize = I('get.ps', 100, 'intval');

		$where = array(
			'status'=>9,
		);
		if ( $type=='id' && $id>0 ) {
			$where['id'] = $id;
		}
		// if ( $type=='date' && ( !empty($ds) || !empty($de) ) ) {
		// 	$ds = strtotime($ds);
		// 	$de = strtotime($de);
		// 	if ( $ds > $de ) { $t=$ds; $ds=$de; $de=$t; }
		// 	$where['ptime'] = array( array('egt', $ds), array('elt', $de) );
		// }
		if ( $type=='all' ) {
		}
		$mKnowledge = D('Knowledge', 'Model', 'Common');
		$total = $mKnowledge->where($where)->count();
		$count = ceil($total / $pagesize);
		$page = I('get.page', 1, 'intval');
		$page = ( $page < 1 ) ? 1 : $page;
		$page = ( $page > $count ) ? $count : $page;
		$list = $mKnowledge->where($where)->order('id desc')->page($page, $pagesize)->select();
		// var_dump($where, $total, $count);
		// var_dump(count($list), $list);
		$pager = array(
			'total' => $total,
			'page' => $page,
			'count' => $count,
			'pagesize' => $pagesize,
		);
		var_dump($pager);

		$lInfo = D('Infos', 'Logic', 'Common');
		$ret = $lInfo->batchPushNewsPool($list);
		var_dump($ret);
	}

	/**
	 * 将有效的问题数据同步到搜索引擎
	 * @param $page int 页码
	 * @param $pagesize int 每页信息数量
	 */
	public function syncQuestionToIndeces() {
		$page = I('get.page', 1, 'intval');
		$page = $page < 1 ? 1 : $page;

		$pagesize = I('get.pagesize', 100, 'intval');

		$mQuestion = D('Question', 'Model', 'Common');
		$where = array(
			'status'=>array('in', array(21,22,23)),
		);
		$total = $mQuestion->where($where)->count();
		$list = $mQuestion->where($where)->page($page, $pagesize)->select();

		$result = array('status'=>false);
		$result['pager'] = array(
			'page' => $page,
			'pagesize' => $pagesize,
			'total' => $total,
			'count' => ceil($total/$pagesize),
		);

		$lSearch = D('Search', 'Logic', 'Common');
		$docs = array();
		foreach ( $list as $i => $item ) {
			$doc = $lSearch->questionDocConvert($item);
			array_push($docs, $doc);
		}
		if ( $this->_debug ) {
			$result['list'] = $list;
			$result['docs'] = $docs;
		}
		$ret = $lSearch->create($docs, 'question');
		$result['ret'] = $ret;
		$result['status'] = $ret;

		$this->ajax_return($result);
	}

	public function rebuildCatetree() {
	}

	// 重新加载公共头尾模版
	public function reloadCommonTpl() {
		$act = I('get.act', '', 'strtolower,trim');
		if ( $act=='flush' ) {
			$lPage = D('Front', 'Logic', 'Common');
			$lPage->getPCPublicTemplate(true);
			$this->ajax_return(array('status'=>true, 'reason'=>'模版缓存更新成功'));
		}
		$this->ajax_error('模版缓存更新失败');
	}

	public function showUserRole() {
		echo '<!--', PHP_EOL,
			 print_r($this->_user, true), PHP_EOL,
			 print_r($this->getAuthorities(), true), PHP_EOL,
			 '-->', PHP_EOL;
	}

}