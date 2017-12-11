<?php
/**
 * 搜索服务逻辑
 * @author Robert <yongliang1@leju.com>
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

	protected function setCatePath($id,$path)
	{
		$this->_cacher->hset($this->_kpath,$id,$path);
		return true;
	}

	protected function setCateName($id,$name)
	{
		$this->_cacher->hSet($this->_kname, intval($id), $name);
		return true;
	}

	protected function setCateTree($id,$sonids)
	{
		$this->_cacher->hSet($this->_ktree, intval($id), $sonids);
		return true;
	}

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

	public function editTreeNode($id,$name)
	{
		$this->setCateName($id,$name);
		return true;
	}

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
		$this->setCateTree($pid,$ptree);
		return true;
	}

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
			$data = $mcate->getAllCate();
			$tree = $this->_toTree($data,0);
			$this->_cacher->set($this->_kfulltree,json_encode($tree));
		}
		
		return $tree;
	}

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
		$this->getTopCate();
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
		$ids = trim($ids,',');
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

	public function remove($keys)
	{
		$a = $this->_cacher->delete($keys);
		var_dump($a);
		exit;
	}

	
}