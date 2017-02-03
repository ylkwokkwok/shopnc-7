$(function() {
    var goods = getcookie('goods');
    var goods_info = goods.split('@');

    if (goods_info.length > 0) {
        for (var i = 0; i < goods_info.length; i++) {
            AddViewGoods(goods_info[i]);
        }
    } else {
        var html = '<li>没有符合条件的记录</li>';
        $('#viewlist').append(html);
    }
});

function AddViewGoods(goods_id) {
    $.ajax({
        type: 'get',
        url: ApiUrl + '/index.php?act=goods&op=goods_detail&goods_id=' + goods_id,
        dataType: 'json',
        success: function(result) {
            var html;
            var data = result.datas;
            if(!data.error){
                var pic = data.goods_image.split(',');
                /* wqw@newland 修改开始   **/
                /* 时间：2015/06/08        **/
                /* 功能ID：ADMIN006      **/
                if ($.inArray(data.goods_info.store_id, data.stroe_vip_list) != -1) {
                    html = '<li>'
                            + '<a href="' + WapSiteUrl + '/tmpl/product_detail.html?goods_id=' + data.goods_info.goods_id + '" class="mf-item clearfix">'
                            + '<span class="mf-pic">'
                            + '<img src="' + pic[0] + '"/>'
                    /*zly@newland 隐藏VIP标示开始**/
                    /*时间：2015/08/10**/
//                            + '<img src= "' + SiteUrl + '/data/upload/shop/store/goods/1/goods_vip.jpg" class="flt_img_favorite"/>'
                    /*zly@newland 隐藏VIP标示结束**/
                            + '</span>'
                            + '<div class="mf-infor">'
                            + '<p class="mf-pd-name">' + data.goods_info.goods_name + '</p>'
                            + '<p class="mf-pd-price">￥' + data.goods_info.goods_price + '</p></div>';
                    +'</a></li>';
                } else {
                    html = '<li>'
                            + '<a href="' + WapSiteUrl + '/tmpl/product_detail.html?goods_id=' + data.goods_info.goods_id + '" class="mf-item clearfix">'
                            + '<span class="mf-pic">'
                            + '<img src="' + pic[0] + '"/>'
                            + '</span>'
                            + '<div class="mf-infor">'
                            + '<p class="mf-pd-name">' + data.goods_info.goods_name + '</p>'
                            + '<p class="mf-pd-price">￥' + data.goods_info.goods_price + '</p></div>';
                    +'</a></li>';
                }
                /* wqw@newland 修改结束   **/
                $('#viewlist').append(html);

                /* lyq@newland 添加开始 **/
                /* 时间：2015/06/10      **/
                /* wap端loading画面      **/
                // 隐藏loading画面
                $("#loading_page").hide();
                /* lyq@newland 添加结束 **/
            } else {
                /* wqw@newland 修改开始   **/
                /* 时间：2015/06/19        **/
                /* 功能ID：ADMIN006      **/
                html = '<div class="no-record">'
                        + '暂无记录' 
                        + '</div>';  
                $('#viewlist').append(html);
                $("#loading_page").hide();
                /* wqw@newland 修改结束   **/
            }
        }
    });
}