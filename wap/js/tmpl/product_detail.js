/* lyq@newland 删除开始   **/
/* 时间：2015/06/02        **/
/* 功能ID：SHOP017         **/
// 删除评论显示相关代码
/* lyq@newland 删除结束   **/
$(function() {
    var unixTimeToDateString = function(ts, ex) {
        ts = parseFloat(ts) || 0;
        if (ts < 1) {
            return '';
        }
        var d = new Date();
        d.setTime(ts * 1e3);
        var s = '' + d.getFullYear() + '-' + (1 + d.getMonth()) + '-' + d.getDate();
        if (ex) {
            s += ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();
        }
        return s;
    };

    var buyLimitation = function(a, b) {
        a = parseInt(a) || 0;
        b = parseInt(b) || 0;
        var r = 0;
        if (a > 0) {
            r = a;
        }
        if (b > 0 && r > 0 && b < r) {
            r = b;
        }
        return r;
    };

    template.helper('isEmpty', function(o) {
        for (var i in o) {
            return false;
        }
        return true;
    });

    // 图片轮播
    function picSwipe() {
        var elem = $("#mySwipe")[0];
        window.mySwipe = Swipe(elem, {
            continuous: true,
            // disableScroll: true,
            stopPropagation: true,
            callback: function(index, element) {
                $(".pds-cursize").html(index + 1);
            }
        });
    }

    // 获取商品ID
    var goods_id = GetQueryString("goods_id");

    /* lyq@newland 修改开始 **/
    /* 时间：2015/06/03     **/
    /* 功能ID：SHOP018      **/

    /**
     * 初始化页面
     *   （放到方法内，方便多次调用）
     * @param string goods_id 商品ID
     * @param bool spec_display 是否显示商品分类
     */
    function init_page(goods_id, spec_display) {
        //渲染页面
        $.ajax({
            url: ApiUrl + "/index.php?act=goods&op=goods_detail",
            type: "get",
            data: {goods_id: goods_id},
            dataType: "json",
            success: function(result) {
                var data = result.datas;
                if (!data.error) {
                    /* wqw@newland 添加开始   **/
                    /* 时间：2015/06/02        **/
                    /* 功能ID：ADMIN006      **/
                    data.SiteUrl = SiteUrl;
                    /* wqw@newland 添加结束   **/
                    //商品图片格式化数据
                    if (data.goods_image) {
                        var goods_image = data.goods_image.split(",");
                        data.goods_image = goods_image;
                    } else {
                        data.goods_image = [];
                    }
                    //商品规格格式化数据
                    if (data.goods_info.spec_name) {
                        var goods_map_spec = $.map(data.goods_info.spec_name, function(v, i) {
                            var goods_specs = {};
                            goods_specs["goods_spec_id"] = i;
                            goods_specs['goods_spec_name'] = v;
                            if (data.goods_info.spec_value) {
                                $.map(data.goods_info.spec_value, function(vv, vi) {
                                    if (i == vi) {
                                        goods_specs['goods_spec_value'] = $.map(vv, function(vvv, vvi) {
                                            var specs_value = {};
                                            specs_value["specs_value_id"] = vvi;
                                            specs_value["specs_value_name"] = vvv;
                                            return specs_value;
                                        });
                                    }
                                });
                                return goods_specs;
                            } else {
                                data.goods_info.spec_value = [];
                            }
                        });
                        data.goods_map_spec = goods_map_spec;
                    } else {
                        data.goods_map_spec = [];
                    }

                    // 虚拟商品限购时间和数量
                    if (data.goods_info.is_virtual == '1') {
                        data.goods_info.virtual_indate_str = unixTimeToDateString(data.goods_info.virtual_indate, true);
                        data.goods_info.buyLimitation = buyLimitation(data.goods_info.virtual_limit, data.goods_info.upper_limit);
                    }

                    // 预售发货时间
                    if (data.goods_info.is_presell == '1') {
                        data.goods_info.presell_deliverdate_str = unixTimeToDateString(data.goods_info.presell_deliverdate);
                    }

                    /* wqw@newland 添加开始   　**/
                    /* 时间：2015/06/08         **/
                    /* 功能ID：ADMIN006         **/
                    template.helper('in_array', function(str, arr) {
                        return $.inArray(str, arr);
                    });
                    /* wqw@newland 添加结束   **/
                    //渲染模板
                    var html = template.render('product_detail', data);
                    $("#product_detail_wp").html(html);

                    //图片轮播
                    picSwipe();


                    /* lyq@newland 删除开始 **/
                    /* 时间：2015/06/03     **/
                    /* 功能ID：SHOP018      **/
                    // 1.原手机端详情显示
                    // 2.商品描述扩展点击事件
                    /* lyq@newland 删除结束 **/

                    /* lyq@newland 添加开始 **/
                    /* 时间：2015/06/03     **/
                    /* 功能ID：SHOP018      **/

                    // 判断是否显示商品分类
                    if (spec_display) {
                        // 显示商品分类
                        $(".spec_list").show();
                    } else {
                        // 隐藏商品分类
                        $(".spec_list").hide();
                    }
                    // 商品分类 显示/隐藏
                    $(".pddcp-arrow").click(function() {
                        if ($(this).find('span').attr('display_type') == 'hide') {
                            // 改变背景位置（改变箭头方向）
                            $(this).find('span').css('background-position', '-25px -229px');
                            // 更改显示类型属性
                            $(this).find('span').attr('display_type', 'show');
                            // 显示商品分类
                            $(".spec_list").show();
                        } else {
                            // 改变背景位置（改变箭头方向）
                            $(this).find('span').css('background-position', '-43px -229px;');
                            // 更改显示类型属性
                            $(this).find('span').attr('display_type', 'hide');
                            // 隐藏商品分类
                            $(".spec_list").hide();
                        }
                    });
                    /* lyq@newland 添加结束 **/

                    //规格属性
                    var myData = {};
                    myData["spec_list"] = data.spec_list;
                    $(".pddc-stock a").click(function() {
                        var self = this;
                        arrowClick(self, myData);
                    });
                    //购买数量，减
                    $(".minus-wp").click(function() {
                        var buynum = $(".buy-num").val();
                        if (buynum > 1) {
                            $(".buy-num").val(parseInt(buynum - 1));
                        }
                    });
                    //购买数量加
                    $(".add-wp").click(function() {
                        var buynum = parseInt($(".buy-num").val());
                        if (buynum < data.goods_info.goods_storage) {
                            $(".buy-num").val(parseInt(buynum + 1));
                        }
                    });
                    // 一个F码限制只能购买一件商品 所以限制数量为1
                    if (data.goods_info.is_fcode == '1') {
                        $('.minus-wp').hide();
                        $('.add-wp').hide();
                        $(".buy-num").attr('readOnly', true);
                    }
                    //收藏
                    $(".pd-collect").click(function() {
                        var key = getcookie('key');//登录标记
                        if (key == '') {
                            window.location.href = WapSiteUrl + '/tmpl/member/login.html';
                        } else {
                            $.ajax({
                                url: ApiUrl + "/index.php?act=member_favorites&op=favorites_add",
                                type: "post",
                                dataType: "json",
                                data: {goods_id: goods_id, key: key},
                                success: function(fData) {
                                    if (checklogin(fData.login)) {
                                        if (!fData.datas.error) {
                                            $.sDialog({
                                                skin: "green",
                                                content: "收藏成功！",
                                                okBtn: false,
                                                cancelBtn: false
                                            });
                                        } else {
                                            $.sDialog({
                                                skin: "red",
                                                content: fData.datas.error,
                                                okBtn: false,
                                                cancelBtn: false
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    });
                    //加入购物车
                    $(".add-to-cart").click(function() {
                        var key = getcookie('key');//登录标记
                        if (key == '') {
                            window.location.href = WapSiteUrl + '/tmpl/member/login.html';
                        } else {
                            var quantity = parseInt($(".buy-num").val());
                            $.ajax({
                                url: ApiUrl + "/index.php?act=member_cart&op=cart_add",
                                data: {key: key, goods_id: goods_id, quantity: quantity},
                                type: "post",
                                success: function(result) {
                                    var rData = $.parseJSON(result);
                                    if (checklogin(rData.login)) {
                                        if (!rData.datas.error) {
                                            $.sDialog({
                                                skin: "block",
                                                content: "添加购物车成功！",
                                                "okBtnText": "再逛逛",
                                                "cancelBtnText": "去购物车",
                                                okFn: function() {
                                                },
                                                cancelFn: function() {
                                                    window.location.href = WapSiteUrl + '/tmpl/cart_list.html';
                                                }
                                            });
                                        } else {
                                            $.sDialog({
                                                skin: "red",
                                                content: rData.datas.error,
                                                okBtn: false,
                                                cancelBtn: false
                                            });
                                        }
                                    }
                                }
                            })
                        }
                    });

                    //立即购买
                    if (data.goods_info.is_virtual == '1') {
                        $(".buy-now").click(function() {
                            var key = getcookie('key');//登录标记
                            if (key == '') {
                                window.location.href = WapSiteUrl + '/tmpl/member/login.html';
                                return false;
                            }

                            var buynum = parseInt($('.buy-num').val()) || 0;

                            if (buynum < 1) {
                                $.sDialog({
                                    skin: "red",
                                    content: '参数错误！',
                                    okBtn: false,
                                    cancelBtn: false
                                });
                                return;
                            }

                            buynum = Number(buynum);
                            var havenum = Number(data.goods_info.goods_storage);

                            if (buynum > havenum) {
                                //if (buynum > data.goods_info.goods_storage) {
                                $.sDialog({
                                    skin: "red",
                                    content: '库存不足！',
                                    okBtn: false,
                                    cancelBtn: false
                                });
                                return;
                            }

                            // 虚拟商品限购数量
                            if (data.goods_info.buyLimitation > 0 && buynum > data.goods_info.buyLimitation) {
                                $.sDialog({
                                    skin: "red",
                                    content: '超过限购数量！',
                                    okBtn: false,
                                    cancelBtn: false
                                });
                                return;
                            }

                            var json = {};
                            json.key = key;
                            json.cart_id = goods_id;
                            json.quantity = buynum;
                            $.ajax({
                                type: 'post',
                                url: ApiUrl + '/index.php?act=member_vr_buy&op=buy_step1',
                                data: json,
                                dataType: 'json',
                                success: function(result) {
                                    if (result.datas.error) {
                                        $.sDialog({
                                            skin: "red",
                                            content: result.datas.error,
                                            okBtn: false,
                                            cancelBtn: false
                                        });
                                    } else {
                                        location.href = WapSiteUrl + '/tmpl/order/vr_buy_step1.html?goods_id=' + goods_id + '&quantity=' + buynum;
                                    }
                                }
                            });
                        });
                    } else {
                        $(".buy-now").click(function() {
                            var key = getcookie('key');//登录标记
                            if (key == '') {
                                window.location.href = WapSiteUrl + '/tmpl/member/login.html';
                            } else {
                                /* lyq@newland 修改开始 **/
                                /* 时间：2015/07/10      **/
                                // 购买数量
                                var buynum = parseInt($('.buy-num').val()) || 0;
                                buynum = Number(buynum);
                                // 库存数量
                                var havenum = Number(data.goods_info.goods_storage);
                                /* lyq@newland 修改结束 **/

                                if (buynum < 1) {
                                    $.sDialog({
                                        skin: "red",
                                        content: '参数错误！',
                                        okBtn: false,
                                        cancelBtn: false
                                    });
                                    return;
                                }
                                
                                /* lyq@newland 修改开始 **/
                                /* 时间：2015/11/02      **/
                                // 限购数量
                                var p_limit = Number(data.goods_info.purchase_limit);
                                 // 方式
                                var delivery_type = Number(data.goods_info.delivery_type);
                                //奶品种类
                                var milk_product_num = Number(data.goods_info.milk_product_num);
                                //奶卡类别
                                var milk_card_type = Number(data.goods_info.milk_card_type);
                                //最佳配货时间
                                var book_time = data.goods_info.best_time;
                                // 用户已购买数量
                                var buyed_num = 0;
                                var buyed_json = {};
                                buyed_json.key = key;
                                buyed_json.goods_id = goods_id;
                                $.ajax({
                                    type: 'post',
                                    url: ApiUrl + '/index.php?act=member_buy&op=get_goods_buyed_count',
                                    data: buyed_json,
                                    async: false,
                                    dataType: 'json',
                                    success: function(result) {
                                        buyed_num = result.datas.buyed_num;
                                    }
                                });
                                // 限购数量大于0(是限购商品) 且 用户已购买数量与本次购买数量的和大于限购数量
                                if (p_limit > 0 && buyed_num + buynum > p_limit) {
                                    $.sDialog({
                                        skin: "red",
                                        content: '超出限购数量！',
                                        okBtn: false,
                                        cancelBtn: false
                                    });
                                    return;
                                }
                                /* lyq@newland 修改结束 **/
                            
                                if (buynum > havenum) {
                                    $.sDialog({
                                        skin: "red",
                                        content: '库存不足！',
                                        okBtn: false,
                                        cancelBtn: false
                                    });
                                    return;
                                }

                                var json = {};
                                json.key = key;
                                json.cart_id = goods_id + '|' + buynum;
                                $.ajax({
                                    type: 'post',
                                    url: ApiUrl + '/index.php?act=member_buy&op=buy_step1',
                                    data: json,
                                    dataType: 'json',
                                    success: function(result) {
                                        if (result.datas.error) {
                                            $.sDialog({
                                                skin: "red",
                                                content: result.datas.error,
                                                okBtn: false,
                                                cancelBtn: false
                                            });
                                        } else {
                                            location.href = WapSiteUrl + '/tmpl/order/buy_step1.html?goods_id=' + goods_id + '&buynum=' + buynum + '&delivery_type=' + delivery_type+ '&milk_product_num=' + milk_product_num+ '&milk_card_type=' + milk_card_type+'&book_time=' + book_time;
                                        }
                                    }
                                });
                            }
                        });

                    }

                    /* lyq@newland 添加开始 **/
                    /* 时间：2015/06/09      **/
                    // 点击QQ图标联系商家
//                    $("#contact_store").click(function() {
//                        var url = $(this).attr('href');
//                        window.open(WapSiteUrl + '/tmpl/contact_store.html?url=' + url);
//                        return false;
//                    });
                    /* lyq@newland 添加结束 **/
                } else {

                    $.sDialog({
                        content: data.error + '！<br>请返回上一页继续操作…',
                        okBtn: false,
                        cancelBtnText: '返回',
                        cancelFn: function() {
                            history.back();
                        }
                    });

                    //var html = data.error;
                    //$("#product_detail_wp").html(html);

                }

                //验证购买数量是不是数字
                $("#buynum").blur(buyNumer);
                AddView();

                /* lyq@newland 添加开始   **/
                /* 时间：2015/06/02        **/
                /* 功能ID：SHOP017         **/
                // 判断是否存在手机端详情信息
                if (data.goods_info.mobile_body != '') {
                    // 存在手机端详情信息
                    // 显示手机端详情
                    $("#product_info").html(data.goods_info.mobile_body);
                    // 控制图片宽度
                    $("#product_info img").css('width', '100%');
                    /* lyq@newland 添加开始 **/
                    /* 时间：2015/06/10      **/
                    /* wap端loading画面      **/
                    // 隐藏loading画面
                    $("#loading_page").hide();
                    /* lyq@newland 添加结束 **/
                } else {
                    // 不存在手机端详情信息
                    // 显示电脑端详情
                    get_goods_info();
                }
                /* lyq@newland 添加结束   **/
            }
        });
    }

    // 初始化页面
    init_page(goods_id, false);
    /* lyq@newland 修改结束 **/

    /* lyq@newland 添加开始   **/
    /* 时间：2015/06/01        **/
    /* 功能ID：SHOP017         **/

    /**
     * 显示商品图文详情
     */
    function get_goods_info() {
        $.ajax({
            url: ApiUrl + "/index.php?act=goods&op=goods_body",
            data: {goods_id: goods_id},
            type: "get",
            success: function(result) {
                /* lyq@newland 添加开始 **/
                /* 时间：2015/06/10      **/
                /* wap端loading画面      **/
                // 隐藏loading画面
                $("#loading_page").hide();
                /* lyq@newland 添加结束 **/
                $("#product_info").html(result);
            }
        });
    }
    /* lyq@newland 添加结束   **/


    //点击商品规格，获取新的商品
    function arrowClick(self, myData) {
        $(self).addClass("current").siblings().removeClass("current");
        //拼接属性
        var curEle = $(".pddc-stock-spec").find("a.current");
        var curSpec = [];
        $.each(curEle, function(i, v) {
            curSpec.push($(v).attr("specs_value_id"));
        });
        var spec_string = curSpec.sort().join("|");
        //获取商品ID
        var spec_goods_id = myData.spec_list[spec_string];

        /* lyq@newland 修改开始 **/
        /* 时间：2015/06/03     **/
        /* 功能ID：SHOP018      **/
        // 重新初始化页面
        init_page(spec_goods_id, true);
        /* lyq@newland 修改结束 **/
    }

    function AddView() {//增加浏览记录
        var goods_info = getcookie('goods');
        var goods_id = GetQueryString('goods_id');
        if (goods_id < 1) {
            return false;
        }

        if (goods_info == '') {
            goods_info += goods_id;
        } else {

            var goodsarr = goods_info.split('@');
            if (contains(goodsarr, goods_id)) {
                return false;
            }
            if (goodsarr.length < 5) {
                goods_info += '@' + goods_id;
            } else {
                goodsarr.splice(0, 1);
                goodsarr.push(goods_id);
                goods_info = goodsarr.join('@');
            }
        }

        addcookie('goods', goods_info);
        return false;
    }

    function contains(arr, str) {//检测goods_id是否存入
        var i = arr.length;
        while (i--) {
            if (arr[i] === str) {
                return true;
            }
        }
        return false;
    }

    $.sValid.init({
        rules: {
            buynum: "digits"
        },
        messages: {
            buynum: "请输入正确的数字"
        },
        callback: function(eId, eMsg, eRules) {
            if (eId.length > 0) {
                var errorHtml = "";
                $.map(eMsg, function(idx, item) {
                    errorHtml += "<p>" + idx + "</p>";
                });
                $.sDialog({
                    skin: "red",
                    content: errorHtml,
                    okBtn: false,
                    cancelBtn: false
                });
            }
        }
    });
    //检测商品数目是否为正整数
    function buyNumer() {
        $.sValid();
    }
});