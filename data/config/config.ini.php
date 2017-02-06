<?php
// defined('InShopNC') or exit('Access Invalid!');

$config = array();
$config['base_site_url']        = 'http://shopnc.siburuxue.org';
$config['shop_site_url'] 		= 'http://shopnc.siburuxue.org/shop';
$config['shop_friendship_url'] 		= 'http://www.cenler.com';
$config['cms_site_url'] 		= 'http://shopnc.siburuxue.org/cms';
$config['microshop_site_url'] 	= 'http://shopnc.siburuxue.org/microshop';
$config['circle_site_url'] 		= 'http://shopnc.siburuxue.org/circle';
$config['admin_site_url'] 		= 'http://shopnc.siburuxue.org/admin';
$config['mobile_site_url'] 		= 'http://shopnc.siburuxue.org/mobile';
$config['wap_site_url'] 		= 'http://shopnc.siburuxue.org/wap';
$config['chat_site_url'] 		= 'http://shopnc.siburuxue.org/chat';
$config['node_site_url'] 		= 'http://127.0.0.1:8090';
$config['upload_site_url']		= 'http://shopnc.siburuxue.org/data/upload';
$config['resource_site_url']	= 'http://shopnc.siburuxue.org/data/resource';
$config['milk_return_url'] = 'http://192.168.33.107:8080/';
$config['milk_return_url_p'] = 'http://192.168.33.107:8080/salesPromotion/common.do?method=getCustomerReturnOrders&refundSn=';
$config['milk_return_url_z'] = 'http://192.168.33.107:8080/selfTakeMilkSpot/common.do?method=getCustomerReturnOrders&refundSn=';
$config['version'] 		= '201502020388';
$config['setup_date'] 	= '2016-12-19 11:59:12';
$config['gip'] 			= 0;
$config['dbdriver'] 	= 'mysqli';
$config['db']['slave']        = $config['db']['master'];

$config['db']['shop']['tablepre']		= 'shopnc_';
$config['db']['shop']['dbhost']       = 'localhost';
$config['db']['shop']['dbport']       = '3306';
$config['db']['shop']['dbuser']       = 'root';
$config['db']['shop']['dbpwd']        = '123456';
$config['db']['shop']['dbname']       = 'shopnc';
$config['db']['shop']['dbcharset']    = 'UTF-8';

$config['db']['mobile']['tablepre']		= 'wxshop_';
$config['db']['mobile']['dbhost']       = '127.0.0.1';
$config['db']['mobile']['dbport']       = '3306';
$config['db']['mobile']['dbuser']       = 'root';
$config['db']['mobile']['dbpwd']        = '123456';
$config['db']['mobile']['dbname']       = 'dlxinle';
$config['db']['mobile']['dbcharset']    = 'UTF-8';

$config['db']['wx']['tablepre']		= '';
$config['db']['wx']['dbhost']       = $config['db']['mobile']['dbhost'];
$config['db']['wx']['dbport']       = $config['db']['mobile']['dbport'];
$config['db']['wx']['dbuser']       = $config['db']['mobile']['dbuser'];
$config['db']['wx']['dbpwd']        = $config['db']['mobile']['dbpwd'];
$config['db']['wx']['dbname']       = 'promotion_db';
$config['db']['wx']['dbcharset']    = 'UTF-8';

$config['session_expire'] 	= 3600;
$config['lang_type'] 		= 'zh_cn';
$config['cookie_pre'] 		= 'C292_';
$config['thumb']['cut_type'] = 'gd';
$config['thumb']['impath'] = '';
$config['cache']['type'] 			= 'file';
//$config['redis']['prefix']      	= 'nc_';
//$config['redis']['master']['port']     	= 6379;
//$config['redis']['master']['host']     	= '127.0.0.1';
//$config['redis']['master']['pconnect'] 	= 0;
//$config['redis']['slave']      	    = array();
//$config['fullindexer']['open']      = false;
//$config['fullindexer']['appname']   = '33hao';
$config['debug'] 			= false;
$config['default_store_id'] = '1';
//如果开始伪静态，这里设置为true
$config['url_model'] = false;
//如果店铺开启二级域名绑定的，这里填写主域名如baidu.com
$config['subdomain_suffix'] = '';
//$config['session_type'] = 'redis';
//$config['session_save_path'] = 'tcp://127.0.0.1:6379';
$config['node_chat'] = true;
//流量记录表数量，为1~10之间的数字，默认为3，数字设置完成后请不要轻易修改，否则可能造成流量统计功能数据错误
$config['flowstat_tablenum'] = 3;
$config['sms']['gwUrl'] = 'http://sdkhttp.eucp.b2m.cn/sdk/SDKService';
$config['sms']['serialNumber'] = '';
$config['sms']['password'] = '';
$config['sms']['sessionKey'] = '';
$config['queue']['open'] = false;
$config['queue']['host'] = '127.0.0.1';
$config['queue']['port'] = 6379;
$config['cache_open'] = false;
$config['delivery_site_url']    = 'http://shopnc.siburuxue.org/delivery';
return $config;