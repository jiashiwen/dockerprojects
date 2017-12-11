<?php
/**
 * 分类逻辑
 * @author hongwang <hongwang@leju.com>
 */
namespace Common\Logic;

class CateLogic {

	protected $_cacher = null;
	protected $_kpath = null;
	protected $_kname = null;
	protected $_ktree = null;
	protected $_korder = null;
	protected $_kfulltree = null;

	public function __construct() {
		$this->_cacher = S(C('REDIS'));
		$this->_kpath = '{type}:cate:path';
		$this->_kname = '{type}:cate:name';
		$this->_ktree = '{type}:cate:tree';
		$this->_kfulltree = '{type}:cate:fulltree';
	}


	public function init( $lock=0, $type='kb' ) {
		$data = $this->toTree($lock, $type);
		$one = array();
		foreach ($data as $k => $i) {
			$two = array();
			$one[] = $i['id'];
			$this->setCatePath($i['id'], $i['path'], $type);
			$this->setCateName($i['id'], $i['name'], $type);
			if (isset($i['son'])) {
				foreach ($i['son'] as $kk => $ii) {
					$three = array();
					$two[] = $ii['id'];
					$this->setCatePath($ii['id'], $ii['path'], $type);
					$this->setCateName($ii['id'], $ii['name'], $type);
					if (isset($ii['son'])) {
						foreach ($ii['son'] as $kkk => $iii) {
							$three[] = $iii['id'];
							$this->setCatePath($iii['id'], $iii['path'], $type);
							$this->setCateName($iii['id'], $iii['name'], $type);
						}
						$this->setCateTree($ii['id'], implode(',', $three), $type);
					}
				}
				$this->setCateTree($i['id'], implode(',', $two), $type);
			}
		}
		$this->setCateTree(0, implode(',', $one), $type);
		return true;
	}

	/**
	 * @desc 设置分类路径path
	 * @param $id
	 * @param $path
	 * @return bool
	 */
	protected function setCatePath($id, $path, $type='kb') {
		$key = str_replace('{type}', $type, $this->_kpath);
		$this->_cacher->hset($key, $id, $path);
		return true;
	}

	/**
	 * @desc 设置分类名称
	 * @param $id
	 * @param $name
	 * @return bool
	 */
	protected function setCateName($id, $name, $type='kb') {
		$key = str_replace('{type}', $type, $this->_kname);
		$this->_cacher->hSet($key, intval($id), $name);
		return true;
	}

	/**
	 * @desc 设置分类节点的叶子节点
	 * @param $id
	 * @param $sonids
	 * @return bool
	 */
	protected function setCateTree($id, $sonids, $type='kb') {
		$key = str_replace('{type}', $type, $this->_ktree);
		$this->_cacher->hSet($key, intval($id), $sonids);
		return true;
	}

	/**
	 * @desc 添加节点
	 * @param $pid
	 * @param $id
	 * @param $name
	 * @param $path
	 * @param $type 栏目类别 kb=>知识栏目 qa=>问答栏目
	 * @return bool
	 */
	public function addTreeNode($pid, $id, $name, $path, $type='kb') {
		$id = intval($id)<=0 ? '' : intval($id);
		$key = str_replace('{type}', $type, $this->_ktree);
		$ptree = $this->_cacher->hGet($key, $pid);
		$ptree = explode(',', trim($ptree, ','));
		array_push($ptree, $id);
		$ptree = trim(implode(',', $ptree), ',');

		$this->setCateTree($pid, $ptree, $type);
		$this->setCateName($id, $name, $type);
		$this->setCatePath($id, $path, $type);
		$a = $this->_cacher->hGet($pid);
		return true;
	}

	/**
	 * @desc 编辑节点
	 * @param $id
	 * @param $name
	 * @return bool
	 */
	public function editTreeNode($id, $name, $type='kb') {
		$this->setCateName($id, $name, $type);
		return true;
	}

