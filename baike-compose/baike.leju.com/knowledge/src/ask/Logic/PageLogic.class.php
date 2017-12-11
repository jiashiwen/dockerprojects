<?php
/**
 * 页面核心逻辑基础类
 * @author Robert <yongliang1@leju.com>
 */

namespace ask\Logic;

class PageLogic {
	protected $data = array();
	protected $_device = 'pc';
	protected $_type = 'page';
	protected $_flush = false;

	public function setType( $type='api' ) {
		if ( in_array($type, array('api', 'page')) ) {
			$this->_type = $type;
		}
		return $this;
	}
	/**
	 * 问答首页页面逻辑
	 *
	 */
	public function pc_index_logic() {
		# 焦点推荐
		$lRecommend = D('Recommend', 'Logic', 'Common');
		$list = $lRecommend->getAskFocus(5);
		$focus = array();
		foreach ( $list as $i => $item ) {
			array_push($focus, array(
				'id' => $item['id'],
				'cateid' => $item['cateid'],
				'pic' => $item['extra']['imgSrc'],
				'title' => $item['title'],
				'desc' => strip_tags($item['desc']),
			));
		}

		# 栏目列表
		$lCate = D('Cate', 'Logic', 'Common');
		$catetree = $lCate->getIndexTopCategories('qa');
		$binds = array(
			'focus' => $focus,
			'catetree' => $catetree,
		);


		if ( $this->_device == 'pc' ) {
			# 热门栏目
			$binds['most_categories'] = $this->hot_categories(5);

			# 中部 热门话题
			$binds['agg_tags'] = $this->agg_tags(8);

			# 中部下方 猜你想知道
			$binds['hot_tags'] = $this->hot_tags(4);
			# 中部下方 获取热门回答列表
			$binds['most_answers'] = $this->most_answers(5);
			$binds['hot_answers'] = $this->hot_answers(1, 3);

			# 右侧下方 待帮助/回答
			$binds['need_answer'] = $this->need_answer(4);
			# 加载专家配置
			$binds['professors'] = $this->load_professors();
		} else {
			# 热门问题
			$binds['q'] = $this->hot_questions(1, 10);
			# 热门知识
			$list = $this->hot_knowledge(24);
			$binds['hot_kb'] = array_chunk($list, 8);
		}

		return $binds;
	}

	/**
	 * 加载专家列表
	 */
	public function load_professors() {
		$pro = array(
			'20000002800886' => array(
				'usernick' => '方欢',
				'id' => 20000002800886,
				'email' => 'ljmf_01@sina.com',
				'ask' => '//cdn.leju.com/qapc/images/newCard01.jpg',
				'index' => '//cdn.leju.com/qapc/images/z_card1.jpg',
				'detail' =>  '//cdn.leju.com/qapc/images/ty_card1.png',
			),
			'20000002800887' => array(
				'usernick' => '吕茜',
				'id' => 20000002800887,
				'email' => 'ljmf_02@163.com',
				'ask' => '//cdn.leju.com/qapc/images/newCard02.jpg',
				'index' => '//cdn.leju.com/qapc/images/z_card2.jpg',
				'detail' =>  '//cdn.leju.com/qapc/images/ty_card2.png',
			),
			'20000002800888' => array(
				'usernick' => '于飞',
				'id' => 20000002800888,
				'email' => 'ljmf_03@163.com',
				'ask' => '//cdn.leju.com/qapc/images/newCard03.jpg',
				'index' => '//cdn.leju.com/qapc/images/z_card3.jpg',
				'detail' =>  '//cdn.leju.com/qapc/images/ty_card3.png',
			),
		);
		return $pro;
	}

	public function pc_ask_logic() {
		$binds['hot_tags'] = $this->hot_tags(4);
		return $binds;
	}

	# 热门回答 (PC端) v1.1 新版 pc 问答
	public function hot_answers( $page=1, $pagesize=3 ) {
		$result = array('status'=>true, 'reason'=>'获取成功');
		$device = ( $this->_device=='pc' ) ? 'pc' : 'touch';
		$where = array(
			'status'=>array('in', array(22,23))
		);
		$order = 'i_hits desc';
		$ret = $this->_getlist($where, $order, $page, $pagesize);
		if ( $ret ) {

			$total = intval($ret['total']);
			# 分页数据
			$form = array(
				'page' => $page,
				'total' => $total,
				'pagesize' => $pagesize,
			);
			$pager = $this->linkopts($form, $total, 'index');
			// $pager = array(
			// 	'page' => $page,
			// 	'pagesize' => $pagesize,
			// 	'total' => $total,
			// 	'count' => ceil($total/$pagesize),
			// );
			// $pager['hasnext'] = ( $pager['count'] > $pager['page'] ) ? 1 : 0;
			$result['pager'] = $pager;

			$mAnswers = D('Answer', 'Model', 'Common');
			$list = array();
			$row_filter = array_flip(array('id', 'title', 'desc', 'ctime', 'status', 'anonymous', 'usernick', 'i_replies', 'tagsinfo', 'catenamepath'));
			foreach ( $ret['list'] as $i => $item ) {
				if ( trim($item['usernick'])=='' ) { $item['usernick']='乐居网友'; }
				if ( intval($item['anonymous'])==1 ) { $item['usernick']='乐居网友'; }
				$item = array_intersect_key($item, $row_filter);
				$item['url'] = url('show', array($item['id']), $device, 'ask');
				if ( is_array($item['tagsinfo']) ) {
					foreach ( $item['tagsinfo'] as $_i => &$tag ) {
						$tag_filter = array_flip(array('id','name'));
						$tag = array_intersect_key($tag, $tag_filter);
						$tag['url'] = url('agg', array($tag['id']), $device, 'ask');
					}
				}
				if ( is_array($item['catenamepath']) ) {
					$cates = array();
					foreach ( $item['catenamepath'] as $cateid => $catename ) {
						array_push($cates, array(
							'id'=>$cateid,
							'name'=>$catename,
							'url'=>url('agg', array($cateid), $device, 'ask'),
						));
					}
					$item['catenamepath'] = $cates;
				}
				$item['id'] = intval($item['id']);
				$item['ctime'] = intval($item['ctime']);
				$item['i_replies'] = intval($item['i_replies']);
				$item['anonymous'] = intval($item['anonymous']);
				$item['status'] = intval($item['status']);

				$ans_where = array(
					'status' => array('in', array(21,22,23)),
				);
				$ans = $mAnswers->field('id, usernick, anonymous, ctime')
								->order('ctime desc')
								->where($ans_where)
								->find();
				if ( $ans ) {
					$ans['id'] = intval($ans['id']);
					$ans['anonymous'] = intval($ans['anonymous']);
					$ans['ctime'] = intval($ans['ctime']);
					if ( trim($ans['usernick'])=='' ) { $ans['usernick']='乐居网友'; }
					if ( $ans['anonymous']==1 ) { $ans['usernick']='乐居网友'; }
				} else {
					$ans = false;
				}

				$item['last_answer'] = $ans;
				array_push($list, $item);
			}
			$result['list'] = $list;
		}
		return $result;
	}

