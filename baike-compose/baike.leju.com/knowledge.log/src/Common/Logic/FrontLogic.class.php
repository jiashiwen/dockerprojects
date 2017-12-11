<?php
/**
 * 前台数据逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class FrontLogic {

	protected $r;
	protected $_cache_time = 86400;
	protected $_public_tpl = array();

	public function __construct() {
		$this->r = S(C('REDIS'));
	}

	public function getPCPublicTemplate( $flush=false ) {
		$key_h = 'TPL:PC:COMMON:HEADER';
		$key_f = 'TPL:PC:COMMON:FOOTER';
		$keys = array('h'=>$key_h, 'f'=>$key_f);
		// echo 'key : ', $key_h, ' -- ', $key_f, PHP_EOL;
		if ( $flush == true ) {
			$this->r->del($keys);
		}

		$result = array();
		$list = $this->r->mget($keys);
		// $hurl = 'http://bj.leju.com/include/leju/pc/2016/topnav.shtml';
		// $furl = 'http://bj.leju.com/include/leju/pc/2016/footer.shtml';
		$hurl = 'http://bj.leju.com/include/leju/pc/2017/topnav.shtml';
		$furl = 'http://bj.leju.com/include/leju/pc/2017/footer.shtml';
		if ( !$list[0] ) {
			$ret = curl_get($hurl);
			if ( $ret['status'] ) {
				$list[0] = $ret['result'];
				$this->r->setex($key_h, $this->_cache_time, $ret['result']);
			}
		}
		if ( !$list[1] ) {
			$ret = curl_get($furl);
			if ( $ret['status'] ) {
				$list[1] = $ret['result'];
				$this->r->setex($key_f, $this->_cache_time, $ret['result']);
			}
		}

		$result['header'] = &$list[0];
		$result['footer'] = &$list[1];
		return $result;
	}

	/*
	 * 首页热门词条
	*/
	public function getHot()
	{
		$page=1;
		$pagesize=12;
		//热门词条api
		$hot = $this->r->get("wiki:tag:hot");
		if($hot)
		{
			return $hot;
		}
		else
		{
			$hot_api = curl_get(C('DATA_TRANSFER_API_URL')."api/item?page={$page}&pagesize={$pagesize}&sort=hits");
			$hot = json_decode($hot_api['result'], true);
			if(!empty($hot['result']))
			{
				$this->r->set("wiki:tag:hot", $hot['result'], 300);
				return $hot['result'];
			}
		}
	}

	/**
	 * @author hongwang@leju.com
	 * @desc 获取热门知识20条
	 * @return bool|mixed
	 */
	public function getHotSearchList($city_code,$city_cn)
	{
        $rkey = 'kb:rank:'.$city_code;
		$data = $this->r->get($rkey);
		if (!$data)
		{
		   $data = $this->getknowledgerank($city_code,$rkey);
		}
		if ($data)
        {
            $lSearch = D('Search','Logic','Common');
            $order = array('_docupdatetime', 'desc');
            $fields = array('_id','_title');
            $prefix = array();
            foreach ($data as $k=>$item)
            {
                $opts = array(array('false', '_deleted'),array("{$item['id']}",'_id'),array("{$city_cn},全国",'_scope'));
                $result = $lSearch->select(1, 1,'',$opts, $prefix, $order, $fields);
                if ($result['pager']['total'] <= 0)
                {
                    unset($data[$k]);
                }

            }
        }
		return $data;
	}


	public function getSuggest($keyword='', $city_cn='北京',$city_code='bj')
	{
		// $cities = C('CITIES.ALL');
		// $city_cn = isset($cities[$city]) ? $cities[$city]['cn'] : '';
		if (!$keyword)
		{
			//输出静态数据
			$result = array();
			$result['kb'] = array();
			$result['tag'] = array();
			$kblist = $this->getHotSearchList($city_code,$city_cn);
			if ($kblist)
			{
				foreach ($kblist as $k=>$item)
				{
					$result['kb'][$k]['id'] = $item['id'];
					$result['kb'][$k]['title'] = $item['title'];
					$result['kb'][$k]['url'] = url('show', array('id'=>$item['id']), 'pc', 'baike');
				}
				$result['kb'] = array_values($result['kb']);
			}
			$tags = curl_get(C('DATA_TRANSFER_API_URL')."api/item?page=1&pagesize=20&sort=hits");
			if ($tags['status'] == 1)
			{
				$tags = json_decode($tags['result'],true);
				foreach ($tags['result'] as $k => $item)
				{
					$list[$k]['title'] = $item['entry'];
					$list[$k]['id'] = $item['id'];
					$list[$k]['url'] = url('show', array(base64_encode($item['id'])), 'pc', 'wiki');
				}
				$result['tag'] = $list;
			}
		}
		else
		{
			//knowledge
			$result['kb'] = $this->getKonwledgeSuggest($keyword,$city_cn);
			$result['tag'] = $this->getTagsSuggest($keyword);
		}
		return $result;
	}

	private function getKonwledgeSuggest($keyword,$city)
	{
		$engine = D('Search', 'Logic', 'Common');
		$opts = array(
			array('false', '_deleted'),
			array("{$city},全国",'_scope'),
		);
		$result = array();
		$prefix = array(array($keyword, "_multi.title_prefix"));
		$order = array('_doccreatetime', 'desc');
		$fields = array('_id','_title','_scope','_origin.content');
		$search = $engine->select(1, 20, '', $opts, $prefix, $order, $fields);
		if ($search && $search['pager']['total'] > 0)
		{
			foreach ($search['list'] as $key => $value) {
				$result[$key]['id'] = $value['_id'];
				$result[$key]['title'] = $value['_title'];
				$result[$key]['url'] = url('show', array('id'=>$value['_id']), 'pc', 'baike');
			}
		}
		return $result;
	}

	private function getTagsSuggest($keyword)
	{
		$suggest_url = C('DATA_TRANSFER_API_URL') . "api/item/suggest?k={$keyword}&n=20";
		$result = curl_get($suggest_url);
		$list = array();
		if ($result['status'] == 1)
		{
			$result = json_decode($result['result'],true);
			foreach ($result['result'] as $k => $item)
			{
				$list[$k]['title'] = $item['entry'];
				$list[$k]['id'] = $item['id'];
				$list[$k]['url'] = url('show', array(base64_encode($item['id'])), 'pc', 'wiki');
			}
		}
		return $list;
	}

	public function getknowledgerank($city,$rkey,$limit = 20) {

        $DataStatistics = D('DataStatistics','Model','Common');
        $result = $DataStatistics->where(array('chartid'=>$rkey))->order('ctime desc')->find();
        if ($result)
        {
            $data = json_decode($result['data'],true);
        }
        else
        {
            $data = array();
            $mVisitStats = D('VisitStats', 'Model', 'Common');
            $_days = $this->_beforetime();
            $begin = $_days['7']['begin'];
            $end = $_days['1']['end'];
            $ttl = 24*3600*2;

            $list = $mVisitStats->todayClickByCity($begin,$end,$limit,$city);
            if ($list)
            {
                $mKnowledge = D('Knowledge', 'Model', 'Common');
                foreach ($list as $key => $value) {
                    $info = $mKnowledge->field('id,title')->find($value['relid']);
                    $data[$key]['total'] = $value['total'];
                    $data[$key]['id'] = $info['id'];
                    $data[$key]['title'] = $info['title'];
                }
            }
        }
        if ($data)
        {
            $this->r->setex($rkey,$ttl,json_encode($data));
        }

		return $data;
	}

	protected function _beforetime($days=7)
	{
		$todyTime = getDayTime();
		$difftime = 86400;
		$date = array();
		for ( $i=$days; $i > 0; $i-- )
		{
			$date[$i]['date'] = date('m-d',($todyTime['begin'] - ($difftime * ($i-1))));
			$date[$i]['begin'] = $todyTime['begin'] - ($difftime * ($i-1));
			$date[$i]['end'] = $date[$i]['begin'] + 86399;
		}
		return $date;
	}

	public function getKnowledgeByTags($tags=array(),$city_cn,$id)
	{
		$list = array();
		if (!empty($tags) && is_array($tags))
		{
			$lSearch = D('Search','Logic','Common');
			$order = array('_doccreatetime', 'desc');
			$fields = array('_id','_title','_origin.cover','_origin.ptime');
			$ids = array();
			foreach ($tags as $k=> $tag)
			{
				$opts = array(array("$tag","_tags"),array('false', '_deleted'),array("{$city_cn},全国",'_scope'));
				if ($id > 0)
				{
					$opts = array(array("$tag","_tags"),array("!{$id}","_id"),array('false', '_deleted'),array("{$city_cn},全国",'_scope'));
				}
				$result = $lSearch->select(1, 1, '', $opts, $prefix=array(), $order, $fields);
				if ($result['pager']['total'] > 0 && !in_array($result['list']['0']['_id'],$ids))
				{
				    array_push($ids,$result['list']['0']['_id']);
					$list[$k]['title'] = $result['list']['0']['_title'];
					$list[$k]['id'] = $result['list']['0']['_id'];
					$list[$k]['cover'] = $result['list']['0']['_origin']['cover'];
					$list[$k]['ptime'] = date('Y-m-d H:i',$result['list']['0']['_origin']['ptime']);
					$list[$k]['url'] = url('show', array('id'=>$result['list'][0]['_id']), 'pc', 'baike');
				}
			}
		}

		return $list;
	}

	
}