	/**
	 * @desc 交换节点
	 * @param $pid
	 * @param $oid
	 * @param $nid
	 * @return bool
	 */
	public function exchTreeNode($pid, $oid, $nid, $type='kb') {
		$key = str_replace('{type}', $type, $this->_ktree);
		$ptree = $this->_cacher->hGet($key, $pid);
		$ptree = explode(',', $ptree);
		foreach ($ptree as $_key => $value) {
			if ($value == $oid) {
				$ptree[$_key] = $nid;
			}
			if ($value == $nid) {
				$ptree[$_key] = $oid;
			}
		}
		$ptree = implode(',', $ptree);
		$this->setCateTree($pid, $ptree, $type);
		return true;
	}

	/**
	 * @desc 删除节点
	 * @param $pid
	 * @param $id
	 * @return bool
	 */
	public function delTreeNode($pid, $id, $type='kb') {
		$key = str_replace('{type}', $type, $this->_ktree);
		$ptree = $this->_cacher->hGet($key, $pid);
		$ptree = explode(',', $ptree);
		$ptree = array_flip($ptree);
		unset($ptree[$id]);
		$ptree = array_flip($ptree);
		// var_dump(implode(',', $ptree));
		// foreach ($ptree as $_key => $val) {
		// 	if ($id == $val) {
		// 		unset($ptree[$_key]);
		// 		break;
		// 	}
		// }
		$ptree = implode(',', $ptree);
		$key = str_replace('{type}', $type, $this->_kpath);
		$this->_cacher->hDel($key, $id);
		$key = str_replace('{type}', $type, $this->_kname);
		$this->_cacher->hDel($key, $id);
		$this->setCateTree($pid, $ptree, $type);
		return true;
	}

	/**
	 * @desc 恢复删除的节点
	 * @param $pid
	 * @param $id
	 * @param $name
	 * @param $path
	 * @return bool
	 */
	public function recoverTreeNode($pid, $id, $name, $path, $type='kb') {
		$mcate = D('Categories', 'Model', 'Common');
		$where = array(
			'parent' => $pid,
			'status' => 0,
			'type' => $type,
		);
		$list = $mcate->where($where)->order('iorder asc')->select();
		if ($list) {
			foreach ($list as $k=>$item) {
				$ptree[] = $item['id'];
			}
			$ptree = implode(',',$ptree);
			$this->setCateTree($pid, $ptree, $type);
			$this->setCateName($id, $name, $type);
			$this->setCatePath($id, $path, $type);
		}
		return true;
	}

	/**
	 * @desc 生成树
	 * @param int $lock
	 * @return array
	 */
	public function toTree($lock=0, $type='kb') {
		$key = str_replace('{type}', $type, $this->_kfulltree);
		$rlist = $this->_cacher->get($key);
		if ($lock == 0 && $rlist) {
			$tree = $rlist;
		} else {
			$mcate = D('Categories', 'Model', 'Common');
			$data = $mcate->getValidAllCate($type);
			$tree = $this->_toTree($data,0);
			$this->_cacher->set($key, json_encode($tree));
		}

		return $tree;
	}

	/**
	 * @desc 生成树的节点
	 * @param $data
	 * @param int $pid
	 * @return array
	 */
	protected function _toTree($data, $pid=0) {
		$list = array();
		$tem = array();
		foreach ($data as $item) {
			if ($item['parent'] == $pid) {
				$tem = $this->_toTree($data, $item['id']);
				//判断是否存在子数组
				$tem && $item['son'] = $tem;
				$list[] = $item;
			}
		}
		return $list;
	}


	public function getCateListById($id, $type='kb') {
		$key = str_replace('{type}', $type, $this->_ktree);
		$ids = $this->_cacher->hget($key, $id);
		$ids = trim($ids,',');
		$ids = explode(',',$ids);
		$key = str_replace('{type}', $type, $this->_kname);
		$names = $this->_cacher->hMGet($key, $ids);
		return $names;
	}

	public function getCateListByIds($ids, $type='kb') {
		if (!is_array($ids)) {
			$ids = trim($ids,',');
			$ids = explode(',', $ids);
		}
		$key = str_replace('{type}', $type, $this->_kname);
		$names = $this->_cacher->hMGet($key, $ids);
		return $names;
	}

