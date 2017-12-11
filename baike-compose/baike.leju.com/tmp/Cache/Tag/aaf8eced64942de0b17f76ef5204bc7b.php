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

<div class="wtbaike_auto">
	<ul class="wtbaike_fc">
		<li><a href="<?php echo url('index', array(), 'pc', 'wiki'); ?>" target="_blank">房产词条&ensp;&ensp;></a></li>
		<li><a href="<?php echo url('listall', array(1), 'pc', 'wiki'); ?>" target="_blank">词条列表&ensp;&ensp;></a></li>
		<li><a class="wtbaike_active"><?php echo ($detail["title"]); ?></a></li>
	</ul>
	<div class="wtbaike_content">
		<div class="wtbaike_content_lf">
			<?php if ( $cateid==1 ) { ?>
			<!-- 企业/公司词条 -->
			<div class="wtbaikedljttit"><h1><?php echo ($detail["title"]); ?></h1></div>
			<?php } ?>
			<?php if ( $cateid==2 ) { ?>
			<!-- 人物词条 -->
			<div class="wtbaikedljttit"><h1><?php echo ($detail["title"]); ?></h1>
			<?php
 $position = trim($detail['basic']['position']); if ( $position!='' ) { ?>
			<span>(<?php echo ($detail["basic"]["position"]); ?>)</span>
			<?php } ?>
			</div>
			<?php } ?>

			<?php echo ($detail["summary"]); ?>

			<?php
 $html = []; $show = false; if ( count($detail['basic'])>0 ) { array_push($html, '<!-- 基本信息 -->'); array_push($html, '<h2>基本信息</h2>'); array_push($html, '<ul class="wtbaike_li">'); $i = 0; foreach ( $dict_basic as $member => $info ) { if ( trim($detail['basic'][$member])!='' ) { $member_name = $info['title']; $member_value = $detail['basic'][$member]; if ( isset($info['show']) ) { switch ( $info['show'] ) { case 'showdate': $member_value = date('Y-m-d', $member_value); break; default: } } $show = true; if ( isset($info['verify_len30']) && $info['verify_len30']==true ) { array_push($html, '</ul><ul class="wtbaike_li">'); $i = 0; } else { $i += 1; } array_push($html, '<li><span class="wtzb" data-inx="'.$i.'">'.$member_name.'</span><span>'.$member_value.'</span></li>'); if ( $i%3==0 ) { $i = 0; array_push($html, '</ul><ul class="wtbaike_li">'); } } } array_push($html, '</ul>'); } if ( $show ) { $html = implode('', $html); $html = str_replace('<ul class="wtbaike_li"></ul>', '', $html); echo $html; } ?>

			<?php echo ($detail["content"]); ?>

			<?php if ( $detail['rel']['news'] ) { ?>
			<!--相关新闻-->
			<div class="wtaike_jbxx wtbaiketymg">
				<div class="wtbaike_xgxw"><span class="wtlh">相关新闻</span><i></i></div>
				<ul class="wtbaike_xwlb">
				<?php
 $_rel = $stats['_rel']['news'][$cateid]; foreach ( $detail['rel']['news'] as $i => $news ) { if ( trim($news['picurl'])!='' ) { ?>
					<li>
						<div class="wtxwimg"><a href="<?php echo ($news["url"]); echo ($_rel); ?>" target="_blank"><img src="<?php echo ($news["picurl"]); ?>" alt="" /></a></div>
						<div class="wtxwcontent">
							<a href="<?php echo ($news["url"]); echo ($_rel); ?>" target="_blank" class="wtconttitle"><?php echo ($news["title"]); ?></a>
							<p class="wtlpp"><?php echo ($news["zhaiyao"]); ?></p>
							<?php if ( $news['tagsinfo'] ) { ?>
							<div class="wtcontlb">
								<?php foreach ( $news['tagsinfo'] as $_t => $tag ) { ?>
								<span><?php echo ($tag["name"]); ?></span>
								<?php } ?>
							</div>
							<?php } ?>
							<div class="wtcontbot">
								<span class="wtbaikelj">乐居</span>
								<span><?php echo (date("Y-m-d H:i:s", $news["createtime"])); ?></span>
							</div>
						</div>
					</li>
				<?php
 } else { ?>
					<li class="wtbon">
						<div class="wtxwcontent wtxwcontentone">
							<a href="<?php echo ($news["url"]); echo ($_rel); ?>" target="_blank" class="wtconttitle"><?php echo ($news["title"]); ?></a>
							<p class="wtlpp"><?php echo ($news["zhaiyao"]); ?></p>
							<?php if ( $news['tagsinfo'] ) { ?>
							<div class="wtcontlb">
							<?php
 foreach ( $news['tagsinfo'] as $_t => $tag ) { ?>
							<span><?php echo ($tag["name"]); ?></span>
							<?php } ?>
							</div>
							<?php } ?>
							<div class="wtcontbot">
								<span class="wtbaikelj">乐居</span>
								<span><?php echo (date("Y-m-d H:i:s", $news["createtime"])); ?></span>
							</div>
						</div>
					</li>
				<?php
 } } ?>
				</ul>
			</div>
			<?php } ?>
		</div>
		<div class="wtbaike_content_rgt">
		<?php
 $cover = trim($detail['cover']); $album = false; if ( trim($detail['album']['cover']['pc'])!='' ) { $cover = trim($detail['album']['cover']['pc']); $album = true; } $_rel = $stats['_rel']['album'][$cateid]; ?>
			<div class="wtbaike_rgt_tc">
				<div class="wtbaike_rgt_tcimg">
				<?php if ($album==true) { ?><a href="/tag/album-<?php echo ($detail["id"]); ?>.html<?php echo ($_rel); ?>" target="_blank"><img src="<?php echo ($cover); ?>"></a><?php } else { ?><img src="<?php echo ($cover); ?>"><?php } ?>
				</div>
				<div class="wtbaike_rgt_tcbotm"><i></i><span><?php
 $album_title = trim($detail['album']['title'])!='' ? trim($detail['album']['title']) : $detail['title'].'图册'; echo $album_title; ?></span></div>
			</div>
			<div class="wtbaike_rgt_zlly">
			<?php
 $_rel = ''; if ( $detail['src_type']==2 ) { $_rel = $stats['_rel']['hudong'][$cateid]; ?>
				<h2 class="wtzla">资料来源</h2>
				<div class="wthdbk">
					<a href="<?php echo ($_rel); ?>" target="_blank"><img src="images/wtbaike09.jpg" alt="" /></a>
				</div>
			<?php } ?>
			<?php
 $_rel = $stats['_rel']['companies'][$cateid]; if ( !empty($detail['rel']['companies']) ) { ?>
				<div class="wtbaike_rgt_xggs">
					<div class="wtxggxtit"><h2>相关公司</h2><i></i></div>
					<ul class="wtxggxul">
					<?php
 foreach ( $detail['rel']['companies'] as $id => $rel ) { $url = url('show', array($rel['id'], 1), 'pc', 'wiki'); ?>
						<li>
							<div class="wtxggxul_img"><a href="<?php echo ($url); echo ($_rel); ?>" target="_blank"><img src="<?php echo ($rel["cover"]); ?>" alt="<?php echo ($rel["title"]); ?>" /></a></div>
							<a href="<?php echo ($url); ?>" class="wtbaikexm"><?php echo ($rel["title"]); ?></a>
						</li>
					<?php } ?>
					</ul>
				</div>
			<?php } ?>
			<?php
 $_rel = $stats['_rel']['figures'][$cateid]; if ( !empty($detail['rel']['figures']) ) { ?>
				<div class="wtbaike_rgt_xggs">
					<div class="wtxggxtit"><h2>相关人物</h2><i></i></div>
					<ul class="wtxggxul">
					<?php
 foreach ( $detail['rel']['figures'] as $id => $rel ) { $url = url('show', array($rel['id'], 2), 'pc', 'wiki'); ?>
						<li>
							<div class="wtxggxul_img"><a href="<?php echo ($url); echo ($_rel); ?>" target="_blank"><img src="<?php echo ($rel["cover"]); ?>" alt="<?php echo ($rel["title"]); ?>" /></a></div>
							<a href="<?php echo ($url); ?>" class="wtbaikexm"><?php echo ($rel["title"]); ?></a>
						</li>
					<?php } ?>
					</ul>
				</div>
			<?php } ?>
			</div>
			<div class="wtbaikewb" style="display:none"></div>
		</div>
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
<script id="sidetpl" type="text/x-dot-template">
	<div class="wtbaikergtmenu">
				<i class="wtmenushangtu"></i>
				<i class="wtmenuxiatu"></i>
				<div class="wtull js_ul mCustomScrollbar">
					<ul class="wtbaikergtmenu_ul">
					{{for(var i=0;i<it.length;i++){ }}
						<li class="sidebar_li">
							<div class="wtmenubt">
								 <span class="wtmenunumber">{{=(i+1)}}</span>
								<a href="#{{=it[i]['point']}}" class="title_point"><span  class="wtmenucon">{{=it[i]['value']}}</span></a>
							</div>
							<div class="wtxulieone"><i></i></div>
							{{ if ( it[i].child.length>0 ) { var childs = it[i].child; }}
								<ul class="wtyijimenu">
					                {{for(var m=0,mlength=childs.length;m<mlength;m++){ }}
									<li>
										<div class="wtmenubt">
											<span class="wtnumebcl">{{=(i+1)}}.{{=m+1}}</span>
											<a href="#{{=childs[m].point}}"><span class="wtmenuconone">{{=childs[m].value}}</span></a>
										</div>
									</li>
					                {{ } }}
								</ul>
			                {{ } }}

						</li>
					{{ } }}
					</ul>
				</div>
				<div class="wtbaikerightbod" style="display:none;"></div>
			</div>
			<!--返回顶部-->
			<a href="javascript:;" class="wtbacktop js_backtop" title="返回顶部"></a>
</script>
<script type="text/javascript">
var city = 'quanguo';
var level1_page = '<?php echo ($stats["level1"]); ?>';
var level2_page = '<?php echo ($stats["level2"]); ?>';
var level3_page = '<?php echo ($stats["level3"]); ?>';
var custom_id = '<?php echo ($stats["custom_id"]); ?>';
var news_source='<?php echo ($stats["news_source"]); ?>';
</script>
<script type="text/javascript" src="http://cdn.leju.com/lejuTj/gather.pc.source.js"></script>

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