<?php
/**
 * 知识库 问知内容查看页面
 * 用于展示问答的排行信息
 */
namespace Baike\Controller;
use Think\Controller;

class ShowController extends BaseController {

	/**
	 * 知识内容查看页面
	 */
	public function index() {
		$id = I('get.id', 0, 'intval');
		if ($id > 0)
		{
			$mKnowledge = D('Knowledge', 'Model', 'Common');
			$where['id'] = $id;
			$where['status'] = 9;
			$detail = $mKnowledge->where($where)->find();

			if ($detail)
			{
				$detail['rel_news'] = json_decode($detail['rel_news'],true);
				$detail['rel_house'] = json_decode($detail['rel_house'],true);
				$detail['url'] = C('FRONT_URL.show'). $detail['id'];
				$detail['tags'] = explode(' ',$detail['tags']);
				$detail['ctime'] = date('Y-m-d H:i',$detail['version']);
				$detail = $this->getNewsAndHouse($detail);
				$detail['catepath'] = $detail['catepath'];


				//替换内容中出现的词条
				if(!empty($detail['content']))
				{
					$s = D('Search', 'Logic', 'Common');
					$detail['content'] = $s->renderingContent($detail['content']);

				}

				//是否显示大纲
				if(strpos($detail['content'],'<generalize>') !== false)
				{
					$this->assign('show_title_nav',true);
				}

				$this->kbcount($detail);
			}
			else
			{
				$this->error('知识不存在');
				exit;
			}

		}
		else
		{
			$this->error('知识ID错误');
			exit;
		}

		$cities = C('CITIES.ALL');
		foreach ($cities as $key => $city) {
			if ($this->_city['cn'] == $city['cn'])
			{
				$city_en = $key;
				break;
			}
		}
		$binds['register'] = 0;
		$this->assign('info', $detail);
		$this->assign('jsflag', 'kb_show');
		$this->assign('city_en', $city_en);
		$this->assign('more',getMore());
		$this->assign('binds',$binds);

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'kd_info');
		$this->assign('custom_id', $id);
		$this->assign('kd_info_xgzx', '#ln=kd_info_xgzx');
		$this->assign('kd_info_xglp', '#ln=kd_info_xglp');

		$this->display();
	}

	private function getNewsAndHouse($info)
	{
		$lInfos = D('Infos', 'Logic', 'Common');
		if ($info['rel_news'])
		{
			$rel_news = $info['rel_news'];
			$newsids = array();
			foreach ($rel_news as $key => $news)
			{
				$newsids[] = $news['id'];
				$tmp_news[$news['id']] = $news;
			}
			$newslist = $lInfos->getNews($newsids);
			if ($newslist)
			{
				foreach ($newslist as $k => $value) {
					if(array_key_exists($k, $tmp_news))
					{
						$newslist[$k]['title'] = empty($tmp_news[$k]['title']) ? $newslist[$k]['title'] : $tmp_news[$k]['title'];
					}
					else
					{
						unset($tmp_news[$k]);
					}
				}
				$info['rel_news'] = $newslist;
			}
		}
		else
		{
			$result = $lInfos->relNews($info['tags'], 5);
			if ($result)
			{
				$info['rel_news'] = $result;
			}
		}

		if ($info['rel_house'])
		{
			$rel_house = $info['rel_house'];
			$houseids = array();
			foreach ($rel_house as $k => $house)
			{
				$houseids[] = $house['site'].$house['hid'];
				$tmp_house[$house['site'].$house['hid']] = $house;
			}
			$houselist = $lInfos->getHouse($houseids);
			if ($houselist)
			{
				foreach ($houselist as $key => $value) {
					if (array_key_exists($key, $tmp_house))
					{
						$houselist[$key]['salephone'] = $houselist[$key]['phone_extension'] ? "4006108616,2{$houselist[$key]['phone_extension']}" : '4006108616';
						$houselist[$key]['name'] = !empty($tmp_house[$key]['name']) ? $tmp_house[$key]['name'] : $houselist[$key]['name'];
						//$houselist[$k]['price_display'] = $tmp_house[$k]['price_display'];
						//$houselist[$k]['pic_s320'] = $tmp_house[$k]['pic_s320'];
					}
					else
					{
						unset($temp_houses[$key]);
					}
				}
				$info['rel_house'] = $houselist;
			}
		}
		return $info;
	}


	private function kbcount($info)
	{
		if ($info)
		{
			$mVisitStats = D('VisitStats', 'Model', 'Common');
			$data['uid'] = 1;
			$data['relid'] = $info['id'];
			$data['reltype'] = 'kb';
			$data['relcateid'] = $info['catepath'];
			$data['ctime'] = NOW_TIME;
			return $mVisitStats->add($data);
		}
		return false;
	}
}