	# 热门知识 (移动端)
	public function hot_knowledge( $pagesize=8 ) {
		$lSearch = D('Search', 'Logic', 'Common');
		$keyword = '';
		$opts = array(
			array('false', '_deleted'),
		);
		$prefix = array();
		$limit = $pagesize * $page;
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_origin.content');
		$ret = $lSearch->select(1, $limit, $keyword, $opts, $prefix, $order, $fields, 0, 'knowledge');
		$result = array();
		foreach ($ret['list'] as $key => $value) {
			array_push($result, array(
				'id'=>$value['_id'],
				'title'=>$value['_title'],
				'url' => url('show', array($value['_id']), $this->_device, 'baike'),
			));
		}
		return $result;
	}
	# 热门问答 (移动端)
	public function hot_questions( $page=1, $pagesize=10 ) {
		$key = 'TOUCH:INDEX:HOTQ:PAGE'.$page; // cache key
		$expire = 60; // cache data 60 seconds

		$result = array('status'=>true, 'reason'=>'获取成功');
		$where = array('status'=>array('in', array(21,22,23)));
		$order = 'i_replies desc, i_attention desc';
		$ret = $this->_getlist($where, $order, $page, $pagesize);
		if ( $ret ) {
			$list = array();
			$device = ( $this->_device=='pc' ) ? 'pc' : 'touch';
			$row_filter = array_flip(array('id', 'title', 'ctime', 'anonymous', 'usernick', 'i_replies', 'tagsinfo', 'catenamepath'));
			foreach ( $ret['list'] as $i => $item ) {
				if ( trim($item['usernick'])=='' ) { $item['usernick']='乐居网友'; }
				if ( intval($item['anonymous'])==1 ) { $item['usernick']='乐居网友'; }
				$item = array_intersect_key($item, $row_filter);
				$item['url'] = url('show', array($item['id']), $device, 'ask');
				if ( is_array($item['tagsinfo']) ) {
					foreach ( $item['tagsinfo'] as $_i => &$tag ) {
						$tag_filter = array_flip(array('id','name'));
						$tag = array_intersect_key($tag, $tag_filter);
						$tag['url'] = url('agg', array($tag['id']), $device, 'ask');
					}
				}
				if ( is_array($item['catenamepath']) ) {
					$cates = array();
					foreach ( $item['catenamepath'] as $cateid => $catename ) {
						array_push($cates, array(
							'id'=>$cateid,
							'name'=>$catename,
							'url'=>url('agg', array($cateid), $device, 'ask'),
						));
					}
					$item['catenamepath'] = $cates;
				}
				array_push($list, $item);
			}
			$result['list'] = $list;
			// 处理分页相关信息
			$pagesize = 10;
			$total = intval($ret['total']);
			$pager = array(
				'page' => $page,
				'pagesize' => $pagesize,
				'total' => $total,
				'count' => ceil($total/$pagesize),
			);
			$pager['hasnext'] = ( $pager['count'] > $pager['page'] ) ? 1 : 0;
			$result['pager'] = $pager;
		} else {
			$result['status'] = false;
			$result['reason'] = '接口错误';
		}
		return $result;
	}

	# 热门栏目
	protected function hot_categories($num=5) {
		# 热门栏目
		$mCategories = D('Categories', 'Model', 'Common');
		$where = array('status'=>0, 'type'=>'qa', 'level'=>2);
		$list = $mCategories->where($where)->order('i_hits desc')->limit($num)->select();
		return $list;
	}
	# 热门回答列表 最多回复 倒排
	protected function most_answers($num=5, $page=1) {
		# 热门回答
		$mQuestion = D('Question', 'Model', 'Common');
		$where = array('status'=>array('in', array('22,23')));
		$list = $mQuestion->where($where)->order('i_replies desc')->page($page, $num)->select();
		$this->compatible($list);
		return $list;
	}
	# 等待帮助
	protected function need_answer($num=4) {
		# 待回答
		$mQuestion = D('Question', 'Model', 'Common');
		$where = array('status'=>array('in', array('21')));
		$list = $mQuestion->where($where)->order('ctime desc')->limit($num)->select();
		$this->compatible($list);
		return $list;
	}

