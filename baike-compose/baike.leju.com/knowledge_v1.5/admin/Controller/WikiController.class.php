<?php
/**
 * 词条百科控制逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace admin\Controller;
use Think\Controller;

class WikiController extends BaseController {
	/**
	 * 知识管理列表及搜索页面
	 */
	public function index(){
		//权限验证
		$this->checkAuthorization('wiki/list');
		
		//分页处理
		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 10, 'intval');
		if($page < 1) $page = 1;
		if($pagesize < 1) $pagesize = 1;
		
		//处理搜索表单
		$form = I('get.', '', 'strip_tags,trim');
		if($form['releasetime'])
		{
			$form['releasetime'] = strtotime($form['releasetime']);
		}

		$array = array("entry" => '', "from" => '', "category" => '', "creator" => '', "releasetime" => '',
				 "check" => '', "sort" => '');
		$newform = array_intersect_key($form, $array);
		$newform = array_merge($newform, array("page" => $page, "pagesize" => $pagesize));
		if(empty($newform['sort']))$newform['sort'] = "time";
		if($newform['check'] == '')$newform['check'] = 2;
		//请求接口查询列表数据
		$url = C('DATA_TRANSFER_API_URL') . "api/item?mode=1&search=1";

		$result = curl_get($url, $newform);

		$info = array();
		if($result['status'] == true)
		{
			$info = json_decode($result['result'], true);
		}
		
		//分页模块
		$total = $info['total'];
		if($total)
		{
			//封装linkopts
			$linkopts = array();
			$form['entry'] && array_push($linkopts, "entry={$form['entry']}");
			$form['from'] != '' && array_push($linkopts, "from={$form['from']}" );
			$form['category'] != '' && array_push($linkopts, "category={$form['category']}");
			$form['creator'] && array_push($linkopts, "creator={$form['creator']}");
			$form['releasetime'] && array_push($linkopts, "releasetime={$form['releasetime']}");
			$form['check'] != '' && array_push($linkopts, "check={$form['check']}");
			$form['sort'] && array_push($linkopts, "sort={$form['sort']}");
			$linkstring = !empty($linkopts) ? '/Wiki/?page=#&'.implode('&',$linkopts) : '/Wiki/?page=#';

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
		}

		$this->assign('list', $info['result']);
		$this->assign('form',$form);
		//当前时间用于和定时时间比较
		$this->assign('nowtime',time());
		
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
	 * 添加百科词条内容
	 */
	public function add(){
		//权限验证
		$this->checkAuthorization('wiki/add');
		// wiki/add
		$pageinfo = array(
			'crumb' => array(),
			'title' => '添加词条内容',
		);
		$this->assign('pageinfo', $pageinfo);

		if ( IS_GET ) {
			$this->editor();
		}
		if ( IS_POST ) {
			//获取表单内容
			$form = I('post.', '', 'trim');
			//var_dump($form);exit;
			//对表单进行验证
			$this->valiDate($form);
			//第二版入库操作
			//$this->save_bak($form);
			
			//保存数据
			$this->save($form);
		}
		// $this->display();
	}

	/**
	 * 编辑百科词条内容
	 */
	public function edit(){
		//权限验证
		$this->checkAuthorization('wiki/edit');
		// wiki/edit
		$pageinfo = array(
			'crumb' => array(),
			'title' => '编辑词条内容',
		);
		$this->assign('pageinfo', $pageinfo);

		if ( IS_GET ) {
			$id = I('get.id', '', 'strip_tags,trim');
			$history_id = I('get.history_id', '', 'strip_tags,trim');
			
			//获取历史版本
			$history_url = C('DATA_TRANSFER_API_URL') . "api/item/historylist?id={$id}";
			$result = curl_get($history_url);
			$history_info = array();
			if($result['status'] == true)
			{
				$history_info = json_decode($result['result'], true);
			}
			
			//获取根据creatorid获取对应用户用户
			if($history_info['result'])
			{
				$users = array();
				$ids = array();
				foreach($history_info['result'] as $v)
				{
					if($v['creatorid'])
					$ids[] = $v['creatorid'];
				}
				$ids = implode(",", $ids);
				if($ids)
				{
					$admin = D('admins', 'Model', 'Common');
					$users = $admin->where(" id in({$ids})")->getField('id, truename');
				}
				$this->assign('users', $users);
			}
			$this->assign('histroy_info', $history_info['result']);
			
			// 从数据库中读取 PK => id 的数据
			
			//获取单个词条的详细内容
			$entry_url = C('DATA_TRANSFER_API_URL') . "api/item?id={$id}&mode=1";
			
			//获取历史版本信息
			if($history_id != '')
			{
				$entry_url = C('DATA_TRANSFER_API_URL') . "api/item/history?id={$history_id}";
			}
			$entry_result = curl_get($entry_url);

			$data = array();
			if($entry_result['status'] == true)
			{
				$data = json_decode($entry_result['result'], true);
			}
			if(empty($data['result']))
			{
				$this->error("此词条不存在");
			}
			
			$this->assign('id', $id);
			$this->assign('history_id', $history_id);
			$this->editor($data);
		}
		if ( IS_POST ) {
			//获取表单内容
			$form = I('post.', '', 'trim');
			
			//对表单进行验证
			$this->valiDate($form, 'edit');
			//第二版入库操作
			//$this->edit_bak($form);
			
			//保存数据
			$this->save($form);
		}
		// $this->display();
	}

	/**
	 * 词条内容编辑器
	 */
	protected function editor ( $data=array() ) {
		define('CDN_IMG_URL','//cdn.leju.com/encyclopedia/');
		$this->assign('cdn_img_url', CDN_IMG_URL);
		$this->assign('data', $data['result']);
		$this->display('editor');
	}

	/**
	 * 保存词条内容
	 */
	protected function save ( $data=array() ) {
		if(strtotime($data['releasetime']) > NOW_TIME)
		{
			$data['releasetime'] = strtotime($data['releasetime']);
		}
		else 
		{
			$data['releasetime'] = time();
		}
 		$array = array("id" => '', "entry" => '', "pinyin" => '', "from" => '', "fromurl" => '', 
 				"content" => '', "pic" => '', "recommend" => '', "tags" => '', "category" => '', 
 				"creator" => '', "releasetime" => '', "news" => '', "house" => '', 'text' => '',
 				 "creatorid" => '', "checkid" => '');
 		
		
		$new_form = array_intersect_key($data, $array);
		$new_form['tags'] = json_decode($new_form['tags']);
		$new_form['house'] = json_decode($new_form['house']);
		$new_form['news'] = json_decode($new_form['news']);
		//var_dump($new_form);exit;
		$url = C('DATA_TRANSFER_API_URL') . "api/item?id={$data['id']}&check=0";
		$save_result = curl_post($url, json_encode($new_form));
		$api_result = json_decode($save_result['result'], true);

		if($save_result['status'] == true && $api_result['id'] != '')
		{
			$r = S(C('REDIS'));
			$r->rm('wiki:tag:focus');
			$r->rm('wiki:tag:human');
			$r->rm('wiki:tag:organization');
			$r->rm('wiki:tag:fresh');
			$r->rm('wiki:tag:list:pcall');
			$r->rm('wiki:tag:list:all');
			$r->rm('wiki:tag:tag');
			if($data['id'])$r->rm("wiki:tag:detail:{$data['id']}");
			if(strtotime($data['releasetime']) > NOW_TIME)$r->rm('wiki:tag:hot');
			
			usleep(200000);
			ajax_succ(json_decode($save_result['result']));
		}
		if($api_result['id'] == '')
		{
			ajax_error(array("id" => '', "msg" => $api_result['msg']));
		}
		
		if($data['id'] != '')
		{
			ajax_error(array("id" => $data['id'], "msg" => "编辑失败"));
		}
		ajax_error(array("msg" => "添加失败"));
		//echo '<h3>保存词条内容</h3>';
	}
	/**
	 * 删除词条
	 */
	public function delete()
	{
		//权限验证
		$this->checkAuthorization('wiki/del', 'ajax');
		// wiki/delete
		$id = I('get.id', '', 'trim,strip_tags');
		
		//第二套删除
		//$this->delete_bak($id);
		
		$url = C('DATA_TRANSFER_API_URL') . "api/item/_delete?id=" . $id;
		$result = curl_get($url);
		if($result['status'] == true)
		{
			$r = S(C('REDIS'));
			$r->rm('wiki:tag:focus');
			$r->rm('wiki:tag:human');
			$r->rm('wiki:tag:organization');
			$r->rm('wiki:tag:fresh');
			$r->rm('wiki:tag:hot');
			$r->rm("wiki:tag:detail:{$id}");
			$r->rm('wiki:tag:list:pcall');
			$r->rm('wiki:tag:list:all');
			$r->rm('wiki:tag:tag');
			ajax_succ(json_decode($result['result']));
		}
		ajax_error("删除失败");
	}
	/*
	 * 兼容第一套删除时第二套根据名称删除
	 */
	private function delete_bak($id)
	{
		$entry_url = C('DATA_TRANSFER_API_URL') . "api/item?id={$id}&mode=1";
		$entry_result = curl_get($entry_url);
		$title  = "";
		if($entry_result['status'] == true)
		{
			$data = json_decode($entry_result['result'], true);
			$title  = $data['result'][0]['entry'];
			
			$wiki = D('Wiki', 'Model', 'Common');
			$info = $wiki->where(" title = '{$title}' AND status != -1")->find();
			if($info)
			{
				$ret = $wiki->where("id = " . $info['id'])->save(array("status" => -1));
				if ($ret)
				{
					//清除缓存
					$this->removeAllredis($info['id']);
					
					$lSearch = D('Search', 'Logic', 'Common');
					$ret['push'] = $lSearch->removeWiki(array($info['id']));
					// 通过服务接口向字典中删除词条
					$ret['remove_word'] = $lSearch->removeDictWords(array($title), 'dict_wiki');
				}
			}
		}
		
	}
	/**
	 * 相关资讯
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
			ajax_succ($news_result[$newsid]);
		}
		ajax_error(array("newsid" => $newsid, "msg" => "无相关资讯"));
	}
	
	/**
	 * 相关楼盘
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
			ajax_succ($house_result[$hid]);
		}
		ajax_error(array("hid" => $hid, "msg" => "无相关楼盘"));
	}
	
	/**
	 * 内容中匹配乐居标签
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
			ajax_succ($tags_result);
		}
		ajax_error(array("msg" => "无匹配的标签"));
	}
	/**
	 * 标签联想词
	 * @param $word 输入的标签
	 * @return json 数据集合
	 */
	public function suggest()
	{
		$word = I('get.word','','trim');
		$limit = I('get.limit',5,'intval');
		$engine = D('Search', 'Logic', 'Common');
        $result = $engine->suggest($word, $limit);
		if ($result)
		{
			ajax_succ($result);		
		}
	
		$this->ajax_return(array("msg" => "无匹配的标签"));
	}
	
	/**
	 * 表单数据校验
	 * @param $data 表单数据集 $type add新增 edit编辑
	 * @echo json 数据集合
	 */
	protected function valiDate(&$data=array(), $type ='add')
	{
		if(empty($data))
		{
			ajax_error(array("msg" => "表单不能为空"));
		}
		
		//新增和编辑必填项验证
		$array = array("entry" => '词条名称', "pinyin" => '词条拼音', "content" => '内容', "pic" => '配图',
				 "creator" => '作者');
		foreach($array as $k => $v)
		{
			if($data[$k] == '')
			{
				ajax_error(array("name" => $k, "msg" => $array[$k]."不能为空"));
			}
		}
		if(count(json_decode($data['tags'])) == 0)
		{
			ajax_error(array("name" => "tags", "msg" => "标签不能为空"));
		}
		$data['content'] = clean_xss($data['content']);
 		if($data['entry'] != '')
		{
			if(!preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u",$data['entry']))
			{
				ajax_error(array("name" => 'entry', "msg" => "标题内容包含非法字符"));
			}
			if(strlen($data['entry']) > 30)
			{

				ajax_error(array("name" => 'entry', "msg" => "标题内容长度最长30个字符"));
			}
		}
		if($data['pinyin'] != '' && !preg_match("/^[A-Za-z]+$/",$data['pinyin']))
		{
			ajax_error(array("name" => 'entry', "msg" => "标题内容包含非法字符"));
		}

		//编辑时id 来源等验证
		if($type != 'add')
		{
			if($data['id'] == '' && $data['id'] == 'checkid')
			{
				ajax_error(array("name" => "id", "msg"=> "标题id不能为空"));
			}
			if($data['from'] == '')
			{
				ajax_error(array("name" => "from", "msg"=> "来源不能为空"));
				if($k == 'from' && !in_array($data['from'], array("0", "1")))
				{
					ajax_error(array("name" => $k, "msg" => "来源值错误"));
				}
			}
			if($data['fromurl'] == '' && $data['from'] == '1')
			{
				ajax_error(array("fromurl" => "id", "msg"=> "来源地址不能为空"));
			}
		}
		
		//处理内容
		if($data['content'])
		{
			$data['text'] = clear_all($data['content']);
		}
		
		$newdata = array();
		
		//焦点图验证
		$newdata[0] = array();
		if($data['index_focus'] == 'on')
		{
			if($data['focus_title'] == '')
			{
				$data['focus_title'] = $data['entry'];
			}
			if($data['focus_pic'] == '')
			{
				$data['focus_pic'] = $data['pic'];
			}
			$newdata[0] = array("type" => 0, "title" => $data['focus_title'], "pic" => $data['focus_pic']);
		}
		
		//名人验证
		$newdata[1] = array();
		if($data['index_celebrity'] == 'on')
		{
			if($data['celebrity_title'] == '')
			{
				$data['celebrity_title'] = $data['entry'];
			}
			if($data['celebrity_pic'] == '')
			{
				$data['celebrity_pic'] = $data['pic'];
			}
			$newdata[1] = array("type" => 1, "title" => $data['celebrity_title'], "pic" => $data['celebrity_pic']);
		}
		
		
		//名企验证
		$newdata[2] = array();
		if($data['index_company'] == 'on')
		{
			if($data['company_title'] == '')
			{
				$data['company_title'] = $data['entry'];
			}
			if($data['company_pic'] == '')
			{
				$data['company_pic'] = $data['pic'];
			}
			$newdata[2] = array("type" => 2, "title" => $data['company_title'], "pic" => $data['company_pic']);
		}
		
		//拼推荐数据
		if(!empty($newdata))
		{
			$data['recommend'] = $newdata;
		}
	}
	/*
	 * 备份添加数据
	 */
	private function save_bak($data)
	{
		$new_data = $this->buildData($data);
		$new_data['ctime'] = NOW_TIME;
		//var_dump($new_data);exit;
		
		$wiki = D('Wiki', 'Model', 'Common');

		$exist = $wiki->where("title='{$new_data['title']}' AND status != '-1'")->find();
		if($exist)
		{
			ajax_error(array("name" => "entry", "msg"=> "已存在此词条，不能重复添加"));
		}
		if ($wiki->add($new_data))
		{
			$lastid = $wiki -> getLastInsID();

			if ($new_data['status'] == 9)
 			{
 				//清除缓存
 				$this->removeRedis();
 				
 				//推送数据并入影子表
 				$new_data['id'] = $lastid;
 				$push_data = $new_data;
 				$push_data['url'] = url('show', array($new_data['title']), 'touch', 'wiki');
 				$push_data['scope'] = "全国";
 				$push_data['rel_news'] = json_decode($push_data['rel_news'], true);
 				$push_data['rel_house'] = json_decode($push_data['rel_news'], true);
 				$push_data['content'] = clear_all($push_data['content']);
 				$lSearch = D('Search', 'Logic', 'Common');
				if ($lSearch->createWiki(array($push_data)))
				{
					// 通过服务接口向字典中追回词条
					$ret['push_word'] = $lSearch->appendDictWords(array($push_data['title']), 'dict_wiki');
					
					$wikiHistory = D('WikiHistory', 'Model', 'Common');
	 				if ($wikiHistory->create($new_data))
	 				{
	 					$ret['history'] = $wikiHistory->add();
	 				}
	 				else
	 				{
	 					$ret['history'] = $wikiHistory->getError();
	 				}
				}
				else
				{
					$status['status'] = 1;
					$wiki->where(array('id'=>$lastid))->save($status);
				}
 			}
 				//ajax_succ(array("id" => $lastid, "msg" => "创建成功"));
		}
		else 
		{
			//ajax_error(array("id" => "", "msg" => "创建失败"));
		}
		
	}
	/*
	 * 备份修改数据
	 */
	private function edit_bak($data)
	{
		$new_data = $this->buildData($data);
		
		$wiki = D('Wiki', 'Model', 'Common');
		$exist = $wiki->where("title='{$new_data['title']}' AND status != '-1'")->find();
		if(!$exist)
		{
			ajax_error(array("name" => "entry", "msg"=> "无此词条"));
		}
		$title = $new_data['title'];
		unset($new_data['title']);
		if ($wiki->where("title='{$title}'")->save($new_data))
		{
			$new_data['id'] = $exist['id'];
			$new_data['title'] = $title;
			$new_data['ctime'] = $exist['ctime'];
			$push_data = $new_data;
			$push_data['url'] = url('show', array($new_data['title']), 'touch', 'wiki');
			$push_data['scope'] = "全国";
			$push_data['rel_news'] = json_decode($push_data['rel_news'], true);
			$push_data['rel_house'] = json_decode($push_data['rel_news'], true);
			$push_data['content'] = clear_all($push_data['content']);
			$lSearch = D('Search', 'Logic', 'Common');
			if ($new_data['status'] == 9)
			{
				//清除缓存
				$this->removeSomeredis();
				$this->removeDetailredis($exist['id']);
				
				//推送数据并入影子表
				if ($lSearch->createWiki(array($push_data)))
				{
					// 通过服务接口向字典中追回词条
					//$ret['push_word'] = $lSearch->appendDictWords(array($push_data['title']), 'dict_wiki');
					
					$wikiHistory = D('WikiHistory', 'Model', 'Common');
					if ($wikiHistory->create($new_data))
					{
						$ret['history'] = $wikiHistory->add();
					}
					else
					{
						$ret['history'] = $wikiHistory->getError();
					}
				}
				else
				{
					$status['status'] = 1;
					$wiki->where(array('id'=>$exist['id']))->save($status);
				}
			}
			if($new_data['status'] == 1 && $exist['status'] == 9)
			{
				//清除缓存
				$this->removeAllredis($exist['id']);
				
				$ret['remove'] = $lSearch->createWiki(array($push_data));
				// 通过服务接口向字典中删除词条
				$ret['remove_word'] = $lSearch->removeDictWords(array($title), 'dict_wiki');
			}
			//ajax_succ(array("id" => $lastid, "msg" => "创建成功"));
		}
		else
		{
			//ajax_error(array("id" => "", "msg" => "创建失败"));
		}
	
	}
	/*
	 * 对表单数据进行重新构建
	 */
	private function buildData($data)
	{
		$new_data = array();
		if (strtotime($data['releasetime']) <= NOW_TIME)
		{
/* 			if($data['releasetime'])
			{
				$new_data['ptime'] = strtotime($data['releasetime']);
			}
			else 
			{
				$new_data['ptime'] = 0;
			} */
			
			$new_data['ptime'] = $new_data['version'] = $new_data['utime'] = NOW_TIME;
			$new_data['status'] = 9;
		}
		else
		{
			$new_data['version'] = strtotime($data['releasetime']);
			$new_data['ptime'] = strtotime($data['releasetime']);
			$new_data['utime'] = NOW_TIME;
			$new_data['status'] = 1;
		}
		$new_data['focus_time'] = $data['index_focus'] ? NOW_TIME : 0;
		$new_data['celebrity_time'] = $data['index_celebrity'] ? NOW_TIME : 0;
		$new_data['company_time'] = $data['index_company'] ? NOW_TIME : 0;
		
		$new_data['title'] = $data['entry'];
		$new_data['cateid'] = ($data['category'] == '') ? intval($data['category']) : intval($data['category']) + 1;
		$new_data['cover'] = $data['pic'];
		//$data['coverinfo'] = '';
		$new_data['editorid'] = intval($data['creatorid']);
		$new_data['editor'] = $data['creator'];
		$new_data['src_type'] = $data['from'];
		$new_data['src_url'] = $data['fromurl'];
		$new_data['tags'] = implode(' ', json_decode($data['tags']));
		$new_data['rel_news'] = $data['news'];
		$new_data['rel_house'] = $data['house'];
		$new_data['pinyin'] = $data['pinyin'];
		$new_data['content'] = $data['content'];
		$new_data['focus_title'] = $data['focus_title'];
		$new_data['focus_pic'] = $data['focus_pic'];
		$new_data['celebrity_title'] = $data['celebrity_title'];
		$new_data['celebrity_pic'] = $data['celebrity_pic'];
		$new_data['company_title'] = $data['company_title'];
		$new_data['company_pic'] = $data['company_pic'];
		
		$new_data['firstletter'] = substr($data['pinyin'], 0, 1);

		$new_data['firstletter'] = preg_match("/[a-z]/i", $new_data['firstletter']) ? strtoupper($new_data['firstletter']) : "#";
		return $new_data;
	}
	/*
	 * 备份词条管理列表
	 */
	public function index2()
	{
		//权限验证
		$this->checkAuthorization('wiki/list');
		
		//分页处理
		$page = I('get.page', 1, 'intval');
		$pagesize = I('get.pagesize', 10, 'intval');
		if($page < 1) $page = 1;
		if($pagesize < 1) $pagesize = 1;
		
		$newform['page'] = $page;
		$newform['pagesize'] = $pagesize;
		$newform['entry'] = I('get.entry','','strip_tags,trim');
		$newform['from'] = I('get.from','','strip_tags,trim');
		$newform['category'] = I('get.category','','strip_tags,trim');
		$newform['creator'] = I('get.creator','','strip_tags,trim');
		$newform['releasetime'] = I('get.releasetime','','strip_tags,trim');
		$newform['check'] = I('get.check','','strip_tags,trim');
		$newform['sort'] = I('get.sort','','strip_tags,trim');

		$sql = "status != -1";
		if($newform['entry'])
		{
			$sql .= " AND title like '%{$newform['entry']}%'";
		}
		if($newform['from'] != '')
		{
			$sql .= " AND src_type = " . $newform['from'];
		}
		if($newform['category'])
		{
			$sql .= " AND cateid = " . $newform['category'];
		}
		if($newform['creator'])
		{
			$sql .= " AND editor = '{$newform['creator']}'";
		}
		if($newform['releasetime'])
		{
			$newform['releasetime'] = strtotime($newform['releasetime']);
			$sql .= " AND ptime >= '" . $newform['releasetime'] . "' AND ptime <= '" . ($newform['releasetime'] + 86400) . "'";
		}
		if($newform['check'])
		{
			if($newform['check'] == '9')
			{
				$sql .= " AND status = 9";
			}
			else
			{
				$sql .= " AND status != 9";
			}
		}
		if($newform['sort'] == '')
		{
			$order = "ptime desc";
		}
		elseif($newform['sort'] == "0")
		{
			$order = "hits asc";
		}
		else
		{
			$order = "hits desc";
		}

		$offset = ($page-1) * $pagesize;
		$wiki = D('Wiki', 'Model', 'Common');
		$total = $wiki->where($sql)->count();

		if($total)
		{
			$result = $wiki->where($sql)->order($order)->limit($offset,$pagesize)->select();
		}
		$this->linkopts($newform,$total);
		$this->assign('list', $result);
		$this->assign('form',$newform);
		//当前时间用于和定时时间比较
		$this->assign('nowtime',time());
		
		$this->display('index2');
	}
	public function linkopts($form,$total)
	{
		//分页模块
		if($total)
		{
			//封装linkopts
			$linkopts = array();
			$form['entry'] && array_push($linkopts, "entry={$form['entry']}");
			$form['from'] != '' && array_push($linkopts, "from={$form['from']}" );
			$form['category'] != '' && array_push($linkopts, "category={$form['category']}");
			$form['creator'] && array_push($linkopts, "creator={$form['creator']}");
			$form['releasetime'] && array_push($linkopts, "releasetime={$form['releasetime']}");
			$form['check'] != '' && array_push($linkopts, "check={$form['check']}");
			$form['sort'] && array_push($linkopts, "sort={$form['sort']}");
			$linkstring = !empty($linkopts) ? '/Wiki/index_bak?page=#&'.implode('&',$linkopts) : '/Wiki/?page=#';
		
			$opts = array(
					'first' => true, //首页
					'last' => true,	//尾页
					'prev' => true, //上一页
					'next' => true, //下一页
					'number' => 5, //显示页码数
					'linkstring' => $linkstring
			);
			$pager = pager($form['page'], $total, $form['pagesize'], $opts);
			$this->assign('pager', $pager);
		}
	}
	/**
	 * 添加百科词条内容
	 */
	public function add2(){
		//权限验证
		$this->checkAuthorization('wiki/add');
		// wiki/add
		$pageinfo = array(
				'crumb' => array(),
				'title' => '添加词条内容',
		);
		$this->assign('pageinfo', $pageinfo);
	
		if ( IS_GET ) {
			$this->editor_bak();
		}
		if ( IS_POST ) {
			//获取表单内容
			$form = I('post.', '', 'trim');
			//var_dump($form);exit;
			//对表单进行验证
			$this->valiDate($form);
			//第二版入库操作
			$this->save_bak($form);
		}
		// $this->display();
	}
	
	/**
	 * 编辑百科词条内容
	 */
	public function edit2()
	{
		//权限验证
		$this->checkAuthorization('wiki/edit');
		// wiki/edit
		$pageinfo = array(
				'crumb' => array(),
				'title' => '编辑词条内容',
		);
		$this->assign('pageinfo', $pageinfo);
	
		if ( IS_GET ) {
			$id = I('get.id', '', 'intval');
			$history_id = I('get.history_id', '', 'strip_tags,trim');
				
			//获取历史版本
			$wikiHistory = D('WikiHistory', 'Model', 'Common');
			$result = $wikiHistory->field("pkid, id, editor, utime")->where(" status = 9 AND id = '{$id}'")->order("utime desc")->select();

			$history_info = array();
			if($result)
			{
				$history_info = $result;
			}
	
			$this->assign('histroy_info', $history_info);
			$this->assign('histroy_num', count($history_info));
				
			//获取历史版本信息
			if($history_id != '')
			{
				$entry_result = $wikiHistory->where(" status != -1 AND pkid = {$history_id}")->find();
			}
			else
			{
				//获取单个词条的详细内容
				$wiki = D('Wiki', 'Model', 'Common');
				$entry_result = $wiki->where(" status != -1 AND id = {$id}")->find();
			}
	
			$data = array();
			if($entry_result)
			{
				$entry_result['tags'] = explode(" ", $entry_result['tags']);
				$entry_result['news'] = json_decode($entry_result['rel_news'], true);
				$entry_result['house'] = json_decode($entry_result['rel_house'], true);
				$data = $entry_result;
			}
			else
			{
				$this->error("此词条不存在");
			}
				
			$this->assign('id', $id);
			$this->assign('history_id', $history_id);
			$this->editor_bak($data);
		}
		if ( IS_POST ) {
			//获取表单内容
			$form = I('post.', '', 'trim');
				
			//对表单进行验证
			$this->valiDate($form, 'edit');
			//第二版入库操作
			$this->edit_bak($form);
		}
		// $this->display();
	}
	/**
	 * 词条内容编辑器
	 */
	protected function editor_bak ( $data=array() ) {
		define('CDN_IMG_URL','//cdn.leju.com/encyclopedia/');
		$this->assign('cdn_img_url', CDN_IMG_URL);
		$this->assign('data', $data);
		$this->display('editor2');
	}
	/**
	 * 删除词条适用于第二版
	 */
	public function delete2()
	{
		//权限验证
		$this->checkAuthorization('wiki/del', 'ajax');
		
		$id = I('get.id',0,'intval');
		if ($id)
		{
			$wiki = D('Wiki', 'Model', 'Common');
			$info = $wiki->find($id);
			if ($info)
			{
				$wiki->status = -1;
				$ret = $wiki->save();
				if ($ret)
				{
					//清除缓存
					$this->removeAllredis($id);
					
					$lSearch = D('Search', 'Logic', 'Common');
 					$ret['push'] = $lSearch->removeWiki(array($id));
 					// 通过服务接口向字典中删除词条
 					$ret['remove_word'] = $lSearch->removeDictWords(array($info['title']), 'dict_wiki');
				}
			}
			ajax_succ('删除成功');
		}
		ajax_error('删除失败');
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
  	/* public function getDictWords()
	{
		$lSearch = D('Search', 'Logic', 'Common');
 		$ret = $lSearch->getDictWords('dict_wiki');
 		var_dump($ret);exit;
	} */  

}