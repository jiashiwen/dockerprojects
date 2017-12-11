<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, minimum-scale=1.0, user-scalable=no">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="format-detection" content="telephone=no">
	<title><?php echo ($pageinfo["seo_title"]); ?></title>
	<meta name="applicable-device" content="pc">
	<meta name="keywords" content="<?php echo ($pageinfo["keywords"]); ?>"/>
	<meta name="description" content="<?php echo ($pageinfo["description"]); ?>" />
	<link rel="canonical" href="<?php echo ($pageinfo["alt_url"]); ?>">
	<link rel="stylesheet" href="//cdn.leju.com/qawap/styles/styles.css">
	<script type="text/javascript">
	fnResize();
	var k = null;
	window.addEventListener("resize", function() {
		clearTimeout(k);
		k = setTimeout(fnResize, 300);
	}, false);
	function fnResize() {
		document.getElementsByTagName('html')[0].style.fontSize = (document.documentElement.clientWidth) / 15 + 'px';
	}
	</script>
</head>

<body>
	
	<?php if(($isapp) == "notapp"): ?><div class="header">
	<div class="h-t-warp">
		<i class="i-h-back"></i>
		<i class="i-h-title"></i>
		<i class="i-h-t-search"></i>
	</div>
	<div class="h-s-warp hide">
		<!--form id="search_form1" action="/search.html" method="get"-->
		<div class="h-s-i-warp">
			<i class="i-h-i-search"></i>
			<input type="text" class="h-s-input" name="k" placeholder="房产知识、专业术语、问题解疑一站解决" value="<?php echo ($keyword); ?>">
			<a href="javascript:;" class="h-s-clear"><i class="i-h-clear"></i></a>
		</div>
		<!--/form-->
		<a href="javascript:;" class="h-s-cancle">取消</a>
	</div>
</div><?php endif; ?>

<div class="section s-question">
	<div class="s-content">
		<ul class="s-c-article">
			<li data-qid="<?php echo ($question["id"]); ?>">
				<a href="#">
					<p><?php echo ($question["title"]); ?></p>
				</a>
				<div class="s-c-desc">
					<p><?php echo ($question["desc"]); ?></p>
					<a href="javascript:;" class="s-c-d-btn">
						展开所有
						<i class="i-s-a-show"></i>
					</a>
				</div>
				<?php if ( intval($question['i_images'])>0 && $question['data']['cover']!='' ) { ?>
				<div class="s-c-img" id="js-s-c-img">
					<img src="<?php echo ($question["data"]["cover"]); ?>">
				</div>
				<?php } ?>
				<div class="s-c-info">
					<div class="s-c-i-name"><?php if ( $question['anonymous']==1 ) { ?>乐居网友<?php } else { echo ((isset($question["usernick"]) && ($question["usernick"] !== ""))?($question["usernick"]):'乐居网友'); } ?></div>
					<div class="s-c-i-time"><?php echo (date('Y年m月d日', $question["ctime"])); ?></div>
					<?php if ( $attentioned ) { ?>
					<a href="javascript:;" data-qid="<?php echo ($question["id"]); ?>" class="s-c-i-best">已关注</a>
					<?php } else { ?>
					<a href="javascript:;" data-qid="<?php echo ($question["id"]); ?>" class="s-c-i-best">关注问题</a>
					<?php } ?>
				</div>
				<?php
 if ( $question['tagsinfo'] ) { $tag_ids = array(); foreach ( $question['tagsinfo'] as $i => $tag ) { $tagid = intval(trim($tag['id'])); if ( $tagid===0 ) { continue; } if ( !in_array($tagid, $tag_ids) ) { array_push($tag_ids, $tagid); } } ?>
				<input type="hidden" id="alltags" value="<?php echo implode(',', $tag_ids); ?>">
				<?php } ?>
			</li>
			<?php if ( $can_reply ) { ?>
			<li>
				<div class="s-c-share">
					<div class="s-c-s-txt1">
						<p>这个问题你会答吗？</p>
					</div>
					<div class="s-c-s-txt2">
						<p>如果你知道问题的答案，不如花几分钟帮助他！</p>
					</div>
					<div class="s-c-info">
						<a href="javascript:;" class="s-c-i-best">
							点击分享我的智慧
						</a>
					</div>
				</div>
			</li>
			<li class="hide">
				<textarea class="s-c-answer" placeholder="输入您答案"></textarea>
				<div class="s-c-a-num"><p>(0/200)</p></div>
				<a href="javascript:;" class="s-c-submit">点击提交</a>
			</li>
			<?php } ?>
		</ul>
	</div>
