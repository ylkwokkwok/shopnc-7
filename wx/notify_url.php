<?php

    /**
     * 通用通知接口demo
     * ====================================================
     * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
     * 商户接收回调信息后，根据需要设定相应的处理流程。
     * 
     * 这里举例使用log文件形式记录回调信息。
     */
    // 定义框架路径
    define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
    // 引用框架核心类
    if (!@include(dirname(dirname(__FILE__)) . '/global.php')) exit('global.php isn\'t exists!');
    if (!@include(BASE_CORE_PATH . '/nl_wx_shop.php')) exit('nl_wx_shop.php isn\'t exists!');
    // 执行框架
    Base::run(FALSE);

    require_once("wx_core.php");
    include_once("wx_common.php");

    logResult('notify_url start');
    //使用通用通知接口
    $notify = new Notify_pub();

    //存储微信的回调
    $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
    $notify->saveData($xml);

    //验证签名，并回应微信。
    //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
    //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
    //尽可能提高通知的成功率，但微信不保证通知最终能成功。
    if ($notify->checkSign() == FALSE) {
        $notify->setReturnParameter("return_code", "FAIL"); //返回状态码
        $notify->setReturnParameter("return_msg", "签名失败"); //返回信息
        logResult('签名失败');
    } else {
        logResult('SUCCESS');
        $notify->setReturnParameter("return_code", "SUCCESS"); //设置返回码
    }
    $returnXml = $notify->returnXml();
    echo $returnXml;
    //==商户根据实际情况设置相应的处理流程，此处仅作举例=======
    //以log文件形式记录回调信息
    logResult("【接收到的notify通知】:\n" . $xml . "\n");
    if ($notify->checkSign() == TRUE) {
        if ($notify->data["return_code"] == "FAIL") {
            //此处应该更新一下订单状态，商户自行增删操作
            logResult("【通信出错】:\n" . $xml . "\n");
        } elseif ($notify->data["result_code"] == "FAIL") {
            //此处应该更新一下订单状态，商户自行增删操作
            logResult("【业务出错】:\n" . $xml . "\n");
        } else {
            //此处应该更新一下订单状态，商户自行增删操作
            logResult("【支付成功】:\n" . $xml . "\n");

            $logic_payment = Logic('payment');
            // 获取支付单号
            $pay_sn = $notify->data["out_trade_no"];
            // 检索订单信息
            $result = $logic_payment->getRealOrderInfo($pay_sn);
            // 获取订单列表
            $order_list = $result['data']['order_list'];
            // 更新订单信息
            $result = $logic_payment->updateRealOrder($pay_sn, '微信支付', $order_list, $notify->data["transaction_id"]);

            /* lyq@newland 修改开始 **/
            /* 时间：2015/09/06     **/
            // 日志
            if ($result['state'] == true && $result['msg'] == '操作成功') {
                logResult("【更新订单】:成功！");
                // 根据pay_sn获取一条订单信息
                $order_info = Model()->table('order')->where('pay_sn = "' . $pay_sn . '"')->find();
                // 订单的 客户订奶支付记录ID 不为空时
                if (!empty($order_info['milk_order_log_id'])) {
                    $log_id = $order_info['milk_order_log_id'];
                    // 加载 订奶共通模块
                    require_once("milk_order_common.php");
                    // 执行系统 订奶操作
                    milk_order_operations($log_id, $notify, FALSE);
                }
                //                            // 根据微信openid和允许接收推送字段查询用户数
                //                            $member_count = Model()->table('member')
                //                                                   ->where(array(
                //                                                       'member_wx_id' => $notify->data['openid'],
                //                                                       'allow_push' => 1))
                //                                                   ->count();
                //                            if (intval($member_count) > 0) {    // 会员数大于0，则会员允许接收推送消息
                //                                // 根据付款编号获取订单编号列表
                //                                $order_sn_list = Model()->table('order')
                //                                                        ->where(array('pay_sn' => $pay_sn))
                //                                                        ->field('order_sn')
                //                                                        ->select();
                //                                // 订单编号数目大于1，则用第一个订单编号拼接‘等’，否则直接使用第一个订单编号
                //                                $order_sn = count($order_sn_list) > 1 ? $order_sn_list[0]['order_sn'] . ' 等' : $order_sn_list[0]['order_sn'] ;
                //                                // 整理模板消息需要的数据
                //                                $data = array(
                //                                    'member_wx_id' => $notify->data['openid'],                  // 接收消息方的微信openid
                //                                    'url'          => WAP_SITE_URL . '/tmpl/member/order_list.html',  // 请求发送消息的url
                //                                    'first'        => '恭喜您购买成功！',                        // 消息内容头部
                //                                    'order_sn'     => $order_sn,                                // 订单号
                //                                    'pay_amount'   => intval($notify->data['total_fee']) / 100, // 支付金额
                //                                    'remark'       => '欢迎再次购买！'                           // 消息内容尾部
                //                                );
                //                                // 载入微信消息模块
                //                                require_once("wx_message.php");
                //                                // 实例化微信消息类
                //                                $wx_msg_obj = new wx_message();
                //                                // 发送消息
                //                                $msg_response = $wx_msg_obj->send_message($data, 'pay_success');
                //                                if ($msg_response->errcode == 0) {  // 消息发送成功
                //                                    logResult("【发送消息】:成功！");
                //                                    logResult("【发送消息】:msgid:" . $msg_response->msgid);
                //                                } else {    // 消息发送失败
                //                                    logResult("【发送消息】:失败！");
                //                                    logResult("【发送消息】:errmsg:" . $msg_response->errmsg);
                //                                }
                //                            } else {    // 会员拒接接收推送消息
                //                                logResult("【发送消息】:会员拒绝接收推送消息！");
                //                            }
            } else {
                logResult("【更新订单】:失败！");
                logResult("【更新订单】:" . $result['msg']);
            }
            /* lyq@newland 修改结束 **/
        }
    }
?>