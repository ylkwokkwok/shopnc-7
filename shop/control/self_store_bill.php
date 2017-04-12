<?php
/**
 * 实物订单结算
 ***/


defined('NlWxShop') or exit('Access Invalid!');
class self_store_billControl extends BaseSellerControl {
    /**
     * 每次导出多少条记录
     * @var unknown
     */
    const EXPORT_SIZE = 1000;

    public function __construct() {
    	parent::__construct() ;
    	Language::read('member_layout');
    }
        /**
	 * 结算列表
	 *
	 */
    public function indexOp() {

        $self_cd = $_GET['self_cd'];
        $start_unixtime = $_GET['query_start_date'];
        $end_unixtime   = $_GET['query_end_date'];    
        $model_self_bill = Model('self_bill_total');   
        $condition ='`order`.self_receive_spot_cd != ""';
        $condition .=' AND ( ';
        $condition .='( ';
	$condition .='  `order`.order_state != 0 ';
	$condition .='  AND `order`.wx_pay_sn != "") ';
        $condition .=')';
        $condition .='  AND `order`.refund_state = 0 ';
        if(!empty($self_cd)){
            $condition .=' AND self_receive.self_receive_nm like "%'.$self_cd.'%"';
        }
         if (!empty($start_unixtime)) {
             $condition .=' AND FROM_UNIXTIME( `order`.add_time, "%Y-%m-%d") >="'.$start_unixtime.'"';
        }
         if (!empty($end_unixtime)) {
             $condition .=' AND FROM_UNIXTIME( `order`.add_time, "%Y-%m-%d") <="'.$end_unixtime.'"';
        }
        
        $count = $model_self_bill->getCount($condition);
        $resultlist = $model_self_bill->totalOrder($condition,$count[0]['orderNum']);
        Tpl::output('resultlist',$resultlist);
        Tpl::output('show_page',$model_self_bill->showpage());
        $this->profile_menu('resultlist','resultlist');
        Tpl::showpage('self_store_bill_total');
    }
	

	/**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @return
	 */
	private function profile_menu($menu_type,$menu_key='') {
		$menu_array	= array();
		switch ($menu_type) {
                    //act=bill&op=list
                        case 'resultlist':
				$menu_array = array(
                                  array('menu_key'=>'resultlist','menu_name'=>'商品销售统计', 'menu_url'=> urlShop('store_bill', 'total')),
				);
				break;
		
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
	}
     }
     /**
	 * 导出
	 *
	 */
	public function export_step1Op(){
         $lang	= Language::getLangContent();

       $self_cd = $_GET['self_cd'];
        $start_unixtime = $_GET['query_start_date'];
        $end_unixtime   = $_GET['query_end_date'];    
        $model_self_bill = Model('self_bill_total');   
//        if(!empty($self_cd)){
//            $condition['self_receive.self_receive_nm'] = array('like',"%".$_GET['self_cd']."%");
//        }
        $condition ='`order`.self_receive_spot_cd != ""';
        $condition .=' AND ( ';
        $condition .='( ';
        $condition .='`order`.wx_pay_sn = ""';
	$condition .=' AND `order`.order_state != 0 ';
	$condition .=') || (`order`.wx_pay_sn != "") ';
        $condition .=')';
        if(!empty($self_cd)){
            $condition .=' AND self_receive.self_receive_nm like "%'.$self_cd.'%"';
        }
         if (!empty($start_unixtime)) {
             $condition .=' AND FROM_UNIXTIME( `order`.add_time, "%Y-%m-%d") >="'.$start_unixtime.'"';
        }
         if (!empty($end_unixtime)) {
             $condition .=' AND FROM_UNIXTIME( `order`.add_time, "%Y-%m-%d") <="'.$end_unixtime.'"';
        }
		if (!is_numeric($_GET['curpage'])){
			$count = $model_self_bill->getSallOrderCount($condition);
			$array = array();
			if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=self_store_bill&op=index');
				Tpl::showpage('export.excel');
			}else{	//如果数量小，直接下载
				$data = $model_self_bill->getSallOrderList($condition,self::EXPORT_SIZE);
				$this->createExcel($data);
			}
		}else{	//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_self_bill->getSallOrderList($condition,"{$limit1},{$limit2}");
			$this->createExcel($data);
		}
	}

	/**
	 * 生成excel
	 *
	 * @param array $data
	 */
	private function createExcel($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'自取点');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'买家');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'购买日期');
                $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名称');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商品数量');
		 foreach ($data as $k=>$v){
                    $tmp = array();
                    $tmp[] = array('data'=>$v['self_receive_nm']);
                    $tmp[] = array('data'=>$v['reciver_name']);
                    $tmp[] = array('data'=>$v['add_time']);
                    $tmp[] = array('data'=>$v['goods_name']);
                    $tmp[] = array('data'=>$v['goods_num']);
	            $excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset('自取点销售情况',CHARSET));
		$excel_obj->generateXML($excel_obj->charset('自取点销售情况',CHARSET).'-'.date('Y-m-d-H',time()));
	}
}
