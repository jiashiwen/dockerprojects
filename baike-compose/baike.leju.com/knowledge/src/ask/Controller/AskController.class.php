<?php
/**
 * 问答提问页面
 *
 */
namespace ask\Controller;
use Think\Controller;
class AskController extends BaseController {

	public function __construct() {
		parent::__construct();
		if ( !$this->_islogined ) {
			// 需要用户登录，否则不允许进入
			// originUrl
			$url = 'https://my.leju.com/';
			$this->error('请登录系统后再进行提问', $url, 5);
		}
	}

	public function index(){
		$lPage = D('Page', 'Logic');
		$proid = I('get.pro', 0, 'intval');
		$professors = $lPage->load_professors();
		if ( array_key_exists($proid, $professors) ) {
			$professor = $professors[$proid];
		} else {
			$professor = false;
		}
		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->pc_ask_logic();
		$this->setStatsCode($lPage->_getStatsConfig('ask'));
		$binds['professor'] = $professor;
		$this->assign_all($binds);
		$this->display();
	}

	public function save() {
		// $ip = get_client_ip();
		// $location = getIPLocation($ip, array('city_cn'=>'', 'city_en'=>''));
		$location = getCookieLocation($this->_device);
		$form = I('request.');
		$fields = array_flip(array(
			'title', 'desc', 'data_notice', 'tags', 'anonymous', 'images', 'tags_id', 'pro',
			));
		$form = array_intersect_key($form, $fields);

		$proid = intval($form['pro']);
		$professor = false;
		$professors = D('Page', 'Logic')->load_professors();
		if ( array_key_exists($proid, $professors) ) {
			$professor = $professors[$proid];
		}

		$form['title'] = filterInput( clean_xss($form['title']) );
		$form['desc'] = filterInput( clean_xss($form['desc']) );

		if ( $form['title']=='' ) {
			$this->ajax_error('问题标题是必填项');
		}
		if ( abslength($form['title']) > 200 ) {
			$this->ajax_error('标题字数不可超过 200 字');
		}
		if ( abslength($form['desc']) > 5000 ) {
			$this->ajax_error('描述信息不可超过超过 5000 字');
		}
		if ( trim($form['tags'])=='' ) {
			$this->ajax_error('提问需要指定标签');
		}
		$tags = explode(',', trim(trim($form['tags']), ','));
		foreach ( $tags as $i => &$tag ) {
			$tag = trim($tag);
			if ( $tag=='' ) {
				unset($tags[$i]);
			}
		}
		if ( empty($tags) ) {
			$this->ajax_error('提问需要指定标签');
		}
		$tags = implode(' ', array_values($tags));
		// 新，使用标签id存储
		$tagids = explode(',', $form['tags_id']);
		unset($form['tags_id']);
		foreach ( $tagids as $i => $tagid ) {
			$tagid = intval(trim($tagid));
			if ( $tagid == 0 ) {
				unset($tagids[$i]);
			}
		}
		if ( empty($tagids) || count($tagids)>3 ) {
			$this->ajax_error('提问需要指定标签 且 最多不能超过3个标签!');
		}
		$form['tagids'] = ','.implode(',', $tagids).',';

		// 标签约束逻辑
		$lPage = D('Page', 'Logic');
		$_tags = $lPage->convert_tags($tags);
		if ( count($_tags)==0 || count($_tags)>3 ) {
			$this->ajax_error('问题的标签数量错误，请指定至少 1 个有效的标签，最多只能指定 3 个标签');
		}
		$tags = array();
		foreach ( $_tags as $i => $tag ) {
			array_push($tags, $tag['name']);
		}
		$form['tags'] = implode(' ', $tags);

		$notice = trim($form['data_notice']);
		if ( $notice=='' ) {
			$this->ajax_error('请输入提醒方式 手机号码或者是邮箱');
		} else {
			$notice_type = '';
			if ( preg_match('/\d{11}/i', $notice) ) {
				$notice_type = 'sms';
			}
			if ( preg_match('/([\w\-]+\@[\w\-]+\.[\w\-]+)/', $notice) ) {
				$notice_type = 'email';
			}
			if ( $notice_type=='' ) {
				$this->ajax_error('提醒方式输入有误，请输入接收短信的手机号码或是邮箱地址');
			}
		}

		if ( $this->_device=='pc' ) {
			$source = 51;
		} else {
			if ( $this->_isapp ) {
				$source = 101;
			} else {
				$source = 151;
			}
		}

		$data = array(
			'cateid' => 0,
			'catepath' => '0-',
			'ctime' => NOW_TIME,
			'userid' => $this->_userid, // 需要通过 $this->userinfo['id'] 获取
			'usernick' => $this->_userinfo['username'], // 需要通过 $this->userinfo['nick'] 获取
			'source' => $source,	// 重新定义来源字典，0时为抓取 51 web 101 为app 151 为wap
			'status' => 21,	// 默认为正常提交
			'i_attention' => 0,
			'i_hits' => 0,
			'i_replies' => 0,
			'last_best' => 0,
		) + $form;

		// 多张图片上传时，每张图片使用","进行分隔
		$images = isset($data['images']) ? explode(',', $data['images']) : array();
		unset($data['images']);

		# 附属信息
		$data['data'] = array();
		$data['data']['ip'] = $location['ip'];
		$data['data']['scope'] = $location;
		# data 后台，前台均有使用
		if ( $images ) {
			$data['data']['images'] = $images;	// 上传多张图也可保存
			$data['i_images'] = count($images); // 存储上传图片的数量
		} 
		# 联系方式
		$data['data']['notice'] = array(
			'contact' => $notice,
			'type' => $notice_type,
		);
		# 提问者所在城市
		$scope = ''; // 默认为空
		if ( $location['city_cn']!='' ) {
			$scope = $location['city_cn'];
		} else {
			if ( trim($ip)!='' ) {
				$scope = $ip;
			}
		}
		$data['scope'] = $scope;

		# 敏感词过滤
		// 如果为待审核信息，存储敏感词审核信息 后台使用
		$lSensitive = D('Sensitive', 'Logic', 'Common');
		$content = $data['title'] . PHP_EOL . $data['desc'];
		$ret = $lSensitive->detect($content, 0);
		if ( $ret && $ret['status'] ) {
			$data['data']['sensitive'] = $ret;
			if ( in_array($ret['type'], array('政治','色情','营销')) ) {
				$data['status'] = 12; // 需要审核
			}
		}

		// 添加至数据库
		$mQuestion = D('Question', 'Model', 'Common');
		$data['data'] = json_encode($data['data']);
		$data['_id'] = md5(rand(0,100).'|'.json_encode($data).'|'.rand(0,100));
		$id = $mQuestion->data($data)->add();
		if ( $id ) {
			$data['id'] = $id;
			$data['data'] = json_decode($data['data'], true);

			if ( $data['status'] > 20 ) {
				if ( $professor ) {
					// 如果是请专家回答的问题，向专家发起数据日志
					$_log_data = array(
						'uid'=>$professor['id'],
						'uuid'=>$this->_browserid,
						'act'=>41,
						'relid'=>$id,
						'ctime'=>NOW_TIME
					);
					D('Oplogs', 'Model', 'Common')
						->data($_log_data)
						->add();
					D('Members', 'Model', 'Common')
						->where(array('uid'=>$professor['id']))
						->setInc('i_needanswer', 1);
				}

				// 添加到新闻池待推送集合
				D('Question', 'Logic', 'Common')->appendToPushSet($id);
				// 数据发布
				$data['url'] = url('show', array($data['id']), $this->_device, 'ask');
				$data['rcmd_time'] = 0;
				$lSearch = D('Search', 'Logic', 'Common');
				$doc = $lSearch->questionDocConvert($data);
				$ret = $lSearch->create($doc, 'question', '');
				// 用户提问成功后，自动主动推送到百度
				$lSearch->pushToBaidu(array($data['url']), 'ask');
				// 发布成功
				$this->ajax_return(array('status'=>true, 'reason'=>'提问成功', 'jump'=>'/ask/success?id='.$id));
			}
			if ( $data['status']==12 ) {
				$this->ajax_return(array('status'=>true, 'reason'=>'您提的问题中有敏感信息，问题已经提交，请等待客服人员审核', 'jump'=>'/ask/success?id='.$id));
			}
		}
		$this->ajax_error('提问失败');
	}

