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
	
<!-- 跟随导航条 -->
<div class="z_fixed_menu none">
	<div class="inner" id="menu">
		<?php if(is_array($D["kblist"])): $i = 0; $__LIST__ = $D["kblist"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ls): $mod = ($i % 2 );++$i; if(!empty($ls["list"])): ?><a data-menuanchor="page<?php echo ($i); ?>" href="#page<?php echo ($i); ?>" <?php if(($i) == "1"): ?>class="cur"<?php endif; ?>><i class="line"></i><?php echo ($ls["name"]); ?></a><?php endif; endforeach; endif; else: echo "" ;endif; ?>
	</div>
</div>
<div class="ty_content clearfix">
	<div class="z_main_pic">
		<div class="inner clearfix">
			<?php if(!empty($D["rcmdlist"])): if(is_array($D["rcmdlist"])): $i = 0; $__LIST__ = $D["rcmdlist"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$rcmd): $mod = ($i % 2 );++$i; if(($i) == "1"): ?><a target="_blank" href="<?php echo url('show', array('id'=>$rcmd['_id']), 'pc', 'baike');?>#wt_source=pc_fcbk_pic" class="pic1"><img src="<?php if(!empty($rcmd["_origin"]["rcmd_cover"])): echo (changeImageSize($rcmd["_origin"]["rcmd_cover"], 656, 492)); else: echo (changeImageSize($rcmd["_origin"]["cover"], 656, 492)); endif; ?>" alt="<?php echo (str_replace('"', '&quot;', $rcmd["_title"])); ?>" width="656" height="492"><span class="mask"></span><span class="tit"><?php if(!empty($rcmd["_origin"]["rcmd"]["title"])): echo ($rcmd["_origin"]["rcmd"]["title"]); else: echo ($rcmd["_title"]); endif; ?></span></a><?php endif; ?>
					<?php if(($i) == "2"): ?><a target="_blank" href="<?php echo url('show', array('id'=>$rcmd['_id']), 'pc', 'baike');?>#wt_source=pc_fcbk_pic" class="pic2"><img src="<?php if(!empty($rcmd["_origin"]["rcmd_cover"])): echo (changeImageSize($rcmd["_origin"]["rcmd_cover"], 262, 196)); else: echo (changeImageSize($rcmd["_origin"]["cover"], 262, 196)); endif; ?>" alt="<?php echo (str_replace('"', '&quot;', $rcmd["_title"])); ?>" width="262" height="196"><span class="mask"></span><span class="tit"><?php if(!empty($rcmd["_origin"]["rcmd"]["title"])): echo ($rcmd["_origin"]["rcmd"]["title"]); else: echo ($rcmd["_title"]); endif; ?></span></a><?php endif; ?>
					<?php if(($i) == "3"): ?><a target="_blank" href="<?php echo url('show', array('id'=>$rcmd['_id']), 'pc', 'baike');?>#wt_source=pc_fcbk_pic" class="pic2"><img src="<?php if(!empty($rcmd["_origin"]["rcmd_cover"])): echo (changeImageSize($rcmd["_origin"]["rcmd_cover"], 262, 196)); else: echo (changeImageSize($rcmd["_origin"]["cover"], 262, 196)); endif; ?>" alt="<?php echo (str_replace('"', '&quot;', $rcmd["_title"])); ?>" width="262" height="196"><span class="mask"></span><span class="tit"><?php if(!empty($rcmd["_origin"]["rcmd"]["title"])): echo ($rcmd["_origin"]["rcmd"]["title"]); else: echo ($rcmd["_title"]); endif; ?></span></a><?php endif; ?>
					<?php if(($i) == "4"): ?><a target="_blank" href="<?php echo url('show', array('id'=>$rcmd['_id']), 'pc', 'baike');?>#wt_source=pc_fcbk_pic" class="pic3"><img src="<?php if(!empty($rcmd["_origin"]["rcmd_cover"])): echo (changeImageSize($rcmd["_origin"]["rcmd_cover"], 534, 286)); else: echo (changeImageSize($rcmd["_origin"]["cover"], 534, 286)); endif; ?>" alt="<?php echo (str_replace('"', '&quot;', $rcmd["_title"])); ?>" width="534" height="286"><span class="mask"></span><span class="tit"><?php if(!empty($rcmd["_origin"]["rcmd"]["title"])): echo ($rcmd["_origin"]["rcmd"]["title"]); else: echo ($rcmd["_title"]); endif; ?></span></a><?php endif; endforeach; endif; else: echo "" ;endif; endif; ?>
		</div>
	</div>
	<div class="z_main_con clearfix">
		<div class="z_main_l" id="fullpage">
		<?php if(!empty($D["kblist"])): if(is_array($D["kblist"])): $lv2 = 0; $__LIST__ = $D["kblist"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($lv2 % 2 );++$lv2; if(!empty($item["list"])): ?><div class="section">
				<h2 class="ty_tit">
					<i></i><span><?php echo ($item["name"]); ?></span>
					<?php if(is_array($item["son"])): $nav3 = 0; $__LIST__ = $item["son"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$i): $mod = ($nav3 % 2 );++$nav3; if(!empty($i["list"])): ?><em class="<?php if(($nav3) > "5"): ?>none<?php endif; ?>"><a target="_blank" href="<?php echo url('cate', array('id'=>$i['id'], 'page'=>1), 'pc', 'baike');?>#wt_source=pc_fcbk_list"><?php echo ($i["name"]); ?></a></em><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					<a target="_blank" href="<?php echo url('cate', array('id'=>$item['id'], 'page'=>1), 'pc', 'baike');?>#wt_source=pc_fcbk_more">更多<i></i></a>
				</h2>
				<!-- level 2 -->
				<div class="ty_list" data-cid="<?php echo ($item['id']); ?>">
					<ul>
						<?php if(!empty($item["list"])): if(is_array($item["list"])): $i = 0; $__LIST__ = $item["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$i): $mod = ($i % 2 );++$i;?><li>
							<a class="pic" href="<?php echo url('show', array('id'=>$i['id']), 'pc', 'baike');?>#wt_source=pc_fcbk_tp"><img src="<?php echo (changeImageSize($i["cover"], 208, 156)); ?>" alt="<?php echo (str_replace('"', '&quot;', $i["title"])); ?>"></a>
							<div class="z_r">
							<h3><a target="_blank" href="<?php echo url('show', array('id'=>$i['id']), 'pc', 'baike');?>#wt_source=pc_fcbk_bt"><?php echo ($i["title"]); ?></a></h3>
							<p class="pc"><?php echo ($i["content"]); ?></p>
							<p class="pa">
								<?php if(is_array($i["tagsinfo"])): foreach($i["tagsinfo"] as $key=>$t): ?><a target="_blank" href="<?php echo url('agg', array('tag'=>$t['id'], 'id'=>$cateid, 'page'=>1), 'pc', 'baike');?>#wt_source=pc_fcbk_bq"><?php echo ($t["name"]); ?></a><?php endforeach; endif; ?>
							</p>
							</div>
						</li><?php endforeach; endif; else: echo "" ;endif; endif; ?>
					</ul>
				</div>
				<!-- level 2 end -->
				<!--level3-->
				<?php if(!empty($item["son"])): if(is_array($item["son"])): $i = 0; $__LIST__ = $item["son"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ii): $mod = ($i % 2 );++$i; if(!empty($ii["list"])): ?><div class="ty_list none ty_lists" data-cid="<?php echo ($ii["id"]); ?>">
					<ul>
					<?php if(is_array($ii["list"])): foreach($ii["list"] as $kv3=>$iii): ?><li>
							<a class="pic" target="_blank" href="<?php echo url('show', array('id'=>$iii['id']), 'pc', 'baike');?>#wt_source=pc_fcbk_tp"><img src="<?php echo (changeImageSize($iii["cover"], 208, 156)); ?>" alt="<?php echo (str_replace('"', '&quot;', $iii["title"])); ?>"></a>
							<div class="z_r">
							<h3><a target="_blank" href="<?php echo url('show', array('id'=>$iii['id']), 'pc', 'baike');?>#wt_source=pc_fcbk_bt"><?php echo ($iii["title"]); ?></a></h3>
							<p class="pc"><?php echo ($iii["content"]); ?></p>
							<p class="pa">
								<?php if(is_array($iii["tagsinfo"])): foreach($iii["tagsinfo"] as $key=>$t): ?><a target="_blank" href="<?php echo url('agg', array('tag'=>$t['id'], 'id'=>$cateid, 'page'=>1), 'pc', 'baike');?>#wt_source=pc_fcbk_bq"><?php echo ($t["name"]); ?></a><?php endforeach; endif; ?>
							</p>
							</div>
						</li><?php endforeach; endif; ?>
					</ul>
				</div><?php endif; endforeach; endif; else: echo "" ;endif; endif; ?>
				<!--level3-->
			</div><?php endif; endforeach; endif; else: echo "" ;endif; endif; ?>
		</div>
		<div class="b_right">
			<?php if(!empty($D["hottag"])): ?><h2>热门百科词条<i></i><span>专业术语不懂问词条</span></h2>
				<div class="labelBox">
					<div class="labels">
						<?php if(is_array($D["hottag"])): foreach($D["hottag"] as $key=>$h): ?><a target="_blank" href="<?php echo url('show', array($h['id'], $h['cateid']), 'pc', 'wiki'); ?>#wt_source=pc_fcbk_rmct"><?php echo ($h["title"]); if($h["hot"] > 0): ?><i class="up"></i><?php endif; if($h["hot"] < 0): ?><i class="down"></i><?php endif; ?></a><?php endforeach; endif; ?>
					</div>
					<a target="_blank" href="<?php echo url('index', array(), 'pc', 'wiki'); ?>#wt_source=pc_fcbk_qbfcct" class="more">全部房产词条</a>
				</div><?php endif; ?>
				<div class="hotbk">
					<?php if(!empty($D["rank"])): ?><h2>热门百科知识<i></i></h2>
                <ul class="b_list">
                    <?php if(is_array($D["rank"])): $i = 0; $__LIST__ = $D["rank"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i; if(($i) < "11"): ?><li><a target="_blank" href="<?php echo ($r["url"]); ?>#wt_source=pc_fcbk_rmzx"><em <?php if(($i) < "4"): ?>class="i01"<?php endif; ?>><?php echo ($i); ?></em><?php echo ($r["title"]); ?></a></li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                </ul><?php endif; ?>
				</div>
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

<!-- 乐居统一标准 友情链接 -->
<?php echo ($common_tpl["links"]); ?>

	
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