	public function getTopCate($type='kb') {
		$key = str_replace('{type}', $type, $this->_ktree);
		$top = $this->_cacher->hGet($key, 0);
		$ids = explode(',', $top);
		$key = str_replace('{type}', $type, $this->_kname);
		$names = $this->_cacher->hMGet($key, $ids);
		return $names;
	}

	/**
	 * 获取指定节点的详细信息，包括父结点编号和子结点集合编号
	 */
	public function getCateInfo($id, $type='kb') {
		$key = str_replace('{type}', $type, $this->_kpath);
		$_ktree = str_replace('{type}', $type, $this->_ktree);
		$path = $this->_cacher->hGet($key, $id);
		if ( !$path ) {
			return false;
		}
		$_path = explode('-', $path);
		$result = array();
		$level = count($_path) - 1;
		$result['pid'] = ( $level > 1 ) ? $_path[$level-1] : 0;
		$result['path'] = $path;
		$result['name'] = $this->getCateName($id, $type);
		$ids = explode(',', trim($this->_cacher->hGet($_ktree, $id), ','));
		$result['child'] = $this->getChildsInfo($ids, $type);
		$result['level'] = $level;
		return $result;
	}

	/**
	 * 指定结点集合，获取节点基本信息
	 */
	public function getChildsInfo($ids=array(), $type='kb') {
		$result = array();
		if ( is_array($ids) ) {
			$_kpath = str_replace('{type}', $type, $this->_kpath);
			$_kname = str_replace('{type}', $type, $this->_kname);
			$tree = $this->_cacher->hMGet($_kname, $ids);
			$path = $this->_cacher->hMGet($_kpath, $ids);
			foreach ( $ids as $id ) {
				$result[$id] = array(
					'id' => $id,
					'name' => $tree[$id],
					'path' => $path[$id],
				);
			}
		}
		return $result;
	}

	public function getCateList($id,$level)
	{
		$path = $this->_cacher->hGet($this->_kpath, $id);
		$p = explode('-', $path);
		$pid = $p[$level] == null ? $this->getPreCate($id) : $p[$level];
		$pname = $this->_cacher->hGet($this->_kname,$pid);
		//
		$ids = $this->_cacher->hGet($this->_ktree,$pid);
		$ids = explode(',', trim($ids, ','));
		//
		$tree = $this->_cacher->hMGet($this->_kname,$ids);
		$allpath = $this->_cacher->hMGet($this->_kpath,$ids);
		$list['pname'] = $pname;
		$list['allpath'] = $allpath;
		$list['tree'] = $tree;
		$list['ids'] = $ids;
		$list['pid'] = $pid;
		return $list;
	}

	private function getPreCate($id, $type='kb')
	{
		$key = str_replace('{type}', $type, $this->_ktree);
		$ids = $this->_cacher->hGet($key, $id);

		if ( $ids ) {
			$ids = trim($ids,',');
			$ids = explode(',', $ids);
			return $ids['0'];
		}
		return false;
	}

	public function getCateName($id, $type='kb') {
		$key = str_replace('{type}', $type, $this->_kname);
		return $this->_cacher->hGet($key, $id);
	}

	public function getCateChildInfoById($id, $type='kb')
	{
		$key = str_replace('{type}', $type, $this->_ktree);
		$ids = $this->_cacher->hGet($key, $id);
		$ids = explode(',',trim($ids,','));
		$key = str_replace('{type}', $type, $this->_kpath);
		$path = $this->_cacher->hMGet($key, $ids);
		$key = str_replace('{type}', $type, $this->_kname);
		$name = $this->_cacher->hMGet($key, $ids);
		$result = array();
		foreach ($ids as $id) {
			if ( $id=='' ) {
				continue;
			}
			$result[$id]['name'] = $name[$id];
			$result[$id]['path'] = $path[$id];
			$result[$id]['id'] = $id;
		}
		return $result;
	}

	public function getCatePathById($id, $type='kb') {
		$key = str_replace('{type}', $type, $this->_kpath);
		return $this->_cacher->hGet($key, $id);
	}

