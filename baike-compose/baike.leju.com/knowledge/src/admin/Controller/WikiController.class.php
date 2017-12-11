<?php
/**
 * 词条百科控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class WikiController extends BaseController {

	protected $pagesize = 20;
	protected $UO = array();
	protected $SO = array();

	protected $mWiki = null;

	protected $action = array();

	protected $dict = null;
	protected function init_wiki_dicts() {
		if ( is_null($this->dict) ) {
			$dict = C('DICT.WIKI');
			$this->dict = $dict;
		}
		return $this->dict;
	}
	public function __construct() {
		parent::__construct();
		$this->mWiki = D('Wiki', 'Model', 'Common');
		$this->init_wiki_dicts();
	}

	/**
	 * 百科词条管理列表及搜索页面
	 */
	public function index(){
		// 权限验证
		$this->checkAuthorization('wiki/list');

		// 初始化字典
		$dicts = $this->init_wiki_dicts();
		$this->assign('dicts', $dicts);

		// 解析并处理查询条件
		$this->_handle_conditions();

		//请求接口查询列表数据
		$where = & $this->SO['where'];
		$total = $this->mWiki->where($where)->count();
		if ( $total > 0 ) {
			$fields = array('id', 'title', 'cateid', 'ptime', 'ctime', 'utime', 'status', 'src_type', 'extra', 'hits', 'editor');
			$this->mWiki->where($where)->field($fields);

			$order = & $this->SO['order'];
			if ( $order ) {
				$this->mWiki->order($order);
			}
			$page = & $this->SO['page'];
			$pagesize = & $this->SO['pagesize'];
			$list = $this->mWiki->page($page, $pagesize)->select();

			$ids = array();
			// 适配数据
			foreach ( $list as $i => &$item ) {
				array_push($ids, intval($item['id']));
				// 生成预览码
				$outpoint = intval( intval(date('i', NOW_TIME)) / 10 );
				$deadline = strtolower( date('Y-m-d H:'.($outpoint*10).':00', NOW_TIME) );
				$token = substr(md5($deadline), 0, 6);
				$id = intval($item['id']);
				$cateid = intval($item['cateid']);
				$item['viewurl'] = array(
					'pc' => url('show', array('id'=>$id, 'cateid'=>$cateid), 'pc', 'wiki'),
					'touch' => url('show', array('id'=>$id, 'cateid'=>$cateid), 'touch', 'wiki'),
				);
				$item['preview'] = array(
					'pc' => url('index', array(), 'pc', 'wiki') . 'Show-preview?id='.$id.'&nofit=1&token=' . $token,
					'touch' => url('index', array(), 'touch', 'wiki') . 'Show-preview?id='.$id.'&nofit=1&token=' . $token,
				);
				$item['extra'] = json_decode($item['extra'], true);
			}
			$this->mWiki->getRecommended($ids, $list);

		} else {
			$list = [];
		}
		$this->assign('list', $list);
		// echo '<!--', PHP_EOL, '查询条件', PHP_EOL,
		// 	 var_export($this->SO, true), PHP_EOL,
		// 	 var_export($this->mWiki->getLastSql(), true), PHP_EOL,
		// 	 '-->', PHP_EOL;
		// echo '<!--', PHP_EOL, '查询结果 共', $total, '条', PHP_EOL, var_export($list, true), PHP_EOL, '-->', PHP_EOL;

		// 处理分页条
		$linkopts = $this->UO;
		unset($linkopts['page']);
		$linkstring = http_build_query($linkopts);
		$linkstring = !empty($linkopts) ? '/Wiki/?page=#&'.$linkstring : '/Wiki/?page=#';
		$opts = array(
				'first' => true, //首页
				'last' => true,	//尾页
				'prev' => true, //上一页
				'next' => true, //下一页
				'number' => 5, //显示页码数
				'linkstring' => $linkstring
		);
		$pager = pager($page, $total, $pagesize, $opts);
		$this->assign('pager', $pager);
		$this->assign('form', $this->UO);
		// echo '<!--', PHP_EOL, '表单输入', PHP_EOL, var_export($this->UO, true), PHP_EOL, '-->', PHP_EOL;

		$this->display('index');
	}

	/**
	 * 知识内容审核
	 * @return ajax-json
	 */
	public function confirm(){
		// wiki/confirm
		echo '<h1>这是 词条内容审核 页面</h1>';
		if ( IS_POST ) {
		}
		// $this->display();
	}

	/**
	 * 列表页面搜索逻辑处理
	 * list -> [search]_handle_conditions -> list
	 */
	protected function _handle_conditions () {
		$this->SO = array('where'=>array(), 'page'=>1, 'pagesize'=>$this->pagesize, 'order'=>false);
		$S = I('get.', '', 'strip_tags,trim');
		$url_params = array_flip(array('page', 'pagesize', 'title', 'src_type', 'cateid', 'editor', 'ptime', 'status', 'sort'));
		$S = array_intersect_key($S, $url_params);

		// 设置查询分页
		$page = isset($S['page']) ? intval($S['page']) : 1;
		$pagesize = isset($S['pagesize']) ? intval($S['pagesize']) : $this->pagesize;
		if ( $page <= 1 ) {
			$page = 1; 
		} else {
			$this->UO['page'] = $page;
		}
		if ( $pagesize != $this->pagesize ) {
			if ( $pagesize < 5 ) $pagesize = 5;
			if ( $pagesize > 50 ) $pagesize = 50;
			$this->UO['pagesize'] = $pagesize;			
		}
		$this->SO['page'] = $page;
		$this->SO['pagesize'] = $pagesize;

		// 设置查询排序
		$orders = array(''=>'utime desc', '0'=>'hits asc', '1'=>'hits desc');
		$S['sort'] = trim($S['sort']);
		$S['sort'] = !array_key_exists($S['sort'], $orders) ? '' : $S['sort'];
		if ( $S['sort']!='' ) {
			$this->UO['sort'] = $S['sort'];
		}
		$this->SO['order'] = $orders[$S['sort']];

		// 组织查询条件
		if ( trim($S['title'])!='' ) {
			$V = trim($S['title']);
			$this->SO['where']['title'] = array('like', '%'.$V.'%');
			$this->UO['title'] = $V;
		}
		if ( trim($S['src_type'])!='' ) {
			$V = intval($S['src_type']);
			$this->SO['where']['src_type'] = $V;
			$this->UO['src_type'] = $V;
		}
		if ( trim($S['cateid'])!='' ) {
			$V = intval($S['cateid']);
			$this->SO['where']['cateid'] = $V;
			$this->UO['cateid'] = $V;
		}
		if ( trim($S['editor'])!='' ) {
			$V = trim($S['editor']);
			$this->SO['where']['editor'] = array('like', $V.'%');
			$this->UO['editor'] = $V;
		}
		if ( trim($S['ptime'])!='' ) {
			$V = strtotime($S['ptime']);
			$this->SO['where']['ptime'] = array('between', array(
				strtotime(date('Y-m-d 00:00:00', $V)),
				strtotime(date('Y-m-d 23:59:59', $V)),
			));
			$this->UO['ptime'] = $V;
		}
		if ( trim($S['status'])!='' ) {
			$V = intval($S['status']);
			$this->SO['where']['status'] = $V;
			$this->UO['status'] = $V;
		}

		return true;
	}

	/**
	 * 添加百科词条内容
	 */
	public function add(){
		//权限验证
		$this->checkAuthorization('wiki/add');

		$this->action['name'] = 'add';

		if ( IS_GET ) {
			$cates = $this->dict['CATE'];
			// 强制转换当前编辑词条的类型
			$info = array('cateid'=>0);
			$_cateid = intval($info['cateid']);
			$cateid = $this->_convert_wiki_cate($_cateid);
			if ( $cateid != $_cateid ) {
				$info['cateid'] = $cateid;
				$info['basic'] = array();
				$info['content'] = '';
			}
			$info['_changed'] = true;
			$this->assign('cateid', $cateid);
			// wiki/add
			$pageinfo = array(
				'crumb' => array(),
				'title' => '创建'.$cates[$cateid]['name'].'词条',
			);
			$this->assign('pageinfo', $pageinfo);
			$this->editor($cateid, $info);
		}
		if ( IS_POST ) {
			//获取表单内容
			$data = $this->filter();
			
			//保存数据
			$this->save($data);
		}
	}

	/**
	 * 编辑百科词条内容
	 */
	public function edit(){
		//权限验证
		$this->checkAuthorization('wiki/edit');

		$this->action['name'] = 'edit';

		if ( IS_GET ) {
			$id = I('get.id',0,'intval');
			$pkid = I('get.pkid',0,'intval');
			if (!$id) {
				$this->error('ID错误');
			}

			$cates = $this->dict['CATE'];
			$mWiki = D('Wiki', 'Model', 'Common');
			if ($pkid <= 0) {
				$info = $mWiki->find($id);
			} else {
				$binds['pkid'] = $pkid;
				$info = $mWiki->getHistoryVersion($id,$pkid);
			}
			if ( empty($info) ) {
				$this->error('词条不存在');
			}
			$this->assign('id', $id);
			$histories = $mWiki->getHistorys($id);
			$this->assign('histroy_info', $histories);

			// 强制转换当前编辑词条的类型
			$_cateid = intval($info['cateid']);
			$cateid = $this->_convert_wiki_cate($_cateid);
			if ( $cateid != $_cateid ) {
				$info['cateid'] = $cateid;
				$info['basic'] = array();
				$info['content'] = '';
				$info['_changed'] = true;
			}
			$this->assign('cateid', $cateid);
			// 来源如果是CRIC的数据 并且没有人编辑保存过的，请求使用编辑模版
			if ( $info['src_type']==2 && $info['editorid']==0 ) {
				$info['_changed'] = true;
			}
			// wiki/edit
			$pageinfo = array(
				'crumb' => array(),
				'title' => $cates[$cateid]['name'].'词条编辑',
			);
			$this->assign('pageinfo', $pageinfo);


			if ( $_cateid==1 ) {
				if ( trim($info['company_stock_code'])!='' ) {
					$_t = explode('.', $info['company_stock_code']);
					$info['listmarket'] = $_t[0];
					$info['listcode'] = $_t[1];
				}
				if ( intval($info['company_parent_id'])>0 ) {
					$parent_where = array(
						// 'status' => 9,
						'id' => intval($info['company_parent_id']),
					);
					$parent = $mWiki->field('title')
									->where($parent_where)
									->find();
					if ( $parent ) {
						$info['company_parent_name'] = trim($parent['title']);
					} else {
						$info['company_parent_id'] = 0;
						$info['company_parent_name'] = '';
					}
				}
			}
			$info = $mWiki->convertFields($info, false);
			$this->editor($cateid, $info);
		}
		if ( IS_POST ) {
			//获取表单内容
			$data = $this->filter();
			//保存数据
			$this->save($data);
		}
	}

	protected function filter() {
		$this->action['type'] = '';
		if ( isset($_POST['action_type']) ) {
			$this->action['type'] = strtolower(trim($_POST['action_type']));
		}
		if ( !in_array($this->action['type'], array('save', 'publish')) ) {
			$this->action['type'] = 'save';
		}
		// 保留的数据库字段
		$filters = array (
			'id'=>'','ctime'=>'','cateid'=>'','src_type'=>'','extra'=>'',
			'title'=>'','stname'=>'','short'=>'','summary'=>'','content'=>'',
			'cover'=>'','media'=>'','editor'=>'','editorid'=>'',
			'ptime'=>'','tags_id'=>'','tags_name'=>'',
			'album'=>'','basic'=>'','recommend'=>'','rel'=>'','seo'=>'',
			'city'=>'',
			'business_line'=>'', 'is_recommended'=>'',
			'person_rank_title'=>'','person_rank_link'=>'', 'ranklist'=>'',
			'company_cric_id'=>'', 'company_parent_id'=>'', 'listmarket'=>'', 'listcode'=>'', 
		);
		$data = array_intersect_key($_POST, $filters);
		// 知识来源
		if ( isset($data['src_type']) ) {
			$data['src_type'] = intval($data['src_type']);
		}
		// 标签参数
		if ( isset($data['tags_id']) && isset($data['tags_name']) ) {
			$tagids = explode(',', $data['tags_id']);
			foreach ( $tagids as $_i => $tagid ) {
				$tagid = intval($tagid);
				if ( $tagid==0 ) {
					unset($tagids[$_i]);
				}
			}
			$tags = explode(',', $data['tags_name']);
			foreach ( $tags as $_i => $tag ) {
				$tag = trim($tag);
				if ( $tag=='' ) {
					unset($tags[$_i]);
				}
			}
			$data['tags'] = implode(' ', $tags);
			$data['tagids'] = ','.implode(',', $tagids).',';
			unset($data['tags_id']);
			unset($data['tags_name']);
		} else {
			$data['tags'] = '';
			$data['tagids'] = ',,';
		}
		if ( !isset($data['album']) ) {
			$data['album'] = array('title'=>'','cover'=>array('pc'=>'','h5'=>''), 'list'=>array());
		}
		if ( !isset($data['rel']) ) {
			$data['rel'] = array('news'=>array(),'houses'=>array(),'companies'=>array(),'figures'=>array());
		}
		if ( !isset($data['recommend']) ) {}
		if ( !isset($data['seo']) ) {
			$data['seo'] = array('title'=>'','keywords'=>'','description'=>'');
		}

		$cateid = intval($data['cateid']);
		$data['company_parent_id'] = intval($data['company_parent_id']);
		if ( $cateid==1 ) {	// 公司
			$data['stname'] = clean_xss($data['stname']);
			$data['short'] = clean_xss($data['short']);
			if ( $data['company_parent_id']>0 ) { // 指定了当前公司百科的上级公司时
				$parent = D('Wiki', 'Model', 'Common')
							->field(['id','src_type','company_cric_id'])
							->where(['id'=>$data['company_parent_id']])
							->find();
				if ( intval($parent['src_type'])==2 && intval($data['src_type'])==2 ) { // 来源是CRIC导入的数据
					$data['company_cric_id'] = trim($parent['company_cric_id']);
				}
			}
			// 上市市场信息
			if ( trim($data['listmarket'])!='' && trim($data['listcode'])!='' ) {
				$data['company_stock_code'] = strtoupper(trim($data['listmarket'])).'.'.trim($data['listcode']);
			} else {
				$data['company_stock_code'] = '';
			}
			if ( !isset($data['ranklist']) ) {
				$data['ranklist'] = [];
			} else {
				$data['ranklist'] = array_values($data['ranklist']);
			}
		}
		if ( $cateid==2 ) {	// 人物
			$data['person_rank_title'] = trim($data['person_rank_title']);
			$data['person_rank_link'] = trim($data['person_rank_link']);
		}
		unset($data['listmarket']); unset($data['listcode']);

		$data['utime'] = $data['version'] = NOW_TIME;

		$data['title'] = clean_xss($data['title']);
		$data['summary'] = clean_xss($data['summary']);
		$data['content'] = clean_xss($data['content']);

		isset($data['ptime']) && trim($data['ptime'])!='' && $data['ptime'] = strtotime($data['ptime']);
		empty($data['editor']) && $data['editor'] = $this->_user['truename'];

		$py = D('Pinyin', 'Logic', 'Common')->get_pinyin($data['title']);
		$data['pinyin'] = $py;
		$py = strtoupper(substr($py, 0, 1));
		$data['firstletter'] = ( $py < 'A' || $py > 'Z' ) ? '#' : $py;

		return $data;
	}

	/**
	 * 强制变更编辑器的词条类别
	 * @param $cate int 默认或原词条的类别编号
	 * @return int 最终词条类别编号
	 */
	protected function _convert_wiki_cate( $cate=0 ) {
		$cateid = I('get.cateid', false, 'trim');
		$cates = & $this->dict['CATE'];
		// 如果不是强制指定新类型，使用原始数据的词条类型
		if ( $cateid===false ) {
			$cateid = $cate;
		}
		$cateid = intval($cateid);
		if ( !array_key_exists($cateid, $cates) ) {
			$cateid = 0;
		}
		$this->assign('cateid', $cateid);
		return $cateid;
	}
	/**
	 * 当前管理员的推荐权限处理
	 */
	protected function _get_recommend_authorities() {
		// 词条编辑器与角色权限绑定
		$set_auth = array('wiki_focus'=>1, 'wiki_person'=>1, 'wiki_company'=>1);
		$filters = array(
			'recommend/wiki_focus'=>'wiki_focus',
			'recommend/wiki_person'=>'wiki_person',
			'recommend/wiki_company'=>'wiki_company',
		);
		// var_dump($this->authorities);
		$authorities = array_flip(array_values(array_intersect_key($filters, $this->authorities)));
		$authorities = array_intersect_key($set_auth, $authorities);
		// var_dump($authorities);
		return $authorities;
	}
	/**
	 * 词条内容编辑器
	 * @param $cate int 0普通词条 1机构词条 2人物词条
	 * @param $data array 词条信息内容
	 */
	protected function editor ( $cateid=0, $data=array() ) {
		$cates = &$this->dict['CATE'];
		// var_dump($cate, $cates, array_key_exists($cate, $cates));
		if ( !array_key_exists($cateid, $cates) ) {
			$cateid = 0;
		}
		// 词条编辑器与角色权限绑定
		$this->assign('recommend_auth', $this->_get_recommend_authorities());

		if ( $this->action == 'edit' ) {
			$data = D('Wiki', 'Model', 'Common')->convertFields($data, true);
		}
		if ( isset($data['_changed']) && $data['_changed']===true ) {
			$data['basic'] = array();
		}
		$this->assign('dicts', $this->dict);
		$this->assign('cities', C('CITIES.ALL'));

		// 已经推荐出去的数据
		$lRecommend = D('Recommend', 'Logic', 'Common');
		$relid = intval($data['id'])<=0 ? 0 : intval($data['id']);
		$isparent = false;
		// var_dump($data['id'], $relid, $data);
		if ( $relid > 0 ) {
			$ret = $lRecommend->getRecommends('wiki', $relid);
			$recommends = array();
			foreach ( $ret as $i => $item ) {
				$flag = intval($item['flag']);
				$recommends[$flag] = array(
					'status' => $flag,
					'extra' => $item['extra'],
				);
			}
			$this->assign('recommends', $recommends);
			// 查找当前词条是否被设定为其它词条的父词条，如果已经是父词条的情况下，不允许进行父词条设置
			$_where = ['company_parent_id'=>$relid, 'status'=>['in', [1,9]]];
			$mWiki = D('Wiki', 'Model', 'Common');
			$isparent = !!$mWiki->where($_where)->count();
		}
		$this->assign('isparent', $isparent);
		// echo '<!--', PHP_EOL, $id, PHP_EOL, print_r($recommends, true), PHP_EOL, '-->', PHP_EOL;

		$this->assign('data', $data);

		// echo '<!--', PHP_EOL, print_r($data, true), PHP_EOL, '-->', PHP_EOL;
		$this->assign('current_action', $this->action['name']);

		// $editor_tpl = 'editor.'.$cate;
		$editor_tpl = 'editor';
		$this->display($editor_tpl);
	}


	/**
	 * 保存词条内容
	 */
	protected function save ( $data=array() ) {

		$title = trim($data['title']);
		$stname = $data['stname'];
		if ( $title == $stname ) {
			$this->ajax_error('提示词条中文和别的词条简称重复');
		}

		if ( $data['person_rank_title']!='' ) {
			$cnt = abslength($data['person_rank_title']);
			if ( $cnt > 15 ) {
				$this->ajax_error('活动名称不能超过15个汉字');
			}
		}
		if ( $data['person_rank_link']!='' ) {
			$info = parse_url($data['person_rank_link']);
			if ( $info===false || !isset($info['host']) ) {
				$this->ajax_error('跳转链接不能为空');
			}
		}

		// 确认操作逻辑
		$action = $this->action['type'];
		$actions = array('publish', 'save');
		if ( !in_array($action, $actions) ) {
			$this->ajax_error('请指定您要做的操作！');
		}

		$lPublish = D('WikiPublish', 'Logic', 'Common');

		$recommends = $data['recommend']; unset($data['recommend']);

		$act_name = intval($data['id'])==0 ? '创建' : '修改';
		$mWiki = D('Wiki', 'Model', 'Common');
		$ret = $mWiki->verifyData($data);
		if ( $ret!==true ) {
			$this->ajax_error($ret.'，'.$act_name.'失败');
		}

		$_ret = false;
		// 进行发布处理
		if ( in_array($action, array('publish')) ) {
			$log_actid = intval($data['id'])==0 ? WIKI_ADDPUB_ACT : WIKI_MODPUB_ACT;
			$_ret = $lPublish->Publish($data);
		}
		// 进行保存草稿处理
		if ( $action=='save' ) {
			$log_actid = intval($data['id'])==0 ? WIKI_ADDSAVE_ACT : WIKI_MODSAVE_ACT;
			$_ret = $lPublish->Save($data);
		}

		$id = $lPublish->getId();
		if ( $id ) {
			$lRecommend = D('Recommend', 'Logic', 'Common');
			if ( $action == 'publish' ) {
				$lRecommend->batchRecommend('wiki', $id, $recommends);
				$taginfo = array(
					'tag' => trim($data['title']),
					'remark' => str_replace(PHP_EOL, '', trim(strip_tags($data['summary']))),
					'pic' => trim($data['cover']),
				);
				// D('Tags', 'Logic', 'Common')->syncToTag($taginfo);
			}
			if ( $action == 'save' ) {
				$lRecommend->batchClean('wiki', $id);
			}

			// 更新相关子公司的cricid数据
			if ( intval($data['id'])>0 && intval($data['cateid'])==1 && intval($data['company_parent_id'])==0 ) {
				$wiki_id = intval($data['id']);
				$cric_id = trim($data['company_cric_id']);
				$mWiki->updateCompanyCricID($wiki_id, $cric_id, $action);
			}
			// 清理详情页缓存
			$key = "WIKI:DETAIL:{$id}:CORE";
			$cacher = S(C('REDIS'));
			$cacher->Del($key);

		} else {
			// 操作失败
			$result = array('status' => false, 'reason' => $lPublish->getError(),);
			$this->ajax_return($result);
		}

		if ( $ret ) {
			// @TODO: 处理 id => recommend 里的推荐信息
			// 创建审计日志可读文本
			$log_note = array(
				'status' => true, // 操作执行状态
				'reason' => '',	// 错误时的原因
				'actor' => trim($this->_user['truename']),	// 操作者
				'actorid' => intval($this->_user['id']),	// 操作者id
				'id' => intval($id),	// 知识内容编号
				'title' => $data['title'],
			);
			$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));

			$result = array('status' => true, 'reason' => $act_name.'成功',);
		} else {
			if ( $id > 0 ) {
				// 创建审计日志可读文本
				$log_note = array(
					'status' => false, // 操作执行状态
					'reason' => $result['reason'],	// 错误时的原因
					'actor' => trim($this->_user['truename']),	// 操作者
					'actorid' => intval($this->_user['id']),	// 操作者id
					'id' => intval($id),	// 知识内容编号
					'title' => $data['title'],
				);
				$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));
			}
			$result = array('status' => false, 'reason' => $lPublish->getError(),);
		}
		$this->ajax_return($result);
	}

	/**
	 * 后台联想词猜测搜索
	 * 查询关联企业 : /admin.baike.leju.com/Wiki/suggest/?cateid=1&word=
	 * 查询关联人物 : /admin.baike.leju.com/Wiki/suggest/?cateid=2&word=
	 * 参数 : 
	 * 1. word : 要查询的词条名称字符串
	 * 2. cateid : 指定查询类型 默认为""搜索全部词条，1为企业 2为人物 0为普通
	 * 3. parent : 是否只查询父公司（即上级公司）""或0是默认值 ，表示所有公司 1为只查询父公司为空的公司，即查询顶级公司设定的词条名 
	 * 3. status : 指定词条状态 默认9表示已发布
	 * 4. num : 指定返回的词条数量 默认5条 有效范围 1~20
	 */
	public function suggest() {
		$word = I('get.word', '', 'trim,clear_all,clean_xss');
		$cateid = I('get.cateid', '', 'trim');
		// 是否只查询父公司，默认不传为0，表示所有公司，仅在 cateid = 1 时有效
		$isroot = I('get.parent', 0, 'intval');
		$status = I('get.status', 9, 'intval');
		$num = I('get.num', 5, 'intval');

		if ( $word=='' ) {
			$this->ajax_error('请输入要查询的词条名称');
		}
		if ( $num > 20 || $num < 1 ) {
			$num = 5;
		}
		$word = str_replace('%', '', $word);
		$mWiki = D('Wiki', 'Model', 'Common');
		$field = array('id', 'title');
		$where = array('status' => $status,);
		if ( array_key_exists($cateid, $this->dict['CATE']) ) {
			$where['cateid'] = intval($cateid);
		}
		// 查询公司时，判断是否查询上级公司
		if ( $cateid==1 && $isroot!=0 ) {
			$where['company_parent_id'] = 0;
			// 修改模式，查询所有与当前词条编号不同的顶级公司
			if ( $isroot > 0 ) {
				// 查找当前词条是否被设定为其它词条的父词条，如果已经是父词条的情况下，不允许进行父词条设置
				$isparent = $mWiki->where(array('company_parent_id'=>$isroot))->count();
				if ( $isparent > 0 ) {
					$reason = '当前词条已经是顶级公司词条，不允许再为其设置父公司词条';
					$this->ajax_error($reason);
				}
				$where['id'] = array('neq', $isroot);
			}
		}
		$where['title'] = array('like', $word.'%');

		$list = $mWiki->field($field)->where($where)->page(1, $num)->order('id desc')->select();
		foreach ( $list as $i => &$item ) {
			if ( $status==9 ) {
				$item['url'] = url('show', array($item['id'], $item['cateid']), 'pc', 'wiki');
			}
		}
		$result = array('status'=>true,'list'=>$list);
		// $result['_dbg'] = $mWiki->getLastSql();
		$this->ajax_return($result);
	}

	/**
	 * 标签联想词
	 * ? 是否还在使用？
	 * @param $word 输入的标签
	 * @return json 数据集合
	 */
	public function suggestWord() {
		$result = array('status'=>true);
		$word = I('get.word','','trim');
		$limit = I('get.limit',5,'intval');
		$engine = D('Search', 'Logic', 'Common');
        $list = $engine->suggest($word, $limit);
		if ($list) {
			$result['info'] = $list;
			$this->ajax_return($result);
		}
	
		$result['reason'] = '无匹配的标签';
		$this->ajax_return($result);
	}

	/**
	 * 搜索公司词条关联的项目公司名称
	 * @type: restful_api
	 * @date: 2017-10-10
	 * @developer: Robert <yongliang1@leju.com>
	 * @requirement: 从新闻池楼盘库获取开发商名称
	 * @project_manager: 赵珊
	 */
	public function suggestDeveloper() {
		$word = I('get.word', '', 'trim,clear_all,clean_xss');
		if ( $word=='' ) {
			$this->ajax_error('请输入要查询的开发商名称');
		}
		$ret = D('Infos','Logic','Common')->getDeveloperName($word, 1, 10);
		$result = array( 'status' => true, 'list' => [] );
		foreach ( $ret['list'] as $i => $item ) {
			array_push($result['list'], array('name'=>$item['developer']));
		}
		$this->ajax_return($result);
	}

	/**
	 * 删除词条
	 * @TODO
	 */
	public function delete() {
		//权限验证
		$this->checkAuthorization('wiki/del', 'ajax');
		// wiki/delete
		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$this->ajax_error('请指定要删除的百科编号');
		}

		$mWiki = D('Wiki', 'Model', 'Common');
		$info = $mWiki->find($id);
		if ( !$info ) {
			$this->ajax_error('没有指定数据');
		}

		// 创建审计日志可读文本
		$log_note = array(
			'status' => true, // 操作执行状态
			'reason' => '',	// 错误时的原因
			'actor' => trim($this->_user['truename']),	// 操作者
			'actorid' => intval($this->_user['id']),	// 操作者id
			'id' => intval($id),			// 被操作管理员id
			'title' => trim($info['title']),
		);
		$log_actid = WIKI_DEL_ACT;		//第二套删除

		// 删除逻辑处理
		$mWiki->status = -1;
		$mWiki->utime = NOW_TIME;
		$ret = $mWiki->save();
		if ($ret) {
			$this->_logger->addLog($this->_user['id'], $log_actid, $id, json_encode($log_note));

			$lPublish = D('WikiPublish', 'Logic', 'Common');
			$lPublish->MarkDelete($info);
			// // 删除服务接口的数据
			// $lSearch = D('Search', 'Logic', 'Common');
			// $ret['push'] = $lSearch->removeWiki(array($id));
			// // 伪删除时也对新闻池推送删除操作 删除新闻池相关数据
			// $lInfos = D('Infos','Logic','Common');
			// $lInfos->pushNewsPool($data=array('id'=>$id), $lInfo true);
		} else {
			$error_msg = '删除操作失败';
			// 删除异常
			$log_note['status'] = false;
			$log_note['reason'] = $error_msg;
			$this->_logger->addLog($this->_user['id'], $log_actid, $uid, json_encode($log_note));
			$this->ajax_error($error_msg);
		}
		$result = array('status'=>true, 'reason'=>'删除成功');
		$this->ajax_return($result);
	}

	// 测试使用，随时可删除
	public function testMarkDelete() {
		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$this->ajax_error('请指定要删除的百科编号');
		}

		$mWiki = D('Wiki', 'Model', 'Common');
		$info = $mWiki->find($id);
		if ( !$info ) {
			$this->ajax_error('没有指定数据');
		}
		var_dump($info);
		$lPublish = D('WikiPublish', 'Logic', 'Common');
		$ret = $lPublish->MarkDelete($info);
		var_dump($ret);
	}

	/**
	 * 相关资讯
	 * @TODO
	 * @param $newsid 新闻id
	 * @return json 数据集合
	 */
	public function getNews()
	{
		$newsid = I('get.newsid', 0, 'trim,strip_tags');
		$recommender = D('Infos', 'Logic','Common');
		$news_result = $recommender->getNews($newsid);
		if($news_result)
		{
			$result = array('status'=>true, 'info'=>$news_result[$newsid]);
			$this->ajax_return($result);
		}
		$this->ajax_error(array("newsid" => $newsid, "msg" => "无相关资讯"));
	}
	
	/**
	 * 相关楼盘
	 * @TODO
	 * @param $houseid 楼盘id
	 * @return json 数据集合
	 */
	public function getHouse()
	{
		$hid = I('get.hid', '', 'trim,strip_tags');
		$recommender = D('Infos', 'Logic','Common');
		$house_result = $recommender->getHouse($hid);
		if($house_result)
		{
			$result = array('status'=>true, 'info'=>$house_result[$hid]);
			$this->ajax_return($result);
		}
		$this->ajax_error(array("hid" => $hid, "msg" => "无相关楼盘"));
	}
	
	/**
	 * 内容中匹配乐居标签
	 * @TODO
	 * @param $content 内容
	 * @return json 数据集合
	 */
	public function getContentTags()
	{
		$content = I('post.content', '', 'trim,strip_tags,htmlspecialchars');
		$limit =I('post.limit', 0, 'intval');
		$search_content = D('Search', 'Logic', 'Common');
		$tags_result = $search_content->analyze($content, true, $limit);

		if($tags_result)
		{
			$result = array('status'=>true, 'info'=>$tags_result);
			$this->ajax_return($result);
		}
		$this->ajax_error(array("msg" => "无匹配的标签"));
	}


	
	public function removeAllredis($id)
	{
		//清除缓存
		$r = S(C('REDIS'));
		$r->rm('wiki:tag:hot2');
		$this->removeDetailredis($id);
		$api_domain = getDomain('API');
		curl_get($api_domain . "Cron/hotWords");
		$this->removeRedis();
	}
	public function removeRedis()
	{
		//清除缓存
		$r = S(C('REDIS'));
		$r->rm('wiki:tag:list:cate-0');
		$r->rm('wiki:tag:list:cate-1');
		$r->rm("wiki:tag:list:all");
		$r->rm("wiki:tag:fresh2");
		$this->removeSomeredis();
	}
	public function removeSomeredis()
	{
		$r = S(C('REDIS'));
		$r->rm('wiki:tag:focus2');
		$r->rm('wiki:tag:human2');
		$r->rm('wiki:tag:organization2');
	}
	public function removeDetailredis($id)
	{
		$r = S(C('REDIS'));
		$r->rm("wiki:tag:detail2:{$id}");
	}

}