	public function _get_api_tags($max) {
		// 如果 7 天内没有数据 使用垫底数据
		$hot_tags = array(
			'买房' => array('id'=>'1355', 'name'=>'买房', 'hits'=>0),
			'卖房' => array('id'=>'1894', 'name'=>'卖房', 'hits'=>0),
			'学区房' => array('id'=>'1231', 'name'=>'学区房', 'hits'=>0),
			'住房公积金' => array('id'=>'49', 'name'=>'住房公积金', 'hits'=>0),
			'贷款' => array('id'=>'2022', 'name'=>'贷款', 'hits'=>0),
			'装修知识' => array('id'=>'2742', 'name'=>'装修知识', 'hits'=>0),
			'二手房' => array('id'=>'66', 'name'=>'二手房', 'hits'=>0),
			'毛胚房' => array('id'=>'4707', 'name'=>'毛胚房', 'hits'=>0),
		);
		return $hot_tags;
		$redis = S(C('REDIS'));
		$key = 'QA:HOT:TAGS';
		$tags = $redis->get($key);
		if ( !$tags || $this->_flush ) {
			$expire = strtotime(date('Y-m-d 23:59:59')) - NOW_TIME;
			$day = 7;
			$hot_tags = getHotTags($max, $day);

			if ( empty($hot_tags) || count($hot_tags)<8 ) {
				// 如果 7 天内没有数据 使用垫底数据
				$hot_tags = array(
					array('id'=>'1355', 'name'=>'买房', 'hits'=>0),
					array('id'=>'1894', 'name'=>'卖房', 'hits'=>0),
					array('id'=>'1231', 'name'=>'学区房', 'hits'=>0),
					array('id'=>'49', 'name'=>'住房公积金', 'hits'=>0),
					array('id'=>'2022', 'name'=>'贷款', 'hits'=>0),
					array('id'=>'2742', 'name'=>'装修知识', 'hits'=>0),
					array('id'=>'71', 'name'=>'政策', 'hits'=>0),
					array('id'=>'4707', 'name'=>'毛胚房', 'hits'=>0),
				);
				return $hot_tags;
			}
			$tagnames = array();
			$tags = array();
			foreach ( $hot_tags as $i => $tag ) {
				$tagname = trim($tag['tag']);
				// 用于排除重复
				if ( in_array($tagname, $tagnames) ) {
					continue;
				}
				array_push($tagnames, $tagname);
				$tags[$tagname] = array('name'=>$tagname, 'hits'=>$tag['hits'], 'id'=>$tag['tag_id']);
			}
			// 缓存热门标签列表
			$redis->setEx($key, $expire, json_encode($tags));
		}
		return $tags;
	}
	# 相关话题(猜你想知道)
	# 统计标签访问量，并按标签访问量最高的前 N 项，获取相关数据
	public function hot_tags($num=4) {
		$expire = 300; // 问题列表缓存5分钟
		$max_page = 5;
		$max = $num * $max_page;

		$redis = S(C('REDIS'));

		$tags = $this->_get_api_tags($max);

		$list = array();
		$_ctags = array_rand($tags, $num);
		foreach ( $_ctags as $_i => $_tagname ) {
			$ctags[$_tagname] = $tags[$_tagname];
			$tagid = intval($tags[$_tagname]['id']);
			$key = 'QA:HOT:TAGS:QUESTIONS:'.$tagid;
			$ret = $redis->get($key);
			if ( !$ret || $this->_flush ) {
				$mQuestion = D('Question', 'Model', 'Common');
				$order = 'i_hits desc';
				$limit = 5;
				$where = array(
					'status'=>array('in', array(21, 22,23)),
					'i_replies'=>array('neq', 0),
					// 'last_best'=>array('neq', 0),
					'tags' => array('like', "%,{$tagid},%"),
					/*
					'tags' => array('like', 
						array(
							"{$_tagname}",
							"{$_tagname} %",
							"% {$_tagname}",
							"% {$_tagname} %",
							'OR',
						),
					),
					*/
				);
				$ret = $mQuestion->field('id, title, i_hits, i_replies, utime, tags')->where($where)->order($order)->limit($limit)->select();
				foreach ( $ret as $_i => &$item ) {
					$item['url'] = url('show', array($item['id']), $this->_device, 'ask');
					// $item['tagsinfo'] = array_values($this->convert_tags($item['tags']));
					$item['tagsinfo'] = array_values($this->convert_tagids($item['tagids']));
					// foreach ( $item['tagsinfo'] as $_t => &$tag ) {
					// 	$tag['url'] = url('agg', array($tag['id']), $this->_device, 'ask');
					// }
				}
				// 如果条件查询有结果，就进行一次缓存
				if ( $ret ) {
					$redis->setEx($key, $expire, json_encode($ret));
				}
			} // end cached ret
			// 拼装结果列表
			array_push($list, array(
				'id' => $tags[$_tagname]['id'],
				'hits' => $tags[$_tagname]['hits'],
				'title' => $_tagname,
				'list' => $ret,
			));
		}
		$result = array(
			'status' => true,
			'list' => $list,
		);
		return $result;
	}
	# 热门话题
	# 通过 7 天内的问题，反向统计标签，并按标签中的问题数量进行排序，取前 N 个标签并显示
	public function agg_tags($num=8) {
		$key = 'STATS:AGG:QA:TAGS:'.$num;
		$redis = S(C('REDIS'));
		$result = $redis->get($key);
		// echo '<!--', PHP_EOL, var_export($result, true), PHP_EOL, '-->', PHP_EOL;
		if ( !$result || $this->_flush ) {
			$result = array(
				'status' => true,
				'list' => array(),
				'time' => NOW_TIME,
			);

			$max = 20;
			$tags = $this->_get_api_tags($max);
			$tags = array_slice($tags, 0, $num);
			// echo '<!--', PHP_EOL, var_export($tags, true), PHP_EOL, '-->', PHP_EOL;

			$mQuestion = D('Question', 'Model', 'Common');
			$end = date('Y-m-d 00:00:00', NOW_TIME);
			$start = date('Y-m-d 00:00:00', strtotime('-7 days'));
			$end = strtotime($end);
			$start = strtotime($start);
			$fields = array(
				'count(id) as c_questions',
				'sum(i_attention) as s_attentions',
				'sum(i_hits) as s_hits',
				'sum(i_replies) as s_replies'
			);
			$sorted = array();
			foreach ( $tags as $i => &$tag ) {
				$where = array(
					// 'ctime'=>array('between', array($start, $end)),
					'status'=>array('in', array(21,22,23)),
					'tagids'=>array('like', "%,{$tag['id']},%")
				);
				$stats = $mQuestion->field($fields)
					    ->where($where)->find();
				foreach ( $stats as $i => $item ) {
					$tag[$i] = intval($item);
				}
				$sorted[$tag['name']] = intval($stats['c_questions']);
				$order = 'i_hits desc';
				$limit = 3;
				$field = 'id, title';
				$tags[$tag['name']]['list'] = $mQuestion->field($field)->where($where)->order($order)->limit($limit)->select();
			}
			arsort($sorted);
			$sorted_tags = array();
			foreach ( $sorted as $tag => $stat ) {
				if ( !is_array($tags[$tag]) ) {
					continue;
				}
				array_push($sorted_tags, $tags[$tag]);
			}
			$result['list'] = $sorted_tags;
			/*
			echo '<!--', PHP_EOL, 
				 var_export($sorted, true), PHP_EOL,
				 var_export($tags, true), PHP_EOL, 
				 var_export($sorted_tags, true), PHP_EOL, 
				 '-->', PHP_EOL;
			*/
			$redis->set($key, $result);
			$expireat = strtotime(date('Y-m-d 23:59:59'));
			$redis->expireat($key, $expireat);
			$result['cached'] = false;
		} else {
			$result['cached'] = true;
		}

		return $result;
	}
	# 相关话题/标签
	# @问题详情页
	public function rel_tags_by_question($tagids) {
		$result = array();
		if ( isset($this->data['rel_tagids']) && !empty($this->data['rel_tagids']) ) {
			$tagids = &$this->data['rel_tagids'];

			$sorted = array();
			$tagsinfo = array();

			$mQuestion = D('Question', 'Model', 'Common');
			$end = date('Y-m-d 00:00:00', NOW_TIME);
			$start = date('Y-m-d 00:00:00', strtotime('-7 days'));
			$end = strtotime($end);
			$start = strtotime($start);
			$fields = array(
				'count(id) as c_questions',
				'sum(i_attention) as s_attentions',
				'sum(i_hits) as s_hits',
				'sum(i_replies) as s_replies'
			);
			$tagids = D('Tags', 'Logic', 'Common')->getTagnamesByTagids($tagids, 'pc');
			foreach ( $tagids as $i => &$tag ) {
				$where = array(
					'status' => array('in', array(21,22,23)),
					'tagids' => array('like', "%,{$tag['id']},%"),
				);
				$stats = $mQuestion->field($fields)
						->where($where)->find();
				// var_dump($mQuestion->getLastSql(), $stats);
				foreach ( $stats as $_i => $item ) {
					$tag[$_i] = intval($item);
				}
				$sorted[$i] = $tagids[$i]['s_hits'];
				$limit = 2;
				$order = 'i_hits desc';
				$field = 'id, title';
				$tagids[$i]['list'] = $mQuestion->field($field)->where($where)->order($order)->limit($limit)->select();
			}
			arsort($sorted);
			foreach ( $sorted as $_inx => $hits ) {
			 	array_push($tagsinfo, $tagids[$_inx]);
			}
			$result = $tagsinfo;
		}
		return $result;
	}
	public function compatible(&$list) {
		$lCate = D('Cate', 'Logic', 'Common');
		$mAnswers = D('Answers', 'Model', 'Common');
		$CPNs = array();
		foreach ( $list as $i => &$item ) {
			$cp = $item['catepath'];
			if ( !array_key_exists($item['catepath'], $CPNs) ) {
				$CPNs[$cp] = $this->filterCates($lCate->getPathName($cp, 'qa'));
			}
			$status = intval($item['status']);
			if ( in_array($status, array(22,23)) ) {
				$where = array(
					'qid'=>$item['id'],
					'status'=>array('in', array(21,22,23)),
				);
				$last_answer = $mAnswers->where($where)->order('id desc')->find();
				$item['last_answer'] = $last_answer;
			}
			$item['tags'] = trim(str_replace(',', ' ', $item['tags']));
			// $item['tagsinfo'] = array_values($this->convert_tags($item['tags']));
			$item['tagsinfo'] = $this->convert_tagids($item['tagids']);
			$item['catenamepath'] = $CPNs[$cp];
		}
		return true;
	}
	public function filterCates( $crumb=array() ) {
		if ( count($crumb)<2 ) {
			return false;
		}
		foreach ( $crumb as $cateid => $catename ) {
			if ( !$crumb[$cateid] ) {
				unset($crumb[$cateid]);
			}
		}
		return $crumb;
	}