	// 用于快速调用 SEO 使用
	public function getCatePathByIdForSEO($id, $type='kb') {
		$path = $this->getCatePathById($id, $type);
		$path = explode('-', $path);
		if ( count($path) == 2 ) {
			array_push($path, '');
		}
		if ( count($path) == 3 ) {
			array_push($path, '');
		}
		return $path;
	}

	public function remove($keys)
	{
		$a = $this->_cacher->delete($keys);
		var_dump($a);
		exit;
	}


	/**
	 * @author hongwang@leju.com
	 * @desc 获取一级栏目和二级栏目
	 * @return array
	 */
	public function getIndexTopCategories($type='kb') {
		$topCate = $this->getTopCate($type);
		$result = array();
		if ($topCate) {
			foreach ($topCate as $id => $name) {
				$result[$id]['id'] = $id;
				$result[$id]['name'] = $name;
				$childs = $this->getCateChildInfoById($id, $type);
				$result[$id]['son'] = $childs;
				if ( $type=='kb' && !empty($childs) ) {
					foreach ( $childs as $k => $item ) {
						$level3 = $this->getCateChildInfoById($item['id'], $type);
						$result[$id]['son'][$item['id']]['son'] = $level3;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * @author hongwang@leju.com
	 * @desc 根据ID获取相关子栏目
	 * @return array
	 */
	public function getCategoriesById($id, $type='kb') {
		$topCate = $this->getCateName($id, $type);
		$result = array();
		if ( $topCate ) {
			$result[$id]['id'] = $id;
			$result[$id]['name'] = $topCate;
			$result[$id]['son'] = $this->getCateChildInfoById($id, $type);
			foreach ( $result[$id]['son'] as $k => $item ) {
				$level3 = $this->getCateChildInfoById($item['id'], $type);
				$result[$id]['son'][$item['id']]['son'] = $level3;
			}
		}
		return $result;
	}

	/**
	 * @author hongwang@leju.com
	 * @desc获取第一个栏目的ID
	 * @return mixed
	 */
	public function getFirstTopCateid($type='kb') {
		$key = str_replace('{type}', $type, $this->_ktree);
		$top = $this->_cacher->hGet($key, 0);
		$ids = explode(',', $top);
		// @issue: 如果 ids 为空怎样？
		if ( empty($ids) ) {
			return false;
		}
		return intval($ids[0]);
	}

	/**
	 * @desc 面包屑
	 * @param $path
	 * @param $city
	 * @return bool|mixed
	 */
	public function crumbs($path,$city,$autofocus=true) {
		if (!is_array($path)) {
			$path = explode('-',trim($path));
		}
		$count = count($path);
		$href = array();
		$href[] = '<a target="_blank" href="/">房产百科</a><i></i>';
		$key = str_replace('{type}', 'kb', $this->_kname);
		if ($count > 1) {
			foreach ($path as $k=>$cateid) {
				$name = $this->_cacher->hGet($key, $cateid);
				if ($k > 0 && $k < $count) {
					$url = url('cate', array('id'=>$cateid, 'page'=>1), 'pc', 'baike');
					if ( $k == $count-1 ) {
						$focus_style = $autofocus ? ' class="on"' : '';
						$href[] = '<a'.$focus_style.' target="_blank" href="'.$url.'">'.$name.'</a>';
					} else {
						$href[] = '<a target="_blank" href="'.$url.'">'.$name.'</a><i></i>';
					}
				}
			}
			$href = implode('',$href);
		}
		return $href;
	}

	/**
	 * 指定一个 catepath cateid的字符串，返回对应的栏目名称列表
	 */
	public function getPathName ( $path, $type='kb' ) {
		$key = str_replace('{type}', $type, $this->_kname);
		$path = explode('-', trim($path));
		$pathname = $this->_cacher->hMGet($key, $path);
		return $pathname;
	}

	public function pathname($path, $type='kb') {
		$key = str_replace('{type}', $type, $this->_kname);
		$path = explode('-', trim($path));
		$ret = $this->_cacher->hMGet($key, $path);
		if ( count($path) == 4) {
			foreach ($path as $k => $v) {
				if ($k > 0) {
					$name[] = $this->_cacher->hGet($key, $v);
				}
			}
			return implode('-', $name);
		}
		return false;
	}

}