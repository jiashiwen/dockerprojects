<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, minimal-ui">
	<meta name="format-detection" content="telephone=no" />
	<title><?php echo ($pageinfo["title"]); ?>-乐居百科</title>
	<meta name="applicable-device" content="mobile">
	<meta name="keywords" content="<?php echo ($pageinfo["keywords"]); ?>"/>
	<meta name="description" content="<?php echo ($pageinfo["description"]); ?>" />
	<link rel="canonical" href="<?php echo ($pageinfo["alt_url"]); ?>">
	<link rel="stylesheet" href="//<?php echo ($_SERVER['PS_URL']); ?>/prd/css/entry.css">
	<script> ;(function() {fnResize(); var k = null; window.addEventListener("resize",function(){clearTimeout(k);k = setTimeout(fnResize,300);},false); function fnResize(){document.getElementsByTagName('html')[0].style.fontSize = (document.documentElement.clientWidth) / 15 + 'px';}}());</script>
</head>
<body class="y_bg">
<?php if(!empty($detail["pic"])): ?><div style='margin:0 auto;width:0px;height:0px;overflow:hidden;'><img src="<?php echo ($detail["pic"]); ?>" width='700'></div><?php endif; ?>
<?php if(($isapp) == "notapp"): ?><header class="ll_header">
	<a class="ll_header_bk" href="#"></a>
	<?php if((CONTROLLER_NAME== 'Show') AND $show_title_nav): ?><a class="z_header_link" href="#"></a><?php endif; ?>
	<h2 class="ll_header_h2"><a href="<?php echo url('index', array(), 'touch', 'baike'); ?>"><img src="//<?php echo ($_SERVER['PS_URL']); ?>/images/y_logo.png"></a></h2>
	<div class="ll_headerR">
		<a class="ll_header_sch ll_i" href="#"></a>
	</div>
</header><?php endif; ?>

<div class="content_wrapper tagindex">
	<?php if(!empty($focus)): ?><div class="l_focus">
			<ul class="l_focus_ul" style="width: 3000px;">
				<?php if(is_array($focus)): foreach($focus as $key=>$f): ?><li>
						<a href="<?php echo url('show', array($f['id'], $f['cateid']), 'touch', 'wiki'); ?>#ln=wd_index_pic"><img src="<?php echo (changeImageSize($f["cover"],750,340)); ?>" alt="<?php echo ($f["title"]); ?>"></a>
						<p><?php echo ($f["title"]); ?></p>
					</li><?php endforeach; endif; ?>
			</ul>
			<div class="l_focus_dot">
				<?php if(is_array($focus)): foreach($focus as $k=>$f): if(($k) == "0"): ?><a class="l_cur" href="javascript:;"></a>
					<?php else: ?>
						<a href="javascript:;"></a><?php endif; endforeach; endif; ?>
			</div>
		</div><?php endif; ?>
	<div class="l_box">
		<h2 class="b_title">热门词条</h2>
		<?php if(!empty($hot)): ?><div class="l_list01">
				<ul>
					<?php if(is_array($hot)): foreach($hot as $key=>$h): if($h["hot"] > 0): ?><li class="l_up">
						<?php elseif($h["hot"] < 0): ?>
							<li class="l_dn">
						<?php else: ?>
							<li><?php endif; ?>
						<a href="<?php echo url('show', array($h['id'], $h['cateid']), 'touch', 'wiki'); ?>#ln=wd_index_rm"><?php echo ($h["title"]); ?></a></li><?php endforeach; endif; ?>
				</ul>
			</div><?php endif; ?>
	</div>
	<div class="y_section">
		<div class="b_wrapper">
			<h2 class="b_title">房产机构百科 <a href="<?php echo url('list', array(1), 'touch', 'wiki'); ?>#ln=wd_index_jg">更多</a></h2>
		</div>
		<?php if(!empty($organization)): ?><ul class="y_list01">
				<?php if(is_array($organization)): foreach($organization as $key=>$o): ?><li>
						<a href="<?php echo url('show', array($o['id'], $o['cateid']), 'touch', 'wiki'); ?>#ln=wd_index_jg">
							<div class="y_img"><img src="<?php echo (changeImageSize($o["cover"],240,180)); ?>"></div>
							<div class="y_tit"><?php echo ($o["title"]); ?></div>
						</a>
					</li><?php endforeach; endif; ?>
			</ul><?php endif; ?>
	</div>
	<div class="y_section">
		<div class="b_wrapper">
			<h2 class="b_title">最新词条</h2>
		</div>
		<?php if(!empty($fresh)): ?><ul class="y_list02">
				<?php if(is_array($fresh)): foreach($fresh as $key=>$f): ?><li><a href="<?php echo url('show', array($f['id'], $f['cateid']), 'touch', 'wiki'); ?>#ln=wd_index_zx"><?php echo ($f["title"]); ?></a></li><?php endforeach; endif; ?>
			</ul><?php endif; ?>
		<a class="y_more" href="<?php echo url('listall', array(), 'touch', 'wiki'); ?>#ln=wd_index_all">全部词条</a>
	</div>
	<div class="l_footer">
		<p>北京怡生乐居信息服务有限公司</p>
		<p>京ICP证080057号</p>
	</div>
</div>

<div class="search_wrapper none b_wrapper">
	<div class="b_topBox">
		<a href="#" class="b_cancel fr">取消</a>
		<div class="b_searchBox fr">
			<form action="<?php echo url('search', array(''), 'touch', 'wiki'); ?>">
				<input type="search" placeholder="搜词条" name="word" autocomplete="off">
				<a href="#" class="error none"></a>
			</form>
		</div>
	</div>
	<ul class="b_list">
		<!-- <li><a href="#"><span>恒大</span>地产</a></li> -->
	</ul>
</div>
<script type="text/javascript" src="//<?php echo ($_SERVER['PS_URL']); ?>/prd/js/entry.js"></script>
<script type="text/javascript">
    var city = 'quanguo';
    var level1_page = '<?php echo ($level1_page); ?>';
    var level2_page = '<?php echo ($level2_page); ?>';
    var custom_id = '<?php echo ($custom_id); ?>';
    var webtype='';
    var news_source='';
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