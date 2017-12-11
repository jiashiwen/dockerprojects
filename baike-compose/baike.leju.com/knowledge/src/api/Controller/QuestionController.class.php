<?php
/**
 * 乐道问答 App 接口
 * @author Robert <yongliang1@leju.com>
 * 使用方 :
 * 1. 乐居财经
 * 2. 人物问答
 */
namespace api\Controller;
use Think\Controller;

class QuestionController extends BaseController {

	/**
	 * 获取公司或人物问答总量
	 */
	public function stats() {
		$flush = I('get.flush', 0, 'intval');
		$flush = 1;

		$type = I('get.type', 1, 'intval');
		$type = !in_array($type, [1,2]) ? 1 : $type;
		$opts = [
			1=>['name'=>'公司','model'=>'Companies',],
			2=>['name'=>'人物','model'=>'Persons',],
		];
		$opt = $opts[$type];

		$result = [
			'status' => false,
			'code' => 0,
			'title' => '',
			'stats' => [
				'questions' => 0,
				'answers' => 0,
			],
			'exists' => false,
			'msg' => '',
		];

		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$result['code'] = 100;
			$result['msg'] = '请指定要查询的'.$opt['name'].'编号';
			$this->ajax_return($result);
		}

		$model = D($opt['model'], 'Model', 'Common');
		// 统计公司问答信息
		if ( $type == 1 ) {
			$info = $model->getCompanyInfo($id);
		}
		// 统计人物问答信息
		if ( $type == 2 ) {
			$info = $model->getPersonInfo($id);
		}

		if ( $info ) {
			$result['stats']['questions'] = intval($info['cntq']);
			$result['stats']['answers'] = intval($info['cnta']);
			$mWiki = D('Wiki', 'Model', 'Common');
			$exists = $mWiki->field('title')->where(['id'=>$id, 'cateid'=>$type, 'status'=>9])->find();
			if ( $exists ) {
				$result['status'] = true;
				$result['title'] = trim($exists['title']);
				$result['exists'] = true;
				$result['code'] = 0;
				$result['msg'] = '';
			} else {
				$result['exists'] = false;
				$result['code'] = 200;
				$result['msg'] = $opt['name'].'词条不存在';
			}
		} else {
			$result['exists'] = false;
			$result['code'] = 300;
			$result['msg'] = $opt['name'].'问答未开启';
		}
		// $result['debug'] = $model->getLastSql();
		$this->ajax_return($result);
	}


	/**
	 * 为会员中心提供问答基础信息的接口
	 */
	public function getinfo() {
		$qid = I('get.qid', '', 'trim');
		$aid = I('get.aid', '', 'trim');

		$qid = explode(',', $qid);
		$aid = explode(',', $aid);
		if ( count($qid)>50 || count($aid)>50 ) {
			$this->ajax_error('查询数量超过限制');
		}
		foreach ( $qid as $i => &$q ) {
			$q = intval(trim($q));
			if ( $q<=0 ) { unset($qid[$i]); }
		}
		foreach ( $aid as $i => &$a ) {
			$a = intval(trim($a));
			if ( $a<=0 ) { unset($aid[$i]); }
		}

		$result = [
			'status'=>true,
			'q'=>[],
			'a'=>[],
		];

		$type = I('get.type', 1, 'intval');
		$type = !in_array($type, [1,2]) ? 1 : $type;
		$opts = [
			1=>[
				'name'=>'公司',
				'qmodel'=>'CompanyQuestions',
				'qurl'=>'LDQuestion',
				'amodel'=>'CompanyAnswers',
				'aurl'=>'LDAnswer',
			],
			2=>[
				'name'=>'人物',
				'qmodel'=>'PersonQuestions',
				'qurl'=>'PNQuestion',
				'amodel'=>'PersonAnswers',
				'aurl'=>'PNAnswer',
			],
		];

		$opt = $opts[$type];
		$mQuestion = D($opt['qmodel'], 'Model', 'Common');
		$ret = $mQuestion->getListForUserCenter($qid);
		foreach ( $ret as $i => $_t ) {
			$qid = intval($_t['id']);
			$status = $_t['qs']==2 ? true : false;
			$result['q'][$qid] = [
				'id' => $qid,
				'title' => $_t['title'],
				'status' => $status, 
				'url' => $status ? url($opt['qurl'], [$qid], 'touch', 'ask') : '#',
			];

		}
		$mAnswer = D($opt['amodel'], 'Model', 'Common');
		$ret = $mAnswer->getListForUserCenter($aid);
		foreach ( $ret as $i => $_t ) {
			$aid = intval($_t['id']);
			$qid = intval($_t['question_id']);
			$status = ($_t['as']==2 && $_t['qs']==2) ? true : false;
			$result['a'][$aid] = [
				'id' => $aid,
				'qid' => $qid,
				'title' => $_t['title'],
				'reply' => $_t['reply'],
				'status' => $status,
				'url' => $status ? url($opt['aurl'], [$qid, $aid], 'touch', 'ask') : '#',
			];
		}
		$this->ajax_return($result);
	}

	/**
	 * 用于批量修正问题和回答数量统计
	 */
	public function fixldstats() {
		$id = I('get.id', 0, 'intval');
		$type = I('get.type', 1, 'intval');
		$type = !in_array($type, [1,2]) ? 1 : $type;
		$opts = [
			1=>['name'=>'公司','model'=>'Companies',],
			2=>['name'=>'人物','model'=>'Persons',],
		];
		$opt = $opts[$type];
		$model = D($opt['model'], 'Model', 'Common');

		if ( $id>0 ) {
			$ret = $model->updateRelQuestions($id);
			$result = ['status'=>true, 'ret'=>$ret];
		} else {
			$ret = $model->fixRelQuestions();
			$result = ['status'=>true, 'ret'=>$ret];
		}
		$this->ajax_return($result);
	}
}