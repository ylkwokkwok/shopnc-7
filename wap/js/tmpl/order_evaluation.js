/**		
 * oeder_evaluation	
 * 录入评价信息	js
 * @author zly		
 */
$(function() {
    var key = getcookie('key');
    //判断是否登陆
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
        return;
    }
    var order_id = GetQueryString("order_id");

    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=evaluation&op=add",
        data: {key: key, order_id: order_id},
        dataType: 'json',
        success: function(result) {
            var data = result.datas;
            if (!data.error) {
                // 页面地址
                data.WapSiteUrl = WapSiteUrl;

                // 渲染页面
                var html = template.render('order-evaluation-tmpl', data);
                $("#order-evaluation-wp").html(html);
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
            // 提交评价信息
            $('.complain-order').click(function() {
                // 获取评价内容
                var goods_eval = $("[name=eval_comment]");
                var evaluate_score = new Object();
                var good_ids = new Array();
                /* lyq@newland 修改开始 **/
                /* 时间：2015/06/17      **/
                // 循环评价内容
                for (var i = 0; i < goods_eval.length; i++) {
                    // 获取评价class
                    var eval_class_name = goods_eval[i].className;
                    // 获取商品ID
                    var goods_id = eval_class_name.replace("nl-textarea ", "");
                    // 将商品评价添加到数组中
                    evaluate_score[goods_id] = goods_eval[i].value;
                    // 将商品ID添加到数组中
                    good_ids[i] = goods_id;
                }
                /* lyq@newland 修改结束 **/

                // 是否匿名评价
                if (document.getElementById("checkbox").checked) {
                    var anony = $('input[name=anony]').val();
                } else {
                    anony = 0;
                }

                // 获取商品等级
                var geval_scores = new Object();
                /* lyq@newland 修改开始 **/
                /* 时间：2015/06/17      **/
                // 循环商品ID
                for (var j = 0; j < good_ids.length; j++) {
                    // 获取商品最高星级的对象
                    var last_gold_star = $("[name=score" + good_ids[j] + "].evaluation-star").last();
                    // 将商品分数添加到数组中
                    geval_scores[good_ids[j]] = last_gold_star.length === 0 ? 0 : $(last_gold_star).attr("score");
                }

                // 获取宝贝与描述相符度
                var desccredit = $("[name=seval_desccredit].evaluation-star").last();
                var store_desccredit = desccredit.length === 0 ? 0 : $(desccredit).attr("score");
                // 卖家的服务态度
                var servicecredit = $("[name=seval_servicecredit].evaluation-star").last();
                var store_servicecredit = servicecredit.length === 0 ? 0 : $(servicecredit).attr("score");
                // 卖家的发货速度
                var deliverycredit = $("[name=seval_deliverycredit].evaluation-star").last();
                var store_deliverycredit = deliverycredit.length === 0 ? 0 : $(deliverycredit).attr("score");
                /* lyq@newland 修改结束 **/

                $.ajax({
                    type: 'post',
                    url: ApiUrl + "/index.php?act=evaluation&op=evaluation_insert",
                    data: {key: key, order_id: order_id, anony: anony, evaluate_score: evaluate_score, geval_scores: geval_scores, store_desccredit: store_desccredit, store_servicecredit: store_servicecredit, store_deliverycredit: store_deliverycredit},
                    dataType: 'json',
                    success: function(result) {
                        /* zly@newland 修改开始 **/
                        /* 时间：2015/06/17      **/
                        var data = result.datas;
                        if (!data.error) {
                            location.href = WapSiteUrl + '/tmpl/member/order_list.html';
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
                        /* zly@newland 修改结束 **/
                    }
                });
            });

            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/17      **/
            // 点击星级
            $('.eval_stars').click(function() {
                // 商品标志
                var name = $(this).attr('name');
                // 选中星级
                var score = parseInt($(this).attr('score'));
                // 循环当前选中星级所属商品的所有星级
                $('[name=' + name + ']').each(function() {
                    if (parseInt($(this).attr('score')) <= score) { // 如果小于等于当前星级
                        // 清除之前的星级显示效果
                        $(this).removeClass('evaluation-star-gray');
                        $(this).removeClass('evaluation-star');
                        // 点亮星级
                        $(this).addClass('evaluation-star');
                    } else { // 如果大于当前星级
                        // 清除之前的星级显示效果
                        $(this).removeClass('evaluation-star-gray');
                        $(this).removeClass('evaluation-star');
                        // 取消点亮星级
                        $(this).addClass('evaluation-star-gray');
                    }
                });
            });
            /* lyq@newland 添加结束 **/

            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/10      **/
            /* wap端loading画面      **/
            // 隐藏loading画面
            $("#loading_page").hide();
            /* lyq@newland 添加结束 **/
        }
    });
});