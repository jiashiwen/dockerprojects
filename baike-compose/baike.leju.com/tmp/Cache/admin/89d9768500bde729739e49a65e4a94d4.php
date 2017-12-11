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
		<span><?php echo ($pageinfo["title"]); ?></span>
		<div class="b_title_r">
		<?php if(!empty($histroy_info)): ?><span class="tit">历史版本：</span>
			<select class="w235">
				<?php if(is_array($histroy_info)): foreach($histroy_info as $k=>$vo): if(!empty($vo)): ?><option value="/Wiki/edit?&id=<?php echo ($id); ?>&pkid=<?php echo ($vo["pkid"]); ?>" <?php if($vo['pkid'] == $history_id): ?>selected<?php endif; ?>>
					v<?php echo count($histroy_info)-$k; ?> <?php echo ($vo["editor"]); ?> <?php echo (date('Y-m-d H:i:s',$vo["version"])); ?>
				</option><?php endif; endforeach; endif; ?>
			</select><?php endif; ?>
		</div>
	</h3>
	<div class="b_wrapper">
		<div class="b_main">
			<form id="entryForm" <?php if(!empty($id)): ?>asyn-action="/Wiki/edit"<?php else: ?>asyn-action="/Wiki/Add"<?php endif; ?> method="POST" data-usetemplate="<?php echo $data['_changed'] ? 1 : 0; ?>">
				<div class="y_section">
					<div class="b_card g_card">
						<span>类&emsp;&emsp;别：</span>
						<div class="inpBox g_Box">
						<?php
 $cfg = array('class'=>'w105', 'name'=>'cateid', 'id'=>'',); echo search_form_select($data['cateid'], $dicts['CATE'], $cfg, false); ?>
						</div>
					</div>
					<input type="hidden" needed="true" name="src_type" value="<?php echo (intval($data["src_type"])); ?>">
					<?php  if ( intval($data['src_type'])==0 ) { ?>
					<input type="hidden" name="extra[src_url]" value="http://www.leju.com/">
					<?php } else { ?>
					<div class="b_card">
						<span>词条来源：</span>
						<div class="inpBox">
						<?php
 $_src_url = trim($data['extra']['src_url']); if ( $_src_url!='' ) { ?>
							<a class="url" target="_blank" href="<?php echo ($data["extra"]["src_url"]); ?>"><?php echo ($data["extra"]["src_url"]); ?></a>&nbsp;<input type="hidden" name="extra[src_url]" value="<?php echo ($data["extra"]["src_url"]); ?>">
						<?php } else { echo '<a class="url" href="javascript:;">', $dicts['SOURCE'][$data['src_type']]['name'], '</a>&nbsp;<input type="hidden" name="extra[src_url]" value="">'; } ?>
						</div>
					</div>
					<?php } ?>
					<div class="b_card g_card clearfix">
						<span>词条中文<i class="g_star">*</i>：</span>
						<div class="date inpBox g_Box">
							<input name="id" type="hidden" id="id" value="<?php echo ((isset($data["id"]) && ($data["id"] !== ""))?($data["id"]):0); ?>">
							<input name="title" type="text" class="inp g215" id="check_1" value="<?php echo (trim($data["title"])); ?>" needed="true" tooltip="[必填]" placeholder="最多填写30个汉字">
						</div>
					</div>
					<?php if ( $cateid==1 ) { ?>
					<div class="b_card g_card clearfix">
						<span>简&emsp;&emsp;称<i class="g_star">*</i>：</span>
						<div class="date inpBox g_Box">
							<input name="stname" type="text" class="inp g215" value="<?php echo ($data["stname"]); ?>" needed="true" tooltip="[必填]" placeholder="最多填写6个汉字">
						</div>
					</div>
					<div class="b_card g_card clearfix">
						<span>短&emsp;&emsp;名<i class="g_star">*</i>：</span>
						<div class="date inpBox g_Box">
							<input name="short" type="text" class="inp g215" value="<?php echo ($data["short"]); ?>" needed="true" tooltip="[必填]" placeholder="最多填写6个汉字">
						</div>
					</div>
					<?php } ?>
					<div class="g_card clearfix">
						<span class="y_subTit">简&emsp;&emsp;介<i class="g_star">*</i>：</span>
						<div class="y_Box">
							<div id="uesummary"></div>
							<textarea name="summary" class="y_areaBox g_areaBox" needed="true" tooltip="[必填]" id="summary" style="display: none;"><?php echo ($data["summary"]); ?></textarea>
						</div>
					</div>
					<div class="b_card g_card clearfix">
						<span>内&emsp;&emsp;容<i class="g_star">*</i>：</span>
						<div class="y_Box">
							<div id="ueditorsc"></div>
							<textarea name="content" class="ueditor y_areaBox g_areaBox" needed="true" tooltip="[必填]" id="ueditor" style="display: none;"><?php echo ($data["content"]); ?></textarea>
						</div>
					</div>
					<div class="b_card g_card clearfix">
					<?php if ( $cateid==1 ) { ?><span>企业Logo<i class="g_star">*</i>：</span><?php } ?>
					<?php if ( $cateid!=1 ) { ?><span>配&emsp;&emsp;图<i class="g_star">*</i>：</span><?php } ?>
						<div class="inpBox">
							<div class="left">
							<img class="sin_preview" src="<?php if(empty($data["cover"])): ?>//<?php echo ($_SERVER['PS_ADMIN']); ?>/images/bImg01.png<?php else: echo ($data["cover"]); endif; ?>" need="true">
							<input name="cover" class="imgurl" needed="true" tooltip="[必填]" value="<?php echo ($data["cover"]); ?>" style="display: none;">
							</div>
							<div class="right">
								<input type="button" class="l_btn l_gray js_imgcut_btn" value="自定义图片">
                                <input type="file" class="imgcut none" accept="image/png,image/jpg" value="">
								<p>请选择jpg、gif格式，小于2M的图</p>
							</div>
						</div>
					</div>
					<div class="b_card g_card clearfix">
						<span>相&emsp;&emsp;册：</span>
						<div class="date inpBox g_Box02">
							<input name="album[title]" value="<?php echo ($data["album"]["title"]); ?>" type="text" class="inp w215" placeholder="相册名称">
							<div class="right">
								<input type="button" class="l_btn l_gray js_h5_files" value="自定义图片">
								<input type="file" id="js_files" class="none" multiple accept="image/png,image/jpg" value=""> 
                                <p>请选择jpg、gif格式，小于2M的图</p>
							</div>
						</div>
						<div class="g_img-list js-pre-show">
							<ul>
							<?php
 foreach ( $data['album']['list'] as $i => $image ) { $is_pc = $data['album']['cover']['pc']==$image['img']?' checked':''; $is_h5 = $data['album']['cover']['h5']==$image['img']?' checked':''; $is_cur = $is_pc!='' || $is_h5!='' ? ' class="g_cur"' : ''; ?>
								<li<?php echo ($is_cur); ?>>
									<input name="album[list][][img]" type="hidden" value="<?php echo ($image["img"]); ?>">
									<img src="<?php echo ($image["img"]); ?>">
									<i class="g_sc js_img_del"></i>
									<div class="g_i02">
									<?php
 ?>
										<p><input name="album[cover][pc]" type="radio" id="a_pc_<?php echo ($i); ?>" value="<?php echo ($image["img"]); ?>"<?php echo ($is_pc); ?>><label for="a_pc_<?php echo ($i); ?>">设为PC封面</label></p>
										<p><input name="album[cover][h5]" type="radio" id="a_h5_<?php echo ($i); ?>" value="<?php echo ($image["img"]); ?>"<?php echo ($is_h5); ?>><label for="a_h5_<?php echo ($i); ?>">设为H5封面</label></p>
									</div>
								</li>
							<?php } ?>
							</ul>
						</div>
					</div>
					<?php if ( $cateid==1 ) { ?><div class="b_card g_card clearfix">
	<span>基本信息：</span>
	<div class="g_inbox clearfix">
		<div class="g_sinput">
			<em>外文名：</em>
			<input type="text" name="basic[enname]" placeholder="外文名" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["enname"]); ?>" leng="10">
		</div>
		<div class="g_sinput">
			<em>简&ensp;&ensp;称：</em>
			<input type="text" name="basic[stname]" placeholder="简称" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["stname"]); ?>" leng="10">
		</div>
		<div class="g_sinput">
			<em>创建时间：</em>
			<?php
 $basic_ctime = ''; if ( isset($data['basic']['ctime']) && trim($data['basic']['ctime'])!='' ) { $basic_ctime = date('Y-m-d', strtotime($data['basic']['ctime'])); } ?>
			<input name="basic[ctime]" type="text" value="<?php echo ($basic_ctime); ?>" placeholder="创建时间" class="g_ip001 js_verify" id="basic_ctime">
		</div>
		<div class="g_sinput">
			<em>城&ensp;&ensp;市：</em>
			<select name="city" class="g_s001 g_s002 js_verify" id="basic_city">
				<option value="">请选择</option>
				<?php
 foreach ( $cities as $cityen => $city ) { $selected = $data['city']==$city['cn'] ? ' selected' : ''; ?>
				<option value="<?php echo ($city["cn"]); ?>"<?php echo ($selected); ?>><?php echo ($cityen); ?> - <?php echo ($city["cn"]); ?></options>
				<?php } ?>
			</select>
		</div>
		<div class="g_sinput">
			<em>官方网站：</em>
			<input type="text" name="basic[homepage]" placeholder="官方网站" class="g_ip001" value="<?php echo ($data["basic"]["homepage"]); ?>">
		</div>
	</div>
