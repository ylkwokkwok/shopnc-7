<?php
/**
 * 交易新模型
 */
defined('InShopNC') or exit('Access Invalid!');
class tradeModel extends Model{
	public function __construct() {
		parent::__construct();
	}
	/**
	 * 订单处理天数
	 *
	 */
	public function getMaxDay($day_type = 'all') {
        if(APP_ID == 'mobile'){
            $max_data = array(
                'order_refund' => 15,//收货完成后可以申请退款退货
                'refund_confirm' => 7,//卖家不处理退款退货申请时按同意处理
                'return_confirm' => 7,//卖家不处理收货时按弃货处理
                'return_delay' => 5//退货的商品发货多少天以后才可以选择没收到
            );
        }else{
            /* lyq@newland 修改开始 **/
            /* 时间：2015/07/13    **/
            $max_data = array(
                'order_refund'   => ORDER_REFUND,   // 收货完成后可以申请退款退货
                'refund_confirm' => REFUND_CONFIRM, // 卖家不处理退款退货申请时按同意处理
                'return_confirm' => RETURN_CONFIRM, // 卖家不处理收货时按弃货处理
                'return_delay'   => RETURN_DELAY    // 退货的商品发货多少天以后才可以选择没收到
            );
            /* lyq@newland 修改结束 **/
        }
		if ($day_type == 'all') return $max_data;//返回所有
		if (intval($max_data[$day_type]) < 1) $max_data[$day_type] = 1;//最小的值设置为1
		return $max_data[$day_type];
	}
	/**
	 * 订单状态
	 *
	 */
	public function getOrderState($type = 'all') {
		$state_data = array(
			'order_cancel' => ORDER_STATE_CANCEL,//0:已取消
			'order_default' => ORDER_STATE_NEW,//10:未付款
			'order_paid' => ORDER_STATE_PAY,//20:已付款
			'order_shipped' => ORDER_STATE_SEND,//30:已发货
			'order_completed' => ORDER_STATE_SUCCESS //40:已收货
			);
		if ($type == 'all') return $state_data;//返回所有
		return $state_data[$type];
	}

