/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/05/19        **/
/* 功能ID：SHOP009         **/
/* 退货退款详细            **/

$(function() {
    // 获取cookie key
    var key = getcookie('key');
    // 判断是否已登录
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }
    // 获取订单ID
    var refund_id = GetQueryString("refund_id");

    // AJAX请求，获取数据
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_refund&op=refund_detail",
        data: {key: key, refund_id: refund_id, type: GetQueryString("type")},
        dataType: 'json',
        success: function(result) {
            // 检测是否登录了
            checklogin(result.login);
            // 响应数据
            var data = result.datas;

            /* lyq@newland 修改开始   **/
            /* 时间：2015/05/25        **/
            /* 功能ID：SHOP009         **/
            /* 增加ajax响应数据验证    **/

            // 判断数据是否有误
            if (!data.error) {
                // 改变title
                if (data.refund.type == 'return') {
                    document.title = '退货退款详细';
                    $('#header h2').html('退货退款详细');
                } else if (data.refund.type == 'refund') {
                    document.title = '订单退款详细';
                    $('#header h2').html('订单退款详细');
                }
                // 页面地址
                data.WapSiteUrl = WapSiteUrl;
                // 绑定方法到模板：时间格式化
                template.helper('$getLocalTime', function(nS) {
                    var d = new Date(parseInt(nS) * 1000);
                    var s = '';
                    s += d.getFullYear() + '年';
                    s += (d.getMonth() + 1) + '月';
                    s += d.getDate() + '日 ';
                    s += (d.getHours() > 9 ? d.getHours() : '0' + d.getHours()) + ':';
                    s += (d.getMinutes() > 9 ? d.getMinutes() : '0' + d.getMinutes());
                    return s;
                });
                /* wqw@newland 添加开始   　**/
                /* 时间：2015/06/08         **/
                /* 功能ID：ADMIN006         **/
                data.SiteUrl = SiteUrl;
                template.helper('in_array', function(str, arr) {
                    return $.inArray(str, arr);
                });
                /* wqw@newland 添加结束   **/
                // 执行模板脚本，返回执行后的html文本
                var html = template.render('refund-detail-tmpl', data);
                // 更新页面html文本
                $("#refund-detail").html(html);

                // 跳转至订单详情
                $(".order-sn").click(order_detail);
                // 跳转至物流跟踪
                $("#viewdelivery-order").click(viewdelivery_order);
                // 提交物流信息
                $(".submit-invoice-info").click(submit_invoice_info);

                /* lyq@newland 添加开始   **/
                /* 时间：2015/05/26       **/
                /* 功能ID：SHOP009        **/

                // 取消订单退款 按钮点击事件
                //   绑定的方法在 nl_refund_common.js 中定义
                $(".undo-refund-order").click(undo_refund);

                // 取消退货 按钮点击事件
                //   绑定的方法在 nl_refund_common.js 中定义
                $(".undo-return-goods").click(undo_return);

                /* lyq@newland 添加结束   **/
                
                /* lyq@newland 添加开始   **/
                /* 时间：2015/07/23       **/
                // 延时 按钮
                $(".delay-action").click(delay_action);
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
     * 跳转至订单详情
     */
    function order_detail() {
        location.href = WapSiteUrl + "/tmpl/member/order_detail.html?order_id=" + $(this).attr('order_id');
    }

    /**
     *  跳转至 物流跟踪 页面
     */
    function viewdelivery_order() {
        location.href = WapSiteUrl + '/tmpl/member/order_delivery.html?order_id=' + $(this).attr('order_id');
    }

    /**
     * 提交物流信息
     */
    function submit_invoice_info() {
        var express_id = $("#express_id").val();
        var invoice_no = $.trim($("#invoice_no").val());
        // 单号必填
        if (invoice_no === '') {
            $.sDialog({
                skin: "red",
                content: '请填写物流单号',
                okBtn: false,
                cancelBtn: false
            });
            return false;
        }
        // 单号长度限制
        if (invoice_no.length > 50) {
            $.sDialog({
                skin: "red",
                content: '物流单号长度限制为50字',
                okBtn: false,
                cancelBtn: false
            });
            return false;
        }
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_refund&op=add_refund_invoice",
            data: {key: key, refund_id: refund_id, express_id: express_id, invoice_no: invoice_no},
            dataType: 'json',
            success: function(result) {
                if (result.datas === 'success') {
                    $.sDialog({
                        content: '提交成功',
                        cancelBtn: false,
                        okFn: function() {
                            window.location.reload(true);
                        }
                    });
                } else {
                    $.sDialog({
                        content: '提交失败',
                        cancelBtn: false,
                        okFn: function() {
                            window.location.reload(true);
                        }
                    });
                }
            }
        });
    }
});

/* lyq@newland 添加结束   **/