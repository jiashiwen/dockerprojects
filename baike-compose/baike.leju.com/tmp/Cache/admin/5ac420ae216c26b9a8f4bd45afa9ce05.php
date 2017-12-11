<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<title><?php echo ($pageinfo["title"]); ?></title>
	<link rel="stylesheet" href="//cdn.leju.com/encyclopedia/styles/stylesqa.css">
	<link rel="stylesheet" href="//<?php echo ($_SERVER['PS_ADMIN']); ?>/styles/styles.css">
</head>
<body>
	<div class="y_wrap">
		<div class="y_head">
	<div class="y_logo"><img src="//<?php echo ($_SERVER['PS_ADMIN']); ?>/images/y_logo.png" ></div>
	<div class="y_user">
		<a class="us" href=""><i></i><?php echo ($userinfo["truename"]); ?></a><a href=""><?php echo ($userinfo["role_name"]); ?></a>
	</div>
	<a class="y_icon01" href="/Login/logout"></a>
</div>

		<?php
 $addon_class = ''; $ctl = strtolower(CONTROLLER_NAME); $act = strtolower(ACTION_NAME); if ( in_array($ctl, array('knowledge','wiki')) ) { if ( in_array($act, array('edit', 'add')) ) { $addon_class = ' y_bg01'; } } ?>
		<div class="y_main<?php echo $addon_class; ?> clearfix">
			<div class="y_left">
	<div class="y_nav">
		<h2 <?php if((CONTROLLER_NAME== 'Index') AND (ACTION_NAME== 'index')): ?>class="on menu_title"<?php else: ?>class="menu_title"<?php endif; ?>>
		<a href="/"><i class="ic01"></i>系统首页</a></h2>

		<h2 <?php if((CONTROLLER_NAME== 'Knowledge') AND (ACTION_NAME!= 'Cate')): ?>class="on menu_title"<?php else: ?>class="menu_title"<?php endif; ?>><i class="ic02"></i>知识管理<em></em></h2>
		<ul class=""> <!--none 为隐藏class名-->
			<li <?php if((CONTROLLER_NAME== 'Knowledge') AND (ACTION_NAME== 'index')): ?>class="on"<?php endif; ?>><a href="/Knowledge/">管理知识</a></li>
			<li <?php if((CONTROLLER_NAME== 'Knowledge') AND (ACTION_NAME== 'Add')): ?>class="on"<?php endif; ?>><a href="/Knowledge/Add">添加知识</a></li>
		</ul>

		<h2 <?php if((CONTROLLER_NAME== 'Question')): ?>class="on menu_title"<?php else: ?>class="menu_title"<?php endif; ?>><i class="ic02"></i>问答管理<em></em></h2>
		<ul class=""> <!--none 为隐藏class名-->
			<li <?php if((CONTROLLER_NAME== 'Question') AND (ACTION_NAME== 'index')): ?>class="on"<?php endif; ?>><a href="/Question/">管理问答</a></li>
			<li <?php if((CONTROLLER_NAME== 'Question') AND (ACTION_NAME== 'company')): ?>class="on"<?php endif; ?>><a href="/Question/company">乐道问答</a></li>
			<li <?php if((CONTROLLER_NAME== 'Question') AND (ACTION_NAME== 'person')): ?>class="on"<?php endif; ?>><a href="/Question/person">人物问答</a></li>
		</ul>

		<h2 <?php if((CONTROLLER_NAME== 'Wiki')): ?>class="on menu_title"<?php else: ?>class="menu_title"<?php endif; ?>><i class="ic03"></i>词条管理<em></em></h2>
		<ul>
			<li <?php if((CONTROLLER_NAME== 'Wiki') AND (ACTION_NAME== 'index')): ?>class="on"<?php endif; ?>><a href="/Wiki/">管理词条</a></li>
			<li <?php if((CONTROLLER_NAME== 'Wiki') AND (ACTION_NAME== 'Add')): ?>class="on"<?php endif; ?>><a href="/Wiki/Add">添加词条</a></li>
		</ul>

		<h2 <?php if((CONTROLLER_NAME== 'Roles') OR (CONTROLLER_NAME== 'Admin')): ?>class="on menu_title"<?php else: ?>class="menu_title"<?php endif; ?>><i class="ic04"></i>用户管理<em></em></h2>
		<ul>
			<li <?php if((CONTROLLER_NAME== 'Roles') AND (ACTION_NAME== 'index')): ?>class="on"<?php endif; ?>><a href="/Roles/">角色管理</a></li>
			<li <?php if((CONTROLLER_NAME== 'Admin') AND (ACTION_NAME== 'index')): ?>class="on"<?php endif; ?>><a href="/Admin/">用户管理</a></li>
		</ul>

		<h2 <?php if((CONTROLLER_NAME== 'Knowledge') AND (ACTION_NAME== 'Cate')): ?>class="on menu_title"<?php else: ?>class="menu_title"<?php endif; ?>><i class="ic05"></i>栏目管理<em></em></h2>
		<ul>
			<li <?php if((CONTROLLER_NAME== 'Cate') AND (ACTION_NAME== 'Knowledge')): ?>class="on"<?php endif; ?>><a href="/Cate/Knowledge">知识栏目</a></li>
			<li <?php if((CONTROLLER_NAME== 'Cate') AND (ACTION_NAME== 'Question')): ?>class="on"<?php endif; ?>><a href="/Cate/Question">问答栏目</a></li>
		</ul>

		<h2 <?php if((CONTROLLER_NAME== 'Logs')): ?>class="on menu_title"<?php else: ?>class="menu_title"<?php endif; ?>><i class="ic02"></i>操作审计<em></em></h2>
		<ul class="">
			<li <?php if((CONTROLLER_NAME== 'Logs') AND (ACTION_NAME== 'index')): ?>class="on"<?php endif; ?>><a href="/Logs/">操作日志</a></li>
		</ul>
		<?php if ( $userinfo['_developer'] ) { ?>
		<h2 <?php if((CONTROLLER_NAME== 'Devel')): ?>class="on menu_title"<?php else: ?>class="menu_title"<?php endif; ?>><i class="ic02"></i>开发者工具<em></em></h2>
		<ul class="">
			<li <?php if((CONTROLLER_NAME== 'Devel') AND (ACTION_NAME== 'index')): ?>class="on"<?php endif; ?>><a href="/Devel/">开发者工具</a></li>
		</ul>
		<?php } ?>
	</div>
