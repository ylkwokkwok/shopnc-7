$(function() {
    var key = getcookie('key');
    if (key == '') {    // 未登录
        // 跳转到微信用户权限获取页
        location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbf22b887fc929ff8&redirect_uri='+SiteUrl+'/wx/milk_index.php&response_type=code&scope=snsapi_userinfo&state=nearby_store#wechat_redirect';
    }
    
    /* lyq@newland 添加开始 **/
    /* 时间：2015/09/15      **/
    // 获取url中的可用自取点参数，无该参数时置为空字符串
    var self_cds = GetQueryString("self_cds") === null ? "" : GetQueryString("self_cds");
    /* lyq@newland 添加结束 **/
    
    $("#search_nearby_stores").click(function(){
        location.href = 'search_nearby_stores.html'+location.search;
        return false;
    });
    
    // ajax获取自取点信息
    $.ajax({
        url: ApiUrl + "/index.php?act=milk_store&op=nearby_stores",
        type: 'post',
        data: {key: key, self_cds:self_cds},
        dataType: 'json',
        success: function(result) {
            var data = result.datas;
            /* lyq@newland 修改开始 **/
            /* 时间：2015/09/21     **/
            // 检查快速订奶开关
            if (check_quick_entr(data)) {
                if (data.expired) { // 定位过期
                    // 显示提示消息
                    show_message('定位过期，将跳转至自取点检索页面...','red');
                    // 1秒后跳转到自取点检索页面
                    setTimeout(function(){
                        location.href = 'search_nearby_stores.html'+location.search;
                    },1500);
                } else {
                    // 渲染模板
                    var html = template.render('stores', data);
                    // 更新页面内容
                    $("#stores_container").html(html);
                    // 隐藏loading画面
                    $("#loading_page").hide();
                    // 绑定店铺点击事件
                    bind_address_click(location.search, key);
                    if (result.datas.position_desc === '定位失败') {
                        // 更新我的位置
                        $("#position_desc").text(result.datas.position_desc);
                    } else {
                        // 更新我的位置
                        $("#position_desc").text('我的位置:'+result.datas.position_desc);
                    }
                }
            }
            /* lyq@newland 修改结束 **/
        }
    });
});
