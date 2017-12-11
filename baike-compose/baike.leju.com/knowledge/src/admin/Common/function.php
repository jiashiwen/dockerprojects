<?php
/**
 * 后台程序使用的通用方法
 */

/**
 * 生成搜索表单的 选项控件
 *
 * @param $selected mixed 被选中的表单值
 * @param $options array 所有可选择的选项
 * @param $cfg array 表单属性配置参数
 * @param $def array 默认值
 * @return string 表单的 html 代码
 */
function search_form_select($selected='', $options=[], $cfg=[], $def=array('id'=>'', 'name'=>'请选择') ) {
	$html = array();
	// 表单属性
	array_push($html, '<select');
	isset($cfg['id']) && trim($cfg['id'])!='' && array_push($html, ' id="'.$cfg['id'].'"');
	isset($cfg['name']) && array_push($html, ' name="'.$cfg['name'].'"');
	isset($cfg['class']) && array_push($html, ' class="'.$cfg['class'].'"');
	array_push($html, '>');
	array_push($html, PHP_EOL);
	// 添加默认值
	$def!==false && array_unshift($options, $def);
	// 遍历所有 select 选项
	foreach ( $options as $id => $item ) {
		array_push($html, '<option value="'.$item['id'].'"');
		if ( strval($selected) === strval($item['id']) ) {
			array_push($html, ' selected');
		}
		array_push($html, '>');
		array_push($html, $item['name']);
		array_push($html, '</option>');
		array_push($html, PHP_EOL);
	}
	array_push($html, '</select>');
	return implode('', $html);
}


/**
 * 生成搜索表单的 单选控件
 *
 * @param $selected mixed 被选中的表单值
 * @param $options array 所有可选择的选项
 * @param $cfg array 表单属性配置参数
 * @param $def array 默认值
 * @return string 表单的 html 代码
 */
function search_form_radio($selected='', $options=[], $cfg=[] ) {
	$html = array();
	// 表单属性
	$name = $cfg['name'];
	$class = false;
	isset($cfg['class']) && $class = $cfg['class'];
	$show_label = true;
	isset($cfg['show_label']) && !$cfg['show_label'] && $show_label = false;
	// 添加默认值
	$def!==null && array_unshift($options, $def);
	// 遍历所有 select 选项
	foreach ( $options as $id => $item ) {
		$value = $item['id'];
		$_id = $name.'_'.$id;
		$title = trim($item['title']);
		array_push($html, '<h2 style="float:left; width:15%;">');
		array_push($html, '<input type="radio" ');
		if ( $selected == $value ) {
			array_push($html, 'checked ');
		}
		array_push($html, 'name="'.$name.'" value="'.$value.'" ');
		array_push($html, 'id="'.$_id.'" class="checkbox">');
		if ( $show_label && $title ) {
			array_push($html, '<b><label for="'.$_id.'">'.$title.'</label></b>');
		}
		array_push($html, '</h2>');
		array_push($html, PHP_EOL);
	}
	// var_dump($html, $selected, $cfg);
	return implode('', $html);
}

/**
 * 生成搜索表单的 输入框控件
 *
 * @param $inputed mixed 用户输入的值
 * @param $cfg array 表单属性配置参数
 * @param $def array 默认值
 * @return string 表单的 html 代码
 */
function search_form_input($inputed='', $cfg=[], $def=null) {
	$html = array();
	// 表单属性
	array_push($html, '<input type="text"');
	isset($cfg['id']) && trim($cfg['id'])!='' && array_push($html, ' id="'.$cfg['id'].'"');
	isset($cfg['class']) && array_push($html, ' class="'.$cfg['class'].'"');
	isset($cfg['name']) && array_push($html, ' name="'.$cfg['name'].'"');
	isset($cfg['placeholder']) && array_push($html, ' placeholder="'.$cfg['placeholder'].'"');
	$value = is_null($def) ? '' : strval($def);
	$inputed = strval($inputed);
	$inputed!='' && $value = $inputed;
	array_push($html, ' value="'.$value.'"');
	array_push($html, '>');
	return implode('', $html);
}



/**
 * 生成搜索表单的 时间选择框控件
 *
 * @param $inputed mixed 用户输入的值
 * @param $cfg array 表单属性配置参数
 * @param $def array 默认值
 * @return string 表单的 html 代码
 */
function search_form_timepicker($inputed='', $cfg=[], $def=null) {
	$html = array();
	// 表单属性
	array_push($html, '<input type="text"');
	isset($cfg['id']) && trim($cfg['id'])!='' && array_push($html, ' id="'.$cfg['id'].'"');
	isset($cfg['class']) && array_push($html, ' class="'.$cfg['class'].'"');
	isset($cfg['name']) && array_push($html, ' name="'.$cfg['name'].'"');
	isset($cfg['placeholder']) && array_push($html, ' placeholder="'.$cfg['placeholder'].'"');
	$value = is_null($def) ? '' : strval($def);
	$inputed = strval($inputed);
	$inputed!='' && $value = $inputed;
	array_push($html, ' value="'.$value.'"');
	array_push($html, '><i class="ty_date"></i>');
	return implode('', $html);
}


/**
 * 生成表单的推荐部件 推荐
 *
 * @param $inputed mixed 用户输入的值
 * @param $cfg array 表单属性配置参数
 * @param $def array 默认值
 * @return string 表单的 html 代码
 */
function form_recommend($data=[], $cfg=[], $def=[]) {
	if ( !isset($def['img']) || trim($def['img'])=='' ) {
		$def['img'] = '//'.$_SERVER['PS_ADMIN'].'/images/bImg02.png';
	}
	if ( !isset($def['name']) || trim($def['name'])=='' ) {
		$def['name'] = 'recommend';
	}

	$id = $def['name'];			// 控件名称
	$status = !empty($data);	// 是否已经推荐 有数据时为已推荐
	$flag = $cfg['id'];		// 推荐位标志
	$img = !isset($data) || trim($data['extra']['img'])=='' ? $def['img'] : $data['extra']['img'];

	if ( empty($data) ) {
		$data = array(
			'status' => false,
			'flag' => $flag,
			'extra' => array(
				'img' => $img,
				'title' => '',
			),
		);
	}

	$html = array();
	// 表单属性
	array_push($html, '<h2>');
	array_push($html, '<input name="'.$id.'['.$flag.'][status]" ');
	array_push($html, ' value="'.$flag.'"');
	array_push($html, ' id="'.$id.'_'.$flag.'_status"');
	array_push($html, ' type="checkbox" class="checkbox"');
	!!$status && array_push($html, ' checked');
	array_push($html, '>');
	array_push($html, '<b><label for="'.$id.'_'.$flag.'_status">'.$cfg['name'].'</label></b>');
	array_push($html, '<input name="'.$id.'['.$flag.'][extra][title]"');
	array_push($html, ' type="text" placeholder="自定义推荐标题" class="inp"');
	array_push($html, ' value="'.$data['extra']['title'].'">');
	array_push($html, '<img src="'.$img.'" class="sin_preview fileupload">');
	array_push($html, '<input type="button" class="l_btn l_gray js_imgcut_btn" value="自定义图片">');
	array_push($html, '<input type="file" class="imgcut none" accept="image/png,image/jpg" value="">');
	array_push($html, ' <em class="y_lh">请选择jpg、gif格式，小于2M的图<br/>默认取标题和配图，可以定义显示标题和图片</em>');
	array_push($html, '<input name="'.$id.'['.$flag.'][extra][img]"');
	array_push($html, ' class="imgurl" value="'.$img.'" style="display: none;">');
	array_push($html, '</h2>');
	return implode('', $html);



}
