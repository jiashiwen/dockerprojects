<?php
/**
 * 问答系统站点地图 For SEO
 * Baidu sitemap xml 返回
 */
namespace ask\Controller;

class SitemapController extends BaseController {
	// 业务类型映射 url 中的业务名称 -> 数据索引业务名称
	protected $_group_size;
	protected $_host;

	public function __construct() {
		parent::__construct();
		$this->_group_size = 50000;	// 每个分组文件包含的数据量
		$this->_host = 'http://'.$_SERVER['HTTP_HOST'];
	}

	/*
	 * sitemap汇总
	 */
	public function index() {
		$mQuestion = D('Question','Model','Common');
		$where = array(
			'status'=>array('in', array(21,22,23)),
		);
		$total = $mQuestion->where($where)->count('id');
		$page = ceil($total/$this->_group_size);

		$time = date('Y-m-d',time());

		set_xml_output(true);
		// if ( $total <= $this->_group_size ) {
		// 	$this->info();
		// } else {
			echo '<?xml version="1.0" encoding="utf-8"?>', PHP_EOL,
				 '<sitemapindex>', PHP_EOL;
			for ( $i=$page; $i>=1; $i-- ) {
				echo '<sitemap>', PHP_EOL,
					 "<loc>{$this->_host}/sitemap-{$i}.xml</loc>", PHP_EOL,
					 "<lastmod>{$time}</lastmod>", PHP_EOL,
					 '</sitemap>', PHP_EOL;
			}
			echo '</sitemapindex>', PHP_EOL;
		// }
	}

	/*
	 * sitemap详情
	 */
	public function info() {
		$page = I('get.group', 1, 'intval');
		$mQuestion = D('Question','Model','Common');
		$where = array(
			'status'=>array('in', array(21,22,23)),
		);
		$data = $mQuestion->field('id')->where($where)->page($page, $this->_group_size)->order('id DESC')->select();
		$time = date('Y-m-d',time());

		set_xml_output(true);
		echo '<?xml version="1.0" encoding="utf-8"?>', PHP_EOL,
			 '<urlset>', PHP_EOL;
		foreach ( $data as $v ) {
			echo '<url>', PHP_EOL, 
				 "<loc>{$this->_host}/{$v['id']}.html</loc>", PHP_EOL,
				 // 移动端多一个标示
				 ( ( $this->_device == 'mobile' ) ? '<mobile:mobile type="mobile"/>' . PHP_EOL : '' ),
				 "<lastmod>{$time}</lastmod>", PHP_EOL,
				 '<changefreq>daily</changefreq>', PHP_EOL,
				 '<priority>0.8</priority>', PHP_EOL,
				 '</url>', PHP_EOL;
		}
		echo '</urlset>', PHP_EOL;
	}
}