	/**
	 * 栏目列表页
	 *
	 */
	public function pc_list_logic() {
		$binds = array('status'=>false);

		$cateid = I('get.id', 0, 'intval');
		$lCate = D('Cate', 'Logic', 'Common');
		$cateinfo = $lCate->getCateInfo($cateid, 'qa');
		if ( !$cateinfo ) {
			$binds['message'] = '指定的栏目不存在';
			return $binds;
		}

		# 当前栏目编号
		$binds['cateid'] = $cateid;

		$pagesize = 10;
		$page = I('get.page', 1, 'intval');
		$page = $page <= 1 ? 1 : $page;

		$order = I('get.order', '', 'trim');
		$orders = array(
			''=>'ctime desc',			// 默认 最新提问
			'zdhf'=>'i_replies desc',	// 最多回复
			'zdfw'=>'i_hits desc',		// 最多访问
		);
		if ( !array_key_exists($order, $orders) ) {
			$order = '';
		}
		$porder = $order;
		$binds['order'] = $order;

		$order = $orders[$order];
		$where = array(
			'status' => array('in', array(21,22,23)),
		);

		if ( $cateinfo['level']==1 ) {
			$where['catepath'] = array('like', '0-'.$cateid.'-%');
		}
		if ( $cateinfo['level']==2 ) {
			$where['cateid'] = $cateid;
		}
		$ret = $this->_getlist($where, $order, $page, $pagesize);
		$total = intval($ret['total']);
		$list = $ret['list'];

		// 请求来源为页面时处理以下扩展数据
		if ( $this->_type=='page' ) {
			# 栏目列表
			$binds['catetree'] = $this->_getcatetree();
			$binds['cateinfo'] = $cateinfo;

			if ( $this->_device=='pc' ) {
				# 猜你喜欢
				$ret = $this->latest_answers();
				$binds['latest_answers'] = $ret['list'];
				unset($ret);

				# 中部下方 猜你想知道
				$binds['hot_tags'] = $this->hot_tags(4);
			} else {
				# 热门知识
				$hot_baike = $this->hot_knowledge(24);
				$binds['hot_kb'] = array_chunk($hot_baike, 8);
			}
			# 分页数据
			$form = array(
				'id' => $cateid,
				'page' => $page,
				'order' => $porder,
				'total' => $total,
				'pagesize' => $pagesize,
			);
			$pager = $this->linkopts($form, $total, 'cate');
			$binds['pager'] = $pager;

			// 相关知识 数据准备 取列表中所有问题关联的标签，并通过标签进行相关知识的查询
			$tagsinfo = array();
			foreach ( $list as $i => $item ) {
				foreach ( $item['tagsinfo'] as $ti => $tags ) {
					$tagid = $tags['id'];
					$tagsinfo[$tagid] = $tags;
				}
			}

			// 初始下方的相关知识
			$tag = $tagsinfo ? $tagsinfo[array_rand($tagsinfo)]['name'] : '';
			$pagesize = 8;
			$binds['tagsinfo'] = $tagsinfo;
			$binds['guess'] = $this->relations($tag, $pagesize);
		}

		$binds['list'] = $list;
		$binds['total'] = $total;
		$binds['status'] = true;
		return $binds;
	}


	protected function linkopts_old($form, $total, $listtype='cate') {
		//封装linkopts
		$linkopts = array();
		$form['k'] && array_push($linkopts, "keyword={$form['keyword']}");
		$form['id'] && array_push($linkopts, "id={$form['id']}");

		$linkstring = '#';
		if ( $listtype=='cate' ) {
			$linkstring = !empty($linkopts) ? 
							url('list', array($form['id'], 'page'=>'#', $form['order']), 'pc', 'ask')
						  :
							url('list', array($form['id'], 1, $form['order']), 'pc', 'ask');
		}
		if ( $listtype=='agg' ) {
			$linkstring = !empty($linkopts) ? 
							url('agg', array($form['id'], 'page'=>'#', $form['order']), 'pc', 'ask')
						  :
							url('agg', array($form['id'], 1, $form['order']), 'pc', 'ask');
		}
		if ( $listtype=='search' ) {
			$linkstring = url('search', array($form['k'], '#', $form['order']), 'pc', 'ask');
			// $query = $form;
			// $query['page'] = '#';
			// unset($query['pagesize']);
			// $linkstring = $linkstring.'?'.urldecode(http_build_query($query));
		}
		// var_dump($form, $linkstring, $linkopts);
		$pagesize = 10;
		$opts = array(
			'first' => true, //首页
			'last' => true,	//尾页
			'prev' => true, //上一页
			'next' => true, //下一页
			'number' => 5, //显示页码数
			'linkstring' => $linkstring,
		);
		$pager = pager($form['page'], $total, $pagesize, $opts);
		return $pager;
	}

	protected function linkopts($form, $total, $listtype='cate') {
		$page = intval($form['page']);
		$pagesize = intval($form['pagesize']);
		$total = intval($form['total']);
		$pagecount = ceil($total/$pagesize);
		$page = $page < 1 ? 1 : $page;
		$page = $page > $pagecount ? $pagecount : $page;
		$next = $page < $pagecount ? $page + 1 : $pagecount;

		//封装linkopts
		$linkopts = array();

		$linkstring = '#';
		if ( $listtype=='index' ) {
			$linkstring = '/index/loadmore';
		}
		if ( $listtype=='cate' ) {
			$form['id'] && $linkopts['id'] = $form['id'];
			$linkstring = '/list/loadmore?';
		}
		if ( $listtype=='agg' ) {
			$form['id'] && $linkopts['id'] = $form['id'];
			$linkstring = '/agg/loadmore?';
		}
		if ( $listtype=='search' ) {
			$form['k'] && $linkopts['k'] = $form['k'];
			$linkstring = '/search/loadmore?';
		}
		if ( $linkstring!='#' ) {
			$linkstring .= http_build_query($linkopts);
		}

		$pager =  array(
			'next_api_url' => $linkstring,
			'page' => $page,
			'is_last' => ( $page >= $pagecount ) ? 1 : 0,
		);
		return $pager;
	}

	/**
	 * 搜索列表页
	 *
	 */
	public function pc_search_logic($keyword='') {
		$keyword = clean_xss(filterInput(clear_all($keyword)));
		$binds = array();
		$binds['keyword'] = $keyword;

		$pagesize = 10;
		$page = I('get.page', 1, 'intval');
		$page = $page <= 1 ? 1 : $page;

		if ( $this->_type=='page' ) {
			$order = isset($_GET['zdhf']) ? 'zdhf' : '';
		} else {
			$order = I('get.order', '', 'strtolower,trim');
		}
		$orders = array(
			''=>'ctime desc',			// 默认 最新提问
			'zdhf'=>'i_replies desc',	// 最多回复
			'zdfw'=>'i_hits desc',		// 最多访问
		);
		if ( !array_key_exists($order, $orders) ) {
			$order = '';
		}
		$porder = $order;
		$binds['order'] = $order;

		$order = $orders[$order];
		$where = array(
			'title' => array('like', "%{$keyword}%"),
			'status' => array('in', array(21,22,23)),
		);
		$ret = $this->_getlist($where, $order, $page, $pagesize);
		$total = intval($ret['total']);
		$list = $ret['list'];

		if ( $this->_type=='page' ) {
			if ( $this->_device == 'pc' ) {
				# 中部下方 猜你想知道
				$binds['hot_tags'] = $this->hot_tags(4);
			} else {
				# 热门知识
				$list = $this->hot_knowledge(24);
				$binds['hot_kb'] = array_chunk($list, 8);
			}
			# 分页数据
			$form = array(
				'k' => $keyword,
				'page' => $page,
				'order' => $porder,
				'total' => $total,
				'pagesize' => $pagesize,
			);
			$pager = $this->linkopts($form, $total, 'search');
			$binds['pager'] = $pager;

			# 栏目列表
			$binds['catetree'] = $this->_getcatetree();
			# 猜你喜欢
			$ret = $this->latest_answers();
			$binds['latest_answers'] = $ret['list'];
			unset($ret);

			// 相关知识 数据准备 取列表中所有问题关联的标签，并通过标签进行相关知识的查询
			$tagsinfo = array();
			foreach ( $list as $i => $item ) {
				foreach ( $item['tagsinfo'] as $ti => $tags ) {
					$tagid = $tags['id'];
					$tagsinfo[$tagid] = $tags;
				}
			}

			// 初始下方的相关知识
			$tag = $tagsinfo ? $tagsinfo[array_rand($tagsinfo)]['name'] : '';
			$pagesize = 8;
			$binds['tagsinfo'] = $tagsinfo;
			$binds['guess'] = $this->relations($tag, $pagesize);
		}

		$binds['list'] = $list;
		$binds['total'] = $total;
		$binds['status'] = true;
		return $binds;
	}

