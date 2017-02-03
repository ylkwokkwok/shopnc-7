$(function() {
    var key = getcookie('key');
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }
    var page = pagesize;
    var curpage = 1;
    var hasMore = true;

    var readytopay = false;
    
    /* lyq@newland 添加开始 **/
    /* 时间：2015/06/10      **/
    /* wap端loading画面      **/
    // ajax响应 计数器
    var ajax_count = 0;
    /* lyq@newland 添加结束 **/
    /* zly@newland 添加订单状态 开始**/
    /* 时间：2015/07/21          **/
    var ifcart = GetQueryString("ifcart");
    var cart_id = GetQueryString("cart_id");
    var goods_id = GetQueryString("goods_id");
    var buynum = GetQueryString("buynum");
    var order_state = GetQueryString("order_state");
    if(order_state !== null){
        $("#order-state").val(order_state);
    }
    /* zly@newland 添加订单状态 结束**/
    /**
     * 初始化页面
     * @param {type} page 每页显示数
     * @param {type} curpage 当前页
     * @param {type} query_str 查询条件
     */
    function initPage(page, curpage, query_str) {
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_order&op=order_list&page=" + page + "&curpage=" + curpage + "&getpayment=true&order_state=" + $("#order-state").val() + query_str,
            data: {key: key},
            dataType: 'json',
            success: function(result) {
                checklogin(result.login);//检测是否登录了
                var data = result.datas;
                /* lyq@newland 修改开始   **/
                /* 时间：2015/05/25        **/
                /* 功能ID：SHOP009         **/
                /* 增加ajax响应数据验证    **/

                // 判断数据是否有误
                if (!data.error) {
                    // 是不是可以用下一页的功能，传到页面里去判断下一页是否可以用
                    data.hasmore = result.hasmore;
                    // 页面地址
                    data.WapSiteUrl = WapSiteUrl;
                    // 当前页，判断是否上一页的disabled是否显示
                    data.curpage = curpage;
                    // API地址
                    data.ApiUrl = ApiUrl;
                    // cookie key
                    data.key = getcookie('key');
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
                    template.helper('p2f', function(s) {
                        return (parseFloat(s) || 0).toFixed(2);
                    });
                    var html = template.render('order-list-tmpl', data);
                    $("#order-list").html(html);
                    // 取消订单
                    $(".cancel-order").click(cancelOrder);
                    // 下一页
                    $(".next-page").click(nextPage);
                    // 上一页
                    $(".pre-page").click(prePage);
                    // 确认订单
                    $(".sure-order").click(sureOrder);
                    // 物流跟踪
                    $('.viewdelivery-order').click(viewOrderDelivery);
                    /*zly@newland 我要评价按钮 点击开始**/
                    /* 时间：2015/05/12                **/
                    /* 功能ID:SHOP001                  **/
                    // 评价按钮点击
                    $('.evaluation').click(Evaluation);
                    /*zly@newland 我要评价按钮 点击结束**/
                    $('.check-payment').click(function() {
                        if (!readytopay) {
                            $.sDialog({
                                skin: "red",
                                content: '暂无可用的支付方式',
                                okBtn: false,
                                cancelBtn: false
                            });
                            return false;
                        }
                    });

                    /* lyq@newland 添加开始   **/
                    /* 时间：2015/05/18        **/
                    /* 功能ID：SHOP010         **/
                    // 订单状态改变事件
                    $("#order-state").one('change', function() {
                        search_order();
                    });
                    /* lyq@newland 添加结束   **/

                    /* lyq@newland 添加开始   **/
                    /* 时间：2015/05/22       **/
                    /* 功能ID：SHOP009        **/
                    // 退货 按钮点击事件
                    $('.return-goods').click(function() {
                        location.href = WapSiteUrl + "/tmpl/member/refund_add.html?order_id=" + $(this).attr('order_id') + "&goods_id=" + $(this).attr('goods_id');
                    });
                    /* lyq@newland 添加结束   **/

                    /* lyq@newland 添加开始   **/
                    /* 时间：2015/05/25       **/
                    /* 功能ID：SHOP009        **/
                    // 订单退款 按钮点击事件
                    $(".refund-order").click(function() {
                        location.href = WapSiteUrl + "/tmpl/member/add_refund_all.html?order_id=" + $(this).attr('order_id');
                    });
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
                    /* 时间：2015/05/27       **/
                    /* 功能ID：SHOP009        **/
                    // 交易投诉 按钮点击事件
                    $(".complain-order").click(function() {
                        location.href = WapSiteUrl + "/tmpl/member/add_complain.html?order_id=" + $(this).attr('order_id') + "&goods_id=" + $(this).attr('goods_id');
                    });
                    /* lyq@newland 添加结束   **/

                    /* lyq@newland 添加开始   **/
                    /* 时间：2015/05/28        **/
                    /* 功能ID：SHOP014         **/
                    // 检索订单
                    $("#order-search").one('click', search_order);
                    // 清空查询条件
                    $("#clear-query").click(function() {
                        $(".order-search input").val('');
                    });
                    /* lyq@newland 添加结束   **/
                    
                    /* hl@newland 添加开始   **/
                    /* 时间：2015/06/18        **/
                    /* 功能ID：SHOP014         **/
                    $(".payment").tap(function(){
                    	wx_pay($(this).attr("pay_sn"));
                    })
					/* hl@newland 添加结束   **/
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
                // 计数器累加
                ajax_count++;
                // 如果计数完成
                if (ajax_count === 2) {
                    // 显示页面内容
                    $(".order-state").show();
                    // 隐藏loading画面
                    $("#loading_page").hide();
                }
                /* lyq@newland 添加结束 **/

                /* lyq@newland 修改开始 **/
                /* 时间：2015/06/10      **/
                // 页面置顶
                document.body.scrollTop = 0;
                /* lyq@newland 修改结束 **/
            }
        });

        $.ajax({
            type: 'get',
            url: ApiUrl + "/index.php?act=member_payment&op=payment_list",
            data: {key: key},
            dataType: 'json',
            success: function(result) {
                $.each((result && result.datas && result.datas.payment_list) || [], function(k, v) {
                    // console.log(v);
                    if (v != '') {
                        readytopay = true;
                        return false;
                    }
                });
                
                /* lyq@newland 添加开始 **/
                /* 时间：2015/06/10      **/
                /* wap端loading画面      **/
                // 计数器累加
                ajax_count++;
                // 如果计数完成
                if (ajax_count === 2) {
                    // 显示页面内容
                    $(".order-state").show();
                    // 隐藏loading画面
                    $("#loading_page").hide();
                }
                /* lyq@newland 添加结束 **/
            }
        });
    }
    //初始化页面 执行检索
    search_order();

    //下一页
    function nextPage() {
        var self = $(this);
        var hasMore = self.attr("has_more");
        if (hasMore == "true") {
            curpage = curpage + 1;
            initPage(page, curpage, get_query_str());
        }
    }
    //上一页
    function prePage() {
        var self = $(this);
        if (curpage > 1) {
            self.removeClass("disabled");
            curpage = curpage - 1;
            initPage(page, curpage, get_query_str());
        }
    }

    //取消订单
    function cancelOrder() {
        var order_id = $(this).attr("order_id");

        $.sDialog({
            content: '确定取消订单？',
            okFn: function() {
                cancelOrderId(order_id);
            }
        });
    }

    function cancelOrderId(order_id) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/index.php?act=member_order&op=order_cancel",
            data: {order_id: order_id, key: key},
            dataType: "json",
            success: function(result) {
                if (result.datas && result.datas == 1) {
                    initPage(page, curpage, get_query_str());
                }
            }
        });
    }

    //确认订单
    function sureOrder() {
        var order_id = $(this).attr("order_id");
        /*zly@newland更改收货提示，并跳转评价页面 开始**/
        /*时间：2015/07/24                            **/
        $.sDialog({
            content: '如您未收到货物，请勿确认！',
            "okBtnText": "确认",
            okFn: function() {
                sureOrderId(order_id);
            }
        });
        /*zly@newland更改收货提示，并跳转评价页面 结束**/
    }

    function sureOrderId(order_id) {
        $.ajax({
            type: "post",
            url: ApiUrl + "/index.php?act=member_order&op=order_receive",
            data: {order_id: order_id, key: key},
            dataType: "json",
            success: function(result) {
                /*zly@newland更改收货提示，并跳转评价页面 开始**/
                /*时间：2015/07/24                            **/
                if (result.datas && result.datas == 1) {
                    $.sDialog({
                        content: '收货成功！',
                        "okBtn": false, //是否显示确定按钮
                        "cancelBtn": false //是否显示确定按钮
                    });
                    setTimeout(function() {
                        location.href = WapSiteUrl + '/tmpl/member/evaluation.html?order_id=' + order_id;
                    }, 2000);
                } else {
                    $.sDialog({
                    content: '收货失败！',
                    okFn: function() {
                        initPage(page, curpage, get_query_str());
                        }
                    });
                }
                /*zly@newland更改收货提示，并跳转评价页面 结束**/
            }
        });
    }

    function viewOrderDelivery() {
        var orderId = $(this).attr('order_id');
        location.href = WapSiteUrl + '/tmpl/member/order_delivery.html?order_id=' + orderId;
    }

    /* zly@newland 我要评价 按钮跳转评价页面开始**/
    /* 时间：2015/05/12                         **/
    /* 功能ID:SHOP001                           **/
    function Evaluation() {
        // 订单ID
        var orderId = $(this).attr('order_id');
        location.href = WapSiteUrl + '/tmpl/member/evaluation.html?order_id=' + orderId;
    }
    /* zly@newland 我要评价 按钮跳转评价页面结束**/


    /* lyq@newland 添加开始   **/
    /* 时间：2015/05/28        **/
    /* 功能ID：SHOP014         **/
    /**
     * 根据条件查找订单信息
     */
    function search_order() {
        // 参数字符串
        var param_str = get_query_str();

        // 重新初始化页面
        initPage(page, 1, param_str);
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
        // 日期范围 开始日期
        var query_start_date = $("input[name=query_start_date]").val();
        // 日期范围 结束日期
        var query_end_date = $("input[name=query_end_date]").val();
        // 参数字符串
        var param_str = "";
        // 判断查询参数是否为空，并拼接查询条件
        if (order_sn != '') {
            param_str += '&order_sn=' + order_sn;
        }
        if (query_start_date != '') {
            param_str += '&query_start_date=' + query_start_date;
        }
        if (query_end_date != '') {
            param_str += '&query_end_date=' + query_end_date;
        }
        // 返回参数字符串
        return param_str;
    }
    /* lyq@newland 添加结束   **/
    
    /* hl@newland 添加开始   **/
    /* 时间：2015/06/18        **/
    /* 功能ID：SHOP014         **/
    
    function wx_pay(pay_sn) {
         //定义Json对象
        var json = {};
        //给json 对象添加数据
        json.key = key;
        json.pay_sn = pay_sn;
        $.ajax({
                   type: 'post',
                   async : false,
                   url: ApiUrl + '/index.php?act=member_buy&op=get_limit',
                   data: json,
                   dataType: 'json',
                   success: function(result) { 
                      var p_limit = result.datas.resultlimit[0].purchase_limit;
                      var goodsId = result.datas.resultlimit[0].goods_id;
                      var goods_name = result.datas.resultlimit[0].goods_name;
                      if(p_limit>0){
                           $.ajax({
                                type: 'post',
                                url: ApiUrl + '/index.php?act=member_buy&op=get_goods_buyed_count',
                                data: {key:key,goods_id:goodsId},
                                async: false,
                                dataType: 'json',
                                success: function(result) {
                                   var  buyed_num = result.datas.buyed_num;
                                     if (buyed_num > p_limit) {
                                          $.sDialog({
                                                // 弹出消息对话框
                                                    skin: "red",
                                                    content:  '商品【'+goods_name+'】超出限购数量！',
                                                    okBtn: false,
                                                    cancelBtn: false
                                                });
                                         return false;
                                       }else{
                                            pay(pay_sn);
                                       }
                                }
                              });
                      }else{
                          pay(pay_sn);
                      }
                  }
       });
    }
    /* hl@newland 添加结束   **/
    function pay(pay_sn){
        $.ajax({
            type: "post",
            url: SiteUrl + "/wx/wx_pay.php",
            data: {pay_sn: pay_sn},
            dataType: "json",
            /* lyq@newland 添加开始   **/
            /* 时间：2015/06/19        **/
            beforeSend: function() {
                // 显示页面覆盖层
                $("#wxpay_loading_mask").show();
                // 显示loading动画
                $("#wxpay_loading").show();
            },
            success: function(result) {
                // 隐藏页面覆盖层
                $("#wxpay_loading_mask").hide();
                // 隐藏loading动画
                $("#wxpay_loading").hide();
            /* lyq@newland 添加结束   **/
            	WeixinJSBridge.invoke(
				'getBrandWCPayRequest', {
		           "appId" :result.appId,     //公众号名称，由商户传入     
		           "timeStamp":result.timeStamp,         //时间戳，自1970年以来的秒数     
		           "nonceStr" :result.nonceStr, //随机串     
		           "package" :result.package,     
		           "signType" : "MD5",         //微信签名方式:     
		           "paySign" :result.paySign //微信签名 
		       },
				function(res){
                                        /* lyq@newland 修改开始   **/
                                        /* 时间：2015/06/19        **/
					if ("get_brand_wcpay_request:ok" == res.err_msg) {
                                                // 弹出消息对话框
                                                $.sDialog({
                                                    skin: "green",
                                                    content: '恭喜您，已成功付款！',
                                                    okBtn: false,
                                                    cancelBtn: false
                                                });
                                                // 重新加载页面
                                                setTimeout("location.reload();",1000);
					} else if ("get_brand_wcpay_request:cancel" == res.err_msg) {
                                                // 弹出消息对话框
                                                $.sDialog({
                                                    content: '您已取消付款了！',
                                                    okBtn: false,
                                                    cancelBtn: false
                                                });
					} else {
                                                $.sDialog({
                                                // 弹出消息对话框
                                                    skin: "red",
                                                    content: '付款失败，请稍后重试！',
                                                    okBtn: false,
                                                    cancelBtn: false
                                                });
					}
                                        /* lyq@newland 修改结束   **/
				}
			);
            },
            error: function() {
            }
        });
    }
});