</div>

			<div class="y_right">
				<h3 class="d-title clearfix">
	<span>词条统计</span>
</h3>
<ul class="d-imglist clearfix">
	<li>
		<div class="d-odd">
			<h3><span>七天更新趋势</span></h3>
			<div class="d-word " >
				<div id="echarts1" style="margin-top:-50px;height:200px;width:90%"></div>
			</div>
		</div>
	</li>
	<li>
		<div class="d-even">
			<h3><span>七天热度趋势</span></h3>
			<div class="d-word"  >
				<div id="echarts2" style="margin-top:-50px;height:200px;width:90%"></div>
			</div>
		</div>
	</li>
	<li>
		<div class="d-odd">
			<h3><span>七天热词排行</span></h3>
			<div class="d-word clearfix">
				<div class="d-wordlist">
					<ul class="d-odd">
						<?php if(is_array($words)): $k = 0; $__LIST__ = $words;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k; if(($mod) == "0"): ?><a href="<?php echo url('show', array($v['id'], $v['cateid']), 'touch', 'wiki')?>" target="_blank">
									<li class="clearfix">
										<?php if(($k) == "1"): ?><p><em class="d-red"><?php echo ($k); ?>.</em><?php echo ($v["title"]); ?></p>
											<span class="d-red"><?php echo ($v["cnt"]); ?></span>
										<?php else: ?>
											<p><em><?php echo ($k); ?>.</em><?php echo ($v["title"]); ?></p>
											<span><?php echo ($v["cnt"]); ?></span><?php endif; ?>
									</li>
								</a><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					</ul>
				</div>
				<div class="d-wordlist">
					<ul class="d-even clearfix">
						<?php if(is_array($words)): $k = 0; $__LIST__ = $words;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k; if(($mod) == "1"): ?><a href="<?php echo url('show', array($v['id'], $v['cateid']), 'touch', 'wiki')?>" target="_blank">
									<li class="clearfix">
										<p><em><?php echo ($k); ?>.</em><?php echo ($v["title"]); ?></p>
										<span><?php echo ($v["cnt"]); ?></span>
									</li>
								</a><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					</ul>
				</div>
			</div>
		</div>
	</li>
</ul>
<h3 class="d-title clearfix">
	<span>知识统计</span>
</h3>
<ul class="d-imglist clearfix">
	<li>
		<div class="d-odd">
			<h3><span>七天更新趋势</span></h3>
			<div class="d-word">
				<div id="echarts4" style="margin-top:-50px;height:200px;width:90%"></div>
			</div>
		</div>
	</li>
	<li>
		<div class="d-even">
			<h3><span>七天热度趋势</span></h3>
			<div class="d-word">
				<div id="echarts5" style="margin-top:-50px;height:200px;width:90%"></div>
			</div>
		</div>
	</li>
	<li>
		<div class="d-even">
			<h3><span>七天知识排行</span></h3>
			<div class="d-word clearfix">
				<div class="d-wordlist">
				<noempty name="rank">
					<ul class="d-odd">
					<?php if(is_array($rank["0"])): $key = 0; $__LIST__ = $rank["0"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($key % 2 );++$key;?><a href="<?php echo url('show', array($r['id']), 'touch', 'baike'); ?>" target="_blank">
						<li class="clearfix">
							<p><em class="<?php if(($key) == "1"): ?>d-red<?php endif; ?>"><?php echo ($key); ?>.</em><?php echo ($r["title"]); ?></p>
							<span <?php if(($key) == "1"): ?>class="d-red"<?php endif; ?>><?php echo ($r["total"]); ?></span>
						</li>
					</a><?php endforeach; endif; else: echo "" ;endif; ?>
					</ul>
				</div>
				</noempty>
				<div class="d-wordlist">
				<noempty name="rank.1">
					<ul class="d-even clearfix">
					<?php if(is_array($rank["1"])): $key = 0; $__LIST__ = $rank["1"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($key % 2 );++$key;?><a href="<?php echo url('show', array($r['id']), 'touch', 'baike'); ?>" target="_blank">
						<li class="clearfix">
							<p><em><?php echo ($key+4); ?>.</em><?php echo ($r["title"]); ?></p>
							<span><?php echo ($r["total"]); ?></span>
						</li>
					</a><?php endforeach; endif; else: echo "" ;endif; ?>
					</ul>
					</noempty>
				</div>
			</div>
		</div>
	</li>
</ul>
<script src="http://cdn.leju.com/queue/js/echarts.common.min.js"></script>
<script type="text/javascript" src="http://cdn.leju.com/jQuery1.8.2;encyclopedia/js/base;encyclopedia/js/Pages/home.js?r"></script>
			</div>
		</div>
	</div>
</body>
</html>