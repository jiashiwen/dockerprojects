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
		<a target="_blank" href="<?php echo url('index', array(), 'pc', 'wiki'); ?>">房产词条</a><i></i><a target="_blank" href="<?php echo url('listall', array(1), 'pc', 'wiki'); ?>">词条列表</a><i></i><a class="on"><?php echo ($detail["title"]); ?></a>
	</div>
	<div class="b_left">
		<h1 class="ty_tit2 b_tit"><i></i><?php echo ($detail["title"]); ?></h1>
		<div class="ty_detial">
			<ul>
				<?php $j=$n=$i=0; ?>
				<?php if(is_array($detail["content"])): foreach($detail["content"] as $k=>$vo): if($vo[0] OR $vo[1]): ?><li>
					<?php if($vo[0]): ?><i class="ty_sq"><?php echo ++$i;?></i><h3><sectiontitle><?php echo ($vo["0"]); ?></sectiontitle></h3><?php endif; ?>
					<?php if($vo[1]): echo (str_replace("sectiontitle2", "h4", $vo["1"])); endif; ?>
				</li><?php endif; endforeach; endif; ?>
			</ul>
			<i class="ty_sq_last"></i>
		</div>
		<!-- 出现的123456点 原内容点visibility: hidden; -->
		<div class="ty_fixbtn">
			<?php if(is_array($detail["content"])): foreach($detail["content"] as $k=>$vo): if($vo[0]): ?><a style="display:none" title="返回第<?php echo ($num_arr[++$n]); ?>步" <?php if($n == 1): ?>class="on"<?php endif; ?> ><?php echo ++$j;?></a><?php endif; endforeach; endif; ?>
		</div>
		<!-- 出现的123456点end -->
		<p class="ty_about">
			<?php if(is_array($detail["tagsinfo"])): foreach($detail["tagsinfo"] as $key=>$vo): ?><a target="_blank" href="<?php echo url('agg', array($vo['id'], $vo['cateid']), 'pc', 'wiki'); ?>"><?php echo ($vo["name"]); ?></a><?php endforeach; endif; ?>
		</p>
		<div class="ty_aboutD">
			<?php if(!empty($tag_know)): ?><h3>相关知识</h3>
			<div class="ty_aboutDtext">
				<ul>
					<?php if(is_array($tag_know)): foreach($tag_know as $key=>$vo): ?><li>
						<a target="_blank" href="<?php echo url('show', array($vo['id']), 'pc', 'baike');?>">
							<?php echo ($vo["title"]); if(!empty($vo["ptime"])): ?><em></em><span><?php echo ($vo["ptime"]); ?></span><?php endif; ?>
						</a>
					</li><?php endforeach; endif; ?>
				</ul>
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
<div class="z_bt_nav">
	<div class="inner clearfix">
		<?php if(is_array($cate_all)): foreach($cate_all as $key=>$vo): ?><div class="nav_box">
			<h2 class="z_title"><?php echo ($vo["name"]); ?><i></i></h2>
			<div class="links clearfix">
				<p>
					<?php if(!empty($vo["son"])): if(is_array($vo["son"])): foreach($vo["son"] as $key=>$v): if(!empty($v["name"])): ?><a href="<?php echo url('cate', array('id'=>$v['id'], 'page'=>1), 'pc', 'baike'); ?>"><?php echo ($v["name"]); ?></a>
					<span class="line"></span><?php endif; endforeach; endif; endif; ?>
				</p>
			</div>
		</div><?php endforeach; endif; ?>
	</div>
</div>
<!-- 页尾 -->
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