$(function () {
    var key = getcookie('key');
    if (key == '') {    // 未登录
        // 跳转到微信用户权限获取页
        location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbf22b887fc929ff8&redirect_uri=' + SiteUrl + '/wx/milk_index.php&response_type=code&scope=snsapi_userinfo&state=nearby_store#wechat_redirect';

    }

    // 自取点编号
    var self_receive_spot_cd = GetQueryString('self_receive_spot_cd');
    // ajax获取商品信息
    $.ajax({
        url: ApiUrl + "/index.php?act=milk_store&op=get_product_list",
        type: 'post',
        data: {key: key, type: "self"},
        dataType: 'json',
        success: function (result) {
            var data = result.datas;
            /* lyq@newland 修改开始 **/
            /* 时间：2015/09/21     **/
            // 检查快速订奶开关
            if (check_quick_entr(data)) {
                data.SiteUrl = SiteUrl;
                // 渲染模板
                var html = template.render('cards', data);
                // 更新页面内容
                $("#card_list").html(html);

                // 配送 选项点击事件
                $("#distribution").click(function () {
                    // 需要配送
                    if ($(this).is(":checked")) {
                        $("#area").removeAttr('readonly').removeClass('disable_input');
                        $("#address").removeAttr('readonly').removeClass('disable_input');
                    }
                    // 不需要配送
                    else {
                        $("#area").val('').attr('readonly', 'true').addClass('disable_input');
                        $("#address").val('').attr('readonly', 'true').addClass('disable_input');
                    }
                });
                // 点击配送文字时间
                $("#dist").click(function(){
                    // 模拟点击配送checkbox
                    $("#distribution").trigger("click");
                });

                // 奶卡数量输入值改变事件
                $("input.card_num").change(function () {
                    // 验证奶卡数量输入值
                    card_num_value_check(this);
                    // 计算总金额
                    count_total_price();
                });

                // 微信支付 按钮点击事件
                $(".pay_wechat").click(pay);

                // 隐藏loading画面
                $("#loading_page").hide();
            }
            /* lyq@newland 修改结束 **/
        }
    });

    /**
     * 验证奶卡数量输入值
     * @param {type} obj 奶卡数量input元素
     * @returns {Boolean} 成功|失败
     */
    function card_num_value_check(obj) {
        if ($.trim($(obj).val()) !== '' && !/^\+?[1-9][0-9]*$/.test($.trim($(obj).val()))) {
            // 显示提示消息
            show_message('奶卡数量必须为正整数', 'red');
        }
    }

    /**
     * 点击微信支付按钮时的奶卡数量输入验证
     * @returns {Boolean}
     */
    function pay_btn_check() {
        /* lyq@newland 添加开始 **/
        /* 时间：2015/09/18      **/
        // 自取点编号丢失
        if (self_receive_spot_cd === null) {
            // 显示提示消息
            show_message('自取点信息错误，请进入公众号重新操作', 'red');
            // 不做操作
            return false;
        }
        /* lyq@newland 添加结束 **/
        
        // 奶卡数量已填写标志
        var one_card_at_least_flag = false;
        // 奶卡数量输入值类型正确标志
        var card_num_value_valid_flag = true;
        // 循环页面奶卡数量input
        $("input.card_num").each(function () {
            // 有一项不为空
            if ($.trim($(this).val()) !== '') {
                // 奶卡数量已填写
                one_card_at_least_flag = true;
            }
            if ($.trim($(this).val()) !== '' && !/^\+?[1-9][0-9]*$/.test($.trim($(this).val()))) {
                // 奶卡数量已填写
                card_num_value_valid_flag = false;
            }
        });

        // 未填写奶卡数量
        if (!one_card_at_least_flag) {
            // 显示提示消息
            show_message('请至少填写一种奶卡的数量', 'red');
            // 不做操作
            return false;
        }

        // 奶卡数量输入值类型错误
        if (!card_num_value_valid_flag) {
            // 显示提示消息
            show_message('奶卡数量必须为正整数', 'red');
            // 不做操作
            return false;
        }

        // 需要配送但未填写区或详细地址时
        if ($("#distribution").is(":checked") &&
                ($.trim($("#area").val()) === '' || $.trim($("#address").val()) === '')) {
            // 显示提示消息
            show_message('请填写配送信息', 'red');
            // 不做操作
            return false;
        }

        // 验证客户信息是否为空
        if ($.trim($("input[name=receiver_name]").val()) === '' ||
                $.trim($("input[name=receiver_tel]").val()) === '') {
            // 显示提示消息
            show_message('请填写客户信息', 'red');
            // 不做操作
            return false;
        }
        
        // 验证电话号码格式
        if (!/^(\d{11}|\d{7,8}|\d{3,4}-\d{7,8})$/.test($.trim($("input[name=receiver_tel]").val()))) {
            // 显示提示消息
            show_message('电话号码格式错误', 'red');
            // 不做操作
            return false;
        }

        return true;
    }

    /**
     * 微信支付
     */
    function pay() {
        // 页面输入项验证成功时
        if (pay_btn_check()) {
            var submit_data = get_data();
            $.ajax({
                type: "post",
                url: SiteUrl + "/wx/wx_milk_pay.php",
                data: submit_data,
                dataType: "json",
                beforeSend: function () {
                    // 显示页面覆盖层
                    $("#wxpay_loading_mask").show();
                    // 显示loading动画
                    $("#wxpay_loading").show();
                },
                success: function (result) {
                    // 隐藏页面覆盖层
                    $("#wxpay_loading_mask").hide();
                    // 隐藏loading动画
                    $("#wxpay_loading").hide();

                    // 记录ID
                    var log_id = result.log_id;
                    
                    WeixinJSBridge.invoke(
                        'getBrandWCPayRequest', {
                            "appId": result.appId, //公众号名称，由商户传入     
                            "timeStamp": result.timeStamp, //时间戳，自1970年以来的秒数     
                            "nonceStr": result.nonceStr, //随机串     
                            "package": result.package,
                            "signType": "MD5", //微信签名方式:     
                            "paySign": result.paySign //微信签名 
                        },
                        function (res) {
                            if ("get_brand_wcpay_request:ok" === res.err_msg) {
                                // 弹出消息对话框
                                show_message("恭喜您，已成功付款！","green");
                                // 2秒后跳转到交易成功页面
                                setTimeout(function(){
                                    location.href = "trade_success.html?log_id="+log_id;
                                }, 2000);
                            } else if ("get_brand_wcpay_request:cancel" === res.err_msg) {
                                // 弹出消息对话框
                                show_message("您已取消付款了！","red");
                            } else {
                                // 弹出消息对话框
                                show_message("付款失败，请稍后重试！","red");
                            }
                        }
                    );
                },
                error: function () {
                }
            });
        }
    }

    /**
     * 获取需要提交到后台的数据
     *   整理数据，并返回
     * @returns {Object} 整理后的数据对象
     */
    function get_data() {
        // 订单数据数组
        var order_data = new Array();
       //取备注信息
        var remark = $('#remark').val();
        // 循环奶卡数量信息
        $(".cards").each(function () {
            // 奶品编号
            var milk_cd = $(this).attr("milk_cd");
            // 周卡对象
            var week_obj = $(this).find("input[name=week]");
            // 月卡对象
            var month_obj = $(this).find("input[name=month]");
            // 季卡对象
            var season_obj = $(this).find("input[name=season]");
            // 半年卡对象
            var halfyear_obj = $(this).find("input[name=halfyear]");
            // 年卡对象
            var year_obj = $(this).find("input[name=year]");
            // 购买了周卡时
            if (week_obj.length > 0 && $.trim($(week_obj).val()) !== '') {
                order_data.push({"milk_cd": milk_cd, "card_type": "4", "goods_id": $(week_obj).attr('goods_id'), "goods_price": $(week_obj).attr('goods_price'), "goods_num": $.trim($(week_obj).val())});
            }
            // 购买了月卡时
            if (month_obj.length > 0 && $.trim($(month_obj).val()) !== '') {
                order_data.push({"milk_cd": milk_cd, "card_type": "0", "goods_id": $(month_obj).attr('goods_id'), "goods_price": $(month_obj).attr('goods_price'), "goods_num": $.trim($(month_obj).val())});
            }
            // 购买了季卡时
            if (season_obj.length > 0 && $.trim($(season_obj).val()) !== '') {
                order_data.push({"milk_cd": milk_cd, "card_type": "1", "goods_id": $(season_obj).attr('goods_id'), "goods_price": $(season_obj).attr('goods_price'), "goods_num": $.trim($(season_obj).val())});
            }
            // 购买了半年卡时
            if (halfyear_obj.length > 0 && $.trim($(halfyear_obj).val()) !== '') {
                order_data.push({"milk_cd": milk_cd, "card_type": "2", "goods_id": $(halfyear_obj).attr('goods_id'), "goods_price": $(halfyear_obj).attr('goods_price'), "goods_num": $.trim($(halfyear_obj).val())});
            }
            // 购买了年卡时
            if (year_obj.length > 0 && $.trim($(year_obj).val()) !== '') {
                order_data.push({"milk_cd": milk_cd, "card_type": "3", "goods_id": $(year_obj).attr('goods_id'), "goods_price": $(year_obj).attr('goods_price'), "goods_num": $.trim($(year_obj).val())});
            }
        });
        // 需要返回的数据对象（自取点编号，用户姓名，用户电话）
        var submit_data = {"self_receive_spot_cd": self_receive_spot_cd, "name": $.trim($("input[name=receiver_name]").val()), "tel": $.trim($("input[name=receiver_tel]").val()),"remark": remark};
        // 如果有地址
        if ($("#distribution").is(":checked")) {
            // 拼接地址存入对象
            submit_data.address = "辽宁省大连市" + $.trim($("#area").val()) + "区 " + $.trim($("#address").val());
        }
        // 将订单信息存入对象
        submit_data.milk_order_datas = order_data;
        // 返回数据对象
        return submit_data;
    }
    
    /**
     * 计算总金额
     */
    function count_total_price() {
        // 默认总金额
        var total_price = 0.00;
        // 循环奶卡数量
        $(".card_num").each(function(){
            // 输入的奶卡数量
            var input_val = $.trim($(this).val());
            // 如果奶卡数量不为空，且为正整数
            if (input_val !== '' && /^\+?[1-9][0-9]*$/.test(input_val)) {
                // 累加 奶卡数量*奶卡价格
                total_price += parseInt(input_val) * parseFloat($(this).attr('goods_price'));
            }
        });
        // 更新页面总金额显示
        $("#total_price").text(parseFloat(total_price).toFixed(2));
    }
});
