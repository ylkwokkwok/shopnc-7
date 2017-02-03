
/* lyq@newland 删除开始   **/
/* 时间：2015/06/01   **/
// SHOP017 此文件已无用
$(function() {
    var goods_id = GetQueryString("goods_id");
    $.ajax({
        url: ApiUrl + "/index.php?act=goods&op=goods_body",
        data: {goods_id: goods_id},
        type: "get",
        success: function(result) {
            $(".fixed-tab-pannel").html(result);
        }
    });
});
/* lyq@newland 删除结束   **/