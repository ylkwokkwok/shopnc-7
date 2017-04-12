<?php
/**
 * 结算模型
 *
 
 */
defined('NlWxShop') or exit('Access Invalid!');

//以下是定义结算单状态
//默认
define('BILL_STATE_CREATE',1);
//店铺已确认
define('BILL_STATE_STORE_COFIRM',2);
//平台已审核
define('BILL_STATE_SYSTEM_CHECK',3);
//结算完成
define('BILL_STATE_SUCCESS',4);

class self_bill_totalModel extends Model {
        public function totalOrder($contion,$count,$page=10){
            $field='self_receive.self_receive_nm AS self_cd,order_goods.goods_name,SUM(order_goods.goods_num) AS num ';
            $on = '`order`.order_id = order_goods.order_id,`order`.self_receive_spot_cd = self_receive.self_receive_spot_cd AND self_receive.delete_flag = 0 ';
            $group = ' `order`.self_receive_spot_cd,order_goods.goods_name';
            $order = ' `order`.self_receive_spot_cd,`order`.order_id desc ';
            return  $this->table('order,order_goods,self_receive')->field($field)->join('inner,inner')->on($on)->where($contion)->group($group)->page($page,$count)->order($order)->select();
        }
          /**
          * 取一览总数数量
          * @param unknown $condition
          */
        public function getCount($condition) {
              $sql= 'select count(*) as orderNum from (' ; 
              $sql.= 'SELECT  `order`.order_id  FROM '.DBPRE.'order  AS `order` ';
              $sql.=' INNER JOIN  '.DBPRE.'order_goods  AS `order_goods` ON  `order`.order_id = order_goods.order_id';
              $sql.=' INNER JOIN '.DBPRE.'self_receive  AS `self_receive` ON `order`.self_receive_spot_cd = self_receive.self_receive_spot_cd';
              $sql.=' AND self_receive.delete_flag = 0';
              $sql.=' where '.$condition.'';
              $sql.= ' GROUP BY `order`.self_receive_spot_cd,order_goods.goods_name';
              $sql.=' ) as tb ';
              $result =  Model()->query($sql);
              return $result;
         }
           /**
          * 取销售数量
          * @param unknown $condition
          */
         public function getSallOrderCount($condition) {
              $field = '*';
              $on = '`order`.order_id = order_goods.order_id,`order`.self_receive_spot_cd = self_receive.self_receive_spot_cd AND self_receive.delete_flag = 0 ';
              $group = ' `order`.self_receive_spot_cd,order_goods.goods_name';
              $order = ' `order`.self_receive_spot_cd,`order`.order_id desc ';
              return  $this->table('order,order_goods,self_receive')->field($field)->join('inner,inner')->on($on)->where($condition)->count();
         }

          public function getSallOrderList($condition,$limit, $master = false){
              $field='self_receive.self_receive_nm,order_goods.goods_name,order_goods.goods_num,FROM_UNIXTIME( add_time, "%Y-%m-%d" ) as add_time,order_common.reciver_name';
              $on = '`order`.order_id = order_goods.order_id,`order`.self_receive_spot_cd = self_receive.self_receive_spot_cd AND self_receive.delete_flag = 0,`order`.order_id = order_common.order_id ';
              $group = ' `order`.self_receive_spot_cd,order_goods.goods_name';
         $order = ' `order`.self_receive_spot_cd,`order`.order_id desc ';
         return  $this->table('order,order_goods,self_receive,order_common')->field($field)->join('inner,inner,inner')->on($on)->where($condition)->order($order)->limit($limit)->master($master)->select();
     }
     //到户统计
      public function totaltohomeOrder($contion,$count,$page=10){
            $field=' order_goods.goods_name,SUM(order_goods.goods_num) AS num ';
            $on = '`order`.order_id = order_goods.order_id ';
            $group = ' order_goods.goods_name';
            $order = '`order`.order_id desc ';
            return  $this->table('order,order_goods')->field($field)->join('inner')->on($on)->where($contion)->group($group)->page($page,$count)->order($order)->select();
        }
        
         /**
          * 取一览总数数量
          * @param unknown $condition
          */
        public function getTohomeCount($condition) {
              $sql= 'select count(*) as orderNum from (' ; 
              $sql.= 'SELECT  `order`.order_id  FROM '.DBPRE.'order  AS `order` ';
              $sql.=' INNER JOIN  '.DBPRE.'order_goods  AS `order_goods` ON  `order`.order_id = order_goods.order_id';
              $sql.=' where '.$condition.'';
              $sql.= ' GROUP BY order_goods.goods_name';
              $sql.=' ) as tb ';
              $result =  Model()->query($sql);
              return $result;
         }
           /**
          * 取销售数量
          * @param unknown $condition
          */
         public function getSallTohomeOrderCount($condition) {
              $field = '*';
              $on = '`order`.order_id = order_goods.order_id ';
              return  $this->table('order,order_goods ')->field($field)->join('inner')->on($on)->where($condition)->count();
         }

          public function getSallTohomeOrderList($condition,$limit, $master = false){
              $field='order_goods.goods_name,order_goods.goods_num,FROM_UNIXTIME( add_time, "%Y-%m-%d" ) as add_time,order_common.reciver_name';
              $on = '`order`.order_id = order_goods.order_id, `order`.order_id = order_common.order_id ';
              $order = ' `order`.order_id desc ';
         return  $this->table('order,order_goods,order_common')->field($field)->join('inner,inner')->on($on)->where($condition)->order($order)->limit($limit)->master($master)->select();
     }
}