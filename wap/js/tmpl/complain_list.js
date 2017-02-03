/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/05/27        **/
/* 功能ID：SHOP009         **/
/* 交易投诉列表            **/

$(function() {
    var key = getcookie('key');
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }
    var page = pagesize;
    var curpage = 1;
    var hasMore = true;

    var readytopay = false;

    function initPage(page, curpage, select_complain_state) {
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_complain&op=complain_list&page=" + page + "&curpage=" + curpage + "&select_complain_state=" + select_complain_state,
            data: {key: key},
            dataType: 'json',
            success: function(result) {
                // 检测是否登录了
                checklogin(result.login);
                var data = result.datas;
                /* wqw@newland 添加开始   **/
                /* 时间：2015/06/02        **/
                /* 功能ID：ADMIN006      **/
                data.SiteUrl = SiteUrl;
                /* wqw@newland 添加结束   **/
                // 判断数据是否有误
                if (!data.error) {
                    // 是不是可以用下一页的功能，传到页面里去判断下一页是否可以用
                    data.hasmore = result.hasmore;
                    // 页面地址
                    data.WapSiteUrl = WapSiteUrl;
                    // 当前页，判断是否上一页的disabled是否显示
                    data.curpage = curpage;
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
                    /* wqw@newland 添加结束   **/
                    // 执行模板脚本，返回执行后的html文本
                    var html = template.render('order-list-tmpl', data);
                    $("#order-list").html(html);
                    // 下一页
                    $(".next-page").click(nextPage);
                    // 上一页
                    $(".pre-page").click(prePage);
                    // 取消投诉
                    $(".cancel-complain").click(cancel_complain);
                    // 改变投诉状态 重新初始化页面
                    $("#select_complain_state").one('change', function() {
                        initPage(page, 1, $(this).val());
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

                /* lyq@newland 添加开始 **/
                /* 时间：2015/06/10      **/
                /* wap端loading画面      **/
                // 显示页面内容
                $("#order-search").show();
                // 隐藏loading画面
                $("#loading_page").hide();
                /* lyq@newland 添加结束 **/

                /* lyq@newland 修改开始 **/
                /* 时间：2015/06/10      **/
                // 页面置顶
                document.body.scrollTop = 0;
                /* lyq@newland 修改结束 **/
            }
        });
    }
    //初始化页面
    initPage(page, curpage, $("#select_complain_state").val());

    /**
     * 下一页
     */
    function nextPage() {
        var self = $(this);
        var hasMore = self.attr("has_more");
        if (hasMore == "true") {
            curpage = curpage + 1;
            initPage(page, curpage, $("#select_complain_state").val());
        }
    }

    /**
     * 上一页
     */
    function prePage() {
        var self = $(this);
        if (curpage > 1) {
            self.removeClass("disabled");
            curpage = curpage - 1;
            initPage(page, curpage, $("#select_complain_state").val());
        }
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
                                    window.location.reload(true);
                                }
                            });
                        } else {
                            $.sDialog({
                                content: '取消投诉失败',
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
        // 控制dialog宽度
        $(".s-dialog-wrapper").css('max-width', '180px');
        return false;
    }
});

/* lyq@newland 添加结束   **/
