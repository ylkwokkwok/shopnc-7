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
	define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
	// 引用框架核心类
	if (!@include(dirname(dirname(__FILE__)).'/global.php')) exit('global.php isn\'t exists!');
	if (!@include(BASE_CORE_PATH.'/nl_wx_shop.php')) exit('nl_wx_shop.php isn\'t exists!');
	// 执行框架
	Base::run(FALSE);
	
	// 获取订单信息
	$pay_sn = $_POST['pay_sn'];
	$sql = '';
	$sql .= ' SELECT';
	$sql .= '	sum(order_amount) pay_amount';
	$sql .= ' FROM';
	$sql .= '	'.DBPRE.'order';
	$sql .= ' WHERE';
	$sql .= '	pay_sn = '.$pay_sn;
        logResult("获取订单信息：" . $sql);
	// 订单信息
	$order_info = Model()->query($sql);
    
    
    $sql = '';
	$sql .= ' SELECT';
	$sql .= '	sum(t1.order_amount) pay_amount,';
	$sql .= '	count(*) goods_num,';
	$sql .= '	t2.goods_name';
	$sql .= ' FROM';
	$sql .= '	'.DBPRE.'order t1';
	$sql .= ' LEFT JOIN '.DBPRE.'order_goods t2 ON t1.order_id = t2.order_id';
	$sql .= ' WHERE';
	$sql .= '	t1.pay_sn = '.$pay_sn;
         logResult("订单信息：" . $sql);
    // 订单信息
	$goods_info = Model()->query($sql);
    
	
	// 获取OPENID
	$sql = '';
	$sql .= ' SELECT';
	$sql .= '	t2.member_wx_id';
	$sql .= ' FROM';
	$sql .= '	'.DBPRE.'mb_user_token t1';
	$sql .= ' LEFT JOIN '.DBPRE.'member t2 ON t1.member_id = t2.member_id';
	$sql .= ' WHERE';
	$sql .= '	t1.token = "'.$_COOKIE['key'].'"';
        logResult("获取OPENID：" . $sql);
	// 获取用户信息
	$member_info = Model()->query($sql);
    // 获得用户的openid
    $openid = $member_info[0]['member_wx_id'];
    
    // 使用jsapi接口
	$jsApi = new JsApi_pub();
    $jsApi->setOpenid($openid);
	//=========步骤2：使用统一支付接口，获取prepay_id============
	//使用统一支付接口
	$unifiedOrder = new UnifiedOrder_pub();
	
	
	//设置统一支付接口参数
	//设置必填参数
	//sign已填,商户无需重复填写
	$unifiedOrder->setParameter("openid",$openid); 
        logResult("unifiedOrder：" . $unifiedOrder);
	// 商品名称
	$goods_name = $goods_info[0]['goods_name'];
        logResult("goods_name：" . $goods_name);
    if ($goods_info[0]['goods_num'] != 1) {
        $goods_name .= ' 等';
        logResult("goods_name1：" . $goods_name);
    }
	$unifiedOrder->setParameter("body",$goods_name); 
        logResult("goods_name1：" . $goods_name);
	// 商户订单号(支付单号)
	$unifiedOrder->setParameter("out_trade_no",$pay_sn); 
        logResult("商户订单号：" . $pay_sn);
	// 总金额
	$unifiedOrder->setParameter("total_fee",$order_info[0]['pay_amount'] * 100);
         logResult("总金额：" . $order_info[0]['pay_amount'] * 100);
	// 通知地址 
	$unifiedOrder->setParameter("notify_url",wx_config::NOTIFY_URL);
         logResult("通知地址：" . wx_config::NOTIFY_URL);
	// 交易类型
	$unifiedOrder->setParameter("trade_type","JSAPI");
	// 预支付ID
	$prepay_id = $unifiedOrder->getPrepayId();
         logResult("预支付ID：" .$prepay_id);
	// 设置预支付ID
	$jsApi->setPrepayId($prepay_id);

	$jsApiParameters = $jsApi->getParameters();
	echo json_encode($jsApiParameters);exit;
?>
