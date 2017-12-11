<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<title><?php echo ($pageinfo["title"]); ?>-乐居百科</title>
	<meta name="keywords" content="<?php echo ($pageinfo["keywords"]); ?>"/>
	<meta name="description" content="<?php echo ($pageinfo["description"]); ?>" />
	<meta name="applicable-device" content="pc">
	<link rel="alternate" media="only screen and (max-width: 640px)" href="<?php echo ($pageinfo["alt_url"]); ?>">
	<link rel="stylesheet" href="//cdn.leju.com/encypc/styles/bkstyles.css">
	<link rel="stylesheet" href="//res.leju.com/resources/encypc/pc/v1/styles/styles.css">
</head>
<body>
<!-- 页头 -->
<?php echo ($common_tpl["header"]); ?>
<!-- 导航条 -->
<?php if((CONTROLLER_NAME!= 'Index')): ?><div class="z_main_menu">
	<div class="inner clearfix">
		<div class="m_l">
			<h2 class="logo">
			<a href="<?php echo url('index', array(), 'pc', 'baike') ?>" title="房产百科">房产百科</a>
			</h2>
			<!-- <div class="city">
				<a href="#" class="btn"><?php echo ($city["cn"]); ?><i></i></a>
			</div> -->
		</div>
		<div class="m_r">
			<ul class="menu">
				<?php if(is_array($cate_all)): $i = 0; $__LIST__ = $cate_all;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li name="<?php echo ($item["id"]); ?>" class="<?php if(($i) == "1"): ?>cur<?php endif; ?>">
					<a href="<?php echo url('index', array('cid'=>$item['id']), 'pc', 'baike');?>"><i class="line"></i><?php echo ($item["name"]); ?></a>
					<div class="menu_ly_wrap">
						<?php if(is_array($item["son"])): $kk = 0; $__LIST__ = $item["son"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lv2): $mod = ($kk % 2 );++$kk; if(($kk) == "1"): ?><div class="menu_ly clearfix none"><?php endif; ?>
							<dl>
								<dt><a href="<?php echo url('cate', array('id'=>$lv2['id'], 'page'=>1), 'pc', 'baike');?>" title="<?php echo ($lv2["name"]); ?>" target="_blank"><?php echo ($lv2["name"]); ?></a><i></i></dt>
								<?php if(is_array($lv2["son"])): $i = 0; $__LIST__ = $lv2["son"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lv3): $mod = ($i % 2 );++$i;?><dd><a href="<?php echo url('cate', array('id'=>$lv3['id'], 'page'=>1), 'pc', 'baike');?>" title="<?php echo ($lv3["name"]); ?>"><?php echo ($lv3["name"]); ?></a></dd><?php endforeach; endif; else: echo "" ;endif; ?>
							</dl>
							<?php if($kk == count($item.son)-1): ?></div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					</div>
				</li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>

			<!-- 搜索框 -->
			<div class="z_search_wrap">
				<div class="z_search">
					<form id="search_form" action="<?php echo url('search', array(), 'pc', 'wiki') ?>" method="GET">
					<input type="text" name="word" class="s_inp" placeholder="乐居房产百科-您身边的房产专家" autocomplete="off">
					<input type="hidden" name="page" value="1">
					<a href="javascript:;" title="搜索" class="s_btn">搜&ensp;索</a>
					</form>
				</div>
				<!-- 浮层 -->
				<div class="z_search_ly none">
				</div>
			</div>
		</div>
	</div>
</div><?php endif; ?>

