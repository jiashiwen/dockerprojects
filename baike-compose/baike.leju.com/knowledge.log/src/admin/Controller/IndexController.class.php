<?php
namespace admin\Controller;
use Think\Controller;
class IndexController extends BaseController {

	protected $redis = null;

	public function test() {
		phpinfo();
	}
	public function index(){
		$this->getWikiStats();
		$this->getKnowledgeStats();
		$this->display('index');
	}

	public function __construct()
	{
		parent::__construct();
		$this->redis = S(C('REDIS'));
	}

	/**
	 * 百科词条数据统计排行榜
	 * @author 羊阳
	 */
	protected function getWikiStats()
	{
		//统计图
		$stats = $this->getStatistics();
		$this->assign('touch', $this->touch_domain());
		$this->assign('words', $stats['words']);
	}

	/**
	 * 知识数据统计排行榜
	 * @author 李红旺
	 */
	protected function getKnowledgeStats()
	{
		$todyTime = getDayTime();
		$begin = $todyTime['begin'];
		$rkey = 'kb:chart:'.$begin;
		$list = $this->redis->get($rkey);
		if ($list['rank'])
		{
			foreach ($list['rank'] as $key => &$value) {
				$value['title'] = mystrcut($value['title'],12);
			}
			$rank = array_chunk($list['rank'], 4);
			$this->assign('touch', $this->touch_domain());
			$this->assign('rank',$rank);
		}
	}


	/**
	 * Dash Board 图表一 ( 全国直营站数据图表 )
	 * cateid / start / end
	 */
	public function globalTrends() {

		$cateid = I('get.cateid', 0, 'intval');
		$start = I('get.start', 0, 'intval');
		$end = I('get.end', 0, 'intval');

		$list = array(
			'pub' => array(),
			'city' => array(),
		);

		$cities = C('CITIES');
		$direct = &$cities['DIRECT'];
		$i = 0;
		foreach ( $direct as $city_en => $city ) {
			$num1 = intval(rand(10, 150));
			$list['pub'][$i] = $num1;
			$list['city'][$i] = $city['cn'];
			$i++;
		}

		$result = array(
			'status'=>false,
			'msg'=>'您没有权限访问全国数据报表权限',
			'params'=>array(),
		);
		if ($list) {
			$result = array(
				'status'=>true,
				'msg'=>'succ',
				'params'=>array('cateid'=>$cateid, 'start'=>$start, 'end'=>$end),
				'list'=>$list,
			);
		}
		$this->ajax_return($result);
	}


	/**
	 * 后台首页 获取 数据图表的接口
	 * @description 针对 知识部份的统计结果
	 * @author 李红旺
	 */
	public function trend()
	{
		$todyTime = getDayTime();
		$begin = $todyTime['begin'];
		$key = 'kb:chart:'.$begin;
		$list = $this->redis->get($key);

		$result = array(
			'status'=>false,
			'msg'=>'fail',
			'params'=>array(),
		);
		if ($list) {
			$result = array(
			'status'=>true,
			'msg'=>'succ',
			'params'=>$list,
			);
		}

		$this->ajax_return($result);

	}

