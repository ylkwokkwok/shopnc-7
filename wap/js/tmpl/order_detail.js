/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/05/18        **/
/* 功能ID：SHOP008-SHOP010 **/
/* 订单详细                **/

$(function() {
    // 获取cookie key
    var key = getcookie('key');
    // 判断是否已登录
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }
    // 获取订单ID
    var order_id = GetQueryString("order_id");

    // AJAX请求，获取数据
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_order&op=order_detail",
        data: {key: key, order_id: order_id},
        dataType: 'json',
        success: function(result) {
            // 检测是否登录了
            checklogin(result.login);

            /* lyq@newland 修改开始   **/
            /* 时间：2015/05/25        **/
            /* 功能ID：SHOP009         **/
            /* 增加ajax响应数据验证    **/

            // 响应数据
            var data = result.datas;
            // 判断数据是否有误
            if (!data.error) {
                /* wqw@newland 添加开始   **/
                /* 时间：2015/06/02        **/
                /* 功能ID：ADMIN006      **/
                data.SiteUrl = SiteUrl;
                template.helper('in_array', function(str, arr) {
                    return $.inArray(str, arr);
                });
                /* wqw@newland 添加结束   **/

                // 页面地址
                data.WapSiteUrl = WapSiteUrl;
                template.helper('typeof', function(s) {
                    return typeof (s);
                });
                
                /* lyq@newland 添加开始 **/
                /* 时间：2015/07/03      **/
                data.order_info.extend_points = data.order_info.extend_points == null ? 0 : data.order_info.extend_points;
                data.order_info.points_cash_ratio = data.order_info.points_cash_ratio == null ? 0 : data.order_info.points_cash_ratio;
                // 计算推广积分抵扣金额
                data.ep_to_cash = (parseInt(data.order_info.extend_points) * parseInt(data.order_info.points_cash_ratio) / 100).toFixed(2);
                // 更新商品实际金额
                data.order_info.goods_amount_true = (parseFloat(data.order_info.goods_amount_true) + parseFloat(data.ep_to_cash)).toFixed(2);
                /* lyq@newland 添加结束 **/
                
                // 执行模板脚本，返回执行后的html文本
                var html = template.render('order-detail-tmpl', data);
                // 更新页面html文本
                $("#order-detail").html(html);
                // 绑定 物流跟踪 按钮点击事件
                $("#viewdelivery-order").click(viewdelivery_order);
                // 退货 按钮点击事件
                $('.return-goods').click(function() {
                    location.href = WapSiteUrl + "/tmpl/member/refund_add.html?order_id=" + $(this).attr('order_id') + "&goods_id=" + $(this).attr('goods_id');
                });

                /* lyq@newland 添加开始   **/
                /* 时间：2015/05/25       **/
                /* 功能ID：SHOP009        **/

                // 订单退款 按钮点击事件
                $(".refund-order").click(function() {
                    location.href = WapSiteUrl + "/tmpl/member/add_refund_all.html?order_id=" + $(this).attr('order_id');
                });
                // 确认订单
                $(".sure-order").click(sureOrder);
                // 取消订单
                $(".cancel-order").click(cancelOrder);

                /* lyq@newland 添加结束   **/

                /* lyq@newland 添加开始   **/
                /* 时间：2015/05/26       **/
                /* 功能ID：SHOP009        **/

                // 取消订单退款 按钮点击事件
                //   绑定的方法在 nl_refund_common.js 中定义
                $(".undo-refund-order").click(undo_refund_order);

                // 取消退货 按钮点击事件
                //   绑定的方法在 nl_refund_common.js 中定义
                $(".undo-return-goods").click(undo_return_goods);

                /* lyq@newland 添加结束   **/

                /* lyq@newland 添加开始   **/
                /* 时间：2015/05/28       **/
                /* 功能ID：SHOP009        **/
                // 交易投诉 按钮点击事件
                $(".complain-order").click(function() {
                    location.href = WapSiteUrl + "/tmpl/member/add_complain.html?order_id=" + $(this).attr('order_id') + "&goods_id=" + $(this).attr('goods_id');
                });
                /* lyq@newland 添加结束   **/
            } else {
                $.sDialog({
                    content: data.error + '！<br>请返回上一页继续操作…',
                    okBtn: false,
                    cancelBtnText: '返回',
                    cancelFn: function() {
                        history.back();
                    }
                });
            }

            /* lyq@newland 修改结束   **/

            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/10      **/
            /* wap端loading画面      **/
            // 隐藏loading画面
            $("#loading_page").hide();
            /* lyq@newland 添加结束 **/
        }
    });

    /**
     *  跳转至 物流跟踪 页面
     */
    function viewdelivery_order() {
        location.href = WapSiteUrl + '/tmpl/member/order_delivery.html?order_id=' + order_id;
    }


    /* lyq@newland 添加开始   **/
    /* 时间：2015/05/25       **/
    /* 功能ID：SHOP009        **/

    /**
     * 取消订单
     */
    function cancelOrder() {
        var order_id = $(this).attr("order_id");

        $.sDialog({
            content: '确定取消订单？',
            okFn: function() {
                cancelOrderId(order_id);
            }
        });
    }

    /**
     * 执行取消订单
     * @param {type} order_id 订单ID
     */
    function cancelOrderId(order_id) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/index.php?act=member_order&op=order_cancel",
            data: {order_id: order_id, key: key},
            dataType: "json",
            success: function(result) {
                if (result.datas && result.datas == 1) {
                    location.href = WapSiteUrl + "/tmpl/member/order_detail.html?order_id=" + order_id;
                }
            }
        });
    }

    /**
     * 确认订单
     */
    function sureOrder() {
        var order_id = $(this).attr("order_id");

        $.sDialog({
            content: '确定确认订单？',
            okFn: function() {
                sureOrderId(order_id);
            }
        });
    }

    /**
     * 执行确认订单
     * @param {type} order_id 订单ID
     */
    function sureOrderId(order_id) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/index.php?act=member_order&op=order_receive",
            data: {order_id: order_id, key: key},
            dataType: "json",
            success: function(result) {
                if (result.datas && result.datas == 1) {
                    location.href = WapSiteUrl + "/tmpl/member/order_detail.html?order_id=" + order_id;
                }
            }
        });
    }

    /* lyq@newland 添加结束   **/
});

/* lyq@newland 添加结束   **/