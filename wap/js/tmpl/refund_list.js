/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/05/19        **/
/* 功能ID：SHOP009         **/
/* 退货退款列表            **/

$(function() {
    var key = getcookie('key');
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }
    var page = pagesize;
    var curpage = 1;

    /**
     * 初始化页面
     * @param {type} page 每页显示数
     * @param {type} curpage 当前页
     * @param {type} type 类型 退货/退款
     * @param {type} query_str 查询条件
     */
    function init_page(page, curpage, type, query_str) {
        // AJAX请求，获取数据
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_refund&op=refund_list&page=" + page + "&curpage=" + curpage + "&type=" + type + query_str,
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
                    // 改变title
                    if (type == 'return') {
                        document.title = '退货列表';
                        $('#type_des').html('退货');
                        $('#header h2').html('退货列表');
                    } else if (type == 'refund') {
                        document.title = '退款列表';
                        $('#type_des').html('退款');
                        $('#header h2').html('退款列表');
                    }
                    // 是不是可以用下一页的功能，传到页面里去判断下一页是否可以用
                    data.hasmore = result.hasmore;
                    // 当前页，判断是否上一页的disabled是否显示
                    data.curpage = curpage;
                    // 列表类型
                    data.type = type;
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
                    var html = template.render('refund-list-tmpl', data);
                    // 更新页面html文本
                    $("#refund-list").html(html);

                    // 页面事件绑定

                    /* lyq@newland 添加开始   **/
                    /* 时间：2015/05/25        **/
                    /* 功能ID：SHOP009         **/
                    // 绑定订单状态改变事件
                    // initPage中第一个ajax请求的get参数中追加 order_state（订单状态）
                    $("#refund_type").one('change', function() {
                        search_refund();
                    });
                    /* lyq@newland 添加结束   **/

                    // 下一页
                    $(".next-page").click(nextPage);
                    // 上一页
                    $(".pre-page").click(prePage);
                    // 跳转至商品详情
                    $(".order-pdpic").click(product_detail);
                    // 跳转至订单详情
                    $(".order-sn").click(order_detail);
                    // 跳转至退货详情  查看 按钮 
                    $(".view-refund-detail").click(refund_detail);
                    // 跳转至退货详情  退货 按钮
                    $(".refund-action").click(refund_action);
                    // 延时 按钮
                    $(".delay-action").click(delay_action);

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
                    /* 时间：2015/05/28        **/
                    /* 功能ID：SHOP014         **/

                    // 检索订单
                    $("#refund-search").one('click', search_refund);
                    // 清空查询条件
                    $("#clear-query").click(function() {
                        $(".refund-search input").val('');
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
                // 页面置顶
                document.body.scrollTop = 0;
                // 显示页面内容
                $(".type_select").show();
                // 隐藏loading画面
                $("#loading_page").hide();
                /* lyq@newland 添加结束 **/
            }
        });
    }

    /* lyq@newland 修改开始   **/
    /* 时间：2015/05/26        **/
    /* 功能ID：SHOP009         **/

    // 初始化页面
    // 获取列表类型
    var type = GetQueryString("type");
    // 判断列表类型
    if (type == 'return') {
        // 退货列表
        init_page(page, curpage, 'return', get_query_str());
        $("#refund_type").val('return');
    } else if (type == 'refund') {
        // 退款列表
        init_page(page, curpage, 'refund', get_query_str());
        $("#refund_type").val('refund');
    } else {
        // 未指定类型时，执行检索
        search_refund();
    }

    /* lyq@newland 修改结束   **/

    /**
     * 下一页
     */
    function nextPage() {
        var self = $(this);
        var hasMore = self.attr("has_more");
        // 有更多页
        if (hasMore == "true") {
            // 当前页数改变
            curpage = curpage + 1;
            // 重载页面
            init_page(page, curpage, $("#refund_type").val(), get_query_str());
        }
    }

    /**
     * 上一页
     */
    function prePage() {
        var self = $(this);
        // 当前也不是第一页
        if (curpage > 1) {
            // 上一页按钮可用
            self.removeClass("disabled");
            // 当前页数改变
            curpage = curpage - 1;
            // 重载页面
            init_page(page, curpage, $("#refund_type").val(), get_query_str());
        }
    }

    /**
     * 跳转至商品详情
     */
    function product_detail() {
        location.href = WapSiteUrl + "/tmpl/product_detail.html?goods_id=" + $(this).attr('goods_id');
    }

    /**
     * 跳转至订单详情
     */
    function order_detail() {
        location.href = WapSiteUrl + "/tmpl/member/order_detail.html?order_id=" + $(this).attr('order_id');
    }

    /**
     * 查看 按钮 跳转至退货详情
     */
    function refund_detail() {
        location.href = WapSiteUrl + "/tmpl/member/refund_detail.html?refund_id=" + $(this).attr('refund_id') + "&type=detail";
    }

    /**
     * 退货 按钮 跳转至退货详情
     */
    function refund_action() {
        location.href = WapSiteUrl + "/tmpl/member/refund_detail.html?refund_id=" + $(this).attr('refund_id') + "&type=action";
    }

    /* lyq@newland 添加开始   **/
    /* 时间：2015/05/28        **/
    /* 功能ID：SHOP014         **/

    /**
     * 根据条件查找退货/退款信息
     */
    function search_refund() {
        // 参数字符串
        var param_str = get_query_str();

        // 重新初始化页面
        init_page(page, 1, $("#refund_type").val(), param_str);
    }
    /* lyq@newland 添加结束   **/
    
    /* lyq@newland 添加开始   **/
    /* 时间：2015/06/26        **/
    /* 功能ID：SHOP014         **/
    /**
     * 获取参数字符串
     * @returns {String} 参数字符串
     */
    function get_query_str() {
        // 订单编号
        var order_sn = $.trim($("input[name=order_sn]").val());
        // 退款/退货编号
        var refund_sn = $.trim($("input[name=refund_sn]").val());
        // 日期范围 开始日期
        var add_time_from = $("input[name=add_time_from]").val();
        // 日期范围 结束日期
        var add_time_to = $("input[name=add_time_to]").val();
        // 参数字符串
        var param_str = "";
        // 判断查询参数是否为空，并拼接查询条件
        if (order_sn != '') {
            param_str += '&order_sn=' + order_sn;
        }
        if (refund_sn != '') {
            param_str += '&refund_sn=' + refund_sn;
        }
        if (add_time_from != '') {
            param_str += '&add_time_from=' + add_time_from;
        }
        if (add_time_to != '') {
            param_str += '&add_time_to=' + add_time_to;
        }
        // 返回参数字符串
        return param_str;
    }
    /* lyq@newland 添加结束   **/
});


/* lyq@newland 添加结束   **/