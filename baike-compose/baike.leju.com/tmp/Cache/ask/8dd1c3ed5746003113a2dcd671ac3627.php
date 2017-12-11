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
		<a href="/">问答首页</a>
		<?php
 if ( $catecrumbs ) { foreach ( $catecrumbs as $level => $cate ) { ?>
		<span>\</span>
		<a href="<?php echo url('list', array($cate['id']), 'pc', 'ask'); ?>"><?php echo ($cate["name"]); ?></a>
		<?php
 } } ?>
		<span>\</span>
		<a class="cur"><?php echo ($question["title"]); ?></a>
	</div>
	<div class="clearfix">
		<div class="sLbox ty_con">
			<h2 class="ty_tit"><?php echo ($question["title"]); ?></h2>
			<p class="ty_detial">
				<span class="name"><?php echo formatUsernick($question['usernick'], $question['anonymous']); ?></span>
				<span class="date"><?php echo (formatQATimer($question["ctime"])); ?></span>
				<span class="tab"><?php
 $tag_ids = array(); if ( $question['tagsinfo'] ) { foreach ( $question['tagsinfo'] as $i => $tag ) { $tagid = intval(trim($tag['id'])); if ( $tagid===0 ) { continue; } if ( !in_array($tagid, $tag_ids) ) { array_push($tag_ids, $tagid); } ?>
					<a href="<?php echo url('agg', array($tagid), 'pc', 'ask'); ?>"><?php echo ($tag["name"]); ?></a>
					<?php
 } } ?><input type="hidden" id="alltags" value="<?php echo implode(',', $tag_ids); ?>"></span>
				<?php
 if ( $question['userid']!=$userid ) { if ( $attentioned ) { ?>
					<span class="focus on" data-qid="<?php echo ($question["id"]); ?>">已关注</span>
				<?php } else { ?>
					<a class="focus" href="javascript:;" data-qid="<?php echo ($question["id"]); ?>">关注问题</a>
				<?php
 } } ?>
			</p>
			<p class="ty_text"><?php echo ($question["desc"]); ?></p>
			<?php if ( intval($question['i_images'])>0 && trim($question['data']['cover'])!='' ) { ?>
			<img class="bigimg" style="height:100px" src="<?php echo ($question["data"]["cover"]); ?>" alt="">
			<?php } ?>

			<?php
 if ( $can_reply ) { ?>
			<div class="ty_answer">
				 <i class="faq"></i>
				 <p class="p1">这个问题你会回答吗？</p>
				 <p class="p2">如果你知道问题的答案，不如花几分钟帮助他~</p>
				 <em class="answer_btn"><i></i>点击回答</em>
			</div>
			<div class="ty_answer_area none">
				<i class="top"></i>
				<textarea id="qa_textarea" cols="30" rows="10" placeholder="输入您的答案"></textarea>
				<input class="submit" id="qa_submit" type="submit" value="提交">
			</div>
			<?php } ?>

			<?php
 if ( $best ) { ?>
			<div class="sTit tyTit">
				<i class="best"></i>最佳答案
				<span></span>
			</div>
			<div class="ty_answer_best" data-qid="<?php echo ($best["qid"]); ?>" data-aid="<?php echo ($best["id"]); ?>">
				<i class="yin"></i>
				<p class="p1"><?php echo ($best["reply"]); ?></p>
				<p class="detial"><span><?php echo formatUsernick($best['usernick'], $best['anonymous']); ?></span>发布于<?php echo (formatQATimer($best["ctime"])); ?></p>
				<?php if ( $professor ) { ?>
				<div class="person_card">
					<img src="<?php echo ($professor["detail"]); ?>" />
						<a class="ask" href="/ask/?id=<?php echo ($professor["id"]); ?>">向他提问</a>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
			<?php if ( !$best ) { ?>
			<div class="sTit tyTit none">
				<i class="best"></i>最佳答案
				<span></span>
			</div>
			<?php } ?>


			<?php
 if ( $answers ) { ?>
			<div class="sTit tyTit2">
				最新答案
				<span></span>
			</div>
			<ul class="ty_answer_newest">
			<?php foreach ( $answers as $i => $answer ) { ?>
				<li data-qid="<?php echo ($answer["qid"]); ?>" data-aid="<?php echo ($answer["id"]); ?>">
					<p class="text"><?php echo ($answer["reply"]); ?></p>
					<p class="nb">
						<span class="left"><em class="name"><?php echo formatUsernick($answer['usernick'], $answer['anonymous']); ?></em>回答了问题<em class="time"><?php echo (formatQATimer($answer["ctime"])); ?></em></span>
						<span class="right">
							<?php
 $qz_uid = intval($question['userid']); $userid = intval($userid); if ( !$has_best && $qz_uid>0 && $userid==$qz_uid ) { ?>
							<a class="set qa_best" href="javascript:;">设为最佳答案</a>
							<?php } ?>

							<?php
 if ( array_key_exists($answer['id'], $goods) ) { $itemclass = ' on'; } else { $itemclass = ''; } ?><span class="value<?php echo ($itemclass); ?> qa_good"><i></i>有用 <?php echo ($answer["i_good"]); ?></span>
						</span>
					</p>
				</li>
				<?php } ?>
			</ul>
			<?php } ?>
			<?php if ( !$answers ) { ?>
			<div class="sTit tyTit2 none">
				最新答案
				<span></span>
			</div>
			<ul class="ty_answer_newest none">
			</ul>
			<?php } ?>

			<?php
 if ( $question['tagsinfo'] ) { ?>
			<!-- 相关知识 -->
			<div class="sTit">
				相关知识
				<div class="fr">
				<?php if ( count($tag_ids) > 1 ) { ?>
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
		<?php
 if ( $relquestions ) { ?>
			<div class="sTit">
				相关问答
				<span></span>
			</div>
			<ul class="ty_rlist">
			<?php foreach ( $relquestions as $i => $question ) { $inx=$i+1; ?>
				<li>
					<a class="title" href="<?php echo url('show', array($question['id']), 'pc', 'ask'); ?>#wt_source=qa_info_xgwd" data-hits="<?php echo ($question["i_replies"]); ?>"><i class="question"></i><?php echo ($question["title"]); ?></a>
					<p class="text"><?php echo ($question["reply"]); ?></p>
				</li>
			<?php } ?>
			</ul>
		<?php } ?>			

			<div class="sTit">
				相关话题
				<span></span>
			</div>
			<ul class="ty_rlist2">
			<?php foreach ( $rel_tags as $i => $info ) { ?>
				<li>
					<p class="title"><a href="<?php echo url('agg', array($info['id']), 'pc', 'ask'); ?>" class="l"><?php echo ($info["name"]); ?></a><a class="r" href="<?php echo url('agg', array($info['id']), 'pc', 'ask'); ?>"><?php echo ($info["c_questions"]); ?>问题</a></p>
					<?php foreach ( $info['list'] as $ii => $q ) { ?>
					<a class="li" href="<?php echo url('show', array($q['id']), 'pc', 'ask'); ?>"><?php echo ($q["title"]); ?></a>
					<?php } ?>
				</li>
			<?php } ?>
			</ul>
		</div>
	</div>
</div>

<script src="http://cdn.leju.com/stat_leju/js/Controls/stat.js"></script>
<script>
<?php
$title = htmlentities($question['title']); $url = url('show', array($question['id']), 'pc', 'ask'); $sysucc = strtoupper( md5( md5($title.$url.'leju.com').'leju.com') ); ?>
var stat_data = {
	"default":{
		"rank":"1",
		"click":"1",
		"plat_key":"pc",
		"unique_id":"<?php echo ($question["id"]); ?>",
		"cate_id":"<?php echo ($question["cateid"]); ?>",
		"city_en":"<?php echo ($city["en"]); ?>",
		"title":"<?php echo ($title); ?>",
		"url":"<?php echo ($url); ?>",
		"sysucc":"<?php echo ($sysucc); ?>"
	},
	"app_key":"cdf8101c8230bfdaf62c9fff0224579d"
}
/*
if (document.all){window.attachEvent('onload',function(){stat_xtx('default')})}//IE
 else{window.addEventListener('load',function(){stat_xtx('default')},false);} //FireFox
*/
</script>

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