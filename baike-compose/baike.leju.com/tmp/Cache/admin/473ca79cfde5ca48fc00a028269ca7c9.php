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
		<span>知识管理</span>
	</h3>

	<form id="searchForm" action="/Knowledge" method="get">
		<div class="ty_Ctop clearfix">
			<ul class="clearfix">
				<li><span>题目</span><input name="keyword" value="<?php if(!empty($params["keyword"])): echo ($params["keyword"]); endif; ?>" type="text"></li>
				<li><span>作者</span><input name="editor" value="<?php if(!empty($params["editor"])): echo ($params["editor"]); endif; ?>" class="w110" type="text"></li>
				<li>
					<span>城市</span>
					<select class="w102" name="scope" id="">
						<option value="" selected>请选择</option>
						<?php if(is_array($cities)): foreach($cities as $k=>$c): ?><option value="<?php echo ($c["en"]); ?>" <?php if(($c["en"]) == $params["scope"]): ?>selected="selected"<?php endif; ?>><?php echo (strtoupper($c["en"])); ?> - <?php echo ($c["cn"]); ?></option><?php endforeach; endif; ?>
					</select>
				</li>
				<li>
					<span>类型</span>
					<select name="src_type" id="">
						<option value="">请选择</option>
					<?php if(is_array($dicts["TYPES"])): foreach($dicts["TYPES"] as $id=>$item): if ( strval($params['src_type']) === strval($id) ) { ?>
						<option value="<?php echo ($id); ?>" selected="selected"><?php echo ($item["name"]); ?></option>
						<?php } else { ?>
						<option value="<?php echo ($id); ?>"><?php echo ($item["name"]); ?></option>
						<?php } endforeach; endif; ?>
					</select>
				</li>
				<li>
					<span>发布时间</span><input value="<?php if(!empty($params["ptime"])): echo ($params["ptime"]); endif; ?>" id="searchdate" name="ptime" class="w210" type="text"><i class="ty_date"></i>
				</li>
				<li class="ty_lanmu">
					<span>栏目</span>
					<select name="level1" id="" class="unionAction">
						<option value="" >请选择</option>
						<?php if(is_array($binds["authorcate"])): foreach($binds["authorcate"] as $k=>$ac): ?><option value="<?php echo ($k); ?>" <?php if(($k) == $params["level1"]): ?>selected="selected"<?php endif; ?> ><?php echo ($ac); ?></option><?php endforeach; endif; ?>
					</select>
					<select name="level2" id="" class="unionAction">
						<option value="">请选择</option>
						<noempty name="level2">
						<?php if(is_array($binds["level2"])): foreach($binds["level2"] as $k=>$l2): ?><option value="<?php echo ($k); ?>" <?php if(($k) == $params["level2"]): ?>selected="selected"<?php endif; ?> ><?php echo ($l2); ?></option><?php endforeach; endif; ?>>
						</noempty>
					</select>
					<select name="cateid" id="" class="unionAction">
						<option value="">请选择</option>
						<noempty name="level3">
						<?php if(is_array($binds["level3"])): foreach($binds["level3"] as $k=>$l3): ?><option value="<?php echo ($k); ?>" <?php if(($k) == $params["cateid"]): ?>selected="selected"<?php endif; ?> ><?php echo ($l3); ?></option><?php endforeach; endif; ?>
						</noempty>
					</select>
				</li>
				<li >
				<span>发布状态</span>
					<select name="status" id="">
						<option value="">请选择</option>
					<?php if(is_array($dicts["STATUS"])): foreach($dicts["STATUS"] as $id=>$item): if ( strval($params['status']) === strval($id) ) { ?>
						<option value="<?php echo ($id); ?>" selected="selected"><?php echo ($item["name"]); ?></option>
						<?php } else { ?>
						<option value="<?php echo ($id); ?>"><?php echo ($item["name"]); ?></option>
						<?php } endforeach; endif; ?>
					</select>
				</li>
				<li class="fr"><a href="#" class="l_btn l_red">查询</a></li>
			</ul>
		</div>
	</from>

	<div class="ty_list">
		<a class="ty_new" href="/Knowledge/Add"><i class="ty_add"></i>新建</a>
		<table class="d-table">
			<thead>
				<th>ID</th>
				<th>栏目</th>
				<th>标题</th>
				<th>城市</th>
				<th>作者</th>
				<th>发布时间</th>
				<th>类型</th>
				<th>状态</th>
				<th>操作</th>
			</thead>
			<tbody>
			<?php if(is_array($data)): foreach($data as $k=>$l): ?><tr data-id="<?php echo ($l["_origin"]["id"]); ?>">
					<td><span><?php echo ($l["_id"]); ?></span></td>
					<td><span><?php echo ($l["_category"]); ?></span></td>
					<td class="tl"><span>
						<?php echo ($l["_title"]); ?>
						<?php if(($l["_origin"]["rcmd_time"]) > "0"): ?><em>荐</em><?php endif; ?>
						<?php if(($l["_origin"]["top_time"]) > "0"): ?><em class="ding">顶</em><?php endif; ?>
						<?php if(($l["_timer"]) == "1"): ?><em class="time"><i class="ty_time" title="<?php echo (date('定时在 Y-m-d H:i 发布',$l["_origin"]["ptime"])); ?>"></i></em><?php endif; ?>
					</span></td>
					<td><span><?php echo ($l["_scope"]); ?></span></td>
					<td><span><?php echo ($l["_origin"]["editor"]); ?></span></td>
					<td>
					<?php if(($l["_origin"]["status"]) == "9"): ?><!--[发布]--><span><?php echo (date("Y-m-d H:i:s",$l["_origin"]["version"])); ?></span><?php endif; ?>
					<?php if(($l["_origin"]["status"]) == "2"): ?><!--[定时]--><span class="l_gray"><?php echo (date("Y-m-d H:i:s",$l["_origin"]["ptime"])); ?></span><?php endif; ?>
					<?php if(($l["_origin"]["status"]) == "1"): ?><!--[草稿]--><span><?php echo (date("Y-m-d H:i:s",$l["_origin"]["utime"])); ?></span><?php endif; ?>
					<?php if(($l["_origin"]["status"]) == "0"): ?><!--[创建] <?php echo (date("Y-m-d H:i:s",$l["_origin"]["ctime"])); ?>--><span></span><?php endif; ?>
					<?php if(($l["_origin"]["status"]) == "-1"): ?><!--[删除]--><span><?php echo (date("Y-m-d H:i:s",$l["_origin"]["utime"])); ?></span><?php endif; ?>
					</td>
					<td>
						<span>
						<?php
 switch ( intval($l['_origin']['src_type']) ) { case 2: echo $dicts['TYPES']['2']['name']; break; case 1: echo '<a class="link" target="_blank" href="', $l['_src_url'], '">', $dicts['TYPES']['1']['name'], '<i class="ty_link"></i></a>'; break; case 0: default: echo $dicts['TYPES']['0']['name']; break; } ?>
						</span>
					</td>
					<td>
						<span>
						<b class="<?php echo $dicts['STATUS'][$l['_origin']['status']]['class']; ?>"><?php echo $dicts['STATUS'][$l['_origin']['status']]['name']; ?></b>
						</span>
					</td>
					<td>
						<p class="last">
						<?php if(($l["_origin"]["status"]) == "9"): ?><a class="a1" href="<?php echo url('show', array('id'=>$l['_id']), 'pc', 'baike'); ?>?nofit=1" target="_blank">PC链接</a>
							<a class="a1" href="<?php echo url('show', array('id'=>$l['_id']), 'touch', 'baike'); ?>?nofit=1" target="_blank">Touch链接</a>
						<?php else: ?>
							<a class="a1" href="<?php echo ($l["preview"]["pc"]); ?>" target="_blank">PC预览</a>
							<a class="a1" href="<?php echo ($l["preview"]["touch"]); ?>" target="_blank">Touch预览</a><?php endif; ?>
							<br>
							<a class="a1" href="/Knowledge/edit?id=<?php echo ($l["_origin"]["id"]); if(($$l["_origin"]["pkid"]) > "0"): ?>&pkid=<?php echo ($l["_origin"]["pkid"]); endif; ?>">修改</a>
							<?php if ( in_array( intval($l['_origin']['status']), array(2, 9) ) ) { ?>
							<a class="a2 del" data-id="<?php echo ($l["_id"]); ?>" data-type="del" href="javascript:;">删除</a>
							<?php } ?>
							<?php if ( in_array( intval($l['_origin']['status']), array(0, -1) )) { ?>
							<a class="a2 del" data-id="<?php echo ($l["_id"]); ?>" data-type="destory" href="javascript:;">彻底删除</a>
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
</div><?php endif; ?>

		<!-- 无结果 -->
		<?php if(($pager["total"]) == "0"): ?><div class="ty_Nores">
			您选择“条件搜索”后，<span>暂无数据</span>！
		</div><?php endif; ?>
		<!-- 无结果end -->

	</div>
</div>

<script type="text/javascript" src="http://cdn.leju.com/jQuery1.8.2;encyclopedia/js/base;encyclopedia/js/Pages/knowledge_manage.js?r"></script>

			</div>
		</div>
	</div>
</body>
</html>