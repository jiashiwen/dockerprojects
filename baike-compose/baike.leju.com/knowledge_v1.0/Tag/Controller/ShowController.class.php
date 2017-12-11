<?php
/**
 * 知识库 百科词条查看
 * @author
 */
namespace Tag\Controller;

class ShowController extends BaseController {
	/**
	 * 查看词条内容页面
	 */
	public function index() {
		$id = I('get.id', '', 'trim,strip_tags,htmlspecialchars');
		if(empty($id))
		{
			//不传词条ID引导回首页
			redirect('/tag/');
		}
		filterInput($id);

		$r = S(C('REDIS'));
		$detail = $r->get("wiki:tag:detail:{$id}");
		if($detail)
		{
			$this->assign('detail', $detail);
			curl_get(C('DATA_TRANSFER_API_URL').'api/item?id='. $id); //推送一个点击量
		}
		else
		{
			//词条详情
			$detail_url = C('DATA_TRANSFER_API_URL').'api/item?id='. $id;
			$detail_api = curl_get($detail_url);

			$detail = json_decode($detail_api['result'], true);
			$detail = @$detail['result'][0];
			if(empty($detail))
			{
				$this->error('词条不存在');
			}

			$recommends = D('Infos', 'Logic', 'Common');
			//百科相关咨询
			if(!empty($detail['news']))
			{
				//拼接ids
				$temp_news = $temp_push = array();
				foreach($detail['news'] as $v)
				{
					$temp_news[$v['id']] = $v;
					array_push($temp_push, $v['id']);
				}

				$info = $recommends->getNews($temp_push);

				//过滤已删除的新闻
				foreach($temp_news as $k=>$v)
				{
					if(array_key_exists($k, $info))
					{
						$temp_news[$k]['title'] || $temp_news[$k]['title'] = $info[$k]['title'];
						$temp_news[$k]['media'] = $info[$k]['media'];
						$temp_news[$k]['createtime'] = $info[$k]['createtime'];
						$temp_news[$k]['m_url'] = $info[$k]['m_url'];
					}
					else
					{
						unset($temp_news[$k]);
					}
				}

				$detail['news'] = array_values($temp_news);
			}
			else
			{
				//没有词条的话需要解析标签
				$tags = $recommends->relNews($detail['tags'], 5);
				$detail['news'] = array_values($tags);
			}

			//百科相关楼盘
			if(!empty($detail['house']))
			{
				//拼接ids
				$temp_houses = $temp_push = array();
				foreach($detail['house'] as $v)
				{
					$temp_houses[$v['site'].$v['hid']] = $v;
					array_push($temp_push, $v['site'].$v['hid']);
				}

				$info = $recommends->getHouse($temp_push);

				//过滤已删除的楼盘
				foreach($temp_houses as $k=>$v)
				{
					if(array_key_exists($k, $info))
					{
						$temp_houses[$k]['salephone'] = $info[$k]['phone_extension'] ? "4006108616,2{$info[$k]['phone_extension']}" : '4006108616';
						$temp_houses[$k]['price_display'] = $info[$k]['price_display'];
						$temp_houses[$k]['pic_s320'] = $info[$k]['pic_s320'];
						$temp_houses[$k]['m_url'] = $info[$k]['m_url'];
						$temp_houses[$k]['city'] = $info[$k]['city'];
					}
					else
					{
						unset($temp_houses[$k]);
					}
				}

				$detail['house'] = array_values($temp_houses);
			}

			//替换内容中出现的词条
			if(!empty($detail['content']))
			{
				$s = D('Search', 'Logic', 'Common');
				$detail['content'] = $s->renderingContent($detail['content']);
			}

			$this->assign('detail',$detail);
			$r->set("wiki:tag:detail:{$id}", $detail, 86400);
		}

		//是否显示大纲
		if(strpos($detail['content'],'<generalize>') !== false)
		{
			$this->assign('show_title_nav',true);
		}

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_info');
		$this->assign('custom_id', $id);

		$this->assign('more',getMore());
		$this->setPageInfo();
		$this->display();
	}

