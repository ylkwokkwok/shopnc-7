$(function() {
    $("input[name=keyword]").val(escape(GetQueryString('keyword')));
    $("input[name=gc_id]").val(GetQueryString('gc_id'));


    $(".page-warp").click(function() {
        $(this).find(".pagew-size").toggle();
    });

    if ($("input[name=gc_id]").val() != '') {
        /* lyq@newland 修改开始              **/
        /* 时间：2015/06/04                   **/
        /* 返回商品列表页无法保持历史状态的问题 **/
        // 排序条件
        var key = $("input[name=key]").val();
        // 排序方式
        var order = parseInt($("input[name=order]").val());
        // 当前页
        var curpage = parseInt($("input[name=curpage]").val());
        // 商品分类id
        var gc_id = parseInt($("input[name=gc_id]").val());
        /* lyq@newland 修改结束              **/
        $.ajax({
            /* lyq@newland 修改开始              **/
            /* 时间：2015/06/04                   **/
            /* 返回商品列表页无法保持历史状态的问题 **/
            url: ApiUrl + "/index.php?act=goods&op=goods_list"
                    + "&key=" + key
                    + "&order=" + order
                    + "&page=" + pagesize
                    + "&curpage=" + curpage
                    + '&gc_id=' + gc_id,
            /* lyq@newland 修改结束              **/
            type: 'get',
            dataType: 'json',
            success: function(result) {
                /* wqw@newland 添加开始   **/
                /* 时间：2015/06/02        **/
                /* 功能ID：ADMIN006      **/
                result.datas.SiteUrl = SiteUrl;
                /* wqw@newland 添加结束   **/

                $("input[name=hasmore]").val(result.hasmore);

                /* lyq@newland 修改开始              **/
                /* 时间：2015/06/04                   **/
                /* 返回商品列表页无法保持历史状态的问题 **/
                // 上一页按钮是否可点
                if ($("input[name=curpage]").val() == '1') {
                    $('.pre-page').addClass('disabled');
                } else {
                    $('.pre-page').removeClass('disabled');
                }
                // 下一页按钮是否可点
                if (!result.hasmore) {
                    $('.next-page').addClass('disabled');
                } else {
                    $('.next-page').removeClass('disabled');
                }
                // 移除排序条件图标高亮
                $(".keyorder").removeClass('current');
                // 根据条件（key）添加排序条件图标高亮
                $("a.keyorder[key='" + $("input[name=key]").val() + "']").addClass('current');
                // 如果排序条件是 价格，根据排序方式确定图标显示方式
                if ($("input[name=key]").val() == '3') {
                    if ($("input[name=order]").val() == '1') {
                        $(".current").find('span').removeClass('desc').addClass('asc');
                    } else {
                        $(".current").find('span').removeClass('asc').addClass('desc');
                    }
                }
                /* lyq@newland 修改结束              **/

                var curpage = $("input[name=curpage]").val();//分页
                var page_total = result.page_total;
                var page_html = '';
                for (var i = 1; i <= result.page_total; i++) {
                    if (i == curpage) {
                        page_html += '<option value="' + i + '" selected>' + i + '</option>';
                    } else {
                        page_html += '<option value="' + i + '">' + i + '</option>';
                    }
                }

                $('select[name=page_list]').empty();
                $('select[name=page_list]').append(page_html);

                /* wqw@newland 添加开始   　**/
                /* 时间：2015/06/08         **/
                /* 功能ID：ADMIN006         **/
                template.helper('in_array', function(str, arr) {
                    return $.inArray(str, arr);
                });
                /* wqw@newland 添加结束   **/
                var html = template.render('home_body', result.datas);
                $("#product_list").append(html);

                /* lyq@newland 添加开始 **/
                /* 时间：2015/06/10      **/
                /* wap端loading画面      **/
                // 显示页面内容
                $(".content").show();
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
    } else {
        /* lyq@newland 修改开始              **/
        /* 时间：2015/06/04                   **/
        /* 返回商品列表页无法保持历史状态的问题 **/
        // 排序条件
        var key = $("input[name=key]").val();
        // 排序方式
        var order = parseInt($("input[name=order]").val());
        // 当前页
        var curpage = parseInt($("input[name=curpage]").val());
        // 查询关键字
        var keyword = $("input[name=keyword]").val();
        /* lyq@newland 修改结束              **/
        $.ajax({
            /* lyq@newland 修改开始              **/
            /* 时间：2015/06/04                   **/
            /* 返回商品列表页无法保持历史状态的问题 **/
            url: ApiUrl + "/index.php?act=goods&op=goods_list"
                    + "&key=" + key
                    + "&order=" + order
                    + "&page=" + pagesize
                    + "&curpage=" + curpage
                    + '&keyword=' + keyword,
            /* lyq@newland 修改结束              **/
            type: 'get',
            dataType: 'json',
            success: function(result) {
                /* wqw@newland 添加开始   **/
                /* 时间：2015/06/02        **/
                /* 功能ID：ADMIN006      **/
                result.datas.SiteUrl = SiteUrl;
                /* wqw@newland 添加结束   **/

                $("input[name=hasmore]").val(result.hasmore);

                /* lyq@newland 修改开始              **/
                /* 时间：2015/06/04                   **/
                /* 返回商品列表页无法保持历史状态的问题 **/
                // 上一页按钮是否可点
                if ($("input[name=curpage]").val() == '1') {
                    $('.pre-page').addClass('disabled');
                } else {
                    $('.pre-page').removeClass('disabled');
                }
                // 下一页按钮是否可点
                if (!result.hasmore) {
                    $('.next-page').addClass('disabled');
                } else {
                    $('.next-page').removeClass('disabled');
                }
                // 移除排序条件图标高亮
                $(".keyorder").removeClass('current');
                // 根据条件（key）添加排序条件图标高亮
                $("a.keyorder[key='" + $("input[name=key]").val() + "']").addClass('current');
                // 如果排序条件是 价格，根据排序方式确定图标显示方式
                if ($("input[name=key]").val() == '3') {
                    if ($("input[name=order]").val() == '1') {
                        $(".current").find('span').removeClass('desc').addClass('asc');
                    } else {
                        $(".current").find('span').removeClass('asc').addClass('desc');
                    }
                }
                /* lyq@newland 修改结束              **/

                var curpage = $("input[name=curpage]").val();//分页
                var page_total = result.page_total;
                var page_html = '';
                for (var i = 1; i <= result.page_total; i++) {
                    if (i == curpage) {
                        page_html += '<option value="' + i + '" selected>' + i + '</option>';
                    } else {
                        page_html += '<option value="' + i + '">' + i + '</option>';
                    }
                }
                $('select[name=page_list]').empty();
                $('select[name=page_list]').append(page_html);

                /* wqw@newland 添加开始   　**/
                /* 时间：2015/06/08         **/
                /* 功能ID：ADMIN006         **/
                template.helper('in_array', function(str, arr) {
                    return $.inArray(str, arr);
                });
                /* wqw@newland 添加结束   **/
                var html = template.render('home_body', result.datas);
                $("#product_list").append(html);

                /* lyq@newland 添加开始 **/
                /* 时间：2015/06/10      **/
                /* wap端loading画面      **/
                // 显示页面内容
                $(".content").show();
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


    $("select[name=page_list]").change(function() {
        var key = parseInt($("input[name=key]").val());
        var order = parseInt($("input[name=order]").val());
        var page = parseInt($("input[name=page]").val());
        var gc_id = parseInt($("input[name=gc_id]").val());
        var keyword = $("input[name=keyword]").val();
        var hasmore = $("input[name=hasmore]").val();

        var curpage = $('select[name=page_list]').val();

        if (gc_id > 0) {
            var url = ApiUrl + "/index.php?act=goods&op=goods_list&key=" + key + "&order=" + order + "&page=" + page + "&curpage=" + curpage + "&gc_id=" + gc_id;
        } else {
            var url = ApiUrl + "/index.php?act=goods&op=goods_list&key=" + key + "&order=" + order + "&page=" + page + "&curpage=" + curpage + "&keyword=" + keyword;
        }

        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            success: function(result) {
                /* wqw@newland 添加开始   **/
                /* 时间：2015/06/02        **/
                /* 功能ID：ADMIN006      **/
                result.datas.SiteUrl = SiteUrl;
                /* wqw@newland 添加结束   **/
                var html = template.render('home_body', result.datas);
                $("#product_list").empty();
                $("#product_list").append(html);

                /* lyq@newland 修改开始 **/
                /* 时间：2015/06/10      **/
                // 页面置顶
                document.body.scrollTop = 0;
                /* lyq@newland 修改结束 **/

                if (curpage > 1) {
                    $('.pre-page').removeClass('disabled');
                } else {
                    $('.pre-page').addClass('disabled');
                }

                if (curpage < result.page_total) {
                    $('.next-page').removeClass('disabled');
                } else {
                    $('.next-page').addClass('disabled');
                }

                $("input[name=curpage]").val(curpage);
            }
        });

    });


    $('.keyorder').click(function() {
        /* lyq@newland 添加开始              **/
        /* 时间：2015/06/04                   **/
        /* 返回商品列表页无法保持历史状态的问题 **/
        // 页面当前页input的值设为1
        $("input[name=curpage]").val('1');
        /* lyq@newland 添加结束              **/
        var key = parseInt($("input[name=key]").val());
        var order = parseInt($("input[name=order]").val());
        var page = parseInt($("input[name=page]").val());
        var curpage = eval(parseInt($("input[name=curpage]").val()) - 1);
        var gc_id = parseInt($("input[name=gc_id]").val());
        var keyword = $("input[name=keyword]").val();
        var hasmore = $("input[name=hasmore]").val();

        var curkey = $(this).attr('key');//1.销量 2.浏览量 3.价格 4.最新排序
        if (curkey == key) {
            if (order == 1) {
                var curorder = 2;
            } else {
                var curorder = 1;
            }
        } else {
            var curorder = 1;
        }

        if (curkey == 3) {
            if (curorder == 1) {
                $(this).find('span').removeClass('desc').addClass('asc');
            } else {
                $(this).find('span').removeClass('asc').addClass('desc');
            }
        }

        $(this).addClass("current").siblings().removeClass("current");

        if (gc_id > 0) {
            var url = ApiUrl + "/index.php?act=goods&op=goods_list&key=" + curkey + "&order=" + curorder + "&page=" + page + "&curpage=1&gc_id=" + gc_id;
        } else {
            var url = ApiUrl + "/index.php?act=goods&op=goods_list&key=" + curkey + "&order=" + curorder + "&page=" + page + "&curpage=1&keyword=" + keyword;
        }

        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            success: function(result) {

                /* lyq@newland 添加开始              **/
                /* 时间：2015/06/04                   **/
                /* 返回商品列表页无法保持历史状态的问题 **/

                /* wqw@newland 添加开始   **/
                /* 时间：2015/06/02        **/
                /* 功能ID：ADMIN006      **/
                result.datas.SiteUrl = SiteUrl;
                /* wqw@newland 添加结束   **/

                // 是否有更多页
                $("input[name=hasmore]").val(result.hasmore);
                // 上一页是否可点
                if ($("input[name=curpage]").val() == '1') {
                    $('.pre-page').addClass('disabled');
                }
                // 下一页是否可点
                if (!result.hasmore) {
                    $('.next-page').addClass('disabled');
                } else {
                    $('.next-page').removeClass('disabled');
                }

                // 当前页
                var curpage = $("input[name=curpage]").val();
                // 总页数
                var page_total = result.page_total;
                // 分页html
                var page_html = '';
                // 循环添加分页html
                for (var i = 1; i <= result.page_total; i++) {
                    if (i == curpage) {
                        page_html += '<option value="' + i + '" selected>' + i + '</option>';
                    } else {
                        page_html += '<option value="' + i + '">' + i + '</option>';
                    }
                }
                // 清空分页列表
                $('select[name=page_list]').empty();
                // 更新分页列表
                $('select[name=page_list]').append(page_html);

                /* lyq@newland 添加结束              **/


                var html = template.render('home_body', result.datas);
                $("#product_list").empty();
                $("#product_list").append(html);
                $("input[name=key]").val(curkey);
                $("input[name=order]").val(curorder);
            }
        });
    });

    $('.pre-page').click(function() {//上一页
        var key = parseInt($("input[name=key]").val());
        var order = parseInt($("input[name=order]").val());
        var page = parseInt($("input[name=page]").val());
        var curpage = eval(parseInt($("input[name=curpage]").val()) - 1);
        var gc_id = parseInt($("input[name=gc_id]").val());
        var keyword = $("input[name=keyword]").val();

        if (curpage < 1) {
            return false;
        }

        if (gc_id >= 0) {
            var url = ApiUrl + "/index.php?act=goods&op=goods_list&key=" + key + "&order=" + order + "&page=" + page + "&curpage=" + curpage + "&gc_id=" + gc_id;
        } else {
            var url = ApiUrl + "/index.php?act=goods&op=goods_list&key=" + key + "&order=" + order + "&page=" + page + "&curpage=" + curpage + "&keyword=" + keyword;
        }
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            success: function(result) {
                /* wqw@newland 添加开始   **/
                /* 时间：2015/06/02        **/
                /* 功能ID：ADMIN006      **/
                result.datas.SiteUrl = SiteUrl;
                /* wqw@newland 添加结束   **/
                $("input[name=hasmore]").val(result.hasmore);
                if (curpage == 1) {
                    $('.next-page').removeClass('disabled');
                    $('.pre-page').addClass('disabled');
                } else {
                    $('.next-page').removeClass('disabled');
                }
                var html = template.render('home_body', result.datas);
                $("#product_list").empty();
                $("#product_list").append(html);
                $("input[name=curpage]").val(curpage);

                var page_total = result.page_total;
                var page_html = '';
                for (var i = 1; i <= result.page_total; i++) {
                    if (i == curpage) {
                        page_html += '<option value="' + i + '" selected>' + i + '</option>';
                    } else {
                        page_html += '<option value="' + i + '">' + i + '</option>';
                    }
                }

                $('select[name=page_list]').empty();
                $('select[name=page_list]').append(page_html);

                /* lyq@newland 修改开始 **/
                /* 时间：2015/06/10      **/
                // 页面置顶
                document.body.scrollTop = 0;
                /* lyq@newland 修改结束 **/
            }
        });
    });

    $('.next-page').click(function() {//下一页
        var hasmore = $('input[name=hasmore]').val();
        if (hasmore == 'false') {
            return false;
        }

        var key = parseInt($("input[name=key]").val());
        var order = parseInt($("input[name=order]").val());
        var page = parseInt($("input[name=page]").val());
        var curpage = eval(parseInt($("input[name=curpage]").val()) + 1);
        var gc_id = parseInt($("input[name=gc_id]").val());
        var keyword = $("input[name=keyword]").val();

        if (gc_id >= 0) {
            var url = ApiUrl + "/index.php?act=goods&op=goods_list&key=" + key + "&order=" + order + "&page=" + page + "&curpage=" + curpage + "&gc_id=" + gc_id;
        } else {
            var url = ApiUrl + "/index.php?act=goods&op=goods_list&key=" + key + "&order=" + order + "&page=" + page + "&curpage=" + curpage + "&keyword=" + keyword;
        }
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            success: function(result) {
                /* wqw@newland 添加开始   **/
                /* 时间：2015/06/02        **/
                /* 功能ID：ADMIN006      **/
                result.datas.SiteUrl = SiteUrl;
                /* wqw@newland 添加结束   **/
                $("input[name=hasmore]").val(result.hasmore);
                if (!result.hasmore) {
                    $('.pre-page').removeClass('disabled');
                    $('.next-page').addClass('disabled');
                } else {
                    $('.pre-page').removeClass('disabled');
                }
                var html = template.render('home_body', result.datas);
                $("#product_list").empty();
                $("#product_list").append(html);
                $("input[name=curpage]").val(curpage);

                var page_total = result.page_total;
                var page_html = '';
                for (var i = 1; i <= result.page_total; i++) {
                    if (i == curpage) {
                        page_html += '<option value="' + i + '" selected>' + i + '</option>';
                    } else {
                        page_html += '<option value="' + i + '">' + i + '</option>';
                    }
                }
                $('select[name=page_list]').empty();
                $('select[name=page_list]').append(page_html);

                /* lyq@newland 修改开始 **/
                /* 时间：2015/06/10      **/
                // 页面置顶
                document.body.scrollTop = 0;
                /* lyq@newland 修改结束 **/
            }
        });
    });
});