<?php
/**
 * 订单管理
 * @author zp@newland
 * 2017/02/06 添加
 */
defined('InShopNC') or exit('Access Invalid!');
class mst_customerModel extends Model {
    public function __construct(){
        parent::__construct('mst_customer');
    }
    /**
     * 获取当前会员相关的客户信息
     */
    public function get_customer_list($condition){
        $model = Model();
        $rs = $model->table('mst_customer,mst_self_receive')
                        ->join('left')
                        ->on('mst_self_receive.self_receive_spot_cd = mst_customer.self_receive_spot_cd_bak')
                        ->field('mst_customer.customer_cd,mst_customer.customer_name,mst_customer.address c_address,mst_customer.tel c_tel,mst_self_receive.self_receive_spot_cd,mst_self_receive.self_receive_nm,mst_self_receive.address sr_address,mst_self_receive.tel sr_tel')
                        ->where($condition)
                        ->order('mst_customer.create_date DESC')
                        ->master('nopre')
                        ->select();
        return $rs;
    }

    /**
     * 反序列化订奶记录数据，获得订单信息
     */
    public function get_milk_order_info($condition){
        return $this->field('customer_cd')->where($condition)->order('customer_cd DESC')->limit('1')->master('nopre')->select();
    }
}
