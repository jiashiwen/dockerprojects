<?php
/**
 * 百科数据模型
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class WikiModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'wiki';

	/**
	 * 判断百科词条数据是否重复
	 *
	 */
	public function isRepeat ( $data ) {
		$where = array();
		// if ( !isset($data['cateid']) || intval($data['cateid'])<=0 ) {
		// 	return false;
		// }
		if ( !isset($data['title']) || trim($data['title'])=='' ) {
			return false;
		}

		$where = array(
			// 'cateid' => intval($data['cateid']),
			'title' => trim($data['title']),
		);
		if ( isset($data['id']) && intval($data['id'])>0 ) {
			$where['id'] = array('neq', intval($data['id']));
		}
		return $this->where($where)->find();
	}

	/**
	 * 一些验证逻辑
	 */
	public function verifyData( $data ) {
		$cateid = intval($data['cateid']);
		$title = trim($data['title']);
		if ( $title!='' ) {
			$where = ['_string'=>"title='{$title}' OR stname='{$title}'"];
			if ( isset($data['id']) && intval($data['id'])>0 ) {
				$where['id'] = ['neq', $data['id']];
			}
			$ret = $this->where($where)->select();
			// var_dump($ret, $this->getLastSql());
			if ( $ret ) {
				return '词条中文名与其它词条中文名或企业词条的简称重复';
			}
		}
		if ( $cateid==1 ) {
			// 验证简称是否重复
			$stname = ( isset($data['stname']) && trim($data['stname'])!='' ) ? trim($data['stname']) : '';
			if ( $title!='' ) {
				$where = ['_string'=>"title='{$stname}' OR stname='{$stname}'"];
				if ( isset($data['id']) && intval($data['id'])>0 ) {
					$where['id'] = ['neq', $data['id']];
				}
				$dup = $this->field('id, title, stname')->where($where)->limit(1)->find();
				if ( $dup ) {
					// return '企业词条简称与['.$dup['title'].']'.$dup['stname'].'('.$dup['id'].')词条的中文名或简称重复';
					return '企业词条简称与其它词条的中文名或简称重复';
				}
			}

			$parentid = intval($data['company_parent_id']);
			$city = trim($data['city']);
			// 验证集团公司的CRICID是否有冲突，排除cricid为空的数据
			$cricid = trim($data['company_cric_id']);
			if ( $parentid==0 && $cricid!='' ) {
				// 条件包括 与当前词条的id不能重复，且是企业类型的，且是集团公司级别的，且cric值不为空，且为当前表单的cric编号的，如果存在，即为冲突
				$where = [
					'cateid'=>1,	// 企业类型的
					'company_parent_id'=>0,	// 集团公司级别的
				];
				// cric值不为空，且为当前表单的cric编号
				$where['_string'] = "company_cric_id<>'' AND company_cric_id='{$cricid}'";
				// 与当前词条的id不能重复
				if ( isset($data['id']) && intval($data['id'])>0 ) {
					$where['id'] = ['neq', $data['id']];
				}
				$dup = $this->field('id, title, stname')->where($where)->limit(1)->find();
				if ( $dup ) {
					// return 'cricid 与 ['.$dup['title'].']'.$dup['stname'].'('.$dup['id'].') 企业的 cricid 冲突';
					return '克尔瑞ID和'.$dup['title'].'冲突，请重新填写';
				}
			}


			if ( $parentid==0 || $city == '' ) {
				return true;
			}
			// 如果当前提交数据设置了上级公司和城市，验证是否同一个上级公司，在同一个城市是否出现了重复数据
			$where = array(
				'cateid' => 1,
				'city' => $city,
				'company_parent_id' => $parentid,
				'id' => ['neq', $data['id']],
				'status' => ['in', [1,9]],
			);
			$exists = $this->where($where)->find();
			if ( $exists ) {
				return '已存在当前城市的子公司';
			}
		}
		return true;
	}

	/**
	 * 统一更新指定集团公司的子公司所有cricid
	 * @2017-11-27 允许集团公司修改cricid的需求，发现的坑 @赵珊
	 */
	public function updateCompanyCricID( $id, $cricid, $action='save' ) {
		$where = ['company_parent_id' => $id,];
		$data = ['company_cric_id'=>$cricid,];
		$ret = $this->where($where)->data($data)->save();
		// 批量向新闻池推送子公司
		if ( $action!=='save' ) {
			$where = [
				'company_parent_id' => $id,
				'status' => 9,
			];
			$datalist = $this->where($where)->select();
			if ( $datalist ) {
				$lInfos = D('Infos', 'Logic', 'Common');
				$ret2 = $lInfos->batchPushNewsPool($datalist, $lInfos::TYPE_WIKI);
				$dbg = [
					'where' => $where,
					'ret' => $ret,
					'ret2' => $ret2,
					'datalist'=>$datalist,
				];
				debug('更新子公司CRICID新闻池接口调用', $dbg, false, true);
			}
		}
		return $ret;
	}

	/**
	 * 转换复用字段数据
	 */
	public function convertFields( $record, $direct=true ) {
		// 压缩扩展字段
		$extras = ['extra', 'seo', 'basic', 'rel', 'album','company_project','ranklist'];
		foreach ( $extras as $i => $field ) {
			if ( isset($record[$field]) ) {
				if ( $direct==true ) {
					// 不是json数据，才进行编码
					if ( 
						is_string($record[$field]) && 
						in_array(substr($record[$field], 0, 1), ['[','{']) && 
						in_array(substr($record[$field], -1), [']','}'])
					) {
						continue;
					} else {
						$record[$field] = json_encode($record[$field]);
					}
				} else {
					// 是json数据，才进行解码
					if ( 
						is_string($record[$field]) && 
						in_array(substr($record[$field], 0, 1), ['[','{']) && 
						in_array(substr($record[$field], -1), [']','}'])
					) {
						$record[$field] = json_decode($record[$field], true);
					} else {
						continue;
					}
					
				}
			}
		}
		return $record;
	}

	/**
	 * 获取历史
	 */
	public function getHistorys($id, $pkid=0) {
		$mWikiHistory = D('WikiHistory', 'Model', 'Common');
		$where = array('id'=>$id);
		$pkid>0 && $where['pkid']=$pkid;
		$order = 'ptime desc';
		$fields = $pkid==0 ? array('pkid', 'version', 'editor') : '*';
		$list = $mWikiHistory->field($fields)->where($where)->order($order)->select();
		// var_dump($mWikiHistory->getLastSql(), $list);
		return $list;
	}

	/**
	 * 获取指定的历史版本数据
	 */
	public function getHistoryVersion( $id, $pkid ) {
		$ret = $this->getHistorys($id, $pkid);
		return $ret ? $ret[0] : false;
	}
	/**
	 * 获取已设置的推荐
	 */
	public function getRecommended( $ids=array(), &$list=array() ) {
		if ( empty($ids) ) {
			return false;
		}
		$mRecommend = D('Recommend', 'Model', 'Common');
		$srctype = 'wiki';
		$where = array(
			'srctype' => 'wiki',
			'relid' => array('in', $ids),
		);
		$all = array();
		$ret = $mRecommend->where($where)->order('relid, flag, ctime desc')->field('relid, flag, extra, ctime')->select();
		foreach ( $ret as $i => $item ) {
			$id = intval($item['relid']);
			$flag = intval($item['flag']);
			if ( !array_key_exists($id, $all) ) {
				$all[$id] = array();
			}
			if ( array_key_exists($flag, $all[$id]) ) {
				// 如果已经存在，就忽略
				continue;
			}
			$all[$id][$flag] = json_decode($item['extra'], true);
			$all[$id][$flag]['rtime'] = intval($item['ctime']);
		}
		foreach ( $list as $i => &$item ) {
			$id = intval($item['id']);
			if ( array_key_exists($id, $all) ) {
				$item['recommends'] = $all[$id];
			}
		}
		return true;
	}

	/**
	 * 保存权限设置
	 */
	public function saveRecommend( $id=0, $data=array(), $recommends=array() ) {

	}
}