	public function ActionMap() {
		$list = array(
			array(
				'name' => '管理后台首页',
				'visible' => '首页',
				'checkid' => '_:system/public/index',
				'description' => '管理后台首页',
				'icon' => 'fa fa-line-chart',
				'_condition' => '',
				'_data' => '',
			),
		/* - 角色管理组 - */
			array(
				'name' => '系统权限角色管理',
				'visible' => '角色管理',
				'checkid' => '_:system/roles/list',
				'description' => '系统权限角色管理列表',
				'icon' => 'fa fa-cubes',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '创建系统角色',
				'visible' => '创建角色',
				'checkid' => '_:system/roles/add',
				'description' => '系统权限角色管理列表',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '编辑角色权限信息',
				'visible' => '编辑权限',
				'checkid' => '_:system/roles/edit',
				'description' => '编辑角色权限信息',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
		/* - 管理用户管理组 - */
			array(
				'name' => '用户管理列表',
				'visible' => '用户管理',
				'checkid' => 'GET:system/admin/list',
				'description' => '用户管理列表',
				'icon' => 'fa fa-users',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '删除管理用户',
				'visible' => '删除',
				'checkid' => 'POST:system/admin/disable',
				'description' => '删除系统管理用户',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '恢复管理用户',
				'visible' => '恢复',
				'checkid' => 'POST:system/admin/enable',
				'description' => '恢复删除的系统管理用户',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
		/* - 知识栏目管理 - */
			array(
				'name' => '知识栏目管理',
				'visible' => '栏目管理',
				'checkid' => 'GET:knowledge/categories/list',
				'description' => '知识栏目管理',
				'icon' => 'fa fa-line-chart',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '知识栏目保存',
				'visible' => false,
				'checkid' => 'POST:knowledge/categories/save',
				'description' => '保存知识栏目设置',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
		/* - 知识数据管理组 - */
			array(
				'name' => '知识管理列表',
				'visible' => '知识管理',
				'checkid' => '_:knowledge/list',
				'description' => '知识管理列表及搜索页面',
				'icon' => 'fa fa-newspaper-o',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '添加知识内容',
				'visible' => '添加知识',
				'checkid' => '_:knowledge/add',
				'description' => '添加知识内容',
				'icon' => 'fa fa-pencil-square-o',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '编辑知识内容',
				'visible' => '编辑知识',
				'checkid' => '_:knowledge/edit',
				'description' => '编辑知识内容',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '知识内容审核',
				'visible' => '审核',
				'checkid' => '_:knowledge/confirm',
				'description' => '知识内容审核',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '知识内容置顶',
				'visible' => '审核',
				'checkid' => '_:knowledge/settop',
				'description' => '知识内容审核',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '知识内容为焦点',
				'visible' => false,
				'checkid' => '_:knowledge/setfocus',
				'description' => '知识内容审核',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '知识内容编辑',
				'visible' => '编辑知识',
				'checkid' => '_:knowledge/editor',
				'description' => '知识信息编辑管理',
				'icon' => 'fa fa-pencil-square-o',
				'_condition' => '',
				'_data' => '',
			),
		/* - 问答数据管理组 - */
			array(
				'name' => '问答管理列表',
				'visible' => '问答管理',
				'checkid' => '_:questions/list',
				'description' => '问答管理列表及搜索页面',
				'icon' => 'fa fa-twitch',
				'_condition' => '',
				'_data' => '',
			),
		/* - 百科词条管理组 - */
			array(
				'name' => '百科词条管理列表',
				'visible' => '词条管理',
				'checkid' => '_:wiki/list',
				'description' => '百科词条管理列表及搜索页面',
				'icon' => 'fa fa-wikipedia-w',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '添加词条内容',
				'visible' => '添加词条',
				'checkid' => '_:wiki/add',
				'description' => '添加词条内容',
				'icon' => 'fa fa-language',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '编辑词条内容',
				'visible' => '编辑词条',
				'checkid' => '_:wiki/edit',
				'description' => '编辑词条内容',
				'icon' => 'fa fa-language',
				'_condition' => '',
				'_data' => '',
			),
			array(
				'name' => '词条内容审核',
				'visible' => '审核',
				'checkid' => '_:wiki/confirm',
				'description' => '词条内容审核',
				'icon' => '',
				'_condition' => '',
				'_data' => '',
			),
		);

		$map = array(
			'system' => array(
				'public' => array(
					'index' => array(
						'name' => '管理后台首页',
					),
					'login' => array(
						'name' => '管理后台登录',
					),
				),
			),
			'knowledge' => array(),
		);
	}

	public function uptrend()
	{
		$num = 7;
		$count = array(1,2);
		$count = $this->redis->lRange('L:KB:UP', 0, 6);
		$c = count($count);
		if ($c < $num)
		{
			$diff = $num - $c;
			$res = array_fill($c,$diff,0);
			$count = array_merge($count,$res);
		}
		ajax_succ($count);
	}

	public function hottrend()
	{
		$num = 7;
		$count = array(1,2);
		$count = $this->redis->lRange('L:KB:HOT', 0, 6);
		$c = count($count);
		if ($c < $num)
		{
			$diff = $num - $c;
			$res = array_fill($c,$diff,0);
			$count = array_merge($count,$res);
		}
		ajax_succ($count);
	}

	public function catetrend()
	{
		$num = 7;
		$count = array(1,2);
		$count = $this->redis->lRange('L:KB:CATE', 0, 6);
		$c = count($count);
		if ($c < $num)
		{
			$diff = $num - $c;
			$res = array_fill($c,$diff,0);
			$count = array_merge($count,$res);
		}
		ajax_succ($count);
	}

	public function rank()
	{
		$ids = $this->redis->zRevRange('L:KB:CATE', 0, 6,true);
		$list = array();
		if ($ids)
		{
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			foreach ($ids as $key => $id)
			{
				$list[] = $mKnowledge->find($id);
			}
		}

		ajax_succ($result);
	}

	//首页统计ajax
	public function statistics()
	{
		$result = $this->getStatistics();
		if($result)
		{
			ajax_succ($result);
		}
		else
		{
			ajax_error('暂无数据');
		}
	}

	//获取统计数据
	protected function getStatistics($plant = null)
	{
		$statistics = array();
		if($plant == 'b')
		{
			//Plant-B方案
			$r = S(C('REDIS'));
			$statistics = $r->get('wiki:admin:7days');
		}
		else
		{
			$tongji_api = curl_get(C('DATA_TRANSFER_API_URL').'api/item/stat');
			$statistics = json_decode($tongji_api['result'], true);
			if($statistics)
			{
				foreach($statistics as $k => $v)
				{
					foreach($v as $n => $b)
					{
						if(isset($b['day']))
						{
							$statistics[$k][$n]['day'] = date('m-d', $b['day']);
						}
					}
				}
			}
		}

		return $statistics;
	}

	//知识&百科编辑器用的静态模板/防跨域机制
	public function link()
	{
		layout(false);
		$this->display();
	}

	//知识&百科编辑器用的静态模板/防跨域机制
	public function searchreplace()
	{
		layout(false);
		$this->display();
	}

	//拼接前台域名
	private function touch_domain()
	{
		$host = explode('.', $_SERVER['HTTP_HOST']);
		if($host[0] == 'dev' || $host[0] == 'ld')
		{
			return str_replace('http://',"http://{$host[0]}.",C('DOMAINS')['TOUCH']);
		}
		return C('DOMAINS')['TOUCH'];
	}
}
