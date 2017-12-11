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
	<div class="b_top">
		<div class="b_searchWrapper">
			<div class="b_searchBox">
				<form id="search_form" action="<?php echo url('search', array(''), 'pc', 'wiki') ?>#wt_source=pc_fcct_ssan" method="GET">
				<input type="text" name="word" placeholder="乐居房产百科-您身边的房产专家！" autocomplete="off">
				<a href="javascript:;" class="search" type="submit" >查词条</a>
				</form>
			</div>

		</div>
	</div>
	<div class="b_conBox clearfix">
		<div class="left clearfix">
			<div class="b_card01">
				<h2>最热词条<em></em><i class="hot"></i></h2>
				<ul class="b_list01">
					<?php if(is_array($hot)): foreach($hot as $key=>$h): ?><li><a target="_blank" href="<?php echo url('show', array($h['id'], $h['cateid']), 'pc', 'wiki'); ?>#wt_source=pc_fcct_zrct"><?php if($h["hot"] > 0): ?><i class="up"></i><?php endif; if($h["hot"] < 0): ?><i></i><?php endif; echo ($h["title"]); ?></a></li><?php endforeach; endif; ?>
				</ul>
			</div>
			<div class="b_card02">
				<h2>最新词条<em></em> <a target="_blank" href="<?php echo url('listall', array(1), 'pc', 'wiki'); ?>#wt_source=pc_fcct_more" class="more">更多</h2>
				<ul class="b_list02">
					<?php if(is_array($fresh)): foreach($fresh as $key=>$f): ?><li><a target="_blank" href="<?php echo url('show', array($f['id'], $f['cateid']), 'pc', 'wiki'); ?>#wt_source=pc_fcct_axct"><i></i><?php echo ($f["title"]); ?></a></li><?php endforeach; endif; ?>
				</ul>
			</div>
		</div>
		<div class="right">
			<div class="b_card">
				<h2>房产机构百科<em></em></h2>
				<ul class="b_list03 clearfix">
					<?php if(is_array($organization)): foreach($organization as $key=>$o): ?><li><a target="_blank" href="<?php echo url('show', array($o['id'], $o['cateid']), 'pc', 'wiki'); ?>"><img src="<?php echo (changeImageSize($o["cover"],240,180)); ?>" alt="<?php echo ($o["title"]); ?>"><p><?php echo ($o["title"]); ?></p></a></li><?php endforeach; endif; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
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