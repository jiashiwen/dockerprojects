<?php
/**
 * 知识控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;


class KnowledgeController extends BaseController {

	protected $dict = array();
	protected $cities = array();

	protected $default_list_pagesize = 20;
	protected $default_list_page = 1;

	public function __construct() {
		parent::__construct();

		$this->cities = C('CITIES.ALL');
		$this->dicts = C('DICT.KNOWLEDGE');
	}

	/**
	 * 知识管理列表及搜索页面
	 */

	public function index()
	{
		$lCate = D('Cate','Logic','Common');

		//获取权限
		$rolekey = 'knowledge/list';
		$role = $this->Role($rolekey);

		$get['page'] = I('get.page', $this->default_list_page, 'intval');
		$get['pagesize'] = I('get.pagesize', $this->default_list_pagesize, 'intval');
		$get['keyword'] = I('get.keyword','','strip_tags,trim');
		$get['scope'] = I('get.scope','','strip_tags,trim');
		$get['editor'] = I('get.editor','','strip_tags,trim');
		$get['ptime'] = I('get.ptime','','strip_tags,trim');
		$get['cateid'] = I('get.cateid',0,'intval');
		$get['src_type'] = I('get.src_type','','trim');
		$get['status'] = I('get.status','','trim');
		$get['level1'] = I('get.level1','','intval');
		$get['level2'] = I('get.level2','','intval');
		//$get['s'] = I('get.search',0,'intval');
		$lSearch = D('Search','Logic','Common');

		if ((int)$get['level1'] > 0)
		{
			$binds['level2'] = $lCate->getCateListById($get['level1']);
		}

		if ((int)$get['level2'] > 0)
		{
			$binds['level3'] = $lCate->getCateListById($get['level2']);
		}

		//查数据库
		$mKnowledge = D('Knowledge', 'Model', 'Common');

		$offset = ($get['page']-1) * $get['pagesize'];
		$condition = $this->formatCondition($get, $role['roleCitys']);
		// G('start_cnt');
		if ( empty($condition) ) {
			$_cond = $condition;
			array_unshift($_cond, array('id'=>array('gt', 0)));
		} else {
			$_cond = $condition;
		}
		$total = $mKnowledge->field(array('id'))->where($_cond)->count();
		// G('end_cnt');
		/*
		echo '<!--', PHP_EOL, 
			 $mKnowledge->getLastSql(), PHP_EOL, 
			 var_export($_cond, true), PHP_EOL,
			 var_export($get, true), PHP_EOL,
			 G('start_cnt', 'end_cnt', 3), PHP_EOL,
			 G('start_cnt', 'end_cnt', 'm'), PHP_EOL,
			 '-->', PHP_EOL;
		*/
		if ($total)
		{
			$fields = array(
				'id', 'scope', 'title', 'tags', 'src_type', 'src_url', 'status', 'version',
				'ptime', 'ctime', 'utime', 'rcmd_time', 'top_time', 'catepath', 'cateid', 'editor',
			);
			$_catenames = array();
			$pagecount = ceil($total/$get['pagesize']);
			// G('start_get');
			$result = $mKnowledge->field($fields)->where($_cond)->order('version desc')->limit($offset,$get['pagesize'])->select();
			// G('end_get');
			// echo '<!--', PHP_EOL, 
			// 	 $mKnowledge->getLastSql(), PHP_EOL, 
			// 	 G('start_get', 'end_get', 3), PHP_EOL,
			// 	 G('start_get', 'end_get', 'm'), PHP_EOL,
			// 	 '-->', PHP_EOL;
			$data = array();
			foreach ($result as $key => &$item) {
				$data[$key]['_origin'] = &$item;
				$data[$key]['_scope'] = $role['roleCitys'][$item['scope']]['cn'];
				$data[$key]['_tags'] = $item['tags'];
				$data[$key]['_title'] = htmlspecialchars($item['title'],ENT_QUOTES);
				$data[$key]['_id'] = $item['id'];
				// $data[$key]['_content'] = $item['content'];
				$data[$key]['_url'] = url('show', array($item['id']), 'pc', 'baike');
				$data[$key]['_src_url'] = $item['src_url'];
				//$data[$key]['_doccreatetime'] = $item['ctime'];
				$data[$key]['_timer'] = ($item['ptime'] > $item['ctime']) && ($item['ptime'] > NOW_TIME) ? 1 : 0;
				// 给管理列表添加知识内容所属顶级栏目名称
				$_top_cid = intval(explode('-', $item['catepath'])[1]);
				if ( $_top_cid == 0 ) {
					$data[$key]['_category'] = '未分类';
				} else {
					if ( !array_key_exists($_top_cid, $_catenames) ) {
						$_catenames[$_top_cid] = $lCate->getCateName($_top_cid);
					}
					$data[$key]['_category'] = $_catenames[$_top_cid];
				}

				// 生成预览码
				$outpoint = intval( intval(date('i', NOW_TIME)) / 10 );
				$deadline = strtolower( date('Y-m-d H:'.($outpoint*10).':00', NOW_TIME) );
				$token = substr(md5($deadline), 0, 6);
				$data[$key]['preview'] = array(
					'pc' => url('index', array(), 'pc', 'baike') . 'Show-preview?id='.$item['id'].'&nofit=1&token=' . $token,
					'touch' => url('index', array(), 'touch', 'baike') . 'Show-preview?id='.$item['id'].'&nofit=1&token=' . $token,
				);
			}
		}
		/*
		后台查询目前先查库
		else
		{
			$lSearch = D('Search','Logic','Common');
			$opts = array(array('false', '_deleted'));
			$today = $this->getTodayTime(strtotime($get['ptime']));
			$today['begain'] *= 1000;
			$today['end'] *= 1000;
			$prefix = array();
			if ($get['scope'])
			{
				if ($get['scope'] == '_')
				{
					$scope = '全国';
				}
				else
				{
					$scope = $role['roleCitys'][$get['scope']]['cn'];	
				}
			}
			else
			{
				if (!array_key_exists("_",$role['roleCitys']))
				{
					foreach ($role['roleCitys'] as $key => $city) {
						$scope[] = $city['cn'];
					}
					$scope = implode(',', $scope);
				}
			}

			$scope && array_push($opts, array("{$scope}",'_scope'));
			$get['cateid'] && array_push($prefix,array("{$get['cateid']}","_multi.cateid"));
			$get['src_type'] && array_push($prefix,array("{$get['src_type']}","_multi.src_type"));
			$get['editor'] && array_push($prefix,array("{$get['editor']}","_multi.editor"));
			$get['ptime'] && array_push($opts,array("[{$today['begain']},{$today['end']}]","_doccreatetime"));
			if (!$get['cateid'])
			{
				if ($get['level1'] && !$get['level2'])
				{
					$path = '0-'.$get['level1'];
					$prefix = array(array("{$path}", '_multi.catepath'));
				}
				if ($get['level1'] && $get['level2'])
				{
					$path = '0-'.$get['level1'].'-'. $get['level2'];
					$prefix = array(array("{$path}", '_multi.catepath'));
				}
			}
			$order = array('_doccreatetime', 'desc');
			$fields = array('_id','_title','_version','_scope','_origin');
			$list = $lSearch->select($get['page'], $get['pagesize'],$get['keyword'],$opts,$prefix, $order, $fields);
			$total = $list['pager']['total'];
			$data = $list['list'];
			//
		}
		*/

		$this->linkopts($get,$total);
		$binds['authorcate'] = $lCate->getCateListByIds($role['roleCate']);
		$this->assign('dicts', $this->dicts);
		$this->assign('binds', $binds);
		$this->assign('data',$data);
		$this->assign('params',$get);
		$this->display('index');
	}

	/**
	 * 管理列表的链接参数
	 */
	private function linkopts ( $form, $total ) {
		// 封装linkopts
		$linkopts = array();
		$form['pagesize'] != '' && array_push($linkopts, "pagesize={$form['pagesize']}" );
		$form['keyword'] && array_push($linkopts, "keyword={$form['keyword']}");
		$form['scope'] && array_push($linkopts, "scope={$form['scope']}");
		$form['editor'] && array_push($linkopts, "editor={$form['editor']}");
		$form['src_type'] != '' && array_push($linkopts, "src_type={$form['src_type']}");
		$form['ptime'] && array_push($linkopts, "ptime={$form['ptime']}");
		$form['cateid'] && array_push($linkopts, "cateid={$form['cateid']}");
		$form['status'] && array_push($linkopts, "status={$form['status']}");
		$form['level1'] && array_push($linkopts, "level1={$form['level1']}");
		$form['level2'] && array_push($linkopts, "level2={$form['level2']}");

		$linkstring = !empty($linkopts) ? '/Knowledge/?page=#&'.implode('&',$linkopts) : '/Knowledge/?page=#';

		$opts = array(
			'first' => true, //首页
			'last' => true,	//尾页
			'prev' => true, //上一页
			'next' => true, //下一页
			'number' => 5, //显示页码数
			'linkstring' => $linkstring,
		);
		$pager = pager($form['page'], $total, $form['pagesize'], $opts);
		$this->assign('pager', $pager);
	}

	
	/**
	 * 对知识列表的查询条件进行标准化处理，以便于进行数据模型查询
	 */
	protected function formatCondition($get,$cities) {
		$condition = array();
		$_all_cities = &$this->cities;
		if ( $get['scope'] ) {
			// $condition['scope'] = array('in',$get['scope']);
			$scope = strtolower($get['scope']);
			if ( array_key_exists($scope, $_all_cities) || $scope == '_' ) {
				$condition['scope'] = $scope;
			}

			/* 暂时忽略城市作用域权限验证
			if (array_key_exists($get['scope'],$cities))
			{
				if ($get['scope'] != '_')
				{
					$condition['scope'] = array('in',$get['scope']);
				}	
			}
			else
			{
				$scope = array_keys($cities);
				$condition['scope'] = array('in',$scope);	
			}
			 */
		} else {
			if ( $cities!==false ) {
				if ( count($cities) == 1 ) {
					$scope = array_keys($cities);
					$condition['scope'] = $scope[0];
				}
				if ( count($_all_cities) > count($cities) ) {
					$scope = array_keys($cities);
					$condition['scope'] = array('in',$scope);
				}
			}
		}

		// 如果指定了 3 级栏目，直接用栏目id进行筛选
		if ( $get['cateid'] ) {
			$condition['cateid'] = $get['cateid'];
		} else {
			if ($get['level1'] ) {
				if ( !$get['level2'] ) {
					$path = '0-'.$get['level1'];
					$condition['catepath'] = array('like',"{$path}%");
				} else {
					$path = '0-'.$get['level1'].'-'.$get['level2'];
					$condition['catepath'] = array('like',"{$path}%");
				}
			}
		}

		if (($ptime = strtotime($get['ptime']))) {
			$today = $this->getTodayTime($ptime);
			$condition['ptime'] = array('between',array($today['begain'],$today['end']));
		}

		if ( isset($get['status']) && is_numeric($get['status']) && in_array($get['status'], array_keys($this->dicts['STATUS'])) ) {
			$condition['status'] = $get['status'];
		}

		// 如果指定来源，则对来源进行筛选
		if (is_numeric($get['src_type']) && in_array($get['src_type'], array_keys($this->dicts['TYPES']))) {
			$condition['src_type'] = $get['src_type'];
		}

		// 文本条件最后再加入到查询条件中
		$get['keyword'] && $condition['title'] = array('like',"%{$get['keyword']}%");
		$get['editor'] && $condition['editor'] = array('like',"%{$get['editor']}%");

		return $condition;
	}

	/**
	 * 格式化时间
	 * @TODO: 提出到公用方法中，并添加指定时间段的参数，便于公用
	 */
	private function getTodayTime($time) {
		$date = date('Y-m-d', $time);
		$start = strtotime($date);
		$end = $start + 24 * 3600;
		return array('begain'=>$start, 'end'=>$end);
	}

	/**
	 * x 知识内容审核
	 */
	public function confirm(){
		// knowledge/confirm
		echo '<h1>这是 知识内容审核 页面</h1>';
		if ( IS_POST ) {
		}
		// $this->display();
	}

	/**
	 * x 知识内容置顶
	 */
	public function settop(){
		// knowledge/add

		$result = array(
			'status'=>false,
			'msg'=>'置顶失败',
		);
		if ( IS_POST ) 
		{
			$id = I('post.id',0,'intval');
			if ($id)
			{
				$mKnowledge = D('Knowledge', 'Model', 'Common');
				if ($mKnowledge->find($id))
				{
					$mKnowledge->top_time = NOW_TIME;
					if ($mKnowledge->save());
					{
						//add log
						$result['status'] = true;
						$result['msg'] = '置顶成功';
					}
				}
			}
		}
		$this->ajax_return($result);
		// $this->display();
	}

	/**
	 * x 知识内容为焦点
	 * 
	 */
	public function setfocus(){
		// knowledge/add
		echo '<h1>这是 知识内容为焦点 页面</h1>';
		if ( IS_POST ) {
		}
		// $this->display();
	}


	private function filter()
	{
		$filters = array (
			'id'=>'','version'=>'','ctime'=>'','top_time'=>'','rcmd_time'=>'',

			'src_type'=>'','title'=>'','summary'=>'','content'=>'',
			'cover'=>'','coverinfo'=>'','media'=>'','editor'=>'','editorid'=>'',

			'scope'=>'','cateid'=>'','catepath'=>'','ptime'=>'',
			'rcmd_title'=>'','rcmd_cover'=>'','rcmd_coverinfo'=>'',
			'top_title'=>'','top_cover'=>'','top_coverinfo'=>'',

			'tags'=>'', 'rel_news'=>'', 'rel_house'=>'',
			'seo_title'=>'', 'seo_keywords'=>'', 'seo_description'=>'',
			//extra
			'action_type'=>'',
			'gettag'=>'','getnew'=>'','gethouse'=>'',
			'cateid1'=>'','cateid2'=>'','pkid'=>'',
			'focus'=>'','top'=>'','optime'=>'',
		);
		$data = array_intersect_key($_POST, $filters);

		// 知识来源
		$data['src_type'] = intval($data['src_type']);
		// 标签参数
		if ( isset($data['gettag']) ) {
			$data['gettag'] = json_decode($data['gettag'],true);
			foreach ($data['gettag'] as $key => $value) {
				$data['tags'][] = clear_all($value['name']);
			}
			$data['tags'] = implode(' ', $data['tags']);
			unset($data['gettag']);
		} else {
			$data['tags'] = '';
		}
		// 相关楼盘
		if ( isset($data['gethouse']) ) {
			$gethouse = json_decode($data['gethouse'],true);
			foreach ($gethouse as $key => &$value) {
				$value['name'] = clear_all($value['name']);
			}
			$data['rel_house'] = json_encode($gethouse);
			unset($data['gethouse']);
		} else {
			$data['rel_house'] = '[]';
		}
		// 相关资讯 (新闻)
		if ( isset($data['getnew']) ) {
			$getnew = json_decode($data['getnew'],true);
			foreach ($getnew as $key => &$value) {
				$value['title'] = clear_all($value['title']);
			}
			$data['rel_news'] = json_encode($getnew);
			unset($data['getnew']);
		} else {
			$data['rel_news'] = '[]';
		}

		$data['utime'] = $data['version'] = NOW_TIME;

		// 验证是否置顶
		if ( isset($data['top']) && $data['top'] ) {
			if ( $data['top_time'] == 0 ) {
				$data['top_time'] = NOW_TIME;
			}
			unset($data['top']);
		} else {
			$data['top_time'] = 0;
		}
		// 验证是否推荐
		if ( isset($data['focus']) && $data['focus'] ) {
			if ( $data['rcmd_time'] == 0 ) {
				$data['rcmd_time'] = NOW_TIME;
			}
			unset($data['focus']);
		} else {
			$data['rcmd_time'] = 0;
		}

		$data['catepath'] = '0-'.intval($data['cateid1']).'-'.intval($data['cateid2']).'-'.intval($data['cateid']);
		$data['cateid1'] = intval($data['cateid1']);
		$data['scope'] = strtolower(trim($data['scope']));
		$data['summary'] = strip_tags($data['summary']);
		$data['content'] = clean_xss($data['content']);
		unset($data['editorValue']);
		isset($data['ptime']) && trim($data['ptime'])!='' && $data['ptime'] = strtotime($data['ptime']);
		empty($data['editor']) && $data['editor'] = $this->_user['truename'];
		!isset($data['action_type']) && $data['action_type'] = 'save';	// 默认以保存草稿方式提交

		return $data;
	}

	/**
	 * 添加知识内容
	 */
	public function add(){
		
		$rolekey = 'knowledge/add';
		$result = array('status'=>false,'msg'=>'创建失败',);
		$pageinfo = array(
			'crumb' => array(),
			'title' => '添加知识内容',
		);
		$role = $this->Role($rolekey);
		$lCate = D('Cate','Logic','Common');
		$binds['roleCate'] = $lCate->getCateListByIds($role['roleCate']);

		if ( IS_GET ) {

			$binds['cdn_img_url'] = C('ADMIN_URL.CDN_IMG_URL');
			$binds['method'] = 'add';
			$this->assign('binds', $binds);
			$this->assign('pageinfo', $pageinfo);
			$this->editor();
		}

		if ( IS_POST ) {
			// 获取表单数据，并进行结构确认
			$data = $this->filter();

			// 操作者权限验证
			$auth = array(
				'top_cateid' => $data['cateid1'],
				'scope' => $data['scope'],
			);
			$role = $this->getRoleList($rolekey);
			$this->verifyCateOp($auth['top_cateid'], $role['roleCate']);
			$this->verifyCityOp($auth['scope'], $role['roleCitys']);

			// 确认操作逻辑
			$action = strtolower($data['action_type']);
			unset($data['action_type']);
			$actions = array('publish', 'pub', 'save');
			if ( !in_array($action, $actions) ) {
				$result['msg'] = '请指定您要做的操作！';
				$this->ajax_return($result);
			}

			$lPublish = D('BaikePublish', 'Logic', 'Common');

			// 进行发布处理
			if ( in_array($action, array('pub', 'publish')) ) {
				$ret = $lPublish->Publish($data);
			}
			// 进行保存草稿处理
			if ( $action=='save' ) {
				$ret = $lPublish->Save($data);
			}

			if ( $ret ) {
				$result['status'] = true;
				$result['msg'] = '修改成功';
			} else {
				$result['msg'] = $lPublish->getError();
			}
			$this->ajax_return($result);
		}
		/*
		if ( IS_POST ) {
			$data = $this->filter();
			$role = $this->getRoleList($rolekey);
			$this->verifyCateOp($data['cateid1'], $role['roleCate']);
			$this->verifyCityOp($data['scope'], $role['roleCitys']);
			//
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$exist = $mKnowledge->where(array('cateid'=>$data['cateid'],'scope'=>$data['scope'],'title'=>$data['title']))->find();
			if ($exist)
			{
				$result['msg'] = '同名 知识已存在，添加失败';
				$this->ajax_return($result);
			}
			//
			$data['ptime'] = strtotime($data['ptime']);
			if ($data['ptime'] == false || $data['ptime'] <= NOW_TIME)
			{
				$data['version'] = $data['ctime'] = $data['utime'] = $data['ptime'] = NOW_TIME;
				$data['status'] = 9;
			}
			else
			{
				$data['version'] = $data['ptime'];
				$data['ctime'] = $data['utime'] = NOW_TIME;
				$data['status'] = 1;
			}
			
			$data['top_time'] = $data['top'] ? NOW_TIME : 0;
			$data['rcmd_time'] = $data['focus'] ? NOW_TIME : 0;
			
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			if (!$mKnowledge->create($data)){
				// 如果创建失败 表示验证没有通过 输出错误提示信息
				$result['msg'] = $mKnowledge->getError();
			}
			else
			{
				if ($mKnowledge->add())
				{
					$lastid = $mKnowledge -> getLastInsID();
					$result['status'] = true;
					$result['msg'] = '创建成功';
					if ($data['status'] == 9)
					{
						$data['id'] = $lastid;
						$push = $this->pushKonwledge($data);
						$lSearch = D('Search', 'Logic', 'Common');
						if ($lSearch->createKnowledge(array($push)))
						{
							$mKnowledgeHistory = D('KnowledgeHistory', 'Model', 'Common');
							if ($mKnowledgeHistory->create($data))
							{
								$ret['history'] = $mKnowledgeHistory->add();
							}
							else
							{
								$ret['history'] = $mKnowledgeHistory->getError();
							}
						}
						else
						{
							$status['status'] = 1;
							$mKnowledge->where(array('id'=>$lastid))->save($status);
						}
						// 向新闻池推送数据
						$lInfos = D('Infos','Logic','Common');
						$lInfos->pushNewsPool($data);
					}
					
				}

			}
			$this->ajax_return($result);
		}
		*/
		// $this->display();
	}

	
	/**
	 * 编辑知识内容
	 */
	public function edit() {
		$result = array('status'=>false,'msg'=>'修改失败',);
		$rolekey = 'knowledge/edit';
		$role = $this->Role($rolekey);
		$lCate = D('Cate','Logic','Common');
		$mKnowledge = D('Knowledge', 'Model', 'Common');
		$mKnowledgeHistory = D('KnowledgeHistory', 'Model', 'Common');
		if ( IS_GET ) {
			
			// knowledge/edit
			$pageinfo = array(
				'crumb' => array(),
				'title' => '编辑知识内容',
			);
			$this->assign('pageinfo', $pageinfo);

			$id = I('get.id',0,'intval');
			$pkid = I('get.pkid',0,'intval');
			if (!$id) {
				$this->error('ID错误');
			}
			if ($pkid <= 0)
			{
				$list = $mKnowledge->find($id);
			}
			else
			{
				$binds['pkid'] = $pkid;
				$list = $mKnowledgeHistory->getHistoryVersion($id,$pkid);
			}

			if ( empty($list) ) {
				$this->error('知识不存在');
			}

			$curcatepath = explode('-', $list['catepath']);
			$curcate = $curcatepath['1'];
			if ( !empty($list['catepath']) && !in_array($curcate, $role['roleCate']))
			{
				$this->error('没有当前知识的栏目权限');
			}

			$none_city = array_key_exists($list['scope'], $this->cities);
			if ( $pkid!=0 && !$none_city && !array_key_exists($list['scope'],$role['roleCitys']) ) {
				$this->error('没有当前知识的城市权限');
			}

			$list['tags'] && $list['tags'] = explode(' ', $list['tags']);
			$list['tags'] = array_map('clear_all', $list['tags']);
			$list['rel_news'] && $list['rel_news'] = json_decode($list['rel_news'],true);
			$list['rel_house'] && $list['rel_house'] = json_decode($list['rel_house'],true);
			$list['title'] = htmlspecialchars($list['title'],ENT_QUOTES);

			$history = $mKnowledgeHistory->getHistoryVersionList($id);
			if ($history)
			{
				$mAdmins = D('Admins','Model','Common');
				foreach($history as $key=>$item)
				{
					$user = $mAdmins->find($item['editorid']);
					$history[$key]['truename'] = $user['truename'];
				}
			}
			$histotal = count($history);
			//$curversion = $mKnowledge->find($id);
			$path = explode('-', $list['catepath']);

			$binds['level2'] = $lCate->getCateListById($path['1']);
			$binds['level3'] = $lCate->getCateListById($path['2']);
			//$binds['curversion'] = $curversion ? $curversion : '';

			$binds['roleCate'] = $lCate->getCateListByIds($role['roleCate']);
			$binds['cdn_img_url'] = C('ADMIN_URL.CDN_IMG_URL');
			$binds['method'] = 'edit';
			$binds['path'] = $path;
			$this->assign('list', $list);
			$this->assign('binds', $binds);
			$this->assign('history', $history);
			$this->assign('histotal', $histotal);
			$this->editor($list);
		}

		if ( IS_POST ) {
			// 获取表单数据，并进行结构确认
			$data = $this->filter();

			// 操作者权限验证
			$auth = array(
				'top_cateid' => $data['cateid1'],
				'scope' => $data['scope'],
			);
			$role = $this->getRoleList($rolekey);
			$this->verifyCateOp($auth['top_cateid'], $role['roleCate']);
			$this->verifyCityOp($auth['scope'], $role['roleCitys']);

			// 确认操作逻辑
			$action = strtolower($data['action_type']);
			unset($data['action_type']);
			$actions = array('publish', 'pub', 'save');
			if ( !in_array($action, $actions) ) {
				$result['msg'] = '请指定您要做的操作！';
				$this->ajax_return($result);
			}

			$lPublish = D('BaikePublish', 'Logic', 'Common');

			// 进行发布处理
			if ( in_array($action, array('pub', 'publish')) ) {
				$ret = $lPublish->Publish($data);
			}
			// 进行保存草稿处理
			if ( $action=='save' ) {
				$ret = $lPublish->Save($data);
			}

			if ( $ret ) {
				$result['status'] = true;
				$result['msg'] = '修改成功';
			} else {
				$result['msg'] = $lPublish->getError();
			}
			$this->ajax_return($result);
		}
	}

	/**
	 * 知识内容编辑器
	 */
	protected function editor ( $data=array() ) {
		// 所有知识文档相关字典
		$this->assign('dicts', $this->dicts);
		// $this->assign('pageinfo', $pageinfo);
		$this->display('editor');
	}

	/**
	 * 保存知识内容
	 */
	protected function save ( $id=0, $data=array() ) {
		echo '<h3>保存知识内容</h3>';
	}


	/**
	 * 知识内容伪删除操作
	 *
	 */
	public function del()
	{
		$rolekey = 'knowledge/del';
		$this->checkAuthorization($rolekey,'ajax');

		$id = I('get.id', 0, 'intval');
		if ( $id ) {
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$info = $mKnowledge->find($id);
			// 找到相关数据才做处理
			if ($info) {

				$role = $this->getRoleList($rolekey);
				$catepath = explode('-', $info['catepath']);

				// 收录(导入)的知识且尚未分配栏目的，不做权限判断和限制
				if ( $info['cateid'] != 0 ) {
					$this->verifyCateOp($catepath['1'], $role['roleCate'],1);
					$this->verifyCityOp($info['scope'], $role['roleCitys'],1);
				}

				// 删除逻辑处理
				$mKnowledge->status = -1;
				// 数据库更新操作
				$ret = $mKnowledge->save();
				if ($ret) {
					// 删除服务接口的数据
					$lSearch = D('Search', 'Logic', 'Common');
					$ret['push'] = $lSearch->removeKnowledge(array($id));
					// 伪删除时也对新闻池推送删除操作 删除新闻池相关数据
					$lInfos = D('Infos','Logic','Common');
					$lInfos->pushNewsPool($data=array('id'=>$id),true);
				}

				ajax_succ('删除成功');
			}
		}

		// id = 0 或 以 id 无法获取到数据时，不做处理，按操作异常返回失败信息
		ajax_error('删除失败');
	}

	/**
	 * 知识内容的真实删除操作
	 */
	public function destory() {
		// $rolekey = 'knowledge/destory';
		// $this->checkAuthorization($rolekey,'ajax');

		$id = I('get.id', 0, 'intval');
		if ( $id ) {
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$info = $mKnowledge->find($id);
			// 找到相关数据才做处理
			if ($info) {

				$role = $this->getRoleList($rolekey);
				$catepath = explode('-', $info['catepath']);
				// var_dump($role, $catepath, $info);
				// 收录(导入)的知识且尚未分配栏目的，不做权限判断和限制
				if ( $info['cateid'] != 0 ) {
					$this->verifyCateOp($catepath['1'], $role['roleCate'],1);
					$this->verifyCityOp($info['scope'], $role['roleCitys'],1);
				}

				// 物理删除处理
				$ret = $mKnowledge->where(array('id'=>$id))->delete();
				if ($ret) {
					// 删除历史版本数据
					$mHistory = D('KnowledgeHistory', 'Model', 'Common');
					$effects = $mHistory->cleanupKnowledge($id);
				}

				ajax_succ('删除成功');
			}
		}

		// id = 0 或 以 id 无法获取到数据时，不做处理，按操作异常返回失败信息
		ajax_error('删除失败');
	}

	// ====================== 栏目管理 ======================
		/**
		 * 知识栏目管理
		 */
		public function cate() {
			// knowledge/categories/list
			$rolekey = 'cate/list';
			//$this->checkAuthorization($rolekey);

			if ( IS_GET ) {
				$list = array();
				$mCategories = D('Categories', 'Model', 'Common');
				$allList = $mCategories->getAllCate();
				if ( $allList ) {
					$list = $this->formatTree($allList);
				}

				$this->assign('list',$list);
				$this->display('cate');
			}
		}

		/**
		 *
		 */
		protected function formatTree($data, $pid = 0){
			$list = array();
			$tem = array();
			foreach ( $data as $item ) {
				if ( $item['parent'] == $pid ) {
					$tem = $this->formatTree($data, $item['id']);
					//判断是否存在子数组
					$tem && $item['son'] = $tem;
					$list[] = $item;
				}
			}
			return $list;
		}


		/**
		 * 保存知识栏目设置
		 */
		public function cateadd() {
			// knowledge/categories/save
			$num = 3;//最大级数
			$rolekey = 'cate/add';
			$result = array('status'=>'fail','reason'=>'添加失败',);
			if ( IS_POST ) {
				$this->checkAuthorization($rolekey,'ajax');
				$post['id'] = I('post.id',0,'intval');
				$post['name'] = I('post.name','','trim');
				$post['code'] = I('post.code','','trim');
				$mCategories = D('Categories', 'Model', 'Common');
				//
				$exist = $mCategories->where(array('parent'=>$post['id'],'name'=>$post['name']))->find();
				if ($exist)
				{
					ajax_error('分类名称已存在');
				}
				!$post['id'] && $post['id'] = 0;
				$iorder = $mCategories->getMaxIorder($post['id']) + 1;
				$post['iorder'] = $iorder;
				$getOne = $mCategories->getCateInfo($post['id']);

				if ($getOne === 0)
				{
					$post['path'] = '0';
					$post['parent'] = 0;
					$post['level'] = 1;
				}
				else
				{
					if ($getOne === NULL)
					{
						ajax_error('栏目类别ID错误');
					}
					else
					{
						if ($getOne['level'] == $num)	
						{
							$msg = '超出最大级数'.$num.'级';
							ajax_error($msg);
						}
					}
					$post['path'] = $getOne['path'];
					$post['parent'] = $getOne['id'];
					$post['level'] = $getOne['level'] + 1;
				}

				$post['status'] = 0;

				if ($post['id']) unset($post['id']);
				$len = abslength(clear_all($post['name']));
				if ($len > 10)
				{
					ajax_error('分类名称超出10个字');
				}
				if ($mCategories->create($post))
				{
					if ($mCategories->add($post))
					{
						$lastid = $mCategories->getLastInsID();
						$mCategories->id = $lastid;
						$allpath = $post['path'] . '-' .$lastid;
						$mCategories->path = $allpath;
						$mCategories->save();
						$result['lastid'] = $lastid;
						$result['name'] = $post['name'];
						$this->addTreeNode($post['parent'],$lastid,$post['name'],$allpath);
						$result['status'] = 'succ';
						$result['reason'] ='添加成功';

						ajax_succ($result);
					}
				}
				else
				{
					$msg = $mCategories->getError();
					ajax_error($msg);
				}
			}
		}

		private function addTreeNode($pid,$id,$name,$path) {
			$lCate = D('Cate','Logic','Common');
			$lCate->addTreeNode($pid,$id,$name,$path);
			return true;
		}

		private function editTreeNode($id,$name) {
			$lCate = D('Cate','Logic','Common');
			$lCate->editTreeNode($id,$name);
			return true;
		}

		/**
		 * 保存知识栏目设置
		 */
		public function cateedit() {
			// knowledge/categories/save
			//$a = $this->checkAuthorization('cate/edit');
			$mCategories = D('Categories', 'Model', 'Common');
			$rolekey = 'cate/edit';
			
			if ( IS_POST ) 
			{

				$roleRet = $this->checkAuthorization($rolekey, 'ajax');

				$id = I('post.id',0,'intval');
				$name = I('post.name','','trim');
				$code = I('post.code','','trim,strtolower');

				if ($id && !empty($name))
				{
					$info = $mCategories->find($id);
					if (!$info)
					{
						ajax_error('分类ID错误');
					}
					$condition['name'] = $name;
					$condition['id'] = array('not in',array($id));
					$condition['parent'] = $info['parent'];
					$exist = $mCategories->where($condition)->find();
					if ($exist)
					{
						ajax_error('分类名称已存在');
					}
					$data['id'] = $id;
					$data['name'] = $name;
					$data['code'] = $code;
					$lenn = abslength(clear_all($name));
					$lenc = strlen(clear_all($name));
					if ( $lenn > 10 ) {
						ajax_error('分类名称超出10个字!');
					}
					if ( $lenc > 20 ) {
						ajax_error('分类代码超过长度限制!');
					}
					$ret = $mCategories->save($data);
					if ( $ret ) {
						$this->editTreeNode($data['id'],$data['name']);
					} else {
						ajax_error('保存失败');
					}
				} else {
					ajax_error('请按正确的格式填写');
				}
				ajax_succ('保存成功!');
			}
		}
		public function getcates()
		{
			$id = I('get.id',0,'intval');
			$result = array(
				'status'=>false,
				'msg'=>'fail',
				'params'=>array(),
			);

			if ($id >= 0)
			{
				$params = array();
				$lCate = D('Cate','Logic','Common');
				$list = $lCate->getCateListById($id);
				if ($list)
				{
					foreach ($list as $key => $name) {
						$params[$key]['id'] = $key;
						$params[$key]['text'] = $name;
						$params[$key]['value'] = $key;
					}
					$params = array_values($params);
					$result['status'] = true;
					$result['msg'] = 'succ';
					$result['params'] = $params;
				}
			}
			$this->ajax_return($result);
		}

		/**
		 * 栏目向上调整顺序
		 */
		public function arrowup() {
			$result = array('status'=>false,'msg'=>'操作失败',);
			$oid = I('get.oid',0,'intval');
			$nid = I('get.nid',0,'intval');
			$rolekey = 'cate/edit';
			if ( IS_POST ) {
				$roleRet = $this->checkAuthorization($rolekey,'ajax');

				if ($oid && $nid)
				{
					$mCategories = D('Categories', 'Model', 'Common');

					$oinfo = $mCategories->getCateInfo($oid);
					$ninfo = $mCategories->getCateInfo($nid);
					if ($oinfo && $ninfo && ($oinfo['parent'] == $ninfo['parent']))
					{
						$omap['id'] = $oinfo['id'];
						$omap['iorder'] = $ninfo['iorder'];
						$nmap['id'] = $ninfo['id'];
						$nmap['iorder'] = $oinfo['iorder'];

						if ($mCategories->save($omap) && $mCategories->save($nmap))
						{
							$this->exchTreeNode($oinfo['parent'],$oid,$nid);
							ajax_succ();
						}
					}
					else
					{
						ajax_error('栏目ID错误');
					}
				}
				else
				{
					ajax_error('请按正确的格式填写');
				}
			}
		}

		private function exchTreeNode($pid,$oid,$nid) {
			$lCate = D('Cate','Logic','Common');
			$lCate->exchTreeNode($pid,$oid,$nid);
			return true;
		}

		private function delTreeNode($pid,$id) {
			$lCate = D('Cate','Logic','Common');
			$lCate->delTreeNode($pid,$id);
			return true;
		}

		/**
		 * 栏目隐藏状态切换接口
		 * @TODO: 连带栏目下的数据一并删除
		 */
		public function cateonoff() {
			$result = array(
				'status'=>false,
				'msg'=>'操作失败',
			);
			$rolekey = 'cate/del';
			if ( IS_POST )
			{
				$roleRet = $this->checkAuthorization($rolekey,'ajax');
				if ($roleRet['status'] == 'fail')
				{
					$result['msg'] = '权限不够';
					$this->ajax_return($result);
				}
				$id = I('post.id',0,'intval');
				if ($id)
				{
					$mCategories = D('Categories', 'Model', 'Common');
					$cate = $mCategories->find($id);
					$parent = $cate['parent'];
					if ($cate)
					{
						$lCate = D('Cate','Logic','Common');
						if ($cate['status'] == 0)
						{
							$data['id'] = $cate['id'];
							$data['status'] = 1;	// 隐藏状态，逻辑删除
							if ($mCategories->save($data))
							{
								$lCate->delTreeNode($parent,$id);
								$result['status'] = true;
								$result['msg'] = '操作成功';
							}
						}

						if ($cate['status'] == 1)
						{
							$data['id'] = $cate['id'];
							$data['status'] = 0;
							if ($mCategories->save($data))
							{
								$lCate->recoverTreeNode($parent,$id,$cate['name'],$cate['path']);
								$result['status'] = true;
								$result['msg'] = '操作成功';
							}
						}
					}
				}
			}

			$this->ajax_return($result);
		}

		/**
		 * 栏目删除操作
		 */
		public function catedel() {
			$result = array(
				'status'=>false,
				'msg'=>'操作失败',
			);
			$rolekey = 'cate/del';
			if ( IS_POST ) {
				$roleRet = $this->checkAuthorization($rolekey,'ajax');
				if ( $roleRet['status'] == 'fail' ) {
					$result['msg'] = '权限不够';
					$this->ajax_return($result);
				}
				$id = I('request.id',0,'intval');
				if ($id) {
					$mCategories = D('Categories', 'Model', 'Common');
					$cate = $mCategories->find($id);
					$parent = $cate['parent'];
					if ($cate) {
						$path = $cate['level']<3 ? $cate['path'].'-' : $cate['path'];

						// 删除栏目数据
						// 1. 内容数据表
						$mKnowledge = D('Knowledge', 'Model', 'Common');
						$where = array(
							'catepath' => array('like', $path.'%'),
						);
						$data_exists = $mKnowledge->where($where)->count();
						$data_exists = intval($data_exists);
						// 当且仅当指定栏目下没有任何知识数据时，才允许删除操作
						if ( $data_exists == 0 ) {
							$cnt1 = $mKnowledge->where($where)->delete();
							// 2. 历史数据表
							$mKnowledgeHistory = D('KnowledgeHistory', 'Model', 'Common');
							$cnt2 = $mKnowledgeHistory->where($where)->delete();
							// 3. 搜索服务数据表
							$lSearch = D('Search', 'Logic', 'Common');
							if ( $cate['level']==3 ) {
								$opts = array(
									array($cate['id'], '_multi.cateid')
								);
								$prefix = array();
							} else {
								$opts = array();
								$prefix = array(array($path, '_multi.catepath'));
							}
							$cnt3 = $lSearch->batchesRemove($opts, $prefix);
							// var_dump($lSearch, $opts, $prefix, $cnt3);
							// 4. 栏目信息删除
							$where = array('id' => $id);
							$cnt4 = $mCategories->where($where)->count();
							$result['debug'] = array(
								'knowledge' => $cnt1,
								'history' => $cnt2,
								'engine' => $cnt3,
								'category' => $cnt4,
							);
							if ( $cnt3 ) {
								$mCategories->where($where)->delete();
								// 清理栏目树缓存
								// 4. 清理缓存
								$lCate = D('Cate','Logic','Common');
								$lCate->delTreeNode($parent,$id);
								$result['status'] = true;
								$result['msg'] = '操作成功';
								$this->ajax_return($result);
							} else {
								$result['msg'] = '操作异常';
							}
						} else {
							$result['msg'] = '当前栏目下尚有知识数据 '.$data_exists.' 条 ，请先处理。';
						}
					} else {
						$result['msg'] = '数据已经删除，请刷新';
					}
				}
			} else {
				$result['msg'] = '请使用POST提交数据';
			}
			$this->ajax_return($result);
		}

	/**
	 * 后台接口 : 通过 楼盘编号查询楼盘信息数据 异步页面接口
	 * @TODO : 融合到基础类中
	 */
	public function gethouse()
	{
		$result = array(
			'status'=>false,
			'msg'=>'请输入正确的楼盘标识',
			'params'=>array(),
		);
		if ( IS_GET )
		{
			$houseid = I('get.houseid');
			$linfos = D('Infos','Logic','Common');
			$list = $linfos->getHouse($houseid);
			if ($list)
			{
				$result['status'] = true;
				$result['msg'] = 'succ';
				$result['params'] = $list[$houseid];
			}
		}

		$this->ajax_return($result);
	}

	/**
	 * 后台接口 : 通过 新闻编号查询楼盘信息数据 异步页面接口
	 * @TODO : 融合到基础类中
	 */
	public function getnews()
	{

		$result = array(
			'status'=>false,
			'msg'=>'请输入正确的新闻ID',
			'params'=>array(),
		);

		if ( IS_GET )
		{
			$id = I('get.id',0,'trim');
			if ($id)
			{
				$linfos = D('Infos','Logic','Common');
				$list = $linfos -> getNews($id);
				if ($list)
				{
					$result['status'] = true;
					$result['msg'] = 'succ';
					$result['params'] = $list[$id];
				}
			}
		}

		$this->ajax_return($result);
	}



	public function analyze() {
		$result = array (
			'status'=>false,
			'msg'=>'fail',
			'result'=>array(),
		);

		if ( IS_POST ) {
			$content = I('post.content','','strip_tags');
			$limit = I('post.limit', 100, 'intval');

			if (!empty($content))
			{
				$content = clear_all($content);
				$dict = C('ENGINE.PARSETAGS_ID');	// dict_tags
				$engine = D('Search', 'Logic', 'Common');
				$list = $engine->analyze($content, true, $limit, $dict);

				if ($list)
				{
					$result['status'] = true;
					$result['msg'] = 'succ';
					$result['info'] = $list;
				}
			}
		}
		$this->ajax_return($result);
	}



	protected function pushKonwledge($record)
	{
		$filter = array (
			'id'=>'','title'=>'','content'=>'','scope'=>'','cover'=>'','coverinfo'=>'','editorid'=>'','cateid'=>'','catepath'=>'','src_url'=>'','ptime'=>'','top_title'=>'','top_time'=>0,'top_cover'=>'','top_coverinfo'=>'','rcmd_time'=>0,'rcmd_title'=>'','rcmd_cover'=>'','rcmd_coverinfo'=>'','status'=>'','utime'=>'','ctime'=>'','version'=>'','src_type'=>'','top_coverinfo'=>'','rel_news'=>'','rel_house'=>'','tags'=>'','editor'=>'',
		);
		$record = array_intersect_key($record, $filter);
		$lPinyin = D('Pinyin', 'Logic', 'Common');
		$py = $lPinyin->get_pinyin($record['title']);
		$str = ucfirst($py);
		$record['content'] = clear_all($record['content']);
		$record['rel_news'] && $record['rel_news'] = json_decode($record['rel_news'],true);
		$record['rel_house'] && $record['rel_house'] = json_decode($record['rel_house'],true);
		$record['title_firstletter'] = substr($str, 0,1);
		$record['title_pinyin'] = $py;
		$record['url'] = 'show/id='.$record['id'];
		$record['id'] = strval($record['id']);
		return $record;

	}

	public function getHistoryVersion()
	{
		$result = array(
			'status'=>false,
			'msg'=>'失败',
			'list'=>array(),
		);
		if ( IS_GET )
		{
			$id = I('get.id',0,'intval');
			if ($id)
			{
				$mKnowledgeHistory = D('KnowledgeHistory', 'Model', 'Common');
				$list = $mKnowledgeHistory->getHistoryVersion($id);
				if ($list)
				{
					$result['status'] = true;
					$result['msg'] = '成功';
					$result['list'] = $list;
				}
				else
				{
					$result['status'] = true;
					$result['msg'] = 'ID错误';
				}
			}
		}
		$this->ajax_return($result);
	}

	public function suggest()
	{
		$result = array(
			'status'=>false,
			'msg'=>'失败',
			'list'=>array(),
		);
		$word = I('get.word','','trim');
		$engine = D('Search', 'Logic', 'Common');
		$list = $engine->suggest($word, $limit);
		if ($list)
		{
			$result['status'] = true;
			$result['msg'] = '成功';
			$result['list'] = $list;
		}

		$this->ajax_return($result);
	}


	/**
	 * 后台富文本编辑器页面，重要
	 */
	public function dialogs() {
		layout(false);
		$this->display('Public/image');
	}


	/**
	 * 用户所在的角色的操作权限验证
	 */
	private function Role ( $rolekey ) {
		// 基本操作权限判断
		$this->checkAuthorization($rolekey);
		// 指定属性验证
		$roleList = $this->getRoleList($rolekey);
		$roleCitys = $roleList['roleCitys'];
		$roleCate = $roleList['roleCate'];

		if ($roleCate === false) {
			$this->error('栏目权限不够');
		}	
		
		if ($roleCitys['scope'] === false) { 
			$this->error('城市权限不够');
		}

		$this->assign('cities', $roleCitys);
		return array('roleCate'=>$roleCate,'roleCitys'=>$roleCitys);
	}

	/**
	 * 从数据表中获取用户角色信息
	 */
	private function getRoleList($rolekey)
	{
		$lR = D('Role', 'Logic', 'admin');
		$role = $lR->getRoleList();
		$roleCate = $this->roleCate($role,$rolekey);
		$roleCitys = $this->roleCitys($role);	
		return array('roleCate'=>$roleCate,'roleCitys'=>$roleCitys);
	}

	/**
	 * 查看指定权限操作 $rolekey 是否在 $role 中被允许操作
	 */
	private function roleCate($role,$rolekey) {
		$authorities = json_decode($role[$this->_user['role_id']]['authorities'], true);
		// var_dump($authorities, $this->_user, $role[$this->_user['role_id']]);
		$roleCate = false;
		if ( $authorities ) {
			if ( $authorities[$rolekey] ) {
				$roleCate = $authorities[$rolekey];
			}
		}
		return $roleCate;
	}

	/**
	 * 获取用户角色对应的操作权限
	 */
	private function roleCitys ( $role ) {
		// $authorities = $role[$this->_user['role_id']]['city'];
		$authorities = strtolower(trim($this->_user['scope']));
		if ( $authorities=='' ) {
			return false;
		}
		$cities = C('CITIES.ALL');
		// var_dump($authorities); exit;
		//暂时为所有管理者使用全国城市，以后优化相关城市权限
		// $authorities = '_';
		if ($authorities == '_') {
			$all['_'] = array (
				'l' => 'A',
				'en' => '_',
				'cn' => '全国',
				'py' => '_',
			);
			$scope = array_merge($all,$cities);
		} else {
			$authorities = explode(',', $authorities);
			foreach ($authorities as $key => $en) {
				$scope[$en] = $cities[$en];
			}
		}
		return $scope;
	}


	private function verifyCateOp($cateid,$roleCate,$out=0) {
		if (!in_array($cateid, $roleCate)) {
			$result = array('status'=>false,'msg'=>'没有当前知识的栏目权限',);
			if ($out == 0) {
				$this->ajax_return($result);
			} else {
				ajax_error('没有当前知识的栏目权限');
			}
		}
		return true;
	}

	private function verifyCityOp($city,$roleCitys,$out=0) {
		if (!array_key_exists($city,$roleCitys)) {
			$result = array('status'=>false,'msg'=>'没有当前知识的城市权限',);
			if ($out == 0) {
				$this->ajax_return($result);
			} else {
				ajax_error('没有当前知识的城市权限');
			}
		}
		return true;
	}



}
