<?php
/**
 * 访问统计
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class KnowledgeModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'knowledge';

	protected $_validate = array (
		array('id', 'require', '', 1, '', 2),
		array('title', 'require', '知识标题不能为空！',1),
		array('title', 'filterTitle', '标题内容包含非法字符!', 1, 'callback'),
		array('title', 'strLen', '标题长度超过30个字符！', 1, 'callback'),
		array('content', 'require', '知识内容不能为空！',1),
		array('scope', 'require', '城市不能为空！', 1, '', 1),
		array('cover', 'require', '知识配图不能为空！',1),
		array('coverinfo', 'require', '知识配图说明不能为空！', 2, '', 1),
		array('editorid', 'require', '编辑UID不能为空！', 2, '', 2),
		array('editor', 'require', '', 2, '', 2),
		array('cateid', 'require', '请完整选择栏目！', 1, '', 1),
		array('catepath', 'require', '请完整选择栏目！', 1, '', 1),
		array('version', 'require', '版本不能为空！', 1, '', 1),
		array('status', 'require', '状态值不能为空！', 1, '', 1),
		array('status', array(-1,0,1,9), '状态值的范围不正确！',1,'in'),
		array('src_type', 'require', '来源不能为空！', 1, '', 1),
		array('src_type', array(0,1,2), '来源的范围不正确！',1,'in'),
		array('src_url', 'require', '源地址不能为空！', 2, '', 2),
		array('ctime', 'require', '创建时间不能为空！', 2, '', 2),
		array('ptime', 'require', '定时发布时间不能为空！', 2, '', 2),
		array('utime', 'require', '版本时间不能为空！', 2, '', 2),
		array('top_time', 'require', '置顶时间不能为空！', 2, '', 2),
		array('top_title', 'require', '置顶标题不能为空！', 2, '', 2),
		array('top_cover', 'require', '置顶配图不能为空！', 2, '', 2),
		array('top_coverinfo', 'require', '置顶配图说明不能为空！', 2, '', 2),
		array('rcmd_time', 'require', '推荐时间不能为空！', 2, '', 2),
		array('rcmd_title', 'require', '推荐标题不能为空！', 2, '', 2),
		array('rcmd_cover', 'require', '推荐配图不能为空！', 2, '', 2),
		array('rcmd_coverinfo', 'require', '推荐配图说明不能为空！', 2, '', 2),
		array('tags', 'require', '标签不能为空！',1),
		array('rel_news', 'require', '相关新闻不能为空！', 2, '', 1),
		array('rel_house', 'require', '相关楼盘不能为空！', 2, '', 1),
	);

	protected function filterContent($content)
	{
		$content = preg_replace( "@<script(.*?)</script>@is", "", $content ); 
		$content = preg_replace( "@<iframe(.*?)</iframe>@is", "", $content ); 
		return $content;
	}

	protected function clearAll($area_str)
	{
		$area_str = trim($area_str); //清除字符串两边的空格
		$area_str = strip_tags($area_str,""); //利用php自带的函数清除html格式
		//$area_str = str_replace("&nbsp;","",$area_str);

		//$area_str = preg_replace("/   /","",$area_str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
		$area_str = preg_replace("/
/","",$area_str);
		$area_str = preg_replace("/
/","",$area_str);
		$area_str = preg_replace("/
/","",$area_str);
		//$area_str = preg_replace("/ /","",$area_str);
		//$area_str = preg_replace("/  /","",$area_str);  //匹配html中的空格
		$area_str = trim($area_str); //返回字符串
		return $area_str;
	}

	protected function strLen($str)
	{
		$len = abslength($str);
		if ($len > 30)
		{
			return false;
		}
		return $len;
	}


	protected function uptrend()
	{
		$map['type'] = 'kb';
		$map['status'] = 9;
		$map['ptime'] = array('between',"{getDayTime()['begin']},{$getDayTime()['end']}");
		return $this->where($map)->count();
	}



	public function updateAllData($ids,$data)
	{
		$where['id'] = array('in',$ids);
		return $this->where($where)->save($data);
	}

	protected function filterTitle($title)
	{
		if(!preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\'\"“”‘’，\??？\s,]+$/u",$title))
		{
			return false;
		}
		return $title;
	}

		//批量更新
	public function saveAll($datas){

		$model || $model=$this->trueTableName;

		$sql   = ''; //Sql
		$lists = []; //记录集$lists
		$pk    = $this->getPk();//获取主键

		foreach ($datas as $data) {
			foreach ($data as $key=>$value) {
				if($pk===$key){
					$ids[]=$value;
				}else{
					$lists[$key].= sprintf("WHEN %u THEN '%s' ",$data[$pk],$value);
				}
			}
		}

		foreach ($lists as $key => $value) {
			$sql.= sprintf("`%s` = CASE `%s` %s END,",$key,$pk,$value);
		}

		$sql = sprintf('UPDATE __%s__ SET %s WHERE %s IN ( %s )',strtoupper($model),rtrim($sql,','),$pk,implode(',',$ids));

		return M()->execute($sql);
	}

	/**
	 * 知识数据是否重复
	 *
	 */
	public function isRepeat ( $data ) {
		$where = array();
		if ( !isset($data['cateid']) || intval($data['cateid'])<=0 ) {
			return false;
		}
		if ( !isset($data['scope']) || trim($data['scope'])=='' ) {
			return false;
		}
		if ( !isset($data['title']) || trim($data['title'])=='' ) {
			return false;
		}

		$where = array(
			'cateid'=>$data['cateid'],
			'scope'=>$data['scope'],
			'title'=>$data['title'],
		);
		if ( isset($data['id']) && intval($data['id'])>0 ) {
			$where['id'] = array('neq', $data['id']);
		}
		return $this->where($where)->find();
	}

}