</div>
<?php } ?>
					<?php if ( $cateid==2 ) { ?><div class="b_card g_card clearfix">
	<span>基本信息：</span>
	<div class="g_inbox clearfix">
		<div class="g_sinput">
			<em>中文名称：</em>
			<input type="text" name="basic[cnname]" placeholder="中文名称" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["cnname"]); ?>" leng="10">
		</div>
		<div class="g_sinput">
			<em>职&ensp;&ensp;位：</em>
			<input type="text" name="basic[position]" placeholder="职位" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["position"]); ?>" leng="10">
		</div>
		<div class="g_sinput">
			<em>出生日期：</em>
			<?php
 $basic_ctime = ''; if ( isset($data['basic']['birthday']) && trim($data['basic']['birthday'])!='' ) { $basic_ctime = date('Y-m-d', strtotime($data['basic']['birthday'])); } ?>
			<input name="basic[birthday]" type="text" value="<?php echo ($basic_ctime); ?>" placeholder="创建时间" class="g_ip001 js_verify" id="basic_birthday">
		</div>
		<div class="g_sinput">
			<em>国&emsp;&emsp;籍：</em>
			<input type="text" name="basic[nationality]" placeholder="该人物当前的国籍" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["nationality"]); ?>" leng="10">
		</div>
		<div class="g_sinput">
			<em>籍&emsp;&emsp;贯：</em>
			<input type="text" name="basic[nativeplace]" placeholder="家乡指该人物的籍贯" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["nativeplace"]); ?>" leng="10">
		</div>
		<div class="g_sinput">
			<em>出&ensp;生&ensp;地：</em>
			<input type="text" name="basic[birthplace]" placeholder="出生地" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["birthplace"]); ?>" leng="10">
		</div>
		<div class="g_sinput">
			<em>民&emsp;&emsp;族：</em>
			<input type="text" name="basic[nation]" placeholder="民族" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["nation"]); ?>" leng="10">
		</div>
		<div class="g_sinput">
			<em>性&emsp;&emsp;别：</em>
			<select name="basic[sex]" class="g_s001 g_s002">
				<option value="">未选择</option>
				<?php
 foreach ( $dicts['BASIC_SEX'] as $i => $item ) { $selected = ( $data['basic']['sex']==$item['name'] ) ? ' selected' : ''; ?>
				<option value="<?php echo ($item['name']); ?>"<?php echo ($selected); ?>><?php echo ($item['name']); ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="g_sinput">
			<em>毕业院校：</em>
			<input type="text" name="basic[college]" placeholder="毕业院校，最多10个汉字" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["college"]); ?>" leng="10">
		</div>
		<div class="g_sinput">
			<em>代表作品：</em>
			<input type="text" name="basic[representative]" placeholder="代表作品，最多50个汉字" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["representative"]); ?>" leng="50">
		</div>
		<div class="g_sinput">
			<em>所获荣誉：</em>
			<input type="text" name="basic[honour]" placeholder="所获荣誉，最多50个汉字" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["honour"]); ?>" leng="50">
		</div>
		<div class="g_sinput">
			<em>主要成就：</em>
			<input type="text" name="basic[achievement]" placeholder="主要成就，最多50个汉字" class="g_ip001 js_verify" value="<?php echo ($data["basic"]["achievement"]); ?>" leng="50">
		</div>
	</div>
