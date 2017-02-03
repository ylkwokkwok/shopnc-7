<?php
/**
 * 退款退货
 */
defined('InShopNC') or exit('Access Invalid!');
class refund_returnModel extends Model{

    /**
     * 取得退单数量
     * @param unknown $condition
     */
    public function getRefundReturn($condition) {
        return $this->table('refund_return')->where($condition)->count();
    }

	/**
	 * 增加退款退货
	 *
	 * @param
	 * @return int
	 */
	public function addRefundReturn($refund_array, $order = array(), $goods = array()) {
	    if (!empty($order) && is_array($order)) {
			$refund_array['order_id'] = $order['order_id'];
			$refund_array['order_sn'] = $order['order_sn'];
			$refund_array['store_id'] = $order['store_id'];
			$refund_array['store_name'] = $order['store_name'];
			$refund_array['buyer_id'] = $order['buyer_id'];
			$refund_array['buyer_name'] = $order['buyer_name'];
	    }
	    if (!empty($goods) && is_array($goods)) {
			$refund_array['goods_id'] = $goods['goods_id'];
			$refund_array['order_goods_id'] = $goods['rec_id'];
			$refund_array['order_goods_type'] = $goods['goods_type'];
			$refund_array['goods_name'] = $goods['goods_name'];
			$refund_array['commis_rate'] = $goods['commis_rate'];
			$refund_array['goods_image'] = $goods['goods_image'];
	    }
	    $refund_array['refund_sn'] = $this->getRefundsn($refund_array['store_id']);
		$refund_id = $this->table('refund_return')->insert($refund_array);

        // 发送商家提醒
        $param = array();
        if (intval($refund_array['refund_type']) == 1) {    // 退款
            $param['code'] = 'refund';
        } else {    // 退货
            $param['code'] = 'return';
        }
        $param['store_id'] = $order['store_id'];
        $type = $refund_array['order_lock'] == 2 ? '售前' : '售后';
        $param['param'] = array(
            'type' => $type,
            'refund_sn' => $refund_array['refund_sn']
        );
        QueueClient::push('sendStoreMsg', $param);

		return $refund_id;
	}
	/* lyq@newland 添加开始   **/
    /* 时间：2015/05/26       **/
    /* 功能ID：SHOP009        **/
    /**
     * 删除退货退款信息
     *   根据退货退款ID删除退货退款信息，返回删除成功标志
     * @param string/int $refund_id 退货退款ID 主键
     * @return bool 删除成功标志 true/false
     */
    public function del_refund_return($refund_id) {
        // 缺少信息推送

        // 根据退货退款ID删除退货退款信息
        $state =  $this->table('refund_return')
                       ->where(array('refund_id' => intval($refund_id)))
                       ->delete();
        // 返回 删除成功标志
        return $state;
    }
    /* lyq@newland 添加结束   **/

	/**
	 * 订单锁定
	 *
	 * @param
	 * @return bool
	 */
	public function editOrderLock($order_id) {
	    $order_id = intval($order_id);
		if ($order_id > 0) {
    	    $condition = array();
    	    $condition['order_id'] = $order_id;
    		$data = array();
    		$data['lock_state'] = array('exp','lock_state+1');
    		$model_order = Model('order');
    		$result = $model_order->editOrder($data,$condition);
    		return $result;
		}
		return false;
	}

	/**
	 * 订单解锁
	 *
	 * @param
	 * @return bool
	 */
	public function editOrderUnlock($order_id) {
	    $order_id = intval($order_id);
		if ($order_id > 0) {
    	    $condition = array();
    	    $condition['order_id'] = $order_id;
    	    $condition['lock_state'] = array('egt','1');
    		$data = array();
    		$data['lock_state'] = array('exp','lock_state-1');
    		$data['delay_time'] = time();
    		$model_order = Model('order');
    		$result = $model_order->editOrder($data,$condition);
    		return $result;
		}
		return false;
	}

