<?php

/*
 * 后台路由权限配置
 * @document：name对应页面渲染的中文显示名，child是子分类，其中key是路由的action，auth_id是对应的自定义权限验证名;
 * @document：如果有根据参数判断的路由则auth_id规则为(例)：system/roles/list:2,3
 * TODO：各个业务修改自己的auth_id和tag
 */

return array(
	array(
		'all' => true, //是否显示全选&反选
		'special' => true, //特殊，只渲染子栏目
		'tag' => '知识管理',
		'tag_en' => 'knowledge',
		'child' => array(
			array(
				'tag' => '知识管理',
				'auth' => array(
					array(
						'auth_id' => 'list',
						'auth_tag' => '查看',
						'classname' => 'list' //前端JS渲染全选反选用
					),
					array(
						'auth_id' => 'add',
						'auth_tag' => '添加',
						'classname' => 'add'
					),
					array(
						'auth_id' => 'edit',
						'auth_tag' => '修改',
						'classname' => 'edit'
					),
					array(
						'auth_id' => 'del',
						'auth_tag' => '删除',
						'classname' => 'del'
					)
				)
			)
		)
	),
	array(
		'all' => true, //是否显示全选&反选
		'special' => true, //特殊，只渲染子栏目
		'tag' => '问答管理',
		'tag_en' => 'question',
		'child' => array(
			array(
				'tag' => '问答管理',
				'auth' => array(
					array(
						'auth_id' => 'list',
						'auth_tag' => '查看',
						'classname' => 'list' //前端JS渲染全选反选用
					),
					array(
						'auth_id' => 'add',
						'auth_tag' => '添加',
						'classname' => 'add'
					),
					array(
						'auth_id' => 'edit',
						'auth_tag' => '修改',
						'classname' => 'edit'
					),
					array(
						'auth_id' => 'del',
						'auth_tag' => '删除',
						'classname' => 'del'
					)
				)
			)
		)
	),
	array(
		'all' => false,
		'special' => false,
		'tag' => '词条管理',
		'tag_en' => 'wiki',
		'child' => array(
			array(
				'tag' => '词条管理',
				'auth' => array(
					array(
						'auth_id' => 'list',
						'auth_tag' => '查看',
						'classname' => 'list'
					),
					array(
						'auth_id' => 'add',
						'auth_tag' => '添加',
						'classname' => 'add'
					),
					array(
						'auth_id' => 'edit',
						'auth_tag' => '修改',
						'classname' => 'edit'
					),
					array(
						'auth_id' => 'del',
						'auth_tag' => '删除',
						'classname' => 'del'
					)
				)
			)
		)
	),
	array(
		'all' => true,
		'special' => false,
		'tag' => '权限管理',
		'tag_en' => 'role',
		'child' => array(
			array(
				'tag' => '角色管理',
				'auth' => array(
					array(
						'auth_id' => 'list',
						'auth_tag' => '查看',
						'classname' => 'list'
					),
					array(
						'auth_id' => 'add',
						'auth_tag' => '添加',
						'classname' => 'add'
					),
					array(
						'auth_id' => 'edit',
						'auth_tag' => '修改',
						'classname' => 'edit'
					),
					array(
						'auth_id' => 'del',
						'auth_tag' => '删除',
						'classname' => 'del'
					)
				)
			),
			array(
				'tag' => '用户管理',
				'auth' => array(
					array(
						'auth_id' => 'userlist',
						'auth_tag' => '查看',
						'classname' => 'list'
					),
					array(
						'auth_id' => 'useradd',
						'auth_tag' => '添加',
						'classname' => 'add'
					),
					array(
						'auth_id' => 'useredit',
						'auth_tag' => '修改',
						'classname' => 'edit'
					),
					array(
						'auth_id' => 'userdel',
						'auth_tag' => '删除',
						'classname' => 'del'
					)
				)
			),
		)
	),
	array(
		'all' => false,
		'special' => false,
		'tag' => '栏目管理',
		'tag_en' => 'cate',
		'child' => array(
			array(
				'tag' => '知识栏目',
				'auth' => array(
					array(
						'auth_id' => 'list',
						'auth_tag' => '查看',
						'classname' => 'list'
					),
					array(
						'auth_id' => 'add',
						'auth_tag' => '添加',
						'classname' => 'add'
					),
					array(
						'auth_id' => 'edit',
						'auth_tag' => '修改',
						'classname' => 'edit'
					),
					array(
						'auth_id' => 'del',
						'auth_tag' => '删除',
						'classname' => 'del'
					)
				)
			),
			array(
				'tag' => '问答栏目',
				'auth' => array(
					array(
						'auth_id' => 'qalist',
						'auth_tag' => '查看',
						'classname' => 'list'
					),
					array(
						'auth_id' => 'qaadd',
						'auth_tag' => '添加',
						'classname' => 'add'
					),
					array(
						'auth_id' => 'qaedit',
						'auth_tag' => '修改',
						'classname' => 'edit'
					),
					array(
						'auth_id' => 'qadel',
						'auth_tag' => '删除',
						'classname' => 'del'
					)
				)
			),			
		)
	),
	array( // 推荐管理
		'all' => false,
		'special' => false,
		'tag' => '推荐管理',
		'tag_en' => 'recommend',
		'child' => array(
			array(
				'tag' => '知识',
				'auth' => array(
					array(
						'auth_id' => 'focus',
						'auth_tag' => '焦点',
						'classname' => 'focus'
					),
					array(
						'auth_id' => 'top',
						'auth_tag' => '置顶',
						'classname' => 'top'
					),
				)
			),
			array(
				'tag' => '百科词条',
				'auth' => array(
					array(
						'auth_id' => 'wiki_focus',
						'auth_tag' => '首页焦点',
						'classname' => 'wiki_focus'
					),
					array(
						'auth_id' => 'wiki_person',
						'auth_tag' => '名人',
						'classname' => 'wiki_person'
					),
					array(
						'auth_id' => 'wiki_company',
						'auth_tag' => '名企',
						'classname' => 'wiki_company'
					),
				)
			),
			array(
				'tag' => '问答数据',
				'auth' => array(
					array(
						'auth_id' => 'qacompanytop',
						'auth_tag' => '企业置顶',
						'classname' => 'qacompanytop'
					),
				)
			),
		)
	),	// end 推荐管理

	array(
		'all' => false, //是否显示全选&反选
		'special' => false, //特殊，只渲染子栏目
		'tag' => '审计日志管理',
		'tag_en' => 'adminlogs',
		'child' => array(
			array(
				'tag' => '审计日志管理',
				'auth' => array(
					array(
						'auth_id' => 'list',
						'auth_tag' => '查看',
						'classname' => 'list' //前端JS渲染全选反选用
					),
				)
			)
		)
	),

);