	/* lyq@newland 修改开始   **/
    /* 时间：2015/07/23       **/
	/**
	 * 更新退款申请
	 * @param int $member_id 会员编号
	 * @param int $store_id 店铺编号
	 */
	public function editRefundConfirm($member_id=0, $store_id=0) {
        if(APP_ID == 'mobile' || APP_ID == 'wx'){
            $refund_confirm = $this->getMaxDay('refund_confirm');//卖家不处理退款申请时按同意并弃货处理
            $day = time()-$refund_confirm*60*60*24;
            // update & left join
            $up_sql  = "UPDATE " . DBPRE . "refund_return ";
            $lj_sql = "LEFT JOIN " . DBPRE . "store ";
            $lj_sql .= "    ON " . DBPRE . "refund_return.store_id = " . DBPRE . "store.store_id ";

            // set 同意
            $set_agree_sql = "SET " . DBPRE . "refund_return.refund_state = '2', ";
            $set_agree_sql .= "    " . DBPRE . "refund_return.seller_state = '2', ";
            $set_agree_sql .= "    " . DBPRE . "refund_return.return_type = '1', ";
            $set_agree_sql .= "    " . DBPRE . "refund_return.seller_time = '" . time() . "', ";
            $set_agree_sql .= "    " . DBPRE . "refund_return.seller_message = '超过"
                . $refund_confirm . "天未处理退款退货申请，按同意处理。' ";
            // set 拒绝
            $set_refuse_sql = "SET " . DBPRE . "refund_return.refund_state = '3', ";
            $set_refuse_sql .= "    " . DBPRE . "refund_return.seller_state = '3', ";
            $set_refuse_sql .= "    " . DBPRE . "refund_return.seller_time = '" . time() . "', ";
            $set_refuse_sql .= "    " . DBPRE . "refund_return.seller_message = '超过"
                . $refund_confirm . "天未处理退款退货申请，按拒绝处理。' ";
            // where
            $where_sql = "WHERE ";
            if ($member_id > 0) {
                $where_sql .= " " . DBPRE . "refund_return.buyer_id = '".$member_id."' AND ";
            }
            if ($store_id > 0) {
                $where_sql .= " " . DBPRE . "refund_return.store_id = '".$store_id."' AND ";
            }
            $where_sql .= "    " . DBPRE . "refund_return.seller_state = 1 ";
            $where_sql .= "AND " . DBPRE . "refund_return.add_time < " . $day . " ";

            // where 认证商家
            $is_ctf_sql = "AND " . DBPRE . "store.authentication_state = 1 ";

            // where 非认证商家
            $not_ctf_sql = "AND " . DBPRE . "store.authentication_state = 0 ";

            // 查询条件拼接
            $condition = " seller_state=1 and add_time<".$day;//状态:1为待审核,2为同意,3为不同意
            $condition_sql = "";
            if ($member_id > 0) {
                $condition_sql = " buyer_id = '".$member_id."'  and ";
            }
            if ($store_id > 0) {
                $condition_sql = " store_id = '".$store_id."' and ";
            }
            $condition_sql = $condition_sql.$condition;
            // 查询退款信息
            //$refund_array = array();
            //$refund_array['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成
            //$refund_array['seller_state'] = '2';//卖家处理状态:1为待审核,2为同意,3为不同意
            //$refund_array['return_type'] = '1';//退货类型:1为不用退货,2为需要退货
            //$refund_array['seller_time'] = time();
            //$refund_array['seller_message'] = '超过'.$refund_confirm.'天未处理退款退货申请，按同意处理。';
            $refund = $this->table('refund_return')->field('refund_sn,store_id,order_lock,refund_type')->where($condition_sql)->select();
            // 更新退款信息 认证商家
            $this->execute($up_sql . $lj_sql . $set_agree_sql . $where_sql . $is_ctf_sql);
            // 更新退款信息 非认证商家
            $this->execute($up_sql . $lj_sql . $set_refuse_sql . $where_sql . $not_ctf_sql);

            // 查询需要解锁的订单的order_id
            $srch_oid_sql  = "SELECT " . DBPRE . "refund_return.order_id ";
            $srch_oid_sql .= "  FROM " . DBPRE . "refund_return ";
            $srch_oid_sql .= $lj_sql;
            $srch_oid_sql .= "WHERE ";
            if ($member_id > 0) {
                $srch_oid_sql .= " " . DBPRE . "refund_return.buyer_id = '".$member_id."' AND ";
            }
            if ($store_id > 0) {
                $srch_oid_sql .= " " . DBPRE . "refund_return.store_id = '".$store_id."' AND ";
            }
            $srch_oid_sql .= "    " . DBPRE . "refund_return.seller_state = '3' ";
            $srch_oid_sql .= "AND " . DBPRE . "refund_return.order_lock = '2' ";
            $srch_oid_sql .= $not_ctf_sql;
            $order_ids_arr = $this->query($srch_oid_sql);
            // 订单id拼接
            $order_ids_str = '';
            if (!empty($order_ids_arr) && is_array($order_ids_arr)) {   // 存在需要解锁的订单
                foreach ($order_ids_arr as $value) {
                    $order_ids_str .= $value['order_id'].',';
                }
            }

            if ($order_ids_str !== '') {    // 存在需要解锁的订单
                // 订单解锁条件
                $unlock_condition = array();
                $unlock_condition['order_id'] = array('in', substr($order_ids_str, 0, strlen($order_ids_str) - 1));
                $unlock_condition['lock_state'] = array('egt','1');
                // 订单解锁数据
                $unlock_data = array();
                $unlock_data['lock_state'] = array('exp','lock_state-1');
                $unlock_data['delay_time'] = time();
                $model_order = Model('order');
                // 执行订单解锁
                $model_order->editOrder($unlock_data, $unlock_condition);
            }

            // 查询需要付款的退款单的refund_id
            $srch_rid_sql  = "SELECT " . DBPRE . "refund_return.refund_id ";
            $srch_rid_sql .= "  FROM " . DBPRE . "refund_return ";
            $srch_rid_sql .= $lj_sql;
            $srch_rid_sql .= "WHERE ";
            if ($member_id > 0) {
                $srch_rid_sql .= " " . DBPRE . "refund_return.buyer_id = '".$member_id."' AND ";
            }
            if ($store_id > 0) {
                $srch_rid_sql .= " " . DBPRE . "refund_return.store_id = '".$store_id."' AND ";
            }
            $srch_rid_sql .= "    " . DBPRE . "refund_return.refund_state = '2' ";
            $srch_rid_sql .= "AND " . DBPRE . "refund_return.seller_state = '2' ";
            $srch_rid_sql .= "AND " . DBPRE . "refund_return.return_type = '1' ";
            $srch_rid_sql .= $is_ctf_sql;
            $refund_ids_arr = $this->query($srch_rid_sql);
            if (!empty($refund_ids_arr) && is_array($refund_ids_arr)) {   // 存在需要退款的订单
                $refund_model = Model('refund_return');
                foreach ($refund_ids_arr as $value) {
                    $refund_model->send_refund_money($value['refund_id']);
                    $refund_model->send_return_money($value['refund_id']);
                }
            }
            //$this->table('refund_return')->where($condition_sql)->update($refund_array);

            // 发送商家提醒
            foreach ((array)$refund as $val) {
                // 参数数组
                $param = array();
                $param['type'] = $val['order_lock'] == 2 ? '售前' : '售后';
                $param['refund_sn'] = $val['refund_sn'];
                if (intval($val['refund_type']) == 1) {    // 退款
                    $this->sendStoreMsg('refund_auto_process', $val['store_id'], $param);
                } else {                                     // 退货
                    $this->sendStoreMsg('return_auto_process', $val['store_id'], $param);
                }
            }

            $return_confirm = $this->getMaxDay('return_confirm');//卖家不处理收货时按弃货处理
            $day = time()-$return_confirm*60*60*24;
            $condition = " seller_state=2 and goods_state=2 and return_type=2 and delay_time<".$day;//物流状态:1为待发货,2为待收货,3为未收到,4为已收货
            $condition_sql = "";
            if ($member_id > 0) {
                $condition_sql = " buyer_id = '".$member_id."'  and ";
            }
            if ($store_id > 0) {
                $condition_sql = " store_id = '".$store_id."' and ";
            }
            $condition_sql = $condition_sql.$condition;
            $refund_array = array();
            $refund_array['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成
            $refund_array['return_type'] = '1';//退货类型:1为不用退货,2为需要退货
            $refund_array['seller_message'] = '超过'.$return_confirm.'天未处理收货，按弃货处理';
            $refund = $this->table('refund_return')->field('refund_id,refund_sn,store_id,order_lock,refund_type')->where($condition_sql)->select();
            $this->table('refund_return')->where($condition_sql)->update($refund_array);

            // 发送商家提醒
            foreach ((array)$refund as $val) {
                $refund_model = Model('refund_return');
                $refund_model->send_return_money($val['refund_id']);
                // 参数数组
                $param = array();
                $param['type'] = $val['order_lock'] == 2 ? '售前' : '售后';
                $param['refund_sn'] = $val['refund_sn'];
                $this->sendStoreMsg('return_auto_receipt', $val['store_id'], $param);
            }
        }else{
            $refund_confirm = $this->getMaxDay('refund_confirm');//卖家不处理退款申请时按同意并弃货处理
            $day = time()-$refund_confirm*60*60*24;
            $condition = " seller_state=1 and add_time<".$day;//状态:1为待审核,2为同意,3为不同意
            $condition_sql = "";
            if ($member_id > 0) {
                $condition_sql = " buyer_id = '".$member_id."'  and ";
            }
            if ($store_id > 0) {
                $condition_sql = " store_id = '".$store_id."' and ";
            }
            $condition_sql = $condition_sql.$condition;
            $refund_array = array();
            $refund_array['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成
            $refund_array['seller_state'] = '2';//卖家处理状态:1为待审核,2为同意,3为不同意
            $refund_array['return_type'] = '1';//退货类型:1为不用退货,2为需要退货
            $refund_array['seller_time'] = time();
            $refund_array['seller_message'] = '超过'.$refund_confirm.'天未处理退款退货申请，按同意处理。';
            $refund = $this->table('refund_return')->field('refund_sn,store_id,order_lock,refund_type')->where($condition_sql)->select();
            $this->table('refund_return')->where($condition_sql)->update($refund_array);

            // 发送商家提醒
            foreach ((array)$refund as $val) {
                // 参数数组
                $param = array();
                $param['type'] = $val['order_lock'] == 2 ? '售前' : '售后';
                $param['refund_sn'] = $val['refund_sn'];
                if (intval($val['refund_type']) == 1) {    // 退款
                    $this->sendStoreMsg('refund_auto_process', $val['store_id'], $param);
                } else {                                     // 退货
                    $this->sendStoreMsg('return_auto_process', $val['store_id'], $param);
                }
            }

            $return_confirm = $this->getMaxDay('return_confirm');//卖家不处理收货时按弃货处理
            $day = time()-$return_confirm*60*60*24;
            $condition = " seller_state=2 and goods_state=2 and return_type=2 and delay_time<".$day;//物流状态:1为待发货,2为待收货,3为未收到,4为已收货
            $condition_sql = "";
            if ($member_id > 0) {
                $condition_sql = " buyer_id = '".$member_id."'  and ";
            }
            if ($store_id > 0) {
                $condition_sql = " store_id = '".$store_id."' and ";
            }
            $condition_sql = $condition_sql.$condition;
            $refund_array = array();
            $refund_array['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成
            $refund_array['return_type'] = '1';//退货类型:1为不用退货,2为需要退货
            $refund_array['seller_message'] = '超过'.$return_confirm.'天未处理收货，按弃货处理';
            $refund = $this->table('refund_return')->field('refund_sn,store_id,order_lock,refund_type')->where($condition_sql)->select();
            $this->table('refund_return')->where($condition_sql)->update($refund_array);

            // 发送商家提醒
            foreach ((array)$refund as $val) {
                // 参数数组
                $param = array();
                $param['type'] = $val['order_lock'] == 2 ? '售前' : '售后';
                $param['refund_sn'] = $val['refund_sn'];
                $this->sendStoreMsg('return_auto_receipt', $val['store_id'], $param);
            }
        }
	}
 	/* lyq@newland 修改结束   **/

    /**
     * 发送店铺消息
     * @param string $code
     * @param int $store_id
     * @param array $param
     */
    private function sendStoreMsg($code, $store_id, $param) {
        QueueClient::push('sendStoreMsg', array('code' => $code, 'store_id' => $store_id, 'param' => $param));
    }
}
?>