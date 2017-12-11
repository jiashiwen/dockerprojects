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
<div class="content_wrapper sort_page <?php echo ($jsflag); ?>">
	<div class="b_wrapper">
		<h2 class="b_title"><?php echo ($binds["parent"]); ?></h2>
		<div class="b_cardBox">
			<?php if(!empty($topKB)): ?><div class="b_card clearfix">
				<div class="b_left">
					<a href="<?php echo ($topKB["url"]); ?>"><h2><?php echo ($topKB["title"]); ?></h2></a>
					<h3>
					<?php if(is_array($topKB["tagsinfo"])): $i = 0; $__LIST__ = $topKB["tagsinfo"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$t): $mod = ($i % 2 );++$i;?><a href="<?php echo url('agg', array($t['id']), 'touch', 'baike');?>"><span><?php echo ($t["name"]); ?></span></a><?php endforeach; endif; else: echo "" ;endif; ?>
					</h3>
					<!--<h4>乐居互联网  05-12</h4>-->
				</div>
				<div class="b_right"><img src="<?php echo (changeImageSize($topKB["cover"], 240, 180)); ?>" alt="<?php echo ($topKB["title"]); ?>"></div>
			</div><?php endif; ?>
			<?php if(!empty($list)): if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><div class="b_card clearfix">
				<div class="b_left">
					<h2><a href="<?php echo ($item["url"]); ?>"><?php echo ($item["title"]); ?></a></h2>
					<h3>
						<?php if(is_array($item["tagsinfo"])): $i = 0; $__LIST__ = $item["tagsinfo"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$t): $mod = ($i % 2 );++$i;?><a href="<?php echo url('agg', array($t['id']), 'touch', 'baike');?>"><span><?php echo ($t["name"]); ?></span></a><?php endforeach; endif; else: echo "" ;endif; ?>
					<h4>乐居互联网  <?php echo ($item["ctime"]); ?></h4>
				</div>
				<div class="b_right"><img src="<?php echo (changeImageSize($item["cover"], 240, 180)); ?>" alt=""></div>
			</div><?php endforeach; endif; else: echo "" ;endif; ?>
			<?php else: ?>
				<div class="b_noCon">
					<i></i>
					<p>我考虑一下放什么内容好， 你先看看其他栏目内容。</p>
				</div><?php endif; ?>
			<div class="b_loading none"></div>
		</div>
	</div>
	<a href="#" class="b_top"></a>
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