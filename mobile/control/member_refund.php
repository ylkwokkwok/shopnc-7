<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('NlWxShop') or exit('Access Invalid!');

/* lyq@newland 添加开始   **/
/* 时间：2015/05/19       **/
/* 功能ID：SHOP009        **/

/**
 * Description of member_refund
 * 退货退款
 *
 * @author Liyiquan
 */
class member_refundControl extends mobileMemberControl {
    //put your code here
    function __construct() {
        parent::__construct();
    }
    
    /**
     * 退货退款列表
     */
    public function refund_listOp() {
        $model_refund = Model('refund_return');
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
        $condition = array();
        // 查询条件：会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];

        /* lyq@newland 添加开始   **/
        /* 时间：2015/05/28       **/
        /* 功能ID：SHOP014        **/
        if ($_GET['order_sn'] != '') {
            // 有订单号，增加查询条件 订单号
            $condition['order_sn'] = array('like','%'.$_GET['order_sn'].'%');
        }
        if ($_GET['refund_sn'] != '') {
            // 有退货/退款编号，增加查询条件 退货/退款编号
            $condition['refund_sn'] = array('like','%'.$_GET['refund_sn'].'%');
        }
        // 匹配日期格式
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['add_time_from']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['add_time_to']);
        // 日期格式正确 取日期，日期格式不正确 赋空
        $start_unixtime = $if_start_date ? strtotime($_GET['add_time_from']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['add_time_to']): null;
        // 开始/结束日是否为空
        if ($start_unixtime || $end_unixtime) {
            // 不为空，增加查询条件 添加时间
            $condition['add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }
        /* lyq@newland 添加结束   **/

        /* lyq@newland 修改开始   **/
        /* 时间：2015/05/19       **/
        /* 功能ID：SHOP025        **/
        
        // 判断列表类型
        if($_GET['type'] == 'refund') {
            // 获取订单退款列表
            $refund_list = $model_refund->getRefundList($condition,$this->page);
        } else {
            // 获取退货退款列表
            $refund_list = $model_refund->getReturnList($condition,$this->page);
        }
        
        /* lyq@newland 修改结束   **/
        
        foreach ($refund_list as &$value) {
            // 商品图片完整url
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
        }
        
        // 获取退货退款列表中的店铺列表
        $store_list = $model_refund->getRefundStoreList($refund_list);
        
        // 获取退款退货状态
        $state_list = $this->get_refund_state_array();
        
        // 响应数据数组
        $array_data = array(
            'refund_list' => $refund_list,
            'store_list'  => $store_list,
            'state_list'  => $state_list
        );
        // 获取总页数
        $page_count = $model_refund->gettotalpage();
        /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $array_data['stroe_vip_list'] = $stroe_vip_list;
        /* wqw@newland 添加结束   **/
        // 输出响应数据
        output_data($array_data, mobile_page($page_count));
    }
    
    /**
     * 退货退款详细
     */
    public function refund_detailOp() {
        // ajax响应数组
        $array_data = array();
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
        $model_refund = Model('refund_return');
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = intval($this->member_info['member_id']);
        // 查询条件 退货ID
        $condition['refund_id'] = intval($_POST['refund_id']);
        // 查询退货信息
        $refund_list = $model_refund->getReturnList($condition);
        
        /* lyq@newland 添加开始   **/
        /* 时间：2015/05/25       **/
        /* 功能ID：SHOP009        **/
        
        // 判断是 订单退款 还是 退货退款
        if (empty($refund_list) || !is_array($refund_list)) {
            // 未查到退货信息
            // 重新查询退款信息
            $refund_list = $model_refund->getRefundList($condition);
            // 判断是否有退款信息
            if (empty($refund_list) || !is_array($refund_list)) {
                // 无退款信息，数据错误，返回错误信息
                output_error('参数错误');
            } else {
                // 有退款信息，退款类型为 订单退款
                $refund_list[0]['type'] = 'refund';
            }
        } else {
            // 退款类型为 退货退款
            $refund_list[0]['type'] = 'return';
        }
        
        /* lyq@newland 添加结束   **/
        
        $refund = $refund_list[0];
        // 添加退货信息到ajax响应数组
        $array_data['refund'] = $refund;
        
        // 获取物流列表
        $express_list  = rkcache('express',true);
        // 添加物流列表到ajax响应数组
        $array_data['express_list'] = $express_list;
        // 1.点击 查看 按钮进入
        // 2.退货物流公司ID不为空
        // 3.退货物流单号不为空
        if ($_POST['type'] == 'detail' && $refund['express_id'] > 0 && !empty($refund['invoice_no'])) {
            $array_data['refund_e_name'] = $express_list[$refund['express_id']]['e_name'];
        }
        
        // 获取退款退货状态
        $state_list = $this->get_refund_state_array();
        // 添加退款退货状态到ajax响应数组
        $array_data['state_list'] = $state_list;
        
        // 清空查询条件
        $condition = array();
        // 查询条件 订单ID
        $condition['order_id'] = $refund['order_id'];
        // 获取订单信息
        $order_list = $model_refund->getRightOrderList($condition, $refund['order_goods_id']);
        // 生成图片url
        foreach ($order_list['goods_list'] as &$value) {
            // 商品图片完整url
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
        }
        // 添加订单信息到ajax响应数组
        $array_data['order_list'] = $order_list;
        
        // 点击退货按钮进入
        if ($_POST['type'] == 'action') {
            // 交易模型 trade.model.php
            $model_trade = Model('trade');
            // 发货默认5天后才能选择没收到
            $array_data['return_delay'] = $model_trade->getMaxDay('return_delay');
            // 卖家不处理收货时按同意并弃货处理
            $array_data['return_confirm'] = $model_trade->getMaxDay('return_confirm');
            // 退货信息表单提交flg
            $array_data['ship'] = 1;
        }
        /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $array_data['stroe_vip_list'] = $stroe_vip_list;
        /* wqw@newland 添加结束   **/
        // 返回响应数据
        output_data($array_data);
    }
    
    /**
     * 更新退货退款物流信息
     */
    public function add_refund_invoiceOp() {
        $model_refund = Model('refund_return');
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = intval($this->member_info['member_id']);
        // 查询条件 退货ID
        $condition['refund_id'] = intval($_POST['refund_id']);
        // 退货退款物流信息
        $refund_array = array();
        // 发货时间
        $refund_array['ship_time'] = time();
        // 收货延迟时间
        $refund_array['delay_time'] = time();
        // 物流公司ID
        $refund_array['express_id'] = $_POST['express_id'];
        // 物流单号
        $refund_array['invoice_no'] = $_POST['invoice_no'];
        // 物流状态
        $refund_array['goods_state'] = '2';
        // 更新物流信息
        $state = $model_refund->editRefundReturn($condition, $refund_array);
        if ($state) {
            // 更新成功
            output_data('success');
        } else {
            // 更新失败
            output_data('faild');
        }
    }
    
    /**
     * 延长 收货延迟时间
     * 时间：2015/05/21
     */
    public function delayOp() {
        $model_refund = Model('refund_return');
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];
        // 查询条件 退货退款ID
        $condition['refund_id'] = intval($_GET['return_id']);
        // 查询退货退款信息
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        // 检查状态,防止页面刷新不及时造成数据错误
        if ($return['seller_state'] != '2' || $return['goods_state'] != '3') {
            output_error('参数错误');
        }
        // 更新字段
        $refund_array = array();
        // 更新字段 收货延迟时间
        $refund_array['delay_time'] = time();
        // 更新字段 物流状态
        $refund_array['goods_state'] = '2';
        // 更新 
        $state = $model_refund->editRefundReturn($condition, $refund_array);
        if ($state) {
            // 更新成功
            output_data('success');
        } else {
            // 更新失败
            output_data('faild');
        }
    }
    
