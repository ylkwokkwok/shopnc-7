<?php

/**
 * 微信用户自动登录
 * 
 * @param type $openid  微信用户openid
 * @param string $redirect_url  重定向url
 */
function wx_login($openid, $redirect_url = '') {
    $model_member = Model('member');
    $array = array();
    $array['member_wx_id'] = $openid;
    // 根据微信用户openid获取用户信息
    $member_info = $model_member->getMemberInfo($array);
    // 根据用户信息生成登录token
    $token = get_login_token($member_info['member_id'], $member_info['member_name']);
    // 如果没有重定向url
    if ($redirect_url === '') {
        // 重定向url设为wap端首页
        $redirect_url = WAP_SITE_URL . '/';
    }
    // 清空重定向cookie值
    setcookie('redirect_url');
    
    if (!empty($token)) {   // 登录成功
        echo '<script type="text/javascript" src="' . WAP_SITE_URL . '/js/common.js"></script>' // 引入共通js
            . '<script>'
            . '    addcookie("username", "' . $member_info['member_name'] . '");'   // js保存用户名至cookie
            . '    addcookie("key", "' . $token . '");' // js保存登录token至cookie
            . '    location.href = "' . $redirect_url . '";'    // 重定向
            . '</script>';
    } else {    // 登录失败
        echo '<script>location.href = "' . WAP_SITE_URL . '/";</script>';
    }
    exit;
}

/**
 * 登录生成token
 * 
 * @param type $member_id   用户id
 * @param type $member_name 用户昵称
 * @return type 返回string为正常token，返回null为登录失败
 */
function get_login_token($member_id, $member_name) {
    $model_mb_user_token = Model('mb_user_token');

    //生成新的token
    $mb_user_token_info = array();
    $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0, 999999)));
    $mb_user_token_info['member_id'] = $member_id;
    $mb_user_token_info['member_name'] = $member_name;
    $mb_user_token_info['token'] = $token;
    $mb_user_token_info['login_time'] = TIMESTAMP;
    $mb_user_token_info['client_type'] = 'wap';

    $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

    if ($result) {  // 生成token成功
        return $token;
    } else {    // 生成token失败
        return null;
    }
}