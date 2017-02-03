<?php

require_once("wx_common.php");

// 获取回调类型
$state = $_GET['state'];
define('APP_ID','wx');
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

/* lyq@newland 添加开始 **/
/* 时间：2015/06/26     **/
// 用户点击微信分享链接时
if (isset($_GET['inviter_code'])) {
    if ($_GET['inviter_code'] == 'milk') {  // 自取奶品
        setcookie('redirect_url', base64_encode(WAP_SITE_URL . '/milk/nearby_stores.html'));
    } else {    // 商城wap端分享
        $inviter_code = strlen($_GET['inviter_code']) == 10 ? $_GET['inviter_code'] : '';
        // 将推荐人编号存入cookie中
        setcookie('inviter_code', $inviter_code);
        // 将重定向URL存入cookie中
        setcookie('redirect_url', $_GET['redirect_url']);
    }
    // 重定向至微信网页授权链接
    redirect_wx_oauth();
}
/* lyq@newland 添加结束 **/

/* lyq@newland 添加开始 **/
/* 时间：2015/08/28     **/
// 点击公众号新鲜送菜单中的分类时
if (isset($_GET['gc_id'])) {
    // 将重定向URL存入cookie中
    setcookie('redirect_url', base64_encode(WAP_SITE_URL.'/tmpl/product_list.html?gc_id='.$_GET['gc_id']));
    // 重定向至微信网页授权链接
    redirect_wx_oauth();
}
/* lyq@newland 添加结束 **/

// 用户点击商城首页的时候
if ($state === 'code') {
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
    
    if (isset($_COOKIE['inviter_code'])) {  // 有推荐人
        // 更新用户信息
        $wx_common->update_invited_user_info($member_info, $_COOKIE['inviter_code']);
    } else {  // 无推荐人
        // 更新用户信息
        $wx_common->update_user_info($member_info);
    }
    
    if (isset($_COOKIE['redirect_url'])) {  // 有重定向url
        // 自动登录
        wx_login($user_info->openid, base64_decode($_COOKIE['redirect_url']));
    } else {    // 无重定向url
        // 自动登录
        wx_login($user_info->openid);
    }
    
}

// 用户点击个人中心的时候
if ($state === 'member') {
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
    
    if (isset($_COOKIE['inviter_code'])) {  // 有推荐人
        // 更新用户信息
        $wx_common->update_invited_user_info($member_info, $_COOKIE['inviter_code']);
    } else {  // 无推荐人
        // 更新用户信息
        $wx_common->update_user_info($member_info);
    }
    // 自动登录
    wx_login($user_info->openid, WAP_SITE_URL . '/tmpl/member/member.html?act=member');
}

/**
 * 重定向至微信网页授权链接
 */
function redirect_wx_oauth() {
    // 公众号的唯一标识
    $_appid = 'appid=' . wx_config::APPID;
    // 授权后重定向的回调链接地址
    $_redirect_uri = '&redirect_uri=' . BASE_SITE_URL . '/wx/index.php';
    // 返回类型，请填写code
    $_response_type = '&response_type=code';
    // 应用授权作用域 snsapi_userinfo 弹出授权页面
    $_scope = '&scope=snsapi_userinfo';
    // 重定向后所带参数
    $_state = '&state=code';
    // 获取访问权限的url
    $get_code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?'
            . $_appid . $_redirect_uri . $_response_type . $_scope . $_state . '#wechat_redirect';
    // 跳转
    header("location:" . $get_code_url, FALSE);
}