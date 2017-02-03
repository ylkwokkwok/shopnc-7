<?php

/**
 * Description of wx_config
 *
 * @author HaoLiang
 */
class wx_config {
    //=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = 'wxbf22b887fc929ff8';
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = '7e63f515e4a3be27937b8dbb8164434a';
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	const KEY = '3KaUmKqecIhqoORGLTcLn2P9kTXzq6q6';
	//受理商ID，身份标识
	const MCHID = '1261541601';
	
	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = '/var/www/html/shopnc_new/wx/cert_list/apiclient_cert.pem';
	const SSLKEY_PATH = '/var/www/html/shopnc_new/wx/cert_list/apiclient_key.pem';
	
	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	const NOTIFY_URL = 'http://fresh.cenler-shop.com/wx/notify_url.php';
        
	const MILK_NOTIFY_URL = 'http://fresh.cenler-shop.com/wx/milk_notify_url.php';
	
	const PAY_URL = 'http://fresh.cenler-shop.com/wap/tmpl/member/order_list.html';
	
	

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;
}
