<?php

    /*
     ***********************************
     * 
     * 以下为
     *     支付回调时，插入、更新执行系统表数据
     * 主操作
     * 
     ***********************************
     */
    
    /**
     * 执行系统 订奶操作
     * @param type $log_id 记录ID
     * @param type $notify 微信支付回调数据对象
     * @param type $if_create_order 是否穿创建商城订单 TRUE：创建|FALSE：不创建
     */
    function milk_order_operations($log_id, $notify, $if_create_order) {
        // 根据订奶记录ID查询未付款订单信息
        $data = Model('milk_order_log')->where('log_id = '.$log_id.' and pay_time is null')->field('order_data')->find();

        // 奶卡分配成功标志 默认成功
        $assign_milk_card_flag = TRUE;

        // 反序列化订奶记录数据，获得订单信息
        $order_data = unserialize($data['order_data']);
        global $valid_milk_cards;
        global $seq_cd;
        try {
            // 开启事务
            Model()->beginTransaction();

            // 获取客户编号信息
            $customer_cd_info = get_customer_cd_info($order_data);

            // 已使用的奶卡列表
            $used_milk_card_list = array();
            // 需要插入订单表的数据
            $order_data_list = array();
            // 订购的奶品种类列表
            $milk_cd_list = array();
            // 获取奶卡种类对应奶卡数
            $card_type_arr = get_card_type_arr();
            // 当前时间
            $now_datetime = date('Y-m-d H:i:s');
           
            // 循环订单数据
            foreach ($order_data['milk_order_datas'] as $order) {
                $type = $order['card_type'] ;
                logResult('type：'.$type);
                // 循环奶卡单品购买数
                for ($i = 0; $i < intval($order['goods_num']); $i++) {
                    logResult('i：'.$i);
                    // 当条订单使用奶卡
                    $use_cards = array();
                    logResult('奶卡类别：'.$order['card_type']);
                    if($type =="4"){
                        logResult('type：'.$type.$i);
                        $valid_milk_cards =get_milk_card($order['milk_cd']);
                        $seq_cd = $valid_milk_cards[0]['card_seq'];
                        logResult('奶卡踩番最大号：'.$valid_milk_cards[0]['start_range']);
                        logResult('序列号：'.$valid_milk_cards[0]['card_seq']);
                    }
                    else{
                        // 获取可用奶卡区间
                        $valid_milk_cards = get_valid_milk_cards($used_milk_card_list, $order['milk_cd'], $card_type_arr[$order['card_type']]);
                        // 未取到奶卡区间时
                        if (empty($valid_milk_cards)) {
                            // 停止生成订单
                            throw new Exception('可用奶卡数量不足');
                        }
                    }
                    // 循环当前订单所需的奶卡数
                    for ($j = 0; $j < $card_type_arr[$order['card_type']]; $j++) {
                         if($order['card_type'] =="4"){
                                $use_cards[] = trim($valid_milk_cards[0]['start_range']);
                                logResult('加入当条订单使用奶卡：'.$use_cards[0].$valid_milk_cards[0]['start_range']);
                               // 加入已使用奶卡
                               $used_milk_card_list[] = trim($valid_milk_cards[0]['start_range']);
                               logResult('加入已使用奶卡：'.$used_milk_card_list[0]);
                         }else{
                                // 奶卡卡号 = 奶卡区间开始 + 偏移量
                                // 加入当条订单使用奶卡
                                $use_cards[] = intval($valid_milk_cards[0]['start_range']) + $j;
                                // 加入已使用奶卡
                                $used_milk_card_list[] = intval($valid_milk_cards[0]['start_range']) + $j;
                                
                         }
                    }
                    
                    // 加入 需要插入订单表的数据
                    $order_data_list[] = array(
                        'customer_cd' => $customer_cd_info['customer_cd'],
                        'gc_id' => $order['milk_cd'],
                        'goods_id' => $order['goods_id'],
                        'card_type' => $order['card_type'],
                        'milk_card_cd_start' => implode(',', $use_cards),
                        'order_from_flag' => '2',
                        'purchase_date' => $now_datetime,
                        'create_user' => 'wap user',
                        'create_date' => $now_datetime,
                        'update_user' => 'wap user',
                        'update_date' => $now_datetime,
                    );
                    // 如果奶品编号不在订购的奶品种类列表中
                    if (!in_array($order['milk_cd'], $milk_cd_list)) {
                        // 向列表中添加该奶品
                        $milk_cd_list[] = $order['milk_cd'];
                    }
                    if($type =="4"){
                        insert_order($customer_cd_info, $order_data_list, $use_cards, $order_data,$order['milk_cd']);
                       logResult('执行次数:'.$i);
                    }
                }
            }
            
            logResult('执行系统订单数据：'.serialize($order_data_list)); 
            // 插入&更新数据
            update_order_datas($customer_cd_info, $order_data_list, $used_milk_card_list, $order_data);

            // 提交事务
            Model()->commit();
            logResult('订奶成功！客户编号【'.$customer_cd_info['customer_cd'].'】，微信商户订单号【'.$notify->data['out_trade_no'].'】');
        } catch (Exception $e) {
            // 回滚事务
            Model()->rollback();
            // 新增通知
            insert_notice($log_id, $notify->data['out_trade_no'], $order_data['self_receive_spot_cd']);
            // 奶卡分配失败
            $assign_milk_card_flag = FALSE;
            logResult('订奶失败！记录ID【'.$log_id.'】，微信商户订单号【'.$notify->data['out_trade_no'].'】');
        }

        // 需要生成订单信息
        if ($if_create_order) {
            // 生成商城订单记录
            $pay_sn = create_order_info($order_data, $notify);
        }
        /* lyq@newland 修改开始 **/
        /* 时间：2015/09/18     **/
        // 自取订奶时
        if (!empty($order_data['self_receive_spot_cd'])) {
            // 模拟完成订单操作
            finish_order(isset($pay_sn)?$pay_sn:$notify->data["out_trade_no"]);
        }
        /* lyq@newland 修改结束 **/
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/10/13     **/
        // 更新订单中的自取点相关信息
        update_order_self_info(isset($pay_sn)?$pay_sn:$notify->data["out_trade_no"], $log_id, $order_data['self_receive_spot_cd'], $used_milk_card_list,$order_data['remark']);
        /* lyq@newland 添加结束 **/

        // 需要更新的订奶记录数据
        $log_upd_info = array('pay_time' => date('Y-m-d H:i:s'));
        // 奶卡分配成功时
        if ($assign_milk_card_flag) {
            $log_upd_info['assign_flag'] = '1'; // 奶卡分配标识 1：已分配
        }
        // 更新订奶记录的付款时间
        Model('milk_order_log')->where('log_id = '.$log_id.' and pay_time is null')->update($log_upd_info);

        // 发送消息相关操作
        // 客户不需要配送时
        if (empty($order_data['address'])) {
            $member_info = Model('member')->where('member_wx_id = "'.$notify->data['openid'].'"')->find();
            // 载入微信消息模块
            require_once("wx_message.php");
            // 实例化微信消息类
            $wx_msg_obj = new wx_message();
            // 奶卡分配成功时
            if ($assign_milk_card_flag) {
                // 循环已订购的奶品种类
                foreach ($milk_cd_list as $milk_cd) {
                    logResult("奶品编号：" . $milk_cd);
                    // 根据奶品编号（分类编号）查询分类信息
                    $gc_info = Model('goods_class')->where('gc_id = "'.$milk_cd.'"')->find();
                    // 整理模板消息需要的数据
                    $data = array(
                        'member_wx_id' => $notify->data['openid'],                  // 接收消息方的微信openid
                        'url'          => 'http://shopnc.siburuxue.org/selfTakeMilkSpot/common.do?method=getMilkCard'
                                            . '&customerCd='.$customer_cd_info['customer_cd'].'&milkType='.$milk_cd,  // 点击消息时跳转的url
                        'first'        => '恭喜您成功订购【'.$gc_info['gc_name'].'】！',   // 消息内容头部
                        'keyword1'     => $customer_cd_info['customer_cd'],         // 会员（客户）编号
                        'keyword2'     => $member_info['member_name'],              // 微信昵称
                        'keyword3'     => date('Y年m月d日 H:i:s'),                  // 时间
                        'remark'       => '请点击此消息获取奶卡信息。'               // 消息内容尾部
                    );
                    // 发送消息
                    $msg_response = $wx_msg_obj->send_message($data, 'pay_milk_success');
                    // 消息发送成功
                    if ($msg_response->errcode == 0) {
                        logResult("【发送消息】:成功！msgid:" . $msg_response->msgid);
                    }
                    // 消息发送失败
                    else {
                        logResult("【发送消息】:失败！errmsg:" . $msg_response->errmsg);
                    }
                }
            }
            // 奶卡分配失败时
            else {
                // 整理模板消息需要的数据
                $data = array(
                    'member_wx_id' => $notify->data['openid'],      // 接收消息方的微信openid
                    'first'        => '恭喜您成功订购心乐奶卡！',     // 消息内容头部
                    'keyword1'     => '无',                         // 会员（客户）编号
                    'keyword2'     => $member_info['member_name'],  // 微信昵称
                    'keyword3'     => date('Y年m月d日 H:i:s'),      // 时间
                    'remark'       => '由于系统原因，奶卡分配失败，稍后工作人员将与您联系，请耐心等待。'
                                    . '如有疑问，请拨打客服电话【400-811-8333】，将记录编号【'.$log_id.'】告知客服。'   // 消息内容尾部
                );
                // 发送消息
                $msg_response = $wx_msg_obj->send_message($data, 'pay_milk_success');
                // 消息发送成功
                if ($msg_response->errcode == 0) {
                    logResult("【发送消息】:成功！msgid:" . $msg_response->msgid);
                }
                // 消息发送失败
                else {
                    logResult("【发送消息】:失败！errmsg:" . $msg_response->errmsg);
                }
            }
        } else {
            logResult("【发送消息】:客户需要配送奶卡，不用发送消息。");
        }
    }
    
    /*
     ***********************************
     * 
     * 以下为
     *     插入、更新 执行系统数据
     * 相关操作
     * 
     ***********************************
     */
    
    /**
     * 获取客户编号信息
     * @param array 订单信息 
     * @return array need_insert：是否需要新增客户，customer_cd：客户编号
     */
    function get_customer_cd_info($milk_order_data) {
        $condition = array(
            'member_id' => $milk_order_data['member_id'],
            'customer_name' => $milk_order_data['name'],
            'address' => $milk_order_data['address'],
            'tel' => $milk_order_data['tel'],
            'delete_flag' => 0,
        );
        if ($milk_order_data['self_receive_spot_cd'] !== '') {
            // 自取点编号不为空时
            $condition['customer_cd'] = array(array('like',$milk_order_data['self_receive_spot_cd'].'%'));
        }else {
            // 自取点编号为空时（到户）
            $condition['customer_cd'] = array(array('like','DH%'));
        }
        $result = Model('mst_customer')->get_milk_order_info($condition);
        // 默认不需要新增客户信息
        $ret_arr['need_insert'] = FALSE;

        // 未查询到客户信息
        if (empty($result)) {
            // 需要新增客户信息
            $ret_arr['need_insert'] = TRUE;
            // 创建新客户编号
            $ret_arr['customer_cd'] = create_customer_cd($milk_order_data);
        } else {
            // 查询到的客户编号
            $ret_arr['customer_cd'] = $result[0]['customer_cd'];
        }
        // 返回客户编号信息
        return $ret_arr;
    }
    
    /**
     * 构造新客户编号
     * @param array $milk_order_data 订单信息
     * @return string 客户编号
     */
    function create_customer_cd($milk_order_data) {
        // 自取点编号不为空时(自取)
        if ($milk_order_data['self_receive_spot_cd'] !== '') {
            $condition = array(array('like',$milk_order_data['self_receive_spot_cd'].'%'));
            $result = Model('mst_customer')->get_milk_order_info($condition);

            // 未查询到客户信息
            if (empty($result)) {
                return $milk_order_data['self_receive_spot_cd'].'0001';
            }
            // 查询到 自取 客户信息
            else {
                $max_cd = $result[0]['customer_cd'];
                $max_num = str_replace($milk_order_data['self_receive_spot_cd'], '', $max_cd);
                $new_num = str_pad(intval($max_num)+1,4,'0',STR_PAD_LEFT);
                return $milk_order_data['self_receive_spot_cd'].$new_num;
            }
        }
        // 自取点编号为空时（到户）
        else {
            $condition = array(array('like','DH%'));
            $result = Model('mst_customer')->get_milk_order_info($condition);

            // 未查询到客户信息
            if (empty($result)) {
                return 'DH00000001';
            }
            // 查询到 到户 客户信息
            else {
                $max_cd = $result[0]['customer_cd'];
                $max_num = str_replace('DH', '', $max_cd);
                $new_num = str_pad(intval($max_num)+1,8,'0',STR_PAD_LEFT);
                return 'DH'.$new_num;
            }
        }
    }
    
    /**
     * 新增客户
     * @param type $milk_order_data 订单信息
     * @param type $customer_cd 客户编号
     */
    function insert_customer($milk_order_data, $customer_cd) {
        // 到户客户
        if (empty($milk_order_data['self_receive_spot_cd'])) {
            // 链接促销系统数据库
            $connect = mysqli_connect('inxinleshop.mysql.rds.aliyuncs.com','wx_shop','yj1fS7eSd1','promotion_db');
            // 设置utf8编码
            mysqli_query($connect, 'SET NAMES utf8');
            // 将客户数据存入促销系统
            $sql = 'INSERT INTO `mst_customer` ( ';
            $sql.= '    customer_cd, ';         // 客户编号
            $sql.= '    customer_name, ';       // 客户名
            $sql.= '    member_id, ';           // 会员ID
            $sql.= '    address, ';             // 客户详细地址
            $sql.= '    tel, ';                 // 联系电话
            $sql.= '    order_from_flag, ';     // 订单来源
            $sql.= '    create_user, ';         // 作成者
            $sql.= '    create_date, ';         // 作成日时
            $sql.= '    update_user,';          // 更新者
            $sql.= '    update_date ';          // 更新日时
            $sql.= ') ';
            $sql.= 'VALUES ';
            $sql.= '    (';
            $sql.= '        "'.$customer_cd.'", ';
            $sql.= '        "'.$milk_order_data['name'].'", ';
            $sql.= '        "'.$milk_order_data['member_id'].'", ';
            $sql.= '        "'.(empty($milk_order_data['address'])?'':$milk_order_data['address']).'", ';
            $sql.= '        "'.$milk_order_data['tel'].'", ';
            $sql.= '        "2", ';
            $sql.= '        "wap user", ';
            $sql.= '        "'.date('Y-m-d H:i:s').'", ';
            $sql.= '        "wap user", ';
            $sql.= '        "'.date('Y-m-d H:i:s').'" ';
            $sql.= '    )';
            logResult('insert cxsys customer sql:'.$sql);
            // 插入客户信息数据
            mysqli_query($connect, $sql);

            $sql = 'INSERT INTO `mst_log_customer` ( ';
            $sql.= '    version_cd, ';          // 版本号
            $sql.= '    customer_cd, ';         // 客户编号
            $sql.= '    customer_name, ';       // 客户名
            $sql.= '    member_id, ';           // 会员ID
            $sql.= '    address, ';             // 客户详细地址
            $sql.= '    tel, ';                 // 联系电话
            $sql.= '    order_from_flag, ';     // 订单来源 2:客户
            $sql.= '    create_user, ';         // 作成者
            $sql.= '    create_date, ';         // 作成日时
            $sql.= '    update_user,';          // 更新者
            $sql.= '    update_date ';          // 更新日时
            $sql.= ') ';
            $sql.= 'VALUES ';
            $sql.= '    (';
            $sql.= '        "0", ';
            $sql.= '        "'.$customer_cd.'", ';
            $sql.= '        "'.$milk_order_data['name'].'", ';
            $sql.= '        "'.$milk_order_data['member_id'].'", ';
            $sql.= '        "'.(empty($milk_order_data['address'])?'':$milk_order_data['address']).'", ';
            $sql.= '        "'.$milk_order_data['tel'].'", ';
            $sql.= '        "2", ';
            $sql.= '        "wap user", ';
            $sql.= '        "'.date('Y-m-d H:i:s').'", ';
            $sql.= '        "wap user", ';
            $sql.= '        "'.date('Y-m-d H:i:s').'" ';
            $sql.= '    )';
            logResult('insert cxsys log_customer sql:'.$sql);
            // 插入客户信息履历数据
            mysqli_query($connect, $sql);
            // 关闭数据库连接
            mysqli_close($connect);
        }
        // 将客户数据存入执行系统
        $sql = 'INSERT INTO `mst_customer` ( ';
        $sql.= '    customer_cd, ';         // 客户编号
        $sql.= '    customer_name, ';       // 客户名
        $sql.= '    customer_type, ';       // 客户区分 0:自取 1:到户
        $sql.= '    member_id, ';           // 会员ID
        $sql.= '    address, ';             // 客户详细地址
        $sql.= '    tel, ';                 // 联系电话
        $sql.= '    self_receive_spot_cd, ';// 自取点编号
        $sql.= '    create_user, ';         // 作成者
        $sql.= '    create_date, ';         // 作成日时
        $sql.= '    update_user,';          // 更新者
        $sql.= '    update_date ';          // 更新日时
        $sql.= ') ';
        $sql.= 'VALUES ';
        $sql.= '    (';
        $sql.= '        "'.$customer_cd.'", ';
        $sql.= '        "'.$milk_order_data['name'].'", ';
        $sql.= '        "'.(empty($milk_order_data['self_receive_spot_cd'])?'1':'0').'", ';
        $sql.= '        "'.$milk_order_data['member_id'].'", ';
        $sql.= '        "'.(empty($milk_order_data['address'])?'':$milk_order_data['address']).'", ';
        $sql.= '        "'.$milk_order_data['tel'].'", ';
        $sql.= '        "'.(empty($milk_order_data['self_receive_spot_cd'])?'':$milk_order_data['self_receive_spot_cd']).'", ';
        $sql.= '        "wap user", ';
        $sql.= '        "'.date('Y-m-d H:i:s').'", ';
        $sql.= '        "wap user", ';
        $sql.= '        "'.date('Y-m-d H:i:s').'" ';
        $sql.= '    )';
        logResult('insert zxsys customer sql:'.$sql);
        // 插入客户信息数据
        Model()->execute($sql);

        $sql = 'INSERT INTO `mst_log_customer` ( ';
        $sql.= '    version_cd, ';          // 版本号
        $sql.= '    customer_cd, ';         // 客户编号
        $sql.= '    customer_name, ';       // 客户名
        $sql.= '    customer_type, ';       // 客户区分 0:自取 1:到户
        $sql.= '    member_id, ';           // 会员ID
        $sql.= '    address, ';             // 客户详细地址
        $sql.= '    tel, ';                 // 联系电话
        $sql.= '    self_receive_spot_cd, ';// 自取点编号
        $sql.= '    order_from_flag, ';     // 订单来源 2:客户
        $sql.= '    create_user, ';         // 作成者
        $sql.= '    create_date, ';         // 作成日时
        $sql.= '    update_user,';          // 更新者
        $sql.= '    update_date ';          // 更新日时
        $sql.= ') ';
        $sql.= 'VALUES ';
        $sql.= '    (';
        $sql.= '        "0", ';
        $sql.= '        "'.$customer_cd.'", ';
        $sql.= '        "'.$milk_order_data['name'].'", ';
        $sql.= '        "'.(empty($milk_order_data['self_receive_spot_cd'])?'1':'0').'", ';
        $sql.= '        "'.$milk_order_data['member_id'].'", ';
        $sql.= '        "'.(empty($milk_order_data['address'])?'':$milk_order_data['address']).'", ';
        $sql.= '        "'.$milk_order_data['tel'].'", ';
        $sql.= '        "'.(empty($milk_order_data['self_receive_spot_cd'])?'':$milk_order_data['self_receive_spot_cd']).'", ';
        $sql.= '        "2", ';
        $sql.= '        "wap user", ';
        $sql.= '        "'.date('Y-m-d H:i:s').'", ';
        $sql.= '        "wap user", ';
        $sql.= '        "'.date('Y-m-d H:i:s').'" ';
        $sql.= '    )';
        logResult('insert zxsys log_customer sql:'.$sql);
        // 插入客户信息履历数据
        Model()->execute($sql);
    }
    
    /**
     * 获取奶卡种类对应奶卡数
     * @return type
     */
    function get_card_type_arr() {
        return array(
            0 => 1, // 月卡：1张
            1 => 3, // 季卡：3张
            2 => 6, // 半年卡：6张
            3 => 12,// 年卡：12张
            4 => 1  //周卡：1张
        );
    }
    
    /**
     * 获取可用奶卡区间
     * @param type $used_milk_card_list 已分配的奶卡列表
     * @param type $milk_cd 奶品编号
     * @param type $card_num 需要分配的奶卡数量
     * @return type 可用奶卡区间信息
     */
    function get_valid_milk_cards($used_milk_card_list, $milk_cd, $card_num) {
        return Model('mst_customer_card')->get_valid_milk_cards($used_milk_card_list, $milk_cd, $card_num);
    }
    
    /**
     * 插入&更新操作
     *   插入客户信息（非必须）
     *   插入订单信息（必须）
     *   更新奶卡信息（必须）
     * @param type $customer_cd_info 客户编号信息
     * @param type $order_data_list 需要插入的订单数据
     * @param type $used_milk_card_list 已使用的奶卡列表
     * @param type $order_data 订单数据
     */
    function update_order_datas($customer_cd_info, $order_data_list, $used_milk_card_list, $order_data) {
        // 如果需要插入客户信息
        if ($customer_cd_info['need_insert']) {
            // 插入客户信息
            insert_customer($order_data, $customer_cd_info['customer_cd']);
        }
        
        // 循环 需要插入的订单数据
        foreach ($order_data_list as $order) {
            // 插入订单信息
                  insert_milk_order($order, $order_data);
        } 
         foreach ($order_data['milk_order_datas'] as $order) {
        if($order['card_type'] !="4"){
            // 更新奶卡信息
             update_milk_card_info($used_milk_card_list, $customer_cd_info['customer_cd'], $order_data);
        }
    }
  }
    
   function insert_order($customer_cd_info, $order_data_list, $used_milk_card_list, $order_data,$milk_cd){    
           try {
                inset_milk_card($used_milk_card_list, $customer_cd_info['customer_cd'],$milk_cd );
                 // 提交事务
                  Model()->commit();
                  $seq_cd =get_milk_card($milk_cd);
                  update_increment($milk_cd,$seq_cd[0]['card_seq']);
                  logResult('更新奶品:'.$milk_cd.$seq_cd[0]['card_seq']);
              } catch (Exception $e) {
                 // 回滚事务
                 Model()->rollback();
              }
   }
    
    /**
     * 插入订单信息
     * @param type $order 需要插入的订单数据
     * @param type $order_data 订单数据
     */
    function insert_milk_order($order, $order_data) {
        // 插入订单信息
        Model('trn_milk_order')->insertAction($order);
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/10/15     **/
        // 到户订奶，订单信息添加到促销系统的到户订单表中
        if (empty($order_data['self_receive_spot_cd'])) {
            // 促销系统sql头
            $sql_tohome = 'INSERT INTO `trn_milk_distribution_order` ( ';
            // 执行系统sql
            $sql = '    customer_cd, ';         // 客户编号
            $sql.= '    gc_id, ';               // 商品分类
            $sql.= '    goods_id, ';            // 商品ID
            $sql.= '    card_type, ';           // 奶卡种类
            $sql.= '    milk_card_cd_start, ';  // 奶卡编号
            $sql.= '    order_from_flag, ';     // 订单来源
            $sql.= '    purchase_date, ';       // 购买日期
            $sql.= '    create_user, ';         // 作成者
            $sql.= '    create_date, ';         // 作成日时
            $sql.= '    update_user, ';         // 更新者
            $sql.= '    update_date ';          // 更新日时
            $sql.= ') ';
            $sql.= 'VALUES ';
            $sql.= '    (';
            $sql.= '        "'.$order['customer_cd'].'", ';
            $sql.= '        "'.$order['gc_id'].'", ';
            $sql.= '        "'.$order['goods_id'].'", ';
            $sql.= '        "'.$order['card_type'].'", ';
            $sql.= '        "'.$order['milk_card_cd_start'].'", ';
            $sql.= '        "'.$order['order_from_flag'].'", ';
            $sql.= '        "'.$order['purchase_date'].'", ';
            $sql.= '        "'.$order['create_user'].'", ';
            $sql.= '        "'.$order['create_date'].'", ';
            $sql.= '        "'.$order['update_user'].'", ';
            $sql.= '        "'.$order['update_date'].'" ';
            $sql.= '    )';
            logResult('insert milk_distribution_order sql:'.$sql_tohome.$sql);
            // 链接促销系统数据库
            $connect = mysqli_connect('inxinleshop.mysql.rds.aliyuncs.com','wx_shop','yj1fS7eSd1','promotion_db');
            // 设置utf8编码
            mysqli_query($connect, 'SET NAMES utf8');
            // 插入订单信息
            mysqli_query($connect, $sql_tohome.$sql);
            // 关闭数据库连接
            mysqli_close($connect);
        }
        /* lyq@newland 添加结束 **/
    }
    
    /**
     * 更新奶卡信息
     * @param type $used_milk_card_list 奶卡编号列表
     * @param type $customer_cd 客户编号
     * @param type $order_data 订单数据
     */
    function update_milk_card_info($used_milk_card_list, $customer_cd, $order_data) {
        $array = array();
        $array['milk_card_flag'] = (empty($order_data['self_receive_spot_cd'])?'1':'0');
        $array['active_flag'] = 1;
        $array['customer_cd'] = $customer_cd;
        $array['update_user'] = "wap user";
        $array['update_date'] = date('Y-m-d H:i:s');
        $array['milk_card_cd'] = array('in',implode(',', $used_milk_card_list));
        $array['post_flag'] = (empty($order_data['address']) ? 1 : 0);
        Model('mst_customer_card')->update($array);
        /* lyq@newland 添加开始 **/
        /* 时间：2015/10/15     **/
        // 到户订奶，奶卡信息添加到促销系统的奶卡表中
        if (empty($order_data['self_receive_spot_cd'])) {
            $condition = array('milk_card_cd' => array('in',implode(',', $used_milk_card_list)));
            $milk_card_list = Model('mst_customer_card')->queryAllItem($condition);
            // 查询结果不为空
            if (!empty($milk_card_list)) {
                // 循环查询到的奶卡列表
                foreach ($milk_card_list as $milk_card) {
                    // 将奶卡信息添加到促销系统的奶卡表中
                    insert_milk_card_to_promotion_db($milk_card);
                }
            }
        }
        /* lyq@newland 添加结束 **/
    }
    
    /* lyq@newland 添加开始 **/
    /* 时间：2015/10/15     **/
    /**
     * 插入奶卡信息到促销系统中
     * @param type $milk_card 奶卡信息
     */
    function insert_milk_card_to_promotion_db($milk_card) {
        $sql = 'INSERT INTO `mst_milk_card` ';
        $sql.= 'VALUES ';
        $sql.= '    (';
        $sql.= '        "'.$milk_card['milk_card_cd'].'", ';
        $sql.= '        "'.$milk_card['milk_cd'].'", ';
        $sql.= '        "'.$milk_card['milk_num'].'", ';
        $sql.= '        "'.$milk_card['milk_surplus_num'].'", ';
        $sql.= '        "'.$milk_card['milk_card_flag'].'", ';
        $sql.= '        "'.$milk_card['start_date'].'", ';
        $sql.= '        "'.$milk_card['end_date'].'", ';
        $sql.= '        "'.$milk_card['active_flag'].'", ';
        $sql.= '        "'.$milk_card['print_flag'].'", ';
        $sql.= '        "'.$milk_card['remind_flag'].'", ';
        $sql.= '        "'.$milk_card['post_flag'].'", ';
        $sql.= '        "'.$milk_card['customer_cd'].'", ';
        $sql.= '        "'.$milk_card['create_card_user'].'", ';
        $sql.= '        "'.$milk_card['first_receive_user'].'", ';
        $sql.= '        "'.$milk_card['receive_user'].'", ';
        $sql.= '        "'.$milk_card['owner_user'].'", ';
        $sql.= '        "'.$milk_card['create_user'].'", ';
        $sql.= '        "'.$milk_card['create_date'].'", ';
        $sql.= '        "'.$milk_card['update_user'].'", ';
        $sql.= '        "'.$milk_card['update_date'].'", ';
        $sql.= '        "'.$milk_card['delete_staff'].'", ';
        $sql.= '        "'.$milk_card['delete_date'].'", ';
        $sql.= '        "'.$milk_card['delete_flag'].'" ';
        $sql.= '    )';
        logResult('insert mst_milk_card FOR promotion_db sql:'.$sql);
        // 链接促销系统数据库
        $connect = mysqli_connect('inxinleshop.mysql.rds.aliyuncs.com','wx_shop','yj1fS7eSd1','promotion_db');
        // 设置utf8编码
        mysqli_query($connect, 'SET NAMES utf8');
        // 插入奶卡信息
        mysqli_query($connect, $sql);
        // 关闭数据库连接
        mysqli_close($connect);
    }
    /* lyq@newland 添加结束 **/
    
    /**
     * 新增通知
     * @param type $log_id 记录ID
     * @param type $out_trade_no 微信支付：商户订单号
     * @param type $self_receive_spot_cd 自取点编号
     */
    function insert_notice($log_id, $out_trade_no, $self_receive_spot_cd) {
        $data = array(
            'info_title' => "奶卡数量不足，订单相关信息更新失败",
            'info_content' => "客户微信支付成功，但由于奶卡数量不足，无法更新订单相关信息。\n微信支付商户订单号：'.$out_trade_no.'\n记录ID：'.$log_id.'",
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => "9999-12-31 23:59:59",
            'info_flag' => "4",
            'new_flag' => "0",
            'milk_station_no' => (empty($self_receive_spot_cd)?'':$self_receive_spot_cd),
            'user_type' => "0",
            'create_user' => "wap user",
            'create_date' => date('Y-m-d H:i:s'),
            'update_user' => "wap user",
            'update_date' => date('Y-m-d H:i:s')
        );
        $model = Model('mst_notice');
        $model->table_prefix = '';
        $model->insert($data);
    }
    
    
    /*
     ***********************************
     * 
     * 以下为
     *     生成 商城订单记录
     * 相关操作
     * 
     ***********************************
     */
    
    /**
     * 生成商城订单记录
     * @param type $order_data 订单数据（订奶）
     * @param type $notify 微信支付回调数据
     * @return type 支付单号
     */
    function create_order_info($order_data, $notify) {
        // 购物车信息
        $cart_list = array();
        // 循环订单数据
        foreach ($order_data['milk_order_datas'] as $order) {
            // 添加购物车信息
            $cart_list[] = add_cart($order_data['member_id'], $order['goods_id'], $order['goods_num']);
        }
        // 获取 生成商城订单所需数据
        $shop_order_data = buy_step1($cart_list, $order_data['member_id']);
        logResult("【shop_order_data】:" . serialize($shop_order_data) . "");
        // 生成订单，获取生成的订单信息
        $shop_order_info = buy_step2($shop_order_data, $order_data);
        logResult("【shop_order_info】:" . serialize($shop_order_info) . "");
        // 模拟wap端支付成功回调中的操作
        $pay_result = pay_success($shop_order_info['data']['pay_sn'], $notify);
        logResult("【pay_success】:" . serialize($pay_result) . "");
        // 返回支付单号
        return $shop_order_info['data']['pay_sn'];
    }
    
    /**
     * 添加购物车信息
     * @param type $member_id 会员ID
     * @param type $goods_id 商品ID
     * @param type $goods_num 商品数量
     * @return type 购物车ID|商品数量
     */
    function add_cart($member_id, $goods_id, $goods_num) {
        $goods_info = Model()->table('goods')->where('goods_id = "'.$goods_id.'"')->find();
        $array    = array();
        $array['buyer_id']	  = $member_id;
        $array['store_id']	  = $goods_info['store_id'];
        $array['store_name']  = $goods_info['store_name'];
        $array['goods_id']	  = $goods_id;
        $array['goods_name']  = $goods_info['goods_name'];
        $array['goods_price'] = $goods_info['goods_price'];
        $array['goods_image'] = $goods_info['goods_image'];
        $array['goods_num']   = $goods_num;
        $array['bl_id']       = 0;
        $cart_id = Model()->table('cart')->insert($array);
        return $cart_id.'|'.$goods_num;
    }
    
    /**
     * 获取 生成商城订单所需数据
     *   模拟wap端buy_step1
     * @param type $cart_list 购物车列表
     * @param type $member_id 会员ID
     * @return type 生成商城订单所需数据
     */
    function buy_step1($cart_list, $member_id) {
        $logic_buy = logic('buy');
        
        // 读取卖家信息
        $seller_info = Model('seller')->getSellerInfo(array('member_id'=>$member_id));

        // 得到购买数据
        $buy_step1_data = $logic_buy->buyStep1($cart_list, 1, $member_id, $seller_info['store_id']);
        
        $shop_order_data['vat_hash'] = $buy_step1_data['data']['vat_hash'];
        // 推广积分与现金比例
        $shop_order_data['points_cash_ratio'] = intval(C('points_cash_ratio'));     
        // 推广积分订单抵扣比例
        $shop_order_data['order_cash_ratio']  = floatval(C('order_cash_ratio')) / 100;     
        
        $address_data = $logic_buy->changeAddr($buy_step1_data['data']['freight_list'], '108', '1532', $member_id);
        $shop_order_data['offpay_hash'] = $address_data['offpay_hash'];
        $shop_order_data['offpay_hash_batch'] = $address_data['offpay_hash_batch'];
        $shop_order_data['cart_id'] = $cart_list;
        // 返回 生成商城订单所需数据
        return $shop_order_data;
    }
    
    /**
     * 生成订单
     *   返回商城订单信息
     *   模拟wap端buy_step2
     * @param type $shop_order_data 生成商城订单所需数据
     * @param type $order_data 订单数据（订奶）
     * @return type 购物车ID|商品数量
     */
    function buy_step2($shop_order_data, $order_data) {
        $logic_buy = logic('buy');
        
        // 追加 生成商城订单所需数据
        $shop_order_data['ifcart']      = '1';
        $shop_order_data['address_id']  = 'no_need';
        $shop_order_data['pay_name']    = 'online';
        $shop_order_data['invoice_id']  = '';
        $shop_order_data['voucher']     = array(''=>'');
        $shop_order_data['pd_pay']      = '0';
        $shop_order_data['rcb_pay']     = '0';
        $shop_order_data['password']    = NULL;
        $shop_order_data['fcode']       = NULL;
        $shop_order_data['order_from']  = 2;
        $shop_order_data['extend_points'] = 0;
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/09/18     **/
        // 自取点编号
        $shop_order_data['self_receive_spot_cd'] = empty($order_data['self_receive_spot_cd'])?'':$order_data['self_receive_spot_cd'];
        // 客户名称
        $shop_order_data['name'] = $order_data['name'];
        // 客户电话
        $shop_order_data['tel'] = $order_data['tel'];
        // 邮寄地址
        $shop_order_data['address'] = empty($order_data['address'])?'':$order_data['address'];
        /* lyq@newland 添加结束 **/
        
        // 根据会员ID获取会员信息
        $member_info = Model()->table('member')->where('member_id = "'.$order_data['member_id'].'"')->find();
        // 生成订单 返回商城订单信息
        return $logic_buy->buyStep2($shop_order_data, $order_data['member_id'], $member_info['member_name'], $member_info['member_email']);
    }
    
    /**
     * 模拟wap端支付成功回调中的操作
     * @param type $pay_sn 支付单号
     * @param type $notify 微信支付回调数据
     */
    function pay_success($pay_sn, $notify) {
        $logic_payment = Logic('payment');
        // 检索订单信息
        $order_info = $logic_payment->getRealOrderInfo($pay_sn);
        // 获取订单列表
        $order_list = $order_info['data']['order_list'];
        // 更新订单信息
        
        /* zz@newland.com 修改开始 */
        /* 2016.4.19 */
        /* 调用updateRealOrder方法中$pay_sn参数没有被应用，所以我将该参数更名为$milk_card并应用在判断该操所是不是mulk_card页面操作    */
        $milk_card = 'milk_card';
        return $logic_payment->updateRealOrder($milk_card, '微信支付', $order_list, $notify->data["transaction_id"]);
        /* zz@newland.com 修改结束 */
    }
    
    /**
     * 模拟完成订单操作
     * @param type $pay_sn 支付单号
     */
    function finish_order($pay_sn) {
        $model_order = Model('order');

        $update_order = array();
        // 订单完成时间
        $update_order['finnshed_time'] = TIMESTAMP;
        // 订单状态：已完成
        $update_order['order_state'] = ORDER_STATE_SUCCESS;
        // 截止日：无（无法退货）
        $update_order['delay_time'] = 0;
        // 评价状态 2已过期未评价
        $update_order['evaluation_state'] = 2;
        // 更新订单状态
        $model_order->editOrder($update_order,array('pay_sn'=>$pay_sn));
    }
    
    /* lyq@newland 添加开始 **/
    /* 时间：2015/10/13     **/
    /**
     * 更新订单中的自取点相关信息
     * @param type $pay_sn 支付单号
     * @param type $log_id 客户订奶支付记录ID
     * @param type $self_receive_spot_cd 自取点编号
     * @param type $used_milk_card_list 已使用的奶卡列表
     */
    function update_order_self_info($pay_sn, $log_id, $self_receive_spot_cd, $used_milk_card_list,$remark) {
        $model_order = Model('order');
        logResult("【remark】:" .$remark );
        $update_order = array();
        // 客户订奶支付记录ID
        $update_order['milk_order_log_id'] = $log_id;
        //备注
        $update_order['remark'] = $remark;
        // 自取点编号
        $update_order['self_receive_spot_cd'] = empty($self_receive_spot_cd)?'':$self_receive_spot_cd;
        // 
        if (!empty($used_milk_card_list)) {
            $update_order['milk_card_list'] = implode(',', $used_milk_card_list);
        }
        // 更新订单
        $model_order->editOrder($update_order,array('pay_sn'=>$pay_sn));
    }
       /* lyq@newland 添加结束 **/
    
     /* jys@newland 添加开始 **/
    /*
     * 周卡奶卡号查询
     * 
     */
    function get_milk_card($milk_cd){
        $condition = array(
            'delete_flag' => 0,
            'milk_cd' => $milk_cd,
        );
        return Model('mst_card_increment')->get_milk_card($condition);
    }
     /* jys@newland 添加开始 **/
    
     /* jys@newland 添加开始 **/
    /*
     * 插卡奶卡基本信息表
     * 
     */
    function inset_milk_card($used_milk_card_list, $customer_cd, $milkcd){
        $data = array(
            'milk_card_cd' => trim($used_milk_card_list[0]),
            'milk_cd' => $milkcd,
            'milk_num' => 10,
            'milk_surplus_num' => 10,
            'milk_card_flag' => 0,
            'active_flag' => 1,
            'print_flag' => 0,
            'post_flag' => 2,
            'remind_flag' => 0,
            'customer_cd' => $customer_cd,
            'create_user' => "wap user",
            'create_date' => date('Y-m-d H:i:s'),
            'update_user' => "wap user",
            'update_date' => date('Y-m-d H:i:s'),
        );
        $model = Model('mst_milk_card');
        $model->table_prefix = '';
        $model->insert($data);
    }
    
    function update_increment($milk_cd,$card_seq){
        $para = array(
            'card_seq' => $card_seq,
            'update_user' => "wap user",
            'update_date' => date('Y-m-d H:i:s'),
            'milk_cd' => $milk_cd,
        );
        $model = Model(mst_card_increment);
        $model->table_prefix = '';
        $model->update($para);
    }
     /* jys@newland 添加开始 **/
 