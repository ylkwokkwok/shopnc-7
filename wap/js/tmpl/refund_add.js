/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/05/22        **/
/* 功能ID：SHOP009         **/
/* 申请退货退款            **/

$(function() {
    var key = getcookie('key');
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }

    // 订单ID
    var order_id = GetQueryString('order_id');
    // 商品ID
    var goods_id = GetQueryString('goods_id');
    // AJAX请求，获取数据
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_refund&op=refund_add&order_id=" + order_id + "&goods_id=" + goods_id,
        data: {key: key},
        dataType: 'json',
        success: function(result) {
            // 检测是否登录了
            checklogin(result.login);
            var data = result.datas;
            /* lyq@newland 修改开始   **/
            /* 时间：2015/05/25        **/
            /* 功能ID：SHOP009         **/
            /* 增加ajax响应数据验证    **/

            // 判断数据是否有误
            if (!data.error) {
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
                var html = template.render('refund-add-tmpl', data);
                // 更新页面html文本
                $("#login-form").html(html);
                // 提交退货申请
                $("#submitbtn").click(refund_add_action);
                // 跳转至订单详情
                $(".order-sn").click(order_detail);
                // 跳转至物流跟踪
                $("#viewdelivery-order").click(viewdelivery_order);
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

    /* lyq@newland 修改开始     **/
    /* 时间：2015/05/25          **/
    /* 功能ID：SHOP009           **/
    /* 增加buyer_message长度验证 **/
    /* lyq@newland 修改结束     **/

    // 表单验证
    $.sValid.init({
        rules: {
            reason_id: "required",
            refund_amount: {
                required: true,
                number: true
            },
            goods_num: "required",
            buyer_message: {
                required: true,
                maxlength: 300
            }
        },
        messages: {
            reason_id: "请选择退货原因",
            refund_amount: {
                required: "请填写退款金额",
                number: "退款金额必须为数字"
            },
            goods_num: {
                required: "请填写退货数量",
                number: "退货数量必须为数字"
            },
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
     * 提交退货申请
     */
    function refund_add_action() {
        // 退货退款原因ID
        var reason_id = $("#reason_id").val();
        // 退款金额
        var refund_amount = $.trim($("#refund_amount").val());
        // 退货数量
        var goods_num = $.trim($("#goods_num").val());
        // 申请原因
        var buyer_message = $.trim($("#buyer_message").val());
        // 需要验证最大最小值的数据
        var check_arr = [
            {
                val: refund_amount,
                max: $("#goods_pay_price").val(),
                min: parseFloat($(".goods_pay_price").val()) >= 0.01 ? 0.01 : 0,
                name: "退款金额"
            },
            {
                val: goods_num,
                max: $("#max_goods_num").val(),
                min: parseFloat($(".max_goods_num").val()) >= 1 ? 1 : 0,
                name: "退货数量"
            }
        ];
        // 验证数据
        if ($.sValid() && num_check(check_arr)) {
            // 成功
            // AJAX请求，更新数据
            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=member_refund&op=refund_add_action&order_id=" + order_id + "&goods_id=" + goods_id,
                data: {key: key, reason_id: reason_id, refund_amount: refund_amount, goods_num: goods_num, buyer_message: buyer_message},
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
     * 验证数字项的最大最小值
     * @param {Array} check_arr 需要验证的数据数组
     *      [{
     *          val, // 值
     *          max, // 最大值
     *          min, // 最小值
     *          name // 表单项名称
     *       }]
     * @returns {Boolean} 验证成功/失败 TRUE/FALSE
     */
    function num_check(check_arr) {
        // 错误信息
        var err_msg = "";
        // 循环数据数组
        for (var i = 0; i < check_arr.length; i++) {
            // 验证最大值
            if (parseFloat(check_arr[i].val) > parseFloat(check_arr[i].max)) {
                err_msg += "<p>最大" + check_arr[i].name + " " + check_arr[i].max + "</p>";
            }
            // 验证最小值
            if (parseFloat(check_arr[i].val) < parseFloat(check_arr[i].min)) {
                err_msg += "<p>最大" + check_arr[i].name + " " + check_arr[i].min + "</p>";
            }
        }
        // 错误信息是否为空
        if (err_msg !== "") {
            // 不为空，显示错误，返回FALSE
            $(".error-tips").html(err_msg).show();
            return false;
        } else {
            // 为空，隐藏错误区域，返回TRUE
            $(".error-tips").html("").hide();
            return true;
        }
    }

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
});


/* lyq@newland 添加结束   **/