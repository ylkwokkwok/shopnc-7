$(function() {

    $.ajax({
        url: ApiUrl + "/index.php?act=index",
        type: 'get',
        dataType: 'json',
        success: function(result) {
            var data = result.datas;
            var html = '';

            $.each(data, function(k, v) {
                $.each(v, function(kk, vv) {
                    switch (kk) {
                        case 'adv_list':
                        case 'home3':
                            $.each(vv.item, function(k3, v3) {
                                vv.item[k3].url = buildUrl(v3.type, v3.data);
                            });
                            break;

                        case 'home1':
                            vv.url = buildUrl(vv.type, vv.data);
                            break;

                        case 'home2':
                        case 'home4':
                            vv.square_url = buildUrl(vv.square_type, vv.square_data);
                            vv.rectangle1_url = buildUrl(vv.rectangle1_type, vv.rectangle1_data);
                            vv.rectangle2_url = buildUrl(vv.rectangle2_type, vv.rectangle2_data);
                            break;
                        case 'home5':
                            vv.square_url = buildUrl(vv.square_type, vv.square_data);
                            vv.rectangle1_url = buildUrl(vv.rectangle1_type, vv.rectangle1_data);
                            vv.rectangle2_url = buildUrl(vv.rectangle2_type, vv.rectangle2_data);
                            vv.rectangle3_url = buildUrl(vv.rectangle3_type, vv.rectangle3_data);
                            vv.rectangle4_url = buildUrl(vv.rectangle4_type, vv.rectangle4_data);
                            vv.rectangle5_url = buildUrl(vv.rectangle5_type, vv.rectangle5_data);
                            vv.rectangle6_url = buildUrl(vv.rectangle6_type, vv.rectangle6_data);
                            break;
                        case 'home6':
                            $.each(vv.item, function(k6, v6) {
                                vv.item[k6].url = buildUrl(v6.type, v6.data);
                            });
                            break;
                    }

                    /* lyq@newland 修改开始 **/
                    /* 时间：2015/07/13      **/
                    if (kk === 'adv_list') {    // 广告模块
                        var adv_html = template.render(kk, vv);
                        $("#adv-container").html(adv_html);
                    } else if (kk === 'home5') {    // 广告模块
                        var adv_html = template.render(kk, vv);
                        $("#adv-home5").html(adv_html);
                    } 
                    else {    // 非广告模块
                        html += template.render(kk, vv);
                    }
                    /* lyq@newland 修改结束 **/

                    return false;
                });
            });

            $("#main-container").html(html);

            /* zz@newland 修改开始 **/
            /* 时间：2016/03/08      **/
            //将前台上写的js放在后台，改变加载顺序。
            $(function() {
                $('#slides').slidesjs({
                    width: 940,
                    height: 380,
                    play: {
                        active: false,
                        auto: true,
                        interval: 4000,
                        swap: true
                    }
                });
            });
            /* zz@newland 修改结束 **/

            /* lyq@newland 修改开始 **/
            /* 时间：2015/07/13      **/
            // 移除搜索框下第一个模块的上边距
            $(".index_block").eq(0).removeClass('index_block');
            /* lyq@newland 修改结束 **/

            $('.adv_list').each(function() {
                if ($(this).find('.item').length < 2) {
                    return;
                }

                Swipe(this, {
                    startSlide: 2,
                    speed: 400,
                    auto: 3000,
                    continuous: true,
                    disableScroll: false,
                    stopPropagation: false,
                    callback: function(index, elem) {
                    },
                    transitionEnd: function(index, elem) {
                    }
                });
            });

            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/10      **/
            /* wap端loading画面      **/
            // 显示首页头部
            $("header").show();
            $(".home-logo").show();
            // 隐藏loading画面
            $("#loading_page").hide();
            /* lyq@newland 添加结束 **/
        }
    });

    $('.select').click(function() {
        var keyword = encodeURIComponent($('#keyword').val());
        location.href = WapSiteUrl + '/tmpl/product_list.html?keyword=' + keyword;
    });

});


