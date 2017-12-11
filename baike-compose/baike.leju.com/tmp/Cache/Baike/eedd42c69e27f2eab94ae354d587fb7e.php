<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, minimal-ui">
<meta name="format-detection" content="telephone=no" />
<title><?php echo ($pageinfo["title"]); ?></title>
<meta name="applicable-device" content="mobile">
<meta name="title" content="<?php echo ($pageinfo["seo_title"]); ?>"/>
<meta name="keywords" content="<?php echo ($pageinfo["keywords"]); ?>"/>
<meta name="description" content="<?php echo ($pageinfo["description"]); ?>" />
<link rel="canonical" href="<?php echo ($pageinfo["alt_url"]); ?>">
<link rel="stylesheet" href="//<?php echo ($_SERVER['PS_URL']); ?>/prd/css/lore.css">
<script> ;(function() {fnResize(); var k = null; window.addEventListener("resize",function(){clearTimeout(k);k = setTimeout(fnResize,300);},false); function fnResize(){document.getElementsByTagName('html')[0].style.fontSize = (document.documentElement.clientWidth) / 15 + 'px';}}());</script>
<script type="text/javascript">
	var sortId = "<?php echo ($sortId); ?>";
	var city = "<?php echo ($city['code']); ?>";
</script>
</head>
<body class="l_body">
<?php if(!empty($info["cover"])): ?><div style='margin:0 auto;width:0px;height:0px;overflow:hidden;'><img src="<?php echo ($info["cover"]); ?>" width='700'></div><?php endif; ?>
<input type="hidden" id="sortId" name="sortId" value="<?php echo ($binds["sortid"]); ?>">
<?php if(($isapp) == "notapp"): if(($index_flag) == "0"): ?><header class="ll_header">
	<a class="ll_logo ll_i" href="http://m.leju.com/index_<?php echo ($city['code']); ?>.html"></a>
	<h2 class="ll_header_h2"><a href="<?php echo url('index', array('city'=>$city['code']), 'touch', 'baike'); ?>"><img src="//<?php echo ($_SERVER['PS_URL']); ?>/images/d_logo.png"></a></h2>
	<div class="ll_headerR">
		<a class="ll_header_sch ll_i" href="#"></a>
	</div>
</header>
<?php else: ?>
<header class="ll_header">
	<a class="ll_header_bk" href="#"></a>
	<?php if((CONTROLLER_NAME== 'Show') AND (ACTION_NAME== 'index') AND $show_title_nav): ?><a class="z_header_link" href="#"></a><?php endif; ?>
	<h2 class="ll_header_h2"><a href="<?php echo url('index', array('city'=>$city['code']), 'touch', 'baike'); ?>"><img src="//<?php echo ($_SERVER['PS_URL']); ?>/images/d_logo.png"></a></h2>
	<div class="ll_headerR">
		<a class="ll_header_sch ll_i" href="#"></a>
	</div>
</header><?php endif; endif; ?>
<div class="content_wrapper <?php echo ($jsflag); ?>">
	<!-- 知识导航 -->
	<div class="zNav">
		<?php if(!empty($cate_all)): foreach ( $cate_all as $ti => $c ) { ?>
		<div class="nav_blk">
			<a href="<?php echo url('cate', array('id'=>$c['id'], 'city'=>$city['code']));?>"><h2><?php echo ($c["name"]); ?></h2></a>
			<?php if(!empty($c["son"])): ?><ul class="clearfix">
			<li>
			<?php
 $key = 0; foreach ( $c['son'] as $si => $s ) { ?>
				<?php if($key % 3 == 0): if(($key) != "1"): ?></li><li><?php endif; ?><a href="<?php echo url('cate', array('id'=>$s['id'], 'city'=>$city['code']));?>"><?php echo ($s["name"]); ?></a>
				<?php else: ?>
					<a href="<?php echo url('cate', array('id'=>$s['id'], 'city'=>$city['code']));?>"><?php echo ($s["name"]); ?></a><?php endif; ?>
			<?php
 $key ++; } ?>
			</ul><?php endif; ?>
		</div>
		<?php } endif; ?>
	</div>
</div>

<div class="search_wrapper none b_wrapper">
	<div class="b_topBox">
		<a href="#" class="b_cancel fr">取消</a>
		<div class="b_searchBox fr">
			<form action="<?php echo url('search', array('keyword'=>''), 'touch', 'baike');?>">
				<input type="text" placeholder="搜知识" autocomplete="off" name="keyword">
				<a href="#" value="<?php echo ($pageinfo["keyword"]); ?>" class="error none"></a>
			</form>
		</div>
	</div>
	<ul class="b_list">
		<!-- <li><a href="#"><span>恒大</span>地产</a></li> -->
	</ul>
</div>
<script type="text/javascript" src="//<?php echo ($_SERVER['PS_URL']); ?>/prd/js/lore.js"></script>
<script type="text/javascript">
    var city = "<?php echo ($city["stat"]); ?>";
    var level1_page = "<?php echo ($level1_page); ?>";
    var level2_page = "<?php echo ($level2_page); ?>";
    var custom_id = "<?php echo ($custom_id); ?>";
    var webtype='';
    var news_source="<?php echo ($news_source); ?>";
</script>
<script type="text/javascript" src="http://cdn.leju.com/lejuTj/gather.source.js"></script>
<script>
(function(){
    var bp = document.createElement('script');
    var curProtocol = window.location.protocol.split(':')[0];
    if (curProtocol === 'https') {
        bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
    }
    else {
        bp.src = 'http://push.zhanzhang.baidu.com/push.js';
    }
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(bp, s);
})();
</script>
</body>
</html>