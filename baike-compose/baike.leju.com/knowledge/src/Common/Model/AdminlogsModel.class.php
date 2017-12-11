<?php
/**
 * 管理员后台操作日志
 */
namespace Common\Model;
use Think\Model;

// 定义审计日志使用的审计操作类型常量
// define constants start
	define('ADMIN_LOGIN_ACT', 		100);	// 管理员登录
	define('ADMIN_SYNC_ACT', 		101);	// 同步管理员
	define('ADMIN_ROLE_ACT', 		102);	// [x] 管理员角色变更
	define('ADMIN_CITY_ACT', 		105);	// 管理员城市权限变更
	define('ADMIN_REMOVE_ACT', 		103);	// [x] 管理员禁用
	define('ADMIN_RESTORE_ACT', 	104);	// [x] 管理员恢复
	define('ROLE_ADD_ACT', 			201);	// [x] 添加角色
	define('ROLE_MOD_ACT', 			202);	// [x] 修改角色
	define('ROLE_DEL_ACT', 			203);	// [x] 删除角色
	define('KBCATE_ADD_ACT', 		501);	// [x] 添加知识栏目
	define('KBCATE_MOD_ACT', 		502);	// [x] 修改知识栏目
	define('KBCATE_HIDE_ACT', 		503);	// [x] 禁用/启用 知识栏目
	define('KBCATE_DEL_ACT', 		504);	// [x] 删除知识栏目
	define('QACATE_ADD_ACT', 		601);	// [x] 添加问答栏目
	define('QACATE_MOD_ACT', 		602);	// [x] 修改问答栏目
	define('QACATE_HIDE_ACT', 		603);	// [x] 禁用/启用 问答栏目
	define('QACATE_DEL_ACT', 		604);	// [-] 删除问答栏目
	define('KB_ADDSAVE_ACT', 		701);	// [x] 添加并保存百科知识
	define('KB_ADDPUB_ACT', 		702);	// [x] 添加并发布百科知识
	define('KB_MODSAVE_ACT', 		711);	// [x] 修改并保存百科知识
	define('KB_MODPUB_ACT', 		712);	// [x] 修改并发布百科知识
	define('KB_DEL_ACT', 			704);	// [x] 删除百科知识
	define('KB_CLEAN_ACT', 			705);	// [x] 彻底删除百科知识
	define('WIKI_ADDSAVE_ACT', 		801);	// 添加百科词条
	define('WIKI_ADDPUB_ACT', 		802);	// 添加百科词条
	define('WIKI_MODSAVE_ACT', 		811);	// 修改百科词条 // 保存草稿
	define('WIKI_MODPUB_ACT', 		812);	// 修改百科词条 // 保存草稿
	define('WIKI_DEL_ACT', 			804);	// 删除百科词条
	define('WIKI_CLEAN_ACT', 		805);	// 彻底删除百科词条
	define('QA_MOD_ACT', 			902);	// 修改问答参数(分类和标签)
	define('QA_VERIFY_ACT', 		903);	// [x] 审核通过
	define('QA_RESTORE_ACT', 		904);	// [x] 恢复问答
	define('QA_REMOVE_ACT', 		905);	// [x] 删除问答
	define('QA_CONFIRM_ACT', 		906);	// [x] 确认导入问题
	define('QAA_VERIFY_ACT', 		1003);	// [x] 回复审核通过
	define('QAA_RESTORE_ACT', 		1004);	// [x] 恢复回答
	define('QAA_REMOVE_ACT', 		1005);	// [x] 删除回答
	define('RCMD_KB_TOP',			2001);	// 知识置顶
// define constants end

class AdminlogsModel extends Model {
	protected $trueTableName = 'adminlogs';

	protected $bulk = false;		// 是否批量操作
	protected $cacher = null;	// 队列缓存对象

	protected $acts = null;
	public function addLog( $uid=0, $act=0, $relid=0, $note='', $ctime=NOW_TIME ) {
		$data = array(
			'act' => intval($act),
			'admin' => intval($uid),
			'relid' => intval($relid),
			'ctime' => intval($ctime),
			'note' => trim($note),
		);
		// var_dump($data);
		// return true;
		return !!$this->data($data)->add();
	}

	public function helper( $record, $actid=0 ) {
		if ( $actid == 0 ) {
			$actid = intval($record['act']);
		}
		$acts = $this->initActs();
		if ( !array_key_exists($actid, $acts) ) {
			return false;
		}
		$action = $acts[$actid];
		$method = $action['ID'];
		if ( !method_exists($this, $method) ) {
			return false;
		}
		$msg = call_user_func_array(array($this, $method), array('record'=>$record));
		return $msg;
	}

