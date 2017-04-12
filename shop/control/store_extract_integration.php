<?php
/**
 * 积分管理
 * @author zly
 */
defined('NlWxShop') or exit('Access Invalid!');
class store_extract_integrationControl extends BaseSellerControl{
    private $extract_state;
	public function __construct() {
		parent::__construct() ;
		Language::read('member_layout,member_voucher,member_store_index');
                // 下拉控件：提取状态
                $this->extract_state = array('not_extracted'=>array(4,'未审核'),'extracted'=>array(1,'审核通过'),'examine'=>array(2,'审核未通过'),'untransfer'=>array(3,'已到账'));
                Tpl::output('extract_state',$this->extract_state);
	}
        /**
         * 提现记录
         */
        public function indexOp(){
            // 加载模型
            $model = Model('extract');
            // 当前店铺id
            $condition['extract.extract_user'] = $_SESSION['store_id'];
            // 检索条件：推广积分提现
            $condition['extract.extract_type'] = 1;
            // 检索表单提交
            if (chksubmit()){
                // 检索条件：未审核
                if($_GET['select_state'] == 4){
                    $condition['extract.extract_flg'] = 0;
                // 检索条件：为空    
                } else if($_GET['select_state'] == 0){
                //  检索条件： 审核通过 审核未通过 已付款   
                } else {
                    $condition['extract.extract_flg'] = $_GET['select_state'];
                }
                // 申请开始时间
                $stime = $_GET['txt_startdate']?strtotime( $_GET['txt_startdate']):NULL;
                // 申请结束时间
		$etime = $_GET['txt_enddate']?strtotime($_GET['txt_enddate']):NULL;
		if ($stime > 0 && $etime>0){
		    $condition['extract.extract_date'] = array('time',array($stime,$etime));
		}elseif ($stime > 0){
		    $condition['extract.extract_date'] = array('egt',$stime);
		}elseif ($etime > 0){
		    $condition['extract.extract_date'] = array('elt',$etime);
		}
            }
            // 分页配置
            $page = new Page();
            $page->setEachNum(10);
            $page->setStyle('admin') ;
            // 提现数据
            $list = $model->extract_list($condition,$page);
            TPL::output('list',$list);
            Tpl::output('show_page',$model->showpage());
            // 按钮显示
            $this->profile_menu('extrate_list','extrate_list');
            Tpl::showpage('store_extract.list');
        }
        /**
         *  订单记录
         */
        public function store_extractOp(){
            // 加载Model
            $model = Model('extract');
            // 检索条件：当前店铺ID
            $condition['store_id'] = $_SESSION['store_id'];
            if(chksubmit()){
                // 检索条件：下单开始时间
                $stime = $_GET['order_startdate']?strtotime( $_GET['order_startdate']):NULL;
                // 检索条件：下单结束时间
		$etime = $_GET['order_enddate']?strtotime($_GET['order_enddate']):NULL;
		if ($stime > 0 && $etime>0){
		    $condition['add_time'] = array('time',array($stime,$etime));
		}elseif ($stime > 0){
		    $condition['add_time'] = array('egt',$stime);
		}elseif ($etime > 0){
		    $condition['add_time'] = array('elt',$etime);
		} 
                if($_GET['order_sn'] != ''){
                    $condition['order_sn'] = array('like',"%".$_GET['order_sn']."%");
                }
            }
            // 分页配置
            $page = new Page();
            // 每页查询记录条数
            $page->setEachNum(10) ;
            // 分页样式
            $page->setStyle('admin') ;
            // 列表显示
            $list = $model->extract_order($condition,$page);
            Tpl::output('show_page',$model->showpage());
            TPL::output('list',$list);
            // 按钮
            $this->profile_menu('extrate_order','extrate_order');
            Tpl::showpage('store_extract.order') ;
        }
        /**
         * 提现页面
         */
        public function extract_startOp(){
            $model = Model('extract');
            if($_SESSION['store_id'] == ''){
                showMessage('未绑定微信，不能申请提现！', '', 'html', 'error');
            }
            $where['store_id'] = $_SESSION['store_id'];
            // 总金额
            $all_extend_points = $model->all_extend_points($where);
            TPL::output('all_extend_points',$all_extend_points);
            // 可提现金额
            $extend_points = $model->extend_points($where);
            TPL::output('extend_points',$extend_points);
            // 按钮
            $this->profile_menu('extract_start','extract_start');
            Tpl::showpage('store_extract.start');
        }
        /**
         * 提现申请
         */
        public function store_extract_applyOp(){
            // 加载Model
            $model_extract = Model('extract');
            $data = array();
            // 增加提现记录：卖家ID
            $data['extract_user'] = $_SESSION['store_id'];
            // 增加提现记录：提现金额
            $data['extract_money'] = $_GET['extend_money'];
            // 增加提现记录：提现类型
            $data['extract_type'] = 1;
            // 增加提现记录：提取日期
            $data['extract_date'] = TIMESTAMP;
            try
            {
                // 开启事务
                $model_extract->beginTransaction();
                // 增加提现记录
                $apply_extract_id = $model_extract->insert_extract($data);
                // 本次提现ID
                $extract['extract_id'] = $apply_extract_id;
                // 更改条件：当前店铺
                $where['store_id'] = $_SESSION['store_id'];
                // 更改订单表提现ID
                $model_extract->update_extract($extract,$where);
                // 提交事务
                $model_extract->commit();
                // 显示提现信息并跳转到提现列表
                showMessage('申请提现成功！','index.php?act=store_extract_integration&op=index');
            }
            catch (Exception $ex)
            {
                // 回滚事务
                $model_extract->rollback();
                showMessage('申请提现失败！', '', 'html', 'error');
            }
        }
        /**
         * 提现信息详细
         */
        public function show_order_detailOp(){
            // 订单ID
            $order_id = $_GET['order_id'];
            // 判断获取订单ID是否成功
	    if ($order_id <= 0) {
	        showMessage(Language::get('wrong_argument'),'','html','error');
	    }
            // 加载Model
	    $model_order = Model('order');
            // 拼接查询条件
	    $condition = array();
            // 查询条件:当前订单ID
            $condition['order_id'] = $order_id;
            // 查询条件:当前店铺ID
            $condition['store_id'] = $_SESSION['store_id'];
            // 获取订单信息
	    $order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods','member','extract'));
            // 判断获取订单信息是否成功
            if (empty($order_info)) {
	        showMessage(Language::get('store_order_none_exist'),'','html','error');
	    }
            //显示系统自动取消订单日期
            if ($order_info['order_state'] == ORDER_STATE_NEW) {
                            $order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_DAY + 3 * 24 * 3600;
            }
            //发货信息
            if (!empty($order_info['extend_order_common']['daddress_id'])) {
                $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
                Tpl::output('daddress_info',$daddress_info);
            }
            foreach ($order_info['extend_order_goods'] as $value) {
            // 商品图片
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            // 商品类型
            $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
            // 判断是否是赠品
            if ($value['goods_type'] == 5) {
                $order_info['zengpin_list'][] = $value;
            } else {
                $order_info['goods_list'][] = $value;
            }
            }
            // 判断商品总数量        
            if (empty($order_info['zengpin_list'])) {
                // 没有赠品
                $order_info['goods_count'] = count($order_info['goods_list']);
            } else {
                // 有赠品
                $order_info['goods_count'] = count($order_info['goods_list']) + 1;
            }
            // 提现信息：提现状态
            if(!empty($order_info['extract_id'])){
                if($order_info['extract_detail']['extract_flg'] == 0){
                    $order_info['extract_detail']['extract_message'] = '未审核';
                } else if ($order_info['extract_detail']['extract_flg'] == 1){
                    $order_info['extract_detail']['extract_message'] = '审核通过';
                } else if($order_info['extract_detail']['extract_flg'] == 3){
                    $order_info['extract_detail']['extract_message'] = '已付款';
                }
            }
            // 按钮
            $this->profile_menu('order_detail','order_detail');
            TPL::output('order_info',$order_info);
            Tpl::showpage('store_extract.detail');
        }
        /**
         * 按钮
         * @param type $menu_type 
         * @param type $menu_key
         */
        private function profile_menu($menu_type,$menu_key=''){
            $menu_array	= array();
		switch ($menu_type) {
                    case 'extrate_list':
                        $menu_array = array(
                            1 => array('menu_key' => 'extrate_list', 'menu_name' => '提现记录', 'menu_url' => 'index.php?act=store_extract_integration&op=index'),
                            2 => array('menu_key' => 'extrate_order', 'menu_name' => '积分明细', 'menu_url' => 'index.php?act=store_extract_integration&op=store_extract'),
                        );
                       
                        break;
                    case 'extrate_order':
                        $menu_array = array(
                               1 => array('menu_key' => 'extrate_list', 'menu_name' => '提现记录', 'menu_url' => 'index.php?act=store_extract_integration&op=index'),
                               2 => array('menu_key' => 'extrate_order', 'menu_name' => '积分明细', 'menu_url' => 'index.php?act=store_extract_integration&op=store_extract')
                           );
                        break;
                    case 'extract_start':
                        $menu_array = array(
                               1 => array('menu_key' => 'extrate_list', 'menu_name' => '提现记录', 'menu_url' => 'index.php?act=store_extract_integration&op=index'),
                               2 => array('menu_key' => 'extrate_order', 'menu_name' => '积分明细', 'menu_url' => 'index.php?act=store_extract_integration&op=store_extract'),
                               3 => array('menu_key' => 'extract_start', 'menu_name' => '提现', 'menu_url' => 'index.php?act=store_extract_integration&op=extract_start')
                            );
                        break;
                    case 'order_detail':
                        $menu_array = array(
                               1 => array('menu_key' => 'extrate_list', 'menu_name' => '提现记录', 'menu_url' => 'index.php?act=store_extract_integration&op=index'),
                               2 => array('menu_key' => 'extrate_order', 'menu_name' => '积分明细', 'menu_url' => 'index.php?act=store_extract_integration&op=store_extract'),
                               4 => array('menu_key' => 'order_detail', 'menu_name' => '订单详细', 'menu_url' => NULL)
                            );
                        break;
        }
                Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
        }
}