	public function pc_agg_logic() {
		$binds = array();
		$tagid = I('get.id', 0, 'intval');
		if ( $tagid===0 ) {
			return false;
		}

		$binds['id'] = $id;
		$pagesize = 10;
		$page = I('get.page', 1, 'intval');
		$page = $page <= 1 ? 1 : $page;

		$order = I('get.order', '', 'strtolower,trim');
		$orders = array(
			''=>'ctime desc',			// 默认 最新提问
			'zdhf'=>'i_replies desc',	// 最多回复
			'zdfw'=>'i_hits desc',		// 最多访问
		);
		if ( !array_key_exists($order, $orders) ) {
			$order = '';
		}
		$porder = $order;
		$binds['order'] = $order;

		$order = $orders[$order];
		$where = array(
			'tagids'=> array('like', "%,$tagid,%"),
			'status' => array('in', array(21,22,23)),
		);
		$ret = $this->_getlist($where, $order, $page, $pagesize);
		$total = intval($ret['total']);
		$list = $ret['list'];

		if ( $this->_type=='page' ) {
			if ( $this->_device == 'pc' ) {
				# 中部下方 猜你想知道
				$binds['hot_tags'] = $this->hot_tags(4);
			} else {
				# 热门知识
				$list = $this->hot_knowledge(24);
				$binds['hot_kb'] = array_chunk($list, 8);
			}
			# 分页数据
			$form = array(
				'id' => $tagid,
				'page' => $page,
				'order' => $porder,
				'total' => $total,
				'pagesize' => $pagesize,
			);
			$pager = $this->linkopts($form, $total, 'agg');
			$binds['pager'] = $pager;

			// 相关知识 数据准备 取列表中所有问题关联的标签，并通过标签进行相关知识的查询
			$tagsinfo = array();
			foreach ( $list as $i => $item ) {
				foreach ( $item['tagsinfo'] as $ti => $tags ) {
					$_tagid = $tags['id'];
					$tagsinfo[$_tagid] = $tags;
				}
			}

			# 栏目列表
			$binds['catetree'] = $this->_getcatetree();

			# 当前标签的标签属性信息
			$lTags = D('Tags', 'Logic', 'Common');
			$taginfo = $lTags->getTagnameByTagid($tagid);
			$binds['taginfo'] = $taginfo;

			# 猜你喜欢
			$ret = $this->latest_answers();
			$binds['latest_answers'] = $ret['list'];
			unset($ret);

			// 初始下方的相关知识
			$tag = $tagsinfo ? $tagsinfo[array_rand($tagsinfo)]['name'] : '';
			$pagesize = 8;
			$binds['tagsinfo'] = $tagsinfo;
			$binds['guess'] = $this->relations($tag, $pagesize);
		}

		$binds['list'] = $list;
		$binds['total'] = $total;
		$binds['status'] = true;
		return $binds;
	}

	// 获取最新回答的问题列表
	// #COMMON
	protected function latest_answers() {
		$where = array(
			'status' => array('in', array(21,22,23)),
			'i_replies' => array('gt', 0),
		);
		return $this->_getlist($where, 'utime desc', 1, 8);
	}

	public function _getlist($where=array(), $order='id desc', $page=1, $pagesize=10, $field='*') {
		$mQuestion = D('Question', 'Model', 'Common');
		$total = $mQuestion->where($where)->count();
		$list = $mQuestion->field($field)->where($where)->order($order)->page($page, $pagesize)->select();
		// echo '<!--', PHP_EOL, $mQuestion->getLastSql(), PHP_EOL, print_r($list, true), PHP_EOL, '-->', PHP_EOL;
		$this->compatible($list);
		// echo '<!--', PHP_EOL, print_r($list, true), PHP_EOL, '-->', PHP_EOL;
		return array('total'=>$total, 'list'=>$list);
	}

	protected function _getcatetree() {
		# 栏目列表
		$lCate = D('Cate', 'Logic', 'Common');
		$catetree = $lCate->getIndexTopCategories('qa');
		return $catetree;
	}
	/**
	 * 将 tag 转换为 tagid
	 */
	public function convert_tags($tags) {
		$mTags = D('Tags', 'Model', 'Common');
		$tags = explode(' ', $tags);

		$result = array();
		if ( !is_array($tags) || empty($tags) ) {
			return $result;
		}

		$_tags = $tags;
		$_tags = array_unique($_tags);
		$_tags = implode("','", $_tags);
		if ( $_tags != '' ) {
			$ret = $mTags->where("name in ('{$_tags}')")->select();
			$cv_fields = array('id', 'i_total', 'source', 'status');
			foreach ( $ret as $i => $item ) {
				$tagname = trim($item['name']);
				foreach ( $cv_fields as $_i => $f ) {
					$item[$f] = intval($item[$f]);
				}
				$alltags[$tagname] = $item;
				$result[$tagname] = $item;
			}
		}
		return $result;
	}
	// 将 tagids 转换为 tagsinfo 列表
	public function convert_tagids($tagids='') {
		return D('Tags', 'Logic', 'Common')->getTagnamesByTagids($tagids, $this->_device);
	}
	/**
	 * PC版详情页
	 *
	 */
	public function pc_show_logic($id=0, $userid=0) {
		$binds = array('status' => false,);

		$id = I('get.id', 0, 'intval');
		if ( $id<=0 ) {
			$binds['message'] = '请指定要查看的问题';
			return $binds;
		}


		$expire = 300;
		$key = 'QA:DETAIL:'.$id;
		$redis = S(C('REDIS'));
		$binds = $redis->get($key);
		if ( !$binds || $this->_flush ) {
			// 重新获取
			$binds = array('status' => false,);
			$mQuestion = D('Question', 'Model', 'Common');
			$Q = $mQuestion->find($id);
			if ( !$Q ) {
				$binds['message'] = '您要查看的问题不存在';
				return $binds;
			}
			$Q['status'] = intval($Q['status']);
			if ( !in_array($Q['status'], array(21,22,23)) ) {
				$binds['message'] = '您要查看的问题不存在.';
				return $binds;
			}

			// 处理扩展数据
			$Q['data'] = trim($Q['data']);
			$extra = array();
			if ( $Q['data']!='' && substr($Q['data'], 0, 1)=='{' ) {
				$extra = json_decode($Q['data'], true);
			}
			$extra['images'] = isset($extra['images']) ? (array)$extra['images'] : array();
			$extra['cover'] = '';
			if ( !empty($extra['images']) ) {
				// 封图，取第一张图片
				$images = $extra['images'];
				$extra['cover'] = array_shift($images);
			}
			$Q['data'] = $extra;

			$aids = array();	// 所有跟此问题有关的回复
			// $pagesize = 10;
			$binds['best'] = false;
			$mAnswers= D('Answer', 'Model', 'Common');
			// 如果存在最佳答案，则读取最佳答案
			if ( $Q['last_best']>0 ) {
				$where = array('qid'=>$id, 'status'=>23);
				$answer_best = $mAnswers->where($where)->limit(1)->select();
				if ( !$answer_best ) {
					// 数据异常，需要补偿
					$answer_best = array(array());
				} else {
					array_push($aids, $answer_best[0]['id']);
				}
				$binds['best'] = $answer_best[0];
				$answer_uid = intval($binds['best']['userid']);
				$professors = $this->load_professors();
				if ( array_key_exists($answer_uid, $professors) ) {
					$professor = $professors[$answer_uid];
				} else {
					$professor = false;
				}
				$binds['professor'] = $professor;
			}
			// 读取包含置顶在内的答案列表 (排除最佳答案)
			$where = array('qid'=>$id, 'status'=>array('in', array(21,22)));
			$order = 'status desc, ctime desc, id desc';
			$Ac = $mAnswers->where($where)->count();
			// $As = $mAnswers->where($where)->order($order)->page($page, $pagesize)->select();
			$As = $mAnswers->where($where)->order($order)->select();

			$pager = array();
			$binds['status'] = true;
			$binds['id'] = $id;
			$Q['tagsinfo'] = $this->convert_tagids($Q['tagids']);
			if ( intval($Q['cateid'])>0 ) {
				$lCate = D('Cate', 'Logic', 'Common');
				$Q['catename'] = $lCate->getCateName($Q['cateid'], 'qa');
			} else {
				$Q['catename'] = '';
			}
			# 栏目列表
			$catepath = explode('-', trim($Q['catepath'], '-'));
			$binds['catecrumbs'] = array();
			// var_dump($catepath);
			if ( count($catepath)>1 ) {
				$binds['catetree'] = $this->_getcatetree();
				$level1 = intval($catepath[1]);
				$binds['catecrumbs'][1] = array(
					'id' => $level1,
					'name' => $binds['catetree'][$level1]['name'],
				);
				if ( array_key_exists(2, $catepath) ) {
					$level2 = intval($catepath[2]);
					if ( array_key_exists($level2, $binds['catetree'][$level1]['son']) ) {
						$binds['catecrumbs'][2] = array(
							'id' => $level2,
							'name' => $binds['catetree'][$level1]['son'][$level2]['name'],
						);
					}
				}
			}

			$binds['question'] = $Q;
			$binds['answers'] = $As;
			$binds['pager'] = $pager;

			// 初始下方的相关知识
			$tag = $Q['tagsinfo'] ? $Q['tagsinfo'][array_rand($Q['tagsinfo'])]['name'] : '';
			$pagesize = 8;
			$binds['guess'] = $this->relations($tag, $pagesize);


			# 右侧上方 相关问答
			/* - 规则 -
			通过问题的相关标签调用相关问答，显示比例为Ｎ/ｎ ,Ｎ为显示总数量，ｎ 为标签数量，
			例如显示数量N=9，ｎ＝３，那么9／３＝３，每个标签取3个，按照回复数量倒叙，不调用没有回复的问题。
			*/
			// $binds['relations'] = $this->_relquestions($Q['tags']);
			$binds['relquestions'] = $this->_relquestions($Q['tagids'], 6, $id);
			# 右侧上方 相关话题
			$binds['rel_tags'] = $this->rel_tags_by_question($Q['tagids']);
			# 右部下方 获取热门回答列表
			$binds['most_answers'] = $this->most_answers(5);
			$redis->setEx($key, $expire, json_encode($binds));
			$binds['cached'] = false;
		} else {
			$binds['cached'] = true;
		}

		if ( $this->_device == 'pc' ) {
		} else {
			# 热门知识
			$list = $this->hot_knowledge(24);
			$binds['hot_kb'] = array_chunk($list, 8);
		}

		$binds['userid'] = $userid;
		// 是否存在采纳回复
		$binds['has_best'] = !!$binds['best'];
		// 当前用户的点赞数据
		$binds['goods'] = array();
		// 是否能回复
		$binds['can_reply'] = true;
		if ( $userid ) {
			// 如果提问者与登录者是同一个人
			if ( $userid==$binds['question']['userid'] ) {
				$binds['can_reply'] = false;
			}
			// 当前用户是否关注此问题
			$mOplogs = D('Oplogs', 'Model', 'Common');
			$where = array('uid'=>$userid, 'act'=>11, 'relid'=>$id);
			$exists = $mOplogs->where($where)->count();
			$binds['attentioned'] = $exists == 1 ? true : false;

			// 当前用户，当前问题所有回复的点赞
			$aids = array();
			if ( $binds['best'] ) {
				array_push($aids, $binds['best']['id']);
			}
			foreach ( $binds['answers'] as $i => $item ) {
				// if ( $userid>0 && $userid==$item['userid'] ) {
				// 	$binds['can_reply'] = false;
				// 	break;
				// }
				array_push($aids, $item['id']);
			}
			if ( $aids ) {
				$where = array('uid'=>$userid, 'act'=>31, 'relid'=>array('in', $aids));
				$goods = $mOplogs->where($where)->select();
				foreach ( $goods as $i => $igood ) {
					$binds['goods'][$igood['relid']] = $igood;
				}
				unset($goods);
			}
		} else {
			// 未登录的用户不能回复
			// $binds['can_reply'] = false;
		}
		// 问题访问统计计数
		$this->_visit_question($id, $userid);

		return $binds;
	}

