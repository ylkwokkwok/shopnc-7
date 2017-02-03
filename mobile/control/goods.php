<?php
/**
 * 商品
 *
 * by abc.com 多用户商城
 *
 *
 */
//by abc.com

defined('NlWxShop') or exit('Access Invalid!');
class goodsControl extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 商品列表
     */
    public function goods_listOp() {
        $model_goods = Model('goods');
        $model_search = Model('search');
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
        //查询条件
        $condition = array();
        if(!empty($_GET['gc_id']) && intval($_GET['gc_id']) > 0) {
            $condition['gc_id'] = $_GET['gc_id'];
            /* wqw@newland 修改开始   　**/
            /* 时间：2015/06/02       **/
            /* 功能ID：SHOP016        **/
        } elseif (!empty($_GET['keyword']) && $_GET['keyword'] != 'null' && $_GET['keyword'] != 'purchase_limit') {
            /* wqw@newland 修改结束   **/
            $condition['goods_name|goods_jingle'] = array('like', '%' . $_GET['keyword'] . '%');
        } elseif (!empty($_GET['keyword']) && $_GET['keyword'] != 'null' && $_GET['keyword'] == 'purchase_limit') {
            $condition['purchase_limit'] = array('gt', 0);
        }

        /* wqw@newland 修改开始  　**/
        /* 时间：2015/06/02        **/
        /* 功能ID：ADMIN006        **/
        //所需字段
        $fieldstr = "store_id as store_id_vip,goods_id,goods_commonid,store_id,goods_name,goods_price,goods_marketprice,goods_image,goods_salenum,evaluation_good_star,evaluation_count";
        /* wqw@newland 修改结束   **/
        
        // 添加3个状态字段
        $fieldstr .= ',is_virtual,is_presell,is_fcode,have_gift';

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

        //优先从全文索引库里查找
        list($indexer_ids,$indexer_count) = $model_search->indexerSearch($_GET,$this->page);
        if (is_array($indexer_ids)) {
            
            /* lyq@newland 修改开始 **/
            /* 时间：2015/06/03     **/
            /* 功能ID：SHOP018      **/
            // 修改：添加group by条件 goods_commonid
            //商品主键搜索
            $goods_list = $model_goods->getGoodsOnlineList(array('goods_id'=>array('in',$indexer_ids)), $fieldstr, 0, $order, $this->page, 'goods_commonid', false);
            /* lyq@newland 修改结束 **/
            
            //如果有商品下架等情况，则删除下架商品的搜索索引信息
            if (count($goods_list) != count($indexer_ids)) {
                $model_search->delInvalidGoods($goods_list, $indexer_ids);
            }
            pagecmd('setEachNum',$this->page);
            pagecmd('setTotalNum',$indexer_count);
        } else {
            /* lyq@newland 修改开始 **/
            /* 时间：2015/06/03     **/
            /* 功能ID：SHOP018      **/
            // 修改：更改获取商品列表所调用的方法  getGoodsListByColorDistinct ->> getGoodsListByCommnidDistinct
            $goods_list = $model_goods->getGoodsListByCommnidDistinct($condition, $fieldstr, $order, $this->page);
            /* lyq@newland 修改结束 **/
        }
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/06/03     **/
        /* 功能ID：SHOP018      **/
        // 添加：重新获取商品名称
        $this->_format_goods_name($goods_list);
        /* lyq@newland 添加结束 **/

        $page_count = $model_goods->gettotalpage();

        //处理商品列表(抢购、限时折扣、商品图片)
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
        /* lyq@newland 修改开始 **/
        /* 时间：2015/08/28     **/
        // 默认排序条件，添加时间
        $result = 'goods_addtime desc';
        /* lyq@newland 修改结束 **/
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
     * 处理商品列表(抢购、限时折扣、商品图片)
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
        /* lyq@newland 添加开始 **/
        /* 时间：2015/11/02     **/
        // 获取限购信息
        $purchase_limit_list = Model('goods')->getPurchaseLimitListByGoodsCommonIDString(implode(',', $commonid_array));
        /* lyq@newland 添加结束 **/
        foreach ($goods_list as $key => $value) {
            //抢购
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

            /* lyq@newland 添加开始 **/
            /* 时间：2015/11/02     **/
            // 限购
            $goods_list[$key]['purchase_limit'] = intval($purchase_limit_list[$value['goods_commonid']]['purchase_limit']);
            /* lyq@newland 添加结束 **/

            //商品图片url
            /* wqw@newland 修改开始    **/
            /* 时间 2015/06/30        **/
            /* 功能ID                 **/
            $goods_common_image = Model()->table('goods_common')->field('goods_image')->where(array('goods_commonid' => $value['goods_commonid']))->find();
            $goods_list[$key]['goods_image_url'] = cthumb($goods_common_image['goods_image'], 360, $value['store_id']);
            /* wqw@newland 修改结束   **/
            unset($goods_list[$key]['store_id']);
            unset($goods_list[$key]['goods_commonid']);
            unset($goods_list[$key]['nc_distinct']);
        }

        return $goods_list;
    }

    /**
     * 商品详细页
     */
    public function goods_detailOp() {
        $goods_id = intval($_GET ['goods_id']);
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
        // 商品详细信息
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);
        if (empty($goods_detail)) {
            output_error('商品不存在');
        }

        /* lyq@newland 删除开始 **/
        /* 时间：2015/06/03     **/
        // 删除获取商品推荐相关的代码
        /* lyq@newland 删除结束 **/
        
        $model_store = Model('store');
        $store_info = $model_store->getStoreInfoByID($goods_detail['goods_info']['store_id']);
        $goods_detail['store_info']['store_id'] = $store_info['store_id'];
        $goods_detail['store_info']['store_name'] = $store_info['store_name'];
        $goods_detail['store_info']['member_id'] = $store_info['member_id'];
		//显示QQ及旺旺 多用户商城
		$goods_detail['store_info']['store_qq'] = $store_info['store_qq'];
		$goods_detail['store_info']['store_ww'] = $store_info['store_ww'];
		$goods_detail['store_info']['store_phone'] = $store_info['store_phone'];
        $goods_detail['store_info']['member_name'] = $store_info['member_name'];

        //商品详细信息处理
        $goods_detail = $this->_goods_detail_extend($goods_detail);
        /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $goods_detail['stroe_vip_list'] = $stroe_vip_list;
        /* wqw@newland 添加结束   **/
        output_data($goods_detail);
    }
    
    /* zly@newland 调取商品评价信息开始 **/            
    /* 时间：2015/05/12                **/		
    /* 功能ID：SHOP001                 **/
    /**
     * 调取商品评价信息
     */
    public function commentsOp() {
        $id   = $_POST ['goods_id'];
        /* lyq@newland 修改开始 **/
        /* 时间：2015/06/12     **/
        // 默认好评
        $type = in_array($_POST ['type'], array('1','2','3')) ? $_POST ['type'] : '1';
        /* lyq@newland 修改结束 **/
        $model_goods = Model('goods');
        // 商品编号
        $goods_id = intval($id);
        // 调用获取商品评论$condition
        $evaluate['goods_evaluate'] = $this->_get_comments($goods_id, $type,$this->page);
        // 获取分页数
        $page_count = $model_goods->gettotalpage();
        
        /* lyq@newland 添加开始  **/
        /* 时间：2015/06/02      **/		
        /* 功能ID：SHOP017       **/	

        // 商品评价数目信息
        $evaluate['goods_evaluate_info'] = Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsID($goods_id);
        
        /* lyq@newland 添加结束  **/
        
        /* lyq@newland 修改开始 **/
        /* 时间：2015/06/12     **/
        // 向页面发送评价类型 1：好评，2：中评，3：差评
        $evaluate['type'] = $type;
        /* lyq@newland 修改结束 **/
        output_data($evaluate,mobile_page($page_count));
    }
    
    /**
     * 获取商品各个等级评论信息
     * @param type $goods_id 商品编号
     * @param type $type 等级评论好坏
     * @param type $page 查询评价条数
     * @return 评论信息
     */
    private function _get_comments($goods_id, $type, $page) {
        // 初始化检索条件
        $condition = array();
        // 检索条件，商品编号
        $condition['geval_goodsid'] = $goods_id;
        // 判断评价类型：好 中 差
        switch ($type) {
            case '1':
                // 查询条件，评论类型为 好
                $condition['geval_scores'] = array('in', '5,4');
                break;
            case '2':
                // 查询条件，评论类型为 中
                $condition['geval_scores'] = array('in', '3,2');
                break;
            case '3':
                // 查询条件，评论类型为 差
                $condition['geval_scores'] = array('in', '1');
                break;
        }

        // 查询指定评论类型的评论信息列表
        $model_evaluate_goods = Model("evaluate_goods");
        // 返回指定评论类型的评论信息列表
        return $model_evaluate_goods->getEvaluateGoodsList($condition, $page);
    }
    /* zly@newland 调取商品评价信息结束**/
    
    /**
     * 商品详细信息处理
     */
    private function _goods_detail_extend($goods_detail) {
        //整理商品规格
        unset($goods_detail['spec_list']);
        $goods_detail['spec_list'] = $goods_detail['spec_list_mobile'];
        unset($goods_detail['spec_list_mobile']);

        //整理商品图片
        unset($goods_detail['goods_image']);
        $goods_detail['goods_image'] = implode(',', $goods_detail['goods_image_mobile']);
        unset($goods_detail['goods_image_mobile']);

        //商品链接
        $goods_detail['goods_info']['goods_url'] = urlShop('goods', 'index', array('goods_id' => $goods_detail['goods_info']['goods_id']));

        //整理数据
        unset($goods_detail['goods_info']['goods_commonid']);
        unset($goods_detail['goods_info']['gc_id']);
        unset($goods_detail['goods_info']['gc_name']);
        unset($goods_detail['goods_info']['store_name']);
        unset($goods_detail['goods_info']['brand_id']);
        unset($goods_detail['goods_info']['brand_name']);
        unset($goods_detail['goods_info']['type_id']);
        unset($goods_detail['goods_info']['goods_image']);
        unset($goods_detail['goods_info']['goods_body']);
        unset($goods_detail['goods_info']['goods_state']);
        unset($goods_detail['goods_info']['goods_stateremark']);
        unset($goods_detail['goods_info']['goods_verify']);
        unset($goods_detail['goods_info']['goods_verifyremark']);
        unset($goods_detail['goods_info']['goods_lock']);
        unset($goods_detail['goods_info']['goods_addtime']);
        unset($goods_detail['goods_info']['goods_edittime']);
        unset($goods_detail['goods_info']['goods_selltime']);
        unset($goods_detail['goods_info']['goods_show']);
        unset($goods_detail['goods_info']['goods_commend']);
        unset($goods_detail['goods_info']['explain']);
        unset($goods_detail['goods_info']['cart']);
        unset($goods_detail['goods_info']['buynow_text']);
        unset($goods_detail['groupbuy_info']);
        unset($goods_detail['xianshi_info']);

        return $goods_detail;
    }

    /**
     * 商品详细页
     */
    public function goods_bodyOp() {
        $goods_id = intval($_GET ['goods_id']);

        $model_goods = Model('goods');

        $goods_info = $model_goods->getGoodsInfoByID($goods_id, 'goods_commonid');
        $goods_common_info = $model_goods->getGoodeCommonInfoByID($goods_info['goods_commonid']);

        Tpl::output('goods_common_info', $goods_common_info);
        Tpl::showpage('goods_body');
    }
	/**
     * 手机商品详细页
     */
	public function wap_goods_bodyOp() {
        $goods_id = intval($_GET ['goods_id']);

        $model_goods = Model('goods');

        $goods_info =$model_goods->getGoodsInfoByID($goods_id, 'goods_id');
        $goods_common_info =$model_goods->getMobileBodyByCommonID($goods_info['goods_commonid']);
        Tpl:output('goods_common_info',$goods_common_info);
        Tpl::showpage('goods_body');
    }
}
