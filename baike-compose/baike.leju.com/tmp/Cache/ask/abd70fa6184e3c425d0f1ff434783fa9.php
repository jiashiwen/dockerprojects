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

<div class="sWrap">
	<div class="sCrumbs">
		<a href="/">问答首页</a><span>\</span><a class="cur"><?php echo ($taginfo["name"]); ?></a>
	</div>
	<div class="clearfix">
		<div class="sLbox">
			<div class="sTit">
				<?php echo ($taginfo["name"]); ?>
				<div class="fr sort">
					<a href="javascript:;" data-order="zdfw">按浏览量</a>
					<u>|</u>
					<a href="javascript:;" data-order="zdhf">按回答数</a>
				</div>
				<span></span>
			</div>

			<ul class="sAsk_list01">
				<?php foreach ( $list as $i => $item ) { ?>
<li>
	<h3><a href="<?php
 echo url('show', array($item['id']), 'pc', 'ask'); if ( $_list_source_flag ) { echo '#wt_source=', $_list_source_flag; } ?>"><?php echo ($item["title"]); ?></a></h3>
	<p><?php echo ($item["desc"]); ?></p>
	<div class="clearfix">
		<div class="lab_box">
			<?php foreach ( $item['tagsinfo'] as $i => $tag ) { ?>
			<a href="<?php echo url('agg', array($tag['id']), 'pc', 'ask'); ?>#wt_source=qa_list_tag"><?php echo ($tag["name"]); ?></a>
			<?php } ?>
		</div>
		<div class="info_box">
			<?php if ( $item['status']!=21 ) { ?>
			<span class="name"><?php echo formatUsernick($item['last_answer']['usernick'], $item['last_answer']['anonymous']); ?></span>
			<span>回答了问题</span>
			<span class="line">|</span>
			<span><?php echo (formatQATimer($item["last_answer"]["ctime"])); ?></span>
			<?php } else { ?>
			<span class="name"><?php echo formatUsernick($item['usernick'], $item['anonymous']); ?></span>
			<span>提出了问题</span>
			<span class="line">|</span>
			<span><?php echo (formatQATimer($item["ctime"])); ?></span>
			<?php } ?>
			<?php
 $dom_node = 0; switch( $item['status'] ) { case 22: $dom_node = '<span class="state2"><i></i>待采纳</span>'; break; case 23: $dom_node = '<span class="state1"><i></i>已解决</span>'; break; case 21: default: $dom_node = '<a class="help" href="'.url('show', array($item['id']), 'pc', 'ask').'"><i></i>帮帮他</a>'; break; } echo $dom_node; ?>
		</div>
	</div>
</li>
<?php } ?>

			</ul>
			<?php if ( $pager['is_last']!=1 ) { $item_class = ' none'; } ?>
<a class="sLoading<?php echo ($item_class); ?>" href="javascript:;" data-api="<?php echo ($pager["next_api_url"]); ?>" data-current="<?php echo ($pager["page"]); ?>" data-islast="<?php echo ($pager["is_last"]); ?>">
	<img src="//cdn.leju.com/qapc/images/HLloading.gif" class="none">加载更多
</a>


			<?php
if ( $tagsinfo ) { $tag_ids = array_keys($tagsinfo); ?>
<input type="hidden" id="alltags" value="<?php echo implode(',', $tag_ids); ?>"></span>
<!-- 相关知识 -->
<div class="sTit">
	相关知识
	<div class="fr">
	<?php if ( count($tagsinfo) > 1 ) { ?>
		<a class="change" href="#wt_source=pc_qa_hyh"><i></i>换一换</a>
	<?php } ?>
	</div>
	<span></span>
</div>
<div class="ty_knowledge">
	<ul>
	<?php foreach ( $guess as $i => $item ) { ?>
		<li>
			<a href="<?php echo ($item["url"]); ?>#wt_source=qa_info_kd" target="_blank">
				<?php if ( $item['cover'] ) { ?>
				<img src="<?php echo (changeImageSize($item["cover"], 208, 158)); ?>">
				<?php } else { ?>
				<img src="//cdn.leju.com/qapc/images/temp/ty_temp1.png">
				<?php } ?>
				<span class="bar"></span>
				<h4><?php echo ($item["title"]); ?></h4>
			</a>
		</li>
	<?php } ?>
	</ul>
</div>
<?php } ?>

		</div>
		<div class="sRbox">
			<!-- category tree start -->
			<div class="z_category">
			<h3 class="z_side_hd mb30">问题分类<i></i></h3>
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

			<!-- 猜你喜欢 -->
			<div class="z_side_list">
	<h3 class="z_side_hd">猜您喜欢<i></i></h3>
	<ul>
	<?php foreach ( $latest_answers as $i => $question ) { ?>
		<li><a href="<?php echo url('show', array($question['id']), 'pc', 'ask'); ?>#wt_source=qa_list_cnxh"><?php echo ($question["title"]); ?></a></li>
	<?php } ?>
	</ul>
</div>

		</div>
	</div>
</div>
<script>
(function(){
    window.tags_config = {
        changehits: true, //true为允许转化
        exposehits: true, //是否允许曝光传值
        key: "5ed33f7008771c9d49e3716aeaeca581" //业务key
    }
})();
</script>
<script type="text/javascript" src="http://cdn.leju.com/tags-fe/prd/tags_stat.js"></script>

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