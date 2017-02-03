/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/05/25        **/
/* 功能ID：SHOP009         **/
/* 申请订单退款            **/

$(function() {
    var key = getcookie('key');
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }

    var order_id = GetQueryString('order_id');
    // AJAX请求，获取数据
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_refund&op=add_refund_all&order_id=" + order_id,
        data: {key: key},
        dataType: 'json',
        success: function(result) {
            // 检测是否登录了
            checklogin(result.login);
            var data = result.datas;
            // 判断数据是否有误
            if (!data.error) {
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
                // 执行模板脚本，返回执行后的html文本
                var html = template.render('refund-add-tmpl', data);
                // 更新页面html文本
                $("#login-form").html(html);
                // 提交退货申请
                $("#submitbtn").click(add_refund_all_action);
                // 跳转至订单详情
                $(".order-sn").click(order_detail);
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

            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/10      **/
            /* wap端loading画面      **/
            // 隐藏loading画面
            $("#loading_page").hide();
            /* lyq@newland 添加结束 **/
        }
    });

    // 表单验证
    $.sValid.init({
        rules: {
            buyer_message: {
                required: true,
                maxlength: 300
            }
        },
        messages: {
            buyer_message: {
                required: "请填写退货说明",
                maxlength: "退货说明长度限制为300字"
            }
        },
        callback: function(eId, eMsg, eRules) {
            if (eId.length > 0) {
                var errorHtml = "";
                $.map(eMsg, function(idx, item) {
                    errorHtml += "<p>" + idx + "</p>";
                });
                $(".error-tips").html(errorHtml).show();
            } else {
                $(".error-tips").html("").hide();
            }
        }
    });

    /**
     * 提交退款申请
     */
    function add_refund_all_action() {
        // 申请原因
        var buyer_message = $.trim($("#buyer_message").val());
        // 验证数据
        if ($.sValid()) {
            // 成功
            // AJAX请求，更新数据
            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=member_refund&op=add_refund_all_action&order_id=" + order_id,
                data: {key: key, buyer_message: buyer_message},
                dataType: 'json',
                success: function(result) {
                    if (result.datas === 'success') {
                        $.sDialog({
                            content: '申请成功',
                            cancelBtn: false,
                            okFn: function() {
                                location.href = WapSiteUrl + '/tmpl/member/order_list.html';
                            }
                        });
                    } else {
                        $.sDialog({
                            content: '申请失败',
                            cancelBtn: false,
                            okFn: function() {
                                location.href = WapSiteUrl + '/tmpl/member/order_list.html';
                            }
                        });
                    }
                }
            });
        } else {
            // 失败
            return false;
        }
    }

    /**
     * 跳转至订单详情
     */
    function order_detail() {
        location.href = WapSiteUrl + "/tmpl/member/order_detail.html?order_id=" + $(this).attr('order_id');
    }
});


/* lyq@newland 添加结束   **/