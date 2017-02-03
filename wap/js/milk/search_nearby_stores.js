$(function() {
    var key = getcookie('key');
    if (key == '') {    // 未登录
        // 跳转到微信用户权限获取页
        location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbf22b887fc929ff8&redirect_uri='+SiteUrl+'/wx/milk_index.php&response_type=code&scope=snsapi_userinfo&state=nearby_store#wechat_redirect';
    }
//    
    /* lyq@newland 添加开始 **/
    /* 时间：2015/09/15      **/
    // 获取url中的可用自取点参数，无该参数时置为空字符串
    var self_cds = GetQueryString("self_cds") === null ? "" : GetQueryString("self_cds");
    /* lyq@newland 添加结束 **/
    
    // 隐藏loading画面
    $("#loading_page").hide();
    
    // “定位到当前位置”点击事件
    $(".position").click(function(){
        // 清空检索条件
        $("input[name=search]").val('');
        if (navigator.geolocation) {    // 浏览器支持定位
            // 获取当前位置
            navigator.geolocation.getCurrentPosition(
                // 定位成功
                function(position){
                    $.ajax({
                        url: ApiUrl + "/index.php?act=milk_store&op=search_position_stores",
                        type: 'post',
                        data: {key: key, lat: position.coords.latitude, lng: position.coords.longitude, self_cds:self_cds},
                        dataType: 'json',
                        beforeSend: function() {
                            // 显示页面覆盖层
                            $("#wxpay_loading_mask").show();
                            // 显示loading动画
                            $("#wxpay_loading").show();
                        },
                        success: function(result) {
                            // 隐藏页面覆盖层
                            $("#wxpay_loading_mask").hide();
                            // 隐藏loading动画
                            $("#wxpay_loading").hide();

                            var data = result.datas;
                            /* lyq@newland 修改开始 **/
                            /* 时间：2015/09/21     **/
                            // 检查快速订奶开关
                            if (check_quick_entr(data)) {
                                // 渲染模板
                                var html = template.render('stores', data);
                                // 更新页面内容
                                $("#stores_container").html(html);
                                // 隐藏loading画面
                                $("#loading_page").hide();
                                // 绑定店铺点击事件
                                bind_address_click(location.search, key);
                                // 更新我的位置
                                $("#position_desc").text(':'+result.datas.position_desc);
                            }
                            /* lyq@newland 修改结束 **/
                        }
                    });
                },
                // 定位失败
                function(error){
                    // 错误消息
                    var err_msg = '';
                  switch(error.code) 
                {
                    case error.PERMISSION_DENIED:
                      err_msg="请打开手机的定位功能";
                      break;
                    case error.POSITION_UNAVAILABLE:
                    case error.TIMEOUT:
                    case error.UNKNOWN_ERROR:
                      err_msg="位置信息获取失败";
                      break;
                }
                    // 显示错误消息
                    show_message(err_msg, 'red');
                }
            );
        } else {    // 浏览器不支持定位
            // 显示提示消息
            show_message('您的浏览器不支持定位功能', 'red');
        }
    });
    
    // 检索条件更改事件
    $("#search").click(function(){
        // 检索关键字
        var keyword = $.trim($("input[name=search]").val());
        // 检索条件为空时，模拟定位点击事件
        if (keyword === "") {
            $(".position").trigger("click");
            return false;
        }
        $.ajax({
            url: ApiUrl + "/index.php?act=milk_store&op=search_nearby_stores",
            type: 'post',
            data: {key: key, keyword: keyword, self_cds:self_cds},
            dataType: 'json',
            beforeSend: function() {
                // 显示页面覆盖层
                $("#wxpay_loading_mask").show();
                // 显示loading动画
                $("#wxpay_loading").show();
            },
            success: function(result) {
                // 隐藏页面覆盖层
                $("#wxpay_loading_mask").hide();
                // 隐藏loading动画
                $("#wxpay_loading").hide();
                
                var data = result.datas;
                /* lyq@newland 修改开始 **/
                /* 时间：2015/09/21     **/
                // 检查快速订奶开关
                if (check_quick_entr(data)) {
                    // 渲染模板
                    var html = template.render('stores', data);
                    // 更新页面内容
                    $("#stores_container").html(html);
                    // 隐藏loading画面
                    $("#loading_page").hide();
                    // 绑定店铺点击事件
                    bind_address_click(location.search, key);
                    // 更新我的位置
                    $("#position_desc").text(':'+result.datas.position_desc);
                }
                /* lyq@newland 修改结束 **/
            }
        });
    });
    
    // 默认加载页面完成后执行一次点击定位事件
    $(".position").trigger("click");
});
