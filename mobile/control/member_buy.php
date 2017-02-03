<?php
/**
 * 购买
 *
 *
 *
 *
 */

defined('NlWxShop') or exit('Access Invalid!');

class member_buyControl extends mobileMemberControl {

	public function __construct() {
		parent::__construct();
	}

    /**
     * 购物车、直接购买第一步:选择收获地址和配置方式
     */
    public function buy_step1Op() {
        $cart_id = explode(',', $_POST['cart_id']);

        $logic_buy = logic('buy');

        //得到购买数据
        $result = $logic_buy->buyStep1($cart_id, $_POST['ifcart'], $this->member_info['member_id'], $this->member_info['store_id']);
        if(!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }
        /*----------------添加动态取奶品方法------------------------*/
         $milkproductarray = array();
        $model_goods = Model('goods');
        $resultProduct = $model_goods->getMilkProduct();
         foreach ($resultProduct as $product) {
           	$milkproductarray[] = $product['O_Number'];
         }
       /*----------------修改结束------------------------*/
        /* lyq@newland 添加开始 **/
        /* 时间：2015/08/24     **/
        // 是否购买了心乐自营店的奶卡
        $if_buy_milk_cards = FALSE;
        /* lyq@newland 添加结束 **/
       
        //整理数据
        $store_cart_list = array();
        foreach ($result['store_cart_list'] as $key => $value) {
            /* lyq@newland 添加开始 **/
            /* 时间：2015/08/24     **/
            // 总自取优惠金额
            $total_self_discount = 0.0;
            // 循环商品列表
            foreach ($value as $goods_key => $goods) {
                /* lyq@newland 添加开始 **/
                /* 时间：2015/09/17     **/
                // 获取商品自取优惠金额
                $value[$goods_key]['goods_self_discount'] = Model('goods_common')->getfby_goods_commonid($goods['goods_commonid'],'goods_self_discount');
                // 累加自取优惠金额(自取优惠金额*所购商品数量)
                $total_self_discount += floatval($value[$goods_key]['goods_self_discount']) * intval($goods['goods_num']);
                /* lyq@newland 添加结束 **/
                // 购买了心乐自营店的奶卡时
                /*----------------修改原array(1001,1002,1004)替换 implode(',', $milkproductarray)------------------------*/
                if ($goods['store_id'] == 1 && in_array($goods['gc_id'], $milkproductarray)) {
                    $if_buy_milk_cards = TRUE;
                }
                /*----------------修改结束------------------------*/
            }
            // 店铺自取总金额
            $store_cart_list[$key]['store_goods_self_total'] = ncPriceFormat(floatval($result['store_goods_total'][$key]) - $total_self_discount);
            /* lyq@newland 添加结束 **/
            $store_cart_list[$key]['goods_list'] = $value;
            $store_cart_list[$key]['store_goods_total'] = $result['store_goods_total'][$key];
            if(!empty($result['store_premiums_list'][$key])) {
                $result['store_premiums_list'][$key][0]['premiums'] = true;
                $result['store_premiums_list'][$key][0]['goods_total'] = 0.00;
                $store_cart_list[$key]['goods_list'][] = $result['store_premiums_list'][$key][0];
            }
            $store_cart_list[$key]['store_mansong_rule_list'] = $result['store_mansong_rule_list'][$key];
            $store_cart_list[$key]['store_voucher_list'] = $result['store_voucher_list'][$key];
            if(!empty($result['cancel_calc_sid_list'][$key])) {
                $store_cart_list[$key]['freight'] = '0';
                $store_cart_list[$key]['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
            } else {
                $store_cart_list[$key]['freight'] = '1';
            }
            $store_cart_list[$key]['store_name'] = $value[0]['store_name'];
        }

        $buy_list = array();
        $buy_list['store_cart_list'] = $store_cart_list;
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/09/15     **/
        // 可用的自取点列表
        $buy_list['avilable_self_cds'] = $this->get_self_intersect(array_keys($buy_list['store_cart_list']));
        /* lyq@newland 添加结束 **/
        $buy_list['freight_hash'] = $result['freight_list'];
        $buy_list['address_info'] = $result['address_info'];
        $buy_list['ifshow_offpay'] = $result['ifshow_offpay'];
        $buy_list['vat_hash'] = $result['vat_hash'];
        $buy_list['inv_info'] = $result['inv_info'];
        $buy_list['available_predeposit'] = $result['available_predeposit'];
        $buy_list['available_rc_balance'] = $result['available_rc_balance'];
        
        /* lyq@newland 添加开始 **/
        /* 时间:2015/05/14      **/
        /* 功能ID:SHOP003       **/
        // 推广积分抵扣开关
        $buy_list['points_use_isuse']  = C('points_use_isuse') == '1' ? TRUE : FALSE;     
        // 推广积分与现金比例
        $buy_list['points_cash_ratio'] = intval(C('points_cash_ratio'));     
        // 推广积分订单抵扣比例
        $buy_list['order_cash_ratio']  = floatval(C('order_cash_ratio')) / 100;     
        /* lyq@newland 添加结束 **/
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/07/02     **/
        // 查询推广积分
        $extend_points_result = Model()->table('member')->field('extend_points')->where(array('member_id' => $this->member_info['member_id']))->find();
        // 推广积分
        $buy_list['extend_points'] = intval($extend_points_result['extend_points']);
        /* lyq@newland 添加结束 **/
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/08/24     **/
        $buy_list['if_buy_milk_cards'] = $if_buy_milk_cards;
        /* lyq@newland 添加结束 **/
        
        output_data($buy_list);
    }

    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     */
    public function buy_step2Op() {
        $param = array();
        $param['remark'] = $_POST['remark'];
        $param['ifcart'] = $_POST['ifcart'];
        $param['cart_id'] = explode(',', $_POST['cart_id']);
        $param['address_id'] = $_POST['address_id'];
        $param['vat_hash'] = $_POST['vat_hash'];
        $param['offpay_hash'] = $_POST['offpay_hash'];
        $param['offpay_hash_batch'] = $_POST['offpay_hash_batch'];
        $param['pay_name'] = $_POST['pay_name'];
        $param['invoice_id'] = $_POST['invoice_id'];

        //处理代金券
        $voucher = array();
        $post_voucher = explode(',', $_POST['voucher']);
        if(!empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($voucher_t_id, $store_id, $voucher_price) = explode('|', $value);
                $voucher[$store_id] = $value;
            }
        }
        $param['voucher'] = $voucher;
        $remark = $_POST['remark'];
        $order_best_time = $_POST['appDateTime'];
        //手机端暂时不做支付留言，页面内容太多了
        //$param['pay_message'] = json_decode($_POST['pay_message']);
        $param['pd_pay'] = $_POST['pd_pay'];
        $param['rcb_pay'] = $_POST['rcb_pay'];
        $param['password'] = $_POST['password'];
        $param['fcode'] = $_POST['fcode'];
        $param['order_from'] = 2;
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/07/02     **/
        // 推广积分与现金比例
        $param['points_cash_ratio'] = $_POST['points_cash_ratio'];
        // 推广积分订单抵扣比例
        $param['order_cash_ratio'] = $_POST['order_cash_ratio'];
        // 当前使用积分
        $param['extend_points'] = $_POST['extend_points'];
        /* lyq@newland 添加结束 **/
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/08/21     **/
        // 自取点编号
        $param['self_receive_spot_cd'] = empty($_POST['self_receive_spot_cd'])?'':$_POST['self_receive_spot_cd'];
        /* lyq@newland 添加结束 **/
        
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/08/24     **/
        $milk_order_log_id = $this->add_milk_order_log($param, $_POST['mc_deliver_type']);
        /* lyq@newland 添加结束 **/
        
        $logic_buy = logic('buy');

        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email']);
        if(!$result['state']) {
            output_error($result['msg']);
        }
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/08/24     **/
        // 需要更新的订单数据
        $update_data = array(
            'self_receive_spot_cd' => $param['self_receive_spot_cd'],  // 自取点编号
        );
        // 添加订奶信息记录 成功时
        if ($milk_order_log_id !== FALSE) {
            $update_data['milk_order_log_id'] = $milk_order_log_id; // 订奶信息记录ID
        }
         $update_data['remark'] = $remark;
         $update_data['order_best_time'] = $order_best_time;
        // 更新订单信息
        Model()->table('order')->where('pay_sn = "'.$result['data']['pay_sn'].'"')->update($update_data);
        /* lyq@newland 添加结束 **/
        output_data(array('pay_sn' => $result['data']['pay_sn']));
    }

