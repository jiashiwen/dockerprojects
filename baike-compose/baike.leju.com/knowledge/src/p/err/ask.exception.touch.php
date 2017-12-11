<?php
// 移动端异常页面处理
$cities = C('CITIES.ALL');
$city_code = I('get.city', '', 'trim,strip_tags,htmlspecialchars');
$city_code = $city_code == '' ? cookie('B_CITY') : $city_code;
// 统一城市信息
if ( !array_key_exists($city_code, $cities) ) {
	$city_code = 'bj';
}
$city = $cities[$city_code];
$city['code'] = $city_code;

// 推荐信息列表
$page = D('Front', 'Logic', 'Common'); 
$result = $page->getSuggest('', $city['cn'], $city['code']);

?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, minimal-ui">
<meta name="format-detection" content="telephone=no" />
<title>访问出现异常</title>
<meta name="title" content="访问出现异常"/>
<meta name="keywords" content="访问出现异常"/>
<meta name="description" content="访问出现异常" />
<link rel="stylesheet" href="//<?php echo $_SERVER['PS_URL']; ?>/prd/css/lore.css">
<script> ;(function() {fnResize(); var k = null; window.addEventListener("resize",function(){clearTimeout(k);k = setTimeout(fnResize,300);},false); function fnResize(){document.getElementsByTagName('html')[0].style.fontSize = (document.documentElement.clientWidth) / 15 + 'px';}}());</script>
</head>

<body class="l_body">
<?php if ( $is_app == 'notapp' ) { ?>
<!--
	<header class="ll_header">
		<a class="ll_logo ll_i" href="http://m.leju.com/index_{$city['code']}.html"></a>
		<h2 class="ll_header_h2"><a href="<?php echo url('index', array('city'=>$city['code']), 'touch', 'baike'); ?>"><img src="//<?php echo $_SERVER['PS_URL']; ?>/images/d_logo.png"></a></h2>
		<div class="ll_headerR">
			<a class="ll_header_sch ll_i" href="#"></a>
		</div>
	</header>
-->
<?php } ?>
	<div class="b_404">
		<i></i>
		<h2>非常抱歉，无法打开页面</h2>
		<h3>可能原因：</h3>
		<ul>
			<li>1. 网络信号差</li>
			<li>2. 找不到请求的页面</li>
			<li>3. 输入的网址不正确</li>
		</ul>
		<div class="b_btnBox">
			<a href="<?php echo url('index', array(), 'touch', 'baike'); ?>#">返回百科首页</a>
			<a href="javascript:history.back();" class="backLastPage">返回上一页</a>
		</div>
	</div>
	<?php if ( $result['kb'] ) { ?>
	<div class="l_box">
		<h2 class="b_title">热门知识<a href="<?php echo url('index', array(), 'touch', 'baike'); ?>">更多</a></h2>
		<ul class="b_list002 b_list001">
		<?php
			$i = 0;
			foreach ( $result['kb'] as $k => $tag ) {
				if ( $i<5 ) {
					echo '<li><a href="', $tag['url'], '"><i></i>', $tag['title'], '</a></li>', PHP_EOL;
				} else {
					break;
				}
				$i++;
			}
		?>
		</ul>
	</div>
	<?php } ?>
	<?php if ( $result['tag'] ) { ?>
	<div class="l_box">
		<h2 class="b_title">热门词条</h2>
		<ul class="b_list002">
		<?php
			$i = 0;
			foreach ( $result['tag'] as $k => $tag ) {
				if ( $i<6 ) {
					echo '<li><a href="', $tag['url'], '">', $tag['title'], '</a></li>', PHP_EOL;
				} else {
					break;
				}
				$i++;
			}
		?>
		</ul>
	</div>
	<?php } ?>

	<div class="l_footer">
		<p>北京怡生乐居信息服务有限公司</p>
		<p>京ICP证080057号</p>
	</div>
<!--
	<div class="search_wrapper none b_wrapper">
		<div class="b_topBox">
			<a href="#" class="b_cancel fr">取消</a>
			<div class="b_searchBox fr">
				<form action="<?php echo url('search', array('keyword'=>''), 'touch', 'baike');?>">
					<input type="text" placeholder="搜知识" autocomplete="off" name="keyword">
					<a href="#" value="{$pageinfo.keyword}" class="error none"></a>
				</form>
			</div>
		</div>
		<ul class="b_list">
		</ul>
	</div>
-->
	<script type="text/javascript" src="//<?php echo $_SERVER['PS_URL']; ?>/prd/js/lore.js"></script>
	<script type="text/javascript" src="http://cdn.leju.com/lejuTj/gather.source.js"></script>
</body>
</html>