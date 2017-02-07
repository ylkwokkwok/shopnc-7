<?php
/**
 * 订单管理
 * @author zp@newland
 * 2017/02/06 添加
 */
defined('InShopNC') or exit('Access Invalid!');
class mst_card_incrementModel extends Model {
    public function __construct(){
        parent::__construct('mst_card_increment');
    }

    /**
     * 查询奶卡信息
     */
    public function get_milk_card($condition){
        return $this->field('trim(concat(card_prefix,lpad(card_seq+1,6,0))) start_range,card_seq+1 card_seq')->where($condition)->master('nopre')->select();
    }
}
