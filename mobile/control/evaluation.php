<?php
/**		
 * evaluationControl		
 * 会员中心——买家评价	
 * @author zly		
 */		
defined('NlWxShop') or exit('Access Invalid!');
class evaluationControl extends mobileMemberControl{
    public function __construct(){
        parent::__construct() ;
        //加载语言包
        Language::read('member_layout,member_evaluate,common');
    }
    /**
     * 订单添加评价
     */
    public function addOp(){
        // 获取订单id
        $order_id = intval($_POST['order_id']);
        // 判断订单id是否有值
        if (!$order_id){
            output_error($err_msg = '参数错误');
        }
        $model_order = Model('order');
        $model_store = Model('store');
        // 获取订单信息
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id));
        // 判断订单身份
       if($order_info['buyer_id'] != $this->member_info['member_id']) {
           output_error($err_msg = '参数错误');
        }
        // 订单为'已收货'状态，并且未评论
        $order_info['evaluate_able'] = $model_order->getOrderOperateState('evaluation',$order_info);
        if (empty($order_info) || !$order_info['evaluate_able']){
            output_error($err_msg = '参数错误');
        }
        // 查询店铺信息
        $store_info = $model_store->getStoreInfoByID($order_info['store_id']);
        if(empty($store_info)){
            output_error($err_msg = '参数错误');
        }
        // 获取订单商品
        $order_goods = $model_order->getOrderGoodsList(array('order_id'=>$order_id));
        if(empty($order_goods)){
            output_error($err_msg = '参数错误');
        }
        for ($i = 0, $j = count($order_goods); $i < $j; $i++) {
            // 获取商品图片
            $order_goods[$i]['goods_image_url'] = cthumb($order_goods[$i]['goods_image'], 60, $store_info['store_id']);
        }
        // 订单信息 订单商品信息 店铺信息
        $data_all = array(  'order_info' => $order_info,
                            'order_goods'=> $order_goods,
                            'store_info' => $store_info
                );
        // 输出信息
        output_data($data_all);
    } 
    /**
     * 录入评价信息
     */
    function evaluation_insertOp(){ 
        // 获取订单id
        $order_id = intval($_POST['order_id']);
        $model = Model();
        try {
            // 事务开始
            $model->beginTransaction();
            // 加载模型
            $model_order = Model('order');
            $model_store = Model('store');
            $model_evaluate_goods = Model('evaluate_goods');
            $model_evaluate_store = Model('evaluate_store');
            // 获取订单详细信息
            $order_info = $model_order->getOrderInfo(array('order_id' => $order_id));
            // 判断订单身份
            if ($order_info['buyer_id'] != $this->member_info['member_id']) {
                output_error($err_msg = '参数错误');
            }
            // 订单为'已收货'状态，并且未评论
            $order_info['evaluate_able'] = $model_order->getOrderOperateState('evaluation', $order_info);
            if (empty($order_info) || !$order_info['evaluate_able']) {
                output_error($err_msg = '参数错误');
            }
            // 查询店铺信息
            $store_info = $model_store->getStoreInfoByID($order_info['store_id']);
            if (empty($store_info)) {
                output_error($err_msg = '参数错误');
            }
            // 获取订单商品
            $order_goods = $model_order->getOrderGoodsList(array('order_id' => $order_id));
            if (empty($order_goods)) {
                output_error($err_msg = '参数错误');
            }
            $evaluate_goods_array = array();
            $goodsid_array = array();
            foreach ($order_goods as $value) {

                // 如果未评分，默认为5分
                $evaluate_score = $_POST['geval_scores'][$value["goods_id"]];
                if ($evaluate_score <= 0 || $evaluate_score > 5) {
                    $evaluate_score = 5;
                }
                // 默认评语
                if (!empty($_POST['evaluate_score'][$value["goods_id"]])) {
                    $evaluate_comment = $_POST['evaluate_score'][$value["goods_id"]];
                } else {
                    $evaluate_comment = '不错哦';
                }
                // 整理评价信息数据
                $evaluate_goods_info = array();
                $evaluate_goods_info['geval_orderid'] = $order_id;
                $evaluate_goods_info['geval_orderno'] = $order_info['order_sn'];
                $evaluate_goods_info['geval_ordergoodsid'] = $value['rec_id'];
                $evaluate_goods_info['geval_goodsid'] = $value['goods_id'];
                $evaluate_goods_info['geval_goodsname'] = $value['goods_name'];
                $evaluate_goods_info['geval_goodsprice'] = $value['goods_price'];
                $evaluate_goods_info['geval_goodsimage'] = $value['goods_image'];
                $evaluate_goods_info['geval_scores'] = $evaluate_score;
                $evaluate_goods_info['geval_content'] = $evaluate_comment;
                $evaluate_goods_info['geval_isanonymous'] = $_POST['anony'];
                $evaluate_goods_info['geval_addtime'] = TIMESTAMP;
                $evaluate_goods_info['geval_storeid'] = $store_info['store_id'];
                $evaluate_goods_info['geval_storename'] = $store_info['store_name'];
                $evaluate_goods_info['geval_frommemberid'] = $this->member_info['member_id'];
                $evaluate_goods_info['geval_frommembername'] = $this->member_info['member_name'];
                $evaluate_goods_array[] = $evaluate_goods_info;
                $goodsid_array[] = $value['goods_id'];
            }
            // 插入评论信息
            $model_evaluate_goods->addEvaluateGoodsArray($evaluate_goods_array, $goodsid_array);

            // 默认宝贝与描述相符度等级
            $store_desccredit = intval($_POST['store_desccredit']);
            if ($store_desccredit <= 0 || $store_desccredit > 5) {
                $store_desccredit = 5;
            }
            // 默认家的服务态度等级
            $store_servicecredit = intval($_POST['store_servicecredit']);
            if ($store_servicecredit <= 0 || $store_servicecredit > 5) {
                $store_servicecredit = 5;
            }
            // 卖家的发货速度
            $store_deliverycredit = intval($_POST['store_deliverycredit']);
            if ($store_deliverycredit <= 0 || $store_deliverycredit > 5) {
                $store_deliverycredit = 5;
            }
            // 整理店铺信息 
            if (!$store_info['is_own_shop']) {
                $evaluate_store_info = array();
                $evaluate_store_info['seval_orderid'] = $order_id;
                $evaluate_store_info['seval_orderno'] = $order_info['order_sn'];
                $evaluate_store_info['seval_addtime'] = time();
                $evaluate_store_info['seval_storeid'] = $store_info['store_id'];
                $evaluate_store_info['seval_storename'] = $store_info['store_name'];
                $evaluate_store_info['seval_memberid'] = $this->member_info['member_id'];
                $evaluate_store_info['seval_membername'] = $this->member_info['member_name'];
                $evaluate_store_info['seval_desccredit'] = $store_desccredit;
                $evaluate_store_info['seval_servicecredit'] = $store_servicecredit;
                $evaluate_store_info['seval_deliverycredit'] = $store_deliverycredit;
            }
            // 添加店铺评价
            $model_evaluate_store->addEvaluateStore($evaluate_store_info);

            // 更新订单信息并记录订单日志
            $state = $model_order->editOrder(array('evaluation_state' => 1), array('order_id' => $order_id));
            $model_order->editOrderCommon(array('evaluation_time' => TIMESTAMP), array('order_id' => $order_id));
            if ($state) {
                $data = array();
                $data['order_id'] = $order_id;
                $data['log_role'] = 'buyer';
                $data['log_msg'] = L('order_log_eval');
                $model_order->addOrderLog($data);
            }

            // 添加会员积分
            if (C('points_isuse') == 1) {
                $points_model = Model('points');
                $points_model->savePointsLog('comments', array('pl_memberid' => $this->member_info['member_id'], 'pl_membername' => $this->member_info['member_id']));
            }
            // 添加会员经验值
            Model('exppoints')->SaveExppointsLog('comments', array('exp_memberid' => $this->member_info['member_id'], 'exp_membername' => $this->member_info['member_id']));
            // 事物结束
            $model->commit();
            output_data(TRUE);
        } catch (Exception $e) {
            // 数据库操作异常，回退数据库操作
            $model->rollback();
            // 返回错误信息
            output_error('评价失败！');
        }
    }
}
