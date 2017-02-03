/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/05/27        **/
/* 功能ID：SHOP009         **/
/* 申请交易投诉            **/

$(function() {
    var key = getcookie('key');
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }

    // 订单ID
    var order_id = GetQueryString('order_id');
    // 订单商品ID
    var goods_id = GetQueryString('goods_id');
    // AJAX请求，获取数据
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_complain&op=add_complain&order_id=" + order_id + "&goods_id=" + goods_id,
        data: {key: key},
        dataType: 'json',
        success: function(result) {
            // 检测是否登录了
            checklogin(result.login);
            var data = result.datas;
            /* wqw@newland 添加开始   **/
            /* 时间：2015/06/03        **/
            /* 功能ID：ADMIN006      **/
            data.SiteUrl = SiteUrl;
            /* wqw@newland 添加结束   **/
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
                /* wqw@newland 添加开始   　**/
                /* 时间：2015/06/08         **/
                /* 功能ID：ADMIN006         **/
                template.helper('in_array', function(str, arr) {
                    return $.inArray(str, arr);
                });
                // 执行模板脚本，返回执行后的html文本
                var html = template.render('refund-add-tmpl', data);
                // 更新页面html文本
                $("#login-form").html(html);
                // 提交退货申请
                $("#submitbtn").click(add_complain_action);
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
                // 控制dialog宽度
                $(".s-dialog-wrapper").css('max-width', '180px');
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
            input_complain_content: {
                required: true,
                maxlength: 255
            }
        },
        messages: {
            input_complain_content: {
                required: "请填写投诉内容",
                maxlength: "投诉内容长度限制为255字"
            }
        },
        callback: function(eId, eMsg, eRules) {

            if (eId.length > 0) {
                var errorHtml = "";
                // 验证投诉主题是否被选择
                if (!radio_check()) {
                    // 未选择，添加错误信息
                    errorHtml += "<p>请选择投诉主题</p>";
                }
                $.map(eMsg, function(idx, item) {
                    errorHtml += "<p>" + idx + "</p>";
                });
                $(".error-tips").html(errorHtml).show();
            }
            /* lyq@newland 添加开始   **/
            /* 时间：2015/05/27        **/
            /* 功能ID：SHOP009         **/
            // 投诉内容验证通过，投诉主题验证不通过时，显示错误信息
            else if (!radio_check()) {
                // 未选择，添加错误信息
                var errorHtml = "<p>请选择投诉主题</p>";
                // 显示错误信息
                $(".error-tips").html(errorHtml).show();
            }
            /* lyq@newland 添加结束   **/
            else {
                $(".error-tips").html("").hide();
            }
        }
    });

    /**
     * 提交退款申请
     */
    function add_complain_action() {
        // 投诉主题
        var input_complain_subject = $("input[name=input_complain_subject]:checked").val();
        // 投诉内容
        var input_complain_content = $.trim($("#input_complain_content").val());
        // 验证数据
        if ($.sValid() && radio_check()) {
            // 成功
            // AJAX请求，更新数据
            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=member_complain&op=save_complain",
                data: {
                    key: key,
                    input_order_id: order_id,
                    input_goods_id: goods_id,
                    input_complain_subject: input_complain_subject,
                    input_complain_content: input_complain_content
                },
                dataType: 'json',
                success: function(result) {
                    // 判断数据是否有误
                    if (!result.datas.error) {
                        if (result.datas === 'success') {
                            $.sDialog({
                                content: '投诉成功',
                                cancelBtn: false,
                                okFn: function() {
                                    location.href = WapSiteUrl + '/tmpl/member/complain_list.html';
                                }
                            });
                        } else {
                            $.sDialog({
                                content: '投诉失败',
                                cancelBtn: false,
                                okFn: function() {
                                    location.href = WapSiteUrl + '/tmpl/member/complain_list.html';
                                }
                            });
                        }
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
                }
            });
            // 控制dialog宽度
            $(".s-dialog-wrapper").css('max-width', '180px');
        } else {
            // 失败
            return false;
        }
    }

    /**
     * 检查投诉主题是否被选中
     * @returns {Boolean} true/false
     */
    function radio_check() {
        if (typeof ($("input[name=input_complain_subject]:checked").val()) == 'undefined') {
            return false;
        } else {
            return true;
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