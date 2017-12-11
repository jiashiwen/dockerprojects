<?php
/**
 * 分类树处理
 * @author Robert <yongliang1@leju.com>
 */

namespace Baike\Logic;

class CategoriesLogic {

	protected $_sp = '_';
	protected $cates = null;
	protected $_flat = array();
	protected $_tree = array();

	public function init() {
		$this->cates = C('CATEGORIES');

		$this->_flat = $this->toFlat();
		$this->_tree = $this->toTree();
	}

	protected function toFlat() {
		$result = array();
		foreach ( $this->cates as $i => $cate ) {
			$id = & $cate['id'];
			$result[$id] = $cate;
		}
		return $result;
	}
	protected function toTree() {
		$result = array();
		foreach ( $this->cates as $i => $cate ) {
			if ( $cate['parent']=='' ) {
				$id = & $cate['id'];
				$result[$id] = $cate;
				$result[$id]['child'] = $this->_toTree($cate['name']);
			}
		}

		return $result;
	}

	protected function _toTree( $parent = '' ) {
		$result = array();
		foreach ( $this->cates as $i => $cate ) {
			if ( $cate['parent']==$parent ) {
				unset($this->cates[$i]);
				$id = & $cate['id'];
				$result[$id] = $cate;
				$result[$id]['child'] = $this->_toTree($cate['name']);
			}
		}

		return $result;
	}


	public function __construct() {
		$this->init();
	}

	public function getTree( $parent = '' ) {
		if ( empty($this->_tree) ) {
			$this->_tree = $this->toTree();
		}

		return $this->_tree;
	}

	public function getCrumb($itemid) {
		$path = explode($this->_sp, $itemid);
		$crumb = array();
		$_id = '';
		for ( $i=0; $i < count($path); $i++ ) {
			$_id = trim($_id . $this->_sp . $path[$i], $this->_sp);
			$_name = $this->getName($_id);
			if ( $_name == false ) {
				break;
			}
			$crumb[$_id] = $_name;
			if ( $itemid == $_id ) {
				$crumb[$_id]['actived'] = true;
			}
		}
		return $crumb;
	}

	public function getName( $itemid ) {
		if ( !array_key_exists($itemid, $this->_flat) ) {
			return false;
		}
		return $this->_flat[$itemid];
	}

	public function getTags( $id ) {
		$tags = false;
		if ( array_key_exists($id, $this->_flat) ) {
			$tags = $this->_flat[$id]['tags'];
			if ( is_string($tags) ) {
				$tags = explode(',', $tags);
			}

			foreach ( $tags as $i => &$tag ) {
				$tag = "{$tag}@@_tags";
			}
			$tags = implode('<>', $tags);
		}
		return $tags;
	}

	public function debug() {
		echo PHP_EOL, '<!--', PHP_EOL, PHP_EOL,
			 'Cates', PHP_EOL, print_r($this->cates, true), PHP_EOL, PHP_EOL,
			 'Flat', PHP_EOL, print_r($this->_flat, true), PHP_EOL, PHP_EOL,
			 'Tree', PHP_EOL, print_r($this->_tree, true), PHP_EOL, PHP_EOL,
			 PHP_EOL, '-->', PHP_EOL, PHP_EOL;
	}
}