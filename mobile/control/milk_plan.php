<?php

defined('NlWxShop') or exit('Access Invalid!');

class milk_planControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 客户信息列表
     */
    public function customer_listOp() {
        // 获取客户信息
        $customer_list = $this->_get_customer_list();
        
        $output_list = empty($customer_list)?array():$customer_list;
        
        output_data(array('customer_list' => $output_list));
    }
    
    /**
     * 获取当前会员相关的客户信息
     * @return type
     */
    private function _get_customer_list() {
        $sql = 'SELECT ';
        $sql.= '    t1.customer_cd, ';          // 客户编号
        $sql.= '    t1.customer_name, ';        // 客户姓名
        $sql.= '    t1.address c_address, ';    // 客户地址
        $sql.= '    t1.tel c_tel, ';            // 客户电话
        $sql.= '    t1.self_receive_spot_cd, '; // 自取点编号
        $sql.= '    t2.self_receive_nm, ';      // 自取点名称
        $sql.= '    t2.address sr_address, ';   // 自取点地址
        $sql.= '    t2.tel sr_tel ';            // 自取点电话
        $sql.= 'FROM ';
        $sql.= '    `mst_customer` t1 ';
        $sql.= 'LEFT JOIN `mst_self_receive` t2 ON t2.self_receive_spot_cd = t1.self_receive_spot_cd ';
        $sql.= 'WHERE ';
        $sql.= '    t1.member_id = "'.$this->member_info['member_id'].'" '; // 会员ID
        $sql.= 'AND t1.customer_type = "0" ';   // 客户区分 自取
        $sql.= 'AND t1.delete_flag = "0" ';     // 未删除
        $sql.= 'ORDER BY ';
        $sql.= '    t1.create_date DESC ';
        return Model()->query($sql);
    }
}