    /**
     * 验证密码
     */
    public function check_passwordOp() {
        if(empty($_POST['password'])) {
            output_error('参数错误');
        }

        $model_member = Model('member');

        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if($member_info['member_paypwd'] == md5($_POST['password'])) {
            output_data('1');
        } else {
            output_error('密码错误');
        }
    }

    /**
     * 更换收货地址
     */
    public function change_addressOp() {
        $logic_buy = Logic('buy');
        $data = $logic_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $this->member_info['member_id']);
        if(!empty($data) && $data['state'] == 'success' ) {
            output_data($data);
        } else {
            output_error('地址修改失败');
        }
    }

    /**
     * 更改奶站配送地点 
     */
    public function change_address_milkOp() {
        $logic_buy = Logic('buy');
        $data = $logic_buy->changeAddrMilk($_POST['freight_hash'], $_POST['city_id'], $_POST['self_receive_spot_cd'], $this->member_info['member_id']);
        if(!empty($data) && $data['state'] == 'success' ) {
            output_data($data);
        } else {
            output_error('地址修改失败');
        }
    }

    /* lyq@newland 添加开始 **/
    /* 时间：2015/08/21 - 2015/08/24 **/
    /**
     * ajax获取自取点信息
     */
    public function get_self_receive_spot_infoOP() {
        $self_receive_spot_cd = $_POST['self_receive_spot_cd'];
        $sql = 'SELECT ';
        $sql.= '    * ';
        $sql.= 'FROM ';
        $sql.= '    `mst_self_receive` ';
        $sql.= 'WHERE ';
        $sql.= '    self_receive_spot_cd = "'.$self_receive_spot_cd.'"';
        $result = Model()->query($sql);
        output_data($result[0]);
    }
    
    /**
     * 添加订奶信息记录
     * @param type $shop_order_data 商城订单数据
     * @param type $mc_deliver_type 奶卡配送方式 1：配送，0：不需配送
     * @return type 订奶信息记录ID
     */
    private function add_milk_order_log($shop_order_data, $mc_deliver_type) {
        // 得到所购买的id和数量
        $cart_list = $this->_parseItems($shop_order_data['cart_id']);
        // 获取买家地址信息（个人信息）
        $address_info = Model('address')->getAddressInfo(array('address_id'=>$shop_order_data['address_id']));
        // 获取所买商品中的奶卡商品信息
        $milk_info = $this->get_milk_info($cart_list, $shop_order_data['ifcart']);
        // 无奶卡商品时，不做操作
        if (empty($milk_info)) { return false; }
        
        // 获取奶卡类型列表
        $milk_type_list = $this->get_milk_type_list();
        // 整理订奶记录数据
        $order_data = array(
            'self_receive_spot_cd' => $shop_order_data['self_receive_spot_cd'], // 自取点编号
            'name' => $address_info['true_name'],                               // 客户姓名
            'tel' => $address_info['mob_phone'],                                // 客户电话
            'milk_order_datas' => array(),                                      // 订奶数据
            'member_id' => $this->member_info['member_id'],                     // 会员ID
            'remark' => $shop_order_data['remark']
        );
        // 奶卡配送方式为 配送时
        if ($mc_deliver_type == 1) {
            // 向订单数据中加入地址项
            $order_data['address'] = $address_info['area_info'].' '.$address_info['address'];
        }
        // 循环奶卡商品 补全 订奶数据
        foreach ($milk_info as $value) {
            $order_data['milk_order_datas'][] = array(
                'milk_cd' => $value['gc_id'],
                /*----------------修改原 $milk_type_list[$value['gc_id']][$value['goods_id']]------------------------*/
                'card_type' => $milk_type_list[$value['gc_id']][$value['milk_card_type']],
                 /*----------------修改结束------------------------*/
                'goods_id' => $value['goods_id'],
                'goods_num' => $shop_order_data['ifcart'] == 1?$cart_list[$value['cart_id']]:$cart_list[$value['goods_id']],
            ); 
        }
        // 整理订奶记录数据
        $milk_log = array(
            'order_data' => serialize($order_data),
            'order_time' => date('Y-m-d H:i:s')
        );
        // 插入订奶记录，返回记录ID
        return Model('milk_order_log')->insert($milk_log);
    }
    
    /**
     * 得到所购买的id和数量
     * @param type $cart_id
     * @return type
     */
    private function _parseItems($cart_id) {
        //存放所购商品ID和数量组成的键值对
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    if (intval($match[2][0]) > 0) {
                        $buy_items[$match[1][0]] = $match[2][0];
                    }
                }
            }
        }
        return $buy_items;
    }
    
    /**
     * 获取所买商品中的奶卡商品信息
     * @param type $cart_list 商品列表
     * @param type $ifcart 是否是购物车购买
     * @return type 商品信息
     */
    private function get_milk_info($cart_list, $ifcart) {
     /*----------------修改动态取奶品------------------------*/
        $milk_gc_list = array();
        $model_goods = Model('goods');
        $resultProduct = $model_goods->getMilkProduct();
         foreach ($resultProduct as $product) {
                 // 奶品编号
           	$milk_gc_list[] = $product['O_Number'];
         }
        /*----------------修改结束------------------------*/
        // 购物车购买
        if ($ifcart == 1) {
            $sql = 'SELECT ';
            $sql.= '    t1.cart_id, ';
            $sql.= '    t1.store_id, ';
            $sql.= '    t1.goods_id, ';
            $sql.= '    t2.milk_card_type, ';
            $sql.= '    t2.milk_product_num as gc_id ';
            $sql.= 'FROM';
            $sql.= '    '.DBPRE.'cart t1 ';
            $sql.= 'LEFT JOIN '.DBPRE.'goods t2 ON t1.goods_id = t2.goods_id ';
            $sql.= 'WHERE ';
            $sql.= '    t1.cart_id IN ('.implode(',', array_keys($cart_list)).') ';
            $sql.= 'AND ';
            $sql.= '    t1.store_id = 1 ';
            $sql.= 'AND ';
//            $sql.= '    t2.gc_id in (1001,1002,1004)';
            $sql.= '    t2.milk_product_num in ('.implode(',', $milk_gc_list).') ';
        }
        // 直接购买商品
        else {
            $sql = 'SELECT ';
            $sql.= '    store_id, ';
            $sql.= '    goods_id, ';
            $sql.= '    milk_card_type, ';
            $sql.= '    milk_product_num as gc_id ';
            $sql.= 'FROM';
            $sql.= '    '.DBPRE.'goods ';
            $sql.= 'WHERE ';
            $sql.= '    goods_id IN ('.implode(',', array_keys($cart_list)).')';
            $sql.= 'AND ';
            $sql.= '    store_id = 1 ';
            $sql.= 'AND ';
//            $sql.= '    gc_id in (1001,1002,1004)';
            $sql.= '    milk_product_num in ('.implode(',', $milk_gc_list).')';
        }
        // 返回查询数据
        return Model()->query($sql);
    }
    
    /**
     * 获取奶卡类型列表
     * @return string 奶卡类型列表
     */
     /*----------------修改动态取奶卡类别------------------------*/
    private function get_milk_type_list() {
        $milk_gc_list = array();
        $model_goods = Model('goods');
        $resultProduct = $model_goods->getMilkProduct();
         foreach ($resultProduct as $product) {
                 // 奶品编号
           	$milk_gc_list[] = $product['O_Number'];
         }
        // 奶卡类型列表
        $milk_type_list = array();
        $array = array();
        // 循环奶品编号
        foreach ($milk_gc_list as $value) {
            // 获取相应奶品下的奶卡信息
               $goods_product = Model('goods')->where('milk_product_num = '.$value.' and store_id = 1')->order('milk_card_type asc')->select();
                foreach ($goods_product as $product){
                    $array[] = $product['milk_card_type'];
                }
                $milk_type_list[$value] = $array;
        }
//             // 奶品编号
//        $milk_gc_list = array(1001,1002,1004);
//        // 奶卡类型列表
//        $milk_type_list = array();
//        // 循环奶品编号
//        foreach ($milk_gc_list as $value) {
//            // 获取相应奶品下的奶卡信息
//            $goods_list = $goods_list = Model('goods')->where('gc_id = '.$value.' and store_id = 1')->order('goods_id asc')->select();
//            // 添加奶卡类型
//            $milk_type_list[$value] = array(
//                $goods_list[0]['goods_id'] => '0',  // 月卡
//                $goods_list[3]['goods_id'] => '1',  // 季卡
//                $goods_list[1]['goods_id'] => '2',  // 半年卡
//                $goods_list[2]['goods_id'] => '3',  // 年卡
//                $goods_list[4]['goods_id'] => '4',  // 周卡
//            );
//        }
        // 返回奶卡类型列表
        return $milk_type_list;
    }
    /*----------------修改动态取奶卡类别结束------------------------*/
    /* lyq@newland 添加结束 **/
    
    /* lyq@newland 添加开始 **/
    /* 时间：2015/09/15     **/
    /**
     * 获取自取点交集
     *   根据店铺ID列表，获取各店铺所绑定的自取点交集
     * @param type $store_ids 店铺ID数组
     * @return type 自取点交集数组(array)|无需验证(TRUE)|无自取点交集(FALSE)
     */
    private function get_self_intersect($store_ids) {
        // 过滤店铺ID数组，筛选出自取点绑定类型不为“所有”的店铺
        $store_filter_sql = 'SELECT ';
        $store_filter_sql.= '    store_id ';
        $store_filter_sql.= 'FROM ';
        $store_filter_sql.= '    '.DBPRE.'store ';
        $store_filter_sql.= 'WHERE ';
        $store_filter_sql.= '    store_id IN ('.implode(',', $store_ids).') ';
        $store_filter_sql.= 'AND self_bind_type != 2 ';
        $store_filter_result = Model()->query($store_filter_sql);
        // 所有店铺的自取点绑定类型都为“所有”
        if (empty($store_filter_result)) {
            // 返回 TRUE
            return TRUE;
        }
        // 只有一个店铺的自取点绑定类型不为“所有”
        else if (count($store_filter_result) === 1) {
            // 取该该店铺绑定的自取点
            $sql = 'SELECT ';
            $sql.= '    self_receive_spot_cd ';
            $sql.= 'FROM ';
            $sql.= '    '.DBPRE.'store_self_bind ';
            $sql.= 'WHERE ';
            $sql.= '    store_id = '.$store_filter_result[0]['store_id'];
            $self_cd_result = Model()->query($sql);
        }
        // 多店铺自取点绑定类型不为“所有”
        else {
            // 整理店铺列表作为条件
            $store_id_in = '';
            foreach ($store_filter_result as $value) {
                $store_id_in .= $value['store_id'].',';
            }
            // 获取各店铺绑定自取点的交集
            $sql = 'SELECT ';
            $sql.= '    self_receive_spot_cd, ';
            $sql.= '    count(*) count ';
            $sql.= 'FROM ';
            $sql.= '    '.DBPRE.'store_self_bind ';
            $sql.= 'WHERE ';
            $sql.= '    store_id IN ('.substr($store_id_in, 0, strlen($store_id_in)-1).') ';
            $sql.= 'GROUP BY ';
            $sql.= '    self_receive_spot_cd ';
            $sql.= 'HAVING ';
            $sql.= '    count > 1 ';
            $self_cd_result = Model()->query($sql);
            // 无交集时返回 FALSE
            if (empty($self_cd_result)) {
                return FALSE;
            }
        }
        // 整理可用自取点并返回
        $self_cds = '';
        foreach ($self_cd_result as $value) {
            $self_cds .= $value['self_receive_spot_cd'].',';
        }
        return substr($self_cds, 0, strlen($self_cds)-1);
    }
    /* lyq@newland 添加结束 **/
    
    /* lyq@newland 添加开始 **/
    /* 时间：2015/11/02     **/
    /**
     * 获取用户已购买某商品的数量
     */
    public function get_goods_buyed_countOp() {
        // 获取商品ID
        $goods_id = $_POST['goods_id'];
        // 实例化订单模型
        $order_model = Model('order');
        // 查询用户购买过该商品的数量
        $buyed_num = $order_model->getProductBuyedNum($goods_id, $this->member_info['member_id']);
        // 返回数据
        output_data(array('buyed_num' => $buyed_num));
    }
    /* lyq@newland 添加结束 **/
    
     /* jys@newland 添加开始 **/
    /* 时间：2015/11/16     **/
    /**
     * 检验配送方式
     */
    public function get_typeOp(){
        $str = ""; 
        $cart_ids = explode(',', $_POST['cart_id']);
        for($i=0;$i<sizeof($cart_ids);$i++){
            $cart = explode('|',$cart_ids[$i]);
            $str .= $cart[0].',';
        }

        $type_filter_sql = 'SELECT ';
        $type_filter_sql.= '    delivery_type,';
         $type_filter_sql.= '   '.DBPRE.'cart.goods_name ';
        $type_filter_sql.= 'FROM ';
        $type_filter_sql.= '    '.DBPRE.'goods  ';
        $type_filter_sql.= ' INNER JOIN  '.DBPRE.'cart   ';
        $type_filter_sql.= ' ON  '.DBPRE.'goods.goods_id = '.DBPRE.'cart.goods_id ';
        $type_filter_sql.= ' WHERE ';
        $type_filter_sql.= '  '.DBPRE.'cart.cart_id IN ('.substr($str, 0, strlen($str)-1).') ';
        $type_filter_result = Model()->query($type_filter_sql);;
         output_data( array('type_filter_result' => $type_filter_result));
    }
    
      /* jys@newland 添加开始 **/
    /* 时间：2015/11/23    **/
    /**
     * 检验限购次数
     */
    public function get_limitOp(){
        $pay_sn= $_POST['pay_sn'];
        $limit_filter_sql = 'SELECT ';
        $limit_filter_sql.=  '   '.DBPRE.'order_goods.goods_id ,';
        $limit_filter_sql.=  '   '.DBPRE.'order_goods.goods_name ,';
        $limit_filter_sql.=  '   '.DBPRE.'order_goods.goods_num ,';
        $limit_filter_sql.=  '   '.DBPRE.'goods.purchase_limit ';
        $limit_filter_sql.= 'FROM ';
        $limit_filter_sql.= '    '.DBPRE.'order_goods  ';
        $limit_filter_sql.= ' INNER JOIN  '.DBPRE.'order   ';
        $limit_filter_sql.= ' ON  '.DBPRE.'order_goods.order_id = '.DBPRE.'order.order_id ';
        $limit_filter_sql.= ' INNER JOIN  '.DBPRE.'goods   ';
        $limit_filter_sql.= ' ON  '.DBPRE.'order_goods.goods_id = '.DBPRE.'goods.goods_id ';
        $limit_filter_sql.= ' WHERE ';
        $limit_filter_sql.= ' '.DBPRE.'order.pay_sn ='.$pay_sn;
        $resultlimit = Model()->query($limit_filter_sql);
        output_data( array('resultlimit' => $resultlimit));
    }
    
    /* jys@newland 添加开始 **/
    /* 时间：2015/11/16     **/
    /**
     * 检验配送方式
     */
    public function get_best_timeOp(){
        $str = ""; 
        $cart_ids = explode(',', $_POST['cart_id']);
        for($i=0;$i<sizeof($cart_ids);$i++){
            $cart = explode('|',$cart_ids[$i]);
            $str .= $cart[0].',';
        }
        $filter_sql = 'SELECT ';
        $filter_sql.= '   max(best_time) as best_time';
        $filter_sql.= '  FROM ';
        $filter_sql.= '    '.DBPRE.'goods  ';
        $filter_sql.= ' INNER JOIN  '.DBPRE.'cart   ';
        $filter_sql.= ' ON  '.DBPRE.'goods.goods_id = '.DBPRE.'cart.goods_id ';
        $filter_sql.= ' WHERE ';
        $filter_sql.= '  '.DBPRE.'cart.cart_id IN ('.substr($str, 0, strlen($str)-1).') ';
        $filter_result = Model()->query($filter_sql);
        output_data( array('filter_result' => $filter_result));
    }
    
}

