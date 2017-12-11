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
    <div class="ty_left">
        <div class="b_right ty_bright">
            <h2>百科导航<i></i></h2>
            <div class="ty_slid">
                <?php if(is_array($result["nav"]["son"])): $i = 0; $__LIST__ = $result["nav"]["son"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cc): $mod = ($i % 2 );++$i;?><h3><?php echo ($cc["name"]); ?><i></i></h3>
                    <p>
                        <?php if(is_array($cc["son"])): $i = 0; $__LIST__ = $cc["son"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ccc): $mod = ($i % 2 );++$i;?><a href="<?php echo url('cate', array('id'=>$ccc['id'], 'page'=>1), 'pc', 'baike');?>"><?php echo ($ccc["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
                    </p><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        </div>
    </div>
    <div class="ty_right">
        <h2 class="ty_tit">
            <i></i><span><?php echo ($binds["parent"]); ?></span>
        </h2>
        <div class="ty_list">
            <ul>
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li>
                    <a class="pic" href="<?php echo url('show', array('id'=>$item['id']), 'pc', 'baike');?>"><img src="<?php echo (changeImageSize($item["cover"], 208, 156)); ?>" alt=""></a>
                    <h3><a href="<?php echo url('show', array('id'=>$item['id']), 'pc', 'baike');?>"><?php echo ($item["title"]); ?></a></h3>
                    <p class="pc"><?php echo ($item["content"]); ?></p>
                    <p class="pa">
                        <?php if(is_array($item["tags"])): $i = 0; $__LIST__ = $item["tags"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$t): $mod = ($i % 2 );++$i;?><a href="<?php echo url('agg', array('tag'=>$t['id'], 'id'=>$cateid, 'page'=>1), 'pc', 'baike');?>"><?php echo ($t["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
                    </p>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
        <?php if(($result["maxpage"]) > "0"): ?><div class="ty_pages clearfix">
            <?php if(!empty($pager["prev"])): ?><a class="pre" href="<?php echo ($pager["prev"]); ?>"><&nbsp;&nbsp;上一页</a><?php endif; ?>
            <?php if(($pager["sp_before"]) == "true"): ?><em>...</em><?php endif; ?>
            <?php if(is_array($pager["list"])): foreach($pager["list"] as $k=>$vo): if(($pager["page"]) == $vo["num"]): ?><a class="fbtn on"><?php echo ($vo["num"]); ?></a>
                    <?php else: ?>
                    <a class="ebtn" href="<?php echo ($vo["url"]); ?>"><?php echo ($vo["num"]); ?></a><?php endif; endforeach; endif; ?>
            <?php if(($pager["sp_after"]) == "true"): ?><em>...</em><?php endif; ?>
            <?php if(!empty($pager["next"])): ?><a class="next" href="<?php echo ($pager["next"]); ?>">下一页&nbsp;&nbsp;></a><?php endif; ?>
            <span>共<?php echo ($result["maxpage"]); ?>页</span>
        </div><?php endif; ?>
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