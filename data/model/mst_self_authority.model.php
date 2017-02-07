<?php
/**
 * 订单管理
 * @author zp@newland
 * 2017/02/06 添加
 */
defined('InShopNC') or exit('Access Invalid!');
class mst_self_authorityModel extends Model {
    public function __construct(){
        parent::__construct('mst_self_authority');
    }

    /**
     * 判断查询的结果是否符合以下规则 如果不符合从结果集中去除
     */
    public function checkExist($id){
        $condition = array(
            'authority_id' => 9,
            'self_receive_spot_cd' => $id
        );
        $rs = $this->where($condition)->master('nopre')->select();
        return count($rs) == 0;
    }
}
