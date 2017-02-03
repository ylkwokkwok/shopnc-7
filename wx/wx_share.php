<?php
/* lyq@newland 添加  **/
/* 时间：2015/06/26  **/
/* 微信分享信息控制  **/

include_once("wx_common.php");
// 定义框架路径
define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
// 引用框架核心类
if (!@include(dirname(dirname(__FILE__)) . '/global.php')) exit('global.php isn\'t exists!');
if (!@include(BASE_CORE_PATH . '/nl_wx_shop.php')) exit('nl_wx_shop.php isn\'t exists!');
// 执行框架
Base::run(FALSE);

// 使用jsapi接口
$jsApi = new JsApi_pub();
// 获取wx js配置参数
$jsConfigParam = $jsApi->getConfigParam();

// 获取用户token信息
$user_token = Model('mb_user_token')->where(array('token' => $_COOKIE['key']))->select();
// 获取用户信息
$member = Model('member')->where(array('member_id' => $user_token[0]['member_id']))->select();
// 微信openid
$openid = $member[0]['member_wx_id'];

// 分享操作的相关信息
$share_param = array(
    'title'  => '米都商城', // 分享标题
    'desc'   => '米都商城，买东西也能赚钱！', // 分享描述（分享到朋友圈无此项）
    'link'   => BASE_SITE_URL . '/wx/index.php?inviter_code=' . substr($openid, -10)
                . '&redirect_url=' . base64_encode($_SERVER['HTTP_REFERER']), // 分享链接
    'imgUrl' => WAP_SITE_URL . '/images/home_logo.png'  // 分享图标
);

// 返回值
$ret_arr = array(
    'wx_config'   => $jsConfigParam,
    'share_param' => $share_param,
);
// 返回配置参数
echo json_encode($ret_arr);
exit;