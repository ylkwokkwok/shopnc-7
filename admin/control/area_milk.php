<?php
/**
 * 奶品配送地区设置
 *
 */
defined('NlWxShop') or exit('Access Invalid!');
class area_milkControl extends SystemControl {
    public function __construct(){
        parent::__construct();
        //读取语言包
        Language::read('area_milk');
    }

    /**
     * 初始化奶站
     */
    public function indexOp() {
        $model_milk = Model('milk');
        $model_area = Model('area_milk');
        // 判断提交数据 
        if (chksubmit()) {
            // 奶站编号 
            $station_id = $_POST['station_id'];
            // 配送范围集合 
            $area_str = $_POST['area_arr'];
            // 格式化数据 
            $area_arr = explode(',', $area_str);
            // 批处理内容 
            try {
                $where = array();
                $update = array();
                $insert = array();
                $model_area->beginTransaction();
                // 更新数据全部状态 
                $where = array('station_id'=>$station_id);
                $update = array('update_time'=>TIMESTAMP, 'update_admin'=>$this->admin_info['name'], 'update_flg'=>1);
                $model_area->updateAreaStation($where, $update);
                // 循环添加 
                foreach($area_arr as $val){
                    // 判断数据是否存在 
                    $where = array('station_id'=>$station_id, 'area_milk_id'=>$val);
                    $al1 = $model_area->getAreaStationInfo($where);
                    $where = array('area_milk_id'=>$val, 'update_flg'=>0);
                    $al2 = $model_area->getAreaStationInfo(array('area_milk_id'=>$val, 'update_flg'=>0));
                    // 区域存在其他奶站 
                    if(!empty($al2) && count($al2) > 0){
                        // 取得奶站信息 
                        $where = array('O_Number'=>$al2['station_id']);
                        $milk_info = $model_milk->getStationInfo($where);
                        throw new Exception('其他奶站已配送 (奶站名称:'.$milk_info['O_StationName'].')');
                    }
                    if(empty($al1) || count($al1) == 0){
                        // 取得区域信息 
                        $area_info = $model_area->getAreaMilkInfo(array('area_id'=>$val));
                        // 添加数据 
                        $insert['station_id'] = $station_id;
                        $insert['area_milk_id'] = $area_info['area_id'];
                        $insert['area_milk_name'] = $area_info['area_name'];
                        $insert['update_time'] = TIMESTAMP;
                        $insert['update_admin'] = $this->admin_info['name'];
                        $model_area->insertAreaStation($insert);
                    }else{
                        // 数据已存在 变更数据状态 
                        $where = array('station_id'=>$station_id, 'area_milk_id'=>$val);
                        $update = array('update_time'=>TIMESTAMP, 'update_admin'=>$this->admin_info['name'], 'update_flg'=>0);
                        $model_area->updateAreaStation($where, $update);
                    }
                }
                $model_area->commit();
                unset($where);
                unset($update);
                unset($insert);
                showMessage(Language::get('nc_common_save_succ'));
            } catch (Exception $e) {
                $model_area->rollback();
                showMessage($e->getMessage());
            }
        }
        $condition = array();
        // 奶站 
        $station_nm = $_GET['station_nm'];
        if(!empty($station_nm)){
            $condition['O_StationName'] = array('like', '%'.$station_nm.'%');
        }
        // 取得奶站类表 
        $milk = $model_milk->getStationList($condition, '*', 10);
        Tpl::output('show_page', $model_milk->showpage());
        // 循环取得奶站配送区域 
        foreach($milk as $mk=>$minfo){
            $milk[$mk]['area_list'] = $model_area->getAreaStationList(array('station_id'=>$minfo['O_Number'], 'update_flg'=>0), '*', '300');
        }
        // 显示内容信息 
        Tpl::output('milk_list', $milk);
        // 取得顶级地区信息 
        $q_array = $model_area->getAreaMilkList(array('area_parent_id'=>0), '*', 'area_sort');
        // 显示内容信息 
        Tpl::output('q_array', $q_array);
        $this->show_menu('milk');
        Tpl::showpage('area_milk.index');
    }

    /**
     * 初始化显示区域 
     */
    public function areaOp() {
        $model_area = Model('area_milk');
        // 取得顶级地区信息 
        $q_array = $model_area->getAreaMilkList(array('area_parent_id'=>0), '*', 'area_sort');
        // 根据顶级区域取得地区信息
        foreach($q_array as $qk=>$qinfo){
            // 取得街道信息 
            $j_array = $model_area->getAreaMilkList(array('area_parent_id'=>$qinfo['area_id']), '*', 'area_sort');
            // 放入列表 
            $q_array[$qk]['child'] = $j_array;
            // 释放信息 
            unset($j_array);
        }
        // 显示内容信息 
        Tpl::output('q_array', $q_array);
        $this->show_menu('area_milk');
        Tpl::showpage('area_milk.area');
    }

