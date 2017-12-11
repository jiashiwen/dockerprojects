<?php
/**
 * 访问统计
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class CategoriesModel extends Model {

	
	// 真实数据表名
	protected $trueTableName = 'categories';

	protected $_validate = array (
        array('id', 'require', '栏目ID不能为空！', 2, '', 2),
		array('name', 'require', '标题不能为空！', 1, '', 2),
		array('name', 'strLen', '标题长度超过10个字符！', 1, 'callback'),
		array('path', 'require', '栏目全路径不能为空！', 1, '', 1),
		array('level', 'require', '栏目级别不能为空！', 1, '', 2),
		array('parent', 'require', '栏目父级编号不能为空！', 1, '', 2),
		array('code', 'require', '栏目url代号不能为空！', 2, '', 2),
		array('iorder', 'require', '排序不能为空！', 1, '', 1),
		array('status', 'require', '状态值不能为空！', 1, '', 1),
		array('status', array(0,1), '状态值的范围不正确！',2,'in'),
		array('type', array('kb','qa','wiki'), '来源值的范围不正确！',2,'in'),
    );


    public function getCateList($ids)
    {
        if (!is_array($ids))
        {
            $ids = (array)$ids;
        }
        
    	$condition = array('parent'=>array('in',$ids),'type'=>'kb','status'=>0);
		$list = $this->where($condition)->order('iorder asc')->select();
		return $list;
    }

    public function authorCate($ids)
    {
    	if (!is_array($ids))
    	{
            $ids = explode(',', $ids);
    	}
    	if (!empty($ids))
    	{
    		$map['id'] = array('in',$ids); 
    		$map['status'] = 0;
    		$map['type'] = 'kb';
    		$map['parent'] = 0;
    		return $this->where($map)->order('iorder asc')->select();
    	}
    	else
    	{
    		return false;
    	}

    }

    public function getMaxIorder($id)
    {
    	$condition = array('type'=>'kb','parent'=>$id);	
    	$info = $this->where($condition)->max('iorder');
    	if ($info)
    	{
    		return $info;
    	}
    	return 0;
    }

    public function getCateInfo($id)
    {
    	if (empty($id) || intval($id) == 0)
    	{
    		return 0;
    	}
    	$condition = array('type'=>'kb','id'=>$id);	
    	$info = $this->where($condition)->order('iorder asc')->find();
    	return $info;
    }

    protected function strLen($str)
	{
		$len = abslength($str);
		if ($len > 10)
		{
			return false;
		}
		return $len;
	}

	public function getAllCate()
	{
		return $this->order('level asc,iorder asc')->select();
	}

	//for Front
	public function getValidAllCate()
    {
        return $this->where(array('status'=>0))->order('level asc,iorder asc')->select();
    }

    public function frontTopList($maxnum)
    {
        $map['type'] = 'kb';
        $map['status'] = 0;
        $map['parent'] = 0;
        $count = $this->where($map)->count();
        //$num = ((int)$count > $maxnum) ? ($maxnum) : $maxnum;
        //$list = $this->where($map)->order('iorder asc')->limit($num)->select();     
        $list = $this->where($map)->order('iorder asc')->select();     
        return $list;
    }


}
