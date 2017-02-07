<?php
/**
 * 订单管理
 * @author zp@newland
 * 2017/02/06 添加
 */
defined('InShopNC') or exit('Access Invalid!');
class trn_milk_orderModel extends Model {
    public function __construct(){
        parent::__construct('trn_milk_order');
    }

    /**
     * 插入订单表数据
     */
    public function insertAction($order){
        $array = array(
            'customer_cd' => $order['customer_cd'],
            'gc_id' => $order['gc_id'],
            'goods_id' => $order['goods_id'],
            'card_type' => $order['card_type'],
            'milk_card_cd_start' => $order['milk_card_cd_start'],
            'order_from_flag' => $order['order_from_flag'],
            'purchase_date' => $order['purchase_date'],
            'create_user' => $order['create_user'],
            'create_date' => $order['create_date'],
            'update_user' => $order['update_user'],
            'update_date' => $order['update_date'],
        );
        $this->master('nopre')->insert($array);
    }
}
