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
				<div class="ty_content">
	<h3 class="d-title clearfix">
		<span>词条管理</span>
	</h3>
	<form id="searchForm" action="/Wiki/" method="get">
	<div class="ty_Ctop clearfix">
		<ul class="clearfix">
			<li><span>词条</span><?php
 $cfg = array('class'=>'w166', 'name'=>'title', 'id'=>'',); echo search_form_input($form['title'], $cfg); ?></li>
			<li>
				<span>来源</span>
				<?php
 $cfg = array('class'=>'w110', 'name'=>'src_type', 'id'=>'',); echo search_form_select($form['src_type'], $dicts['SOURCE'], $cfg); ?>
			</li>
			<li>
				<span>类别</span>
				<?php
 $cfg = array('class'=>'w102', 'name'=>'cateid', 'id'=>'',); echo search_form_select($form['cateid'], $dicts['CATE'], $cfg); ?>
			</li>
			<li><span>作者</span><?php
 $cfg = array('class'=>'w110', 'name'=>'editor', 'id'=>'',); echo search_form_input($form['editor'], $cfg); ?></li>
			<li><span>发布时间</span><?php
 $cfg = array('class'=>'w210', 'name'=>'ptime', 'id'=>'searchdate'); $ptime = isset($form['ptime']) ? date('Y-m-d', $form['ptime']) : ''; echo search_form_timepicker($ptime, $cfg); ?></li>
			<li class="ty_lanmu"><span>热度排行</span><?php
 $cfg = array('class'=>'', 'name'=>'sort', 'id'=>'',); echo search_form_select($form['sort'], $dicts['SORT'], $cfg); ?></li>
			<li><span>发布状态</span><?php
 $cfg = array('class'=>'w102', 'name'=>'status', 'id'=>'',); echo search_form_select($form['status'], $dicts['STATUS'], $cfg); ?></li>
			<li class="fr"><a href="javascript:;" class="l_btn l_red">查询</a></li>
		</ul>
	</div>
	<div class="ty_list">
		<?php if(!empty($list)): ?><a class="ty_new" href="/Wiki/Add"><i class="ty_add"></i>新建</a>
		<table class="d-table">
			<thead>
				<th>词条</th>
				<th>热度</th>
				<th>作者</th>
				<th>类别</th>
				<th>更新时间</th>
				<th>来源</th>
				<th>状态</th>
				<th>操作</th>
			</thead>
			<tbody>
				<?php if(is_array($list)): foreach($list as $k=>$vo): ?><tr>
					<td><span><?php echo ($vo["title"]); if($vo[recommend][0] OR $vo[recommend][1] OR $vo[recommend][2]): ?><em>荐</em><?php endif; if(($vo["p"] - $nowtime) > 0): ?><em class="time"><i class="ty_time"></i></em><?php endif; ?></span></td>
					<td><span><?php echo ($vo["hits"]); ?></span></td>
					<td><span><?php echo ($vo["editor"]); ?></span></td>
					<td><span><?php echo $dicts['CATE'][$vo['cateid']]['name']; ?></span></td>
					<td><span><?php
 $time_flag = ''; $time = ''; switch ( intval($vo['status']) ) { case 2: $time_flag = '<b class="l_grn1">[定时]</b>'; $time = date('Y-m-d H:i:s', $vo['ptime']); break; case 0: $time_flag = '<b class="l_gray">[未发布]</b>'; $time = ''; break; case 1: $time_flag = '<b class="l_org">[草稿]</b>'; $time = date('Y-m-d H:i:s', $vo['utime']); break; case 9: $time_flag = '<b class="l_grn2">[已发布]</b>'; $time = date('Y-m-d H:i:s', $vo['utime']); break; case -1: $time_flag = '<b class="l_red">[删除]</b>'; $time = date('Y-m-d H:i:s', $vo['utime']); break; } echo $time; ?></span>
					</td>
					<td><span><?php
 $_src_name = $dicts['SOURCE'][$vo['src_type']]['name']; if ( trim($vo['extra']['src_url'])!='' || $vo['src_type']==1 ) { $_src_url = trim($vo['extra']['src_url']); } else { $_src_url = ''; } if ( $_src_url == '' ) { echo $_src_name; } else { echo '<a class="link" target="_blank" href="'.$_src_url.'">'.$_src_name.'<i class="ty_link"></i></a>'; } ?></span></td>
					<td><span><?php echo $time_flag; ?></span></td>
					<td>
						<p class="last">
						<?php if(($vo["status"]) == "9"): ?><a class="a1" href="<?php echo ($vo["viewurl"]["pc"]); ?>?nofit=1" target="_blank">PC链接</a>
							<a class="a1" href="<?php echo ($vo["viewurl"]["touch"]); ?>?nofit=1" target="_blank">Touch链接</a>
						<?php else: ?>
							<a class="a1" href="<?php echo ($vo["preview"]["pc"]); ?>" target="_blank">PC预览</a>
							<a class="a1" href="<?php echo ($vo["preview"]["touch"]); ?>" target="_blank">Touch预览</a><?php endif; ?>
						<br>
						<a class="a1" href="/Wiki/edit?id=<?php echo ($vo["id"]); ?>">修改</a>
						<?php if ( intval($vo['status'])!=-1 ) { ?>
						<a class="a2 del" did="<?php echo ($vo["id"]); ?>" data-type="" href="javascript:;">删除</a>
						<?php } ?>
						</p>
					</td>
				</tr><?php endforeach; endif; ?>
			</tbody>
		</table>
		<?php if(!empty($pager)): ?><div class="d-pages clearfix">
	<em>共 <?php echo ($pager["total"]); ?> 条</em>
	<!--
	<em>当前第 <?php echo ($pager["page"]); ?> 页</em>
	<em>每页 <?php echo ($pager["pagesize"]); ?> 条</em>
	-->
	<em>共 <?php echo ($pager["count"]); ?> 页</em>
	<div class="d-pagebox">
		<?php if(!empty($pager["first"])): ?><a class="d-pageBtn-2" href="<?php echo ($pager["first"]); ?>">首页</a><?php endif; ?>
		<?php if(!empty($pager["prev"])): ?><a class="d-pageBtn" href="<?php echo ($pager["prev"]); ?>">上一页</a><?php endif; ?>
		<?php if(($pager["sp_before"]) == "true"): ?><a class="disabled"><?php echo ($pager["spline"]); ?></a><?php endif; ?>
		<?php if(is_array($pager["list"])): foreach($pager["list"] as $k=>$vo): if(($pager["page"]) == $vo["num"]): ?><span><?php echo ($vo["num"]); ?></span>
			<?php else: ?>
			<a href="<?php echo ($vo["url"]); ?>"><?php echo ($vo["num"]); ?></a><?php endif; endforeach; endif; ?>
		<?php if(($pager["sp_after"]) == "true"): ?><a class="disabled"><?php echo ($pager["spline"]); ?></a><?php endif; ?>
		<?php if(!empty($pager["next"])): ?><a class="d-pageBtn" href="<?php echo ($pager["next"]); ?>">下一页</a><?php endif; ?>
		<?php if(!empty($pager["last"])): ?><a class="d-pageBtn-2" href="<?php echo ($pager["last"]); ?>">末页</a><?php endif; ?>
		<?php if(($pager["jump"]) == "true"): ?><form id="frm_list_pager" style="display: inline;" action="" method="GET">
		<?php foreach ( $pager['linkopts'] as $name => $value ) { ?>
			<input type="hidden" value="<?php echo ($value); ?>" name="<?php echo ($name); ?>" >
		<?php } ?>
		到第<input class="d-inp" type="text" name="<?php echo ($pager["var"]); ?>" value="<?php echo ($pager["page"]); ?>">页
		<a href="javascript:frm_list_pager.submit();" class="d-submit">确定</a>
		</form><?php endif; ?>
	</div>
</div><?php endif; endif; ?>
		<?php if(empty($list)): ?><!-- 无结果 -->
		<div class="ty_Nores">
			您选择“条件搜索”后，<span>暂无数据</span>！
		</div>
		<!-- 无结果end --><?php endif; ?>
	</div>
	</form>
	<script type="text/javascript" src="http://cdn.leju.com/jQuery1.8.2;encyclopedia/js/base;encyclopedia/js/Pages/entry_manage.js?r"></script>
</div>


			</div>
		</div>
	</div>
</body>
</html>