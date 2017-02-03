$(function() {
    var page = pagesize;
    var curpage = 1;
    var hasmore = true;
    var hasmoreflg = true;
    $('.tab-con').height($(window).height()-90);
    // 获取一级分类
    $.ajax({
        url: ApiUrl + "/index.php?act=goods_class&op=get_root_class",
        type: 'get',
        dataType: 'json',
        success: function(result) {
            // 渲染模板
            var html = template.render('category-root', result.datas);
            // 更新页面内容
            $("#root_class").html(html);
            // 获取二级分类
            get_child_class($(".box-item").attr("gc_id"), page);
            // 一级分类点击事件
            $(".gc_list").click(function() {
                $("#loading_page").show();
                $('.tab-con').scrollTop(0);
                curpage = 1;
                hasmore = true;
                // 改变之前选中的一级分类的样式
                $(".box-item").removeClass("box-item").addClass("box-item-selected");
                // 改变当前选中的一级分类的样式
                $(this).removeClass("box-item-selected").addClass("box-item");
                // 获取二级分类
                get_child_class($(".box-item").attr("gc_id"));
            });
            //增加延迟加载功能，当滚动条拉到底，加载更多商品
            $('.tab-con').scroll(function() {
                var divHeight = $(this).height();
                var nScrollHeight = $(this)[0].scrollHeight - 20;
                var nScrollTop = $(this)[0].scrollTop;
                if(nScrollTop + divHeight >= nScrollHeight && hasmore == true && hasmoreflg == true) {
                    $("#loading_page").show();
                    curpage = curpage + 1;
                    hasmoreflg = false;
                    get_child_class($(".box-item").attr("gc_id"), '1');
                }
            });
        }
    });
    
    /**
     * 获取二级分类
     * 
     * @param string gc_parent_id 一级分类id
     */
    function get_child_class(gc_parent_id, num) {
        $.ajax({
            /* zz@newland 修改开始 **/
            /* 时间：2016/03/3     **/
            //修改调用的查询方法 把查询商品分类改为一次查询一分类中10个商品
//            url: ApiUrl + "/index.php?act=goods_class&op=get_goods&gc_parent_id=" + gc_parent_id + "&page=" + page + "&curpage=" + curpage + "&getpayment=true",
            url: ApiUrl + "/index.php?act=goods&op=goods_list"
                    + "&page=" + pagesize
                    + "&curpage=" + curpage
                    + '&gc_id=' + gc_parent_id,
            type: 'get',
            dataType: 'json',
            success: function(result) {
                hasmoreflg = true;
                hasmore = result.hasmore;
                if (result.datas.num_payment != 0) {    // 一级分类有子分类时
                    var data = result.datas;
                    data.WapSiteUrl = WapSiteUrl;
                    // 渲染模板
                    var html = template.render('category-child', data);
                    // 更新页面内容
                    if (num == 1) {
                        $("#child_class").append(html);
                    } else {
                        $("#child_class").html(html);
                    }

                } else {    // 一级分类无子分类时
//                        跳转到一级分类下的商品列表
//                        location.href = WapSiteUrl + "/tmpl/product_list.html?gc_id=" + gc_parent_id;
                    $("#child_class").html('');

                }
                /* zz@newland 修改结束 **/
                
                // 隐藏loading画面
                $("#loading_page").hide();
            }
        });

    }
});