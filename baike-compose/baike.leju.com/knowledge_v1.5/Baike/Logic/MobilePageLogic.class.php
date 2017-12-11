<?php
/**
 * 移动端页面逻辑
 * @author Robert <yongliang1@leju.com>
 */

namespace Baike\Logic;

class MobilePageLogic extends PageLogic {

    protected $cate_pagesize = 2;
    protected $search_pagesize = 10;
    protected $agg_pagesize = 10;

    protected $pages = array(
        'index' => array(),
    );

    public function initIndexPage($city,$cateid=0,$city_code='') {
        $result = array();

        // 首页焦点信息列表
        $result['forces'] = $this->getForces($city,5);

        // 获取知识 1级 分类
        $result['cates'] = $this->getTopCategories();

        // 获取最新知识列表
        $result['latest'] = $this->getLatestKB($city);

        // 获取热门词条
        $result['hotwords'] = $this->getHotWords();

        return $result;
    }

    /**
     * @hongwang@leju.com
     * @desc 获取分类列表页面数据
     * @param $id
     * @return mixed
     */
    public function getCatePage($id=0,$page=1,$city_en='bj',$city_cn='北京')
    {
        $list = array();

        $lCate = D('Cate', 'Logic', 'Common');

        // 取当前指定栏目 id 的信息和子集
        $current = $lCate->getCateInfo($id);
        if ( !$current || $current['level']>2 || $current['level']<1 ) {
            exit('分类ID错误');
        }


        if ( $current['level']==1 ) {
            if ( count($current['child'])>0 ) {
                $ids = array_keys($current['child']);
                $active = intval($ids[0]);
            } else {
                $active = false;
            }
            $level1 = &$current;
            $level2 = $active ? $lCate->getCateInfo($active) : array();
        }
        if ( $current['level']==2 ) {
            $level1 = $lCate->getCateInfo($current['pid']);
            $level2 = &$current;
            $active = $id;
        }

        $lSearch = D('Search','Logic','Common');
        $order = array('_docupdatetime', 'desc');
        $fields = array('_id','_title','_version','_origin');
        $prefix = array();

        // 获取当前点亮的第二级分类下所有三级中，各取 cate_pagesize 条 数据
        foreach ($level2['child'] as $k => $item) {
            if ( intval($k)==0 ) {
                continue;
            }
            $path = $item['path'];
            $opts = array(array('false', '_deleted'),array("{$city_cn},全国",'_scope'),array("{$path}", '_multi.catepath'));
            $result = $lSearch->select(1, $this->cate_pagesize,'',$opts,$prefix, $order, $fields);
            if ($result['pager']['total'] > 0)
            {
                foreach ($result['list'] as $kk => $ii)
                {
                    $list[$k]['name'] = $item['name'];
                    $list[$k]['cateid'] = $k;
                    $list[$k]['list'][$kk]['title'] = $ii['_title'];
                    $list[$k]['list'][$kk]['id'] = $ii['_origin']['id'];
                    $list[$k]['list'][$kk]['cover'] = $ii['_origin']['cover'];
                    $list[$k]['list'][$kk]['tags'] = explode(' ',$ii['_origin']['tags']);
                    $list[$k]['ctime'] = date('Y-m-d H:i:s',$ii['_origin']['ptime']);
                }
            }
        }
        $binds['cate'] = $level1['name'];
        $binds['cid'] = $active;
        $binds['catelist'] = $level1;
        $binds['list'] = $list;
        return $binds;
    }

    public function getAggPage($tag,$page,$city_cn,$cateid='',$form='')
    {
        $lSearch = D('Search','Logic','Common');
        $opts = array(array("$tag","_tags"),array('false', '_deleted'),array("{$city_cn},全国",'_scope'));
        $order = array('_doccreatetime', 'desc');
        $fields = array('_id','_title','_version','_origin');

        $result = $lSearch->select($page, $this->agg_pagesize, '', $opts, $prefix=array(), $order, $fields);
        $list = array();

        if ($result['pager']['total'] > 0)
        {
            foreach ($result['list'] as $key => $item)
            {
                $list[$key]['id'] = $item['_origin']['id'];
                $list[$key]['title'] = $item['_origin']['title'];
                $list[$key]['content'] = clear_all($item['_origin']['content']);
                $list[$key]['cover'] = $item['_origin']['cover'];
                $list[$key]['url'] = url('show', array($item['_origin']['id']));
                $list[$key]['tags'] = explode(' ',$item['_origin']['tags']);
                $list[$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ptime']);
            }
        }

        return array('list'=>$list);
    }

    public function getSearchPage($page=1,$keyword,$city_cn='',$city_en='',$id=0)
    {
        $lSearch = D('Search','Logic','Common');
        $opts = array(array('false', '_deleted'),array("{$city_cn},全国",'_scope'));
        $order = array('_docupdatetime', 'desc');
        //$prefix = array(array("0-{$id}-", '_multi.catepath'));
        $fields = array('_id','_title','_origin');
        $result = $lSearch->select($page,$this->search_pagesize,$keyword,$opts,$prefix, $order, $fields);

        if ($result['pager']['total'] > 0)
        {
            $dbg = 0;
        }
        else
        {
            $result = $lSearch->select($page,$this->search_pagesize,$keyword,$opts,$prefix, $order, $fields,1);
            $dbg = 1;
        }

        if ($result['pager']['total'])
        {
            foreach ($result['list'] as $key => $item)
            {
                $list[$key]['id'] = $item['_origin']['id'];
                $list[$key]['title'] = $item['_origin']['title'];
                $list[$key]['cover'] = $item['_origin']['cover'];
                $list[$key]['url'] = url('show', array($item['_origin']['id']));
                $list[$key]['tags'] = explode(' ',$item['_origin']['tags']);
                $list[$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ptime']);
                $content = $this->__cutContentByWord($item['_origin']['content'],$keyword);
                $list[$key]['content'] = str_replace($keyword, '<em class="red">'.$keyword.'</em>', $content);
            }
        }
        return array('dbg'=>$dbg,'list'=>$list);
    }

    private function __cutContentByWord($content,$word,$len=50)
    {
        $content = clear_all($content);
        $pos = mb_strripos($content,$word);
        $start = $pos-$len <= 0 ? 0 : $pos-$len;
        return mb_substr($content, $start,100);
    }

}