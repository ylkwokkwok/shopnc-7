<?php
/**
 * 奶站相关操作 
 *
 * master 为必须填写项目   传递 milk  连接奶站信息
 * 
 * @author xiashihui@newlandsystem
 */
defined('NlWxShop') or exit('Access Invalid!');
class milkModel extends Model {

    /**
     * 取得奶站信息
     * 
     * 根据订单编号 取得奶站信息
     * 
     * @param string $order_sn 订单编号 
     * 
     * @return array 奶站信息 
     */
    public function getStationName($order_sn) {
//        // 表关联条件 
//        $on = 'mst_customer.milk_station_no = td_stationinformation.O_Number,trn_customer_order.customer_cd = mst_customer.customer_cd';
//        // 查询条件 
//        $where = "trn_customer_order.order_sn = '".$order_sn."'";
//        // 查询 
//        $result = $this->table('td_stationinformation,mst_customer,trn_customer_order')->field("td_stationinformation.*,mst_customer.customer_cd")
//                ->join('inner,inner')->on($on)
//                ->where($where)->master("milk")->find();
        // 奶站订单 
        $trn_customer_order = $this->table('trn_customer_order')->where(array('order_sn'=>$order_sn))->master("milk")->find();
        if(count($trn_customer_order) == 0){
            return array();
        }
        // 奶站顾客信息表 
        $mst_customer = $this->table('mst_customer')->where(array('customer_cd'=>$trn_customer_order['customer_cd']))->master("milk")->find();
        if(count($mst_customer) == 0){
            return array();
        }
        // 奶站信息表 
        $td_stationinformation = $this->table('td_stationinformation')->where(array('O_Number'=>$mst_customer['milk_station_no']))->master("milk")->find();
//        echo '<pre>';
//        var_dump($td_stationinformation);
        if(count($td_stationinformation) == 0){
            return array();
        }
        // 返回值 
        $result = $td_stationinformation;
        $result['customer_cd'] = $mst_customer['customer_cd'];
        // 返回 
        return $result;
    }

    /**
     * 取得
     */
    public function updateTest($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null) {
        $ddd = $this->table('mst_constant')->master("milk")->find();
        return $this->table('mst_constant')->master("milk")->where(array('customer_id'=>$ddd['customer_id']))->update($ddd);
    }
    
    /**
     * yzp@newland 2016/03/10 修改添加
     * 奶站查询
     */
    public function selectstationName(){
        $data = array();
        $stationname = $this->table('td_stationinformation')->page('1000')->master('milk')->select();
      
        foreach ((array) $stationname as $a){
            $data['id'][$a['O_ID']] = $a['O_ID'];
            $data['o_number'][$a['O_ID']] = $a['O_Number'];
            $data['o_name'][$a['O_ID']] = $a['O_StationName'];
            
         }
        return $data;
    }

    /**
     * 取得奶站列表 
     * 
     * @param unknown $condition 查询条件 
     * @param string $pagesize 每页数 
     * @param string $fields 显示字段 
     * @param string $order 排序 
     * 
     * @return array 奶站信息 
     */
    public function getStationList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null) {
        return $this->table('td_stationinformation')->where($condition)->field($fields)->order($order)->limit($limit)->master("milk")->page($pagesize)->select();
    }

    /**
     * 取得奶站信息
     * 
     * @param unknown $condition 查询条件 
     * @param string $pagesize 每页数 
     * @param string $fields 显示字段 
     * @param string $order 排序 
     * 
     * @return array 奶站信息 
     */
    public function getStationInfo($condition = array(), $fields = '*') {
        return $this->table('td_stationinformation')->where($condition)->field($fields)->master("milk")->find();
    }

    /**
     * 取得奶站列表 
     * 
     * @param unknown $condition 查询条件 
     * @param string $pagesize 每页数 
     * @param string $fields 显示字段 
     * @param string $order 排序 
     * 
     * @return array 奶站信息 
     */
    public function getStationCount($condition = array()) {
        return $this->table('td_stationinformation')->where($condition)->master("milk")->count();
    }
}
