<?php
/**
 * 订单管理
 */
defined('InShopNC') or exit('Access Invalid!');
class orderModel extends Model {

    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return unknown
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $order = '',$group = '') {
        $order_info = $this->table('order')->field($fields)->where($condition)->group($group)->order($order)->find();
        if (empty($order_info)) {
            return array();
        }
        if (isset($order_info['order_state'])) {
            $order_info['state_desc'] = orderState($order_info);
        }
        if (isset($order_info['payment_code'])) {
            $order_info['payment_name'] = orderPaymentName($order_info['payment_code']);
        }

        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_info['extend_order_common'] = $this->getOrderCommonInfo(array('order_id'=>$order_info['order_id']));
            $order_info['extend_order_common']['reciver_info'] = unserialize($order_info['extend_order_common']['reciver_info']);
            $order_info['extend_order_common']['invoice_info'] = unserialize($order_info['extend_order_common']['invoice_info']);
        }

        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $order_info['extend_store'] = Model('store')->getStoreInfo(array('store_id'=>$order_info['store_id']));
        }

        //返回买家信息
        if (in_array('member',$extend)) {
            $order_info['extend_member'] = Model('member')->getMemberInfoByID($order_info['buyer_id']);
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>$order_info['order_id']));
            $order_info['extend_order_goods'] = $order_goods_list;
        }

		/* zly@newland 获取提现信息 开始   * */
        /* 时间：2015/07/09                * */
        /* 功能ID：获取提现信息             * */
        // 提现信息
        if (in_array('extract', $extend)) {
            //取商品列表
            $store_extract_detail = $this->getExtractDetail(array('extract_id' => $order_info['extract_id'], 'extract_type' => 1));
            $order_info['extract_detail'] = $store_extract_detail;
        }
        /* zly@newland 获取提现信息 结束   * */
        return $order_info;
    }

	/**
     * 提现详细
     */
    public function getExtractDetail($condition = array(), $field = '*') {
        return $this->table('extract')->field($field)->where($condition)->find();
    }

    public function getOrderCommonInfo($condition = array(), $field = '*') {
        return $this->table('order_common')->where($condition)->find();
    }

    public function getOrderPayInfo($condition = array(), $master = false) {
        return $this->table('order_pay')->where($condition)->master($master)->find();
    }

    /**
     * 取得支付单列表
     *
     * @param unknown_type $condition
     * @param unknown_type $pagesize
     * @param unknown_type $filed
     * @param unknown_type $order
     * @param string $key 以哪个字段作为下标,这里一般指pay_id
     * @return unknown
     */
    public function getOrderPayList($condition, $pagesize = '', $filed = '*', $order = '', $key = '') {
        return $this->table('order_pay')->field($filed)->where($condition)->order($order)->page($pagesize)->key($key)->select();
    }

    /**
     * 取得订单列表(未被删除)
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getNormalOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array()){
        $condition['delete_state'] = 0;
        return $this->getOrderList($condition, $pagesize, $field, $order, $limit, $extend);
    }

    /**
     * 取得订单列表(所有)
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false){
        $list = $this->table('order')->field($field)->where($condition)->page($pagesize)->order($order)->limit($limit)->master($master)->select();
        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }
        	if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;

        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
            	if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = Model('store')->getStoreList(array('store_id'=>array('in',$store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
            	$store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member',$extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))));
            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {
                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods'] = array();
            }
        }

        return $order_list;
    }

    /**
     * 取得(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id   买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return array
     */
    public function getOrderCountCache($type, $id, $key) {
        if (!C('cache_open')) return array();
        $type = 'ordercount'.$type;
        $ins = Cache::getInstance('cacheredis');
        $order_info = $ins->hget($id,$type,$key);
        return !is_array($order_info) ? array($key => $order_info) : $order_info;
    }

    /**
     * 设置(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param array $data
     */
    public function editOrderCountCache($type, $id, $data) {
        if (!C('cache_open') || empty($type) || !intval($id) || !is_array($data)) return ;
        $ins = Cache::getInstance('cacheredis');
        $type = 'ordercount'.$type;
        $ins->hset($id,$type,$data);
    }

    /**
     * 取得买卖家订单数量某个缓存
     * @param string $type $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return int
     */
    public function getOrderCountByID($type, $id, $key) {
        $cache_info = $this->getOrderCountCache($type, $id, $key);
        
        if (is_string($cache_info[$key])) {
            //从缓存中取得
            $count = $cache_info[$key];
        } else {
            //从数据库中取得
            $field = $type == 'buyer' ? 'buyer_id' : 'store_id';
            $condition = array($field => $id);
            $func = 'getOrderState'.$key;
            $count = $this->$func($condition);
            $this->editOrderCountCache($type,$id,array($key => $count));
        }
        return $count;
    }

    /**
     * 删除(买/卖家)订单全部数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id   买家ID、店铺ID
     * @return bool
     */
    public function delOrderCountCache($type, $id) {
        if (!C('cache_open')) return true;
        $ins = Cache::getInstance('cacheredis');
        $type = 'ordercount'.$type;
        return $ins->hdel($id,$type);
    }

    /**
     * 待付款订单数量
     * @param unknown $condition
     */
    public function getOrderStateNewCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_NEW;
        return $this->getOrderCount($condition);
    }

    /**
     * 待发货订单数量
     * @param unknown $condition
     */
    public function getOrderStatePayCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_PAY;
        return $this->getOrderCount($condition);
    }

    /**
     * 待收货订单数量
     * @param unknown $condition
     */
    public function getOrderStateSendCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SEND;
        return $this->getOrderCount($condition);
    }

    /**
     * 待评价订单数量
     * @param unknown $condition
     */
    public function getOrderStateEvalCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['evaluation_state'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 交易中的订单数量
     * @param unknown $condition
     */
    public function getOrderStateTradeCount($condition = array()) {
        $condition['order_state'] = array(array('neq',ORDER_STATE_CANCEL),array('neq',ORDER_STATE_SUCCESS),'and');
        return $this->getOrderCount($condition);
    }

    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderCount($condition) {
        return $this->table('order')->where($condition)->count();
    }

    /**
     * 取得订单商品表详细信息
     * @param unknown $condition
     * @param string $fields
     * @param string $order
     */
    public function getOrderGoodsInfo($condition = array(), $fields = '*', $order = '') {
        return $this->table('order_goods')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得订单商品表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     * @param string $page
     * @param string $order
     * @param string $group
     * @param string $key
     */
    public function getOrderGoodsList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'rec_id desc', $group = null, $key = null) {
        return $this->table('order_goods')->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->key($key)->page($page)->select();
    }

    /**
     * 取得订单扩展表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     */
    public function getOrderCommonList($condition = array(), $fields = '*', $order = '', $limit = null) {
        return $this->table('order_common')->field($fields)->where($condition)->order($order)->limit($limit)->select();
    }

    /**
     * 插入订单支付表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderPay($data) {
        return $this->table('order_pay')->insert($data);
    }

    /**
     * 插入订单表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrder($data) {
        $insert = $this->table('order')->insert($data);
        if ($insert) {
            //更新缓存
            QueueClient::push('delOrderCountCache',array('buyer_id'=>$data['buyer_id'],'store_id'=>$data['store_id']));
        }
        return $insert;
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderCommon($data) {
        return $this->table('order_common')->insert($data);
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderGoods($data) {
        return $this->table('order_goods')->insertAll($data);
    }

	/**
	 * 添加订单日志
	 */
	public function addOrderLog($data) {
	    $data['log_role'] = str_replace(array('buyer','seller','system','admin'),array('买家','商家','系统','管理员'), $data['log_role']);
	    $data['log_time'] = TIMESTAMP;
	    return $this->table('order_log')->insert($data);
	}

	/**
	 * 更改订单信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrder($data,$condition,$limit = '') {
		$update = $this->table('order')->where($condition)->limit($limit)->update($data);
		if ($update) {
		    //更新缓存
		    QueueClient::push('delOrderCountCache',$condition);
		}
		return $update;
	}

	/**
	 * 更改订单信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrderCommon($data,$condition) {
	    return $this->table('order_common')->where($condition)->update($data);
	}

	/**
	 * 更改订单信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrderGoods($data,$condition) {
	    return $this->table('order_goods')->where($condition)->update($data);
	}

	/**
	 * 更改订单支付信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrderPay($data,$condition) {
		return $this->table('order_pay')->where($condition)->update($data);
	}

	/**
	 * 订单操作历史列表
	 * @param unknown $order_id
	 * @return Ambigous <multitype:, unknown>
	 */
    public function getOrderLogList($condition) {
        return $this->table('order_log')->where($condition)->select();
    }

    /**
     * 取得单条订单操作记录
     * @param unknown $condition
     * @param string $order
     */
    public function getOrderLogInfo($condition = array(), $order = '') {
        return $this->table('order_log')->where($condition)->order($order)->find();
    }

    /**
     * 返回是否允许某些操作
     * @param unknown $operate
     * @param unknown $order_info
     */
    public function getOrderOperateState($operate,$order_info){

        if (!is_array($order_info) || empty($order_info)) return false;

        switch ($operate) {

            //买家取消订单
        	case 'buyer_cancel':
        	   $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
        	       ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
        	   break;

    	   //申请退款
    	   case 'refund_cancel':
    	       $state = $order_info['refund'] == 1 && !intval($order_info['lock_state']);
    	       break;

    	   //商家取消订单
    	   case 'store_cancel':
    	       $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
    	       ($order_info['payment_code'] == 'offline' &&
    	       in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND)));
    	       break;

           //平台取消订单
           case 'system_cancel':
               $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
               ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
               break;

           //平台收款
           case 'system_receive_pay':
               $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
               break;

	       //买家投诉
	       case 'complain':
	           $state = in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND)) ||
	               intval($order_info['finnshed_time']) > (TIMESTAMP - C('complain_time_limit'));
	           break;

	       case 'payment':
	           $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
	           break;

            //调整运费
        	case 'modify_price':
        	    $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
        	       ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
        	    $state = floatval($order_info['shipping_fee']) > 0 && $state;
        	   break;
	    //调整商品价格
        	case 'spay_price':
        	    $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
        	       ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
				   $state = floatval($order_info['goods_amount']) > 0 && $state;
        	   break;

        	//发货
        	case 'send':
        	    $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_PAY;
        	    break;

        	//收货
    	    case 'receive':
    	        $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_SEND;
    	        break;

    	    //评价
    	    case 'evaluation':
    	        $state = !$order_info['lock_state'] && !$order_info['evaluation_state'] && $order_info['order_state'] == ORDER_STATE_SUCCESS;
    	        break;

        	//锁定
        	case 'lock':
        	    $state = intval($order_info['lock_state']) ? true : false;
        	    break;

        	//快递跟踪
        	case 'deliver':
        	    $state = !empty($order_info['shipping_code']) && in_array($order_info['order_state'],array(ORDER_STATE_SEND,ORDER_STATE_SUCCESS));
        	    break;

        	//放入回收站
        	case 'delete':
        	    $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL,ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 0;
        	    break;

        	//永久删除、从回收站还原
        	case 'drop':
        	case 'restore':
        	    $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL,ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 1;
        	    break;

        	//分享
        	case 'share':
        	    $state = true;
        	    break;


        }
        return $state;

    }
    
    /**
     * 联查订单表订单商品表
     *
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     * @return array
     */
    public function getOrderAndOrderGoodsList($condition, $field = '*', $page = 0, $order = 'rec_id desc') {
        return $this->table('order_goods,order')->join('inner')->on('order_goods.order_id=order.order_id')->where($condition)->field($field)->page($page)->order($order)->select();
    }
    
    /**
     * 订单销售记录 订单状态为20、30、40时
     * @param unknown $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getOrderAndOrderGoodsSalesRecordList($condition, $field="*", $page = 0, $order = 'rec_id desc') {
        $condition['order_state'] = array('in', array(ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
        return $this->getOrderAndOrderGoodsList($condition, $field, $page, $order);
    }

	/* zly@newland查询待支付、待收货数目开始* */
    /* 时间：2015/07/24            * */
    public function not_pay_num($unreceived = '', $field = '*') {
        // 条件：待支付
        $unreceived['order_state'] = array('in', 10);
        $list_num = $this->table('order')->field($field)->where($unreceived)->select();
        return count($list_num);
    }

    public function not_received_num($unreceived = '', $field = '*') {
        // 条件：待收货
        $unreceived['order_state'] = array('in', 30);
        $unreceived['lock_state'] = array('in', 0);
        $list_num = $this->table('order')->field($field)->where($unreceived)->select();
        return count($list_num);
    }

    /* zz@newland 添加开始 * */
    /* 时间：2016/03/3     * */

    //查询会员待评价商品数量功能
    public function not_valuate_num($unreceived = '', $field = '*') {
        // 条件：待评价
        $unreceived['order_state'] = array('in', 40);
        $list_num = $this->table('order')->field($field)->where($unreceived)->select();
        return count($list_num);
    }

    /* zz@newland 修改结束 * */
    /* zly@newland查询待支付、待收货数目结束* */

    /* lyq@newland 添加开始 * */
    /* 时间：2015/11/02     * */

    /**
     * 获取用户已购买某商品的总数量
     * @param type $goods_id 商品ID
     * @param type $buyer_id 买家ID(会员ID)
     */
    public function getProductBuyedNum($goods_id, $buyer_id) {
        $sql = 'SELECT ';
        $sql.= '        SUM(goods_num) buyed_num ';
        $sql.= 'FROM ';
        $sql.= '        `' . DBPRE . 'order_goods` ';
        $sql.= 'LEFT JOIN `' . DBPRE . 'order` ON ' . DBPRE . 'order.order_id = ' . DBPRE . 'order_goods.order_id ';
        $sql.= 'WHERE ';
        $sql.= '        ' . DBPRE . 'order_goods.goods_id = "' . $goods_id . '"';
        $sql.= 'AND ' . DBPRE . 'order_goods.buyer_id = "' . $buyer_id . '" ';
        $sql.= 'AND ';
        $sql.= '( ';
        $sql.= '    ( ';
        $sql.= '        ' . DBPRE . 'order.wx_pay_sn = "" ';
        $sql.= '        AND ' . DBPRE . 'order.order_state != 0 ';
        $sql.= '    ) || (' . DBPRE . 'order.wx_pay_sn != "") ';
        $sql.= ') ';
        $result = $this->query($sql);
        return count($result) ? intval($result[0]['buyed_num']) : 0;
    }

    /**
     * 获取会员Openid
     * jys 2015/12/24
     * @param type $order_id
     * @return type
     * 
     */
    public function getMemberId($order_id) {
        $condition = array(
            'order.order_id' => $order_id
        );
        $model = Model();
        $result = $model->table('order,member,order_common,express')
                ->join('inner,inner,inner')
                ->on('order.buyer_id = member.member_id,order.order_id = order_common.order_id,order_common.shipping_express_id = express.id')
                ->field('member`.member_wx_id as member_wx_id,express`.e_name as e_name')
                ->where($condition)
                ->select();
        return $result;
    }

    /* lyq@newland 添加结束 * */

    /* zp@newland 添加开始 **/
    /* 时间：2017/02/06 **/
    /* 订单信息 */
    public function getWXOrderInfo($condition){
        $model = Model();
        return $model->table('order,order_goods')
                        ->left('left')
                        ->on('order.order_id = order_goods.order_id')
                        ->field('sum(order.order_amount) pay_amount,count(*) goods_num,order_goods.goods_name')
                        ->where($condition)
                        ->select();
    }

    /**
     * 订单信息
     */
    public function getSNOrderInfo($condition){
        return $this->field('sum(order_amount) pay_amount')->where($condition)->select();
    }
    /* zp@newland 添加结束 **/
}
