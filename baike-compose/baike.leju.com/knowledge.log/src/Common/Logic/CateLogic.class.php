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
		$this->_kpath = 'kb:cate:path';
		$this->_kname = 'kb:cate:name';
		$this->_ktree = 'kb:cate:tree';
		$this->_kfulltree = 'kb:cate:fulltree';
	}


	public function init($lock = 0)
	{
		$data = $this->toTree($lock);
		$one = array();
		foreach ($data as $k => $i)
		{
			$two = array();
			$one[] = $i['id'];
			$this->setCatePath($i['id'],$i['path']);
			$this->setCateName($i['id'],$i['name']);
			if (isset($i['son']))
			{
				foreach ($i['son'] as $kk => $ii)
				{
					$three = array();
					$two[] = $ii['id'];
					$this->setCatePath($ii['id'],$ii['path']);
					$this->setCateName($ii['id'],$ii['name']);
					if (isset($ii['son']))
					{
						foreach ($ii['son'] as $kkk => $iii) {
							$three[] = $iii['id'];
							$this->setCatePath($iii['id'],$iii['path']);
							$this->setCateName($iii['id'],$iii['name']);
						}
						$this->setCateTree($ii['id'],implode(',', $three));
					}
				}
				$this->setCateTree($i['id'],implode(',', $two));
			}
		}
		$this->setCateTree(0,implode(',', $one));
		return true;
	}

	/**
	 * @desc 设置分类路径path
	 * @param $id
	 * @param $path
	 * @return bool
	 */
	protected function setCatePath($id,$path)
	{
		$this->_cacher->hset($this->_kpath,$id,$path);
		return true;
	}

	/**
	 * @desc 设置分类名称
	 * @param $id
	 * @param $name
	 * @return bool
	 */
	protected function setCateName($id,$name)
	{
		$this->_cacher->hSet($this->_kname, intval($id), $name);
		return true;
	}

	/**
	 * @desc 设置分类节点的叶子节点
	 * @param $id
	 * @param $sonids
	 * @return bool
	 */
	protected function setCateTree($id,$sonids)
	{
		$this->_cacher->hSet($this->_ktree, intval($id), $sonids);
		return true;
	}

	/**
	 * @desc 添加节点
	 * @param $pid
	 * @param $id
	 * @param $name
	 * @param $path
	 * @return bool
	 */
	public function addTreeNode($pid,$id,$name,$path)
	{
		$id = intval($id)<=0 ? '' : intval($id);
		$ptree = $this->_cacher->hGet($this->_ktree,$pid);
		$ptree = explode(',', trim($ptree, ','));
		array_push($ptree, $id);
		$ptree = trim(implode(',', $ptree), ',');

		$this->setCateTree($pid, $ptree);
		$this->setCateName($id, $name);
		$this->setCatePath($id, $path);
		$a = $this->_cacher->hGet($pid);
		return true;
	}

	/**
	 * @desc 编辑节点
	 * @param $id
	 * @param $name
	 * @return bool
	 */
	public function editTreeNode($id,$name)
	{
		$this->setCateName($id,$name);
		return true;
	}

	/**
	 * @desc 交换节点
	 * @param $pid
	 * @param $oid
	 * @param $nid
	 * @return bool
	 */
	public function exchTreeNode($pid,$oid,$nid)
	{
		$ptree = $this->_cacher->hGet($this->_ktree,$pid);
		$ptree = explode(',', $ptree);
		foreach ($ptree as $key => $value) {
			if ($value == $oid)
				$ptree[$key] = $nid;
			if ($value == $nid)
				$ptree[$key] = $oid;
		}
		$ptree = implode(',', $ptree);
		$this->setCateTree($pid,$ptree);
		return true;
	}

	/**
	 * @desc 删除节点
	 * @param $pid
	 * @param $id
	 * @return bool
	 */
	public function delTreeNode($pid,$id)
	{
		$ptree = $this->_cacher->hGet($this->_ktree,$pid);
		$ptree = explode(',', $ptree);
		foreach ($ptree as $key => $val) {
			if ($id == $val)
			{
				unset($ptree[$key]);
				break;
			}
		}
		$ptree = implode(',', $ptree);
		$this->_cacher->hDel($this->_kpath,$id);
		$this->_cacher->hDel($this->_kname,$id);
		$this->setCateTree($pid,$ptree);
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
	public function recoverTreeNode($pid,$id,$name,$path)
	{
		$mcate = D('Categories', 'Model', 'Common');
		$list = $mcate->where(array('status'=>0,'parent'=>$pid))->order('iorder asc')->select();
		if ($list)
		{
			foreach ($list as $k=>$item)
			{
				$ptree[] = $item['id'];
			}
			$ptree = implode(',',$ptree);
			$this->setCateTree($pid, $ptree);
			$this->setCateName($id, $name);
			$this->setCatePath($id, $path);
		}
		return true;
	}

	/**
	 * @desc 生成树
	 * @param int $lock
	 * @return array
	 */
	public function toTree($lock=0)
	{
		$rlist = $this->_cacher->get($this->_kfulltree);
		if ($lock == 0 && $rlist)
		{
			//$tree = json_decode($rlist);
			$tree = $rlist;
		}
		else
		{
			$mcate = D('Categories', 'Model', 'Common');
			$data = $mcate->getValidAllCate();
			$tree = $this->_toTree($data,0);
			$this->_cacher->set($this->_kfulltree,json_encode($tree));
		}

		return $tree;
	}

	/**
	 * @desc 生成树的节点
	 * @param $data
	 * @param int $pid
	 * @return array
	 */
	protected function _toTree($data, $pid = 0){
		$list = array();
		$tem = array();
		foreach ($data as $item)
		{
			if ($item['parent'] == $pid)
			{
				$tem = $this->_toTree($data, $item['id']);
				//判断是否存在子数组
				$tem && $item['son'] = $tem;
				$list[] = $item;
			}
		}
		return $list;
	}


	public function getCateListById($id)
	{
		$ids = $this->_cacher->hget($this->_ktree,$id);
		$ids = trim($ids,',');
		$ids = explode(',',$ids);
		$names = $this->_cacher->hMGet($this->_kname,$ids);
		return $names;
	}

	public function getCateListByIds($ids)
	{
		if (!is_array($ids))
		{
			$ids = trim($ids,',');
			$ids = explode(',', $ids);
		}
		$names = $this->_cacher->hMGet($this->_kname,$ids);
		return $names;
	}

	public function getTopCate()
	{
		$top = $this->_cacher->hGet($this->_ktree,0);
		$ids = explode(',', $top);
		$names = $this->_cacher->hMGet($this->_kname,$ids);
		return $names;
	}

	/**
	 * 获取指定节点的详细信息，包括父结点编号和子结点集合编号
	 */
	public function getCateInfo($id) {
		$path = $this->_cacher->hGet($this->_kpath, $id);
		if ( !$path ) {
			return false;
		}
		$_path = explode('-', $path);
		$result = array();
		$level = count($_path) - 1;
		$result['pid'] = ( $level > 1 ) ? $_path[$level-1] : 0;
		$result['path'] = $path;
		$result['name'] = $this->getCateName($id);
		$ids = explode(',', trim($this->_cacher->hGet($this->_ktree, $id), ','));
		$result['child'] = $this->getChildsInfo($ids);
		$result['level'] = $level;
		return $result;
	}

	/**
	 * 指定结点集合，获取节点基本信息
	 */
	public function getChildsInfo($ids=array()) {
		$result = array();
		if ( is_array($ids) ) {
			$tree = $this->_cacher->hMGet($this->_kname, $ids);
			$path = $this->_cacher->hMGet($this->_kpath, $ids);
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

	private function getPreCate($id)
	{
		$ids = $this->_cacher->hGet($this->_ktree,$id);

		if ($ids)
		{
			$ids = trim($ids,',');
			$ids = explode(',', $ids);
			return $ids['0'];
		}
		return false;
	}

	public function getCateName($id)
	{
		return $this->_cacher->hGet($this->_kname,$id);
	}

	public function getCateChildInfoById($id)
	{
		$ids = $this->_cacher->hGet($this->_ktree,$id);
		$ids = explode(',',trim($ids,','));
		$path = $this->_cacher->hMGet($this->_kpath, $ids);
		$name = $this->_cacher->hMGet($this->_kname, $ids);
		$result = array();
		foreach ($ids as $id)
		{
			$result[$id]['name'] = $name[$id];
			$result[$id]['path'] = $path[$id];
			$result[$id]['id'] = $id;
		}
		return $result;
	}

	public function getCatePathById($id)
	{
		return $this->_cacher->hGet($this->_kpath,$id);
	}

	// 用于快速调用 SEO 使用
	public function getCatePathByIdForSEO($id)
	{
		$path = $this->getCatePathById($id);
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
	public function getIndexTopCategories()
	{
		$topCate = $this->getTopCate();
		$result = array();
		if ($topCate)
		{
			foreach ($topCate as $id => $name)
			{
				$result[$id]['id'] = $id;
				$result[$id]['name'] = $name;
				$result[$id]['son'] = $this->getCateChildInfoById($id);
				foreach ($result[$id]['son'] as $k => $item)
				{
					$level3 = $this->getCateChildInfoById($item['id']);
					$result[$id]['son'][$item['id']]['son'] = $level3;
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
	public function getCategoriesById($id)
	{
		$topCate = $this->getCateName($id);
		$result = array();
		if ($topCate)
		{
			$result[$id]['id'] = $id;
			$result[$id]['name'] = $topCate;
			$result[$id]['son'] = $this->getCateChildInfoById($id);
			foreach ($result[$id]['son'] as $k => $item)
			{
				$level3 = $this->getCateChildInfoById($item['id']);
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
	public function getFirstTopCateid()
	{
		$top = $this->_cacher->hGet($this->_ktree,0);
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
	public function crumbs($path,$city)
	{
		if (!is_array($path))
		{
			$path = explode('-',trim($path));
		}
		$count = count($path);
		$href = array();
		$href[] = '<a target="_blank" href="/">房产百科</a><i></i>';
		if ($count > 1)
		{
			foreach ($path as $k=>$cateid)
			{
				$name = $this->_cacher->hGet($this->_kname,$cateid);
				if ($k > 0 && $k < $count)
				{
					$url = url('cate', array('id'=>$cateid, 'city'=>$city, 'page'=>1), 'pc', 'baike');
					if ($k == $count-1)
					{
						$href[] = '<a target="_blank" href="'.$url.'">'.$name.'</a>';
					}
					else
					{
						$href[] = '<a target="_blank" href="'.$url.'">'.$name.'</a><i></i>';
					}
				}
			}
			$href = implode('',$href);
		}
		return $href;
	}

	public function pathname($path)
	{
		$path = explode('-',trim($path));
		if (count($path) == 4)
		{
			foreach ($path as $k => $v)
			{
				if ($k > 0)
				{
					$name[] = $this->_cacher->hGet($this->_kname,$v);
				}
			}

			return implode('-',$name);
		}
		return false;
	}

}