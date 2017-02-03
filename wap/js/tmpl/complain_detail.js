/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/05/27        **/
/* 功能ID：SHOP009         **/
/* 投诉详细                **/

$(function() {
    // 获取cookie key
    var key = getcookie('key');
    // 判断是否已登录
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }
    // 获取投诉ID
    var complain_id = GetQueryString("complain_id");

    // AJAX请求，获取数据
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_complain&op=complain_detail&complain_id=" + complain_id,
        data: {key: key},
        dataType: 'json',
        success: function(result) {
            // 检测是否登录了
            checklogin(result.login);
            // 响应数据
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
                /* wqw@newland 添加开始   　**/
                /* 时间：2015/06/08         **/
                /* 功能ID：ADMIN006         **/
                data.SiteUrl = SiteUrl;
                template.helper('in_array', function(str, arr) {
                    return $.inArray(str, arr);
                });
                /* wqw@newland 添加结束   **/
                // 执行模板脚本，返回执行后的html文本
                var html = template.render('complain-detail-tmpl', data);
                // 更新页面html文本
                $("#complain-detail").html(html);

                // 跳转至订单详情
                $(".order-sn").click(order_detail);
                // 取消投诉
                $(".cancel-complain").click(cancel_complain);

                // 获取对话
                get_complain_talk();
                // 发布对话
                $("#btn_publish").click(function() {
                    // 验证对话信息是否为空
                    if ($.trim($("#complain_talk").val()) == '') {
                        $.sDialog({
                            skin: "red",
                            content: '对话不能为空',
                            okBtn: false,
                            cancelBtnText: '返回'
                        });
                    } else {
                        publish_complain_talk();
                    }
                });
                // 刷新对话
                $("#btn_refresh").click(function() {
                    get_complain_talk();
                });
                // 提交仲裁
                $("#btn_handle").click(function() {
                    $.sDialog({
                        skin: "red",
                        content: '确认提交仲裁,提交后管理员将做出裁决',
                        okFn: apply_handle
                    });
                    // 控制dialog宽度
                    $(".s-dialog-wrapper").css('max-width', '180px');
                });

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

    /**
     * 跳转至订单详情
     */
    function order_detail() {
        location.href = WapSiteUrl + "/tmpl/member/order_detail.html?order_id=" + $(this).attr('order_id');
    }

    /**
     * 取消投诉
     */
    function cancel_complain() {
        var complain_id = $(this).attr('complain_id');
        $.sDialog({
            skin: "red",
            content: '确认取消该投诉?',
            okFn: function() {
                $.ajax({
                    type: 'post',
                    url: ApiUrl + "/index.php?act=member_complain&op=cancel_complain&complain_id=" + complain_id,
                    data: {key: key},
                    dataType: 'json',
                    success: function(result) {
                        if (result.datas === 'success') {
                            $.sDialog({
                                content: '取消投诉成功',
                                cancelBtn: false,
                                okFn: function() {
                                    location.href = WapSiteUrl + "/tmpl/member/complain_list.html";
                                }
                            });
                        } else {
                            $.sDialog({
                                content: '取消投诉失败',
                                cancelBtn: false,
                                okFn: function() {
                                    location.href = WapSiteUrl + "/tmpl/member/complain_list.html";
                                }
                            });
                        }
                    }
                });
            }
        });
        // 控制dialog宽度
        $(".s-dialog-wrapper").css('max-width', '180px');
        return false;
    }

    /**
     * 获取对话
     */
    function get_complain_talk() {
        $("#div_talk").empty();
        $.ajax({
            type: 'POST',
            url: ApiUrl + '/index.php?act=member_complain&op=get_complain_talk',
            cache: false,
            data: {key: key, complain_id: complain_id},
            dataType: 'json',
            success: function(result) {
                if (result.datas == 'none' || result.datas.length < 1) {
                    $("#div_talk").append("<p class='admin'>目前没有对话</p>");
                } else {
                    for (var i = 0; i < result.datas.length; i++)
                    {
                        $("#div_talk").append("<p class='" + result.datas[i].css + "'>" + result.datas[i].talk + "</p>");
                    }
                }

                /* lyq@newland 添加开始 **/
                /* 时间：2015/06/10      **/
                /* wap端loading画面      **/
                // 隐藏loading画面
                $("#loading_page").hide();
                /* lyq@newland 添加结束 **/
            }
        });
    }

    /**
     * 发布对话
     */
    function publish_complain_talk() {
        $.ajax({
            type: 'POST',
            url: ApiUrl + '/index.php?act=member_complain&op=publish_complain_talk',
            cache: false,
            data: {key: key, complain_id: complain_id, complain_talk: $.trim($("#complain_talk").val())},
            dataType: 'json',
            success: function(result) {
                var data = result.datas;
                // 判断数据是否有误
                if (!data.error) {
                    if (data == 'success') {
                        $("#complain_talk").val('');
                        get_complain_talk();
                        $.sDialog({
                            content: '对话发送成功',
                            cancelBtn: false,
                        });
                    }
                } else {
                    $.sDialog({
                        content: data.error + '！',
                        okBtn: false,
                        cancelBtnText: '确定',
                        cancelFn: function() {
                            window.location.reload(true);
                        }
                    });
                }
            }
        });
    }

    /**
     * 提交仲裁
     */
    function apply_handle() {
        $.ajax({
            type: 'POST',
            url: ApiUrl + '/index.php?act=member_complain&op=apply_handle',
            cache: false,
            data: {key: key, input_complain_id: complain_id},
            dataType: 'json',
            success: function(result) {
                var data = result.datas;
                // 判断数据是否有误
                if (!data.error) {
                    if (data == 'success') {
                        $.sDialog({
                            content: '提交仲裁成功',
                            okBtn: false,
                            cancelBtnText: '确定',
                            cancelFn: function() {
                                window.location.reload(true);
                            }
                        });
                    }
                } else {
                    $.sDialog({
                        content: data.error + '！',
                        cancelFn: function() {
                            window.location.reload(true);
                        }
                    });
                }
            }
        });
    }
});

/* lyq@newland 添加结束   **/