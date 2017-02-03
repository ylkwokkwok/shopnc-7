<?php

require_once("wx_common.php");

// 获取回调类型
$state = $_GET['state'];

// 定义框架路径
define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
// 引用框架核心类
if (!@include(dirname(dirname(__FILE__)) . '/global.php')) exit('global.php isn\'t exists!');
if (!@include(BASE_CORE_PATH . '/nl_wx_shop.php')) exit('nl_wx_shop.php isn\'t exists!');
// 执行框架
Base::run(FALSE);

// 微信用户wap自动登录
require_once("auto_login.php");

// 获取微信操作共同类
$wx_common = new wx_common();

// 用户点击订奶相关时
if (!empty($state)) {
    logResult('code=' . $_GET['code']);
    // 获取网页授权的ACCESS TOEEN
    $access_token_info = $wx_common->get_access_token($_GET['code']);
    // 根据accesstoken和openid获取用户基本信息
    $user_info = $wx_common->get_user_info_by_oauth($access_token_info->access_token, $access_token_info->openid);

    // 构造系统用户基本信息
    $member_info = array();
    // 用户的OPENID
    $member_info['member_wx_id'] = $user_info->openid;
    // 用户的昵称
    $member_info['member_name'] = $user_info->nickname;
    // 用户的头像
    $member_info['member_avatar'] = $user_info->headimgurl;
    // 用户的性别 值为1时是男性，值为2时是女性，值为0时是未知
    $member_info['member_sex'] = $user_info->sex;
    // 用户积分默认赠送20分
    $member_info['extend_points'] = C('points_reg');
    // 当前登录时间
    $member_info['member_login_time'] = TIMESTAMP;
    
    // 更新用户信息
    $wx_common->update_user_info($member_info);
    
    if ($state == 'nearby_store') {
        $redirect_url = WAP_SITE_URL . '/milk/nearby_stores.html';
    } else if ($state == 'tohome') {
        $redirect_url = WAP_SITE_URL . '/milk/order_milk_card_tohome.html';
    } else if ($state == 'activity_register') {
        $redirect_url = WAP_SITE_URL . '/milk/activity_register.html';
    }
    
    // 自动登录
    wx_login($user_info->openid, $redirect_url);
}