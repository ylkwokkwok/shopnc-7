<?php
/**
 * 积分管理
 * 
 */
defined('NlWxShop') or exit('Access Invalid!');
class integrationModel extends Model {

    public function __construct(){
        parent::__construct('commission');
    }
    /**
     * 查询积分订单
     * @param string $condition 查询条件
     * @return array 积分订单
     */
    public function integration_select_list($condition) {
        $field = '';
        $field .= ' SELECT so.*, sc.*,';
        $field .= ' sm.member_id,sm.member_name';
        $field .= ' FROM '.DBPRE.'order sc';
        $field .= ' LEFT JOIN '.DBPRE.'extract so ON sc.extract_id = so.extract_id';
        $field .= ' LEFT JOIN '.DBPRE.'member sm ON sm.member_id = sc.buyer_id';
        $field .= ' WHERE';
        $field .= " sc.order_state = 40";
        $field .= " AND sc.refund_state = 0";
        $field .= " AND sc.extend_points > 0";
        // 分页信息 
        $limit = $condition['limit'];
        // 店铺名称 
        $store_name = $condition['store_name'];
        // 订单编号 
        $order_sn = $condition['order_sn'];
        // 订单完成日期(开始) 
        $sdate = $condition['sdate'];
        // 订单完成日期(结束) 
        $edate = $condition['edate'];
        // 查询条件 
        // 店铺名称 
        if(trim($store_name)){
            $field .= " AND sc.store_name like '%".$store_name."%'";
        }
        // 订单编号 
        if(trim($order_sn)){
            $field .= " AND sc.order_sn like '%".$order_sn."%'";
        }
        // 订单完成日期 
        if(trim($sdate) && trim($edate)){
            $field .= " AND sc.finnshed_time BETWEEN ".$sdate." AND ".($edate + 86400 -1)."";
        }elseif (trim($sdate)){
            $field .= " AND sc.finnshed_time > ".$sdate;
        }elseif (trim($edate)){
            $field .= " AND sc.finnshed_time < ".($edate + 86400 -1);
        }
        // 排序
        $field .= " order by sc.finnshed_time desc";
        // 分页信息  
        if(trim($limit)){
            $field .= ' limit '.$limit;
        }
        // 取得积分获取信息列表信息 
        $list = $this->query($field);
        if (empty($list)) return array();
        return $list;
    }
    /**
     * 查询提现记录
     * @param type $condition 查询条件
     * @return type 提现记录列表
     */
    public function integration_extract_list($condition=array()){
        $field = '';
        $field .= ' SELECT se.*,sm.store_name,sa.admin_name';
        $field .= ' FROM '.DBPRE.'extract se';
        $field .= ' LEFT JOIN '.DBPRE.'store sm ON se.extract_user= sm.store_id';
        $field .= ' LEFT JOIN '.DBPRE.'admin sa ON se.check_user = sa.admin_id';
        $field .= ' WHERE se.del_flg = 0';
        $field .= ' and se.extract_type = 1';
        // 分页信息 
        $limit = $condition['limit'];
         // 提现ID
        $extract_id = $condition['extract_id'];
        // 申请人
        $store_name = $condition['store_name'];
        // 申请日期(开始) 
        $esdate = $condition['esdate'];
        // 申请日期(结束) 
        $eedate = $condition['eedate'];
        // 审核日期(开始) 
        $csdate = $condition['csdate'];
        // 审核日期(结束) 
        $cedate = $condition['cedate'];
        // 审核状态 
        $extract_flg = $condition['extract_flg'];
        // 查询条件 
        // 积分ID 
        if(trim($extract_id)){
            $field .= " AND se.extract_id = $extract_id";
        }
        // 申请店铺
        if(trim($store_name)){
            $field .= " AND sm.store_name like '%".$store_name."%'";
        }
        // 申请日期 
        if(trim($esdate) && trim($eedate)){
            $field .= " AND se.extract_date BETWEEN ".$esdate." AND ".($eedate + 86400 -1)."";
        }elseif (trim($esdate)){
            $field .= " AND se.extract_date > ".$esdate;
        }elseif (trim($eedate)){
            $field .= " AND se.extract_date < ".($eedate + 86400 -1);
        }
        // 审核日期 
        if(trim($csdate) && trim($cedate)){
            $field .= " AND se.check_time BETWEEN ".$csdate." AND ".($cedate + 86400 -1)."";
        }elseif (trim($csdate)){
            $field .= " AND se.check_time > ".$csdate;
        }elseif (trim($cedate)){
            $field .= " AND se.check_time < ".($cedate + 86400 -1);
        }
        // 审核状态 
        if($extract_flg != NULL && $extract_flg != -1){
            $field .= " AND se.extract_flg = '".$extract_flg."'";
        }
        // 排序
        $field .= " order by se.extract_date desc,se.extract_flg desc,se.extract_id";
        // 分页信息  
        if(trim($limit)){
            $field .= ' limit '.$limit;
        }
        // 取得积分获取信息列表信息 
        $list = $this->query($field);
        if (empty($list)) return array();
        return $list;
    }
    /**
     * 提现列表总数 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function integration_extract_count($condition, $master = false) {
        $field = '';
        $field .= '  SELECT count(*) extract_count';
        $field .= ' FROM '.DBPRE.'extract se';
        $field .= ' LEFT JOIN '.DBPRE.'store sm ON se.extract_user= sm.store_id';
        $field .= ' LEFT JOIN '.DBPRE.'admin sa ON se.check_user = sa.admin_id';
        $field .= ' WHERE se.del_flg = 0';
        $field .= ' and se.extract_type = 1';
        // 申请店铺
        $store_name = $condition['store_name'];
        // 申请日期(开始) 
        $esdate = $condition['esdate'];
        // 申请日期(结束) 
        $eedate = $condition['eedate'];
        // 审核日期(开始) 
        $csdate = $condition['csdate'];
        // 审核日期(结束) 
        $cedate = $condition['cedate'];
        // 审核状态 
        $extract_flg = $condition['extract_flg'];
        // 查询条件 
        // 申请人 
        if(trim($store_name)){
            $field .= " AND sm.store_name like '%".$store_name."%'";
        }
        // 申请日期 
        if(trim($esdate) && trim($eedate)){
            $field .= " AND se.extract_date BETWEEN ".$esdate." AND ".($eedate + 86400 -1)."";
        }elseif (trim($esdate)){
            $field .= " AND se.extract_date > ".$esdate;
        }elseif (trim($eedate)){
            $field .= " AND se.extract_date < ".($eedate + 86400 -1);
        }
        // 审核日期 
        if(trim($csdate) && trim($cedate)){
            $field .= " AND se.check_time BETWEEN ".$csdate." AND ".($cedate + 86400 -1)."";
        }elseif (trim($csdate)){
            $field .= " AND se.check_time > ".$csdate;
        }elseif (trim($cedate)){
            $field .= " AND se.check_time < ".($cedate + 86400 -1);
        }
        // 审核状态 
        if($extract_flg != -1){
            $field .= " AND se.extract_flg = '".$extract_flg."'";
        }
        // 取得积分获取信息列表信息 
        $list = $this->query($field);
        if (empty($list)) return 0;
        return $list[0]['extract_count'];
    }
     /**
     * 积分订单数据条数
     * @param string $condition
     * @return array
     */
    public function integration_order_count($condition) {
        $field = '';
        $field .= ' SELECT count(*) as order_count';
        $field .= ' FROM '.DBPRE.'order sc';
        $field .= ' LEFT JOIN '.DBPRE.'extract so ON sc.extract_id = so.extract_id';
        $field .= ' LEFT JOIN '.DBPRE.'member sm ON sm.member_id = so.extract_user';
        $field .= ' WHERE';
        $field .= " sc.order_state = 40";
        $field .= " AND sc.refund_state = 0";
        $field .= " AND sc.extend_points > 0";
        // 店铺名称 
        $store_name = $condition['store_name'];
        // 订单编号 
        $order_sn = $condition['order_sn'];
        // 订单完成日期(开始) 
        $sdate = $condition['sdate'];
        // 订单完成日期(结束) 
        $edate = $condition['edate'];
        // 查询条件 
        // 店铺名称 
        if(trim($store_name)){
            $field .= " AND sc.store_name like '%".$store_name."%'";
        }
        // 订单编号 
        if(trim($order_sn)){
            $field .= " AND sc.order_sn like '%".$order_sn."%'";
        }
        // 订单完成日期 
        if(trim($sdate) && trim($edate)){
            $field .= " AND sc.finnshed_time BETWEEN ".$sdate." AND ".($edate + 86400 -1)."";
        }elseif (trim($sdate)){
            $field .= " AND sc.finnshed_time > ".$sdate;
        }elseif (trim($edate)){
            $field .= " AND sc.finnshed_time < ".($edate + 86400 -1);
        }
        // 取得积分获取信息列表信息 
        $list = $this->query($field);
        if (empty($list)) return 0;
        return $list[0]['order_count'];
    }
    /**
     * 平台审核不通过则删除相应extract_id
     *  @param string $extractids 条件：相应extract_id
     * @return array bool
     */
    public function interation_verify($extractids = array()){
        // 设置相应extract_id位空
        $data = array(
            'extract_id' => NULL
        );
        // 条件：相应extract_id
        $where['extract_id'] = array('in',$extractids);
        return $this->table('order')->where($where)->update($data);
    }
}
