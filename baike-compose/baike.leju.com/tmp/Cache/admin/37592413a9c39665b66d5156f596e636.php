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
				<link href="//cdn.leju.com/cms2.0/js/Controls/cropper-master/dist/cropper.css" rel="stylesheet" type="text/css">
<div class="ty_content">
	<h3 class="d-title clearfix">
		<span>内容编辑</span>
		<div class="b_title_r">
			<?php if(!empty($history)): ?><span class="tit">历史版本：</span>
			<select class="w235" name="history">
			<?php if(is_array($history)): foreach($history as $k=>$i): ?><option value="/Knowledge/edit?id=<?php echo ($list["id"]); ?>&pkid=<?php echo ($i["pkid"]); ?>" <?php if(($i["pkid"]) == $binds["pkid"]): ?>selected="selected"<?php endif; ?>> v<?php echo ($histotal-$k); ?> <?php echo $cities[$i['scope']]['cn']; ?> <?php echo ($i["truename"]); ?> <?php echo (date("Y年m月d日 H:i:s",$i["utime"])); ?></option><?php endforeach; endif; ?>
			</select><?php endif; ?>
		</div>
	</h3>
	<form id="docForm" asyn-action="/Knowledge/<?php echo ($binds["method"]); ?>" method="post">
	<input type="hidden" name="id" value="<?php if(empty($list["id"])): else: echo ($list["id"]); endif; ?>">

	<div class="b_wrapper">
		<div class="b_main">
			<div class="y_section">
				<div class="b_card">
					<span>标&emsp;&emsp;题： </span>
					<div class="inpBox">
						<?php if ( $list['src_type'] != 1 ) { ?>
						<select class="w105" name="src_type">
							<option value="0"<?php if(($list["src_type"]) == "0"): ?>selected="true"<?php endif; ?>>原创</option>
							<option value="2"<?php if(($list["src_type"]) == "2"): ?>selected="true"<?php endif; ?>>转载</option>
						</select>
						<?php } ?>
						<input type="text" class="inp w403" name="title" value="<?php echo ($list["title"]); ?>" placeholder="最多填写30个汉字">
					</div>
				</div>
				<div class="b_card clearfix">
					<span>摘&emsp;&emsp;要： </span>
					<div class="inpBox07">
						<textarea class="y_areaBox" name="summary" id="summary" placeholder="请填写70-100字的介绍主要内容" needed="true" tooltip="[必填]"><?php echo ($list["summary"]); ?></textarea>
					</div>
				</div>
				<div class="b_card clearfix">
					<span>内&emsp;&emsp;容：</span>
					<div class="inpBox07">
						<div class="b_editBox" id="ueditorsc"></div>
						<textarea name="content" class="ueditor" id="ueditor" style="display: none;"><p><?php echo ($list["content"]); ?></p></textarea>
					</div>
				</div>
				<div class="b_card clearfix">
					<span>配&emsp;&emsp;图：</span>
					<div class="inpBox">
						<div class="left"><img name="check_4" class="sin_preview" src="
							<?php if(empty($list["cover"])): echo ($binds["cdn_img_url"]); ?>images/bImg01.png<?php else: echo ($list["cover"]); endif; ?>" alt="">
						</div>
						<div class="right fileupload" id="picExp_upbtn">
							<input type="button" class="l_btn l_gray js_imgcut_btn" value="自定义图片">
							<input type="file" class="imgcut none" accept="image/png,image/jpg" value="">
							<p>请选择jpg、gif格式，小于2M的图</p>
						</div>
					</div>
					<input class="imgurl" type="hidden" name="cover" value="<?php if(empty($list["cover"])): else: echo ($list["cover"]); endif; ?>" needed="true" tooltip="[必填]">
				</div>
				<div class="b_card">
					<span>媒体机构：</span>
					<div class="date inpBox">
						<input type="text" class="inp w215" name="media" placeholder="媒体机构名称" value="<?php echo ($list["media"]); ?>">
					</div>
				</div>
				<div class="b_card">
					<span>媒体作者：</span>
					<div class="date inpBox">
						<input type="hidden" class="inp"  name="editorid" value="<?php echo ($userinfo["id"]); ?>">
						<input type="text" class="inp w215" placeholder="媒体作者姓名" name="editor" 
						value="<?php if(empty($list["editor"])): echo ($userinfo["truename"]); else: echo ($list["editor"]); endif; ?>">
					</div>
				</div>
			</div>

			<div class="y_subtit">发布设置</div>
			<div class="y_section">
				<div class="b_card">
					<span>城&emsp;&emsp;市：</span>
					<div class="inpBox">
						<select class="w105" name="scope">
						<option value="">请选择</option>
						<?php if(is_array($cities)): foreach($cities as $k=>$c): ?><option value="<?php echo ($c["en"]); ?>" <?php if(($k) == $list["scope"]): ?>selected="selected"<?php endif; ?>><?php echo (strtoupper($c["en"])); ?> - <?php echo ($c["cn"]); ?></option><?php endforeach; endif; ?>
						</select>
					</div>
				</div>
				<div class="b_card">
					<span>栏&emsp;&emsp;目： </span>
					<div class="inpBox">
						<select class="w105 unionAction" name="cateid1">
							<option value="">请选择</option>
						<?php if(is_array($binds["roleCate"])): foreach($binds["roleCate"] as $k=>$ac): ?><option value="<?php echo ($k); ?>" <?php if(($k) == $binds["path"]["1"]): ?>selected="selected"<?php endif; ?>><?php echo ($ac); ?></option><?php endforeach; endif; ?>
						</select>
						<select class="w105 unionAction" name="cateid2">
							<option value="">请选择</option>
							<?php if(is_array($binds["level2"])): foreach($binds["level2"] as $k=>$ll): ?><option value="<?php echo ($k); ?>" <?php if(($k) == $binds["path"]["2"]): ?>selected="selected"<?php endif; ?>><?php echo ($ll); ?></option><?php endforeach; endif; ?>
						</select>
						<select class="w105 unionAction" name="cateid">
							<option value="">请选择</option>
							<?php if(is_array($binds["level3"])): foreach($binds["level3"] as $k=>$ll): ?><option value="<?php echo ($k); ?>" <?php if(($k) == $binds["path"]["3"]): ?>selected="selected"<?php endif; ?>><?php echo ($ll); ?></option><?php endforeach; endif; ?>
						</select>
					</div>
				</div>
				<div class="b_card clearfix">
					<span>定时发布：</span>
					<div class="date inpBox07">
						<input name="ptime" value="<?php if(!empty($list["ptime"])): echo (date("Y-m-d H:i",$list["ptime"])); endif; ?>" type="text" class="inp w215" id="pubdate">
						<span id="cron_pub_time" class=" "></span>
						<i></i>
						<em>不更改时间立即发布；选择将来时间定时发布。</em>
					</div>
				</div>
				<?php if ( !empty($editor_auth) ) { ?>
				<div class="b_card clearfix">
					<span>推&emsp;&emsp;荐：</span>
					<div class="inpBox07">
					<?php if ( array_key_exists('focus', $editor_auth) ) { ?>
						<h2>
							<input name="focus" type="checkbox" class="checkbox" <?php if(($list["rcmd_time"]) > "0"): ?>checked="checked"<?php endif; ?> >
							<b>首页焦点图</b>
							<input type="text" name="rcmd_title" placeholder="自定义推荐标题" class="inp" value="<?php if(!empty($list["rcmd_title"])): echo ($list["rcmd_title"]); endif; ?>">
							<img src="<?php if(empty($list["rcmd_cover"])): echo ($binds["cdn_img_url"]); ?>images/bImg02.png<?php else: echo ($list["rcmd_cover"]); endif; ?>" class="sin_preview" alt=""><input type="button" class="l_btn l_gray js_imgcut_btn " value="自定义图片"><input type="file"  class="imgcut none" accept="image/png,image/jpg" value=""><em class="y_lh">请选择jpg、gif格式，小于2M的图<br/>默认取标题和配图，可以定义显示标题和图片</em>
							<input class="imgurl" name="rcmd_cover" value="<?php if(empty($list["rcmd_cover"])): else: echo ($list["rcmd_cover"]); endif; ?>" style="display: none;">
						</h2>
					<?php } ?>
					<?php if ( array_key_exists('top', $editor_auth) ) { ?>
						<h2>
							<input type="checkbox" class="checkbox" name="top" <?php if(($list["top_time"]) > "0"): ?>checked="checked"<?php endif; ?>><b>置顶</b>
							<input type="text" name="top_title"  placeholder="自定义推荐标题" class="inp" value="<?php if(!empty($list["top_title"])): echo ($list["top_title"]); endif; ?>">
							<img src="<?php if(empty($list["top_cover"])): echo ($binds["cdn_img_url"]); ?>images/bImg02.png<?php else: echo ($list["top_cover"]); endif; ?>" class="sin_preview"  alt=""><input type="button" class="l_btn l_gray js_imgcut_btn " value="自定义图片"><input type="file"  class="imgcut none" accept="image/png,image/jpg" value=""><em class="y_lh">请选择jpg、gif格式，小于2M的图<br/>默认取标题和配图，可以定义显示标题和图片</em>
							<input class="imgurl" name="top_cover" value="<?php if(empty($list["top_cover"])): else: echo ($list["top_cover"]); endif; ?>" style="display: none;">
						</h2>
					<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>

			<div class="y_subtit">关联内容推荐</div>
			<div class="y_section">
				<div class="b_card clearfix" >
					<span>标&emsp;&emsp;签：</span>
					<div class="inpBox label">
						<div class="tags_content_new">
						<input type="hidden" value="<?php echo trim($list['tagids'], ','); ?>" name="tags_id" data-tags="tags_id">
						<input type="hidden" value="<?php echo str_replace(' ', ',', $list['tags']); ?>" name="tags_name" data-tags="tags_name">
						</div>
						<script type="text/javascript">
							(function(){
								window.tags_config = {
									wrapClass:'tags_content_new',      //容器class
									insertCss:true,    //是否插入外链样式文件修改
									suggestInput:true,  //是否有联想搜索
									deleteAbled:true,     //是否允许删除
									placeHolder:false,    //删除后是否占位
									getContentClass:"ueditor",  //推荐内容class，没有就不要设置
									// cityEn:'yt',       //指定城市（楼盘库需要）
									key:'938a03492a8c9623d65b698ac5ce249d'  //测试业务key
								}
							})();
						</script>
					</div>
					<div class="b_popBox" style="display: none;">
					</div>
				</div>
				<div class="b_card clearfix">
					<span>相关资讯：</span>
					<div class="inpBox07">
						<div class="clearfix b_form">
							<input type="text" name="" class="inp w293" placeholder="输入新闻ID">
							<input type="button" class="l_btn l_gray" id="getnew" value="添加">
						</div>
						<textarea name="getnew" id="b_list01" style="display: none;"></textarea>
						<ul class="b_list01">
						<?php if(is_array($list["rel_news"])): $i = 0; $__LIST__ = $list["rel_news"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li class="cur clearfix"><em><?php echo ($item["title"]); ?></em>
							<input style="display:none" value="<?php echo ($item["id"]); ?>">
							<a href="javascript:void(0)" class="edit"></a><i></i><a href="<?php echo ($item["url"]); ?>" target="_blank" class="link"></a><i></i><a href="javascript:void(0)" class="del"></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>

					</div>
				</div>
				<div class="b_card clearfix">
					<span>相关楼盘：</span>
					<div class="inpBox07">
						<div class="clearfix b_form">
							<input type="text" class="inp w245" placeholder="输入楼盘ID  （CITYEN+HID组成）">
							<input type="button" id="gethouse" class="l_btn l_gray" value="添加">
						</div>
						<textarea name="gethouse" id="b_list02" style="display: none;"></textarea>
						<ul class="b_list02 clearfix">
						<?php if(is_array($list["rel_house"])): $i = 0; $__LIST__ = $list["rel_house"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li hid="<?php echo ($item["hid"]); ?>" site="<?php echo ($item["site"]); ?>"><?php echo ($item["name"]); ?><a href="<?php echo ($item["url"]); ?>" target="_blank" class="link-o"></a><i></i><a href="javascript:void(0)" class="del-o"></a>
							</li><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
					</div>
				</div>
			</div>

			<div class="y_subtit">搜索引擎收录设置</div>
			<div class="y_section">
				<div class="b_card">
					<span>title： </span>
					<div class="inpBox">
						<input type="text" class="inp w245" name="seo_title" value="<?php echo ($list["seo_title"]); ?>" needed="true" tooltip="[必填]">
						<input type="button" id="seo_title" class="seo_copy l_btn l_gray" value="复制标题内容">
					</div>
				</div>
				<div class="b_card clearfix" >
					<span>keywords：</span>
					<div class="inpBox label">
						<input type="text" placeholder="输入标签" class="inp w190" name="seo_keywords" needed="true" tooltip="[必填]" value="<?php echo ($list["seo_keywords"]); ?>">
						<input type="button" id="seo_keywords" class="seo_copy l_btn l_gray" value="复制标签内容">
					</div>
				</div>
				<div class="b_card clearfix">
					<span>description：</span>
					<div class="inpBox07">
						<textarea name="seo_description" class="y_areaBox" needed="true" tooltip="[必填]"><?php echo ($list["seo_description"]); ?></textarea>
						<input type="button" id="seo_description" class="seo_copy l_btn l_gray mrtop114" value="复制摘要内容">
					</div>
				</div>
			</div>
		</div>
		<div class="b_btnBox clearfix">
			<input name="action_type" value="save" type="hidden">
			<a href="javascript:void(0)" class="l_btn l_red saveBtn">保存</a>
			<a href="javascript:void(0)" class="l_btn l_grn pubBtn">发布</a>
		</div>
	</div>
	</form>
</div>

<script type="text/javascript" src="//cdn.leju.com/jQuery1.8.2;encyclopedia/js/base;encyclopedia/js/Pages/knowledge_editor.js?r"></script>
<script type="text/javascript" src="//cdn.leju.com/tags-fe/prd/tags_init_new.js"></script>
<script type="text/javascript" src="//cdn.leju.com/encyclopedia/js/Pages/editor2.0.js"></script>

			</div>
		</div>
	</div>
</body>
</html>