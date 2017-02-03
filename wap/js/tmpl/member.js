$(function() {
    var key = getcookie('key');
    if (key == '') {
        location.href = 'login.html';
    }
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_index",
        data: {key: key},
        dataType: 'json',
        //jsonp:'callback',
        success: function(result) {
            checklogin(result.login);
            $('#username').html(result.datas.member_info.user_name);
            $('#user_name').html(result.datas.member_info.user_name);
            // 会员积分
            $('#point').html(result.datas.member_info.point);

            /* zz@newland 添加开始   **/
            /* 时间：2016/03/01        **/
            // 个人信息显示邮箱 电话 性别。
            $('#member_email').html(result.datas.member_info.member_email);
            $('#member_mobile').html(result.datas.member_info.member_mobile);
            $('#member_sex').html(result.datas.member_info.member_sex);
            /* zz@newland 添加结束   **/
            
            // 会员等级
            $('#level').html(result.datas.member_info.level);
//            $('#predepoit').html(result.datas.member_info.predepoit);

            /* zz@newland 添加开始   **/
            /* 时间：2016/03/01        **/
            // 显示待付款，待收货，待评价的数量。
            $('#num_payment').html(result.datas.member_info.not_pay_num);
            $('#num_goods_receipt').html(result.datas.member_info.not_received_num);
            $('#num_evaluate').html(result.datas.member_info.not_valuate_num);
            /* zz@newland 添加结束   **/

            $('#avatar').attr("src", result.datas.member_info.avator);
            $('#tx').attr("src", result.datas.member_info.avator);
            /* lyq@newland 添加开始   **/
            /* 时间：2015/05/14        **/
            /* 功能ID：SHOP005        **/
            // 显示VIP会员标识
            /* zly@newland 修改开始**/
            /* 时间：2015/08/10     **/
//            if (result.datas.member_info.is_vip == '1' ) {
//                $('#my_commission').show();
//                $("#commission").show();
//                $("#username").css('color', 'red');
//                $('#images_rice').attr('src', '../../images/jinmi_1.png');
//                /* 时间：2015/05/29        **/
//                /* 功能ID：SHOP013         **/
//                // 积分转赠 链接
//                $("#points_transfer").attr("href", WapSiteUrl + '/tmpl/member/points_transfer.html');
//            }else{
//                $('#my_commission').hide();
//                $("#commission").hide();
//            }
            /* zly@newland 修改结束**/
            /* 时间：2015/05/15        **/
            /* 功能ID：SHOP012        **/
            // 更改开关显示效果
            change_push_class(result.datas.member_info.allow_push);

            /* 时间：2015/06/05        **/
            /* 功能：幸运号            **/
            $('#luck_num').html(result.datas.member_info.luck_num);
            /* lyq@newland 添加结束   **/
            /* zly@newland 添加待支付、待收货红点显示开始**/
            /* 时间：2015/07/24                          **/
            // 判断待支付红点显示
            if (result.datas.member_info.not_pay_num > 0) {
                $('#notpay_red_dot').show();
            }
            // 判断待收货红点显示
            if (result.datas.member_info.not_received_num > 0) {
                $('#notreceived_red_dot').show();
            }
            /* zly@newland 添加待收货、待支付红点显示结束**/
            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/10      **/
            /* wap端loading画面      **/
            // 显示页面内容
            $(".m-top").show();
            $(".m-center").show();
            // 隐藏loading画面
            $("#loading_page").hide();
            /* lyq@newland 添加结束 **/
            /* zly@newland 添加开始 **/
            /* 时间：2015/07/21      **/
            /* 订单状态栏显示        **/
            $("#taskbar").show();
            /* zly@newland 添加结束 **/
            return false;
        }
    });
    /* lyq@newland 添加开始   **/
    /* 时间：2015/05/15        **/
    /* 功能ID：SHOP012        **/
    // 绑定开关点击事件
    $('#allow_push').click(change_push_flg);
    /* lyq@newland 添加结束   **/





});

/* lyq@newland 添加开始   **/
/* 时间：2015/05/15        **/
/* 功能ID：SHOP012        **/

/**
 * 检查是否登录
 * @returns {is_login.key}
 */
function is_login() {
    var key = getcookie('key');
    if (key == '') {
        location.href = 'login.html';
    } else {
        return key;
    }
}

/**
 * 更改消息推送状态
 */
function change_push_flg() {
    // cookie验证key
    var key = is_login();
    // 需要更新的消息推送状态
    var push_flg_to = $('#allow_push').attr('class') === 'allow_push' ? 0 : 1;
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_index&op=change_push_flag",
        data: {key: key, push_flg_to: push_flg_to},
        dataType: 'json',
        success: function(result) {
            // 更改开关显示效果
            change_push_class(result.datas.allow_push);
        }
    });
}

/**
 * 更改开关显示效果
 * @param {type} push_flg 消息推送状态
 */
function change_push_class(push_flg) {
    if (push_flg == 1) {
        $('#allow_push').css('background-position', '0px -700px');
        $('#allow_push').attr('class', 'allow_push');
    } else {
        $('#allow_push').css('background-position', '-72px -700px');
        $('#allow_push').attr('class', 'disallow_push');
    }
}
/* lyq@newland 添加结束   **/
function showImage() {
    var memberId = $('#luck_num').html();
    var image = $("#avatar").attr("src");
    $('#showewm').qrcode({
        width: 200,
        height: 200,
        text: memberId, //根据此串生成第一个二维码  
        src: image
    });
    var hideobj = document.getElementById("hidebg");
    hidebg.style.display = "block";  //显示隐藏层
    hidebg.style.height = document.body.clientHeight + "px";  //设置隐藏层的高度为当前页面高度
    document.getElementById("hidebox").style.display = "block";  //显示弹出层
}
function hide()  //去除隐藏层和弹出层
{
    document.getElementById("hidebg").style.display = "none";
    document.getElementById("hidebox").style.display = "none";
    $("#showewm").empty();
}