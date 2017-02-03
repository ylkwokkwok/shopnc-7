$(function () {
    var key = getcookie('key');
    if (key == '') {    // 未登录
        // 跳转到微信用户权限获取页
        location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbf22b887fc929ff8&redirect_uri=' + SiteUrl + '/wx/milk_index.php&response_type=code&scope=snsapi_userinfo&state=activity_register#wechat_redirect';

    }
    
    // 验证手机号码的正则表达式
    var mobile_pattern = /^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/;
    // 验证码发送中状态
    var sending = false;
    
    // 验证会员是否已绑定手机号
    $.ajax({
        type: "POST",
        url: ApiUrl + "/index.php?act=milk_activity&op=index",
        data: {key: key},
        dataType: "json",
        success: function(result){
            // 未绑定
            if (result.datas.error == 0) {
                // 显示绑定信息录入画面
                $("#bind_page").show();
            }
            // 已绑定
            else {
                // 更新需要显示的姓名
                $("#binded_truename").text(result.datas.truename);
                // 更新需要显示的手机号
                $("#binded_mobile").text(result.datas.mobile);
                // 更新需要显示的绑定日期
                $("#binded_date").text(result.datas.bind_date);
                // 显示已绑定页面
                $("#binded_page").show();
            }
            // 隐藏loading画面
            $("#loading_page").hide();
        }
    });
    
    /**
     * 点击重新绑定按钮
     */
    $("#rebind").on("click", function(){
        // 隐藏已绑定页面
        $("#binded_page").hide();
        // 显示绑定信息录入画面
        $("#bind_page").show();
    });

    /**
     * 手机号码验证
     */
    $("[name=mobile]").on("change keyup", function(){
        // 验证码发送中
        if (sending) {
            // 不做操作
            return false;
        }
        // 手机号码
        var mobile = $(this).val();
        // 验证成功
        if (mobile_pattern.test(mobile)) {
            // 获取验证码按钮可用
            $("#get_verify").removeClass('disable_btn');
        // 验证失败
        } else {
            // 获取验证码按钮不可用
            $("#get_verify").addClass('disable_btn');
        }
    });

    /**
     * 点击获取验证码按钮
     */
    $("#get_verify").on("click", function() {
        // 手机号码
        var mobile = $("[name=mobile]").val();
        // 按钮可用
        if ($(this).attr("class").indexOf("disable_btn") === -1) {
            // 发送验证码
            $.ajax({
                type: "POST",
                url: ApiUrl + "/index.php?act=milk_activity&op=send_verify",
                data: {key: key, mobile: mobile},
                dataType: "json",
                success: function(result){
                    // 发送失败
                    if (result.datas.error == 1) {
                        // 提示信息
                        show_message(result.datas.msg, "red");
                    }
                    // 发送成功
                    else {
                        // 发送中状态
                        sending = true;
                        // 更新按钮文本
                        $("#get_verify").addClass("disable_btn").text("已发送 (60s)");
                        // 1秒后执行倒计时
                        setTimeout(function(){
                            count_down();
                        },1000);
                        // 提示信息
                        show_message(result.datas.msg, "green");
                    }
                }
            });
        }
    });
    
    /**
     * 点击绑定按钮
     */
    $("#bind_btn").on("click", function(){
        // 真实姓名
        var truename = $("[name=truename]").val();
        // 手机号码
        var mobile = $("[name=mobile]").val();
        // 验证码
        var verify = $("[name=verify]").val();
        // 信息填写格式正确
        if (mobile_pattern.test(mobile) && verify.length === 6 && truename.length > 0) {
            $.ajax({
                type: "POST",
                url: ApiUrl + "/index.php?act=milk_activity&op=bind_mobile",
                data: {key: key, truename: truename, mobile: mobile, verify: verify},
                dataType: "json",
                success: function(result){
                    // 绑定失败
                    if (result.datas.error == 1) {
                        // 提示信息
                        show_message(result.datas.msg, "red");
                    }
                    // 绑定成功
                    else {
                        // 隐藏绑定信息录入页面
                        $("#bind_page").hide();
                        // 显示绑定成功页面
                        $("#succsss_page").show();
                    }
                }
            });
        }
        // 信息填写有误
        else {
            show_message("请填写正确绑定信息", "red");
        }
    });

    /**
     * 发送验证码后，60秒倒计时
     */
    function count_down() {
        // 获取按钮文本
        var btn_text = $("#get_verify").text();
        // 获取当前秒数
        var num = parseInt(btn_text.substr(4).replace("(", "").replace("s)", ""));
        // 倒计时未结束
        if (--num > 0) {
            // 更新按钮文本
            $("#get_verify").text("已发送 ("+ num +"s)");
            // 1秒后继续执行倒计时
            setTimeout(function(){
                count_down();
            },1000);
        // 倒计时结束
        } else {
            sending = false;
            $("#get_verify").text("重新发送").removeClass("disable_btn");
        }
    }
});
