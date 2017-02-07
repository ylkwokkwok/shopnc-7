<?php
/**
 * 订单管理
 * @author zp@newland
 * 2017/02/06 添加
 */
defined('InShopNC') or exit('Access Invalid!');
class store_self_bindModel extends Model{

    public function __construct(){
        parent::__construct('store_self_bind');
    }

    /**
     * 只有一个店铺的自取点绑定类型不为“所有”
     */
    public function find_cd($condition){
        return $this->field('self_receive_spot_cd')->where($condition)->select();
    }

    /**
     * 多店铺自取点绑定类型不为“所有”
     */
    public function query_cd($condition){
        return $this->field('self_receive_spot_cd,count(*) count')->where($condition)->group("self_receive_spot_cd")->having('count > 1')->select();
    }
}
