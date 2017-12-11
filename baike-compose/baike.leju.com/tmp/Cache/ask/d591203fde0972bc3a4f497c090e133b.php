<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, minimum-scale=1.0, user-scalable=no">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="format-detection" content="telephone=no">
	<title><?php echo ($pageinfo["seo_title"]); ?></title>
	<meta name="applicable-device" content="pc">
	<meta name="keywords" content="<?php echo ($pageinfo["keywords"]); ?>"/>
	<meta name="description" content="<?php echo ($pageinfo["description"]); ?>" />
	<link rel="canonical" href="<?php echo ($pageinfo["alt_url"]); ?>">
	<link rel="stylesheet" href="//cdn.leju.com/qawap/styles/styles.css">
	<script type="text/javascript">
	fnResize();
	var k = null;
	window.addEventListener("resize", function() {
		clearTimeout(k);
		k = setTimeout(fnResize, 300);
	}, false);
	function fnResize() {
		document.getElementsByTagName('html')[0].style.fontSize = (document.documentElement.clientWidth) / 15 + 'px';
	}
	</script>
</head>

<body>
	
	<div class="banner">
	<img src="//cdn.leju.com/qawap/images/banner-h.jpg" alt="Banner Background">
	<div class="b-slogan">
		<img src="//cdn.leju.com/qawap/images/slogan.png" alt="Banner Slogan">
	</div>
	<div class="b-s-wrap">
		<form action="<?php echo url('search', array(), 'touch', 'ask'); ?>" method="GET">
		<input type="text" name="k" class="b-s-input" placeholder="输入关键词，查找您想要的答案">
		<i class="i-b-search"></i>
		</form>
	</div>
</div>
<div class="nav">
	<ul class="n-list">
	<?php
 if ( $catetree ) { $i = 0; foreach ( $catetree as $lv1_cateid => $lv1_cate ) { $icons = array( 0 => 'i-n-new', 1 => 'i-n-shand', 2 => 'i-n-furn', 3 => 'i-n-fitm', ); ?>
		<li>
			<a href="<?php echo url('list', array($lv1_cateid), 'touch', 'ask').'#ln=ask_index_nav'; ?>">
				<p><?php echo ($lv1_cate["name"]); ?></p>
				<i class="<?php echo $icons[$i]; ?>"></i>
			</a>
		</li>
	<?php
 $i++; } } ?>
	</ul>
</div>
<div class="section">
	<div class="s-header"><p>精华推荐</p></div>
	<div class="s-content">
		<ul class="s-c-swiper">
		<?php foreach ( $focus as $i => $item ) { ?>
			<li>
				<a href="<?php echo url('show', array($item['id']), 'touch', 'ask').'#ln=ask_index_tj'; ?>">
					<img src="<?php echo (changeImageSize($item["pic"], 276, 155)); ?>" alt="<?php echo ($item["title"]); ?>">
					<div class="s-c-s-cover">
						<p><?php echo ($item["title"]); ?></p>
					</div>
				</a>
			</li>
		<?php } ?>
		</ul>
	</div>
</div>
<div class="section">
	<div class="s-header"><p>热门问题</p></div>
	<div class="s-content" id="scroll_1">
		<ul class="s-c-article">
		<?php foreach ( $q['list'] as $i => $_q ) { ?>
			<li>
				<a href="<?php echo ($_q["url"]); ?>#ln=ask_index_rm"><p><?php echo ($_q["title"]); ?></p></a>
				<?php if ( $_q['catepathname'] ) { ?>
				<ul class="s-c-column hide">
				<?php foreach ( $_q['catepathname'] as $i => $cate ) { ?>
					<li><a href="<?php echo ($cate["url"]); ?>#ln=ask_index_rm"><?php echo ($cate["name"]); ?></a></li>
				<?php } ?>
				</ul>
				<?php } ?>
				<div class="s-c-l-wrap">
					<i class="i-s-label"></i>
					<ul class="s-c-label">
					<?php foreach ( $_q['tagsinfo'] as $i => $tag ) { ?>
						<li><a href="<?php echo ($tag["url"]); ?>#ln=ask_index_rm"><?php echo ($tag["name"]); ?></a></li>
					<?php } ?>
					</ul>
				</div>
			</li>
		<?php } ?>
		</ul>
	</div>
