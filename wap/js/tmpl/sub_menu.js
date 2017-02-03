/*
 * @author zly
 */
$(function() {
    var key = getcookie('key');
    if (key == '') {
        location.href = 'login.html';
    }
    // 隐藏loading画面
    $("#loading_page").hide();
});


