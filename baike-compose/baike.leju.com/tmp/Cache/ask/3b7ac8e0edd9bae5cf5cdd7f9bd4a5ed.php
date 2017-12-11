<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, minimal-ui">
	<meta name="format-detection" content="telephone=no" />
	<meta name="applicable-device" content="mobile">
	<title><?php echo ($pageinfo["seo_title"]); ?></title>
	<meta name="keywords" content="<?php echo ($pageinfo["keywords"]); ?>"/>
	<meta name="description" content="<?php echo ($pageinfo["description"]); ?>" />
	<script type="text/javascript" src="//res.leju.com/resources/app/touch/ask/js/index.js"></script>
	<link rel="stylesheet" href="//res.leju.com/resources/app/touch/ask/styles/styles.css">
	<script src="//cdn.leju.com/sso/sso.js"></script>
	<style type="text/css">
            .none {display: none!important; }
            .lMask {position: fixed; width: 100%; height: 100%;left: 0;top: 0;bottom: 0; right: 0; z-index: 100; background-color: #1a1d22;}
            .lMask .lMask_img {width: 100%;height: 88%;position: relative;overflow: auto;}
            .lMask .lMask_imgM {width: 100%;position: absolute; top: 0;bottom: 0;margin: auto;left: 0;right: 0;}
            .lMask .lMask_text {position: absolute;left: 0;bottom: 5%; width: 100%;font-size: 14px; color: #fff; padding: 0 1em;box-sizing: border-box;z-index: 99;}
	</style>
</head>
<body>
	<div class="dll_header">
		<a href="//m.news.leju.com/#ln=touch_ask_cjdh"><h2 class="dll_caijinglogo fl"></h2></a>
		<div class="y_txt">乐道问答</div>
		<div class="dll_functionbar clearfix">
		</div>
		<div class="dll_functionbar clearfix">
			<a href="<?php echo url('LDSearch', [''], 'touch', 'ask'); ?>#ln=touch_qiye_search"><i class="dll_icon18"></i></a>
			<?php if ( !$islogined ) { ?>
			<a href="//my.leju.com" data-status="0"><i class="y_log">登录</i></a>
			<?php } else { ?>
			<a href="javascript:;" data-status="1"><i class="y_log">退出</i></a>
			<?php } ?>
		</div>
	</div>
	<div class="y_box01">
		<m1 class="m1"><img src="<?php echo ($company["cover"]); ?>" height="120" width="120" alt="<?php echo ($company["title"]); ?>"></m1>
		<div class="m2">
			<h2><?php echo ($company["stname"]); ?></h2>
			<h3><?php echo ($stats["questions"]); ?>问题<em>丨</em><?php echo ($stats["answers"]); ?>回答</h3>
			<div class="y_toolBox">
				<a class="ic01 js_share" href="javascript:;">分享</a>
				<?php if ( $company['is_focus']==1 ) { ?>
				<a class="ic03 followBnt" id="follow_company" href="javascript:;" data-direct="0" data-id="<?php echo ($company["id"]); ?>" data-url="/ldapi/uCompany">已关注</a>
				<?php } else { ?>
				<a class="ic02 followBnt" id="follow_company" href="javascript:;" data-direct="1" data-id="<?php echo ($company["id"]); ?>" data-url="/ldapi/uCompany">关注</a>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="y_section01">
		<div class="y_tab01">
			<ul class="js_tab">
				<li class="on" data-type="latest">最新</li>
				<li data-type="essence">推荐</li>
			</ul>
		</div>

		<div class="y_dt <?php if ( !empty($latest) ) { echo 'none'; } ?>" id="noAskData">
			<img src="//res.leju.com/resources/app/touch/ask/images/wtudefined.png" >
			<h2 class="latest">该公司暂无问题，请发起提问。</h2>
			<h2 class="essence none">该公司暂无推荐问题，请发起提问。</h2>
		</div>

		<?php if ( !empty($latest) ) { ?>
		<div class="y_list01">
			<ul class="js_list">
			<?php
 foreach ( $latest as $i => $item ) { ?>
				<li>
					<h2><a href="<?php echo url('LDQuestion', [$item['id']], 'touch', 'ask'); ?>#ln=<?php echo ($item["_ln_ext"]); ?>"><?php
 if ( $item['essence']>0 ) { echo '<em>精华</em>'; } if ( $item['ontop']>0 ) { echo '<em>置顶</em>'; } echo ($item["title"]); ?></a></h2>
					<h3><?php echo ($item["desc"]); ?></h3>
					<?php if ( $item['i_images']>0 ) { ?>
					<dl class="y_imglist">
					<?php foreach ( $item['images'] as $i => $image ) { ?>
						<dd><img class="js_img" src="<?php echo ($image); ?>"></dd>
					<?php } ?>
					</dl>
					<?php } ?>
					<div class="y_userTell" data-uid="<?php echo ($item["userid"]); ?>">
						<div class="m1"><img src="<?php echo $item['avatar'] ? $item['avatar'] : $default['avatar']; ?>"><?php echo ($item["usernick"]); ?></div>
						<div class="m2"><?php echo ($item["i_replies"]); ?>回答<em>丨</em><?php echo ($item["i_attention"]); ?>关注</div>
					</div>
				</li>
			<?php } ?>
			</ul>
		</div>
		<a href="javascript:void(0);" class="y_more <?php if ( !$next ) { echo 'none'; } ?>" id="LJ_more">查看更多</a>
		<?php } ?>
	</div>
	<a href="javascript:void(0);" data-url="<?php echo url('LDAsk', [$company['id']], 'touch', 'ask'); ?>#ln=touch_qiye_tiwen"><div class="y_askBtn">去提问</div></a>
	<div class="y_mask cancelFollowTip" style="display: none;">
		<div class="y_pop">
			<h2>取消关注后将不会推送该公司问题</h2>
			<div class="y_btn sureCancelFollow">取消关注</div>
			<div class="y_off">
				<a class="closeTip"></a>
			</div>
		</div>
	</div>
	<div class="lMask none" id="img_scale">
		<h3 class="g_lbtitle">图片预览<a href="javascript:void(0);" class="g_prev"></a></h3>

        <div class="lMask_img" id="img_scale_box"><img class="lMask_imgM" id="img_scale_img" src="" style="width: 375px;"></div>
        <p class="lMask_text">
            <span></span>
        </p>
    </div>
	
	
	<script type="text/template" id="build_tpl">
	<literal>
        {{~it:item}}
            <li>
				<h2>
					<a href="{{=item.url}}#ln={{=item._ln_ext}}">
					{{?item.ontop > 0}}
						<em>置顶</em>
					{{?}}
					{{?item.essence > 0}}
						<em>精华</em>
					{{?}}
						{{=item.title}}
					</a>
				</h2>
				<h3>{{=item.desc}}</h3>
				{{?item.i_images > 0}}
				<dl class="y_imglist">
					{{~item.images:img}}
					<dd><img  class="js_img" src="{{=img}}"></dd>
					{{~}}
				</dl>
				{{?}}
				<div class="y_userTell" data-uid="{{=item.userid}}">
					<div class="m1"><img src="{{=item.avatar}}">{{=item.usernick}}</div>
					<div class="m2">{{=item.i_replies}}回答<em>丨</em>{{=item.i_attention}}关注</div>
				</div>
			</li>
        {{~}}
	

    </script>
	</literal>
	<script type="text/javascript">
		var pageConf = {
			id:'<?php echo ($company["id"]); ?>',//公司id
			moreUrl:'/list/more_ldq/',//公司聚合页加载更多
			isFollowUrl:'/ldapi/status',//判断用户是否已关注公司
			shareData:{
				url:location.href,
				desc:'分享你的地产看法。',
				img:'<?php echo ($default["cjlogo"]); ?>',
				title:'关于【<?php echo ($company["title"]); ?>】的问答，速来围观-乐居财经'
			}
		}
	</script>
	<script type="text/javascript">
		var city = '<?php echo ($statscode["city"]); ?>';
		var level1_page = '<?php echo ($statscode["level1_page"]); ?>';
		var level2_page = '<?php echo ($statscode["level2_page"]); ?>';
		var custom_id = '<?php echo ($statscode["custom_id"]); ?>';
		var news_source='<?php echo ($statscode["news_source"]); ?>';
	</script>
	<script type="text/javascript" src="//cdn.leju.com/lejuTj/gather.source.js"></script>
	<script src="//res.leju.com/scripts/libs/zepto/v1/zepto.js"></script>
	<script src="//res.leju.com/scripts/app/ask/v1/company.js"></script>
	<script src="//res.leju.com/scripts/app/ask/v1/login.js"></script>
</body>
</html>