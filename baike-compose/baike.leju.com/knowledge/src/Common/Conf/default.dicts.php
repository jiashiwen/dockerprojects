<?php
/**
 * 基础字典数据
 *
 */
// 后台使用的状态字典
return array(
	// 未登录的游客信息
	'GUEST' => array(
		'uid'			=> 0,
		'username'		=> '乐居网友',
		'headurl'		=> 'http://cdn.leju.com/comment-fe/img/user.png',
		'ctime'			=> 946656000,
	),
	'KNOWLEDGE' => array(
		// 知识内容状态字典
		'STATUS' => array(
			9 => array( 'id' => 9, 'name' => '已发布', 'class'=>'l_grn2', ),
			2 => array( 'id' => 2, 'name' => '定时发布', 'class'=>'l_grn1', ),
			1 => array( 'id' => 1, 'name' => '草稿', 'class'=>'l_org', ),
			0 => array( 'id' => 0, 'name' => '未发布', 'class'=>'l_gray', ),
			-1 => array( 'id' => -1, 'name' => '已删除', 'class'=>'l_red', ),
		),
		// 知识内容来源类型字典
		'TYPES' => array(
			0 => array( 'id' => 0, 'name' => '原创', 'class'=>'', ),
			2 => array( 'id' => 2, 'name' => '转载', 'class'=>'', ),
			1 => array( 'id' => 1, 'name' => '收录', 'class'=>'', ),
		),
		// 知识推荐位标志
		'FLAGS' => array(
			'focus' => array('id'=>1, 'flag'=>'focus', 'name'=>'首页焦点图'),
			'top' => array('id'=>2, 'flag'=>'top', 'name'=>'栏目列表置顶'),
		),
	),
	// 百科字典
	'WIKI' => array(
		// 业务线字典
		'BUSINESS_LINES' => array(
			1 => array('id'=>'1', 'name'=>'新房', ),
			2 => array('id'=>'2', 'name'=>'二手房', ),
			3 => array('id'=>'3', 'name'=>'家居', ),
			4 => array('id'=>'4', 'name'=>'抢工长', ),
		),
		// 百科词条类别字典
		'CATE' => array(
			0 => array('id'=>'0', 'name'=>'普通', ),
			1 => array('id'=>'1', 'name'=>'企业', ),
			2 => array('id'=>'2', 'name'=>'人物', ),
			// 原 Java 服务使用的 "0" => "人物", "1" => "机构"
		),
		// [后台使用] 百科词条内容状态
		'STATUS' => array(
			'9' => array( 'id' => '9', 'name' => '已发布', 'class'=>'', ),
			'2' => array( 'id' => '2', 'name' => '定时发布', 'class'=>'', ),
			'1' => array( 'id' => '1', 'name' => '草稿', 'class'=>'', ),
			'0' => array( 'id' => '0', 'name' => '未发布', 'class'=>'', ),
			'-1' => array( 'id' => '-1', 'name' => '已删除', 'class'=>'', ),
		),
		// [后台使用] 百科词条来源
		'SOURCE' => array(
			0 => array('id'=>'0', 'name'=>'原创', ),
			1 => array('id'=>'1', 'name'=>'收录', ),
			2 => array('id'=>'2', 'name'=>'CRIC', ),
		),
		// [后台使用] 百科词条列表显示顺序
		'SORT' => array(
			0 => array('id'=>'0', 'name'=>'最少', ),
			1 => array('id'=>'1', 'name'=>'最多', ),
		),
		// 百科词条推荐位标志
		'FLAGS' => array(
			'wiki_focus' => array('id'=>1, 'flag'=>'wiki_focus', 'name'=>'首页焦点图'),
			'wiki_person' => array('id'=>2, 'flag'=>'wiki_person', 'name'=>'首页名人'),
			'wiki_company' => array('id'=>3, 'flag'=>'wiki_company', 'name'=>'首页名企'),
		),
		// 百科词条基础信息类型中的属性值字典
		'BASIC' => array(
			// 1 企业
			1 => array(
				'enname' => array( 'title'=>'外文名', 'name'=>'enname', ),
				'stname' => array( 'title'=>'简称', 'name'=>'stname', ),
				'ctime' => array( 'title'=>'创建时间', 'name'=>'ctime', 'show'=>'showtime', ),
				'city' => array( 'title'=>'城市', 'name'=>'city', ),
				'homepage' => array( 'title'=>'官方网站', 'name'=>'homepage', ),
				'listmarket' => array( 'title'=>'上市市场', 'name'=>'listmarket', ),
			),
			// 2 人物
			2 => array(
				'cnname' => array('title'=>'中文名称', 'name'=>'cnname', ),
				'position' => array('title'=>'职位', 'name'=>'position', ),
				'birthday' => array('title'=>'出生日期', 'name'=>'birthday', 'show'=>'showtime', ),
				'nationality' => array('title'=>'国籍', 'name'=>'nationality', ),
				'nativeplace' => array('title'=>'籍贯', 'name'=>'nativeplace', ),
				'birthplace' => array('title'=>'出生地', 'name'=>'birthplace', ),
				'nation' => array('title'=>'民族', 'name'=>'nation', ),
				'sex' => array('title'=>'性别', 'name'=>'sex', ),
				'college' => array('title'=>'毕业院校', 'name'=>'college', 'verify_len30'=>true, ),
				'representative' => array('title'=>'代表作品', 'name'=>'representative', 'verify_len30'=>true, ),
				'honour' => array('title'=>'所获荣誉', 'name'=>'honour', 'verify_len30'=>true, ),
				'achievement' => array('title'=>'主要成就', 'name'=>'achievement', 'verify_len30'=>true, ),
			),
		),
		// 百科词条中，企业的上市公司字典
		'LISTMARKET' => array(
			'SH' => array('id'=>'SH', 'name'=>'上交所',),
			'SZ' => array('id'=>'SZ', 'name'=>'深交所',),
			'HK' => array('id'=>'HK', 'name'=>'港交所',),
			// '' => array(''=>'', 'name'=>'纽交所',),
			// '' => array(''=>'', 'name'=>'纳斯达克',),
		),
		// 性别选项
		'BASIC_SEX' => array(
			1 => array( 'name'=>'男', 'value'=>1, ),
			0 => array( 'name'=>'女', 'value'=>0, ),
			2 => array( 'name'=>'其它', 'value'=>2, ),
		),
		// 前端统计代码
		'STATS_CODE' => array(
			'SHOW' => array(
				'PC' => array(
					'level1' => 'pc_fcbk',
					'level2' => array(
						0 => '', 	// 普通
						1 => 'wd_bkqy',	// 企业
						2 => 'wd_bkrw',	// 人物
					),
					'level3' => array(
						0 => '', 	// 普通
						1 => '',	// 企业
						2 => '',	// 人物
					),
					'_rel' => array(
						'news' => array( // 相关新闻
							0 => '',
							1 => '#wt_source=pc_bkqy_xgxw',	// 企业
							2 => '#wt_source=pc_bkrw_xgxw',	// 人物
						),
						'album' => array( // 图册
							0 => '',
							1 => '#wt_source=pc_bkqy_picture',	// 企业
							2 => '#wt_source=pc_bkrw_picture',	// 人物
						),
						'hudong' => array( // 互动百科
							0 => '',
							1 => '#wt_source=pc_bkqy_hdbk',	// 企业
							2 => '#wt_source=pc_bkrw_hdbk',	// 人物
						),
						'companies' => array( // 相关公司
							0 => '',
							1 => '#wt_source=pc_bkqy_xggs',	// 企业
							2 => '#wt_source=pc_bkrw_xggs',	// 人物
						),
						'figures' => array( // 相关人物
							0 => '',
							1 => '#wt_source=pc_bkqy_xgrw',	// 企业
							2 => '#wt_source=pc_bkrw_xgrw',	// 人物
						),
					),
				),
				'TOUCH' => array(
					'level1' => 'baike',
					'level2' => array(
						0 => '', 	// 普通
						1 => 'wd_bkqy',	// 企业
						2 => 'wd_bkrw',	// 人物
					),
					'level3' => array(
						0 => '', 	// 普通
						1 => '',	// 企业
						2 => '',	// 人物
					),
					'_rel' => array(
						'news' => array( // 相关新闻
							0 => '',
							1 => '#ln=touch_bkqy_xgxw',	// 企业
							2 => '#ln=touch_bkrw_xgxw',	// 人物
						),
						'album' => array( // 图册
							0 => '',
							1 => '#ln=touch_bkqy_picture',	// 企业
							2 => '#ln=touch_bkrw_picture',	// 人物
						),
					),
				),
			),
			'ALBUM' => array(
				'PC' => array(
					'level1' => 'pc_fcbk',
					'level2' => array(
						0=>'', 
						1=>'wd_picture',
						2=>'wd_picture',
					),
					'level3' => '',
				),
				'TOUCH' => array(
					'level1' => 'baike',
					'level2' => array(
						0=>'', 
						1=>'wd_picture',
						2=>'wd_picture',
					),
					'level3' => '',
				),
			),
		),
	),
	// 问答字典
	'ASK' => array(
		'SRC' => array(
			  0 => array( 'id'=>0, 'name'=> '收录', ),
			 51 => array( 'id'=>0, 'name'=> 'Web', ),
			101 => array( 'id'=>0, 'name'=> 'App', ),
			151 => array( 'id'=>0, 'name'=> 'Wap', ),
			152 => array( 'id'=>0, 'name'=> 'Wap 楼盘详情页', ),
		),
		'QUESTION_STATUS' => array(
			0  => array( 'id'=>0,  'name'=>'已删除', ),
			11 => array( 'id'=>11, 'name'=>'待确认', ),
			12 => array( 'id'=>12, 'name'=>'待审核', ),
			21 => array( 'id'=>21, 'name'=>'待解决', ),
			22 => array( 'id'=>22, 'name'=>'已回答', ),
			23 => array( 'id'=>23, 'name'=>'已采纳', ),
		),
		'ANSWER_STATUS' => array(
			0  => array( 'id'=>0,  'name'=>'已删除', ),
			11 => array( 'id'=>11, 'name'=>'待确认', ),
			12 => array( 'id'=>12, 'name'=>'待审核', ),
			21 => array( 'id'=>21, 'name'=>'待解决', ),
			22 => array( 'id'=>22, 'name'=>'已置顶', ),
			23 => array( 'id'=>23, 'name'=>'已采纳', ),
		),
	),
	// 乐道问答字典
	'LD' => [
		'SOURCE' => [
			0 => ['id'=>0, 'name'=>'PC'],
			1 => ['id'=>1, 'name'=>'WAP'],
		],
		'QUESTION_STATUS' => [
			0 => ['id'=>0, 'name'=>'已删除'],
			1 => ['id'=>1, 'name'=>'未审核'],
			2 => ['id'=>2, 'name'=>'已审核'],
		],
		'ANSWER_STATUS' => [
			0 => ['id'=>0, 'name'=>'已删除'],
			1 => ['id'=>1, 'name'=>'未审核'],
			2 => ['id'=>2, 'name'=>'已审核'],
		],
		'QTOP' => [
			0=>['id'=>0,'name'=>'未置顶'],
			1=>['id'=>1,'name'=>'已置顶'],
		],
		'QESSENCE' => [
			0=>['id'=>0,'name'=>'未精华'],
			1=>['id'=>1,'name'=>'已精华'],
		],
		'COMPANY_STATUS' => [
			0=>['id'=>0, 'name'=>'未开启'],
			1=>['id'=>1, 'name'=>'已开启'],
		],
	],
	// 乐道问答字典
	'PN' => [
		'SOURCE' => [
			0 => ['id'=>0, 'name'=>'PC'],
			1 => ['id'=>1, 'name'=>'WAP'],
		],
		'QUESTION_STATUS' => [
			0 => ['id'=>0, 'name'=>'已删除'],
			1 => ['id'=>1, 'name'=>'未审核'],
			2 => ['id'=>2, 'name'=>'已审核'],
		],
		'ANSWER_STATUS' => [
			0 => ['id'=>0, 'name'=>'已删除'],
			1 => ['id'=>1, 'name'=>'未审核'],
			2 => ['id'=>2, 'name'=>'已审核'],
		],
		'QTOP' => [
			0=>['id'=>0,'name'=>'未置顶'],
			1=>['id'=>1,'name'=>'已置顶'],
		],
		'QESSENCE' => [
			0=>['id'=>0,'name'=>'未精华'],
			1=>['id'=>1,'name'=>'已精华'],
		],
		'PERSON_STATUS' => [
			0=>['id'=>0, 'name'=>'未开启'],
			1=>['id'=>1, 'name'=>'已开启'],
		],
	],
);

