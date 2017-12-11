<?php
/**
 * 百科词条服务接口
 * @author Robert <yongliang1@leju.com>
 */
namespace api\Controller;

class TagController extends BaseController {

	public function index() {
		echo 'This is [C]Tag[A]Index';
	}
	/**
	 * 提供给 CMS 新闻内容分析词使用
	 */
	public function fetchWords() {
		$s = D('Search', 'Logic', 'Common');
		$c = I('post.c',null);  //内容content
		$n = I('post.n',0,'intval'); //替换数

		$r = $s->cutWord($c, $n);
		//$r = $s->analyze(strip_tags($c), true, 10, 'dict_tags'); plant-b
		if($r)
		{
			ajax_succ($r);
		}
		else
		{
			ajax_error();
		}
	}
}