	/**
	 * 指定标签名称，进行关联知识数据获取
	 */
	public function relations ( $tag='', $pagesize=8 ) {
		$result = array();
		$tagname = is_string($tag) ? $tag : trim($tag['name']);

		// 加缓存
		$src = D('Search', 'Logic', 'Common');
		$prefix = array();
		$opts = array();
		$opts['scope'] = array('', '_scope');

		$lCate = D('Cate', 'Logic', 'Common');

		$total = $pagesize + 10;
		$tagname!='' && $opts['tags'] = array($tagname, '_tags');
		$ret = $src->getRecommendBaike( $total, $prefix, $opts );
		// var_dump($tagname, $opts, $prefix);
		// $cates = array();
		$list = array();
		if ( empty($ret['result']) ) {
			$opts['tags'] = array('', '_tags');
			$ret = $src->getRecommendBaike( $total, $prefix, $opts );
		}
		foreach ( $ret['result'] as $i => $_item ) {
			/*
			$_cates = array();
			$cateinfo = array();
			$_cate = explode('-', trim($_item['_multi']['catepath'], '0-'));
			$_cateid = $_cate[count($_cate)-1];
			foreach ( $_cate as $_i => $_cid ) {
				if ( !array_key_exists($_cid, $cates) ) {
					$cates[$_cid] = $lCate->getCateName($_cid, 'kb');
				}
				if ( $cates[$_cid] ) {
					$_cates[$_i] = $cates[$_cid];
				}
				
				// array_push($cateinfo, array(
				// 	'id'=>$_cid,
				// 	'name'=>$cates[$_cid],
				// 	'url'=>url('cate', array($_cid), $this->_device, 'baike'),
				// ));
			}
			var_dump($_item['_id'], $_item['_multi']['catepath'], $_cate, $_cates);
			*/
			// var_dump($_item);
			array_push($list, array(
				'title' => $_item['_title'],
				'cover' => $_item['_origin']['cover'],
				'tags' => $this->convert_tags($_item['_tags']),
				'catepath' => $_item['_multi']['catepath'],
				// 'cates' => implode('-', $_cates),
				// 'cateinfo' => $cateinfo,
				'url' => url('show', array($_item['_id']), $this->_device, 'baike'),
			));
		}
		$result = array_slice($list, 0, $pagesize);

		return $result;
	}

	public function all_relations ( $tags='', $pagesize=8 ) {
		$result = array();
		$src = D('Search', 'Logic', 'Common');
		$opts = array();
		$opts['scope'] = array('', '_scope');

		$lCate = D('Cate', 'Logic', 'Common');

		foreach ( $tags as $tagname => $taginfo ) {
			$total = $pagesize + 10;
			$prefix = array();
			$tag!='' && $opts['tags'] = array($tagname, '_tags');
			$ret = $src->getRecommendBaike( $total, $prefix, $opts );
			// $cates = array();
			$list = array();
			foreach ( $ret['result'] as $i => $_item ) {
				/*
				$_cates = array();
				$cateinfo = array();
				$_cate = explode('-', trim($_item['_multi']['catepath'], '0-'));
				$_cateid = $_cate[count($_cate)-1];
				foreach ( $_cate as $_i => $_cid ) {
					if ( !array_key_exists($_cid, $cates) ) {
						$cates[$_cid] = $lCate->getCateName($_cid, 'kb');
					}
					if ( $cates[$_cid] ) {
						$_cates[$_i] = $cates[$_cid];
					}
					
					// array_push($cateinfo, array(
					// 	'id'=>$_cid,
					// 	'name'=>$cates[$_cid],
					// 	'url'=>url('cate', array($_cid), $this->_device, 'baike'),
					// ));
				}
				var_dump($_item['_id'], $_item['_multi']['catepath'], $_cate, $_cates);
				*/
				// var_dump($_item);
				array_push($list, array(
					'title' => $_item['_title'],
					'cover' => $_item['_origin']['cover'],
					'tags' => $this->convert_tags($_item['_tags']),
					'catepath' => $_item['_multi']['catepath'],
					// 'cates' => implode('-', $_cates),
					// 'cateinfo' => $cateinfo,
					'url' => url('show', array($_item['_id']), $this->_device, 'baike'),
				));
			}
			$list = array_slice($list, 0, $pagesize);

			$tagid = intval($taginfo['id']);
			$result[$tagid] = $list;
		}

		return $result;
	}

