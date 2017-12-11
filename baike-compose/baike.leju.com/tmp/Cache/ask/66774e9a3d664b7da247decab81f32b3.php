<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<title><?php echo ($pageinfo["seo_title"]); ?></title>
	<meta name="keywords" content="<?php echo ($pageinfo["keywords"]); ?>"/>
	<meta name="description" content="<?php echo ($pageinfo["description"]); ?>" />
    <link rel="alternate" media="only screen and (max-width: 640px)" href="<?php echo ($pageinfo["alt_url"]); ?>">
	<link rel="stylesheet" href="//cdn.leju.com/qapc/styles/styles.css?20170718">
	<script type="text/javascript" src="//cdn.leju.com/encypc/js/fullPage/jquery-1.8.3.min.js"></script>
</head>
<body>
	<!-- 乐居统一标准 页头 -->
<?php echo ($common_tpl["header"]); ?>

	<div class="z_ask_search">
	<div class="inner">
		<h2 class="logo"><a href="/" title="乐居问答">乐居问答</a></h2>
		<h3>专业的房地产行业问答平台</h3>
		<div class="search_wrap">
			<div class="search">
				<form id="search_form" action="/search.html#wt_source=pc_qa_ss" method="get" target="_blank">
					<input type="text" placeholder="输入关键词，查找您想要的答案" class="inp" id="qa_inp" name="k" autocomplete="off" value="<?php echo ($keyword); ?>">
					<a href="javascript:;" title="搜索" class="btn"><i></i></a>
					<div class="search_ly none" id="qa_search_ly">
						<!-- ufoguan添加 class  qa_search_ly -->
							<ul class="qa_search_ul"></ul>
							<div class="all" id="qa_all"><a href="javascript:;">查看全部关于“二手房”的搜索结果></a></div>
					</div>
				</form>
			</div>
			<a href="<?php echo url('ask', array(), 'pc', 'ask'); ?>#wt_source=pc_qa_tw" title="我要提问" class="ask_btn">我要提问</a>
		</div>
	</div>
</div>


<link rel="stylesheet" href="//cdn.leju.com/encypc/js/swiper/swiper-3.4.2.min.css">
<script type="text/javascript" src="//cdn.leju.com/encypc/js/swiper/swiper-3.4.2.jquery.min.js"></script>

<div class="z_ask_con clearfix">
	<div class="z_ask_side">
		<!-- category tree start -->
		<div class="z_category">
		<?php
