<?php
/**
 * 订单管理
 * @author zp@newland
 * 2017/02/06 添加
 */
defined('InShopNC') or exit('Access Invalid!');
class mst_customer_cardModel extends Model {
    public function __construct(){
        parent::__construct('mst_customer_card');
    }

    /**
     * 查询奶卡所有信息
     */
    public function queryAllItem($condition){
        return $this->where($condition)->master('nopre')->select();
    }

    /**
     * 更新奶卡信息
     */
    public function updateAction($array){
        $this->master('nopre')->update($array);
    }
}