	public function & initActs() {
		if ( is_null($this->acts) ) {
			$this->acts = array(
				ADMIN_LOGIN_ACT => array(
					'ID'=>'ADMIN_LOGIN_ACT',
					'NAME'=>'管理员登录',
					'FN'=>'',
				),
				ADMIN_SYNC_ACT => array(
					'ID'=>'ADMIN_SYNC_ACT',
					'NAME'=>'同步管理员',
					'FN'=>'',
				),
				ADMIN_ROLE_ACT => array(
					'ID'=>'ADMIN_ROLE_ACT',
					'NAME'=>'管理员角色变更',
					'FN'=>'',
				),
				ADMIN_CITY_ACT => array(
					'ID'=>'ADMIN_CITY_ACT',
					'NAME'=>'管理员城市权限变更',
					'FN'=>'',
				),
				ADMIN_REMOVE_ACT => array(
					'ID'=>'ADMIN_REMOVE_ACT',
					'NAME'=>'管理员禁用',
					'FN'=>'',
				),
				ADMIN_RESTORE_ACT => array(
					'ID'=>'ADMIN_RESTORE_ACT',
					'NAME'=>'管理员恢复',
					'FN'=>'',
				),
				ROLE_ADD_ACT => array(
					'ID'=>'ROLE_ADD_ACT',
					'NAME'=>'添加角色',
					'FN'=>'',
				),
				ROLE_MOD_ACT => array(
					'ID'=>'ROLE_MOD_ACT',
					'NAME'=>'修改角色',
					'FN'=>'',
				),
				ROLE_DEL_ACT => array(
					'ID'=>'ROLE_DEL_ACT',
					'NAME'=>'删除角色',
					'FN'=>'',
				),
				KBCATE_ADD_ACT => array(
					'ID'=>'KBCATE_ADD_ACT',
					'NAME'=>'添加知识栏目',
					'FN'=>'',
				),
				KBCATE_MOD_ACT => array(
					'ID'=>'KBCATE_MOD_ACT',
					'NAME'=>'修改知识栏目',
					'FN'=>'',
				),
				KBCATE_HIDE_ACT => array(
					'ID'=>'KBCATE_HIDE_ACT',
					'NAME'=>'启用/禁用知识栏目',
					'FN'=>'',
				),
				KBCATE_DEL_ACT => array(
					'ID'=>'KBCATE_DEL_ACT',
					'NAME'=>'删除知识栏目',
					'FN'=>'',
				),
				QACATE_ADD_ACT => array(
					'ID'=>'QACATE_ADD_ACT',
					'NAME'=>'添加问答栏目',
					'FN'=>'',
				),
				QACATE_MOD_ACT => array(
					'ID'=>'QACATE_MOD_ACT',
					'NAME'=>'修改问答栏目',
					'FN'=>'',
				),
				QACATE_HIDE_ACT => array(
					'ID'=>'QACATE_HIDE_ACT',
					'NAME'=>'启用/禁用问答栏目',
					'FN'=>'',
				),
				QACATE_DEL_ACT => array(
					'ID'=>'QACATE_DEL_ACT',
					'NAME'=>'删除问答栏目',
					'FN'=>'',
				),
				/* 百科知识 */
				KB_ADDSAVE_ACT => array(
					'ID'=>'KB_ADDSAVE_ACT',
					'NAME'=>'添加并保存百科知识',
					'FN'=>'',
				),
				KB_ADDPUB_ACT => array(
					'ID'=>'KB_ADDPUB_ACT',
					'NAME'=>'添加并发布百科知识',
					'FN'=>'',
				),
				KB_MODSAVE_ACT => array(
					'ID'=>'KB_MODSAVE_ACT',
					'NAME'=>'修改并保存百科知识',
					'FN'=>'',
				),
				KB_MODPUB_ACT => array(
					'ID'=>'KB_MODPUB_ACT',
					'NAME'=>'修改并发布百科知识',
					'FN'=>'',
				),
				KB_DEL_ACT => array(
					'ID'=>'KB_DEL_ACT',
					'NAME'=>'删除百科知识',
					'FN'=>'',
				),
				KB_CLEAN_ACT => array(
					'ID'=>'KB_CLEAN_ACT',
					'NAME'=>'彻底删除百科知识',
					'FN'=>'',
				),
				/* 百科知识 */
				WIKI_ADDSAVE_ACT => array(
					'ID'=>'WIKI_ADDSAVE_ACT',
					'NAME'=>'添加并保存百科词条',
					'FN'=>'',
				),
				WIKI_ADDPUB_ACT => array(
					'ID'=>'WIKI_ADDPUB_ACT',
					'NAME'=>'添加并发布百科词条',
					'FN'=>'',
				),
				WIKI_MODSAVE_ACT => array(
					'ID'=>'WIKI_MODSAVE_ACT',
					'NAME'=>'修改并保存百科词条',
					'FN'=>'',
				),
				WIKI_MODPUB_ACT => array(
					'ID'=>'WIKI_MODPUB_ACT',
					'NAME'=>'修改并发布百科词条',
					'FN'=>'',
				),
				WIKI_DEL_ACT => array(
					'ID'=>'WIKI_DEL_ACT',
					'NAME'=>'删除百科词条',
					'FN'=>'',
				),
				WIKI_CLEAN_ACT => array(
					'ID'=>'WIKI_CLEAN_ACT',
					'NAME'=>'彻底删除百科词条',
					'FN'=>'',
				),

				QA_MOD_ACT => array(
					'ID'=>'QA_MOD_ACT',
					'NAME'=>'修改问答参数',
					'FN'=>'',
				),
				QA_VERIFY_ACT => array(
					'ID'=>'QA_VERIFY_ACT',
					'NAME'=>'审核通过',
					'FN'=>'',
				),
				QA_RESTORE_ACT => array(
					'ID'=>'QA_RESTORE_ACT',
					'NAME'=>'恢复问答',
					'FN'=>'',
				),
				QA_REMOVE_ACT => array(
					'ID'=>'QA_REMOVE_ACT',
					'NAME'=>'删除问答',
					'FN'=>'',
				),
				QA_CONFIRM_ACT => array(
					'ID'=>'QA_CONFIRM_ACT',
					'NAME'=>'确认导入问题',
					'FN'=>'',
				),
				QAA_VERIFY_ACT => array(
					'ID'=>'QAA_VERIFY_ACT',
					'NAME'=>'审核回答',
					'FN'=>'',
				),
				QAA_RESTORE_ACT => array(
					'ID'=>'QAA_RESTORE_ACT',
					'NAME'=>'恢复回答',
					'FN'=>'',
				),
				QAA_REMOVE_ACT => array(
					'ID'=>'QAA_REMOVE_ACT',
					'NAME'=>'删除回答',
					'FN'=>'',
				),
			);
		}
		return $this->acts;
	}