	/*
	 * 词条详情页替代版
	 */
	public function index2() {
		$id = I('get.id', '', 'trim,strip_tags,htmlspecialchars');
		if(empty($id))
		{
			//不传词条ID引导回首页
			redirect('/tag/');
		}
		filterInput($id);

		$r = S(C('REDIS'));
		$detail = $r->get("wiki:tag:detail2:{$id}");
		if($detail)
		{
			$this->assign('detail', $detail);
		}
		else
		{
			//词条详情
			$mW = D('wiki', 'Model', 'Common');
			$detail = $mW->where(array('title'=>$id))->find();

			if(empty($detail))
			{
				$this->error('词条不存在');
			}

			//拼接数据
			$detail['tags'] = explode(' ', $detail['tags']);
			$detail['rel_news'] = json_decode($detail['rel_news'], true);
			$detail['rel_house'] = json_decode($detail['rel_house'], true);

			$recommends = D('Infos', 'Logic', 'Common');
			//百科相关咨询
			if(!empty($detail['rel_news']))
			{
				//拼接ids
				$temp_news = $temp_push = array();
				foreach($detail['rel_news'] as $v)
				{
					$temp_news[$v['id']] = $v;
					array_push($temp_push, $v['id']);
				}

				$info = $recommends->getNews($temp_push);

				//过滤已删除的新闻
				foreach($temp_news as $k=>$v)
				{
					if(array_key_exists($k, $info))
					{
						$temp_news[$k]['title'] || $temp_news[$k]['title'] = $info[$k]['title'];
						$temp_news[$k]['media'] = $info[$k]['media'];
						$temp_news[$k]['createtime'] = $info[$k]['createtime'];
						$temp_news[$k]['url'] = $info[$k]['url'];
					}
					else
					{
						unset($temp_news[$k]);
					}
				}

				$detail['rel_news'] = array_values($temp_news);
			}
			else
			{
				//没有词条的话需要解析标签
				$tags = $recommends->relNews($detail['tags'], 5);
				$detail['rel_news'] = array_values($tags);
			}

			//百科相关楼盘
			if(!empty($detail['rel_house']))
			{
				//拼接ids
				$temp_houses = $temp_push = array();
				foreach($detail['rel_house'] as $v)
				{
					$temp_houses[$v['site'].$v['hid']] = $v;
					array_push($temp_push, $v['site'].$v['hid']);
				}

				$info = $recommends->getHouse($temp_push);

				//过滤已删除的楼盘
				foreach($temp_houses as $k=>$v)
				{
					if(array_key_exists($k, $info))
					{
						$temp_houses[$k]['salephone'] = $info[$k]['phone_extension'] ? "4006108616,2{$info[$k]['phone_extension']}" : '4006108616';
						$temp_houses[$k]['price_display'] = $info[$k]['price_display'];
						$temp_houses[$k]['pic_s320'] = $info[$k]['pic_s320'];
						$temp_houses[$k]['url'] = $info[$k]['url'];
					}
					else
					{
						unset($temp_houses[$k]);
					}
				}

				$detail['rel_house'] = array_values($temp_houses);
			}

			//替换内容中出现的词条
			if(!empty($detail['content']))
			{
				$s = D('Search', 'Logic', 'Common');
				$detail['content'] = $s->renderingContent($detail['content'], 0, 'b');
			}

			$r->set("wiki:tag:detail2:{$id}", $detail, 86400);
			$this->assign('detail',$detail);
		}

		//是否显示大纲
		if(strpos($detail['content'],'<generalize>') !== false)
		{
			$this->assign('show_title_nav',true);
		}

		//统计代码
		$this->assign('city', cookie('M_CITY'));
		$this->assign('level1_page', 'baike');
		$this->assign('level2_page', 'wd_info');
		$this->assign('custom_id', $id);

		$this->wkcount($detail['id']);
		$this->assign('more',getMore());
		$this->setPageInfo();
		$this->display();
	}

	//统计
	private function wkcount($id)
	{
		if($id)
		{
			$mV = D('VisitStats', 'Model', 'Common');
			$data = array();
			$data['uid'] = intval(cookie('M_UID'));
			$data['relid'] = $id;
			$data['reltype'] = 'wiki';
			$data['ctime'] = NOW_TIME;
			$mV->add($data);

			$mW = D('wiki', 'Model', 'Common');
			$mW->where(array('id'=>$id))->setInc('hits', 1);

			return true;
		}
		return false;
	}

}