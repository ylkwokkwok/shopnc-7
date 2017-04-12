<?php
/**
 * 积分管理
 * @author zly
 **/

defined('NlWxShop') or exit('Access Invalid!');
class integration_extractControl extends SystemControl{

    public function __construct(){
        parent::__construct();
        //读取语言包
        Language::read('commission,trade');
        if($GLOBALS['setting_config']['flea_isuse']!='1'){
            showMessage(Language::get('flea_isuse_off_tips'),'index.php?act=dashboard&op=welcome');
        }
    }

    /**
     * 默认Op
     */
    public function indexOp() {
        // 调用积分统计方法
        $this->integration_recordsOp();
    }

    /**
     * 积分订单列表
     */
    public function integration_recordsOp() {
        // 查询信息 
        $this->integration_data();
        // 跳转画面 
        Tpl::showpage('integration_extract.order');
    }

    /**
     * 积分订单列表 - 查询条件
     */
    public function integration_order_searchOp() {
        // 查询条件 
        $condition = array();
        // 订单编号 
        $order_sn = $_GET['order_sn'];
        $condition['order_sn'] = $order_sn;
        // 店铺名称 
        $store_name = $_GET['store_name'];
        $condition['store_name'] = $store_name;
        // 订单完成日期(开始) 
        $sdate = $_GET['sdate'];
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $sdate);
        $start_unixtime = $if_start_date ? strtotime($sdate) : null;
        $condition['sdate'] = $start_unixtime;
        // 订单完成日期(结束) 
        $edate = $_GET['edate'];
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $edate);
        $end_unixtime = $if_end_date ? strtotime($edate): null;
        $condition['edate'] = $end_unixtime;
        // 查询信息 
        $this->integration_data($condition);
        // 跳转画面 
        Tpl::showpage('integration_extract.order');
    }
    /**
     * 积分统计 - 订单详细 
     */
    public function records_detailOp() {
        $model_integratin= Model('integration');
        $model_order = Model('order');
        // 订单编号 
        $order_id = intval($_GET['gc_id']);
        if($order_id <= 0 ){
            showMessage(L('miss_order_number'));
        }
        // 提现ID
        $extract_id = intval($_GET['integration_extract_id']);
        //积分信息
        $integration_list = $model_integratin->integration_extract_list(array('extract_id'=>$extract_id));
        if(count($integration_list) == 0){
            showMessage('缺少积分资料信息！');
        }
        // 取得订单信息 
        $order_info = $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_goods','order_common','store'));
        //订单变更日志
        $log_list = $model_order->getOrderLogList(array('order_id'=>$order_info['order_id']));
        Tpl::output('order_log',$log_list);
        //退款退货信息
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['order_id'] = $order_info['order_id'];
        $condition['seller_state'] = 2;
        $condition['admin_time'] = array('gt',0);
        $return_list = $model_refund->getReturnList($condition);
        Tpl::output('return_list',$return_list);
        //退款信息
        $refund_list = $model_refund->getRefundList($condition);
        Tpl::output('refund_list',$refund_list);
        //卖家发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
            Tpl::output('daddress_info',$daddress_info);
        }
        Tpl::output('order_info',$order_info);
        Tpl::output('integration_info',$integration_list[0]);
        Tpl::showpage('integration_records.view');
    }
    /**
     * 积分订单列表 
     * @param array $condition 查询条件 
     */
    private function integration_data($condition=array()){
        $model_dp = Model('integration');
        // 分页 
        $count = $model_dp->integration_order_count($condition);
        $page = new Page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        $page->setTotalNum($count);
        $delaypage = intval($_GET['curpage'])>0?intval($_GET['curpage']):1;
        //本页延时加载的当前页数
        $lazy_arr = lazypage(10,$delaypage,$count,false,$page->getNowPage(),$page->getEachNum(),$page->getLimitStart());
        //动态列表
        $limit = $lazy_arr['limitstart'].",".$lazy_arr['delay_eachnum'];
        $condition['limit'] = $limit;
        // 取得积分订单列表  
        $list = $model_dp->integration_select_list($condition);
        Tpl::output('list', $list);
        Tpl::output('show_page',$page->show());
    }
    /**
     * 查询提现申请列表
     */
    public function extract_applyOp() {
        // 查询信息 
        $this->extract_apply_list();
        // 跳转画面 
        Tpl::showpage('integration_extract.verify');
    }
     /**
     * 积分提现审批 - 查询条件 
     */
    public function extract_searchOp() {
        // 查询条件 
        $condition = array();
        // 申请人
        $store_name = $_GET['store_name'];
        $condition['store_name'] = $store_name;
        // 申请日期(开始) 
        $esdate = $_GET['esdate'];
        $if_start_date_e = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $esdate);
        $start_unixtime_e = $if_start_date_e ? strtotime($esdate) : null;
        $condition['esdate'] = $start_unixtime_e;
        // 申请日期(结束) 
        $eedate = $_GET['eedate'];
        $if_end_date_e = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $eedate);
        $end_unixtime_e = $if_end_date_e ? strtotime($eedate): null;
        $condition['eedate'] = $end_unixtime_e;
        // 审核日期(开始) 
        $csdate = $_GET['csdate'];
        $if_start_date_c = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $csdate);
        $start_unixtime_c = $if_start_date_c ? strtotime($csdate) : null;
        $condition['csdate'] = $start_unixtime_c;
        // 审核日期(结束) 
        $cedate = $_GET['cedate'];
        $if_end_date_c = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $cedate);
        $end_unixtime_c = $if_end_date_c ? strtotime($cedate): null;
        $condition['cedate'] = $end_unixtime_c;
        // 审核状态 
        $extract_flg = $_GET['extract_flg'];
        $condition['extract_flg'] = $extract_flg;
        // 查询信息 
        $this->extract_apply_list($condition);
        // 跳转画面 
        Tpl::showpage('integration_extract.verify');
    }
    /**
     * 积分提现记录列表
     * @param type $condition 查询积分提现审批条目
     */
    private function extract_apply_list($condition=array()){
        $model_dp = Model('integration');
        // 状态判断 
        if (!isset($condition['extract_flg'])){
            $condition['extract_flg'] = -1;
        }
        // 分页 
        $count = $model_dp->integration_extract_count($condition);
        $page = new Page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        $page->setTotalNum($count);
        $delaypage = intval($_GET['curpage'])>0?intval($_GET['curpage']):1;
        //本页延时加载的当前页数
        $lazy_arr = lazypage(10,$delaypage,$count,false,$page->getNowPage(),$page->getEachNum(),$page->getLimitStart());
        //动态列表
        $limit = $lazy_arr['limitstart'].",".$lazy_arr['delay_eachnum'];
        $condition['limit'] = $limit;
        // 取得积分获取情况  
        $list = $model_dp->integration_extract_list($condition);
        // 输出显示内容 
        Tpl::output('extract_flg', $condition['extract_flg']);
        Tpl::output('list', $list);
        Tpl::output('show_page',$page->show());
        Tpl::showpage('integration_extract.verify');
    }
    /**
     * 平台审核
     */
    public function integration_verifyOp() {
        if (chksubmit()) {
            $model_dp = Model('integration');
            // 登录用户信息取得  
            $admin = unserialize(decrypt(cookie('sys_key'),MD5_KEY));
            $admin_id = $admin['id'];
            // 提取ID
            $extractids = $_POST['extractids'];
            // 备注 
            $extract_remark = $_POST['extract_remark'];
            // 操作区分 
            $extract_flg = $_POST['extract_flg'];
            // 检验取得ID是否为数字 
            $extract_array = explode(',', $extractids);
            foreach ($extract_array as $value) {
                if (!is_numeric($value)) {
                    showDialog(L('nc_common_op_fail'), 'reload');
                }
            }
            // 检验数据状态是否正确  
            // 基本条件 
            $condition = "extract_id in (".$extractids.") and del_flg=0 and extract_type=1";
            // 审核数据校验 
            try {
                $model_dp->beginTransaction();
                // 更新数据库内容 
                $update_condition = array();
                // 根据区分进行 状态判断 
                if ($extract_flg == 1){
                    // 未审核 或 审核未通过变更为审核通过
                    $update_condition['extract_flg'] = "1";
                    $update_condition['check_time'] = TIMESTAMP;
                    $update_condition['check_user'] = $admin_id;
                } else if ($extract_flg == 2){
                    // 未通过审核 或 审核通过 变更为 审核未通过
                    $update_condition['extract_flg'] = "2";
                    $update_condition['check_time'] = TIMESTAMP;
                    $update_condition['check_user'] = $admin_id;
                    $update_condition['extract_remark'] = $extract_remark;
                    $model_dp->interation_verify($extractids);
                }
                Model('extract')->where($condition)->update($update_condition);
                //提交事务
                $model_dp->commit();
                showDialog(L('nc_common_op_succ'), 'reload', 'succ');
                }catch (Exception $e){
                    //回滚事务
                    $model_dp->rollback();
                    showDialog(L('nc_common_op_fail'), 'reload', 'succ');
                }
        }
        Tpl::output('extractids', $_GET['id']);
        Tpl::showpage('integration_extract.ramark', 'null_layout');
    }
}
