<?php
/**
 * 订单管理
 * @author zp@newland
 * 2017/02/06 添加
 */
defined('InShopNC') or exit('Access Invalid!');
class order_goodsModel extends Model {
    public function __construct(){
        parent::__construct('order_goods');
    }

    /**
     * 检验限购次数
     */
    public function limit_filter($condition){
        $model = Model();
        return $model->table('order_goods,order,goods')
                ->join('inner,inner')
                ->on('order_goods.order_id =order.order_id,order_goods.goods_id = goods.goods_id')
                ->field('order_goods.goods_id,order_goods.goods_name,order_goods.goods_num,goods.purchase_limit')
                ->where($condition)
                ->select();
    }
}
