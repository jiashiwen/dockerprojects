<?php
// PC端异常页面处理

// PC 版页面，乐居标准头尾模版读取
$page = D('Front', 'Logic', 'Common'); 
$tpl = $page->getPCPublicTemplate($flush);

// pc 页面，公用头部显示热搜关键词和知识列表
$cities = C('CITIES.CMS');
$city_code = I('get.city', '', 'clear_all');
$city_code = $city_code == '' ? cookie('citypub') : $city_code;
// 统一城市信息
if ( !array_key_exists($city_code, $cities) ) {
	$city_code = 'bj';
}
$city = $cities[$city_code];
$city['code'] = $city_code;

// 栏目信息列表
$lCate = D('Cate','Logic','Common');
$cateid = $lCate->getFirstTopCateid();
$cate_all = $lCate->getIndexTopCategories();

// 推荐信息列表
$result = $page->getSuggest('', $city['cn'], $city['code']);

?>
<!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<title>访问出现异常</title>
	<meta name="title" content="访问出现异常"/>
	<meta name="keywords" content="访问出现异常"/>
	<meta name="description" content="访问出现异常" />
	<link rel="stylesheet" href="//cdn.leju.com/encypc/styles/styles.css">
	<script type="text/javascript" src="http://cdn.leju.com/encypc/js/fullPage/jquery-1.8.3.min.js"></script>
</head>
<body>
	<!-- 页头 -->
	<?php echo $tpl['header']; ?>
	<!-- 导航条 -->
	<div class="z_main_menu">
		<div class="inner clearfix">
			<div class="m_l">
				<h2 class="logo"><a href="<?php echo url('index', array(), 'pc', 'baike'); ?>" title="房产百科">房产百科</a></h2>
			</div>
			<div class="m_r">
				<ul class="menu">
					<?php foreach ( $cate_all as $k => $item ) { ?>
					<li class="<?php echo $item['id']==$cateid ? 'cur' : ''; ?>">
						<a target="_blank" href="<?php echo url('index', array('city'=>$city['code'], 'cid'=>$item['id']), 'pc', 'baike');?>#wt_source=pc_fcbk_dh">
						<?php echo $item['id']==$cateid ? '<i class="line"></i>' : ''; ?>
						<?php echo $item['name']; ?></a>
						<div class="menu_ly_wrap">
							<?php
							$ic = 0; $cnt = count($item['son'])-1;
							foreach ( $item['son'] as $kk => $lv2 ) { ?>
								<?php echo $ic==0 ? '<div class="menu_ly clearfix none">' : ''; ?>
								<dl>
									<dt><a href="<?php echo url('cate', array('id'=>$lv2['id'], 'city'=>$city['code'], 'page'=>1), 'pc', 'baike');?>#wt_source=pc_fcbk_dh" title="<?php echo $lv2['name']; ?>" target="_blank"><?php echo $lv2['name']; ?></a><i></i></dt>
									<?php foreach ( $lv2['son'] as $kk3 => $lv3 ) { ?>
										<dd><a target="_blank" href="<?php echo url('cate', array('id'=>$lv3['id'], 'city'=>$city['code'], 'page'=>1), 'pc', 'baike');?>#wt_source=pc_fcbk_dh" title="<?php echo $lv3['name']; ?>"><?php echo $lv3['name']; ?></a></dd>
									<?php } // end lv2['son'] ?>
								</dl>
								<?php echo ($ic==$cnt) ? '</div>' : '';
								$ic ++;
								?>
							<?php } // end item['son'] ?>
						</div>
					</li>
					<?php } // end cate_all ?>
				</ul>
				<!-- 搜索框 -->
			<!-- 搜索框 -->
			<div class="z_search_wrap">
				<div class="z_search">
					<form id="search_form" action="<?php echo url('search', array(), 'pc', 'baike');?>#wt_source=pc_fcbk_ssan" method="get">
					<input type="text" name="keyword" value="" class="s_inp" placeholder="乐居房产百科-您身边的房产专家" autocomplete="off">
					<input type="hidden" name="city" value="{$city.code}">
					<input type="hidden" name="id" value="{$cateid}">
					<a href="#" class="s_btn" type="submit">搜&ensp;索</a>
					</form>
				</div>
				<!-- 浮层 -->
				<div class="z_search_ly none"></div>
			</div>

			</div>
		</div>
	</div>
	<div class="ty_content clearfix">
		<div class="ty_404content">
			<i class="ty_404Icon"></i>
			<h3 class="ty_404tit">非常抱歉，无法打开页面</h3>
			<i class="line"></i>
			<p class="ty_404de"><strong>可能原因：</strong><br /><span>1.网络差</span><br /><span>2.找不到请求的页面</span><br /><span>3.输入的网址不正确</span></p>
			<p class="ty_404btn"><a class="backindex" href="<?php echo url('index', array(), $device, 'baike'); ?>">返回百科首页</a><a class="backpre" href="javascript:history.back();" >返回上一页</a></p>
		</div>
	</div>
	
	<div class="z_bt_nav ty_bt_nav">
		<div class="inner clearfix">
			<?php if ( $result['kb'] ) { ?>
			<div class="nav_box">
				<h2 class="z_title">热搜知识<i></i></h2>
				<div class="ty_links clearfix">
				<?php
					foreach ( $result['kb'] as $k => $tag ) {
						echo '<a href="', $tag['url'], '">', $tag['title'], '</a>', PHP_EOL;
					}
				?>
				</div>
			</div>
			<?php } ?>
			<?php if ( $result['tag'] ) { ?>
			<div class="nav_box">
				<h2 class="z_title">关注焦点<i></i></h2>
				<div class="ty_links2 clearfix">
				<?php
					foreach ( $result['tag'] as $k => $tag ) {
						echo '<a href="', $tag['url'], '">', $tag['title'], '</a>', PHP_EOL;
					}
				?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
	<!-- 页尾 -->
	<?php echo $tpl['footer']; ?>
	<script type="text/javascript" src="http://cdn.leju.com/encypc/js/encypc.js?r"></script>
</body>
</html>