	/**
	 * 修改记录
	 *
	 * @param
	 * @return bool
	 */
	public function editRefundReturn($condition, $data) {
		if (empty($condition)) {
			return false;
		}
		if (is_array($data)) {
			$result = $this->table('refund_return')->where($condition)->update($data);
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * 平台确认退款处理
	 *
	 * @param
	 * @return bool
	 */
	public function editOrderRefund($refund) {
	    $refund_id = intval($refund['refund_id']);
		if ($refund_id > 0) {
		    Language::read('model_lang_index');
			$order_id = $refund['order_id'];//订单编号
			$field = 'order_id,buyer_id,buyer_name,store_id,order_sn,order_amount,payment_code,order_state,refund_amount,rcb_amount';
    		$model_order = Model('order');
    		$order = $model_order->getOrderInfo(array('order_id'=> $order_id),array(),$field);

			$model_predeposit = Model('predeposit');
    	    try {
    	        $this->beginTransaction();
    	        $order_amount = $order['order_amount'];//订单金额
    	        $rcb_amount = $order['rcb_amount'];//充值卡支付金额
    	        $predeposit_amount = $order_amount-$order['refund_amount']-$rcb_amount;//可退预存款金额

                /* hl@newland 删除开始   **/
                /* 时间：2015/06/18        **/
                /* 功能ID：SHOP014         **/
                // 退款后不更新用户可用余额
                if(APP_ID == 'shop'){
                    if (($rcb_amount > 0) && ($refund['refund_amount'] > $predeposit_amount)) {//退充值卡
                        $log_array = array();
                        $log_array['member_id'] = $order['buyer_id'];
                        $log_array['member_name'] = $order['buyer_name'];
                        $log_array['order_sn'] = $order['order_sn'];
                        $log_array['amount'] = $refund['refund_amount'];
                        if ($predeposit_amount > 0) {
                            $log_array['amount'] = $refund['refund_amount']-$predeposit_amount;
                        }
                        $state = $model_predeposit->changeRcb('refund', $log_array);//增加买家可用充值卡金额
                    }
                    if ($predeposit_amount > 0) {//退预存款
                        $log_array = array();
                        $log_array['member_id'] = $order['buyer_id'];
                        $log_array['member_name'] = $order['buyer_name'];
                        $log_array['order_sn'] = $order['order_sn'];
                        $log_array['amount'] = $refund['refund_amount'];//退预存款金额
                        if ($refund['refund_amount'] > $predeposit_amount) {
                            $log_array['amount'] = $predeposit_amount;
                        }
                        $state = $model_predeposit->changePd('refund', $log_array);//增加买家可用预存款金额
                    }
                }else{
                    $state = true;
                }
                /* hl@newland 删除结束   **/

    			$order_state = $order['order_state'];
    			$model_trade = Model('trade');
    			$order_paid = $model_trade->getOrderState('order_paid');//订单状态20:已付款
    			if ($state && $order_state == $order_paid) {
            	    Logic('order')->changeOrderStateCancel($order, 'system', '系统', '商品全部退款完成取消订单',false);
            	}
    			if ($state) {
    			    $order_array = array();
    			    $order_amount = $order['order_amount'];//订单金额
    			    $refund_amount = $order['refund_amount']+$refund['refund_amount'];//退款金额
    			    $order_array['refund_state'] = ($order_amount-$refund_amount) > 0 ? 1:2;
    			    $order_array['refund_amount'] = ncPriceFormat($refund_amount);
    			    $order_array['delay_time'] = time();
    			    $state = $model_order->editOrder($order_array,array('order_id'=> $order_id));//更新订单退款
            	}
    			if ($state && $refund['order_lock'] == '2') {
    			    $state = $this->editOrderUnlock($order_id);//订单解锁
    			}
    			$this->commit();
        		return $state;
    		} catch (Exception $e) {
    		    $this->rollback();
    		    return false;
    		}
		}
		return false;
	}

	/**
	 * 增加退款退货原因
	 *
	 * @param
	 * @return int
	 */
	public function addReason($reason_array) {
		$reason_id = $this->table('refund_reason')->insert($reason_array);
		return $reason_id;
	}

	/**
	 * 修改退款退货原因记录
	 *
	 * @param
	 * @return bool
	 */
	public function editReason($condition, $data) {
		if (empty($condition)) {
			return false;
		}
		if (is_array($data)) {
			$result = $this->table('refund_reason')->where($condition)->update($data);
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * 删除退款退货原因记录
	 *
	 * @param
	 * @return bool
	 */
	public function delReason($condition) {
		if (empty($condition)) {
			return false;
		} else {
			$result = $this->table('refund_reason')->where($condition)->delete();
			return $result;
		}
	}

	/**
	 * 退款退货原因记录
	 *
	 * @param
	 * @return array
	 */
    public function getReasonList($condition = array(), $page = '', $limit = '', $fields = '*') {
		$result = $this->table('refund_reason')->field($fields)->where($condition)->page($page)->limit($limit)->order('sort asc,reason_id desc')->key('reason_id')->select();
		return $result;
    }

	/**
	 * 取退款退货记录
	 *
	 * @param
	 * @return array
	 */
	public function getRefundReturnList($condition = array(), $page = '',$where ='', $fields = '*', $limit = '') {
        if(APP_ID == 'shop'){
            $result = $this->table('refund_return')->field($fields)->where($condition)->page($page)->limit($limit)->order('refund_id desc')->select();
            return $result;
        }else{
            /*zly@newland 添加检索条件：退货次数开始  **/
            /*时间：2015/06/10                       **/
            /*功能ID：ADMIN008                       **/
            // 判断检索条件：退货次数是否存在
            if (isset($where['return_num']) && $where['return_num'] != '') {
                // 根据检索条件：退货次数检索退货状态下的店铺
                $refund_type = $condition['refund_type'];
                $refund_state = $condition['refund_state'];
                $refund_state_where = '';
                // 所有记录的时候
                if (isset($condition['refund_state'])) {
                    $refund_state_where = "and refund_state = $refund_state";
                }

                // 检索对应退货次数的商品
                $sql = "SELECT
                        ss.goods_id
                    FROM(
                        SELECT
                            goods_id,
                            refund_type,
                            refund_state,
                            count(*) AS Thum
                        FROM
                            ".DBPRE."refund_return
                        WHERE
                            refund_type = $refund_type $refund_state_where
                        GROUP BY
                            goods_id
                        )   AS ss
                    WHERE 
                        ss.Thum = " . $where['return_num'];
                $goods_id_arr = $this->query($sql);
                // 查询出对应退货次数结果
                if (count($goods_id_arr) > 0) {
                    $in_goods_id = '';
                    foreach ($goods_id_arr as $goods_id) {
                        $in_goods_id .= $goods_id['goods_id'] . ',';
                    }
                    // 截取商品ID，除最后一位
                    $in_goods_id = substr($in_goods_id, 0, strlen($in_goods_id) - 1);
                    // 检索对应商品
                    $condition['goods_id'] = array('in', $in_goods_id);
                } else {
                    // 查询结果为空
                    $condition['2'] = 1;
                }
                // 返回查询结果
                $result = $this->table('refund_return')->field($fields)->where($condition)->page($page)->limit($limit)->order('refund_id desc')->select();
                return $result;
                /* zly@newland 添加检索条件：退货次数结束**/
            } else {
                // 退货次数不存在 返回查询结果
                $result = $this->table('refund_return')->field($fields)->where($condition)->page($page)->limit($limit)->order('refund_id desc')->select();
                return $result;
            }
        }
    }

	/**
	 * 取退款记录
	 *
	 * @param
	 * @return array
	 */
	public function getRefundList($condition = array(), $page = '') {
	    $condition['refund_type'] = '1';//类型:1为退款,2为退货
		$result = $this->getRefundReturnList($condition, $page);
		return $result;
	}

	/**
	 * 取退货记录
	 *
	 * @param
	 * @return array
	 */
	public function getReturnList($condition = array(), $page = '',$where='') {
	    $condition['refund_type'] = '2';//类型:1为退款,2为退货
		$result = $this->getRefundReturnList($condition, $page,$where);
		return $result;
	}

	/**
	 * 退款退货申请编号
	 *
	 * @param
	 * @return array
	 */
	public function getRefundsn($store_id) {
		$result = mt_rand(100,999).substr(100+$store_id,-3).date('ymdHis');
		return $result;
	}

	/**
	 * 取一条记录
	 *
	 * @param
	 * @return array
	 */
	public function getRefundReturnInfo($condition = array(), $fields = '*') {
        return $this->table('refund_return')->where($condition)->field($fields)->find();
	}

	/**
	 * 根据订单取商品的退款退货状态
	 *
	 * @param
	 * @return array
	 */
	public function getGoodsRefundList($order_list = array(), $order_refund = 0) {
	    $order_ids = array();//订单编号数组
	    $order_ids = array_keys($order_list);
	    $model_trade = Model('trade');
	    $condition = array();
	    $condition['order_id'] = array('in', $order_ids);
	    $refund_list = $this->table('refund_return')->where($condition)->order('refund_id desc')->select();
	    $refund_goods = array();//已经提交的退款退货商品
	    if (!empty($refund_list) && is_array($refund_list)) {
    	    foreach ($refund_list as $key => $value) {
    	        $order_id = $value['order_id'];//订单编号
    	        $goods_id = $value['order_goods_id'];//订单商品表编号
    	        if (empty($refund_goods[$order_id][$goods_id])) {
    	            $refund_goods[$order_id][$goods_id] = $value;
    	            if ($order_refund > 0) {//订单下的退款退货所有记录
    	                $order_list[$order_id]['refund_list'] = $refund_goods[$order_id];
    	            }
    	        }
    	    }
	    }
	    if (!empty($order_list) && is_array($order_list)) {
    	    foreach ($order_list as $key => $value) {
    	        $order_id = $key;
    	        $goods_list = $value['extend_order_goods'];//订单商品
    	        $order_state = $value['order_state'];//订单状态
        	    $order_paid = $model_trade->getOrderState('order_paid');//订单状态20:已付款
        	    $payment_code = $value['payment_code'];//支付方式
        	    if ($order_state == $order_paid && $payment_code != 'offline') {//已付款未发货的非货到付款订单可以申请取消
        	        $order_list[$order_id]['refund'] = '1';
        	    } elseif ($order_state > $order_paid && !empty($goods_list) && is_array($goods_list)) {//已发货后对商品操作
        	        $refund = $this->getRefundState($value);//根据订单状态判断是否可以退款退货
            	    foreach ($goods_list as $k => $v) {
            	        $goods_id = $v['rec_id'];//订单商品表编号
            	        if ($v['goods_pay_price'] > 0) {//实际支付额大于0的可以退款
            	            $v['refund'] = $refund;
            	        }
            	        if (!empty($refund_goods[$order_id][$goods_id])) {
            	            $seller_state = $refund_goods[$order_id][$goods_id]['seller_state'];//卖家处理状态:1为待审核,2为同意,3为不同意
            	            if ($seller_state == 3) {
            	                $order_list[$order_id]['extend_complain'][$goods_id] = '1';//不同意可以发起退款投诉
            	            } else {
            	                $v['refund'] = '0';//已经存在处理中或同意的商品不能再操作
            	            }
            	            $v['extend_refund'] = $refund_goods[$order_id][$goods_id];
            	        }
            	        $goods_list[$k] = $v;
            	    }
        	    }
    	        $order_list[$order_id]['extend_order_goods'] = $goods_list;
    	    }
	    }
		return $order_list;
	}

	/**
	 * 根据订单判断投诉订单商品是否可退款
	 *
	 * @param
	 * @return array
	 */
	public function getComplainRefundList($order, $order_goods_id = 0) {
	    $list = array();
	    $refund_list = array();//已退或处理中商品
	    $refund_goods = array();//可退商品
	    if (!empty($order) && is_array($order)) {
            $order_id = $order['order_id'];
            $order_list[$order_id] = $order;
            $order_list = $this->getGoodsRefundList($order_list);
            $order = $order_list[$order_id];
            $goods_list = $order['extend_order_goods'];
            $order_amount = $order['order_amount'];//订单金额
		    $order_refund_amount = $order['refund_amount'];//订单退款金额
            foreach ($goods_list as $k => $v) {
                $goods_id = $v['rec_id'];//订单商品表编号
                if ($order_goods_id > 0 && $goods_id != $order_goods_id) {
                    continue;
                }
        		$v['refund_state'] = 3;
                if (!empty($v['extend_refund'])) {
                    $v['refund_state'] = $v['extend_refund']['seller_state'];//卖家处理状态为3,不同意时能退款
                }
                if ($v['refund_state'] > 2) {//可退商品
                    $goods_pay_price = $v['goods_pay_price'];//商品实际成交价
            		if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
            		    $goods_pay_price = $order_amount - $order_refund_amount;
            		    $v['goods_pay_price'] = $goods_pay_price;
            		}
            		$v['goods_refund'] = $v['goods_pay_price'];
                    $refund_goods[$goods_id] = $v;
                } else {//已经存在处理中或同意的商品不能再退款
                    $refund_list[$goods_id] = $v;
                }
            }
		}
		$list = array(
			'refund' => $refund_list,
			'goods' => $refund_goods
			);
		return $list;
	}

	/**
	 * 详细页右侧订单信息
	 *
	 * @param
	 * @return array
	 */
	public function getRightOrderList($order_condition, $order_goods_id = 0){
		$model_order = Model('order');
		$order_info = $model_order->getOrderInfo($order_condition,array('order_common','store'));
		Tpl::output('order',$order_info);
		$order_id = $order_info['order_id'];

		$store = $order_info['extend_store'];
		Tpl::output('store',$store);
		$order_common = $order_info['extend_order_common'];
		Tpl::output('order_common',$order_common);
		if ($order_common['shipping_express_id'] > 0) {
            $express = rkcache('express',true);
            Tpl::output('e_code',$express[$order_common['shipping_express_id']]['e_code']);
            Tpl::output('e_name',$express[$order_common['shipping_express_id']]['e_name']);
        }

		$condition = array();
		$condition['order_id'] = $order_id;
		if ($order_goods_id > 0) {
		    $condition['rec_id'] = $order_goods_id;//订单商品表编号
        }
		$goods_list = $model_order->getOrderGoodsList($condition);
		Tpl::output('goods_list',$goods_list);
		$order_info['goods_list'] = $goods_list;

        return $order_info;
	}

	/**
	 * 根据订单状态判断是否可以退款退货
	 *
	 * @param
	 * @return array
	 */
	public function getRefundState($order) {
	    $refund = '0';//默认不允许退款退货
	    $order_state = $order['order_state'];//订单状态
	    $model_trade = Model('trade');
	    $order_shipped = $model_trade->getOrderState('order_shipped');//30:已发货
	    $order_completed = $model_trade->getOrderState('order_completed');//40:已收货
	    switch ($order_state) {
            case $order_shipped:
                $payment_code = $order['payment_code'];//支付方式
                if ($payment_code != 'offline') {//货到付款订单在没确认收货前不能退款退货
                    $refund = '1';
                }
                break;
            case $order_completed:
        	    $order_refund = $model_trade->getMaxDay('order_refund');//15:收货完成后可以申请退款退货
        	    $delay_time = $order['delay_time']+60*60*24*$order_refund;
                if ($delay_time > time()) {
                    $refund = '1';
                }
                break;
            default:
                $refund = '0';
                break;
	    }

	    return $refund;
	}

	/**
	 * 向模板页面输出退款退货状态
	 *
	 * @param
	 * @return array
	 */
	public function getRefundStateArray($type = 'all') {
		Language::read('refund');
		$state_array = array(
			'1' => Language::get('refund_state_confirm'),
			'2' => Language::get('refund_state_yes'),
			'3' => Language::get('refund_state_no')
			);//卖家处理状态:1为待审核,2为同意,3为不同意
		Tpl::output('state_array', $state_array);

		$admin_array = array(
			'1' => '处理中',
			'2' => '待处理',
			'3' => '已完成'
			);//确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
		Tpl::output('admin_array', $admin_array);

		$state_data = array(
			'seller' => $state_array,
			'admin' => $admin_array
			);
		if ($type == 'all') return $state_data;//返回所有
		return $state_data[$type];
	}

    /**
     * 退货退款数量
     *
     * @param array $condition
     * @return int
     */
    public function getRefundReturnCount($condition) {
        return $this->table('refund_return')->where($condition)->count();
    }

	/*
	 *  获得退货退款的店铺列表
	 *  @param array $complain_list
	 *  @return array
	 */
	public function getRefundStoreList($list) {
        $store_ids = array();
	    if (!empty($list) && is_array($list)) {
    	    foreach ($list as $key => $value) {
    	        $store_ids[] = $value['store_id'];//店铺编号
    	    }
	    }
	    $field = 'store_id,store_name,member_id,member_name,seller_name,store_company_name,store_qq,store_ww,store_phone,store_domain';
        return Model('store')->getStoreMemberIDList($store_ids, $field);
	}
    /* zly@newland 下载退货名单开始**/
    /* 时间2015/06/02             **/
    /* 功能ID：ADMIN008           **/
    /**
     * 下载退货名单
     * @param array $condition 退货检索信息
     * @return type 退货详细信息
     */
    public function download_getReturnList($condition = array(),$where = '') {
        if (isset($where['return_num']) && $where['return_num'] != '') {
        // 根据检索条件：退货次数检索退货状态下的店铺
        $refund_type = 2;
        $refund_state = $condition['refund_state'];
        $refund_state_where = '';
        // 所有记录的时候
        if (isset($condition['refund_state'])) {
            $refund_state_where = "and refund_state = $refund_state";
        }
        // 检索对应退货次数的商品
        $sql = "SELECT
                    ss.goods_id
                FROM
                (
                    SELECT
                        goods_id,
                        refund_type,
                        refund_state,
                        count(*) AS Thum
                    FROM
                        ".DBPRE."refund_return
                    WHERE
                        refund_type = $refund_type $refund_state_where
                    GROUP BY
                        goods_id
                )   AS ss
                WHERE
                    ss.Thum = " . $where['return_num'];
        $goods_id_arr = $this->query($sql);
        // 查询出对应退货次数结果
        if (count($goods_id_arr) > 0) {
            $in_goods_id = '';
            foreach ($goods_id_arr as $goods_id) {
                $in_goods_id .= $goods_id['goods_id'] . ',';
            }
            // 截取商品ID，除最后一位
            $in_goods_id = substr($in_goods_id, 0, strlen($in_goods_id) - 1);
            // 检索对应商品
            $condition['goods_id'] = array('in', $in_goods_id);
        } else {
            // 查询结果为空
            $condition['2'] = 1;
        }
    }
        // 下载检索条件:退货
    $condition['refund_type'] = '2';
        // 整理下载所需字段
        $fields = 'order_sn,refund_sn,store_name,buyer_name,goods_name,'
                    .'goods_num,refund_amount,order_goods_type,refund_type,'
                    .'seller_state,refund_state,return_type,goods_state,'
                    .'add_time,seller_time,admin_time,reason_info,'
                    .'buyer_message,seller_message,admin_message,express_id,invoice_no,'
                    .'ship_time,delay_time,receive_time,receive_message';
        // 获取退货信息
        $result = $this->table('refund_return')->field($fields)->where($condition)->select(array('limit' => false));
        return $result;
    }
    /* zly@newland 下载退货名单结束**/
    
    
    /* lyq@newland 添加开始   **/
    /* 时间：2015/07/23       **/
    /**
     * 自动审核退款、退钱
     *   商家处理退款请求后，自动执行平台审核，并将购物款退回给买家
     * 
     * @param type $refund_id 退货退款id
     * @return boolean 
     */
    public function send_refund_money($refund_id) {
        $condition = array();
        $condition['refund_id'] = intval($refund_id);
        $refund_list = $this->getRefundList($condition);
        if (empty($refund_list)) {
            return FALSE;
        }
        $refund = $refund_list[0];
        
        if ($refund['refund_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
            return FALSE;
        }
        $order_id = $refund['order_id'];
        $refund_array = array();
        $refund_array['admin_time'] = time();
        $refund_array['refund_state'] = '3'; //状态:1为处理中,2为待管理员处理,3为已完成
        $refund_array['admin_message'] = '自动审核，打款。';
        $state = $this->editOrderRefund($refund);
        /* hl@newland 添加开始   **/
        /* 时间：2015/06/18      **/
        /* 功能ID：SHOP014       **/
        require_once(dirname(__FILE__) . "./../../wx/wx_refund.php");
        // 获取订单信息
        $order_info = Model()->table('order')->where(array('order_id' => $order_id))->select();

        $param_arr = array();
        // 商户订单号
        $param_arr['out_trade_no'] = $order_info[0]['pay_sn'];
        // 微信订单号
        $param_arr['transaction_id'] = $order_info[0]['wx_pay_sn'];
        // 商户退款单号
        $param_arr['out_refund_no'] = $refund['refund_sn'];
        // 总金额
        $param_arr['total_fee'] = $order_info[0]['order_amount'] * 100;
        // 退款金额
        $param_arr['refund_fee'] = $refund['refund_amount'] * 100;
        // 操作员
        $param_arr['user_id'] = "1";
        // 请求微信，发起退款
        $wx_refund_result = refund($param_arr);
        $wx_refund_result['return_code'] = 'SUCCESS';
        /* hl@newland 添加结束   **/
        if ($state && $wx_refund_result['return_code'] == 'SUCCESS') {
            $this->editRefundReturn($condition, $refund_array);


            // 发送买家消息
            $param = array();
            $param['code'] = 'refund_return_notice';
            $param['member_id'] = $refund['buyer_id'];
            $param['param'] = array(
                'refund_url' => urlShop('member_refund', 'view', array('refund_id' => $refund['refund_id'])),
                'refund_sn' => $refund['refund_sn']
            );
            QueueClient::push('sendMemberMsg', $param);

            $this->admin_log('退款确认，退款编号' . $refund['refund_sn']);
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * 自动审核退货、退钱
     *   商家处理退货请求后，自动执行平台审核，并将购物款退回给买家
     * 
     * @param type $return_id 退货退款id
     * @return boolean 
     */
    public function send_return_money($return_id) {
        $condition = array();
        $condition['refund_id'] = intval($return_id);
        $return_list = $this->getReturnList($condition);
        if (empty($return_list)) {
            return FALSE;
        }
        $return = $return_list[0];
        
        if ($return['refund_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
            return FALSE;
        }
        $order_id = $return['order_id'];

        /* lyq@newland 添加开始  **/
        /* 时间：2015/06/19      **/
        /* 功能ID：SHOP014       **/
        // 获取订单信息
        $order_info = Model()->table('order')->where(array('order_id' => $order_id))->select();
        // 可退金额
        $predeposit_amount = $order_info[0]['order_amount'] - $order_info[0]['refund_amount'];
        if($predeposit_amount == 0) {   // 可退金额为0
            // 微信退款标志：否
            $do_wx = FALSE;
        } else if ($predeposit_amount - $return['refund_amount'] < 0) { // 可退金额不为0，且可退金额小于商品退款金额
            // 将商品退款金额改为可退金额
            $return['refund_amount'] = $predeposit_amount;
            // 微信退款标志：是
            $do_wx = TRUE;
        } else {    //
            // 微信退款标志：是
            $do_wx = TRUE;
        }
        /* lyq@newland 添加结束  **/
        $refund_array = array();
        $refund_array['admin_time'] = time();
        $refund_array['refund_state'] = '3'; //状态:1为处理中,2为待管理员处理,3为已完成
        $refund_array['admin_message'] = '自动审核，打款。';
        /* lyq@newland 添加开始  **/
        /* 时间：2015/06/19      **/
        /* 功能ID：SHOP014       **/
        // 退款金额
        $refund_array['refund_amount'] = $return['refund_amount'];
        /* lyq@newland 添加结束  **/
        $state = $this->editOrderRefund($return);

        /* lyq@newland 添加开始  **/
        /* 时间：2015/06/19      **/
        /* 功能ID：SHOP014       **/
        require_once(dirname(__FILE__) . "./../../wx/wx_refund.php");

        $param_arr = array();
        // 商户订单号
        $param_arr['out_trade_no'] = $order_info[0]['pay_sn'];
        // 微信订单号
        $param_arr['transaction_id'] = $order_info[0]['wx_pay_sn'];
        // 商户退款单号
        $param_arr['out_refund_no'] = $return['refund_sn'];
        // 总金额
        $param_arr['total_fee'] = $order_info[0]['order_amount'] * 100;
        // 退款金额
        $param_arr['refund_fee'] = $return['refund_amount'] * 100;
        // 操作员
        $param_arr['user_id'] = "1";
        if ($do_wx) {   // 执行微信退款
            // 请求微信，发起退款
            $wx_refund_result = refund($param_arr);
        } else {    // 不执行微信退款
            // 默认成功标志：成功
            $wx_refund_result['return_code'] = 'SUCCESS';
        }
        /* lyq@newland 添加结束  **/
        if ($state && $wx_refund_result['return_code'] == 'SUCCESS') {
            $this->editRefundReturn($condition, $refund_array);
            $this->admin_log('退货确认，退货编号' . $return['refund_sn']);


            // 发送买家消息
            $param = array();
            $param['code'] = 'refund_return_notice';
            $param['member_id'] = $return['buyer_id'];
            $param['param'] = array(
                'refund_url' => urlShop('return_id', 'view', array('return_id' => $return['refund_id'])),
                'refund_sn' => $return['refund_sn']
            );
            QueueClient::push('sendMemberMsg', $param);
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    
    /**
     * 记录平台系统日志
     *
     * @param $lang 日志语言包
     * @param $state 1成功0失败null不出现成功失败提示
     */
    private final function admin_log($lang = '', $state = 1){
        if (!C('sys_log') || !is_string($lang)) return;
        $data = array();
        if (is_null($state)){
                $state = null;
        }else{
                $state = $state ? '' : L('nc_fail');
        }
        $data['content'] 	= $lang.$state;
        $data['admin_name'] = 'admin';
        $data['createtime'] = TIMESTAMP;
        $data['admin_id'] 	= '1';
        $data['ip']		= getIp();
        $data['url']	= ' ';
        return Model('admin_log')->insert($data);
    }
    /* lyq@newland 添加结束   **/
}
