<?php
/**
 * 页面核心逻辑基础类
 * @author Robert <yongliang1@leju.com>
 */

namespace Baike\Logic;
// use Think\Model;
class PageLogic /* extends Model */{
    // protected $autoCheckFields = false;
    // protected $name = 'page';

    // public function __construct() {}
    /**
     * 获取焦点列表
     * @param $num int 指定获取焦点信息的数量
     * @return array
     * = 结构 =
     *	[
     *		{
     *			'img' =>  'string',
     *			'title' =>  'string',
     *			'url' =>  'string',
     *		}, {}...
     *	]
     */

    public function getForces( $city,$num=5 )
    {
        $lSearch = D('Search','Logic','Common');
        $opts = array(
            array('false', '_deleted'),
            array("{$city},全国",'_scope'),
            array('!0', '_multi.rcmd_time'),
        );
        $order = array('_multi.rcmd_time', 'desc');
        $fields = array('_id','_title','_version','_origin');
        $total = 0;
        $prefix = array();

        $lSearch->getToken(true);
        $result = $lSearch->select(1, $num, '', $opts, $prefix, $order, $fields);

        if ($result['pager']['total'] > 0)
        {
            foreach ($result['list'] as $key => $item)
            {
                $list[$key]['id'] = $item['_origin']['id'];
                $list[$key]['title'] = !empty($item['_origin']['rcmd_title']) ? $item['_origin']['rcmd_title'] : $item['_origin']['title'];
                $list[$key]['cover'] = !empty($item['_origin']['rcmd_cover']) ? $item['_origin']['rcmd_cover'] : $item['_origin']['cover'] ;
                $list[$key]['url'] = C('FRONT_URL.show'). $item['_origin']['id'];
            }
        }
        return $list;
    }

    /**
     * 获取知识顶级分类
     * @return array
     *	= 结构 =
     *	[
     *		{
     *			'icon': 'string',
     *			'title': 'string',
     *			'url': 'string',
     *		}, {}...
     *	]
     */
    public function getTopCategories ()
    {
        $icon = 'l_0';//css class
        $maxnum = 4; //每行显示最多个数
        $result = array();
        $all = array(
            'title'=>'全部知识',
            'icon'=>'l_05 all',
            'url'=>C('FRONT_URL.map'),
            'href'=>url('map', array(), 'touch'),
        );
        $lCate = D('Cate','Logic','Common');
        $topcate = $lCate->getTopCate();
        if (!$topcate)
        {
            //容错，查库
            $mCategories = D('Categories','Model','Common');
            $list = $mCategories->frontTopList($maxnum);
            $ids = array();

            foreach ($list as $key => $item)
            {
                $ids[] = $item['id'];
                $result[$key]['id'] = $item['id'];
                $result[$key]['title'] = $item['name'];
                $result[$key]['icon'] = $icon . ($key+1);
                $result[$key]['href'] = 'javascript:;';

            }

        }
        else
        {
            $i = 0;
            foreach ($topcate as $key => $value) {
                $ids[] = $key;
                $result[$i]['id'] = $key;
                $result[$i]['title'] = $value;
                $result[$i]['icon'] = $icon.($i+1);
                $result[$i]['href'] = 'javascript:;';
                $i++;
                if ($i==$maxnum)
                    break;
            }
        }
        if (!empty($result))
            $result[] = $all;

        return array('list'=>$result,'ids'=>$ids);
    }

