<?php
/* lyq@newland 添加  **/
/* 时间：2015/07/09  **/
/* 商家绑定微信      **/

include_once('../wx/wx_common.php');
// 定义框架路径
define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
// 引用框架核心类
if (!@include(dirname(dirname(__FILE__)) . '/global.php'))
    exit('global.php isn\'t exists!');
if (!@include(BASE_CORE_PATH . '/nl_wx_shop.php'))
    exit('nl_wx_shop.php isn\'t exists!');
// 执行框架
Base::run(FALSE);

if ($_GET['state'] == 'getWxOpenid') {  // 微信用户授权后重定向进入时
    $wx_common = new wx_common();
    // 根据code获取用户信息（openid）
    $user_info = $wx_common->get_access_token($_GET['code']);
    // 将openid放入session中
    $_SESSION['seller_openid'] = $user_info->openid;
}

if (empty($_SESSION['seller_openid'])) {    //  session中没有微信openid时
    // 授权后重定向的回调链接地址
    $_redirect_uri = urlencode(BASE_SITE_URL . '/wap/seller_bind_wx.php');
    // 获取访问权限的url
    $get_code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . wx_config::APPID
            . '&redirect_uri=' . $_redirect_uri
            . '&response_type=code&scope=snsapi_base&state=getWxOpenid#wechat_redirect';
    // 跳转，获取授权
    header("location:" . $get_code_url);
    exit;
}

if ($_POST['submit_flg'] == 'ok') { // 用户提交数据时
    $model_seller = Model('seller');
    // 根据卖家用户名获取卖家用户信息
    $seller_info = $model_seller->getSellerInfo(array('seller_name' => trim($_POST['seller_name'])));
    if ($model_seller->is_wxid_binded($_SESSION['seller_openid'])) {
        // 微信已绑定卖家
        $err_msg = '此微信账号已绑定商家！';
    } elseif ($seller_info) { // 存在卖家用户信息
        $model_member = Model('member');
        // 根据会员ID和密码获取会员信息
        $member_info = $model_member->getMemberInfo(
                array(
                    'member_id' => $seller_info['member_id'],
                    'member_passwd' => md5(trim($_POST['password']))
                )
        );
        if ($member_info) { // 存在会员信息
            // 更新卖家seller_wx_id(openid)
            $model_seller->editSeller(array('seller_wx_id' => $_SESSION['seller_openid']), 
                                    array('seller_id' => $seller_info['seller_id']));
            $succ_msg = '绑定成功！';
        } else {    // 不存在会员信息
            $err_msg = '用户名密码错误！';
        }
    } else {    // 不存在卖家用户信息
        $err_msg = '用户名密码错误！';
    }
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>商家微信绑定</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
        <link rel="stylesheet" type="text/css" href="css/reset.css">
        <link rel="stylesheet" type="text/css" href="css/main.css">
        <link rel="stylesheet" type="text/css" href="css/member.css">
    </head>
    <body>
        <header>
            <div class="header-wrap">
                <h2>商家微信绑定</h2>
            </div>
        </header>
        <div class="login-form">
            <form id="bind_form" action="seller_bind_wx.php" method ="post">
                <input type="hidden" name="submit_flg" value="ok">
                <span>
                    <input type="text" placeholder="用户名" class="input-40" name="seller_name" id="seller_name"
                           value="<?php echo isset($err_msg) ? $_POST['seller_name'] : '' ?>"/>
                </span>
                <span>
                    <input type="password" placeholder="密码" class="input-40" name="password" id="password" value=""/>
                </span>
                <div class="error-tips mt10" 
                <?php
                if (isset($err_msg)) {
                    echo 'style="display: inherit"';
                } elseif (isset($succ_msg)) {
                    echo 'style="display: inherit;color: green;border: 1px solid green;"';
                }
                ?>>
                    <?php
                    echo isset($err_msg) ? $err_msg : '';
                    echo isset($succ_msg) ? $succ_msg : '';
                    ?>
                </div>
                <a href="javascript:void(0);" class="l-btn-login mt10">
                    绑定
                </a>
            </form>
        </div>
        <script type="text/javascript" src="js/zepto.min.js"></script>
        <script>
            $(function() {
                /**
                 * 点击绑定
                 */
                $(".l-btn-login").click(function() {
                    // 清空错误框，还原错误框样式
                    $(".error-tips").hide().html('').css({color: 'red', border: '1px solid red'});
                    // 用户名或密码为空时
                    if ($.trim($("#seller_name").val()) === '' || $.trim($("#password").val()) === '') {
                        $(".error-tips").html('用户名密码未填写！').show();
                        return false;
                    }
                    // 提交表单
                    $("#bind_form").submit();
                });
            });
        </script>
    </body>
</html>