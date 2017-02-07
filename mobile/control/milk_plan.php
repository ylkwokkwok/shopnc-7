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
    /* zp@newland 添加开始 **/
    /* 时间：2017/02/06 **/
    /**
     * 获取当前会员相关的客户信息
     */
    private function _get_customer_list() {
        $condition = array(
            "mst_customer.customer_type" => 0,
            "mst_customer.delete_flag" => 0,
            "mst_customer.member_id" => $this->member_info['member_id'],
        );
        $mst_customer = Model('mst_customer');
        return $mst_customer->get_customer_list($condition);
    }
    /* zp@newland 添加结束 **/
}