    /**
     * 获取最新知识列表
     * @param $num int 指定获取焦点信息的数量
     * @return array
     * = 结构 = (限制 => 只输出已审核数据)
     *	[
     *		{
     *			'cover': 'string',
     *			'title': 'string',
     *			'url': 'string',
     *			'tags': ['string','...'],
     *			'ctime': 'Y-m-d H:i:s',
     *		}, {}...
     *	]
     */
    public function getLatestKB ($city) {
        $lCate = D('Cate','Logic','Common');
        $topcate = $lCate->getTopCate();
        $ids = array_keys($topcate);
        $lSearch = D('Search','Logic','Common');
        $order = array('_docupdatetime', 'desc');
        $fields = array('_id','_title','_version','_origin');
        //array('_multi.top_time','desc');
        foreach ($topcate as $id=>$name)
        {
            $opts = array(array('false', '_deleted'),array("{$city},全国",'_scope'));
            $topcatepath = "0-{$id}-";
            $topKB = $this->getTopKB($lSearch,$city,$topcatepath);
            $num = 2;
            $tops = array();
            if ($topKB !== false)
            {
                $num -= 1;
                $exid = $topKB['_id'];
                array_push($opts, array("!{$exid}","_id"));

                $tops['id'] = $topKB['_origin']['id'];
                $tops['title'] = ($topKB['_origin']['top_time'] > 0
                    && !empty($topKB['_origin']['top_title'])) ? $topKB['_origin']['top_title'] : $topKB['_origin']['title'];
                $tops['cover'] = ($topKB['_origin']['top_time'] > 0
                    && !empty($topKB['_origin']['top_cover'])) ? $topKB['_origin']['top_cover'] : $topKB['_origin']['cover'];
                $tops['url'] = url('show', array($topKB['_origin']['id']));
                $tops['tags'] = explode(' ',$topKB['_origin']['tags']);
                $tops['ctime'] = date('Y-m-d H:i:s',$topKB['_origin']['ctime']);
            }
            $list = array();
            $prefix = array(array("{$topcatepath}", '_multi.catepath'));
            $result = $lSearch->select(1, $num,'',$opts,$prefix, $order, $fields);

            if ($result['pager']['total'] > 0)
            {
                foreach ($result['list'] as $key => $item)
                {
                    $list['list'][$key]['id'] = $item['_origin']['id'];
                    $list['list'][$key]['title'] = ($item['_origin']['top_time'] > 0
                        && !empty($item['_origin']['top_title'])) ? $item['_origin']['top_title'] : $item['_origin']['title'];
                    $list['list'][$key]['cover'] = ($item['_origin']['top_time'] > 0
                        && !empty($item['_origin']['top_cover'])) ? $item['_origin']['top_cover'] : $item['_origin']['cover'];
                    $list['list'][$key]['tags'] = explode(' ',$item['_origin']['tags']);
                    $list['list'][$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ctime']);
                }
            }
            $list['cateid'] = $id;
            $list['name'] = $name;
            $list['topKB'] = $tops;
            $return[] = $list;
        }
        return $return;


    }

    /**
     * 获取热门词条
     * @param $num int 指定获取焦点信息的数量
     * @return array
     * = 结构 = (限制 => 只输出已审核数据)
     *	[
     *		{
     *			'word': 'string',
     *			'rank': 'up|down',
     *		}, {}...
     *	]
     */
    public function getHotWords () {

        //热门词条api
        $r = S(C('REDIS'));
        $rkey = 'wiki:tag:hot';
        $hot = $r->get($rkey);
        if($hot)
        {
            return $hot;
        }
        else
        {
            $hot_api = curl_get(C('DATA_TRANSFER_API_URL').'api/item?page=1&pagesize=6&sort=hits');
            $hot = json_decode($hot_api['result'], true);
            if(!empty($hot['result']))
            {
                $r->set($rkey, $hot['result'], 300);
                return $hot['result'];
            }
        }
        return false;

    }

    private function getTopKB($lSearch,$city,$path)
    {
        $opts = array(
            array('false', '_deleted'),
            array("{$city},全国",'_scope'),
            array('!0', '_multi.top_time'),
        );
        $prefix = array(array("{$path}", '_multi.catepath'));
        $order = array('_multi.top_time', 'desc');
        $fields = array('_id','_title','_version','_origin');
        $result = $lSearch->select(1,1,'',$opts,$prefix, $order, $fields);
        if ($result['pager']['total'] >= 1)
        {
            return $result['list']['0'];
        }
        return false;
    }
}