	// define format msg function start
		// 管理员管理审计消息数据格式化 start
			// 管理员登录
			public function ADMIN_LOGIN_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['user']
						 . '('.$R['note']['id'].') 通过 统一登录系统认证登录 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['user']
						 . '('.$R['note']['id'].') 通过 统一登录系统认证登录 操作失败';
				}
				return $msg;
			}

			// 同步管理员
			public function ADMIN_SYNC_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['user']
						 . '('.$R['note']['id'].') 通过 统一登录系统同步管理帐户 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['user']
						 . '('.$R['note']['id'].') 通过 统一登录系统同步管理帐户 操作失败';
				}
				return $msg;
			}

			// 管理员角色变更
			public function ADMIN_ROLE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor']
						 . ' 将 '.$R['note']['user'].'('.$R['note']['userid'].') 的管理员的角色 '
						 . $R['note']['oldrolename'] . '(#'.$R['note']['oldroleid'].') 变更为 '
						 . $R['note']['rolename'] . '(#'.$R['note']['roleid'].') 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor']
						 . ' 将编号为 '.$R['note']['userid'].' 的管理员的角色变更操作失败 ';
				}
				return $msg;
			}

			// 管理员城市权限变更
			public function ADMIN_CITY_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'].' 将'
						 . $R['note']['user'].'('.$R['note']['userid'].') 的管理员的城市操作权限 '
						 . $R['note']['oldcityname'] . '(#'.$R['note']['oldcity'].') 变更为 '
						 . $R['note']['cityname'] . '(#'.$R['note']['city'].') 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor']
						 . ' 将编号为 '.$R['note']['userid'].' 的管理员的城市操作权限变更 操作失败 ';
				}
				return $msg;
			}

			// 管理员禁用
			public function ADMIN_REMOVE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 删除 '
						 . $R['note']['user'].'('.$R['note']['userid'].') 的管理员 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor']
						 . ' 删除编号为 ' . $R['note']['userid'].' 的管理员 操作失败';
				}
				return $msg;
			}

			// 管理员禁用
			public function ADMIN_RESTORE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 恢复编号为 '.$R['note']['userid'].' 的管理员';
				$msg .= $status
						? ' '.$R['note']['user'].' 操作成功'
						: ' 操作失败';
				return $msg;
			}
		// 管理员管理审计消息数据格式化 end

		// 角色管理审计消息数据格式化 start
			// 添加角色
			public function ROLE_ADD_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建 '.$R['note']['rolename'].' 的角色';
				$msg .= $status
						? ' 操作成功'
						: ' 操作失败';
				return $msg;
			}

			// 修改角色
			public function ROLE_MOD_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 修改 '.$R['note']['rolename'].' 的角色';
				$msg .= $status
						? ' 操作成功'
						: ' 操作失败';
				return $msg;
			}

			// 删除角色
			public function ROLE_DEL_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 删除 '.$R['note']['rolename'].' 的角色';
				$msg .= $status
						? ' 操作成功'
						: ' 操作失败';
				return $msg;
			}
		// 角色管理审计消息数据格式化 end

		// 知识栏目系统审计消息数据格式化 start
			// 添加知识栏目
			public function KBCATE_ADD_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建 '
						 . $R['note']['catename'].' 的知识 '.$R['note']['level'].'级 栏目 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建知识栏目 操作失败';
				}
				return $msg;
			}

			// 修改知识栏目
			public function KBCATE_MOD_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 修改 '
						 . $R['note']['catename'].' 的知识栏目 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 修改知识栏目';
					$msg .= isset($R['note']['catename'])
						  ? ' '.$R['note']['catename'].' 操作失败'
						  : ' 操作失败';
				}
				return $msg;
			}

			// 启用/禁用 知识栏目
			public function KBCATE_HIDE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor']
						 . ' ' .$R['note']['action']. ' '
						 . $R['note']['catename'].' 的知识栏目 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor']
						 . ' ' .$R['note']['action']. ' 知识栏目 操作失败';
				}
				return $msg;
			}

			// 删除知识栏目
			public function KBCATE_DEL_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 删除 '
						 . $R['note']['catename'].' 的知识栏目及子栏目 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor']
						 . ' 删除知识栏目 操作失败';
				}
				return $msg;
			}
		// 知识栏目系统审计消息数据格式化 end

		// 问答栏目系统审计消息数据格式化 start
			// 添加问答栏目
			public function QACATE_ADD_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建 '
						 . $R['note']['catename'].' 的问答 '.$R['note']['level'].'级 栏目 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建问答栏目 操作失败';
				}
				return $msg;
			}

			// 修改问答栏目
			public function QACATE_MOD_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 修改 '
						 . $R['note']['catename'].' 的知识栏目 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 修改知识栏目';
					$msg .= isset($R['note']['catename'])
						  ? ' '.$R['note']['catename'].' 操作失败'
						  : ' 操作失败';
				}
				return $msg;
			}

			// 启用/禁用 问答栏目
			public function QACATE_HIDE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor']
						 . ' ' .$R['note']['action']. ' '
						 . $R['note']['catename'].' 的问答栏目 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor']
						 . ' ' .$R['note']['action']. ' 问答栏目 操作失败';
				}
				return $msg;
			}

			// 删除问答栏目 :: 问答栏目 暂时不支持删除
			public function QACATE_DEL_ACT ( $R ) {
				$msg = '';
				return $msg;
			}
		// 问答栏目系统审计消息数据格式化 end

		// 知识系统审计消息数据格式化 end
			// 添加并保存百科知识
			public function KB_ADDSAVE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建 '
						 . $R['note']['title'].' 的知识内容 保存草稿 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建知识内容 保存草稿 操作失败';
				}
				return $msg;
			}
			// 添加并发布百科知识
			public function KB_ADDPUB_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建 '
						 . $R['note']['title'].' 的知识内容 发布 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建知识内容 发布 操作失败';
				}
				return $msg;
			}
			// 修改并保存百科知识
			public function KB_MODSAVE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 编辑 '
						 . $R['note']['title'].' 的知识内容 保存草稿 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 编辑知识内容 保存草稿 操作失败';
				}
				return $msg;
			}
			// 修改并发布百科知识
			public function KB_MODPUB_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 编辑 '
						 . $R['note']['title'].' 的知识内容 发布 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 编辑知识内容 发布 操作失败';
				}
				return $msg;
			}

			// 删除百科知识
			public function KB_DEL_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 对 '
						 . $R['note']['title'].' 的知识内容 删除 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 对 '
						 . $R['note']['title'].' 的知识内容 删除 操作失败';
				}
				return $msg;
			}

			// 彻底删除百科知识
			public function KB_CLEAN_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 对 '
						 . $R['note']['title'].' 的知识内容 彻底删除 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 对 '
						 . $R['note']['title'].' 的知识内容 彻底删除 操作失败';
				}
				return $msg;
			}
		// 知识系统审计消息数据格式化 end

		// 百科系统审计消息数据格式化 start
			// 添加并保存百科知识
			public function WIKI_ADDSAVE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建 '
						 . $R['note']['title'].' 的词条内容 保存草稿 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建词条内容 保存草稿 操作失败';
				}
				return $msg;
			}
			// 添加并发布百科知识
			public function WIKI_ADDPUB_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建 '
						 . $R['note']['title'].' 的词条内容 发布 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 创建词条内容 发布 操作失败';
				}
				return $msg;
			}
			// 修改并保存百科知识
			public function WIKI_MODSAVE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 编辑 '
						 . $R['note']['title'].' 的词条内容 保存草稿 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 编辑词条内容 保存草稿 操作失败';
				}
				return $msg;
			}
			// 修改并发布百科知识
			public function WIKI_MODPUB_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 编辑 '
						 . $R['note']['title'].' 的词条内容 发布 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 编辑词条内容 发布 操作失败';
				}
			
				return $msg;
			}

			// 删除百科知识
			public function WIKI_DEL_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 对 '
						 . $R['note']['title'].' 的词条内容 删除 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 对 '
						 . $R['note']['title'].' 的词条内容 删除 操作失败';
				}
				return $msg;
			}

			// 彻底删除百科知识
			public function WIKI_CLEAN_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 对 '
						 . $R['note']['title'].' 的词条内容 彻底删除 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 对 '
						 . $R['note']['title'].' 的词条内容 彻底删除 操作失败';
				}
				return $msg;
			}
		// 百科系统审计消息数据格式化 end

		// 问答系统审计消息数据格式化 start
			// 修改问答参数(分类和标签)
			public function QA_MOD_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 修改 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题属性 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 修改 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题属性 操作失败';
				}
				return $msg;
			}	

			// 审核通过
			public function QA_VERIFY_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 审核 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 审核 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题 操作失败';
				}
				return $msg;
			}

			// 恢复问答
			public function QA_RESTORE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 恢复 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 恢复 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题 操作失败';
				}
				return $msg;
			}

			// 删除问答
			public function QA_REMOVE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 删除 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 删除 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题 操作失败';
				}
				return $msg;
			}

			// 确认导入问题
			public function QA_CONFIRM_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 导入 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 导入 '
						 . $R['note']['title'].'('.$R['note']['id'].') 的问题 操作失败';
				}
				return $msg;
			}

			// 回复审核通过
			public function QAA_VERIFY_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 审核 '
						 . $R['note']['title'].'('.$R['note']['qid'].') 的问题的回复 ('
						 . $R['note']['id'].') 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 审核 '
						 . '编号为('.$R['note']['id'].') 的问题回复 操作失败';
				}
				return $msg;
			}

			// 恢复回答
			public function QAA_RESTORE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 恢复 '
						 . $R['note']['title'].'('.$R['note']['qid'].') 的问题的回复 ('
						 . $R['note']['id'].') 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 恢复 '
						 . '编号为('.$R['note']['id'].') 的问题回复 操作失败';
				}
				return $msg;
			}

			// 删除回答
			public function QAA_REMOVE_ACT ( $R ) {
				$status = $R['note']['status'];
				$ctime = intval($R['ctime']);
				if ( $status ) {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 删除 '
						 . $R['note']['title'].'('.$R['note']['qid'].') 的问题的回复 ('
						 . $R['note']['id'].') 操作成功';
				} else {
					$msg = date('[Y年m月d日 H点m分s秒]', $ctime).' '.$R['note']['actor'] . ' 删除 '
						 . '编号为('.$R['note']['id'].') 的问题回复 操作失败';
				}
				return $msg;
			}
		// 问答系统审计消息数据格式化 end
	// define format msg function end

}
