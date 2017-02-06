<?php
/**
 * 订单管理
 */
defined('InShopNC') or exit('Access Invalid!');
class mst_self_authorityModel extends Model {
    public function __construct(){
        parent::__construct('mst_self_authority');
    }

    public function checkExist($id){
        $condition = array(
            'authority_id' => 9,
            'self_receive_spot_cd' => $id
        );
        $this->table_prefix = '';
        $rs = $this->where($condition)->select();
        return count($rs) == 0;
    }
}
