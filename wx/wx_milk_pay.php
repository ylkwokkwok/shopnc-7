<?php

    /**
     * JS_API支付demo
     * ====================================================
     * 在微信浏览器里面打开H5网页中执行JS调起支付。接口输入输出数据格式为JSON。
     * 成功调起支付需要三个步骤：
     * 步骤1：网页授权获取用户openid
     * 步骤2：使用统一支付接口，获取prepay_id
     * 步骤3：使用jsapi调起支付
     */
    include_once("wx_common.php");

    // 定义框架路径
    define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
    // 引用框架核心类
    if (!@include(dirname(dirname(__FILE__)) . '/global.php'))
        exit('global.php isn\'t exists!');
    if (!@include(BASE_CORE_PATH . '/nl_wx_shop.php'))
        exit('nl_wx_shop.php isn\'t exists!');
    // 执行框架
    Base::run(FALSE);

    // 商品总价格
    $total_fee = 0.0;
    // 用户购买月卡
    if (!empty($_POST['milk_order_datas'])) {
        foreach ($_POST['milk_order_datas'] as $order) {
            $total_fee += floatval($order['goods_price']) * intval($order['goods_num']);
        }
    }
    /* zp@newland 添加开始 **/
    /* 时间：2017/02/06 **/
    // 获取OPENID
    $condition = array('mb_user_token.token' => $_COOKIE['key']);
    $member_info = Model('mb_user_token')->getOpenId($condition);
    /* zp@newland 添加结束 **/
    // 获得用户的openid
    $openid = $member_info[0]['member_wx_id'];
    
    // 使用jsapi接口
    $jsApi = new JsApi_pub();
    $jsApi->setOpenid($openid);

    
    $order_data = $_POST;
    // 会员ID
    $order_data['member_id'] = $member_info[0]['member_id'];
    // 整理订奶记录数据
    $milk_log = array(
        'order_data' => serialize($order_data),
        'order_time' => date('Y-m-d H:i:s')
    );
    // 插入订奶记录，获取记录ID
    $log_id = Model('milk_order_log')->insert($milk_log);
    
    //=========步骤2：使用统一支付接口，获取prepay_id============
    //使用统一支付接口
    $unifiedOrder = new UnifiedOrder_pub();
    // 生成商户订单号
    $out_trade_no = $jsApi->createNoncestr(16).time();

    //设置统一支付接口参数
    //设置必填参数
    //sign已填,商户无需重复填写
    $unifiedOrder->setParameter("openid", "$openid");
    // 商品名称
    $unifiedOrder->setParameter("body", "心乐奶卡");
    // 附加数据 订奶记录ID
    $unifiedOrder->setParameter("attach", $log_id);
    // 商户订单号(支付单号)
    $unifiedOrder->setParameter("out_trade_no", $out_trade_no);
    // 总金额
    $unifiedOrder->setParameter("total_fee", $total_fee * 100);
    // 通知地址 
    $unifiedOrder->setParameter("notify_url", wx_config::MILK_NOTIFY_URL);
    // 交易类型
    $unifiedOrder->setParameter("trade_type", "JSAPI");
    // 预支付ID
    $prepay_id = $unifiedOrder->getPrepayId();
    // 设置预支付ID
    $jsApi->setPrepayId($prepay_id);

    // 获取jsapi参数
    $jsApiParameters = $jsApi->getParameters();
    // 添加记录ID
    $jsApiParameters['log_id'] = $log_id;
    
    echo json_encode($jsApiParameters);
    exit;