if ( $catetree ) { foreach ( $catetree as $lv1_cateid => $lv1_cate ) { $subcates = $lv1_cate['son']; if ( !empty($subcates) ) { ?>
	<div class="category">
		<h3><a href="<?php echo url('list', array($lv1_cateid), 'pc', 'ask'); ?>#wt_source=pc_qa_kd"  target="_blank" title="<?php echo ($lv1_cate["name"]); ?>"><i></i><?php echo ($lv1_cate["name"]); ?></a></h3>
		<ul class="clearfix">
	<?php
 foreach ( $subcates as $lv2_cateid => $lv2_cate ) { ?>
			<li><a href="<?php echo url('list', array($lv2_cateid), 'pc', 'ask'); ?>#wt_source=pc_qa_kd" target="_blank"><?php echo ($lv2_cate["name"]); ?></a></li>
	<?php
 } ?>
		</ul>
	</div>
	<?php
 } } } ?>

		</div>
		<!-- category tree end -->

		<div class="z_side_list">
			<h3 class="z_side_hd">待你解决<i></i></h3>
			<ul>
			<?php foreach ( $need_answer as $i => $question ) { $inx = $i+1; ?>
				<li><a href="<?php echo url('show', array($question['id']), 'pc', 'ask'); ?>#wt_source=pc_qa_ddbz"><?php echo ($question["title"]); ?></a></li>
			<?php } ?>
			</ul>
		</div>
	</div>

	<div class="z_ask_main">
		<div class="z_scroll">
			<div class="swiper-container">
		        <div class="swiper-wrapper">
					<?php foreach ( $focus as $i => $item ) { ?>
					<div class="swiper-slide"><a href="<?php echo url('show', array($item['id']), 'pc', 'ask'); ?>#wt_source=pc_qa_pic"><img src="<?php echo (changeImageSize($item["pic"], 296, 166)); ?>" alt=""><i class="mask"></i><span><em><?php echo ($item["title"]); ?></em></span></a></div>
					<?php } ?>
		        </div>
		        <!-- Add Pagination -->
		        <div class="swiper-pagination"></div>
		        <div class="swiper-button-next"></div>
		        <div class="swiper-button-prev"></div>
		    </div>
		</div>
		<div class="z_hot_topic">
			<h3 class="z_main_hd">热门话题<i></i></h3>
			<div class="list">
				<ul class="clearfix">
				<?php foreach ( $agg_tags['list'] as $i => $info ) { if ( $i>=6 ) break; ?>
					<li data-n="<?php echo ($i); ?>">
						<div class="hd clearfix"><h4><a href="<?php echo url('agg', array($info['id']), 'pc', 'ask'); ?>#wt_source=pc_qa_rmht" title="<?php echo ($info["name"]); ?>"><?php echo ($info["name"]); ?></a></h4><a href="<?php echo url('agg', array($info['id']), 'pc', 'ask'); ?>#wt_source=pc_qa_rmht" class="more"><?php echo ($info["c_questions"]); ?>个问题</a></div>
						<div class="links">
						<?php foreach ( $info['list'] as $ii => $q ) { ?> 
							<a href="<?php echo url('show', array($q['id']), 'pc', 'ask'); ?>"><?php echo ($q["title"]); ?></a>
						<?php } ?>
						</div>
					</li>
				<?php } ?>
				</ul>
			</div>
		</div>
		<div class="z_expert">
			<h3 class="z_main_hd">专家顾问<i></i></h3>
			<ul class="card clearfix">
			<?php foreach ( $professors as $id => $pro ) { ?>
				<li><img src="<?php echo ($pro["index"]); ?>" alt="专家 <?php echo ($pro["usernick"]); ?>" width="296"><a href="/ask/?pro=<?php echo ($id); ?>" class="z_ask_btn" title="向Ta提问">向Ta提问</a></li>
			<?php } ?>
			</ul>
		</div>
		<?php if ( $hot_answers ) { ?>
		<div class="z_care_list">
			<h3 class="z_main_hd">大家都关心<i></i></h3>
			<ul class="sAsk_list01 z_list01">
				<?php foreach ( $hot_answers['list'] as $qi => $q ) { ?>
	<li>
		<h3><a href="<?php echo ($q["url"]); ?>"><?php echo ($q["title"]); ?></a></h3>
		<p><?php echo ($q["desc"]); ?></p>
		<div class="clearfix">
			<div class="lab_box">
			<?php foreach ( $q['tagsinfo'] as $ti => $tag ) { ?>
			<a href="<?php echo ($tag["url"]); ?>"><?php echo ($tag["name"]); ?></a>
			<?php } ?>
			</div>
			<div class="info_box">
				<?php if ( $item['status']!=21 ) { ?>
				<span class="name"><?php echo ($q["last_answer"]["usernick"]); ?></span>
				<span>回答了问题</span>
				<span class="line">|</span>
				<?php } ?>
				<span><?php echo (formatQATimer($q["ctime"])); ?></span>
				<?php
 $dom_node = 0; $status = intval($q['status']); switch( $q['status'] ) { case 22: $dom_node = '<span class="state2"><i></i>待采纳</span>'; break; case 23: $dom_node = '<span class="state1"><i></i>已解决</span>'; break; case 21: default: $dom_node = '<a class="help" href="'.url('show', array($item['id']), 'pc', 'ask').'"><i></i>帮帮他</a>'; break; } echo $dom_node; ?>
			</div>
		</div>
	</li>
<?php } ?>

			</ul>
			<?php if ( $hot_answers['pager']['is_last']==0 ) { ?>
			<a class="sLoading" href="javascript:;" data-api="<?php echo ($hot_answers["pager"]["next_api_url"]); ?>" data-current="1" data-islast="<?php echo ($hot_answers["pager"]["is_last"]); ?>">
				<img src="//cdn.leju.com/qapc/images/HLloading.gif">加载更多
			</a>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</div>

	<!-- 乐居统一标准 页尾 -->
<?php echo ($common_tpl["footer"]); ?>

<script type="text/javascript">
	var city = '<?php echo ($city["en"]); ?>';
	var level1_page = '<?php echo ($statscode["level1_page"]); ?>';
	var level2_page = '<?php echo ($statscode["level2_page"]); ?>';
	var level3_page = '<?php echo ($statscode["level3_page"]); ?>';
	var custom_id = '<?php echo ($statscode["custom_id"]); ?>';
	var news_source='<?php echo ($statscode["news_source"]); ?>';
</script>
<script>
(function(){
	var bp = document.createElement('script');
	var curProtocol = window.location.protocol.split(':')[0];
	if (curProtocol === 'https'){
		bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
	} else {
		bp.src = 'http://push.zhanzhang.baidu.com/push.js';
	}
	var s = document.getElementsByTagName("script")[0];
	s.parentNode.insertBefore(bp, s);
})();
</script>
<script type="text/javascript" src="//cdn.leju.com/lejuTj/gather.pc.source.js"></script>
<script type="text/javascript" src="//cdn.leju.com/encyclopedia/js/Controls/template.js"></script>
<script type="text/javascript" src="//cdn.leju.com/qapc/js/fullPage/plugSuggest.js"></script>
<script type="text/javascript" src="//cdn.leju.com/qapc/js/qapc.js?20170718"></script>
<script type="text/javascript" src="//cdn.leju.com/sso/lj_lgbox.js?_=3.141592653"></script>
</body>
</html>