    /**
     * 添加信息  
     */
    public function areaAddOp() {
        $model_area = Model('area_milk');
        // 提交信息 
        if (chksubmit()) {
            // 添加配送地区信息 
            $data = array();
            $data['area_name'] = trim($_POST['area_name']);
            $data['area_parent_id'] = intval($_POST['area_parent_id']);
            $data['area_sort'] = 0;
            $data['area_deep'] = intval($_POST['area_deep']);
            $data['area_flg'] = 0;
            $model_area->insertAreaMilk($data);
            showMessage(Language::get('nc_common_save_succ'));
        }
        // 取得顶级地区信息 
        $q_array = $model_area->getAreaMilkList(array('area_parent_id'=>0), '*', 'area_sort');
        // 显示内容信息 
        Tpl::output('q_array', $q_array);
        $this->show_menu('area_milk_add');
        Tpl::showpage('area_milk.area.add');
    }

    /**
     * 编辑信息  
     */
    public function areaEditOp() {
        $model_area = Model('area_milk');
        // 提交信息 
        if (chksubmit()) {
            // 更新配送地区信息 
            // 更新条件 
            $condition = array();
            $condition['area_id'] = trim($_POST['area_id']);
            // 更新内容 
            $data = array();
            $data['area_name'] = trim($_POST['area_name']);
            $data['area_parent_id'] = intval($_POST['area_parent_id']);
            $data['area_sort'] = 0;
            $data['area_deep'] = intval($_POST['area_deep']);
            $data['area_flg'] = 0;
            // 更新 
            $model_area->updateAreaMilk($condition, $data);
            showMessage(Language::get('nc_common_save_succ'));
        }
        // 取得顶级地区信息 
        $q_array = $model_area->getAreaMilkList(array('area_parent_id'=>0), '*', 'area_sort');
        // 详细信息 
        $area_info = $model_area->getAreaMilkInfo(array('area_id'=>trim($_GET['area_id'])));
        // 查看是否为最终级 
        if($area_info['area_deep'] == 3){
            // 取得次级区域信息
            $j_info = $model_area->getAreaMilkInfo(array('area_id'=>$area_info['area_parent_id']));
            Tpl::output('q_id', $j_info['area_parent_id']);
            $j_array = $model_area->getAreaMilkList(array('area_parent_id'=>$j_info['area_parent_id']), '*', 'area_sort');
            Tpl::output('j_id', $area_info['area_parent_id']);
            Tpl::output('j_array', $j_array);
        }else if($area_info['area_deep'] == 2){
            $j_array = $model_area->getAreaMilkList(array('area_parent_id'=>$area_info['area_parent_id']), '*', 'area_sort');
            Tpl::output('q_id', $area_info['area_parent_id']);
            Tpl::output('j_array', $j_array);
        }else{
            Tpl::output('q_id', $area_info['area_parent_id']);
        }
        // 显示内容信息 
        Tpl::output('q_array', $q_array);
        Tpl::output('area_info', $area_info);
        $this->show_menu('area_milk_edit');
        Tpl::showpage('area_milk.area.edit');
    }

    /**
     * 动态取得信息   
     */
    public function ajaxAreaOp() {
        $model_area = Model('area_milk');
        // 父类信息 
        $area_parent_id = $_GET['id'];
        // 取得顶级地区信息 
        $a_array = $model_area->getAreaMilkList(array('area_parent_id'=>$area_parent_id), '*', 'area_sort');
        // 显示内容信息 
        echo json_encode(array('area_list'=>$a_array, 'deep'=>intval($_GET['deep'])+1));
    }

    /**
     * 页面内导航菜单
     * @param string 	$menu_key	当前导航的menu_key
     * @param array 	$array		附加菜单
     * @return
     */
    private function show_menu($menu_key='') {
        $menu_array = array(
            1=>array('menu_key'=>'milk','menu_name'=>Language::get('am_milk_list'), 'menu_url'=>urlAdmin('area_milk', 'milk')),
            2=>array('menu_key'=>'area_milk','menu_name'=>Language::get('am_area_milk_list'), 'menu_url'=>urlAdmin('area_milk', 'area')),
            3=>array('menu_key'=>'area_milk_add','menu_name'=>Language::get('am_area_milk_add'), 'menu_url'=>urlAdmin('area_milk', 'areaAdd')),
        );
        if($menu_key == 'area_milk_edit') {
            $menu_array[] = array('menu_key'=>'area_milk_edit', 'menu_name'=>Language::get('am_area_milk_edit'), 'menu_url'=>'javascript:;');
        }
        if($menu_key == 'milk_edit') {
            $menu_array[] = array('menu_key'=>'milk_edit', 'menu_name'=>Language::get('am_milk_edit'), 'menu_url'=>'javascript:;');
        }
        Tpl::output('menu', $menu_array);
        Tpl::output('menu_key', $menu_key);
    }
}