<div class="ty_content clearfix">
	<div class="ty_bread">
		<a target="_blank" href="<?php echo url('index', array(), 'pc', 'wiki'); ?>">房产词条</a><i></i><a class="on"><?php if($tag): echo ($tag); else: ?>词条列表<?php endif; ?></a>
	</div>
	<div class="b_left">
		<h2 class="ty_tit b_tit z_mt15"><i></i><?php if($tag): echo ($tag); ?>标签<?php else: ?>词条列表<?php endif; ?> </h2>
		<div class="b_con">
			<ul class="clearfix">
				<?php if(is_array($list)): foreach($list as $key=>$item): ?><li>
					<a target="_blank" href="<?php echo url('show', array($item['id'], $item['cateid']), 'pc', 'wiki'); ?>">
						<span class="pic"><img src="<?php echo (changeImageSize($item["cover"],182,136)); ?>" alt=""></span>
						<p><?php echo ($item["title"]); ?></p>
					</a>
				</li><?php endforeach; endif; ?>
			</ul>
			<?php if(!empty($pager)): ?><div class="ty_pages clearfix b_pages">
				<?php if(!empty($pager["prev"])): ?><a class="pre" href="<?php echo ($pager["prev"]); ?>"><&nbsp;&nbsp;上一页</a><?php endif; ?>
				<?php if(($pager["sp_before"]) == "true"): ?><em>...</em><?php endif; ?>
				<?php if(is_array($pager["list"])): foreach($pager["list"] as $k=>$item): if(($pager["page"]) == $item["num"]): ?><a class="ebtn on" href="javascript:void(0)"><?php echo ($item["num"]); ?></a>
					<?php else: ?>
					<a class="fbtn" href="<?php echo ($item["url"]); ?>"><?php echo ($item["num"]); ?></a><?php endif; endforeach; endif; ?>
				<?php if(($pager["sp_after"]) == "true"): ?><em>...</em><?php endif; ?>
				<a class="next" href="<?php echo ($pager["next"]); ?>">下一页&nbsp;&nbsp;></a>
				<span>共<?php echo ($pager["count"]); ?>页</span>
			</div><?php endif; ?>
		</div>
	</div>
	<div class="b_right">
		<?php if(!empty($hot)): ?><h2>热门百科词条<i></i><span>专业术语不懂问词条</span></h2>
		<div class="labelBox">
			<div class="labels">
				<?php if(is_array($hot)): foreach($hot as $key=>$h): ?><a target="_blank" href="<?php echo url('show', array($h['id'], $h['cateid']), 'pc', 'wiki'); ?>"><?php echo ($h["title"]); if($h["hot"] > 0): ?><i class="up"></i><?php endif; if($h["hot"] < 0): ?><i class="down"></i><?php endif; ?></a><?php endforeach; endif; ?>
			</div>
			<a target="_blank" href="<?php echo url('index', array(), 'pc', 'wiki'); ?>" class="more">全部房产词条</a>
		</div><?php endif; ?>
		<?php if(!empty($hot_know)): ?><h2>热门百科知识<i></i></h2>
		<ul class="b_list">
			<?php if(is_array($hot_know)): foreach($hot_know as $k=>$vo): ?><li><a target="_blank" href="<?php echo url('show', array($vo['id']), 'pc', 'baike');?>"><em <?php if($k < 3): ?>class="i01"<?php endif; ?>><?php echo ($k+1); ?></em><?php echo ($vo["title"]); ?></a></li><?php endforeach; endif; ?>
		</ul><?php endif; ?>
	</div>
</div>
<!-- 页尾导航 -->


<script type="text/javascript" src="http://cdn.leju.com/encypc/js/fullPage/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="http://cdn.leju.com/encypc/js/encypc.js?r"></script>
<script src="http://res.leju.com/scripts/app/encypc/v1/pc_detail.js" type="text/javascript"></script>
<?php
$controller = strtoupper(CONTROLLER_NAME); if ( $controller!='SHOW' ) { ?>
<script type="text/javascript">
    var city = 'quanguo';
    var level1_page = '<?php echo ($level1_page); ?>';
    var level2_page = '<?php echo ($level2_page); ?>';
    var custom_id = '<?php echo ($custom_id); ?>'; 
    var webtype='';
    var news_source='';
</script>
<script type="text/javascript" src="http://m.leju.com/resources/scripts/gather.pc.source.js"></script>
<?php } ?>
<!-- 乐居统一标准 友情链接 -->
<?php echo ($common_tpl["links"]); ?>
<!-- 乐居统一标准 页尾 -->
<?php echo ($common_tpl["footer"]); ?>
<script>
(function(){
	var bp = document.createElement('script');
	var curProtocol = window.location.protocol.split(':')[0];
	if (curProtocol === 'https') {
		bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
	} else {
		bp.src = 'http://push.zhanzhang.baidu.com/push.js';
	}
	var s = document.getElementsByTagName("script")[0];
	s.parentNode.insertBefore(bp, s);
})();
</script>
</body>
</html>