/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* lyq@newland 添加开始   **/
/* 时间：2015/06/02        **/
/* 功能ID：SHOP017         **/
// 将商品详情页的评价信息单独提出

$(function() {
    // 获取cookie key
    var key = getcookie('key');
    // 判断是否已登录
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    }
    // 获取商品ID
    var goods_id = GetQueryString("goods_id");
    /*zly@newland定义查询查询评价数目及当前查询页码、评价显示开始**/
    /*时间：2015/05/17                                            **/
    /*功能ID：SHOP001                                             **/
    // 定义查询查询评价数目
    var page = pagesize;
    // 当前查询页码
    var curpage = 1;
    // 初始化调取好评信息
    get_goods_comment(goods_id, '1', page, curpage);
    /*zly@newland定义查询查询评价数目及当前查询页码、评价显示结束**/
    /*zly@newland获取评鉴内容开始 **/
    /*时间：2015/05/17              **/
    /*功能ID：SHOP001               **/
    /**
     * @param {type} goods_id商品ID，
     * @param {type} type 评价等级
     * @param {type} page 查询数据条数
     * @param {type} curpage当前页数
     */
    function get_goods_comment(goods_id, type, page, curpage) {

        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=goods&op=comments&page=" + page + "&curpage=" + curpage + "",
            data: {goods_id: goods_id, type: type},
            dataType: 'json',
            success: function(result) {
                if (result) {
                    var data = result.datas;
                    // 判断下一页是否可用
                    data.hasmore = result.hasmore;
                    // 当前页，判断是否上一页的disabled是否显示
                    data.curpage = curpage;
                    // 获取每页显示评价数目
                    var evaluate_length = data.goods_evaluate.length;
                    // 判断匿名评价则隐藏部分评价者账户名
                    for (var i = 0; i < evaluate_length; i++) {
                        // 不是匿名评价 显示全部用户名
                        if (data.goods_evaluate[i].geval_isanonymous == "0") {
                            data.goods_evaluate[i].geval_frommembername = data.goods_evaluate[i].geval_frommembername;
                        } else {
                            // 是匿名评价 显示全部用户名前两个字节并且拼接上**
                            var frommembername = data.goods_evaluate[i].geval_frommembername;
                            if(frommembername.length <= 2){
                                data.goods_evaluate[i].geval_frommembername = '**';
                            }else{
                            data.goods_evaluate[i].geval_frommembername = frommembername.substr(0, 2) + "**";
                            }
                        }
                    }

                    /* lyq@newland 添加开始 **/
                    /* 时间：2015/06/12      **/
                    /* 优化评价详情          **/
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
                    /* lyq@newland 添加结束 **/

                    // 渲染页面
                    var html = template.render('evaluation_content', data);
                    $("#evaluation").html(html);

                    /* lyq@newland 添加开始 **/
                    /* 时间：2015/06/12      **/
                    /* 优化评价详情          **/
                    // 更改评价类型
                    $(".comment_tab a").click(change_type);
                    /* lyq@newland 添加结束 **/

                    // 跳转下一页
                    $('.next-page').click(function() {
                        nextPage(goods_id, type, page, curpage);
                        // 页面跳转到评论首位置
                        document.getElementById("evaluation_content_before").scrollIntoView();
                    });
                    // 跳转上一页
                    $('.pre-page').click(function() {
                        prePage(goods_id, type, page, curpage);
                        // 页面跳转到评论首位置
                        document.getElementById("evaluation_content_before").scrollIntoView();
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
    }
    /**
     * 
     * @param {type} goods_id商品ID，
     * @param {type} type 评价等级
     * @param {type} page 查询数据条数
     * @param {type} curpage当前页数
     */
    function nextPage(goods_id, type, page, curpage) {
        var self = $('.next-page');
        var hasMore = self.attr("has_more");
        // 判断 下一页 功能是否能够使用
        if (hasMore == "true") {
            curpage = curpage + 1;
            // 实现下一页的翻页跳转
            get_goods_comment(goods_id, type, page, curpage);
        }
    }
    /**
     * 
     * @param {type} goods_id商品ID，
     * @param {type} type 评价等级
     * @param {type} page 查询数据条数
     * @param {type} curpage当前页数
     * @returns {undefined}商品评价内容
     */
    function prePage(goods_id, type, page, curpage) {
        var self = $('.pre-page');
        if (curpage > 1) {
            self.removeClass("disabled");
            // 实现页码上翻
            curpage = curpage - 1;
            // 实现上一页的翻页跳转
            get_goods_comment(goods_id, type, page, curpage);
        }
    }
    /*zly@newland获取评鉴内容结束**/
    
    /* lyq@newland 添加开始 **/
    /* 时间：2015/06/12      **/
    /* 优化评价详情          **/

    /**
     * 改变评价类型
     */
    function change_type() {
        // 移除上一次选中的评价类型的class
        $("a.choosen").removeClass('choosen');
        // 添加本次选中的评价类型的class
        $(this).addClass('choosen');
        // 当前页初始化：1
        curpage = 1;
        // 重新初始化页面
        get_goods_comment(goods_id, $(this).parent().parent().attr('data-type'), page, curpage);
    }
    /* lyq@newland 添加结束 **/
});

/* lyq@newland 添加结束   **/