</div>
<div class="section s-detail <?php if ( !$best ) { echo 'hide'; } ?>">
	<div class="s-header"><p>最佳答案</p></div>
	<div class="s-content">
		<ul class="s-c-article">
		<?php if ( $best ) { ?>
			<li data-qid="<?php echo ($best["qid"]); ?>" data-aid="<?php echo ($best["id"]); ?>">
				<div class="s-c-desc">
					<p><?php echo ($best["reply"]); ?></p>
					<a href="javascript:;" class="s-c-d-btn hide">
						展开所有
						<i class="i-s-a-show"></i>
					</a>
				</div>
				<div class="s-c-info">
					<div class="s-c-i-name"><?php if ( $best['anonymous']==1 ) { ?>乐居网友<?php } else { echo ((isset($best["usernick"]) && ($best["usernick"] !== ""))?($best["usernick"]):'匿名网友'); } ?></div>
					<div class="s-c-i-time"><?php echo (date('Y-m-d', $best["ctime"])); ?></div>
					<?php
 if ( array_key_exists($best['id'], $goods) ) { $itemclass = 'i-s-a-checked'; } else { $itemclass = 'i-s-a-praise'; } ?>
					<a href="javascript:;" class="s-c-i-praise">
						<i class="<?php echo ($itemclass); ?>"></i><?php echo ($best["i_good"]); ?>
					</a>
				</div>
			</li>
		<?php } ?>
		</ul>
	</div>
</div>

<div class="section s-detail <?php if ( !$answers ) { echo 'hide'; } ?>">
	<div class="s-header"><p>最新答案</p></div>
	<div class="s-content">
		<ul class="s-c-article s-c-articlenew">
		<?php if ( $answers ) { ?>
		<?php foreach ( $answers as $i => $answer ) { ?>
			<li data-qid="<?php echo ($answer["qid"]); ?>" data-aid="<?php echo ($answer["id"]); ?>">
				<div class="s-c-desc">
					<p><?php echo ($answer["reply"]); ?></p>
					<a href="javascript:;" class="s-c-d-btn hide">
						展开所有
						<i class="i-s-a-show"></i>
					</a>
				</div>
				<div class="s-c-info">
					<div class="s-c-i-name"><?php if ( $answer['anonymous']==1 ) { ?>乐居网友<?php } else { echo ((isset($answer["usernick"]) && ($answer["usernick"] !== ""))?($answer["usernick"]):'乐居网友'); } ?></div>
					<div class="s-c-i-time"><?php echo (date('Y-m-d', $answer["ctime"])); ?></div>
					<?php
 if ( array_key_exists($answer['id'], $goods) ) { $itemclass = 'i-s-a-checked'; } else { $itemclass = 'i-s-a-praise'; } ?>
					<a href="javascript:;" class="s-c-i-praise">
						<i class="<?php echo ($itemclass); ?>"></i><?php echo ($answer["i_good"]); ?>
					</a>
					<?php
 $qz_uid = intval($question['userid']); $userid = intval($userid); if ( !$has_best && $qz_uid>0 && $userid==$qz_uid ) { ?>
					<a href="javascript:;" class="s-c-i-best">设为最佳答案</a>
					<?php } ?>
				</div>
			</li>
		<?php } ?>
		<?php } ?>
		</ul>
	</div>
</div>


