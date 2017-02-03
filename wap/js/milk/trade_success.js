$(function () {
    var key = getcookie('key');
    // 未登录
    if (key == '') {
        // 跳转到微信用户权限获取页
        location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbf22b887fc929ff8&redirect_uri=' + SiteUrl + '/wx/milk_index.php&response_type=code&scope=snsapi_userinfo&state=nearby_store#wechat_redirect';
    }

    // 自取点编号
    var log_id = GetQueryString('log_id');
    /* lyq@newland 修改开始 **/
    /* 时间：2015/09/18      **/
    // 到户标志
    var tohome = GetQueryString('tohome');
    // ajax获取自取点信息
    $.ajax({
        url: ApiUrl + "/index.php?act=milk_store&op=get_customer_cd",
        type: 'post',
        data: {key: key, log_id: log_id},
        dataType: 'json',
        success: function (result) {
            var data = result.datas;
            /* lyq@newland 修改开始 **/
            /* 时间：2015/09/21     **/
            // 检查快速订奶开关
            if (check_quick_entr(data)) {
                // 奶卡分配成功，返回正确的客户编号 且 购物渠道是自取 时
                if (data.customer_cd !== '' && tohome === null) {
                    // 显示 设置送奶计划 按钮
                    $(".plan").show();
                    // 设置送奶计划 按钮点击事件
                    $("#pay_plan").click(function(){
                        // 跳转至执行系统设置送奶计划
                        location.href = 'http://fresh.cenler-shop.com/selfTakeMilkSpot/milkPlan.do?method=milkPlan&customer_cd='+data.customer_cd+'&cssFlag=1';
                    });
                    // 显示奶卡分配成功提示信息
                    $("#assign_succed").show();
                }
                // 奶卡分配成功，返回正确的客户编号 且 购物渠道是到户 时
                else if (data.customer_cd !== '' && tohome !== null) {
                    // 显示 订单列表 按钮
                    $(".order").show();
                    // 订单列表 按钮点击事件
                    $("#order_list").click(function(){
                        // 跳转至微商城订单列表
                        location.href = WapSiteUrl + '/tmpl/member/order_list.html';
                    });
                    // 显示奶卡分配成功提示信息
                    $("#tohome_assign_succed").show();
                }
                // 奶卡分配失败，客户编号为空
                else {
                    // 将log_id显示到页面
                    $("#log_id").text(log_id);
                    // 显示奶卡分配失败提示信息
                    $("#assign_failed").show();
                }

                // 隐藏loading画面
                $("#loading_page").hide();
            }
            /* lyq@newland 修改结束 **/
        }
    });
    /* lyq@newland 修改结束 **/

});
