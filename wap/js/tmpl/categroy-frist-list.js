$(function() {
    $.ajax({
        url: ApiUrl + "/index.php?act=goods_class",
        type: 'get',
        jsonp: 'callback',
        dataType: 'jsonp',
        success: function(result) {
            var data = result.datas;
            data.WapSiteUrl = WapSiteUrl;
            var html = template.render('category-one', data);
            $("#categroy-cnt").html(html);

            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/10      **/
            /* wap端loading画面      **/
            // 隐藏loading画面
            $("#loading_page").hide();
            /* lyq@newland 添加结束 **/
        }
    });
});