	/**
	 * 指定标签名称，进行相关问答数据获取
	 */
	public function _relquestions2 ( $tags='', $pagesize=6, $expid=0 ) {
		$result = array();
		if ( !$tags ) {
			return $result;
		}
		if ( is_string($tags) ) {
			$tags = explode(' ', $tags);
		}
		if ( !is_array($tags) ) {
			return $result;
		}
		$tagnum = count($tags);
		$num = ceil($pagesize / $tagnum);
		$mQuestion = D('Question', 'Model', 'Common');
		$exp_ids = array($expid);
		$list = array();
		$allTags = $tags;
		$sorted = array();

		// 有多少个标签即循环多少次
		for ( $i=0; $i<$tagnum; $i++ ) {
			$tag = $tags[$i];
			$where = array(
				'i_replies' => array('gt', 0),
				'status' => array('in', array(22,23)),
				'tags' => array('like', 
					array(
						"{$tag}",
						"{$tag} %",
						"% {$tag}",
						"% {$tag} %",
						'OR',
					),
				),
			);
			if ( !empty($exp_ids) ) {
				$where['id'] = array('not in', $exp_ids);
			}
			$order = 'i_replies desc';
			$ret = $mQuestion->where($where)->order($order)->limit($num)->select();
			foreach ( $ret as $inx => $item ) {
				$list[$item['id']] = $item;
				array_push($exp_ids, $item['id']);
				$sorted[$item['id']] = $item['i_replies'];
			}
		}
		$reltags = array();
		arsort($sorted);
		// foreach ( $list as $i => $item ) {
		foreach ( $sorted as $id => $i_replies ) {
			$item = $list[$id];
			$tags = explode(' ', trim($item['tags']));
			$reltags = array_merge($reltags, $tags);
			array_push($result, array(
				'id' => $item['id'],
				'title' => $item['title'],
				'i_replies' => $item['i_replies'],
				// 'tags' => $tags,
			));
		}
		$this->data['rel_questions'] = $result;
		$this->data['rel_tags'] = array_unique(array_merge($reltags, $allTags));
		return $result;
	}
	public function _relquestions ( $tagids='', $pagesize=6, $expid=0 ) {
		$result = array();
		if ( is_string($tagids) ) {
			$tagids = explode(',', trim($tagids, ','));
		}
		if ( !is_array($tagids) ) {
			return $result;
		}
		$tagnum = count($tagids);
		$num = ceil($pagesize / $tagnum);
		$mQuestion = D('Question', 'Model', 'Common');
		$exp_ids = array($expid);
		$list = array();
		$allTags = $tagids;
		$sorted = array();

		// 有多少个标签即循环多少次
		for ( $i=0; $i<$tagnum; $i++ ) {
			$tagid = $tagids[$i];
			$where = array(
				'i_replies' => array('gt', 0),
				'status' => array('in', array(22,23)),
				'tagids' => array('like', "%,{$tagid},%"),
			);
			if ( !empty($exp_ids) ) {
				$where['id'] = array('not in', $exp_ids);
			}
			$order = 'i_replies desc';
			$ret = $mQuestion->where($where)->order($order)->limit($num)->select();
			foreach ( $ret as $inx => $item ) {
				$list[$item['id']] = $item;
				array_push($exp_ids, $item['id']);
				$sorted[$item['id']] = $item['i_replies'];
			}
		}
		$reltags = array();
		arsort($sorted);
		// $field = 'qid, reply, ctime, usernick, anonymous';
		$field = 'qid, reply';
		$sqls = array();
		// 热裤回复数进行排序
		foreach ( $sorted as $id => $i_replies ) {
			$item = $list[$id];
			$tags = explode(',', trim($item['tagids'], ','));
			$reltags = array_merge($reltags, $tags);
			$result[intval($id)] = array(
				'id' => $item['id'],
				'title' => $item['title'],
				'reply' => '',
				'i_replies' => $item['i_replies'],
			);
			$sql = "(SELECT {$field} FROM `answers` WHERE `qid`='{$id}' AND `status` IN (21,22,23) ORDER BY `id` DESC LIMIT 1)";
			array_push($sqls, $sql);
		}
		if ( !empty($sqls) ) {
			$mAnswers = D('Answers', 'Model', 'Common');
			$sql = implode(PHP_EOL.' UNION ALL '.PHP_EOL, $sqls);
			$list = $mAnswers->query($sql);
			foreach ( $list as $i => $item ) {
				$qid = intval($item['qid']);
				$result[$qid]['reply'] = $item['reply'];
			}
		}

		$this->data['rel_questions'] = array_values($result);
		$this->data['rel_tagids'] = array_unique(array_merge($reltags, $allTags));
		return $result;
	}

	/**
	 * 指定一个问题，对问题访问进行统计计数
	 */
	public function _visit_question ( $id, $userid=0, $uuid='' ) {
		$lQuestion = D('Question', 'Logic', 'Common');
		// 添加到待推送集合
		$lQuestion->appendToPushSet($id);

		$result = array('status'=>false);
		if ( $uuid=='' ) {
			$uuid = clean_xss(cookie('newgatheruuid'));
		}
		$counter = 0;
		$mQuestion = D('Question', 'Model', 'Common');
		$info = $mQuestion->find($id);
		if ( $info && in_array(intval($info['status']), array(21,22,23)) ) {
			$mQuestion->where(array('id'=>$id))->setInc('i_hits', 1);
			$counter = intval($info['i_hits']) + 1;
			$result['status'] = true;
		}
		$result['counter'] = $counter;
		// 判断是否用户登录，登录的用户，进行访问统计
		if ( $userid ) {
			$mOplogs = D('Oplogs', 'Model', 'Common');
			$where = array('uid'=>$userid, 'act'=>51, 'relid'=>$id);
			if ( !$mOplogs->where($where)->find() ) {
				$data = array('uid'=>$userid, 'uuid'=>$uuid, 'act'=>51, 'relid'=>$id, 'ctime'=>NOW_TIME);
				$mOplogs->data($data)->add();
			}
		}
		return $result;
	}

