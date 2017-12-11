<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<title><?php echo ($pageinfo["title"]); ?></title>
	<meta name="title" content="<?php echo ($pageinfo["seo_title"]); ?>"/>
	<meta name="keywords" content="<?php echo ($pageinfo["seo_keywords"]); ?>"/>
	<meta name="description" content="<?php echo ($pageinfo["seo_description"]); ?>" />
	<meta name="applicable-device" content="pc">
	<link rel="alternate" media="only screen and (max-width: 640px)" href="<?php echo ($pageinfo["alt_url"]); ?>">
	<link rel="stylesheet" href="//res.leju.com/resources/encypc/pc/v1/styles/styles.css">
	<script src="http://res.leju.com/scripts/libs/jquery/v1/jquery.js"></script>
</head>
<?php
 $album_title = trim($detail['album']['title']); if ( $album_title == '' ) { $album_title = $detail['title'].'的相册'; } $total = count($detail['album']['list']); $current = 1; $position = ''; if ( $detail['basic']['position'] ) { $position = '（'.$detail['basic']['position'].'）'; } ?>
<body style="background: #f2f3f7;">
	<div class="z_global_nav_n">
		<div class="gn_header clearfix">
			<div class="main_nav dll_mainnav">
				<a href="//leju.com" title="乐居首页" class="cur">乐居首页</a>
				<a href="javascript:;" title="<?php echo ($detail["title"]); ?>" class="dll_crumbs"><?php echo ($detail["title"]); echo ($position); ?></a>
				<span class="dll_crumbsicon">&gt;</span>
				<span class="dll_crumbs02">词条图片</span>
			</div>
			<a href="/tag/word-<?php echo ($detail["id"]); ?>.html" target="_blank" class="dll_return">返回词条</a>
		</div>
	</div>
	<div class="dll_content">
		<div class="dll_page">
			<span class="dll_pagenum"></span>
			<h2><?php echo ($album_title); ?></h2>
		</div>
		<div class="l_scan">
			<div class="l_right">
				<div class="l_rightImg">
					<ul>
					<?php
 foreach ( $detail['album']['list'] as $i => $img ) { $class = $i==0 ? ' class="l_cur"' : ''; ?>
						<li<?php echo ($class); ?>><a href="javascript:;" bsrc="<?php echo (changeImageSize($img["img"], 698, 524)); ?>"><em></em><img src="<?php echo (changeImageSize($img["img"], 120, 90)); ?>"></a></li>
					<?php } ?>
					</ul>
				</div>
				<a href="javascript:;" class="l_up"><i></i></a>
				<a href="javascript:;" class="l_dn"><i></i></a>
			</div>
			<div class="l_main">
				<div class="l_mainImg_wrap">
					<div class="inner clearfix">
					<a href="javascript:;" class="big_pic"><img src="http://res.leju.com/resources/encypc/pc/v1/images/loading.gif" alt=""></a>
					</div>
				</div>
				<!-- 置灰加类名gray1 -->
				<a href="javascript:;" class="l_btn_pre"></a>
				<a href="javascript:;" class="l_btn_next"></a>
			</div>
		</div>
	</div>
<script src="http://res.leju.com/scripts/app/encypc/v1/pc_album.js" type="text/javascript"></script>
<script type="text/javascript">
var city = 'quanguo';
var level1_page = '<?php echo ($stats["level1"]); ?>';
var level2_page = '<?php echo ($stats["level2"]); ?>';
var level3_page = '<?php echo ($stats["level3"]); ?>';
var custom_id = '<?php echo ($stats["custom_id"]); ?>';
var news_source='<?php echo ($stats["news_source"]); ?>';
</script>
<script type="text/javascript" src="http://cdn.leju.com/lejuTj/gather.pc.source.js"></script>
</body>
</html>