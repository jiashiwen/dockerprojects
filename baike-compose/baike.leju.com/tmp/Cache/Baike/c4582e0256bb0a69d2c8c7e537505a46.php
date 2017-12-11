<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<title><?php echo ($pageinfo["title"]); ?></title>
	<link rel="alternate" media="only screen and (max-width: 640px)" href="<?php echo ($pageinfo["alt_url"]); ?>" >
	<meta name="applicable-device" content="pc">
	<meta name="title" content="<?php echo ($pageinfo["seo_title"]); ?>"/>
	<meta name="keywords" content="<?php echo ($pageinfo["keywords"]); ?>"/>
	<meta name="description" content="<?php echo ($pageinfo["description"]); ?>" />
	<link rel="stylesheet" href="//cdn.leju.com/encypc/styles/bkstyles.css">
	<script type="text/javascript" src="http://cdn.leju.com/encypc/js/fullPage/jquery-1.8.3.min.js"></script>
</head>
<body>
	<!-- 乐居统一标准 页头 -->
<?php echo ($common_tpl["header"]); ?>
<!-- 导航条 -->
<div class="z_main_menu">
	<div class="inner clearfix">
		<div class="m_l">
			<h2 class="logo">
			<a href="<?php echo url('index', array(), 'pc', 'baike'); ?>" title="房产百科">房产百科</a>
			</h2>
			<div class="city">
			</div>
		</div>
		<div class="m_r">
			<ul class="menu">
				<?php if(is_array($cate_all)): $k = 0; $__LIST__ = $cate_all;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($k % 2 );++$k;?><li class="<?php if(($item["id"]) == $cateid): ?>cur<?php endif; ?>">
						<a target="_blank" href="<?php echo url('index', array('cid'=>$item['id']), 'pc', 'baike');?>#wt_source=pc_fcbk_dh"><?php if(($item["id"]) == $cateid): ?><i class="line"></i><?php endif; echo ($item["name"]); ?></a>
						<div class="menu_ly_wrap">
							<?php if(is_array($item["son"])): $kk = 0; $__LIST__ = $item["son"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lv2): $mod = ($kk % 2 );++$kk; if(($kk) == "1"): ?><div class="menu_ly clearfix none"><?php endif; ?>
								<dl>
									<dt><a href="<?php echo url('cate', array('id'=>$lv2['id'], 'page'=>1), 'pc', 'baike');?>#wt_source=pc_fcbk_dh" title="<?php echo ($lv2["name"]); ?>" target="_blank"><?php echo ($lv2["name"]); ?></a><i></i></dt>
									<?php if(is_array($lv2["son"])): $i = 0; $__LIST__ = $lv2["son"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lv3): $mod = ($i % 2 );++$i;?><dd><a target="_blank" href="<?php echo url('cate', array('id'=>$lv3['id'], 'page'=>1), 'pc', 'baike');?>#wt_source=pc_fcbk_dh" title="<?php echo ($lv3["name"]); ?>"><?php echo ($lv3["name"]); ?></a></dd><?php endforeach; endif; else: echo "" ;endif; ?>
								</dl>
								<?php if($kk == count($item.son)-1): ?></div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
						</div>
					</li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>

			<!-- 搜索框 -->
			<div class="z_search_wrap">
				<div class="z_search">
					<form id="search_form" action="<?php echo url('search', array(), 'pc', 'baike');?>#wt_source=pc_fcbk_ssan" method="get">
					<input type="text" name="keyword" value="" class="s_inp" placeholder="乐居房产百科-您身边的房产专家" autocomplete="off">
					<input type="hidden" name="city" value="<?php echo ($city["code"]); ?>">
					<input type="hidden" name="id" value="<?php echo ($cateid); ?>">
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
	<div class="ty_bread">
		<?php echo ($bread); ?><i></i><a class="on"><?php echo ($info["title"]); ?></a>
	</div>
	<div class="b_left">
		<h1 class="ty_tit2 b_tit"><i></i><?php echo ($info["title"]); ?></h1>
		<div class="ty_detial">
			<ul>
				<?php $j=$n=$i=0 ?>
				<?php if(is_array($info["content"])): foreach($info["content"] as $k=>$vo): if($vo[0] OR $vo[1]): ?><li>
					<?php if($vo[0]): ?><i class="ty_sq"><?php echo ++$i;?></i>
					<h3><sectiontitle><?php echo ($vo["0"]); ?></sectiontitle></h3><?php endif; ?>
					<?php if($vo[1]): echo (str_replace("sectiontitle2", "h4", $vo["1"])); endif; ?>

				</li><?php endif; endforeach; endif; ?>
			</ul>
		</div>
		<!-- 出现的123456点 原内容点visibility: hidden; -->
		<div class="ty_fixbtn">
			<?php if(is_array($info["content"])): foreach($info["content"] as $k=>$vo): if($vo[0]): ?><a style="display:none" href="javascript:;" title="返回第<?php echo $num_arr[++$n] ?>步" <?php if($n == 1): ?>class="on"<?php endif; ?> ><?php echo ++$j;?></a><?php endif; endforeach; endif; ?>
		</div>
		<!-- 出现的123456点end -->
		<p class="ty_about">
			<?php if(is_array($info["tagsinfo"])): foreach($info["tagsinfo"] as $key=>$vo): ?><a href="<?php echo url('agg', array('tag'=>$vo['id'], 'id'=>$cateid, 'page'=>1), 'pc', 'baike');?>"><?php echo ($vo["name"]); ?></a><?php endforeach; endif; ?>
		</p>
		<?php if(!empty($relakb)): ?><div class="ty_aboutD">
			<h3>相关知识</h3>
			<div class="ty_aboutDpic">
				<ul>
				<?php if(is_array($relakb)): $i = 0; $__LIST__ = $relakb;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$re): $mod = ($i % 2 );++$i;?><li>
						<a target="_blank" href="<?php echo ($re["url"]); ?>">
							<img src="<?php echo ($re["cover"]); ?>" alt="">
							<h4><?php echo ($re["title"]); ?></h4>
						</a>
					</li><?php endforeach; endif; else: echo "" ;endif; ?>
				</ul>
			</div>
		</div><?php endif; ?>
	</div>
	<div class="b_right">
		<h2>百科导航<i></i></h2>
		<div class="ty_slid b_slid">
			<h3><?php echo ($nav["name"]); ?><i></i></h3>
			<p>
				<?php if(is_array($nav["son"])): foreach($nav["son"] as $key=>$s): ?><a target="_blank" href="<?php echo url('cate', array('id'=>$s['id'], 'page'=>1), 'pc', 'baike');?>"><?php if(($s["id"]) == $curcateid): ?><strong style="color:#df1830;"><?php echo ($s["name"]); ?></strong><?php else: echo ($s["name"]); endif; ?></a><?php endforeach; endif; ?>
			</p>
		</div>
		<div class="hotbk">
			<?php if(!empty($rank)): ?><h2>热门百科知识<i></i></h2>
			<ul class="b_list">
				<?php if(is_array($rank)): $i = 0; $__LIST__ = $rank;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i; if(($i) < "11"): ?><li><a target="_blank" href="<?php echo ($r["url"]); ?>"><em <?php if(($i) < "4"): ?>class="i01"<?php endif; ?>><?php echo ($i); ?></em><?php echo ($r["title"]); ?></a></li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
			</ul><?php endif; ?>
			<?php if(!empty($randoms)): ?><h2>猜你喜欢<i></i></h2>
			<ul class="b_list">
				<?php if(is_array($randoms)): $i = 0; $__LIST__ = $randoms;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i; if(($i) < "11"): ?><li><a target="_blank" href="<?php echo ($r["url"]); ?>"><em><?php echo ($i); ?></em><?php echo ($r["title"]); ?></a></li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
			</ul><?php endif; ?>
		</div>
	</div>