	// @TODO: 改成使用 tagids 取相关数据的操作
	public function success() {
		$id = I('get.id', 0, 'intval');
		$lPage = D('Page', 'Logic');

		$binds = $lPage
				->setFlush($this->_flush['data'])
				->setDevice($this->_device)
				->pc_ask_logic();

		if ( $id > 0 ) {
			$condition = array();
			$where = array('id'=>$id);

			$mQuestion = D('Question', 'Model', 'Common');
			$info = $mQuestion->where($where)->find();
			if ( $info ) {
				$tags = explode(' ', $info['tags']);
			}

			if ( !empty($tags) ) {
				foreach ( $tags as $i => $tag ) {
					array_push($condition, array('like', $tag.' %'));
					array_push($condition, array('like', '% '.$tag));
					array_push($condition, array('like', '% '.$tag.' %'));
				}
				array_push($condition, 'OR');

				$device = $this->_device=='pc' ? 'pc' : 'touch';
				$where = array(
					'status'=>array('in', array(21,22,23)),
					'tags'=>$condition,
				);
				$fields = 'id, title';
				$list = $mQuestion->field($fields)->where($where)->page(1, count($tags)*2)->order('i_hits desc')->select();
				// $filters = array_flip(array('id', 'title'));
				foreach ( $list as $i => $item ) {
					// $list[$i] = array_intersect_key($item, $filters);
					$list[$i]['url'] = url('show', array($item['id']), $device, 'ask');
				}
				$binds['rel_questions'] = $list;

				$mKnowledge = D('Knowledge', 'Model', 'Common');
				$where = array(
					'status'=>9,
					'tags'=>$condition
				);
				$list = $mKnowledge->field($fields)->where($where)->page(1, count($tags)*2)->order('utime desc')->select();
				foreach ( $list as $i => $item ) {
					// $list[$i] = array_intersect_key($item, $filters);
					$list[$i]['url'] = url('show', array($item['id']), $device, 'baike');
				}
				$binds['rel_knowledge'] = $list;
			} else {
				// 没有标签忽略
			}
		}

		$this->assign_all($binds);
		$this->display();

		// $this->display('success');
	}