<?php if ( $question['tagsinfo'] ) { ?>
<div class="section">
	<div class="s-header">
		<p>相关知识</p>
		<?php if ( count($tag_ids) > 1 ) { ?>
		<a href="javascript:;" class="s-h-btn">换一批</a>
		<?php } ?>
	</div>
	<div class="s-content">
		<ul class="s-c-relevant">
		<?php foreach ( $guess as $i => $item ) { if ( $i >=4 ) break;?>
			<li>
				<div class="s-c-r-img">
					<a href="<?php echo ($item["url"]); ?>#ln=ask_info_kd">
					<?php if ( $item['cover'] ) { ?>
					<img src="<?php echo (changeImageSize($item["cover"], 182, 137)); ?>">
					<?php } else { ?>
					<img src="//cdn.leju.com/encypc/images/temp/temp-2.jpg">
					<?php } ?>
					</a>
				</div>
				<div class="s-c-r-txt">
					<a href="<?php echo ($item["url"]); ?>#ln=ask_info_kd"><p><?php echo ($item["title"]); ?></p></a>
				</div>
			</li>
		<?php } ?>
		</ul>
	</div>
</div>
<?php } ?>

	<div class="float-wrap sugdiv1 hide">
	<div class="header">
		<div class="h-t-warp hide">
			<i class="i-h-back"></i>
			<i class="i-h-title"></i>
			<i class="i-h-t-search"></i>
		</div>
		<div class="h-s-warp">
			<div class="h-s-i-warp">
				<i class="i-h-i-search"></i>
				<input type="text" class="h-s-input suginp1" placeholder="房产知识、专业术语、问题解疑一站解决">
				<a href="javascript:;" class="h-s-clear"><i class="i-h-clear"></i></a>
			</div>
			<a href="javascript:;" class="h-s-cancle">取消</a>
		</div>
	</div>
	<div class="section s-history">
		<div class="s-header">
			<p>最近搜索</p>
			<a href="javascript:;" class="s-h-btn c-red">清除</a>
		</div>
		<div class="s-content">
			<ul class="s-c-l-list"></ul>
		</div>
	</div>
	<div class="section s-hot">
		<div class="s-header">
			<p>热门知识</p>
			<a href="javascript:;" class="s-h-btn">换一批</a>
		</div>
		<div class="s-content">
		<?php
 foreach ( $hot_kb as $i => $blocks ) { $display = $i > 0 ? ' hotkb hide' : ' hotkb'; ?>
			<ul class="s-c-article<?php echo ($display); ?>">
			<?php foreach ( $blocks as $j => $line ) { ?>
				<li>
					<a href="<?php echo ($line["url"]); ?>">
						<p><?php echo ($line["title"]); ?></p>
					</a>
				</li>
			<?php } ?>
			</ul>
		<?php  } ?>
			<ul class="s-c-article">
			</ul>
		</div>
	</div>
</div>
<div class="float-wrap sugdiv2 hide">
	<div class="header">
		<div class="h-t-warp hide">
			<i class="i-h-back"></i>
			<i class="i-h-title"></i>
			<i class="i-h-t-search"></i>
		</div>
		<div class="h-s-warp">
			<form id="search_form" action="/search.html" method="get">
			<div class="h-s-i-warp">
				<i class="i-h-i-search"></i>
				<input type="text" class="h-s-input suginp2" name="k" placeholder="房产知识、专业术语、问题解疑一站解决">
				<a href="javascript:;" class="h-s-clear"><i class="i-h-clear"></i></a>
			</div>
			<a href="javascript:;" class="h-s-cancle">取消</a>
			</form>
		</div>
	</div>
	<div class="section s-search">
		<div class="s-content">
			<ul class="s-c-article suglist"></ul>
		</div>
	</div>
	<a href="javascript:;" class="ask">提问</a>
</div>


<script src="//cdn.leju.com/qawap/js/qawap.js?r"></script>
<script src="//cdn.leju.com/sso/sso.js"></script>
<script type="text/javascript">
	var city = 'quanguo';
	var level1_page = '<?php echo ($statscode["level1_page"]); ?>';
	var level2_page = '<?php echo ($statscode["level2_page"]); ?>';
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
<script type="text/javascript" src="http://cdn.leju.com/lejuTj/gather.source.js"></script>
</body>
</html>