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
    // 使用通用通知接口
    $notify = new Notify_pub();

    // 存储微信的回调
    $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
    $notify->saveData($xml);

    // 验证签名，并回应微信。
    // 对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
    // 微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
    // 尽可能提高通知的成功率，但微信不保证通知最终能成功。
    if ($notify->checkSign() == FALSE) {
        $notify->setReturnParameter("return_code", "FAIL"); // 返回状态码
        $notify->setReturnParameter("return_msg", "签名失败"); // 返回信息
        logResult('签名失败');
    } else {
        logResult('SUCCESS');
        $notify->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
    }
    $returnXml = $notify->returnXml();
    echo $returnXml;
    // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
    // 以log文件形式记录回调信息
    logResult("【接收到的notify通知】:\n" . $xml . "\n");
    if ($notify->checkSign() == TRUE) {
        if ($notify->data["return_code"] == "FAIL") {
            // 此处应该更新一下订单状态，商户自行增删操作
            logResult("【通信出错】:\n" . $xml . "\n");
        } elseif ($notify->data["result_code"] == "FAIL") {
            // 此处应该更新一下订单状态，商户自行增删操作
            logResult("【业务出错】:\n" . $xml . "\n");
        } else {
            // 此处应该更新一下订单状态，商户自行增删操作
            logResult("【支付成功】:\n" . $xml . "\n");

            // 接收 订奶记录ID
            $log_id = $notify->data['attach'];
            
            // 加载 订奶共通模块
            require_once("milk_order_common.php");
            // 执行系统 订奶操作
            milk_order_operations($log_id, $notify, TRUE);
        }
    }
    
    
    