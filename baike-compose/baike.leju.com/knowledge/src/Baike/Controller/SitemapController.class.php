<?php
/**
 * 站点地图 For SEO
 * Baidu sitemap xml 返回
 */
namespace Baike\Controller;

class SitemapController extends BaseController {
	// 业务类型映射 url 中的业务名称 -> 数据索引业务名称
	protected $_businesses;
	protected $_group_size;
	protected $_host;

	public function __construct() {
		parent::__construct();
		$this->_businesses = array('baike','wiki');
		$this->_group_size = 50000;	// 每个分组文件包含的数据量
		$this->_host = 'http://'.$_SERVER['HTTP_HOST'];
	}

	/*
	 * sitemap汇总
	 */
	public function index() {
		//匹配业务类型
		$business = I('get.business', 'baike', 'trim,strtolower');
		if(!in_array($business, $this->_businesses))
		{
			die('404 not found!');
		}

		if($business == 'baike')
		{
			$mK = D('knowledge','Model','Common');
			$total = $mK->where(array('status'=>9))->count('id');
			$page = ceil($total/$this->_group_size);
		}
		else
		{
			//词条 todo
			$wiki = curl_get(C('DATA_TRANSFER_API_URL').'api/item/sitemap');
			if($wiki['status'])
			{
				$wiki = json_decode($wiki['result'], true);
				$total = $wiki['total'];
				$page = ceil($total/$this->_group_size);
			}
		}

		$time = date('Y-m-d',time());

		set_xml_output(true);
		// header('Content-Type: text/xml;');
		echo '<?xml version="1.0" encoding="utf-8"?>', PHP_EOL;
		echo '<sitemapindex>', PHP_EOL;
		for ( $i=$page; $i>=1; $i-- ) {
			echo '<sitemap>', PHP_EOL;
			echo "<loc>{$this->_host}/sitemap-{$business}-{$i}.xml</loc>", PHP_EOL;
			echo "<lastmod>{$time}</lastmod>", PHP_EOL;
			echo '</sitemap>', PHP_EOL;
		}
		echo '</sitemapindex>', PHP_EOL;
		exit;
	}

	/*
	 * sitemap详情
	 */
	public function info() {
		//匹配业务类型
		$business = I('get.business', 'baike', 'trim,strtolower');
		$page = I('get.group', 1, 'intval');

		if(!in_array($business, $this->_businesses))
		{
			die('404 not found!');
		}

		if($business == 'baike')
		{
			$mK = D('knowledge','Model','Common');
			$data = $mK->field('id')->where(array('status'=>9))->page($page,$this->_group_size)->order('ptime DESC')->select();
		}
		else
		{
			//词条 todo
			$wiki = curl_get(C('DATA_TRANSFER_API_URL').'api/item/sitemap?page='.$page."&pagesize=".$this->_group_size);
			if($wiki['status'])
			{
				$wiki = json_decode($wiki['result'], true);
				if($wiki['result'])
				{
					$data = $wiki['result'];
				}
			}
		}
		$time = date('Y-m-d',time());

		set_xml_output(true);
		// header('Content-Type: text/xml;');
		echo '<?xml version="1.0" encoding="utf-8"?>', PHP_EOL;
		echo '<urlset>', PHP_EOL;
		foreach($data as $v)
		{
			//词条需要转一下base64
			if($business == 'wiki')
			{
				$v['id'] = base64_encode($v['id']);
			}

			echo '<url>', PHP_EOL;
			echo "<loc>{$this->_host}/show-{$v['id']}.html</loc>", PHP_EOL;
			//移动端多一个标示
			if($this->_device == 'mobile')
			{
				echo '<mobile:mobile type="mobile"/>, PHP_EOL';
			}
			echo "<lastmod>{$time}</lastmod>", PHP_EOL;
			echo '<changefreq>daily</changefreq>', PHP_EOL;
			echo '<priority>0.8</priority>', PHP_EOL;
			echo '</url>', PHP_EOL;
		}
		echo '</urlset>', PHP_EOL;
		exit;
	}
}