</div>
<a href="javascript:;" class="ask">提问</a>

<?php if ( $q['pager']['hasnext']==1 ) { ?>
<div class="loading">
	<i class="i-l-loading"></i>
	<p>加载更多</p>
</div>
<?php } ?>

	<div class="float-wrap sugdiv1 hide">
	<div class="header">
		<div class="h-t-warp hide">
			<i class="i-h-back"></i>
			<i class="i-h-title"></i>
			<i class="i-h-t-search"></i>
		</div>
		<div class="h-s-warp">
			<div class="h-s-i-warp">
				<i class="i-h-i-search"></i>
				<input type="text" class="h-s-input suginp1" placeholder="房产知识、专业术语、问题解疑一站解决">
				<a href="javascript:;" class="h-s-clear"><i class="i-h-clear"></i></a>
			</div>
			<a href="javascript:;" class="h-s-cancle">取消</a>
		</div>
	</div>
	<div class="section s-history">
		<div class="s-header">
			<p>最近搜索</p>
			<a href="javascript:;" class="s-h-btn c-red">清除</a>
		</div>
		<div class="s-content">
			<ul class="s-c-l-list"></ul>
		</div>
	</div>
	<div class="section s-hot">
		<div class="s-header">
			<p>热门知识</p>
			<a href="javascript:;" class="s-h-btn">换一批</a>
		</div>
		<div class="s-content">
		<?php
 foreach ( $hot_kb as $i => $blocks ) { $display = $i > 0 ? ' hotkb hide' : ' hotkb'; ?>
			<ul class="s-c-article<?php echo ($display); ?>">
			<?php foreach ( $blocks as $j => $line ) { ?>
				<li>
					<a href="<?php echo ($line["url"]); ?>">
						<p><?php echo ($line["title"]); ?></p>
					</a>
				</li>
			<?php } ?>
			</ul>
		<?php  } ?>
			<ul class="s-c-article">
			</ul>
		</div>
	</div>
</div>
<div class="float-wrap sugdiv2 hide">
	<div class="header">
		<div class="h-t-warp hide">
			<i class="i-h-back"></i>
			<i class="i-h-title"></i>
			<i class="i-h-t-search"></i>
		</div>
		<div class="h-s-warp">
			<form id="search_form" action="/search.html" method="get">
			<div class="h-s-i-warp">
				<i class="i-h-i-search"></i>
				<input type="text" class="h-s-input suginp2" name="k" placeholder="房产知识、专业术语、问题解疑一站解决">
				<a href="javascript:;" class="h-s-clear"><i class="i-h-clear"></i></a>
			</div>
			<a href="javascript:;" class="h-s-cancle">取消</a>
			</form>
		</div>
	</div>
	<div class="section s-search">
		<div class="s-content">
			<ul class="s-c-article suglist"></ul>
		</div>
	</div>
	<a href="javascript:;" class="ask">提问</a>
</div>


<script src="//cdn.leju.com/qawap/js/qawap.js?r"></script>
<script src="//cdn.leju.com/sso/sso.js"></script>
<script type="text/javascript">
	var city = 'quanguo';
	var level1_page = '<?php echo ($statscode["level1_page"]); ?>';
	var level2_page = '<?php echo ($statscode["level2_page"]); ?>';
	var custom_id = '<?php echo ($statscode["custom_id"]); ?>';
	var news_source='<?php echo ($statscode["news_source"]); ?>';
</script>
<script>
(function(){
	var bp = document.createElement('script');
	var curProtocol = window.location.protocol.split(':')[0];
	if (curProtocol === 'https'){
		bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
	} else {
		bp.src = 'http://push.zhanzhang.baidu.com/push.js';
	}
	var s = document.getElementsByTagName("script")[0];
	s.parentNode.insertBefore(bp, s);
})();
</script>
<script type="text/javascript" src="http://cdn.leju.com/lejuTj/gather.source.js"></script>
</body>
</html>