    /**
     * 申请退货退款 页面显示
     * 时间：2015/05/22
     */
    public function refund_addOp() {
        // ajax响应数组
        $array_data = array();
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
        $model_refund = Model('refund_return');
        // 退款退货原因
        $reason_list = $model_refund->getReasonList();
        // 添加退货退款原因到ajax响应数组
        $array_data['reason_list'] = $reason_list;
        // 获取订单ID
        $order_id = intval($_GET['order_id']);
        // 获取订单商品ID
        $goods_id = intval($_GET['goods_id']);
        // 参数验证
        if ($order_id < 1 || $goods_id < 1) {
            output_error('参数错误');
        }
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $order_id;
        // 获取订单信息
        $order = $model_refund->getRightOrderList($condition, $goods_id);
        // 生成图片url
        foreach ($order['goods_list'] as &$value) {
            // 商品图片完整url
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
        }
        // 添加订单信息到ajax响应数组
        $array_data['order_list'] = $order;
        // 订单金额
        $order_amount = $order['order_amount'];
        // 订单退款金额
        $order_refund_amount = $order['refund_amount'];
        // 订单商品列表
        $goods_list = $order['goods_list'];
        // 获取订单商品
        $goods = $goods_list[0];
        // 商品实际成交价
        $goods_pay_price = $goods['goods_pay_price'];
        // 订单金额 小于 商品成交价+订单退款金额时
        if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
            // 重新计算商品实际成交价 (可退金额)
            $goods_pay_price = $order_amount - $order_refund_amount;
            // 更新数组数据
            $goods['goods_pay_price'] = $goods_pay_price;
        }
        // 添加商品信息到ajax响应数组
        $array_data['goods'] = $goods;