</div>
<!-- 页尾 -->
<div class="z_bt_nav">
	<div class="inner clearfix">
		<?php if(is_array($cate_all)): $i = 0; $__LIST__ = $cate_all;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><div class="nav_box">
				<h2 class="z_title"><?php echo ($item["name"]); ?><i></i></h2>
				<div class="links clearfix">
					<?php if(!empty($item["son"])): if(is_array($item["son"])): $i = 0; $__LIST__ = $item["son"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ii): $mod = ($i % 2 );++$i; if(!empty($ii["name"])): ?><a target="_blank" href="<?php echo url('cate', array('id'=>$ii['id'], 'page'=>1), 'pc', 'baike');?>#wt_source=pc_fcbk_dh2"><?php echo ($ii["name"]); ?></a>
						<span class="line"></span><?php endif; endforeach; endif; else: echo "" ;endif; endif; ?>
				</div>
			</div><?php endforeach; endif; else: echo "" ;endif; ?>
	</div>
</div>


<script src="http://cdn.leju.com/stat_leju/js/Controls/stat.js"></script>
<script>
<?php
$title = htmlentities($info['title']); $url = url('show', array($info['id']), 'pc', 'baike'); $sysucc = strtoupper( md5( md5($title.$url.'leju.com').'leju.com') ); $catepath = explode('-', trim($info['catepath'])); ?>
var stat_data = {
	"default":{
		"rank":"1",
		"click":"1",
		"plat_key":"pc",
		"unique_id":"<?php echo ($info["id"]); ?>",
		"cate_id":"<?php echo ($info["cateid"]); ?>",
		"level1":"<?php echo ($catepath[1]); ?>",
		"level2":"<?php echo ($catepath[2]); ?>",
		"city_en":"<?php echo ($city["en"]); ?>",
		"title":"<?php echo ($title); ?>",
		"url":"<?php echo ($url); ?>",
		"sysucc":"<?php echo ($sysucc); ?>"
	},
	"app_key":"cdf8101c8230bfdaf62c9fff0224579d"
}
if (document.all){window.attachEvent('onload',function(){stat_xtx('default')})}//IE
 else{window.addEventListener('load',function(){stat_xtx('default')},false);} //FireFox
</script>


	
<script type="text/javascript">
    var city = '<?php echo ($city["stat"]); ?>';
    var level1_page = '<?php echo ($level1_page); ?>';
    var level2_page = '<?php echo ($level2_page); ?>';
    var level3_page = '<?php echo ($level3_page); ?>';
    var custom_id = '<?php echo ($custom_id); ?>';
    var news_origin = '<?php echo ($news_origin); ?>';
    var news_source = '<?php echo ($news_source); ?>';
</script>
<script type="text/javascript" src="http://m.leju.com/resources/scripts/gather.pc.source.js"></script>
<!-- 乐居统一标准 页尾 -->
<?php echo ($common_tpl["footer"]); ?>


	<script type="text/javascript" src="http://cdn.leju.com/encypc/js/encypc.js?r"></script>
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