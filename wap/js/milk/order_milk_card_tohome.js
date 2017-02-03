$(function () {
    var key = getcookie('key');
    if (key == '') {    // 未登录
        // 跳转到微信用户权限获取页
        location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbf22b887fc929ff8&redirect_uri=' + SiteUrl + '/wx/milk_index.php&response_type=code&scope=snsapi_userinfo&state=tohome#wechat_redirect';

    }

    // ajax获取商品信息
    $.ajax({
        url: ApiUrl + "/index.php?act=milk_store&op=get_product_list",
        type: 'post',
        data: {key: key, type: "home"},
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

                // 奶卡数量输入值改变事件
                $("input.card_num").change(function () {
                    // 验证奶卡数量输入值
                    card_num_value_check(this);
                    // 计算总金额
                    count_total_price();
                });
                if (data.address_info == '') { // 收获地址是否存在
                    // buys1-invoice-cnt
                    var thisPrarent = $(".buys1-address-cnt");
                    hideDetail(thisPrarent);
                    /* lyq@newland 添加开始   **/
                    /* 时间：2015/06/15        **/
                    // 选中 使用新的地址信息
                    $("#new-address-button").attr('checked', 'checked').trigger('click');
                    // 显示 录入新地址 部分
                    $('#new-address-wrapper').show();
                    /* lyq@newland 添加结束   **/
                } else {
                    $('#true_name').html(data.address_info.true_name);
                    $('#address').html(data.address_info.area_info + ' ' + data.address_info.address);
                    $('#mob_phone').html(data.address_info.mob_phone);
                }

                // 点击使用新地址才显示新地址编辑框
                $('#new-address-button').click(function() {
                    // 清空 录入新地址 部分
                    clear_addr_form();
                    // 显示 录入新地址 部分
                    $('#new-address-wrapper').show();
                });

                // 微信支付 按钮点击事件
                $(".pay_wechat").click(pay);

                // 隐藏loading画面
                $("#loading_page").hide();
            }
            /* lyq@newland 修改结束 **/
        }
    });
    
    // 区县取得
    $.ajax({
        type: 'post',
        url: ApiUrl + '/index.php?act=member_address&op=area_list',
        data: {key: key, area_id: "108"},
        dataType: 'json',
        success: function(result) {
            var data = result.datas;
            var region_html = '<option value="">请选择...</option>';
            for (var i = 0; i < data.area_list.length; i++) {
                region_html += '<option value="' + data.area_list[i].area_id + '">' + data.area_list[i].area_name + '</option>';
            }
            $("select[name=region]").html(region_html);
        }
    });
    //修改收获地址
    $(".buys1-edit-address").click(function() {
        $.ajax({
            url: ApiUrl + "/index.php?act=member_address&op=address_list",
            type: 'post',
            data: {key: key},
            dataType: 'json',
            success: function(result) {
                var data = result.datas;
                var html = '';
                for (var i = 0; i < data.address_list.length; i++) {
                    html += '<li class="current existent-address">'
                            + '<label>'
                            // 默认选中第一条地址信息
                            + '<input type="radio" name="address" class="rdo address-radio" value="' + data.address_list[i].address_id + '" city_id="' + data.address_list[i].city_id + '" area_id="' + data.address_list[i].area_id + '" ' + (i === 0 ? 'checked="checked"' : '') + ' />'
                            + '<span class="mr5 rdo-span"><span class="true_name_' + data.address_list[i].address_id + '">' + data.address_list[i].true_name + '</span> <span class="address_id_' + data.address_list[i].address_id + '">' + data.address_list[i].area_info + ' ' + data.address_list[i].address + '</span> <span class="mob_phone_' + data.address_list[i].address_id + '">' + data.address_list[i].mob_phone + '</span></span>'
                            + '</label>'
                            + '<a class="del-address" href="javascript:void(0);" address_id="' + data.address_list[i].address_id + '">[删除]</a>'
                            + '</li>';
                }
                $('li.existent-address').remove();
                $('#addresslist').before(html);
                // 存在地址信息
                if (data.address_list.length > 0) { 
                    // 取消选中使用新的地址信息
                    $("#new-address-button").removeAttr('checked');
                    // 隐藏 录入新地址 部分
                    $('#new-address-wrapper').hide();
                }
                
                //选择收获地址
                $(".rdo").click(function() {
                    $(".error-tips").html("").hide();
                });

                // 点击已有地址 隐藏新地址输入框
                $('li.existent-address input').click(function() {
                    $('#new-address-wrapper').hide();
                });

                $('.del-address').click(function() {
                    var $this = $(this);
                    var address_id = $(this).attr('address_id');
                    $.ajax({
                        type: 'post',
                        url: ApiUrl + '/index.php?act=member_address&op=address_del',
                        data: {key: key, address_id: address_id},
                        dataType: 'json',
                        success: function(result) {
                            $this.parent('li').remove();
                            // 判断地址信息 
                            if ($("li.existent-address").length == 0) { 
                                // 不存在地址信息
                                // 选中 使用新的地址信息
                                $("#new-address-button").attr('checked', 'checked').trigger('click');
                                // 清空 录入新地址 部分
                                clear_addr_form();
                                // 显示 录入新地址 部分
                                $("#new-address-wrapper").show();
                            } else { 
                                // 存在地址信息
                                if ($("li.existent-address input:checked").length === 0) { 
                                    // 不存在选中的地址信息
                                    // 选中第一条地址信息
                                    $("li.existent-address input").first().attr('checked', 'checked').trigger('click');
                                }
                            }
                            /* lyq@newland 添加结束   **/
                        }
                    });
                });

                $('input[name=address]').click(function() {
                    // 取消选中所有radio
                    $('input[name=address]').removeAttr('checked');
                    // 选中当前点击的radio
                    $(this).attr('checked', 'checked');
                });
            }
        });
        $(".error-tips").html("").hide();
        var thisPrarent = $(this).parent().next().children(".buys1-address-cnt");
        hideDetail(thisPrarent);
    });
    
    //更换收获地址
    $(".save-address").click(function() {
        var self = this;
        var selfPr
        //获取address_id
        var addressRadio = $('.address-radio');
        var address_id;
        for (var i = 0; i < addressRadio.length; i++) {
            if (addressRadio[i].checked) {
                address_id = addressRadio[i].value;
            }
        }
        // 判断操作情况 
        if (address_id > 0) {
            //变更地址
            $(".error-tips").html("").hide();
            $("input[name=address_id]").val(address_id);
            $('#address').html($('.address_id_' + address_id).html());
            $('#true_name').html($('.true_name_' + address_id).html());
            $('#mob_phone').html($('.mob_phone_' + address_id).html());
        } else {
            // 新增地址保存地址
            if ($.sValid()) {
                var true_name = $('input[name=true_name]').val();
                var mob_phone = $('input[name=mob_phone]').val();
                var tel_phone = $('input[name=tel_phone]').val();
                var city_id = $('input[name=city]').val();
                var area_id = $('select[name=region]').val();
                var address = $('input[name=vaddress]').val();
                var region_index = $('select[name=region]')[0].selectedIndex;
                var area_info = '辽宁省大连市' + $('select[name=region]')[0].options[region_index].innerHTML;
                //ajax 提交收货地址
                $.ajax({
                    type: 'post',
                    url: ApiUrl + '/index.php?act=member_address&op=address_add',
                    data: {key: key, true_name: true_name, mob_phone: mob_phone, tel_phone: tel_phone, city_id: city_id, area_id: area_id, address: address, area_info: area_info},
                    dataType: 'json',
                    success: function(result) {
                        if (result) {
                            $.ajax({//获取收货地址信息
                                type: 'post',
                                url: ApiUrl + '/index.php?act=member_address&op=address_info',
                                data: {key: key, address_id: result.datas.address_id},
                                dataType: 'json',
                                success: function(result1) {
                                    var data1 = result1.datas;
                                    $('#true_name').html(data1.address_info.true_name);
                                    $('#address').html(data1.address_info.area_info + ' ' + data1.address_info.address);
                                    $('#mob_phone').html(data1.address_info.mob_phone);
                                    $('input[name=address_id]').val(data1.address_info.address_id);
                                    // 隐藏 录入新地址 部分
                                    $("#new-address-wrapper").hide();
                                    // 清空 录入新地址 部分
                                    clear_addr_form();
                                }
                            });
                        }
                    }
                });
            } else {
                return false;
            }
        }

        var thisPrarent = $(this).parents(".buys1-address-cnt");
        showDetial(thisPrarent);
    });
    
    /**
     * 
     */
    function showDetial(parent) {
        $(".buys1-edit-btn").show();
        $(parent).find(".buys1-hide-list").addClass("hide");
        $(parent).find(".buys1-hide-detail").removeClass("hide");
    }
    /**
     * 
     */
    function hideDetail(parent) {
        $(".buys1-edit-btn").hide();
        $(parent).find(".buys1-hide-list").removeClass("hide");
        $(parent).find(".buys1-hide-detail").addClass("hide");
    }
    /**
     * 清空 录入新地址 部分
     */
    function clear_addr_form() {
        var prov_id = $('input[name=prov]').val();
        var city_id = $('input[name=city]').val();
        // 清空 录入新地址 部分的input输入框
        $("#addresslist input").val('');
        $('input[name=prov]').val(prov_id);
        $('input[name=city]').val(city_id);
        // 初始化 录入新地址 部分的select选框
        $("#addresslist select").val('');
    }
    /* 
     * 修改验证规则
     */
    $.sValid.init({//地址验证
        rules: {
            vtrue_name: {
                required: true,
                length_range: {
                    max: 15,
                    min: 2
                }
            },
            vmob_phone: {
                required: true,
                digits_length: 11
            },
            vtel_phone: {
                digits_length_range: {
                    max: 12,
                    min: 7
                }
            },
            vregion: {
                required: true
            },
            vaddress: {
                required: true,
                maxlength: 50
            }
        },
        messages: {
            vtrue_name: {
                required: "请填写姓名！",
                length_range: "收货人姓名2-15个字符限制！"
            },
            vmob_phone: {
                required: "请填写手机号码！",
                digits_length: "手机号码必须为11位数字！"
            },
            vtel_phone: {
                digits_length_range: "电话号码7-12位数字限制！"
            },
            vregion: {
                required: "请选择区县！"
            },
            vaddress: {
                required: "请填写街道！",
                maxlength: "街道50个字符限制！"
            }
        },
        callback: function(eId, eMsg, eRules) {
            if (eId.length > 0) {
                var errorHtml = "";
                $.map(eMsg, function(idx, item) {
                    errorHtml += "<p>" + idx + "</p>";
                });
                $(".error-tips").html(errorHtml).show();
            } else {
                $(".error-tips").html("").hide();
            }
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
        
        // 是否选着配送地址 
        if($(".buys1-hide-detail").hasClass('hide')){
            // 显示提示消息
            show_message('请填写配送信息', 'red');
            // 不做操作
            return false;
        }
        
        // 需要配送地址信息 
        if ($.trim($("#true_name").text()) === '' || $.trim($("#address").text()) === '' || $.trim($("#mob_phone").text()) === '') {
            // 显示提示消息
            show_message('请填写配送信息', 'red');
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
                                    location.href = "trade_success.html?log_id="+log_id+"&tohome=1";
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
        var flag = $('#flag').val();
        // 循环奶卡数量信息
        $(".cards").each(function () {
            // 奶品编号
            var milk_cd = $(this).attr("milk_cd");
            // 月卡对象
             $(this).find("input[name=month]").each(function () {
                 if( $(this).val()!=""){
                      order_data.push({"milk_cd": milk_cd, "card_type":  $(this).attr('milk_card_type'), "goods_id": $(this).attr('goods_id'), "goods_price": $(this).attr('goods_price'), "goods_num": $.trim($(this).val())});
                 }
             });
        });
        // 需要返回的数据对象（自取点编号，用户姓名，用户电话）
        var submit_data = {"self_receive_spot_cd": "", "name": $.trim($("#true_name").text()), "tel": $.trim($("#mob_phone").text()),"remark": remark, "address": $.trim($("#address").text())};
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
