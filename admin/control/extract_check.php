<?php
/**
 * 提现审批 
 *
 *
 *
 ***/

defined('NlWxShop') or exit('Access Invalid!');
class extract_checkControl extends SystemControl{

    public function __construct(){
        parent::__construct();
        //读取语言包
        Language::read('extract_check');
        Language::read('trade');
        if($GLOBALS['setting_config']['flea_isuse']!='1'){
            showMessage(Language::get('flea_isuse_off_tips'),'index.php?act=dashboard&op=welcome');
        }

    }

    /**
     * 默认Op
     */
    public function indexOp() {
        // 查询信息 
        $this->searchPageData();
        // 跳转画面 
        Tpl::showpage('extract_check.index');
    }

    /**
     * 查询内容信息 
     */
    public function searchOp() {
        // 查询条件 
        $condition = array();
        // 申请人
        $extract_user = $_GET['extract_user'];
        $condition['extract_user'] = $extract_user;
        // 申请日期(开始) 
        $esdate = strtotime($_GET['esdate']);
        $condition['esdate'] = $esdate;
        // 申请日期(结束) 
        $eedate = strtotime($_GET['eedate']);
        $condition['eedate'] = $eedate;
        // 审核日期(开始) 
        $csdate = strtotime($_GET['csdate']);
        $condition['csdate'] = $csdate;
        // 审核日期(结束) 
        $cedate = strtotime($_GET['cedate']);
        $condition['cedate'] = $cedate;
        // 审核状态 
        $extract_flg = $_GET['extract_flg'];
        $condition['extract_flg'] = $extract_flg;
        // 查询信息 
        $this->searchPageData($condition);
        
        // 跳转画面 
        Tpl::showpage('extract_check.index');
    }
    
    /**
     * 查询数据信息 
     * 
     * @param array $condition 查询条件 
     * 
     */
    private function searchPageData($condition=array()){ 
        $model_dp = Model('extract');
        // 状态判断 
        if (!isset($condition['extract_flg'])){
            $condition['extract_flg'] = -1;
        }
        // 分页 
        $count = $model_dp->getExtractPageCount($condition);
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
        // 取得佣金获取情况  
        $list = $model_dp->getExtractPageList($condition);
        // 输出显示内容 
        Tpl::output('extract_flg', $condition['extract_flg']);
        Tpl::output('list', $list);
        Tpl::output('show_page',$page->show());
    }
    
    
    
    /**
     * 数据信息操作 
     * 
     * $extract_type 提交分类  1 -> 审核  2 -> 取消审核  3 -> 弃审 
     * $submit_flg 提交方式(post)  num -> 表单提交  其他 -> ajax提交 
     */
    public function doCheckOp(){ 
        $model_dp = Model('extract');
        // 登录用户信息取得  
        $admin = unserialize(decrypt(cookie('sys_key'),MD5_KEY));
        $admin_id = $admin['id'];
        // 查询条件 
        $condition1 = "";
        $condition2 = "";
        // 操作区分 
        $extract_type = $_POST['extract_type'];
        // 提交方式 
        $submit_flg = $_POST['submit_flg'];
        // 提取ID 
        $extract_id = $_POST['extract_id'];
        if($submit_flg == "num"){
            $extract_id = $_POST['check_e_id'];
        }
        // 备注 
        $extract_remark = $_POST['extract_remark'];
        // 根据区分进行  
        if (is_array($extract_id)){
            $condition1 = "extract_id in (".implode(",", $extract_id).") and del_flg=0";
        }else{
            $condition1 = "extract_id = '".$extract_id."' and del_flg=0";
        }
        if ($extract_type == 1){
            // 未审核变更为审核通过 0 -> 1
            $condition2 .= " and extract_flg != 0";
        } else if ($extract_type == 2){
            // 审核通过变更为未审核 1 -> 0
            $condition2 .= " and extract_flg != 1";
        } else if ($extract_type == 3){
            // 未审核或者审核通过变更为审核失败 0/1 -> 2
            $condition2 .= " and extract_flg not in (0, 1)";
        }
        // 取得数据信息 
        $data = $model_dp->getExtractList($condition1.$condition2);
        // 审核数据校验 
        if(count($data) == 0){
            // 更新数据库内容 
            $update_condition = array();
            // 根据区分进行 状态判断 
            if ($extract_type == 1){
                // 未审核变更为审核通过 0 -> 1
                $update_condition['extract_flg'] = "1";
                $update_condition['check_time'] = TIMESTAMP;
                $update_condition['check_user'] = $admin_id;
            } else if ($extract_type == 2){
                // 审核通过变更为未审核 1 -> 0
                $update_condition['extract_flg'] = "0";
                $update_condition['check_time'] = null;
                $update_condition['check_user'] = null;
            } else if ($extract_type == 3){
                // 未审核或者审核通过变更为审核失败 0/1 -> 2
                $update_condition['extract_flg'] = "2";
                $update_condition['check_time'] = TIMESTAMP;
                $update_condition['check_user'] = $admin_id;
                $update_condition['extract_remark'] = $extract_remark;
            }
            Model('extract')->where($condition1)->update($update_condition);
            // 数据修改成功 
            if ($submit_flg == "num"){
                showMessage(Language::get('extract_check_success'));
                // 重新加载画面 
                $this->searchOp();
            }else{
                echo json_encode('success');
            }
        } else {
            // 审核状态已变更刷新操作按钮 
            if ($submit_flg == "num"){
                showMessage(Language::get('extract_check_error2'));
                // 重新加载画面 
                $this->searchOp();
            }else{
                echo json_encode('error');
            }
        }
    }
}
