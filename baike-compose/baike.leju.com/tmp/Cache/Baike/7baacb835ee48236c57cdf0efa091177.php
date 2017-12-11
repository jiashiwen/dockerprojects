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

	<div class="zDetail_con">
		<h1><?php echo ($info["title"]); ?></h1>
		<h3><?php echo ($info["ctime"]); ?> <?php echo ($info["editor"]); ?></h3>

		<div class="artical">
			<!--
			<?php if(!empty($info["cover"])): ?><p class="pic"><img src="<?php echo (changeImageSize($info["cover"],750,340)); ?>" alt="<?php echo ($info["title"]); ?>"></p><?php endif; ?>
			-->
			<?php echo ($info["content"]); ?>
		</div>

		<div class="show_more">
			<a href="#">展开全文<i class="arrow_down"></i></a>
		</div>
	</div>

	<!-- 相关资讯 -->
	<?php if(!empty($info["rel_news"])): ?><div class="l_box">
		<h2 class="b_title">相关资讯<a href="<?php echo ($more["news"]); echo ($kd_info_xgzx); ?>">更多</a></h2>
		<div class="l_list02">
			<ul>
			<?php if(is_array($info["rel_news"])): foreach($info["rel_news"] as $key=>$n): ?><li>
					<a href="<?php echo ($n["m_url"]); echo ($kd_info_xgzx); ?>">
						<h3><?php echo ($n["title"]); ?></h3>
						<div class="l_tips"></div>
						<div class="l_infoBox clearfix">
							<span class="fl pl0"><?php if(!empty($n["media"])): echo ($n["media"]); else: endif; ?></span>
							<span class="fl"><?php echo (date("m-d",$n["createtime"])); ?></span>
						</div>
					</a>
				</li><?php endforeach; endif; ?>
			</ul>
		</div>
	</div><?php endif; ?>

	<!-- 相关楼盘 -->
	<?php if(!empty($info["rel_house"])): ?><div class="zLp_wrap">
			<h2 class="b_title">相关楼盘<a href="<?php echo ($more["house"]); echo ($kd_info_xglp); ?>">更多</a></h2>
			<ul class="zLp_list">
				<?php if(is_array($info["rel_house"])): foreach($info["rel_house"] as $key=>$h): ?><li>
					<a href="<?php echo ($h["m_url"]); echo ($kd_info_xglp); ?>">
						<div class="pic"><img src="<?php echo (changeImageSize($h["pic_s320"], 133, 100)); ?>" alt="<?php echo ($h["name"]); ?>"></div>
						<h3><?php echo ($h["name"]); ?></h3>
						<p class="price">均价<?php echo ($h["price_display"]); ?></p>
						<!--p class="tip">#楼盘优惠#</p-->
					</a>
					<a href="tel:<?php echo ($h["salephone"]); ?>" class="tel" gather="{event:'house_call',event_name:'新房拨打电话',city:'<?php echo ($h["city"]); ?>',level1_page:'kd',level2_page:'kd_info',param1:'',param2:'<?php echo ($h["hid"]); ?>',param3:'kd_info_xglp',param4:'<?php echo ($h["salephone"]); ?>'}"></a>
				</li><?php endforeach; endif; ?>
			</ul>
		</div><?php endif; ?>
	<a class="y_top1" ></a>
	<a class="y_top2" ></a>
	<!-- 大纲 -->
	<div class="b_wrapper01 none toc">
		<div class="b_topBox01">
			词条内容大纲
		</div>
		<ul class="b_list01">
			<li><a href="#">基本介绍</a></li>
			<li><a href="#">扩展阅读</a></li>
			<li class="cur"><a href="#">电商相关</a></li>
			<li><a href="#">房价趋势</a></li>
			<li><a href="#">在售状态</a></li>
		</ul>
	</div>
</div>
<div class="y_overlay share_layer none">
	<a class="y_off" ></a>
	<img class="y_stit" src="//<?php echo ($_SERVER['PS_URL']); ?>/images/y_share.png" height="26" width="519" alt="">
	<p class="y_sp1 mr64">您可以通过浏览器的分享按钮，将这篇经验分享到朋友圈</p>
	<p class="y_sp1 mr20">您也可以复制一下链接，打开朋友圈后进行分享</p>	
	<p class="y_sp1 co01">https://www.zhihu.com/question/31140769</p>
	<ul class="y_share">
		<li>
			<a class="weibo" target="_blank"><i class="y_ic01"></i>
			<p>新浪微博</p>
			</a>
		</li>
		<li>
			<a class="qzone" target="_blank">
			<i class="y_ic02"></i>
			<p>QQ空间</p>
			</a>
		</li>
	</ul>
</div>

<script src="http://cdn.leju.com/stat_leju/js/Controls/stat.js"></script>
<script>
<?php
$title = htmlentities($info['title']); $url = url('show', array($info['id']), 'touch', 'baike'); $sysucc = strtoupper( md5( md5($title.$url.'leju.com').'leju.com') ); $catepath = explode('-', trim($info['catepath'])); ?>
var stat_data = {
	"default":{
		"rank":"1",
		"click":"1",
		"plat_key":"touch",
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