	// 从企业入口进行提问的页面
	public function company() {
		if ( !$this->_islogined ) {
			// 需要用户登录，否则不允许进入
			$url = 'https://my.leju.com/';
			$this->error('请登录系统后再进行提问', $url, 5);
		}
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$this->show_error('请指定要提问的公司');
		}

		$binds = [];
		$binds['company_id'] = $id;
		$binds['succurl'] = url('LDCompany', [$id], 'touch', 'ask');

		$lPage = D('CompanyPage', 'Logic');
		$lPage->setFlush($this->_flush['data'])
			 ->setDevice($this->_device)
			 ->setUserid($this->_userid);
		$binds['islogined'] = $this->_islogined;
		$this->setStatsCode($lPage->_getStatsConfig('ask', $binds));

		$this->assign_all($binds);
		$this->display();
	}

	// 从企业入口进行提问的页面
	public function person() {
		if ( !$this->_islogined ) {
			// 需要用户登录，否则不允许进入
			$url = 'https://my.leju.com/';
			$this->error('请登录系统后再进行提问', $url, 5);
		}
		layout(false);
		if ( $this->_device=='pc' ) {
			$this->error('暂时仅支持移动端访问');
		}
		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$this->show_error('请指定要提问的人物');
		}

		$binds = [];
		$binds['person_id'] = $id;
		$binds['succurl'] = url('PNCompany', [$id], 'touch', 'ask');

		$lPage = D('PersonPage', 'Logic');
		$lPage->setFlush($this->_flush['data'])
			 ->setDevice($this->_device)
			 ->setUserid($this->_userid);
		$binds['islogined'] = $this->_islogined;
		$this->setStatsCode($lPage->_getStatsConfig('ask', $binds));

		$this->assign_all($binds);
		$this->display();
	}
}
 