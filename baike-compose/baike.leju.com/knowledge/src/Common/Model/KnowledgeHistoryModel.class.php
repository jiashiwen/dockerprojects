<?php
/**
 * 访问统计
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Model;
use Think\Model;


class KnowledgeHistoryModel extends Model {
	// 真实数据表名
	protected $trueTableName = 'knowledge_history';

	protected $_validate = array (
        array('id', 'require', '知识ID不能为空！', 1, '', 2),
		array('title', 'require', '知识标题不能为空！', 1, '', 1),
		array('content', 'require', '知识内容不能为空！', 1, '', 1),
		array('scope', 'require', '城市不能为空！', 1, '', 1),
		array('cover', 'require', '知识配图不能为空！', 2, '', 1),
		array('coverinfo', 'require', '知识配图说明不能为空！', 2, '', 1),
		array('editorid', 'require', '编辑UID不能为空！', 2, '', 2),
		array('editor', 'require', '', 2, '', 2),
		array('cateid', 'require', '分类ID不能为空！', 1, '', 1),
		array('catepath', 'require', '分类路径不能为空！', 1, '', 1),
		array('version', 'require', '版本不能为空！', 1, '', 1),
		array('status', 'require', '状态值不能为空！', 1, '', 1),
		array('status', array(-1,0,1,9), '状态值的范围不正确！',1,'in'),
		array('src_type', 'require', '来源不能为空！', 1, '', 1),
		array('src_type', array(0,1), '来源的范围不正确！',1,'in'),
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
		array('tagids', 'require', '标签不能为空！',1),
		array('tags', 'require', '标签不能为空！', 1, '', 1),
		array('rel_news', 'require', '相关新闻不能为空！', 2, '', 1),
		array('rel_house', 'require', '相关楼盘不能为空！', 2, '', 1),
    );

	protected function filterContent($content)
	{
		$content = preg_replace( "@<script(.*?)</script>@is", "", $content ); 
		$content = preg_replace( "@<iframe(.*?)</iframe>@is", "", $content ); 
		$content = preg_replace( "@<style(.*?)</style>@is", "", $content ); 
		$content = preg_replace( "@<(.*?)>@is", "", $content ); 
		return $content;
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

	public function getHistoryVersionList($id)
	{
		$list = $this->where(array('id'=>$id))->order('version desc')->select();
		return $list;
	}

	public function getHistoryVersion($id,$pkid)
	{
		$info = $this->where(array('id'=>$id,'pkid'=>$pkid))->find();
		return $info;
	}

	public function addAllData($data)
	{
		return $this->addAll($data);
	}


	/**
	 * 指定一个知识内容的编号，删除这条知识的所有历史版本数据
	 * @param $id int 知识编号
	 */
	public function cleanupKnowledge( $id ) {
		$id = intval($id);
		if ( $id <= 0 ) {
			return false;
		}
		$ret = $this->where(array('id'=>$id))->delete();
		return true;
	}
}
