<?php

    require_once("wx_common.php");

    // 定义框架路径
    define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
    // 引用框架核心类
    if (!@include(dirname(dirname(__FILE__)).'/global.php')) exit('global.php isn\'t exists!');
    if (!@include(BASE_CORE_PATH.'/nl_wx_shop.php')) exit('nl_wx_shop.php isn\'t exists!');
    // 执行框架
    Base::run(FALSE);
    
    // 获取收款人信息、金额等
    $sql = '';
    $sql .= ' SELECT';
    $sql .= '	t1.extract_id,';
    $sql .= '	sum(t1.extract_money) extract_money,';
    $sql .= '	t2.member_id,';
    $sql .= '	t2.member_wx_id';
    $sql .= ' FROM';
    $sql .= '	'.DBPRE.'extract t1';
    $sql .= ' LEFT JOIN '.DBPRE.'member t2 ON t1.extract_user = t2.member_id';
    $sql .= ' WHERE';
    $sql .= '	t1.extract_flg = 1';
    $sql .= '	AND t1.extract_type = 0';   // 佣金提现 lyq@newland 添加
    $sql .= ' GROUP BY';
    $sql .= '	t2.member_id';
    // 收款信息
    $extract_info = Model()->query($sql);
    
    $transfer = new Transfer_pub();
    
    // 循环给每位用户付款
    for ($i=0;$i<count($extract_info);$i++) {
        $extract = $extract_info[$i];
        // 商户订单号
        $extract_id = $extract['extract_id'];
        $transfer->setParameter('partner_trade_no', "$extract_id");
        // 用户openid
        $member_wx_id = $extract['member_wx_id'];
        $transfer->setParameter('openid', "$member_wx_id");
        // 金额
        $extract_money = $extract['extract_money'];
        $transfer->setParameter('amount', $extract_money * 100);
        // 企业付款描述信息
        $transfer->setParameter('desc', '佣金提现');
        // 发送付款请求
        $result = $transfer->getResult();
        // 会员id
        $member_id = $extract['member_id'];
        $update_data = array();
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            // 付款成功
            $update_data['extract_flg'] = 3;
            // 支付时间
            $update_data['pay_time'] = time();
            // 支付单号
            $update_data['payment_no'] = $result['payment_no'];
            // 支付失败错误信息
            $update_data['payment_err_mes'] = '';
            // 更新表数据
            Model()->table('extract')->where(array('extract_user' => $member_id, 'extract_flg' => '1'))->update($update_data);
        } else {
        	// 支付失败错误信息
            $update_data['payment_err_mes'] = $result['err_code_des'];
            // 更新表数据
            Model()->table('extract')->where(array('extract_user' => $member_id, 'extract_flg' => '1'))->update($update_data);
        }
        
    }
    
    