        // 清空查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $order['buyer_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $order['order_id'];
        // 查询条件 商品ID
        $condition['order_goods_id'] = $goods['rec_id'];
        // 查询条件 卖家处理状态 小于3
        $condition['seller_state'] = array('lt','3');
        // 获取退货退款列表
        $refund_list = $model_refund->getRefundReturnList($condition);
        // 退货退款信息
        $refund = array();
        // 退货退款列表不为空
        if (!empty($refund_list) && is_array($refund_list)) {
            // 更新退货退款信息
            $refund = $refund_list[0];
        }
        // 根据订单状态判断是否可以退款退货
        $refund_state = $model_refund->getRefundState($order);
        // 检查订单状态,防止页面刷新不及时造成数据错误
        if ($refund['refund_id'] > 0 || $refund_state != 1) {
            output_error('参数错误');
        }
        
        // 获取物流列表
        $express_list  = rkcache('express',true);
        // 添加物流列表到ajax响应数组
        $array_data['express_list'] = $express_list;
        /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $array_data['stroe_vip_list'] = $stroe_vip_list;
        /* wqw@newland 添加结束   **/
        // 返回响应数据
        output_data($array_data);
    }
    
    /**
     * 申请退货退款 执行
     * 时间：2015/05/22
     */
    public function refund_add_actionOp() {
         /**  订单数据获取开始  **/
        $model_refund = Model('refund_return');
        // 退款退货原因
        $reason_list = $model_refund->getReasonList();
        // 添加退货退款原因到ajax响应数组
        $array_data['reason_list'] = $reason_list;
        // 获取订单ID
        $order_id = intval($_GET['order_id']);
        // 获取订单商品ID
        $goods_id = intval($_GET['goods_id']);
        // 参数验证
        if ($order_id < 1 || $goods_id < 1) {
            output_error('参数错误');
        }
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $order_id;
        // 获取订单信息
        $order = $model_refund->getRightOrderList($condition, $goods_id);
        // 生成图片url
        foreach ($order['goods_list'] as &$value) {
            // 商品图片完整url
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
        }
        // 订单金额
        $order_amount = $order['order_amount'];
        // 订单退款金额
        $order_refund_amount = $order['refund_amount'];
        // 订单商品列表
        $goods_list = $order['goods_list'];
        // 获取订单商品
        $goods = $goods_list[0];
        // 商品实际成交价
        $goods_pay_price = $goods['goods_pay_price'];
        // 订单金额 小于 商品成交价+订单退款金额时
        if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
            // 重新计算商品实际成交价 (可退金额)
            $goods_pay_price = $order_amount - $order_refund_amount;
            // 更新数组数据
            $goods['goods_pay_price'] = $goods_pay_price;
        }

        // 清空查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $order['buyer_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $order['order_id'];
        // 查询条件 商品ID
        $condition['order_goods_id'] = $goods['rec_id'];
        // 查询条件 卖家处理状态 小于3
        $condition['seller_state'] = array('lt','3');
        // 获取退货退款列表
        $refund_list = $model_refund->getRefundReturnList($condition);
        // 退货退款信息
        $refund = array();
        // 退货退款列表不为空
        if (!empty($refund_list) && is_array($refund_list)) {
            // 更新退货退款信息
            $refund = $refund_list[0];
        }
        // 根据订单状态判断是否可以退款退货
        $refund_state = $model_refund->getRefundState($order);
        // 检查订单状态,防止页面刷新不及时造成数据错误
        if ($refund['refund_id'] > 0 || $refund_state != 1) {
            output_error('参数错误');
        }
        /**  订单数据获取结束  **/
        
        /**  退货数据整理开始  **/
        // 退款数组
        $refund_array = array();
        // 退款金额
        $refund_amount = floatval($_POST['refund_amount']);
        // 退款金额 超出正常金额范围
        if (($refund_amount < 0) || ($refund_amount > $goods_pay_price)) {
            // 默认设置为最大退款金额
            $refund_amount = $goods_pay_price;
        }
        // 退货数量
        $goods_num = intval($_POST['goods_num']);
        // 退货数量 超出正常范围
        if (($goods_num < 0) || ($goods_num > $goods['goods_num'])) {
            // 默认设置为 1
            $goods_num = 1;
        }
        // 原因信息
        $refund_array['reason_info'] = '';
        // 退货退款原因ID
        $reason_id = intval($_POST['reason_id']);
        // 更新退款数组 原因ID
        $refund_array['reason_id'] = $reason_id;
        // 原因数组
        $reason_array = array();
        // 原因信息 ‘其他’
        $reason_array['reason_info'] = '其他';
        // 向原因列表中添加 ‘其他’ 项
        $reason_list[0] = $reason_array;
        // 原因列表中存在相应的原因ID项
        if (!empty($reason_list[$reason_id])) {
            // 更新原因数组
            $reason_array = $reason_list[$reason_id];
            // 更新退款数组 原因信息
            $refund_array['reason_info'] = $reason_array['reason_info'];
        }
        // 更新退款数组 凭证 无 
        $refund_array['pic_info'] = '';

        $model_trade = Model('trade');
        // 订单状态30:已发货
        $order_shipped = $model_trade->getOrderState('order_shipped');
        // 订单状态为 已发货 时
        if ($order['order_state'] == $order_shipped) {
            // 更新退款数组 锁定类型:1为不用锁定,2为需要锁定
            $refund_array['order_lock'] = '2';
        }
        // 更新退款数组 类型:1为退款,2为退货
        $refund_array['refund_type'] = '2';
        // 更新退款数组 退货类型:1为不用退货,2为需要退货
        $refund_array['return_type'] = '2';
        // 更新退款数组 状态:1为待审核,2为同意,3为不同意
        $refund_array['seller_state'] = '1';
        // 更新退款数组 退款金额
        $refund_array['refund_amount'] = ncPriceFormat($refund_amount);
        // 更新退款数组 退款数量
        $refund_array['goods_num'] = $goods_num;
        // 更新退款数组 申请原因
        $refund_array['buyer_message'] = $_POST['buyer_message'];
        // 更新退款数组 申请时间
        $refund_array['add_time'] = time();
        /**  退货数据整理结束  **/
        
        // 执行退货
        $state = $model_refund->addRefundReturn($refund_array,$order,$goods);

        if ($state) {
            // 订单状态为 已发货 时
            if ($order['order_state'] == $order_shipped) {
                // 锁定订单
                $model_refund->editOrderLock($order_id);
            }
            // 更新成功
            output_data('success');
        } else {
            // 更新失败
            output_data('faild');
        }
    }
    
    /**
     * 获取退货退款状态
     * @return array 状态数组
     */
    private function get_refund_state_array() {
        return array(
            'admin' => array(
                '1' => '处理中',
                '2' => '待处理',
                '3' => '已完成'
            ),
            'seller'=> array(
                '1' => '待审核',
                '2' => '同意',
                '3' => '不同意'
            )
        );
    }
    
    /* lyq@newland 添加开始   **/
    /* 时间：2015/05/25       **/
    /* 功能ID：SHOP009        **/
    
    /**
     * 申请订单退款 页面显示
     *   根据订单ID获取订单的详细信息
     */
    public function add_refund_allOp() {
        // ajax响应数组
        $array_data = array();
        
        $model_trade = Model('trade');
        $model_refund = Model('refund_return');
        // 订单ID
        $order_id = intval($_GET['order_id']);
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $order_id;
        // 获取订单信息
        $order = $model_refund->getRightOrderList($condition);
        // 生成图片url
        foreach ($order['goods_list'] as &$value) {
            // 商品图片完整url
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
        }
        // 添加订单信息至ajax响应数组
        $array_data['order'] = $order;
        
        /* 订单状态检查部分开始 **/
        // 清空查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $order['buyer_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $order['order_id'];
        // 查询条件 商品ID 0表示全部退款
        $condition['goods_id'] = '0';
        // 查询条件 卖家处理状态 小于3 （1为待审核,2为同意,3为不同意）
        $condition['seller_state'] = array('lt','3');
        // 获取退货退款信息
        $refund_list = $model_refund->getRefundReturnList($condition);
        $refund = array();
        // 如果退货退款信息不为空
        if (!empty($refund_list) && is_array($refund_list)) {
            $refund = $refund_list[0];
        }
        // 订单状态20:已付款
        $order_paid = $model_trade->getOrderState('order_paid');
        // 支付方式
        $payment_code = $order['payment_code'];
        // 检查订单状态,防止页面刷新不及时造成数据错误
        if ($refund['refund_id'] > 0 || $order['order_state'] != $order_paid || $payment_code == 'offline') {
            output_error('参数错误');
        }
        /* 订单状态检查部分结束 **/
        
        // 获取物流列表
        $express_list  = rkcache('express',true);
        // 添加物流列表到ajax响应数组
        $array_data['express_list'] = $express_list;
        
        output_data($array_data);
    }
    
    /**
     * 申请订单退款 执行
     */
    public function add_refund_all_actionOp() {
        $model_trade = Model('trade');
        $model_refund = Model('refund_return');
        // 订单ID
        $order_id = intval($_GET['order_id']);
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $order_id;
        // 获取订单信息
        $order = $model_refund->getRightOrderList($condition);
        // 订单金额
        $order_amount = $order['order_amount'];
        
        /* 订单状态检查部分开始 **/
        // 清空查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $order['buyer_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $order['order_id'];
        // 查询条件 商品ID 0表示全部退款
        $condition['goods_id'] = '0';
        // 查询条件 卖家处理状态 小于3 （1为待审核,2为同意,3为不同意）
        $condition['seller_state'] = array('lt','3');
        // 获取退货退款信息
        $refund_list = $model_refund->getRefundReturnList($condition);
        $refund = array();
        // 如果退货退款信息不为空
        if (!empty($refund_list) && is_array($refund_list)) {
            $refund = $refund_list[0];
        }
        // 订单状态20:已付款
        $order_paid = $model_trade->getOrderState('order_paid');
        // 支付方式
        $payment_code = $order['payment_code'];
        // 检查订单状态,防止页面刷新不及时造成数据错误
        if ($refund['refund_id'] > 0 || $order['order_state'] != $order_paid || $payment_code == 'offline') {
            output_error('参数错误');
        }
        /* 订单状态检查部分结束 **/
        
        /* 添加退款信息开始 **/
        $refund_array = array();
        // 类型:1为退款,2为退货
        $refund_array['refund_type'] = '1';
        // 状态:1为待审核,2为同意,3为不同意
        $refund_array['seller_state'] = '1';
        // 锁定类型:1为不用锁定,2为需要锁定
        $refund_array['order_lock'] = '2';
        // 商品ID 0表示全部退款
        $refund_array['goods_id'] = '0';
        // 订单商品ID,全部退款是0
        $refund_array['order_goods_id'] = '0';
        // 原因ID:0为其它
        $refund_array['reason_id'] = '0';
        // 原因内容
        $refund_array['reason_info'] = '取消订单，全部退款';
        // 商品名称
        $refund_array['goods_name'] = '订单商品全部退款';
        // 退款金额
        $refund_array['refund_amount'] = ncPriceFormat($order_amount);
        // 申请原因
        $refund_array['buyer_message'] = $_POST['buyer_message'];
        // 添加时间
        $refund_array['add_time'] = time();
        // 添加退款信息
        $state = $model_refund->addRefundReturn($refund_array,$order);
        if ($state) {
            // 锁定订单
            $model_refund->editOrderLock($order_id);
            // 更新成功
            output_data('success');
        } else {
            // 更新失败
            output_data('faild');
        }
        /* 添加退款信息结束 **/
    }
    
    /* lyq@newland 添加结束   **/
    
    
    /* lyq@newland 添加开始   **/
    /* 时间：2015/05/26       **/
    /* 功能ID：SHOP009        **/
    
    /**
     * 取消订单退款
     *   只能取消商家未审核状态的订单退款
     */
    public function undo_refund_orderOp() {
        // 订单ID
        $order_id = intval($_GET['order_id']);
        // 退货退款ID
        $refund_id = intval($_GET['refund_id']);
        
        $model_refund_return = Model('refund_return');
        // 查询条件
        $condition = array();
        // 订单ID是否为0
        if ($order_id != 0) {
            // 不为0
            // 查询条件 订单ID
            $condition['order_id'] = $order_id;
        }
        // 退货图款ID是否为0
        if ($refund_id != 0) {
            // 不为0
            // 查询条件 退货图款ID
            $condition['refund_id'] = $refund_id;
        }
        // 如果条件为空，说明参数传递错误
        if (empty($condition)) {
            // 设置默认查询条件：退货退款ID为0
            $condition['refund_id'] = 0;
        }
        // 根据订单ID查询退款信息
        $refund_info = $model_refund_return->getRefundReturnInfo($condition);
        // 是否有退款信息
        if (!empty($refund_info) && is_array($refund_info)) {
            // 防止页面刷新不及时导致数据错误，重新检查退款状态
            if (!($refund_info['seller_state'] == 1 && $refund_info['goods_id'] == 0)) {
                // 退款状态有误，返回错误信息
                output_error('参数错误');
            }
        } else {
            // 退款状态有误，返回错误信息
            output_error('参数错误');
        }
        
        // 解锁订单
        $model_refund_return->editOrderUnlock($refund_info['order_id']);
        // 删除订单退款信息
        $state = $model_refund_return->del_refund_return($refund_info['refund_id']);
        if ($state) {
            // 取消成功
            output_data('success');
        } else {
            // 取消失败
            output_data('faild');
        }
    }
    
    /**
     * 取消退货
     *   只能取消商家未审核状态的退货
     */
    public function undo_return_goodsOp() {
        // 订单ID
        $order_id = intval($_GET['order_id']);
        // 订单商品ID
        $order_goods_id = intval($_GET['order_goods_id']);
        // 退货退款ID
        $refund_id = intval($_GET['refund_id']);
        
        $model_refund_return = Model('refund_return');
        // 查询条件
        $condition = array();
        // 订单ID是否为0
        if ($order_id != 0) {
            // 不为0
            // 查询条件 订单ID
            $condition['order_id'] = $order_id;
            // 查询条件 订单商品ID
            $condition['order_goods_id'] = $order_goods_id;
        }
        // 退货图款ID是否为0
        if ($refund_id != 0) {
            // 不为0
            // 查询条件 退货图款ID
            $condition['refund_id'] = $refund_id;
        }
        // 如果条件为空，说明参数传递错误
        if (empty($condition)) {
            // 设置默认查询条件：退货退款ID为0
            $condition['refund_id'] = 0;
        }
        // 根据订单ID查询退款信息
        $refund_info = $model_refund_return->getRefundReturnInfo($condition);
        // 是否有退款信息
        if (!empty($refund_info) && is_array($refund_info)) {
            // 防止页面刷新不及时导致数据错误，重新检查退款状态
            if ($refund_info['seller_state'] != 1) {
                // 退款状态有误，返回错误信息
                output_error('参数错误');
            }
        } else {
            // 退款状态有误，返回错误信息
            output_error('参数错误');
        }
        
        // 解锁订单
        $model_refund_return->editOrderUnlock($refund_info['order_id']);
        // 删除订单退款信息
        $state = $model_refund_return->del_refund_return($refund_info['refund_id']);
        if ($state) {
            // 取消成功
            output_data('success');
        } else {
            // 取消失败
            output_data('faild');
        }
    }
    
    /* lyq@newland 添加结束   **/
}

/* lyq@newland 添加结束   **/