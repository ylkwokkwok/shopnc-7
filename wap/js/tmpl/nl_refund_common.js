/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/05/26        **/
/* 功能ID：SHOP009         **/

/**
* 取消订单退款
*   订单列表/详情页面用
*   根据订单ID取消订单退款
*/
function undo_refund_order() {
    // 订单ID
    var order_id = $(this).attr("order_id");
    var key = getcookie('key');
    
    $.sDialog({
        content: '确定取消退款？',
        okFn: function() {
            $.ajax({
                type:'post',
                url:ApiUrl+"/index.php?act=member_refund&op=undo_refund_order&order_id=" + order_id,
                data:{key:key},
                dataType:'json',
                success:function(result){
                    var data = result.datas;
                    // 判断数据是否有误
                    if (!data.error) {
                        if (result.datas === 'success') {
                            $.sDialog({
                                content: '取消退款成功',
                                cancelBtn: false,
                                okFn: function() { 
                                    window.location.reload(true);
                                }
                            });
                        } else {
                            $.sDialog({
                                content: '取消退款失败',
                                cancelBtn:false,                                
                                okFn: function() { 
                                    window.location.reload(true);
                                }
                            });
                        }
                    } else {
                        $.sDialog({
                            content: data.error + '！',
                            okBtn:false,
                            cancelBtnText:'返回',
                            cancelFn: function() {
                                location.href = WapSiteUrl + '/tmpl/member/order_list.html';
                            }
                        });
                    }
                }
            });
        }
    });
}

/**
* 取消订单退款
*   退款列表/详情页面用
*   根据退货退款ID取消订单退款
*/
function undo_refund() {
    var refund_id = $(this).attr("refund_id");
    var key = getcookie('key');
    
    $.sDialog({
        content: '确定取消退款？',
        okFn: function() {
            $.ajax({
                type:'post',
                url:ApiUrl+"/index.php?act=member_refund&op=undo_refund_order&refund_id=" + refund_id,
                data:{key:key},
                dataType:'json',
                success:function(result){
                    var data = result.datas;
                    // 判断数据是否有误
                    if (!data.error) {
                        if (result.datas === 'success') {
                            $.sDialog({
                                content: '取消退款成功',
                                cancelBtn: false,
                                okFn: function() { 
                                    location.href = WapSiteUrl + '/tmpl/member/refund_list.html';
                                }
                            });
                        } else {
                            $.sDialog({
                                content: '取消退款失败',
                                cancelBtn:false,
                                okFn: function() { 
                                    location.href = WapSiteUrl + '/tmpl/member/refund_list.html';
                                }
                            });
                        }
                    } else {
                        $.sDialog({
                            content: data.error + '！',
                            okBtn:false,
                            cancelBtnText:'返回',
                            cancelFn: function() {
                                location.href = WapSiteUrl + '/tmpl/member/refund_list.html';
                            }
                        });
                    }
                }
            });
        }
    });
}

/**
* 取消退货
*   订单列表/详情页面用
*   根据订单ID，订单商品ID 取消退货
*/
function undo_return_goods() {
    // 订单ID
    var order_id = $(this).attr("order_id");
    // 订单商品ID
    var order_goods_id = $(this).attr("goods_id");
    var key = getcookie('key');
    
    $.sDialog({
        content: '确定取消退货？',
        okFn: function() {
            $.ajax({
                type:'post',
                url:ApiUrl+"/index.php?act=member_refund&op=undo_return_goods&order_id=" + order_id + "&order_goods_id=" + order_goods_id,
                data:{key:key},
                dataType:'json',
                success:function(result){
                    var data = result.datas;
                    // 判断数据是否有误
                    if (!data.error) {
                        if (result.datas === 'success') {
                            $.sDialog({
                                content: '取消退货成功',
                                cancelBtn: false,
                                okFn: function() { 
                                    window.location.reload(true);
                                }
                            });
                        } else {
                            $.sDialog({
                                content: '取消退货失败',
                                cancelBtn:false,                                
                                okFn: function() { 
                                    window.location.reload(true);
                                }
                            });
                        }
                    } else {
                        $.sDialog({
                            content: data.error + '！',
                            okBtn:false,
                            cancelBtnText:'返回',
                            cancelFn: function() {
                                location.href = WapSiteUrl + '/tmpl/member/order_list.html';
                            }
                        });
                    }
                }
            });
        }
    });
}

/**
* 取消退货
*   退款列表/详情页面用
*   根据退货退款ID 取消退货
*/
function undo_return() {
    // 根据退货退款ID
    var refund_id = $(this).attr("refund_id");
    var key = getcookie('key');
    
    $.sDialog({
        content: '确定取消退货？',
        okFn: function() {
            $.ajax({
                type:'post',
                url:ApiUrl+"/index.php?act=member_refund&op=undo_return_goods&refund_id=" + refund_id,
                data:{key:key},
                dataType:'json',
                success:function(result){
                    var data = result.datas;
                    // 判断数据是否有误
                    if (!data.error) {
                        if (result.datas === 'success') {
                            $.sDialog({
                                content: '取消退货成功',
                                cancelBtn: false,
                                okFn: function() { 
                                    location.href = WapSiteUrl + '/tmpl/member/refund_list.html?type=return';
                                }
                            });
                        } else {
                            $.sDialog({
                                content: '取消退货失败',
                                cancelBtn:false,                                
                                okFn: function() { 
                                    location.href = WapSiteUrl + '/tmpl/member/refund_list.html?type=return';
                                }
                            });
                        }
                    } else {
                        $.sDialog({
                            content: data.error + '！',
                            okBtn:false,
                            cancelBtnText:'返回',
                            cancelFn: function() {
                                location.href = WapSiteUrl + '/tmpl/member/refund_list.html?type=return';
                            }
                        });
                    }
                }
            });
        }
    });
}

/* lyq@newland 添加开始   **/
/* 时间：2015/07/23       **/
/**
 * 延时 按钮 延长收货延迟时间
 */
function delay_action() {
    var key = getcookie('key');
    var refund_id = $(this).attr('refund_id');
    $.sDialog({
        skin: "red",
        content: '商家选择没收到已经发货的商品，请联系物流进行确认，提交后将重新计时，商家可以再次确认收货。',
        okFn: function() {
            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=member_refund&op=delay&return_id=" + refund_id,
                data: {key: key},
                dataType: 'json',
                success: function(result) {
                    if (result.datas === 'success') {
                        $.sDialog({
                            content: '延时成功',
                            cancelBtn: false,
                            okFn: function() {
                                location.href = WapSiteUrl + '/tmpl/member/refund_list.html?type=return';
                            }
                        });
                    } else {
                        $.sDialog({
                            content: '延时失败',
                            cancelBtn: false,
                            okFn: function() {
                                location.href = WapSiteUrl + '/tmpl/member/refund_list.html?type=return';
                            }
                        });
                    }
                }
            });
        }
    });
    // 控制dialog宽度
    $(".s-dialog-wrapper").css('width', '180px');
    return false;
}
/* lyq@newland 添加结束   **/