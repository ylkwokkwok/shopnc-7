<?php

/**
 * 我的订单
 *
 *
 *
 *
 */
defined('NlWxShop') or exit('Access Invalid!');

class member_orderControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 订单列表
     */
    public function order_listOp() {
        $model_order = Model('order');

        /* wqw@newland 添加开始   　* */
        /* 时间：2015/06/08       * */
        /* 功能ID：ADMIN006       * */
        $model_store = Model('store');
        $temp = $model_store->goods_vip_list();
        if (!empty($temp)) {
            foreach ($temp as $value) {
                $stroe_vip_list[] = $value['store_id'];
            }
        } else {
            $stroe_vip_list[] = '';
        }
        /* wqw@newland 添加结束   * */
        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];

        /* lyq@newland 添加开始   * */
        /* 时间：2015/05/18       * */
        /* 功能ID：SHOP010        * */
        // 增加查询条件：订单状态
        /* zly@newland添加检索条件开始* */
        /* 时间：2015/07/27       * */

        
        /* zz@newland 修改开始   * */
        /* 时间：2016/03/18       * */
        //将待收货和待退款查询分开
        if ($_GET['order_state'] == '30') {
            $condition['order_state'] = array('in', 30);
            $condition['lock_state'] = array('in', 0);
            /* zz@newland 修改结束   * */
            // 检索条件：已退款
        } else if ($_GET['order_state'] == '60') {
            $condition['order_state'] = array('eq', 0);
            $condition['refund_state'] = array('eq', 2);
            // 检索条件：退款中
        } else if ($_GET['order_state'] == '50') {
            $condition['lock_state'] = array('gt', 0);
            // 检索条件：已取消
        } else if ($_GET['order_state'] == '0') {
            $condition['order_state'] = array('eq', 0);
            $condition['refund_state'] = array('eq', 0);
        } else {
            $condition['order_state'] = array('in', $_GET['order_state']);
        }
        /* zly@newland添加检索条件结束* */
        /* lyq@newland 添加结束   * */

        /* lyq@newland 添加开始   * */
        /* 时间：2015/05/28       * */
        /* 功能ID：SHOP014        * */
        // 请求参数中是否有订单号
        if ($_GET['order_sn'] != '') {
            // 有订单号，增加查询条件 订单号
            $condition['order_sn'] = array('like', '%' . $_GET['order_sn'] . '%');
        }
        // 匹配日期格式
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date']);
        // 日期格式正确 取日期，日期格式不正确 赋空
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        // 开始/结束日是否为空
        if ($start_unixtime || $end_unixtime) {
            // 不为空，增加查询条件 添加时间
            $condition['add_time'] = array('time', array($start_unixtime, $end_unixtime));
        }
        /* lyq@newland 添加结束   * */

        // 根据查询条件查询订单信息
        $order_list_array = $model_order->getNormalOrderList($condition, $this->page, '*', 'order_id desc', '', array('order_goods'));

        /* lyq@newland 添加开始   * */
        /* 时间：2015/05/21       * */
        /* 功能ID：SHOP009        * */
        $model_refund_return = Model('refund_return');
        // 更新订单列表，检测商品是否可以退货
        $order_list_array = $model_refund_return->getGoodsRefundList($order_list_array);
        /* lyq@newland 添加结束   * */

        //订单列表以支付单pay_sn分组显示
        $order_group_list = array();
        $order_pay_sn_array = array();
        foreach ($order_list_array as $value) {
            //显示取消订单
            $value['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel', $value);
            //显示收货
            $value['if_receive'] = $model_order->getOrderOperateState('receive', $value);
            //显示锁定中
            $value['if_lock'] = $model_order->getOrderOperateState('lock', $value);
            //显示物流跟踪
            $value['if_deliver'] = $model_order->getOrderOperateState('deliver', $value);

            /* zly@newland 添加开始   * */
            /* 时间：2015/05/22       * */
            /* 功能ID：SHOP002        * */
            // 显示评价
            $value['if_evaluation'] = $model_order->getOrderOperateState('evaluation', $value);

            /* zly@newland 添加结束   * */

            /* lyq@newland 添加开始   * */
            /* 时间：2015/05/25       * */
            /* 功能ID：SHOP009        * */

            // 显示订单退款
            $value['if_refund_cancel'] = $model_order->getOrderOperateState('refund_cancel', $value);

            /* lyq@newland 添加结束   * */


            /* lyq@newland 添加开始   * */
            /* 时间：2015/05/26       * */
            /* 功能ID：SHOP009        * */

            // 显示取消订单退款 默认不显示
            $value['if_undo_refund_cancel'] = FALSE;

            /* lyq@newland 修改开始   * */
            /* 时间：2015/07/23       * */
            // 根据订单ID查询最后一次退款的信息
            $refund_info = Model()->table('refund_return')
                            ->where(array('order_id' => $value['order_id']))
                            ->order('add_time desc')
                            ->field('*')->find();
            /* lyq@newland 修改结束   * */

            // 是否有退款信息
            if (!empty($refund_info) && is_array($refund_info)) {
                // 有退款信息，检查退款状态
                if ($refund_info['seller_state'] == 1 && $refund_info['goods_id'] == 0) {
                    // 卖家待审核 且 全部商品退款
                    // 显示取消订单退款 设置为显示
                    $value['if_undo_refund_cancel'] = TRUE;
                }
            }
            /* lyq@newland 添加结束   * */


            /* lyq@newland 添加开始   * */
            /* 时间：2015/05/27       * */
            /* 功能ID：SHOP009        * */

            // 显示投诉
            $value['if_complain'] = $model_order->getOrderOperateState('complain', $value);
            /* lyq@newland 添加结束   * */

            //商品图
            foreach ($value['extend_order_goods'] as $k => $goods_info) {
                $value['extend_order_goods'][$k]['goods_image_url'] = cthumb($goods_info['goods_image'], 240, $value['store_id']);
            }

            $order_group_list[$value['pay_sn']]['order_list'][] = $value;

            //如果有在线支付且未付款的订单则显示合并付款链接
            if ($value['order_state'] == ORDER_STATE_NEW) {
                $order_group_list[$value['pay_sn']]['pay_amount'] += $value['order_amount'] - $value['rcb_amount'] - $value['pd_amount'];
            }
            $order_group_list[$value['pay_sn']]['add_time'] = $value['add_time'];

            //记录一下pay_sn，后面需要查询支付单表
            $order_pay_sn_array[] = $value['pay_sn'];
        }

        $new_order_group_list = array();
        foreach ($order_group_list as $key => $value) {
            $value['pay_sn'] = strval($key);
            $new_order_group_list[] = $value;
        }

        $page_count = $model_order->gettotalpage();

        $array_data = array('order_group_list' => $new_order_group_list);
        if (isset($_GET['getpayment']) && $_GET['getpayment'] == "true") {
            $model_mb_payment = Model('mb_payment');

            $payment_list = $model_mb_payment->getMbPaymentOpenList();
            $payment_array = array();
            if (!empty($payment_list)) {
                foreach ($payment_list as $value) {
                    $payment_array[] = array('payment_code' => $value['payment_code'], 'payment_name' => $value['payment_name']);
                }
            }
            $array_data['payment_list'] = $payment_array;
        }

        /* wqw@newland 添加开始   　* */
        /* 时间：2015/06/08       * */
        /* 功能ID：ADMIN006       * */
        $array_data['stroe_vip_list'] = $stroe_vip_list;
        /* wqw@newland 添加结束   * */

        //output_data(array('order_group_list' => $array_data), mobile_page($page_count));
        output_data($array_data, mobile_page($page_count));
    }

    /**
     * 取消订单
     */
    public function order_cancelOp() {
        $model_order = Model('order');
        $logic_order = Logic('order');
        $order_id = intval($_POST['order_id']);

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('buyer_cancel', $order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }

        $result = $logic_order->changeOrderStateCancel($order_info, 'buyer', $this->member_info['member_name'], '其它原因');
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            output_data('1');
        }
    }

    /**
     * 订单确认收货
     */
    public function order_receiveOp() {
        $model_order = Model('order');
        $logic_order = Logic('order');
        $order_id = intval($_POST['order_id']);

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('receive', $order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }

        $result = $logic_order->changeOrderStateReceive($order_info, 'buyer', $this->member_info['member_name']);
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            output_data('1');
        }
    }

    /**
     * 物流跟踪
     */
    public function search_deliverOp() {
        $order_id = intval($_POST['order_id']);
        if ($order_id <= 0) {
            output_error('订单不存在');
        }

        $model_order = Model('order');
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $model_order->getOrderInfo($condition, array('order_common', 'order_goods'));
        if (empty($order_info) || !in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
            output_error('订单不存在');
        }

        $express = rkcache('express', true);
        $e_code = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
        $e_name = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];

        $deliver_info = $this->_get_express($e_code, $order_info['shipping_code']);
        output_data(array('express_name' => $e_name, 'shipping_code' => $order_info['shipping_code'], 'deliver_info' => $deliver_info));
    }

    /* lyq@newland 修改开始  * */
    /* 时间：2015/06/04      * */
    /* 订单详情页不弹出对话框提示错误 * */

    /**
     * 从第三方取快递信息
     * @param type $e_code 物流编号
     * @param type $shipping_code 物流单号
     * @param type $err_display 弹出错误信息标志 TRUE:弹出/FALSE：返回错误信息
     * @return type $err_display==FALSE时，返回错误信息
     */
    public function _get_express($e_code, $shipping_code, $err_display = TRUE) {

        $url = 'http://www.kuaidi100.com/query?type=' . $e_code . '&postid=' . $shipping_code . '&id=1&valicode=&temp=' . random(4) . '&sessionid=&tmp=' . random(4);
        import('function.ftp');
        $content = dfsockopen($url);
        $content = json_decode($content, true);

        /* lyq@newland 添加开始  * */
        /* 时间：2015/06/04      * */
        // 禁止弹出错误信息
        if (!$err_display) {
            // 返回错误信息
            return array('物流信息查询失败');
        }
        /* lyq@newland 添加结束  * */

        if ($content['status'] != 200) {
            output_error('物流信息查询失败');
        }
        $content['data'] = array_reverse($content['data']);
        $output = array();
        if (is_array($content['data'])) {
            foreach ($content['data'] as $k => $v) {
                if ($v['time'] == '')
                    continue;
                $output[] = $v['time'] . '&nbsp;&nbsp;' . $v['context'];
            }
        }
        if (empty($output))
            exit(json_encode(false));
        if (strtoupper(CHARSET) == 'GBK') {
            $output = Language::getUTF8($output); //网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        }

        return $output;
    }

    /* lyq@newland 修改结束  * */

    /* lyq@newland 添加开始   * */
    /* 时间：2015/05/18       * */
    /* 功能ID：SHOP008-SHOP010 * */

    /**
     * 订单详细 
     *
     */
    public function order_detailOp() {
        // 订单ID
        $order_id = intval($_POST['order_id']);
        if ($order_id <= 0) {
            output_error('订单不存在');
        }
        $model_order = Model('order');
        /* wqw@newland 添加开始   　* */
        /* 时间：2015/06/08       * */
        /* 功能ID：ADMIN006       * */
        $model_store = Model('store');
        $temp = $model_store->goods_vip_list();
        if (!empty($temp)) {
            foreach ($temp as $value) {
                $stroe_vip_list[] = $value['store_id'];
            }
        } else {
            $stroe_vip_list[] = '';
        }
        /* wqw@newland 添加结束   * */
        // 查询条件
        $condition = array();
        // 查询条件：订单ID
        $condition['order_id'] = $order_id;
        // 查询条件：会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];
        // 获取订单信息
        $order_info = $model_order->getOrderInfo($condition, array('order_goods', 'order_common', 'store'));
        if (empty($order_info) || $order_info['delete_state'] == ORDER_DEL_STATE_DROP) {
            output_error('订单不存在');
        }

        // 返回值数组
        $array_data = array();

        $model_refund_return = Model('refund_return');
        $order_list = array();
        $order_list[$order_id] = $order_info;
        // 订单商品的退款退货显示
        $order_list = $model_refund_return->getGoodsRefundList($order_list, 1);
        $order_info = $order_list[$order_id];
        $refund_all = $order_info['refund_list'][0];
        // 订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
        if (!empty($refund_all) && $refund_all['seller_state'] < 3) {
            $array_data['refund_all'] = $refund_all;
        }

        // 显示锁定中
        $order_info['if_lock'] = $model_order->getOrderOperateState('lock', $order_info);

        // 显示取消订单
        $order_info['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel', $order_info);

        // 显示退款取消订单
        $order_info['if_refund_cancel'] = $model_order->getOrderOperateState('refund_cancel', $order_info);

        // 显示投诉
        $order_info['if_complain'] = $model_order->getOrderOperateState('complain', $order_info);

        // 显示收货
        $order_info['if_receive'] = $model_order->getOrderOperateState('receive', $order_info);

        // 显示物流跟踪
        $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver', $order_info);

        // 显示评价
        $order_info['if_evaluation'] = $model_order->getOrderOperateState('evaluation', $order_info);

        // 显示分享
        $order_info['if_share'] = $model_order->getOrderOperateState('share', $order_info);

        // 显示系统自动取消订单日期
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            $order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_DAY * 24 * 3600;
        }

        // 显示快递信息
        if ($order_info['shipping_code'] != '') {
            $express = rkcache('express', true);
            $order_info['express_info']['e_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
            $order_info['express_info']['e_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
            $order_info['express_info']['e_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_url'];

            /* lyq@newland 修改开始  * */
            /* 时间：2015/06/04      * */
            // 获取物流信息
            $order_info['deliver_info'] = $this->_get_express($order_info['express_info']['e_code'], $order_info['shipping_code'], FALSE);
            /* lyq@newland 修改结束  * */
        }

        //显示系统自动收获时间
        if ($order_info['order_state'] == ORDER_STATE_SEND) {
            $order_info['order_confirm_day'] = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY * 24 * 3600;
        }

        //如果订单已取消，取得取消原因、时间，操作人
        if ($order_info['order_state'] == ORDER_STATE_CANCEL) {
            $order_info['close_info'] = $model_order->getOrderLogInfo(array('order_id' => $order_info['order_id']), 'log_id desc');
        }

        // 获取商品图片、销售类型、url等信息
        foreach ($order_info['extend_order_goods'] as $value) {
            // 商品图片完整url
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            // 商品销售类型
            $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
            // 商品url
            $value['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
            if ($value['goods_type'] == 5) {
                $order_info['zengpin_list'][] = $value;
            } else {
                $order_info['goods_list'][] = $value;
            }
        }

        // 商品数目
        if (empty($order_info['zengpin_list'])) {
            // 无赠品
            $order_info['goods_count'] = count($order_info['goods_list']);
        } else {
            // 有赠品
            $order_info['goods_count'] = count($order_info['goods_list']) + 1;
        }

        // 数据格式化 优惠券
        $order_info['extend_order_common']['voucher_price'] = number_format($order_info['extend_order_common']['voucher_price'], 2, '.', '');
        // 数据格式化 商品总金额
        $order_info['goods_amount_true'] = number_format(number_format($order_info['goods_amount'], 2, '.', '') + number_format($order_info['extend_order_common']['voucher_price'], 2, '.', ''), 2, '.', '');

        $array_data['order_info'] = $order_info;

        // 卖家发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_common']['daddress_id']));
            $array_data['daddress_info'] = $daddress_info;
        }
        /* wqw@newland 修改开始   　* */
        /* 时间：2015/06/08       * */
        /* 功能ID：ADMIN006       * */
        $array_data['stroe_vip_list'] = $stroe_vip_list;
        /* wqw@newland 修改结束   * */
        // 返回数据
        output_data($array_data);
    }

    /* lyq@newland 添加结束   * */
}