</div>
<?php } ?>
					<div class="b_card clearfix">
						<span>媒体机构：</span>
						<div class="date inpBox">
							<input name="media" type="text" placeholder="媒体机构名称" class="inp w215" 
							value="<?php echo ($data["media"]); ?>">
						</div>
					</div>
					<div class="b_card">
						<span>媒体作者：</span>
						<div class="date inpBox">
							<input name="editorid" type="hidden" value="<?php echo ($userinfo["id"]); ?>">
							<input name="editor" type="text" placeholder="媒体作者姓名" class="inp w215" value="<?php if($data['editor'] != ''): echo ($data["editor"]); else: echo ($userinfo["truename"]); endif; ?>" needed="true" tooltip="[必填]">
						</div>
					</div>
				</div>

				<div class="y_subtit">发布设置</div>
				<div class="y_section">
					<div class="b_card clearfix">
						<span>定时发布：</span>
						<div class="date inpBox07">
							<input name="ptime" type="text" value="<?php if ( intval($data['ptime']) > 0 ) { echo date('Y-m-d H:i:s', intval($data['ptime'])); } ?>" class="inp w215" id="pubdate">
							<i></i><em>不更改时间立即发布；选择将来时间定时发布</em>
						</div>
					</div>
					<?php if ( !empty($recommend_auth) ) { ?>
					<div class="b_card clearfix">
						<span>推&emsp;&emsp;荐：</span>
						<div class="inpBox07">
						<?php
 $recommend_type = 'wiki_focus'; if ( intval($recommend_auth[$recommend_type])==1 ) { $flag = $dicts['FLAGS'][$recommend_type]['id']; $cfg = array( 'id' => $flag, 'name' => $dicts['FLAGS'][$recommend_type]['name'], ); echo form_recommend($recommends[$flag], $cfg); } $recommend_type = 'wiki_person'; if ( intval($recommend_auth[$recommend_type])==1 ) { $flag = $dicts['FLAGS'][$recommend_type]['id']; $cfg = array( 'id' => $flag, 'name' => $dicts['FLAGS'][$recommend_type]['name'], ); echo form_recommend($recommends[$flag], $cfg); } $recommend_type = 'wiki_company'; if ( intval($recommend_auth[$recommend_type])==1 ) { $flag = $dicts['FLAGS'][$recommend_type]['id']; $cfg = array( 'id' => $flag, 'name' => $dicts['FLAGS'][$recommend_type]['name'], ); echo form_recommend($recommends[$flag], $cfg); } ?>
						</div>
					</div>
					<?php } ?>
				</div>

				<div class="y_subtit">关联内容推荐</div>
				<div class="y_section">
					<div class="b_card g_card clearfix">
						<span>标&emsp;&emsp;签：</span>
						<div class="inpBox g_Box label">
							<div class="tags_content_new">
							<input name="tags_id" type="hidden" value="<?php echo trim($data['tagids'], ','); ?>" data-tags="tags_id">
							<input name="tags_name" type="hidden" value="<?php echo str_replace(' ', ',', $data['tags']); ?>" data-tags="tags_name">
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
						<textarea id="label_list" style="display: none;"></textarea>
						<div class="b_popBox" style="display: none;"></div>
					</div>
					<?php if ( $cateid==0 ) { ?>
					<div class="b_card clearfix">
						<span>相关资讯：</span>
						<div class="inpBox07">
							<div class="clearfix b_form">
								<input type="text" class="inp w293" placeholder="输入新闻ID">
								<input type="button" id="get_news" class="l_btn l_gray" value="添加">
							</div>
							<ul class="b_list01">
								<?php if(!empty($data["rel"]["news"])): if(is_array($data["rel"]["news"])): foreach($data["rel"]["news"] as $key=>$vo): ?><li class="clearfix">
									<input name="rel[news][][id]" type="hidden" value="<?php echo ($vo["id"]); ?>">
									<input name="rel[news][][title]" type="hidden" value="<?php echo ($vo["title"]); ?>">
									<input name="rel[news][][url]" type="hidden" value="<?php echo ($vo["url"]); ?>">
									<em><?php echo ($vo["title"]); ?></em>
									<a href="javascript:;" class="edit"></a>
									<i></i>
									<a target="_blank" href="<?php echo ($vo["url"]); ?>" class="link"></a>
									<i></i> <a href="javascript:;" class="del"></a>
								</li><?php endforeach; endif; endif; ?>
							</ul>
						</div>
					</div>
					<div class="b_card clearfix">
						<span>相关楼盘：</span>
						<div class="inpBox07">
							<div class="clearfix b_form">
								<input type="text" class="inp w245" placeholder="输入楼盘ID  （CITYEN+HID组成）">
								<input type="button" id="get_house" class="l_btn l_gray" value="添加">
							</div>
							<ul class="b_list02 clearfix">
								<?php if(!empty($data["rel"]["houses"])): if(is_array($data["rel"]["houses"])): foreach($data["rel"]["houses"] as $key=>$vo): ?><li hid="<?php echo ($vo["hid"]); ?>" site="<?php echo ($vo["site"]); ?>"><?php echo ($vo["name"]); ?>
									<input name="rel[houses][][site]" type="hidden" value="<?php echo ($vo["site"]); ?>">
									<input name="rel[houses][][hid]" type="hidden" value="<?php echo ($vo["hid"]); ?>">
									<input name="rel[houses][][name]" type="hidden" value="<?php echo ($vo["title"]); ?>">
									<input name="rel[houses][][url]" type="hidden" value="<?php echo ($vo["url"]); ?>">
									<a target="_blank" href="<?php echo ($vo["url"]); ?>" class="link-o"></a>
									<i></i><a href="javascript:;" class="del-o"></a>
								</li><?php endforeach; endif; endif; ?>
							</ul>
						</div>
					</div>
					<?php } ?>
					<div class="b_card clearfix">
						<span>相关企业：</span>
						<div class="inpBox07">
							<div class="clearfix b_form">
								<input type="text" class="inp w245" id="js_rela_company" placeholder="输入企业名称">
								<input type="button" id="get_house" class="l_btn l_gray js_rel_btn" value="添加">
							</div>
							<ul class="b_list02 clearfix">
							<?php
 if ( !empty($data['rel']['companies']) ) { foreach ( $data['rel']['companies'] as $i => $item ) { $id = intval($item['id']); $title = trim($item['title']); if ( $id > 0 && $title!='' ) { ?>
								<li><?php echo ($title); ?>
									<input name="rel[companies][<?php echo ($id); ?>][id]" type="hidden" value="<?php echo ($id); ?>">
									<input name="rel[companies][<?php echo ($id); ?>][title]" type="hidden" value="<?php echo ($title); ?>">
									<i></i><a href="javascript:;" class="del-o"></a>
								</li>
							<?php  } } } ?>
							</ul>
						</div>
					</div>
					<div class="b_card clearfix">
						<span>相关人物：</span>
						<div class="inpBox07">
							<div class="clearfix b_form">
								<input type="text" class="inp w245" id="js_rela_person" placeholder="输入人物名称">
								<input type="button" id="get_house" class="l_btn l_gray js_rel_btn" value="添加">
							</div>
							<ul class="b_list02 clearfix">
							<?php
 if ( !empty($data['rel']['figures']) ) { foreach ( $data['rel']['figures'] as $i => $item ) { $id = intval($item['id']); $title = trim($item['title']); if ( $id > 0 && $title!='' ) { ?>
								<li><?php echo ($title); ?>
									<input name="rel[figures][<?php echo ($id); ?>][id]" type="hidden" value="<?php echo ($id); ?>">
									<input name="rel[figures][<?php echo ($id); ?>][title]" type="hidden" value="<?php echo ($title); ?>">
									<i></i><a href="javascript:;" class="del-o"></a>
								</li>
							<?php  } } } ?>
							</ul>
						</div>
					</div>
					<?php if ( in_array($cateid, array(1,2) ) ) { ?>
					<div class="b_card g_card">
						<span>业 务 线：</span>
						<div class="inpBox g_Box">
						<?php
 $cfg = array('class'=>'w105', 'name'=>'business_line', 'id'=>'',); echo search_form_select($data['business_line'], $dicts['BUSINESS_LINES'], $cfg); ?>
						</div>
					</div>
					<div class="b_card g_card">
						<span>是否推荐：</span>
						<div class="inpBox07 g_Box">
						<?php
 $radios = array( 1 => array('id'=>1, 'title'=>'是'), 0 => array('id'=>0, 'title'=>'否'), ); $cfg = array('class'=>'w105', 'name'=>'is_recommended', 'id'=>'',); echo search_form_radio($data['is_recommended'], $radios, $cfg); ?>
						</div>
					</div>
					<?php if ( $cateid==1 ) { ?>
					<div class="b_card clearfix">
						<span>股票代码：</span>
						<div class="inpBox07">
							<div class="clearfix b_form">
							<?php
 $_t = explode('.', trim($data['company_stock_code']), 2); $data['listmarket'] = isset($_t[0]) ? trim($_t[0]) : ''; $data['listcode'] = isset($_t[1]) ? trim($_t[1]) : ''; $cfg = array('class'=>'w105', 'name'=>'listmarket', 'id'=>'',); echo search_form_select($data['listmarket'], $dicts['LISTMARKET'], $cfg); ?>
								<input type="text" name="listcode" class="inp w105" id="js_stock_code" placeholder="股票代码" value="<?php echo ($data["listcode"]); ?>">
							</div>
						</div>
					</div>
					<div class="b_card">
						<span>克尔瑞ID：</span>
						<?php
 $group_company = intval($data['company_parent_id'])==0; $allow_modify = false; if ( $group_company ) { $allow_modify = true; } ?>
						<div class="date inpBox">
							<input name="company_cric_id" type="text" placeholder="克尔瑞ID" class="inp w215" <?php echo $allow_modify ? '' : 'readonly="true"'; ?> value="<?php echo $data['company_cric_id'] ? $data['company_cric_id'] : ''; ?>">
						</div>
					</div>
					<div class="b_card">
						<span>上级公司：</span>
						<div class="date inpBox">
							<input name="company_parent_id" type="hidden" value="<?php echo ($data["company_parent_id"]); ?>">
							<input id="company_parent_name" type="text" placeholder="上级公司" class="inp w215" value="<?php echo ($data["company_parent_name"]); ?>"<?php if ( $isparent ) { echo ' disabled'; } ?>>
						</div>
					</div>
					<div class="b_card clearfix">
						<span>荣誉榜单：</span>
						<div class="inpBox07 ranklist">
							<div class="clearfix b_form">
								<input type="text" id="ranklist_title" class="inp w293" placeholder="活动名称不超过15个汉字">
								<input type="text" id="ranklist_url" class="inp w293" placeholder="榜单跳转链接">
								<input type="button" id="ranklist_add" class="l_btn l_gray" value="添加">
							</div>
							<?php if(!empty($data["ranklist"])): if(is_array($data["ranklist"])): foreach($data["ranklist"] as $i=>$vo): ?><div class="clearfix b_form">
								<input type="text" readonly="readonly" name="ranklist[<?php echo ($i); ?>][title]" class="inp w293" value="<?php echo ($vo["title"]); ?>">
								<input type="text" readonly="readonly" name="ranklist[<?php echo ($i); ?>][url]" class="inp w293" value="<?php echo ($vo["url"]); ?>">
								<input type="button" class="l_btn l_gray ranklist_del" value="删除">
							</div><?php endforeach; endif; endif; ?>
						</div>
					</div>
					<?php } ?>
					<?php if ( $cateid==2 ) { ?>
					<div class="b_card">
						<span>人物榜单：</span>
						<div class="date inpBox">
							<input name="person_rank_title" type="text" placeholder="活动名称不超过15个汉字" class="inp w215" value="<?php echo ($data["person_rank_title"]); ?>">
							<input name="person_rank_link" type="text" placeholder="榜单跳转链接" class="inp w215" value="<?php echo ($data["person_rank_link"]); ?>">
						</div>
					</div>
					<?php } ?>
					<?php } ?>
				</div>

				<div class="y_subtit">搜索引擎收录设置</div>
				<div class="y_section">
					<div class="b_card g_card clearfix">
						<span class='g_w100'>title<i class="g_star">*</i>： </span>
						<div class="inpBox">
							<input name="seo[title]" type="text" class="inp w245" value="<?php echo ($data["seo"]["title"]); ?>" needed="true" tooltip="[必填]">
							<input type="button" id="seo_title" class="seo_copy l_btn l_gray" value="复制标题内容">
						</div>
					</div>
					<div class="b_card g_card clearfix" >
						<span class='g_w100'>keywords<i class="g_star">*</i>：</span>
						<div class="inpBox label">
						    <input name="seo[keywords]" type="text" placeholder="输入标签" class="inp w190" needed="true" tooltip="[必填]" value="<?php echo ($data["seo"]["keywords"]); ?>">
						    <input type="button" id="seo_keywords" class="seo_copy l_btn l_gray" value="复制标签内容">
						</div>
					</div>
					<div class="b_card g_card clearfix">
						<span class='g_w100'>description<i class="g_star">*</i>：</span>
						<div class="inpBox07">
							<textarea name="seo[description]" class="y_areaBox" needed="true" tooltip="[必填]"><?php echo ($data["seo"]["description"]); ?></textarea>
							<input type="button" id="seo_description" class="seo_copy l_btn l_gray mrtop114" value="复制摘要内容">
						</div>
					</div>
				</div>

				<div class="b_btnBox clearfix">
					<input name="action_type" value="save" type="hidden">
					<a href="javascript:;" class="l_btn l_red saveBtn">保存</a>
					<a href="javascript:;" class="l_btn l_grn pubBtn">发布</a>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript" src="//cdn.leju.com/jQuery1.8.2;encyclopedia/js/base;encyclopedia/js/Pages/entry_editor.js?r"></script>
<script type="text/javascript" src="//cdn.leju.com/tags-fe/prd/tags_init_new.js"></script>
<script type="text/javascript" src="//cdn.leju.com/encyclopedia/js/Pages/editor2.0.js"></script>
			</div>
		</div>
	</div>
</body>
</html>