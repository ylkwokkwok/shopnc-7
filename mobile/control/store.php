<?php
/**
 * 商品
 *
 *
 *
 
 */
defined('NlWxShop') or exit('Access Invalid!');
class storeControl extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 商品列表
     */
    public function goods_listOp() {
        $model_goods = Model('goods');

        //查询条件
        $condition = array();
        if(!empty($_GET['store_id']) && intval($_GET['store_id']) > 0) {
            $condition['store_id'] = $_GET['store_id'];
        } elseif (!empty($_GET['keyword'])) { 
            $condition['goods_name|goods_jingle'] = array('like', '%' . $_GET['keyword'] . '%');
        }
        /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $model_store = Model('store');
        $temp = $model_store->goods_vip_list();
        if (!empty($temp)){
                foreach ($temp as $value){
                        $stroe_vip_list[] = $value['store_id'];
                }
        }else{
            $stroe_vip_list[] = '';
        }
        /* wqw@newland 添加结束   **/

        /* wqw@newland 修改开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $fieldstr = "store_id as store_id_vip,goods_id,goods_commonid,store_id,goods_name,goods_price,goods_marketprice,goods_image,goods_salenum,evaluation_good_star,evaluation_count";
        /* wqw@newland 修改结束   **/
        
        //排序方式
        $order = $this->_goods_list_order($_GET['key'], $_GET['order']);
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/06/04     **/
        /* 返回商品列表页无法保持历史状态的问题 **/
        // 如果不是按商品ID排序
        if ($order != 'goods_id desc') {
            // 拼接排序条件：商品ID 降序
            $order.=",goods_id desc";
        }
        /* lyq@newland 添加结束 **/

        /* lyq@newland 修改开始 **/
        /* 时间：2015/06/03     **/
        /* 功能ID：SHOP018      **/
        // 修改：更改获取商品列表所调用的方法  getGoodsListByColorDistinct ->> getGoodsListByCommnidDistinct
        $goods_list = $model_goods->getGoodsListByCommnidDistinct($condition, $fieldstr, $order, $this->page);
        /* lyq@newland 修改结束 **/
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/06/03     **/
        /* 功能ID：SHOP018      **/
        // 添加：重新获取商品名称
        $this->_format_goods_name($goods_list);
        /* lyq@newland 添加结束 **/
        
        $page_count = $model_goods->gettotalpage();

        //处理商品列表(团购、限时折扣、商品图片)
        $goods_list = $this->_goods_list_extend($goods_list);
        
        /* wqw@newland 修改开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        output_data(array('goods_list' => $goods_list,'stroe_vip_list'=>$stroe_vip_list), mobile_page($page_count));
        /* wqw@newland 修改结束   **/
    }

    /* lyq@newland 添加开始 **/
    /* 时间：2015/06/03     **/
    /* 功能ID：SHOP018      **/
    
    /**
     * 重新获取商品名称
     *   根据goods_commonid从goods_common表中重新获取商品名称
     * @param type $goods_list 商品信息列表
     */
    private function _format_goods_name(&$goods_list) {
        // 判断参数是否正确
        if (!empty($goods_list) && is_array($goods_list)) {
            // 声明where in 条件数组
            $goods_common_ids = array();
            // 循环商品列表
            foreach ($goods_list as $goods) {
                // 向条件数组中添加数据
                $goods_common_ids[] = $goods['goods_commonid'];
            }
            // 根据条件数组（goods_commonid）查询goods_common表，获取商品ID、名称
            $common_infos = Model()->table('goods_common')->field('goods_commonid,goods_name')->where(array('goods_commonid' => array('in', implode(',', $goods_common_ids))))->select();
            // 声明商品名称数组
            $names = array();
            // 循环$common_infos
            foreach ($common_infos as $value) {
                // 以 key=goods_commonid  val=goods_name 的方式整理商品名称
                $names[$value['goods_commonid']] = $value['goods_name'];
            }
            // 循环商品列表
            foreach ($goods_list as &$value) {
                // 更新商品名称
                $value['goods_name'] = $names[$value['goods_commonid']];
            }
        }
    }
    /* lyq@newland 添加结束 **/
    
    /**
     * 商品列表排序方式
     */
    private function _goods_list_order($key, $order) {
        $result = 'goods_id desc';
        if (!empty($key)) {

            $sequence = 'desc';
            if($order == 1) {
                $sequence = 'asc';
            }

            switch ($key) {
                //销量
                case '1' :
                    $result = 'goods_salenum' . ' ' . $sequence;
                    break;
                //浏览量
                case '2' : 
                    $result = 'goods_click' . ' ' . $sequence;
                    break;
                //价格
                case '3' :
                    $result = 'goods_price' . ' ' . $sequence;
                    break;
            }
        }
        return $result;
    }

    /**
     * 处理商品列表(团购、限时折扣、商品图片)
     */
    private function _goods_list_extend($goods_list) {
        //获取商品列表编号数组
        $commonid_array = array();
        $goodsid_array = array();
        foreach($goods_list as $key => $value) {
            $commonid_array[] = $value['goods_commonid'];
            $goodsid_array[] = $value['goods_id'];
        }

        //促销
        $groupbuy_list = Model('groupbuy')->getGroupbuyListByGoodsCommonIDString(implode(',', $commonid_array));
        $xianshi_list = Model('p_xianshi_goods')->getXianshiGoodsListByGoodsString(implode(',', $goodsid_array));
        foreach ($goods_list as $key => $value) {
            //团购
            if (isset($groupbuy_list[$value['goods_commonid']])) {
                $goods_list[$key]['goods_price'] = $groupbuy_list[$value['goods_commonid']]['groupbuy_price'];
                $goods_list[$key]['group_flag'] = true;
            } else {
                $goods_list[$key]['group_flag'] = false;
            }

            //限时折扣
            if (isset($xianshi_list[$value['goods_id']]) && !$goods_list[$key]['group_flag']) {
                $goods_list[$key]['goods_price'] = $xianshi_list[$value['goods_id']]['xianshi_price'];
                $goods_list[$key]['xianshi_flag'] = true;
            } else {
                $goods_list[$key]['xianshi_flag'] = false;
            }

            //商品图片url
            $goods_list[$key]['goods_image_url'] = cthumb($value['goods_image'], 360, $value['store_id']); 

            unset($goods_list[$key]['store_id']);
            unset($goods_list[$key]['goods_commonid']);
            unset($goods_list[$key]['nc_distinct']);
        }

        return $goods_list;
    }

    /**
     * 商品详细页
     */
    public function store_detailOp() {
        $store_id = intval($_GET ['store_id']);
        // 商品详细信息
        $model_store = Model('store');
        $store_info = $model_store->getStoreOnlineInfoByID($store_id);
        if (empty($store_info)) {
            output_error('店铺不存在');
        }
        $store_detail['store_pf'] = $store_info['store_credit'];
        $store_detail['store_info'] = $store_info;
        // //店铺导航
        // $model_store_navigation = Model('store_navigation');
        // $store_navigation_list = $model_store_navigation->getStoreNavigationList(array('sn_store_id' => $store_id));
        // $store_detail['store_navigation_list'] = $store_navigation_list;
        // //幻灯片图片
        // if($this->store_info['store_slide'] != '' && $this->store_info['store_slide'] != ',,,,'){
        //     $store_detail['store_slide'] = explode(',', $this->store_info['store_slide']);
        //     $store_detail['store_slide_url'] = explode(',', $this->store_info['store_slide_url']);
        // }

        //店铺详细信息处理
        // $store_detail = $this->_store_detail_extend($store_info);
        output_data($store_detail);
    }

    /**
     * 店铺详细信息处理
     */
    private function _store_detail_extend($store_detail) {
        //整理数据
        unset($store_detail['store_info']['goods_commonid']);
        unset($store_detail['store_info']['gc_id']);
        unset($store_detail['store_info']['gc_name']);
        // unset($goods_detail['goods_info']['store_id']);
        // unset($goods_detail['goods_info']['store_name']);
        unset($store_detail['store_info']['brand_id']);
        unset($store_detail['store_info']['brand_name']);
        unset($store_detail['store_info']['type_id']);
        unset($store_detail['store_info']['goods_image']);
        unset($store_detail['store_info']['goods_body']);
        unset($store_detail['store_info']['goods_state']);
        unset($store_detail['store_info']['goods_stateremark']);
        unset($store_detail['store_info']['goods_verify']);
        unset($store_detail['store_info']['goods_verifyremark']);
        unset($store_detail['store_info']['goods_lock']);
        unset($store_detail['store_info']['goods_addtime']);
        unset($store_detail['store_info']['goods_edittime']);
        unset($store_detail['store_info']['goods_selltime']);
        unset($store_detail['store_info']['goods_show']);
        unset($store_detail['store_info']['goods_commend']);

        return $store_detail;
    }

    // /**
    //  * 商品详细页
    //  */
    // public function goods_bodyOp() {
    //     $store_id = intval($_GET ['store_id']);

    //     $model_goods = Model('goods');

    //     $goods_info = $model_goods->getGoodsInfo(array('goods_id' => $goods_id));
    //     $goods_common_info = $model_goods->getGoodeCommonInfo(array('goods_commonid' => $goods_info['goods_commonid']));

    //     Tpl::output('goods_common_info', $goods_common_info);
    //     Tpl::showpage('goods_body');
    // }
}
