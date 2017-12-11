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
	<?php if(!empty($D["forces"])): if(is_array($D["forces"])): foreach($D["forces"] as $cid=>$forces): ?><div class="l_focus" data-cateid="<?php echo ($cid); ?>"<?php if(($cid) != $cateid): ?>style="visiblility:hidden"<?php endif; ?>>
		<ul class="l_focus_ul" style="width: 3000px;">
			<?php if(is_array($forces)): $i = 0; $__LIST__ = $forces;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li>
				<a href="<?php echo url('show', array($item['id'])); echo ($kd_index_pic); ?>">
				<img src="<?php echo (changeImageSize($item["cover"],750,340)); ?>" alt="<?php echo ($item["title"]); ?>"></a>
				<p><a href="<?php echo url('show', array($item['id'])); echo ($kd_index_pic); ?>"><?php echo ($item["title"]); ?></a></p>
			</li><?php endforeach; endif; else: echo "" ;endif; ?>
		</ul>
		<div class="l_focus_dot">
			<?php if(is_array($forces)): $key = 0; $__LIST__ = $forces;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($key % 2 );++$key;?><a class="<?php if(($key) == "1"): ?>l_cur<?php endif; ?>" href="javascript:;"></a><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
	</div><?php endforeach; endif; endif; ?>

	<div class="l_box">
		<div class="l_navBox">
			<ul class="l_nav">
				<?php if(is_array($D["cates"]["list"])): foreach($D["cates"]["list"] as $key=>$item): ?><li data-lmid＝"<?php echo ($item["id"]); ?>" class="<?php if(($key) == "0"): ?>l_cur<?php endif; if(($key) > "3"): ?>none<?php endif; ?>">
					<a href="<?php echo ($item["href"]); ?>">
					<i class="<?php echo ($item["icon"]); ?>"></i><b><?php echo ($item["title"]); ?></b></a>
				</li><?php endforeach; endif; ?>
			</ul>
		</div>
		<?php if(!empty($D["latest"])): if(is_array($D["latest"])): foreach($D["latest"] as $kk=>$l): ?><div class="l_cardBox <?php if(($kk) > "0"): ?>none<?php endif; ?>">
			<div class="b_cardBox">
				<?php if(!empty($l["topKB"])): ?><div class="b_card clearfix">
					<div class="b_left">
						<a href="<?php echo ($l["topKB"]["url"]); ?>"><h2><?php echo ($l["topKB"]["title"]); ?></h2></a>
						<h3><span class="top">置顶</span>
						<?php if(is_array($l["topKB"]["tagsinfo"])): $i = 0; $__LIST__ = $l["topKB"]["tagsinfo"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$t): $mod = ($i % 2 );++$i;?><a href="<?php echo url('agg', array($t['id']), 'touch', 'baike')?>"><span><?php echo ($t["name"]); ?></span></a><?php endforeach; endif; else: echo "" ;endif; ?>
						</h3>
						<!--<h4>乐居互联网  05-12</h4>-->
					</div>
					<div class="b_right"><img src="<?php echo (changeImageSize($l["topKB"]["cover"], 240, 180)); ?>" alt="<?php echo ($l["topKB"]["title"]); ?>"></div>
				</div><?php endif; ?>
			<?php if(is_array($l["list"])): $k = 0; $__LIST__ = $l["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($k % 2 );++$k;?><div class="b_card clearfix">
					<div class="b_left">
						<a href="<?php echo url('show', array($item['id']));?>"><h2><?php echo ($item["title"]); ?></h2></a>
						<h3>
						<?php if(is_array($item["tagsinfo"])): $i = 0; $__LIST__ = $item["tagsinfo"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$t): $mod = ($i % 2 );++$i;?><a href="<?php echo url('agg', array($t['id']), 'touch', 'baike')?>"><span><?php echo ($t["name"]); ?></span></a><?php endforeach; endif; else: echo "" ;endif; ?>
						</h3>
						<!--<h4>乐居互联网  05-12</h4>-->
					</div>
					<div class="b_right"><img src="<?php echo (changeImageSize($item["cover"], 240, 180)); ?>" alt="<?php echo ($item["title"]); ?>"></div>
				</div><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>
			<a class="l_cardMore" href="<?php echo url('cate', array('id'=>$l['cateid'])); echo ($kd_index_wdmore); ?>">更多<?php echo ($l["name"]); ?></a>
		</div><?php endforeach; endif; endif; ?>
	</div>

	<div class="l_box">
		<h2 class="b_title">热门词条<a href="<?php echo url('index', array(), 'touch', 'wiki'); echo ($kd_index_wdmore); ?>">更多</a></h2>
		<?php if(!empty($D["hotwords"])): ?><div class="l_list01">
			<ul>
			<?php if(is_array($D["hotwords"])): foreach($D["hotwords"] as $key=>$h): if($h["hot"] > 0): ?><li class="l_up"><a href="<?php echo url('show', array($h['id'], $h['cateid']), 'touch', 'wiki');?>"><?php echo ($h["title"]); ?></a></li>
				<?php elseif($h["hot"] < 0): ?>
				<li class="l_dn"><a href="<?php echo url('show', array($h['id'], $h['cateid']), 'touch', 'wiki');?>"><?php echo ($h["title"]); ?></a></li>
				<?php else: ?>
				<li><a href="<?php echo url('show', array($h['id'], $h['cateid']), 'touch', 'wiki');?>"><?php echo ($h["title"]); ?></a></li><?php endif; endforeach; endif; ?>
			</ul>
		</div><?php endif; ?>
	</div>

	<?php if(($binds["register"]) != "0"): ?><div class="l_footer">
	<p>北京怡生乐居信息服务有限公司</p>
	<p>京ICP证080057号</p>
</div><?php endif; ?>

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