<?php
/**
 * Description of refresh_token
 *
 * @author HaoLiang
 */

require_once("wx_common.php");


// 定义框架路径
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
// 引用框架核心类
if (!@include(dirname(dirname(__FILE__)).'/global.php')) exit('global.php isn\'t exists!');
if (!@include(BASE_CORE_PATH.'/nl_wx_shop.php')) exit('nl_wx_shop.php isn\'t exists!');
// 执行框架
Base::run(FALSE);
// 获取微信操作共同类
$wx_common = new wx_common();

$result_token = $wx_common->get_token();

$wx_common ->refresh_token($result_token->access_token);

// jsapi接口
$jsapi_pub = new JsApi_pub();
// 获取新的 jsapi_ticket
$jsapi_ticket = $jsapi_pub->getTicket();
// 更新setting表中的 jsapi_ticket
$jsapi_pub->refresh_ticket($jsapi_ticket);