	/**
	 * 用于 SEO 信息提取和展现
	 */
	public function _getPageinfo($page, $data=array()) {
		$_device = $this->_device;
		$seo_configs = array(
			'pc' => array(
				'index' => array(
					'seo_title' => '房产问答_家居装修问题解答_专业的房产问答平台-乐居问答',
					'keywords' => '房产问答,房产问题,家居问题,装修问题,乐居问答',
					'description' => '乐居问答是最专业的房产,家居,装修问题的问答分享平台。这里有专业的房产从业人员为您解答新房，二手房，租房家居,装修的问题。乐居问答，专业的房产问答平台。',
					'params' => array(),
				),
				'list' => array(
					'seo_title' => "{$data['cateinfo']['name']}知识问题解答_{$data['cateinfo']['name']}问题汇总-乐居问答",
					'keywords' => "{$data['cateinfo']['name']},{$data['cateinfo']['name']}问题, {$data['cateinfo']['name']}问答, {$data['cateinfo']['name']}知识, {$data['cateinfo']['name']}解答",
					'description' => "乐居问答提供{$data['cateinfo']['name']}的专业解答，汇集各类{$data['cateinfo']['name']}知识问答，这里有专业房产从业人员为您解决{$data['cateinfo']['name']}相关问题。乐居问答，专业的房产问答平台。",
					'params' => array('cateid'=>$data['cateid'], 'page'=>1, 'order'=>''),
				),
				'agg' => array(
					'seo_title' => "{$data['name']}_{$data['name']}问题汇总-乐居问答",
					'keywords' => "{$data['name']},{$data['name']}问题, {$data['name']}问答, {$data['name']}知识, {$data['name']}解答",
					'description' => "乐居问答提供{$data['name']}的专业解答，汇集各类{$data['name']}知识问答，这里有专业房产从业人员为您解决{$data['name']}相关问题。乐居问答，专业的房产问答平台。",
					'params' => array('tagid'=>$data['id'], 'page'=>1, 'order'=>''),
				),
				'search' => array(
				/*
					'seo_title' => "{$data['name']}_{$data['name']}问题汇总-乐居问答",
					'keywords' => "{$data['name']},{$data['name']}问题, {$data['name']}问答, {$data['name']}知识, {$data['name']}解答",
					'description' => "乐居问答提供{$data['name']}的专业解答，汇集各类{$data['name']}知识问答，这里有专业房产从业人员为您解决{$data['name']}相关问题。乐居问答，专业的房产问答平台。",
				*/
					'params' => array('keyword'=>$data['keyword'], 'page'=>1, 'order'=>''),
				),
				'show' => array(
					'seo_title' => "{$data['question']['title']}-乐居问答",
					'keywords' => '',
					'description' => '',
					'params' => array('qid'=>$data['id'], 'page'=>1),
				),
				'ask' => array(
					'params' => array(),
				),
			),
			'touch' => array(
				'index' => array(
					'seo_title' => '房产问答_家居装修问题解答_专业的房产问答平台-乐居问答',
					'keywords' => '房产问答,房产问题,家居问题,装修问题,乐居问答',
					'description' => '乐居问答是最专业的房产,家居,装修问题的问答分享平台。这里有专业的房产从业人员为您解答新房，二手房，租房家居,装修的问题。乐居问答，专业的房产问答平台。',
					'params' => array(),
				),
				'list' => array(
					'seo_title' => "{$data['cateinfo']['name']}知识问题解答_{$data['cateinfo']['name']}问题汇总-乐居问答",
					'keywords' => "{$data['cateinfo']['name']},{$data['cateinfo']['name']}问题, {$data['cateinfo']['name']}问答, {$data['cateinfo']['name']}知识, {$data['cateinfo']['name']}解答",
					'description' => "乐居问答提供{$data['cateinfo']['name']}的专业解答，汇集各类{$data['cateinfo']['name']}知识问答，这里有专业房产从业人员为您解决{$data['cateinfo']['name']}相关问题。乐居问答，专业的房产问答平台。",
					'params' => array('cateid'=>$data['cateid'], 'page'=>1, 'order'=>''),
				),
				'agg' => array(
					'seo_title' => "{$data['name']}_{$data['name']}问题汇总-乐居问答",
					'keywords' => "{$data['name']},{$data['name']}问题, {$data['name']}问答, {$data['name']}知识, {$data['name']}解答",
					'description' => "乐居问答提供{$data['name']}的专业解答，汇集各类{$data['name']}知识问答，这里有专业房产从业人员为您解决{$data['name']}相关问题。乐居问答，专业的房产问答平台。",
					'params' => array('tagid'=>$data['id'], 'page'=>1, 'order'=>''),
				),
				'search' => array(
					'params' => array('keyword'=>$data['keyword'], 'page'=>1, 'order'=>''),
				),
				'show' => array(
					'seo_title' => "{$data['question']['title']}-乐居问答",
					'keywords' => '',
					'description' => '',
					'params' => array('qid'=>$data['id'], 'page'=>1),
				),
			),
		);
		$seo = $seo_configs[$_device][$page];
		if ( $page=='show' ) {
			$desc = array();
			$title = $this->formatAttribute($data['question']['desc']);
			if ( isset($data['best']) ) {
				array_push($desc, $this->formatAttribute($data['best']['reply']));
			}
			if ( $data['answers'] ) {
				foreach ( $data['answers'] as $i => $ans ) {
					if ( $i >=3 ) { break; }
					array_push($desc, $this->formatAttribute($ans['reply']));
				}
			}
			$seo['seo_title'] = str_replace(array("\n","\r\n"), array('',''), $seo['seo_title']);
			$seo['keywords'] = array_chunk(explode(',', str_replace(' ', ',', trim($data['question']['tags']))), 5);
			$seo['keywords'] = implode(',', $seo['keywords'][0]);
			$seo['description'] = mystrcut( $title.implode('/', $desc), 150);
		}
		$alt_device = $_device == 'pc' ? 'touch' : 'pc';
		$seo['alt_url'] = url($page, $seo['params'], $alt_device, 'ask');
		// var_dump($seo, $_device, $page, $data);
		return $seo;
	}


	/**
	 * 用于 乐居数据部统计使用
	 */
	public function _getStatsConfig($page, $data=array()) {
		$_device = $this->_device;
		$stats_configs = array(
			'pc' => array(
				'index' => array(
					'level1_page' => 'pc_qa',
					'level2_page' => 'index',
					'level3_page' => '',
					'custom_id' => '',
					'news_source' => '',
				),
				'list' => array(
					'level1_page' => 'pc_qa',
					'level2_page' => 'list',
					'level3_page' => 'lanmu',
					'custom_id' => '',
					'news_source' => '',
				),
				'agg' => array(
					'level1_page' => 'pc_qa',
					'level2_page' => 'list',
					'level3_page' => 'tag',
					'custom_id' => '',
					'news_source' => $data['name'],
				),
				'search' => array(
					'level1_page' => 'pc_qa',
					'level2_page' => 'list',
					'level3_page' => 'search',
					'custom_id' => '',
					'news_source' => $data,
				),
				'show' => array(
					'level1_page' => 'pc_qa',
					'level2_page' => 'info',
					'level3_page' => '',
					'custom_id' => $data['id'],
					'news_source' => '',
				),
				'ask' => array(
					'level1_page' => 'pc_qa',
					'level2_page' => 'tiwen',
					'level3_page' => '',
					'custom_id' => '',
					'news_source' => '',
				),
				'profile' => array(
					'level1_page' => 'pc_qa',
					'level2_page' => 'my',
					'level3_page' => '',
					'custom_id' => '',
					'news_source' => $data,
				),
			),
			'touch' => array(
				'index' => array(
					'level1_page' => 'ask',
					'level2_page' => 'ask_index',
					'level3_page' => '',
					'custom_id' => '',
					'news_source' => '',
				),
				'list' => array(
					'level1_page' => 'ask',
					'level2_page' => 'ask_list',
					'level3_page' => '',
					'custom_id' => $data['cateid'],
					'news_source' => $data['cateinfo']['name'],
				),
				'agg' => array(
					'level1_page' => '',
					'level2_page' => '',
					'level3_page' => '',
					'custom_id' => '',
					'news_source' => '', //$data['name'],
				),
				'search' => array(
					'level1_page' => '',
					'level2_page' => '',
					'level3_page' => '',
					'custom_id' => '',
					'news_source' => '', // $data,
				),
				'show' => array(
					'level1_page' => 'ask',
					'level2_page' => 'info',
					'level3_page' => '',
					'custom_id' => $data['id'],
					'news_source' => $data['question']['catename'],
				),
				'ask' => array(
					'level1_page' => '',
					'level2_page' => '',
					'level3_page' => '',
					'custom_id' => '',
					'news_source' => '',
				),
				'profile' => array(
					'level1_page' => '',
					'level2_page' => '',
					'level3_page' => '',
					'custom_id' => '',
					'news_source' => '', //$data,
				),
			),
		);
		$stats = $stats_configs[$_device][$page];
		return $stats;
	}

	protected function formatAttribute($info) {
		$info = clear_all(filterInput(clean_xss($info)));
		$info = preg_replace('/\s+/i', '', $info);
		return $info;
	}

	public function setDevice( $device ) {
		$device = strtolower(trim($device));
		if ( $device=='pc' ) {
			$this->_device = 'pc';
		} else {
			$this->_device = 'touch';
		}
		return $this;
	}

	public function setFlush( $flush ) {
		$flush = !!$flush;
		$this->_flush = $flush;
		return $this;
	}
}
