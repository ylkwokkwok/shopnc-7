<?php

/**
 * 商品分类
 *
 *
 *
 */
defined('NlWxShop') or exit('Access Invalid!');

class goods_classControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        $this->_get_class_list($_GET['gc_id']);
    }

    /**
     * 返回一级分类列表
     */
    private function _get_root_class() {
        $model_goods_class = Model('goods_class');
        $model_mb_category = Model('mb_category');

        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel();

        $class_list = $model_goods_class->getGoodsClassListByParentId(0);
        $mb_categroy = $model_mb_category->getLinkList(array());
        $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
        foreach ($class_list as $key => $value) {
            if (!empty($mb_categroy[$value['gc_id']])) {
                $class_list[$key]['image'] = UPLOAD_SITE_URL . DS . ATTACH_MOBILE . DS . 'category' . DS . $mb_categroy[$value['gc_id']]['gc_thumb'];
            } else {
                $class_list[$key]['image'] = '';
            }

            $class_list[$key]['text'] = '';
            $child_class_string = $goods_class_array[$value['gc_id']]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_list[$key]['text'] .= $goods_class_array[$child_class]['gc_name'] . '/';
            }
            $class_list[$key]['text'] = rtrim($class_list[$key]['text'], '/');
        }

        output_data(array('class_list' => $class_list));
    }

    /**
     * 根据分类编号返回下级分类列表
     */
    private function _get_class_list($gc_id) {
        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel();

        $goods_class = $goods_class_array[$gc_id];

        if (empty($goods_class['child'])) {
            //无下级分类返回0
            output_data(array('class_list' => '0'));
        } else {
            //返回下级分类列表
            $class_list = array();
            $child_class_string = $goods_class_array[$gc_id]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_item = array();
                $class_item['gc_id'] .= $goods_class_array[$child_class]['gc_id'];
                $class_item['gc_name'] .= $goods_class_array[$child_class]['gc_name'];
                $class_list[] = $class_item;
            }
            output_data(array('class_list' => $class_list));
        }
    }

    /* lyq@newland 添加开始   * */
    /* 时间：2015/07/17       * */

    /**
     * 获取一级分类
     */
    public function get_root_classOp() {
        $model_goods_class = Model('goods_class');
        // 获取一级分类
        $root_class = $model_goods_class->getGoodsClassListByParentId(0);
        // 返回数据
        output_data(array('root_class' => $root_class));
    }

    /**
     * 获取二级分类
     */
    public function get_child_classOp() {
        $model_goods_class = Model('goods_class');
        // 获取分类列表
        // 获取父分类数据

        if ($_GET['gc_parent_id'] == 0) {
            $goods_class = $goods_class_array;
        } else {
            $goods_class_array = $model_goods_class->getGoodsClassForCacheModel();
            $goods_class = $goods_class_array[$_GET['gc_parent_id']];
        }
        if (empty($goods_class['child'])) { // 无下级分类
            output_data(array('child_class' => 0));
        } else {    // 有下级分类
            // 初始化子分类列表
            $child_class = array();
            // 获取子分类id串
            $child_class_string = $goods_class['child'];
            // 拆分子分类id串，获取子分类id数组
            $child_class_array = explode(',', $child_class_string);
            // 循环子分类id数组，获取子分类数据
            foreach ($child_class_array as $child_gc_id) {
                $class_item = array();
                $class_item['gc_id'] = strval($goods_class_array[$child_gc_id]['gc_id']);
                $class_item['gc_name'] = strval($goods_class_array[$child_gc_id]['gc_name']);
                // 二级分类图片
                $pic_name = BASE_UPLOAD_PATH . '/' . ATTACH_COMMON . '/category-pic-' . $class_item['gc_id'] . '.jpg';
                if (file_exists($pic_name)) {   // 文件存在
                    $class_item['pic'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/category-pic-' . $class_item['gc_id'] . '.jpg';
                } else {    // 文件不存在
                    // 默认图片
                    $class_item['pic'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/default_goods_image.gif';
                }
                $child_class[] = $class_item;
            }
            // 返回数据
            output_data(array('child_class' => $child_class));
        }
    }

    /* lyq@newland 添加结束   * */


    /* zz@newland 添加开始 * */
    /* 时间：2016/03/3     * */

    //查询一分类中十个商品
    public function get_goodsOp() {
        $page = $this->page;
        $model_goods = Model('goods');
        $num_payment = array();

        if ($_GET['gc_parent_id'] === '0') {
            $num_payment = $model_goods->table('goods')->field('goods_id,goods_name,goods_image,goods_price')->where(array('goods_state' => 1))->page($page)->select();
        } else if ($_GET['gc_parent_id'] == 'purchase_limit') {
            $num_payment = $model_goods->table('goods')->field('goods_id,goods_name,goods_image,goods_price')->where(array('purchase_limit' => 1 ,'goods_state' => 1))->page($page)->select();
        } else {
            $where = $_GET['gc_parent_id'];
            $num_payment = $model_goods->table('goods')->field('goods_id,goods_name,goods_image,goods_price')->where(array('gc_id_1' => $where ,'goods_state' => 1))->page($page)->select();
        }

        foreach ($num_payment as $key => $value) {
            $num_payment[$key]['goods_image'] = cthumb($num_payment[$key]['goods_image']);
        }
        $page_count = $model_goods->gettotalpage();
        output_data(array('num_payment' => $num_payment), mobile_page($page_count));
    }

    /* zz@newland 添加结束 * */
}
