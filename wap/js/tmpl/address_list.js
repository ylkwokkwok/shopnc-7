$(function() {
    var key = getcookie('key');
    if (key == '') {
        location.href = 'login.html';
    }
    //初始化列表
    function initPage() {
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_address&op=address_list",
            data: {key: key},
            dataType: 'json',
            success: function(result) {
                checklogin(result.login);
                if (result.datas.address_list == null) {
                    return false;
                }
                var data = result.datas;
                var html = template.render('saddress_list', data);
                $("#address_list").empty();
                $("#address_list").append(html);
                //点击删除地址
                $('.deladdress').click(delAddress);

                /* zz@newland 添加开始 **/
                /* 时间：2016/03/3     **/
                //默认地址按钮点击事件
                $('.check_address').click(defaultAddress);
                /* zz@newland 添加结束 **/

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

    /* zz@newland 添加开始 **/
    /* 时间：2016/03/3     **/
    //默认地址修改
    function defaultAddress() {
        var set_id = $(this).attr('set_id');
         $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_address&op=default_address",
            data: {set_id: set_id, key: key},
            dataType: 'json',
            success: function(result) {
                checklogin(result.login);
                location.reload();
            }
        });
    }
    /* zz@newland 添加结束 **/

    //点击删除地址
    function delAddress() {
        var address_id = $(this).attr('address_id');
        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_address&op=address_del",
            data: {address_id: address_id, key: key},
            dataType: 'json',
            success: function(result) {
                checklogin(result.login);
                if (result) {
                    initPage();
                }
            }
        });
    }
});