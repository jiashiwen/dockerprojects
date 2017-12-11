<?php
/**
 * 知识库 问题分类查看页面
 */
namespace Baike\Controller;

class AggController extends BaseController {

    /**
     */
    public function index() {
        $tag = I('tag', 0, 'trim,addslashes');
        $tag = clear_all($tag);
        $page = I('page', 1, 'intval');
        $id = I('id', 0, 'intval');//cateid

        if (empty($tag))
        {
            $this->error('标签不存在');
            exit;
        }
        $form = array(
            'page'=>$page,
            'city'=>$this->_city['code'],
            'tag'=>$tag,
            'id'=>$id,
        );

        $Device = ucfirst($this->_device);
        $pageLogic = D( $Device.'Page', 'Logic' );
        $result = $pageLogic->getAggPage($tag,$page,$this->_city['cn'],$id,$form);

        $binds['parent'] = $tag;
        $this->assign('cateid',$id);
        $this->assign('cate_all',$result['cate_all']);
        $this->assign('binds',$binds);
        $this->assign('list', $result['list']);
        $this->assign('result', $result);
        $this->assign('pager', $result['pager']);
        $this->assign('sortId', $tag);


		//SEO
		$seoLogic = D('Seo','Logic','Common');
		$seo = $seoLogic->tag($tag);
        $this->setPageInfo($seo);

        //统计代码
        $count_cate = C('FRONT_BAIKE_COUNT_CATE');
        $level1_page = ($this->_device == 'pc') ? 'pc_fcbk' : 'baike';
        $level2_page = ($this->_device == 'pc') ? $count_cate[$id] : 'kd_cates_list';
        $level3_page = ($this->_device == 'pc') ? 'agg' : '';
        //
        $this->assign('level1_page', $level1_page);
        $this->assign('level2_page', $level2_page);
        $this->assign('level3_page', $level3_page);

        $this->assign('custom_id', $tag);
        $this->assign('jsflag','kb_agg');

        $this->display('Agg/index');
    }

    // 移动版异步加载更多
    public function loading() {
        $tag = I('id', 0, 'trim,addslashes');
        $page = I('page',2, 'intval');
        $pagesize = I('pagesize',10, 'intval');
        $list = array();

        $return = array(
            'status'=>false,
            'list'=>null,
            'msg'=>'',
            'pagesize'=>$pagesize,
            'page'=>$page,
            'total'=>0,
            'tag'=>$tag,
        );
        if (!empty($tag))
        {
            $lSearch = D('Search','Logic','Common');
            $opts = array(array("$tag","_tags"),array('false', '_deleted'),array("{$this->_city['cn']},全国",'_scope'));
            $order = array('_doccreatetime', 'desc');
            $fields = array('_id','_title','_version','_origin');

            $result = $lSearch->select($page, $pagesize,'',$opts,$prefix, $order, $fields);

            if ($result['pager']['total'] > 0)
            {
                $total = $result['pager']['total'];
                foreach ($result['list'] as $key => $item)
                {
                    $list[$key]['id'] = $item['_origin']['id'];
                    $list[$key]['title'] = $item['_origin']['title'];
                    $list[$key]['cover'] = $item['_origin']['cover'];
                    $list[$key]['url'] = url('show', array($item['_origin']['id']));
                    $list[$key]['tags'] = explode(' ',$item['_origin']['tags']);
                    $list[$key]['ctime'] = date('Y-m-d H:i:s',$item['_origin']['ctime']);
                }
            }
            $return = array(
                'status'=>true,
                'list'=>$list,
                'msg'=>'',
                'pagesize'=>$pagesize,
                'page'=>$page,
                'total'=>$total,
                'tag'=>$tag,
            );
        }
        $this->ajax_return($return);
    }
}