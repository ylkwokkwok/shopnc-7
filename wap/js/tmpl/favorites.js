$(function() {
    var key = getcookie('key');
    if (key == '') {
        location.href = 'login.html';
    }
    //初始化页面
    function initPage() {
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_favorites&op=favorites_list",
            data: {key: key},
            dataType: 'json',
            success: function(result) {
                checklogin(result.login);
                var data = result.datas;
                data.WapSiteUrl = WapSiteUrl;
                /* wqw@newland 添加开始   　**/
                /* 时间：2015/06/08         **/
                /* 功能ID：ADMIN006         **/
                data.SiteUrl = SiteUrl;
                template.helper('in_array', function(str, arr) {
                    return $.inArray(str, arr);
                });
                /* wqw@newland 添加结束   **/
                var html = template.render('sfavorites_list', data);
                $("#favorites_list").html(html);
                //删除收藏
                $('.i-del').click(delFavorites);

                /* lyq@newland 添加开始 **/
                /* 时间：2015/06/10      **/
                /* wap端loading画面      **/
                // 隐藏loading画面
                $("#loading_page").hide();
                /* lyq@newland 添加结束 **/
            }
        });
    }
    initPage();
    //删除收藏
    function delFavorites() {
        var goods_id = $(this).attr('goods_id');
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_favorites&op=favorites_del",
            data: {fav_id: goods_id, key: key},
            dataType: 'json',
            success: function(result) {
                checklogin(result.login);
                if (result) {
                    initPage();
                }
            }
        });
        return false;
    }
});