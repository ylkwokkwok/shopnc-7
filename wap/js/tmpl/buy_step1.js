$(function() {
    var key = getcookie('key');
    var ifcart = GetQueryString('ifcart');
    if (ifcart == 1) {
        var cart_id = GetQueryString('cart_id');
        var data = {key: key, ifcart: 1, cart_id: cart_id};
    } else {
        var goods_id = GetQueryString("goods_id");
        var number = GetQueryString("buynum");
        var cart_id = goods_id + '|' + number;
        var data = {key: key, cart_id: cart_id};
        var delivery_type = GetQueryString("delivery_type");
        var milk_product_num = GetQueryString("milk_product_num");
        var milk_card_type = GetQueryString("milk_card_type");
        var book_time = GetQueryString("book_time");
    }
    if (book_time == '0') {
        $("#appDateTime").val("");
        $("#bestDiv").css('display', 'none');
    } else {
        var date = new Date();
        var seperator1 = "-";
        var seperator2 = ":";
        var month = date.getMonth() + 1;
        var strDate = date.getDate() + 1;
        var minutes = date.getMinutes();
        if (month >= 1 && month <= 9) {
            month = "0" + month;
        }
        if (strDate >= 0 && strDate <= 9) {
            strDate = "0" + strDate;
        }
        if (minutes >= 1 && minutes <= 9) {
            minutes = "0" + minutes;
        }
        var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
                + " " + date.getHours() + seperator2 + minutes;
        $("#appDateTime").val(currentdate);
        $("#bestDiv").css('display', 'block');
    }
    var isFCode = false;

    var pf = function(f) {
        return parseFloat(f) || 0;
    };

    var p2f = function(f) {
        return (parseFloat(f) || 0).toFixed(2);
    };

    var isEmpty = function(o) {
        var b = true;
        $.each(o, function(k, v) {
            b = false;
            return false;
        });
        return b;
    }

    var cod = (function() {
        // COD开关
        var codSwitch = $('#buy-type-offline').prop('checked');

        // COD状态
        var codGlobal = false;
        var codStores = {};

        // 0b1 正在使用货到付款
        // 0b2 可以使用预存款和充值卡
        var paymentFlag = 2;

        var stateChanged = function() {
            if (codGlobal) {
                var flag1 = false;
                var flag2 = false;

                $('.store-cod-supported').each(function() {
                    if (codStores[$(this).data('store_id')]) {
                        $(this).hide();
                        flag1 = flag1 || true;
                    } else {
                        $(this).show();
                        flag2 = flag2 || true;
                    }
                });

                paymentFlag = 0;

                if (flag1) {
                    paymentFlag |= 1;
                }
                if (flag2) {
                    paymentFlag |= 2;
                }
            } else {
                $('.store-cod-supported').hide();

                paymentFlag = 2;
            }

            switch (paymentFlag) {
                case 1:
                    // 支持货到付款的同时不支持在线支付
                    $('#offline').show();
                    $('#deposit').hide();
                    break;

                case 3:
                    // 支持货到付款的同时支持在线支付
                    $('#offline').show();
                    $('#deposit').show();
                    break;

                case 0:
                    // none
                case 2:
                    // 只支持在线支付
                default:
                    // default
                    $('#buy-type-offline').prop('checked', false);
                    $('#buy-type-online').prop('checked', true);

                    // 关闭COD开关
                    codSwitch = false;

                    $('#offline').hide();
                    $('#deposit').show();
                    break;
            }

            // 在线支付默认优先开关控制
            if (!codSwitch) {
                $('#deposit').show();
            }

            refleshTotals();
        };

        var refleshTotals = function() {
            if (codSwitch && codGlobal) {
                var codTotal = 0;
                var onlineTotal = 0;

                $('.store_total').each(function() {
                    var sid = this.id.substring(2);
                    var st = parseFloat(this.innerHTML) || 0;
                    if (codStores[sid]) {
                        codTotal += st;
                    } else {
                        onlineTotal += st;
                    }
                });

                //console.log(codTotal);
                //console.log(onlineTotal);
                $('#online-total-wrapper').show();
                $('#online-total').html(p2f(onlineTotal));
            }
            /* lyq@newland 添加开始 **/
            /* 时间：2015/07/02      **/
            else if ($("input[name=points_cash_ratio]").val() != '') {   // 推广积分抵扣开启时
                var codTotal = 0;
                var onlineTotal = 0;

                $('.store_total').each(function() {
                    var sid = this.id.substring(2);
                    var st = parseFloat(this.innerHTML) || 0;
                    if (codStores[sid]) {
                        codTotal += st;
                    } else {
                        onlineTotal += st;
                    }
                });

                // 当前使用的推广积分
                var extend_points = $("input[name=extend_points]").val();
                // 需要在线支付的货款
                onlineTotal -= parseFloat(extend_points == '' ? 0 : (extend_points * parseInt($("input[name=points_cash_ratio]").val()) / 100));

                $('#online-total-wrapper').show();
                $('#online-total').html(p2f(onlineTotal));
            }
            /* lyq@newland 添加结束 **/
            else {
                $('#online-total-wrapper').hide();
            }
        }

        var switchTriggered = function(b) {
            codSwitch = b;

            stateChanged();
        };

        var stateUpdateded = function(allow_offpay, allow_offpay_batch) {
            codGlobal = allow_offpay == '1';
            codStores = allow_offpay_batch || {};

            stateChanged();
        };

        return {
            switchTriggered: switchTriggered,
            stateUpdateded: stateUpdateded,
            refleshTotals: refleshTotals,
            z: 0
        };
    })();

    /* lyq@newland 添加开始 **/
    /* 时间：2015/06/10      **/
    /* wap端loading画面      **/
    // ajax响应 计数器
    var ajax_count = 0;
    /* lyq@newland 添加结束 **/

    /* lyq@newland 添加开始 **/
    /* 时间：2015/08/24      **/
    // 是否购买了心乐自营店的奶卡
    var if_buy_milk_cards = false;
    /* lyq@newland 添加结束 **/

    /* lyq@newland 添加开始 **/
    /* 时间：2015/09/15      **/
    // 可用的自取点列表
    var avilable_self_cds;
    /* lyq@newland 添加结束 **/

    $.ajax({//提交订单信息
        type: 'post',
        url: ApiUrl + '/index.php?act=member_buy&op=buy_step1',
        dataType: 'json',
        data: data,
        success: function(result) {
            var data = result.datas;
            if (typeof (data.error) != 'undefined') {
                location.href = WapSiteUrl;
            }

            var htmldata = '';
            var total_price = 0;
            var i = 0;

            /* lyq@newland 添加开始 **/
            /* 时间：2015/09/15      **/
            // 店铺-自取点 信息
            avilable_self_cds = data.avilable_self_cds;
            /* lyq@newland 添加结束 **/

            /* lyq@newland 添加开始 **/
            /* 时间：2015/07/02      **/
            // 可用推广积分
            var valid_extend_points = 0;
            /* lyq@newland 添加结束 **/
            $.each(data.store_cart_list, function(k, v) {//循环店铺
                /* fq@newland 修改开始   **/
                /* 时间：2015/06/17       **/
                // 商品计数器
                var j = 0;
                /* fq@newland 修改结束   **/

                if (i == 0) {
                    htmldata += '<li>';
                } else {
                    htmldata += '<li class="bd-t-cc">';
                }
                i++;
                /* lyq@newland 修改开始 **/
                /* 时间：2015/09/17     **/
                // 增加店铺ID，更换配送/自取时循环店铺用
                htmldata += '<p class="buys-yt-tlt store_p" store_id="' + k + '">店铺名称：' + v.store_name + '<span data-store_id="' + k + '" class="store-cod-supported" style="display:none;">（该店铺不支持选定收货地址的货到付款）</span></p>';
                /* lyq@newland 修改结束 **/
                $.each(v.goods_list, function(k1, v1) {//循环商品列表
                    if (j == 0) {
                        htmldata += '<div class="buys1-pdlist">';
                    } else {
                        htmldata += '<div class="buys1-pdlist bd-t-de">';
                    }
                    j++;

                    if (v1.is_fcode == '1') {
                        isFCode = true;
                        $('#container-fcode').show();
                    }

                    htmldata += '<div class="clearfix">'
                            + '<a class="img-wp" href="' + WapSiteUrl + '/tmpl/product_detail.html?goods_id=' + v1.goods_id + '">'
                            + '<img src="' + v1.goods_image_url + '"/>'
                            + '</a>'
                            + '<div class="buys1-pdlcnt">'
                            + '<p><a class="buys1-pdlc-name" href="' + WapSiteUrl + '/tmpl/product_detail.html?goods_id=' + v1.goods_id + '">' + v1.goods_name + '</a></p>'
                            /* lyq@newland 修改开始 **/
                            /* 时间：2015/09/17     **/
                            // 配送单价
                            + '<p class="normal_price">单价(元)：￥' + v1.goods_price + '</p>'
                            // 自取单价
                            + '<p class="self_price">单价(元)：￥' + p2f(pf(v1.goods_price) - pf(v1.goods_self_discount)) + '</p>'
                            /* lyq@newland 修改结束 **/
                            + '<p>数量：' + v1.goods_num + '</p>'
                            + '</div>'
                            + '</div>'
                            + '</div>';
                    /* yzp@newland 添加开始**/
                    /* 时间：2016/04/15**/
                    if (v1.gc_id != '1001' && v1.gc_id != '1002' && v1.gc_id != '1004') {
                        $('#mc_deliver').hide();
                        $('#delivery_mode').hide();
                    }
                    /* yzp@newland 添加结束**/
                });
                htmldata += '<div class="shop-total"><p>运费：￥<span id="store' + k + '"></span></p>';
                if (v.store_mansong_rule_list != null) {
                    htmldata += '<p>满即送-' + v.store_mansong_rule_list.desc + ':-' + v.store_mansong_rule_list.discount + '</p>';
                }


                if (v.store_voucher_list && !isEmpty(v.store_voucher_list)) {
                    htmldata += '<p><select name="voucher" store_id="' + k + '">';
                    htmldata += '<option value="0">请选择...</option>';
                    $.each(v.store_voucher_list, function(k2, v2) {
                        htmldata += '<option value="' + v2.voucher_t_id + '|' + k + '|' + v2.voucher_price + '">' + v2.voucher_title + '</option>'
                    });
                    htmldata += '</select>:￥-<span id="sv' + k + '">0.00</span></p>';
                }

                if (v.store_mansong_rule_list != null) {
                    var sp_total = pf(v.store_goods_total) - pf(v.store_mansong_rule_list.discount);
                    htmldata += '<p class="clr-c07">本店合计：￥<span id="st' + k + '" store_price="' + sp_total + '" class="store_total">' + p2f(sp_total) + '</span></p>';
                } else {
                    var sp_total = pf(v.store_goods_total);
                    /* lyq@newland 修改开始 **/
                    /* 时间：2015/09/17     **/
                    // 自取时店铺商品总价
                    var sp_self_total = pf(v.store_goods_self_total);
                    htmldata += '<p class="clr-c07">本店合计：￥<span id="st' + k + '" store_price="' + sp_total + '" normal_price="' + sp_total + '" self_price="' + sp_self_total + '" class="store_total">' + p2f(sp_total) + '</span></p>';
                    /* lyq@newland 修改结束 **/
                }
                htmldata += '</div>';
                htmldata += '</li>';
                /* lyq@newland 添加开始 **/
                /* 时间：2015/07/02      **/
                // 累加可用推广积分
                valid_extend_points += parseInt(parseInt(sp_total * data.order_cash_ratio * 100) / data.points_cash_ratio);
                /* lyq@newland 添加结束 **/
                total_price += sp_total;
            });
            /* lyq@newland 添加开始 **/
            /* 时间：2015/07/02      **/
            if (valid_extend_points > data.extend_points) { // 可用推广积分大于拥有的积分
                valid_extend_points = data.extend_points;
            }
            /* lyq@newland 添加结束 **/

            // 订单列表
            $("#deposit").before(htmldata);
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

            $('#total_price').html(p2f(total_price));
            $('input[name=total_price]').val(total_price);

            /* lyq@newland 添加开始 **/
            /* 时间：2015/07/02      **/
            if (data.points_use_isuse && valid_extend_points > 0) { // 推广积分抵扣可用 且 可用推广积分大于0
                $('.pre-deposit-wp').show();
                $('#ep').show();
                $("#valid_extend_points").text(valid_extend_points);
                $("#cash_per_points").text(parseFloat(data.points_cash_ratio / 100));
                // 保存推广积分与现金比例
                $("input[name=points_cash_ratio]").val(data.points_cash_ratio);
                // 保存推广积分订单抵扣比例
                $("input[name=order_cash_ratio]").val(data.order_cash_ratio);
                // 保存可用推广积分
                $("input[name=available_extend_points]").val(valid_extend_points);
            }
            /* lyq@newland 添加结束 **/

            if (data.available_rc_balance != null && data.available_rc_balance > 0) { // 充值卡余额
                $('.pre-deposit-wp').show();
                $('#wrapper-usercbpay').show();
                $('#available_rc_balance').html(data.available_rc_balance);
                $('input[name=available_rc_balance]').val(data.available_rc_balance);
            }

            if (data.available_predeposit != null && data.available_predeposit > 0) {//预存款
                $('.pre-deposit-wp').show();
                $('#wrapper-usepdpy').show();
                $('#available_predeposit').html(data.available_predeposit);
                $('input[name=available_predeposit]').val(data.available_predeposit);
            }

            // 点击使用新地址才显示新地址编辑框
            $('#new-address-button').click(function() {
                /* lyq@newland 添加开始   **/
                /* 时间：2015/06/15        **/
                // 清空 录入新地址 部分
                clear_addr_form();
                /* lyq@newland 添加结束   **/

                // 显示 录入新地址 部分
                $('#new-address-wrapper').show();
            });

            // 选择COD则不显示预存款和充值卡
            $('#online').click(function() {
                cod.switchTriggered(0);
            });
            $('#offline').click(function() {
                cod.switchTriggered(1);
            });

            /* lyq@newland 添加开始 **/
            /* 时间：2015/08/21      **/
            // 获取url参数中的自取点编号
            var self_receive_spot_cd = GetQueryString("self_receive_spot_cd");
            // 如果url参数中存在自取点编号
            if (self_receive_spot_cd !== null) {
                // 模拟点击“自取”
                $("#self").trigger("click");
                // ajax获取自取点信息
                $.ajax({
                    type: 'post',
                    url: ApiUrl + '/index.php?act=member_buy&op=get_self_receive_spot_info',
                    data: {key: key, self_receive_spot_cd: self_receive_spot_cd},
                    dataType: 'json',
                    success: function(result) {
                        // 获取自取点信息失败
                        if (result.datas === null) {
                            // 自取点编号赋值
                            $("#self_receive_spot_cd").val("");
                            // 隐藏自取点详细信息
                            $("#self_receive_spot_info").hide();
                            var error_html = '<p style="color:red;"><b>自取点信息错误，请重新选择自取点！</b></p>';
                            // 显示自取点错误信息
                            $("#self_error").html(error_html).show();
                        }
                        // 获取自取点信息成功
                        else {
                            // 自取点编号赋值
                            $("#self_receive_spot_cd").val(self_receive_spot_cd);
                            var self_receive_spot_html = '';
                            self_receive_spot_html += '<div class="fleft" style="width:100%;">';
//                            self_receive_spot_html += '    <div class="fimg fleft" style="margin-bottom: -5px;">';
//                            self_receive_spot_html += '        <img src="'+result.datas.logo_url+'">';
//                            self_receive_spot_html += '    </div>';
                            self_receive_spot_html += '    <div class="fleft mbt">';
                            self_receive_spot_html += '        <p>自取点：' + result.datas.self_receive_nm + '</p>';
                            self_receive_spot_html += '        <p>地　址：' + result.datas.address + '</p>';
                            self_receive_spot_html += '        <p>电　话：' + result.datas.tel + '</p>';
                            self_receive_spot_html += '    </div>';
                            self_receive_spot_html += '</div>';
                            // 显示自取点详细信息
                            $("#self_receive_spot_info").html(self_receive_spot_html).show();
                        }
                    }
                });
            } else {
                // 模拟点击“配送”
                $("#send").trigger("click");
            }
            /* lyq@newland 添加结束 **/

            /* lyq@newland 添加开始 **/
            /* 时间：2015/08/24      **/
            // 用户购买了心乐自营店的奶卡
            if (data.if_buy_milk_cards) {
                // 显示奶卡配送信息
                $("#mc_deliver").show();
                if_buy_milk_cards = true;
            }
            /* lyq@newland 添加结束 **/

            /*
             if(data.ifshow_offpay){//支付方式
             $('#offline').show();
             }else{
             $('#offline').hide();
             }
             */

            $('#inv_content').html(data.inv_info.content);
            $('input[name=address_id]').val(data.address_info.address_id);
            $('input[name=area_id]').val(data.address_info.area_id);
            $('input[name=city_id]').val(data.address_info.city_id);
            $('input[name=freight_hash]').val(data.freight_hash);
            $('input[name=vat_hash]').val(data.vat_hash);
            $('input[name=offpay_hash]').val(data.offpay_hash);
            $('input[name=offpay_hash_batch]').val(data.offpay_hash_batch);
            $('input[name=invoice_id]').val(data.inv_info.inv_id);

            var area_id = data.address_info.area_id;
            var city_id = data.address_info.city_id;
            var freight_hash = data.freight_hash;

            $.ajax({//保存地址
                type: 'post',
                url: ApiUrl + '/index.php?act=member_buy&op=change_address_milk',
                data: {key: key, area_id: area_id, city_id: city_id, freight_hash: freight_hash, self_receive_spot_cd: GetQueryString("self_receive_spot_cd")},
                dataType: 'json',
                success: function(result) {
                    if (result.datas.state == 'success') {
                        var sp_s_total = 0;
                        $.each(result.datas.content, function(k, v) {
                            v = pf(v);
                            $('#store' + k).html(p2f(v));
                            var sp_toal = pf($('#st' + k).attr('store_price'));//店铺商品价格
                            sp_s_total += pf(v);
                            $('#st' + k).html(p2f(sp_toal + v));
                        });

                        var total_price = pf($('input[name=total_price]').val()) + sp_s_total;
                        $('#total_price').html(p2f(total_price));
                        //$('input[name=total_price]').val(total_price);

                        cod.stateUpdateded(result.datas.allow_offpay, result.datas.allow_offpay_batch);

                        $('input[name=allow_offpay]').val(result.datas.allow_offpay);
                        $('input[name=offpay_hash]').val(result.datas.offpay_hash);
                        $('input[name=offpay_hash_batch]').val(result.datas.offpay_hash_batch);
                    }

                    /* lyq@newland 添加开始 **/
                    /* 时间：2015/06/10      **/
                    /* wap端loading画面      **/
                    // 计数器累加
                    ajax_count++;
                    // 如果计数完成
                    if (ajax_count === 2) {
                        // 显示页面内容
                        $(".buy_step1").show();
                        // 隐藏loading画面
                        $("#loading_page").hide();
                    }
                    /* lyq@newland 添加结束 **/
                }
            });

            $('select[name=voucher]').change(function() {//选择代金券
                var store_id = $(this).attr('store_id');
                var varr = $(this).val();
                if (varr == 0) {
                    var store_price = 0;
                } else {
                    var store_price = pf(varr.split('|')[2]);
                }
                var store_total_price = pf($('#st' + store_id).attr('store_price'));
                var store_tran = pf($('#store' + store_id).html());
                store_total = pf(store_total_price) - store_price + store_tran;
                $("#sv" + store_id).html(p2f(store_price));
                $("#st" + store_id).html(p2f(store_total));

                var total_price = 0;
                $('.store_total').each(function() {
                    total_price += pf($(this).html());
                });
                $('#total_price').html(p2f(total_price));

                cod.refleshTotals();
            });

            /* lyq@newland 添加开始 **/
            /* 时间：2015/07/02      **/
            $("input[name=extend_points]").change(function() {  // 改变推广积分
                // 当前使用积分
                var extend_points = $("input[name=extend_points]").val() == '' ? 0 : $("input[name=extend_points]").val();
                // 当前可用积分
                var max_ep = $("input[name=available_extend_points]").val();
                if (!/^\d+$/.test(parseFloat(extend_points))) {    // 推广积分输入格式错误
                    $.sDialog({
                        skin: "red",
                        /*zly@newland 修改金米为积分开始**/
                        /* 时间：2015/08/10      **/
                        content: '积分数必须为非负整数',
                        /*zly@newland 修改金米为积分结束**/
                        okBtn: false,
                        cancelBtn: false
                    });
                    return false;
                }
                if (parseInt(extend_points) > parseInt(max_ep)) {    // 使用积分大于可用积分
                    $.sDialog({
                        skin: "red",
                        /*zly@newland 修改金米为积分开始**/
                        /* 时间：2015/08/10      **/
                        content: '可用积分：' + max_ep,
                        /*zly@newland 修改金米为积分结束**/
                        okBtn: false,
                        cancelBtn: false
                    });
                    return false;
                }
                cod.refleshTotals();
            });
            /* lyq@newland 添加结束 **/
        }
    });

    $.ajax({//获取区域列表
        type: 'post',
        url: ApiUrl + '/index.php?act=member_address&op=area_list',
        data: {key: key},
        dataType: 'json',
        success: function(result) {
            checklogin(result.login);
            var data = result.datas;
            var prov_html = '';
            for (var i = 0; i < data.area_list.length; i++) {
                prov_html += '<option value="' + data.area_list[i].area_id + '">' + data.area_list[i].area_name + '</option>';
            }
            $("select[name=prov]").append(prov_html);

            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/10      **/
            /* wap端loading画面      **/
            // 计数器累加
            ajax_count++;
            // 如果计数完成
            if (ajax_count === 2) {
                // 显示页面内容
                $(".buy_step1").show();
                // 隐藏loading画面
                $("#loading_page").hide();
            }
            /* lyq@newland 添加结束 **/
        }
    });

    /* lyq@newland 添加开始 **/
    /* 时间：2015/08/24      **/
    $.ajax({
        type: 'post',
        url: ApiUrl + '/index.php?act=member_address&op=area_list',
        data: {key: key, area_id: "108"},
        dataType: 'json',
        success: function(result) {
            checklogin(result.login);
            var data = result.datas;
            var region_html = '<option value="">请选择...</option>';
            for (var i = 0; i < data.area_list.length; i++) {
                region_html += '<option value="' + data.area_list[i].area_id + '">' + data.area_list[i].area_name + '</option>';
            }
            $("select[name=region]").html(region_html);
        }
    });
    /* lyq@newland 添加结束 **/

    $("select[name=prov]").change(function() {//选择省市
        var prov_id = $(this).val();
        $.ajax({
            type: 'post',
            url: ApiUrl + '/index.php?act=member_address&op=area_list',
            data: {key: key, area_id: prov_id},
            dataType: 'json',
            success: function(result) {
                checklogin(result.login);
                var data = result.datas;
                var city_html = '<option value="">请选择...</option>';
                for (var i = 0; i < data.area_list.length; i++) {
                    city_html += '<option value="' + data.area_list[i].area_id + '">' + data.area_list[i].area_name + '</option>';
                }
                $("select[name=city]").html(city_html);
                $("select[name=region]").html('<option value="">请选择...</option>');
            }
        });
    });

    $("select[name=city]").change(function() {//选择城市
        var city_id = $(this).val();
        $.ajax({
            type: 'post',
            url: ApiUrl + '/index.php?act=member_address&op=area_list',
            data: {key: key, area_id: city_id},
            dataType: 'json',
            success: function(result) {
                checklogin(result.login);
                var data = result.datas;
                var region_html = '<option value="">请选择...</option>';
                for (var i = 0; i < data.area_list.length; i++) {
                    region_html += '<option value="' + data.area_list[i].area_id + '">' + data.area_list[i].area_name + '</option>';
                }
                $("select[name=region]").html(region_html);
            }
        });
    });

    $(".buys1-edit-address").click(function() {//修改收获地址
        var self = this;
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
                            /* lyq@newland 修改开始   **/
                            /* 时间：2015/06/15        **/
                            // 默认选中第一条地址信息
                            + '<input type="radio" name="address" class="rdo address-radio" value="' + data.address_list[i].address_id + '" city_id="' + data.address_list[i].city_id + '" area_id="' + data.address_list[i].area_id + '" ' + (i === 0 ? 'checked="checked"' : '') + ' />'
                            /* lyq@newland 修改结束   **/
                            + '<span class="mr5 rdo-span"><span class="true_name_' + data.address_list[i].address_id + '">' + data.address_list[i].true_name + '</span> <span class="address_id_' + data.address_list[i].address_id + '">' + data.address_list[i].area_info + ' ' + data.address_list[i].address + '</span> <span class="mob_phone_' + data.address_list[i].address_id + '">' + data.address_list[i].mob_phone + '</span></span>'
                            + '</label>'
                            + '<a class="del-address" href="javascript:void(0);" address_id="' + data.address_list[i].address_id + '">[删除]</a>'
                            + '</li>';
                }
                $('li.existent-address').remove();
                $('#addresslist').before(html);

                /* lyq@newland 添加开始   **/
                /* 时间：2015/06/15        **/
                if (data.address_list.length > 0) { // 存在地址信息
                    // 取消选中使用新的地址信息
                    $("#new-address-button").removeAttr('checked');
                    // 隐藏 录入新地址 部分
                    $('#new-address-wrapper').hide();
                }
                /* lyq@newland 添加结束   **/

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
                            /* lyq@newland 添加开始   **/
                            /* 时间：2015/06/15        **/
                            if ($("li.existent-address").length == 0) { // 不存在地址信息
                                // 选中 使用新的地址信息
                                $("#new-address-button").attr('checked', 'checked').trigger('click');
                                // 清空 录入新地址 部分
                                clear_addr_form();
                                // 显示 录入新地址 部分
                                $("#new-address-wrapper").show();
                            } else { // 存在地址信息
                                if ($("li.existent-address input:checked").length === 0) { // 不存在选中的地址信息
                                    // 选中第一条地址信息
                                    $("li.existent-address input").first().attr('checked', 'checked').trigger('click');
                                }
                            }
                            /* lyq@newland 添加结束   **/
                        }
                    });
                });

                $('input[name=address]').click(function() {
                    /* lyq@newland 添加开始   **/
                    /* 时间：2015/06/15        **/
                    // 取消选中所有radio
                    $('input[name=address]').removeAttr('checked');
                    // 选中当前点击的radio
                    $(this).attr('checked', 'checked');
                    /* lyq@newland 添加结束   **/

                    var city_id = $(this).attr('city_id');
                    var area_id = $(this).attr('area_id');

                    $('input[name=city_id]').val(city_id);
                    $('input[name=area_id]').val(area_id);
                });
            }
        });
        var thisPrarent = $(this).parents(".buys1-address-cnt");
        hideDetail(thisPrarent);
    });

    /* lyq@newland 修改开始   **/
    /* 时间：2015/06/16        **/
    /* 修改验证规则            **/
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
            vprov: {
                required: true
            },
            vcity: {
                required: true
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
            vprov: {
                required: "请选择省份！"
            },
            vcity: {
                required: "请选择城市！"
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
    /* lyq@newland 修改结束   **/

    $(".save-address").click(function() {//更换收获地址
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

        if (address_id > 0) {//变更地址
            var area_id = $("input[name=area_id]").val();
            var city_id = $("input[name=city_id]").val();
            var freight_hash = $("input[name=freight_hash]").val();
            $.ajax({
                type: 'post',
                url: ApiUrl + '/index.php?act=member_buy&op=change_address_milk',
                data: {key: key, area_id: area_id, city_id: city_id, freight_hash: freight_hash, self_receive_spot_cd: GetQueryString("self_receive_spot_cd")},
                dataType: 'json',
                success: function(result) {
                    var data = result.datas;
                    var sp_s_total = 0;
                    $.each(data.content, function(k, v) {
                        v = pf(v);
                        $('#store' + k).html(p2f(v));
                        var sp_toal = pf($('#st' + k).attr('store_price'));//店铺商品价格
                        sp_s_total += v;
                        $('#st' + k).html(p2f(sp_toal + v));
                    });

                    var total_price = pf($('input[name=total_price]').val()) + sp_s_total;
                    $('#total_price').html(p2f(total_price));

                    $("input[name=address_id]").val(address_id);
                    $('#address').html($('.address_id_' + address_id).html());
                    $('#true_name').html($('.true_name_' + address_id).html());
                    $('#mob_phone').html($('.mob_phone_' + address_id).html());

                    cod.stateUpdateded(result.datas.allow_offpay, result.datas.allow_offpay_batch);

                    $('input[name=allow_offpay]').val(result.datas.allow_offpay);
                    $('input[name=offpay_hash]').val(result.datas.offpay_hash);
                    $('input[name=offpay_hash_batch]').val(result.datas.offpay_hash_batch);

                    /* lyq@newland 添加开始   **/
                    /* 时间：2015/06/15        **/
                    // 隐藏 录入新地址 部分
                    $("#new-address-wrapper").hide();
                    // 清空 录入新地址 部分
                    clear_addr_form();
                    /* lyq@newland 添加结束   **/

                    return false;
                }
            });
        } else {//保存地址
            if ($.sValid()) {
//                var index = $('select[name=prov]')[0].selectedIndex;
//                var aa = $('select[name=prov]')[0].options[index].innerHTML;


                var true_name = $('input[name=true_name]').val();
                var mob_phone = $('input[name=mob_phone]').val();
                var tel_phone = $('input[name=tel_phone]').val();
                var city_id = $('input[name=city]').val();//$('select[name=city]').val();
                var area_id = $('select[name=region]').val();
                var address = $('input[name=vaddress]').val();

//                var prov_index = $('select[name=prov]')[0].selectedIndex;
//                var city_index = $('select[name=city]')[0].selectedIndex;
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
                                    $('input[name=area_id]').val(data1.address_info.area_id);
                                    $('input[name=city_id]').val(data1.address_info.city_id);

                                    var area_id = data1.address_info.area_id;
                                    var city_id = data1.address_info.city_id;
                                    var freight_hash = $('input[name=freight_hash]').val();

                                    $.ajax({//保存收货地址
                                        type: 'post',
                                        url: ApiUrl + '/index.php?act=member_buy&op=change_address_milk',
                                        data: {key: key, area_id: area_id, city_id: city_id, freight_hash: freight_hash, self_receive_spot_cd: GetQueryString("self_receive_spot_cd")},
                                        dataType: 'json',
                                        success: function(result) {
                                            var data = result.datas;
                                            var sp_s_total = 0;
                                            $.each(result.datas.content, function(k, v) {
                                                v = pf(v);
                                                $('#store' + k).html(p2f(v));
                                                var sp_toal = pf($('#st' + k).attr('store_price'));//店铺商品价格
                                                sp_s_total += v;
                                                $('#st' + k).html(p2f(sp_toal + v));
                                            });

                                            var total_price = pf($('input[name=total_price]').val()) + sp_s_total;
                                            $('#total_price').html(p2f(total_price));

                                            cod.stateUpdateded(data.allow_offpay, data.allow_offpay_batch);

                                            $('input[name=allow_offpay]').val(data.allow_offpay);
                                            $('input[name=offpay_hash]').val(data.offpay_hash);
                                            $('input[name=offpay_hash_batch]').val(data.offpay_hash_batch);

                                            /* lyq@newland 添加开始   **/
                                            /* 时间：2015/06/15        **/
                                            // 隐藏 录入新地址 部分
                                            $("#new-address-wrapper").hide();
                                            // 清空 录入新地址 部分
                                            clear_addr_form();
                                            /* lyq@newland 添加结束   **/

                                            return false;
                                        }
                                    });
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

    $('#pguse').click(function() {//验证密码
        var loginpassword = $("input[name=loginpassword]").val();
        if (loginpassword == '') {
            $('.password_error_tip').show();
            $('.password_error_tip').html('支付密码不能为空');
            return false;
        }
        $.ajax({
            type: 'post',
            url: ApiUrl + '/index.php?act=member_buy&op=check_password',
            data: {key: key, password: loginpassword},
            dataType: 'json',
            success: function(result) {
                if (result.datas == 1) {
                    $('input[name=passwd_verify]').val('1');
                    $('#pd').hide();
                } else {
                    $('#pd').show();
                    $('.password_error_tip').show();
                    $('.password_error_tip').html(result.datas.error);
                }
            }
        });
    });

    $('#usepdpy,#usercbpay').click(function() {//验证密码切换
        if ($('#usepdpy').attr('checked') || $('#usercbpay').attr('checked')) {
            $('#pd').show();
        } else {
            $('#pd').hide();
        }
    });

    $('#buy_step2').click(function() {//提交订单step2
        var data = {};
        if (isFCode) {
            data.fcode = $('#fcode').val();
            if (data.fcode.length < 1) {
                $.sDialog({
                    skin: "red",
                    content: '请输入F码！',
                    okBtn: false,
                    cancelBtn: false
                });
                return false;
            }
        }
        if (ifcart == null) {
            if (delivery_type != "2") {
                if ($("input[name=deliver_type]:checked").val() != delivery_type) {
                    if (delivery_type == "0") {
                        $.sDialog({
                            skin: "red",
                            content: '您选择的商品请用自取方式！',
                            okBtn: false,
                            cancelBtn: false
                        });
                    }
                    if (delivery_type == "1") {
                        $.sDialog({
                            skin: "red",
                            content: '您选择的商品请用配送方式！',
                            okBtn: false,
                            cancelBtn: false
                        });
                    }
                    return false;
                }
            }
            if (book_time != 0) {
                var startDate = new Date();
                var endDate = new Date($("#appDateTime").val());
                var afterDay = dateDiff(endDate, startDate);//取得算出天数后剩余的秒数
                var hour = parseInt(afterDay / (60 * 60));//计算整数小时数 
                if (hour < book_time) {
                    $.sDialog({
                        skin: "red",
                        content: '您选择的商品最佳配送时间为' + book_time + '小时以后！',
                        okBtn: false,
                        cancelBtn: false
                    });
                    return false;
                }
            }
        }
        //调用该方法(主方法) 
        function dateDiff(date1, date2) {
            var type1 = typeof date1, type2 = typeof date2;
            if (type1 == 'string')
                date1 = stringToTime(date1);
            else if (date1.getTime)
                date1 = date1.getTime();
            if (type2 == 'string')
                date2 = stringToTime(date2);
            else if (date2.getTime)
                date2 = date2.getTime();
            return (date1 - date2) / 1000;//结果是秒 
        }


        /* lyq@newland 添加开始 **/
        /* 时间：2015/08/21      **/
        // 选择了自取且自取点编号为空时
        if ($("input[name=deliver_type]:checked").val() == '0' && $("#self_receive_spot_cd").val() == '') {
            $.sDialog({
                skin: "red",
                content: '请选择自取点！',
                okBtn: false,
                cancelBtn: false
            });
            return false;
        }
        // 选择了自取且自取点编号不为空时
        else if ($("input[name=deliver_type]:checked").val() == '0') {
            // 自取点编号
            data.self_receive_spot_cd = $("#self_receive_spot_cd").val();
        }
        // 选择配送时
        else {

        }
        /* lyq@newland 添加结束 **/

        /* lyq@newland 添加开始 **/
        /* 时间：2015/08/24      **/
        // 购买了心乐自营店的奶卡时
        if (if_buy_milk_cards) {
            // 奶卡配送方式 1 配送，0 不需配送
            data.mc_deliver_type = 0;//$("input[name=mc_deliver_type]").is(":checked")?1:0;
        }
        /* lyq@newland 添加结束 **/


        /* lyq@newland 添加开始 **/
        /* 时间：2015/07/02      **/
        if ($("input[name=points_cash_ratio]").val() != '') {   // 推广积分抵扣可用
            // 提交数据中添加 推广积分与现金比例
            data.points_cash_ratio = $("input[name=points_cash_ratio]").val();
            // 提交数据中添加 推广积分订单抵扣比例
            data.order_cash_ratio = $("input[name=order_cash_ratio]").val();
            // 提交数据中添加 当前使用积分
            data.extend_points = $("input[name=extend_points]").val() == '' ? 0 : $("input[name=extend_points]").val();
            // 当前可用积分
            var max_ep = $("input[name=available_extend_points]").val();
            if (!/^\d+$/.test(parseFloat(data.extend_points))) {    // 推广积分输入格式错误
                $.sDialog({
                    skin: "red",
                    content: '积分数必须为非负整数',
                    okBtn: false,
                    cancelBtn: false
                });
                return false;
            }
            if (parseInt(data.extend_points) > parseInt(max_ep)) {    // 使用积分大于可用积分
                // 提示错误信息
                $.sDialog({
                    skin: "red",
                    content: '可用积分：' + max_ep,
                    okBtn: false,
                    cancelBtn: false
                });
                return false;
            }
        }
        /* lyq@newland 添加结束 **/

        //从购物车进支付画面
        if (ifcart == 1) {
            //定义Json对象
            var json = {};
            //给json 对象添加数据
            json.key = key;
            json.cart_id = cart_id;
            var bool = true;
            $.ajax({
                type: 'post',
                async: false,
                url: ApiUrl + '/index.php?act=member_buy&op=get_type',
                data: json,
                dataType: 'json',
                success: function(result) {
                    var data = result.datas;
                    for (var i = 0; i < data.type_filter_result.length; i++) {
                        if (data.type_filter_result[i].delivery_type != "2") {
                            if ($("input[name=deliver_type]:checked").val() != data.type_filter_result[i].delivery_type) {
                                if (data.type_filter_result[i].delivery_type == "0") {
                                    $.sDialog({
                                        skin: "red",
                                        content: '您选择的' + data.type_filter_result[i].goods_name + '商品请用自取方式另行下单！',
                                        okBtn: false,
                                        cancelBtn: false
                                    });
                                }
                                if (data.type_filter_result[i].delivery_type == "1") {
                                    $.sDialog({
                                        skin: "red",
                                        content: '您选择的' + data.type_filter_result[i].goods_name + '商品请用配送方式另行下单！',
                                        okBtn: false,
                                        cancelBtn: false
                                    });
                                }
                                bool = false;
                                break;

                            }
                        }
                    }

                }
            });
            if (!bool) {
                return false;
            }
        }
        //从购物车进支付画面
        if (ifcart == 1) {
            //定义Json对象
            var json = {};
            //给json 对象添加数据
            json.key = key;
            json.cart_id = cart_id;
            var bool = true;
            $.ajax({
                type: 'post',
                async: false,
                url: ApiUrl + '/index.php?act=member_buy&op=get_best_time',
                data: json,
                dataType: 'json',
                success: function(result) {
                    var data = result.datas;
                    var h = data.filter_result[0].best_time;
                    var startDate = new Date();
                    var endDate = new Date($("#appDateTime").val());
                    var afterDay = dateDiff(endDate, startDate);//取得算出天数后剩余的秒数
                    var hour = parseInt(afterDay / (60 * 60));//计算整数小时数 
                    if (hour < h) {
                        $.sDialog({
                            skin: "red",
                            content: '您选择的商品最佳配送时间为' + book_time + '小时以后！',
                            okBtn: false,
                            cancelBtn: false
                        });
                        bool = false;
                    }
                }
            });
            if (!bool) {
                return false;
            }
        }


        data.key = key;
        if (ifcart == 1) {//购物车订单
            data.ifcart = ifcart;
        }
        data.cart_id = cart_id;
        if ($('#appDateTime').val() != "") {
            var appDateTime = $('#appDateTime').val();
            data.appDateTime = appDateTime;
        }
        var remark = $('#remark').val();
        data.remark = remark;
        var address_id = $('input[name=address_id]').val();
        data.address_id = address_id;

        var vat_hash = $('input[name=vat_hash]').val();
        data.vat_hash = vat_hash;

        var offpay_hash = $('input[name=offpay_hash]').val();
        data.offpay_hash = offpay_hash;

        var offpay_hash_batch = $('input[name=offpay_hash_batch]').val();
        data.offpay_hash_batch = offpay_hash_batch;

        //获取address_id
        var payRadio = $('input[name=buy-type]');
        var pay_name;
        for (var i = 0; i < payRadio.length; i++) {
            if (payRadio[i].checked) {
                pay_name = payRadio[i].value;
            }
        }
        data.pay_name = pay_name;

        var invoice_id = $('input[name=invoice_id]').val();
        data.invoice_id = invoice_id;

        /*
         var voucher = new Array();
         $("select[name=voucher]").each(function(){
         var store_id = $(this).attr('store_id');
         voucher[store_id] = $(this).val();
         });
         data.voucher = voucher;
         */

        var voucher = [];
        $("select[name=voucher]").each(function() {
            var v = $(this).val();
            if (v) {
                voucher.push(v);
            }
            // console.log(v);
        });
        data.voucher = voucher.join(',');
        // console.log(data.voucher);return;

        data.rcb_pay = 0;
        var available_rc_balance = parseInt($('input[name=available_rc_balance]').val());
        if (available_rc_balance > 0 && $('#usercbpay').prop('checked')) { // 使用充值卡
            var passwd_verify = parseInt($('input[name=passwd_verify]').val());
            if (passwd_verify != 1) { // 验证密码失败
                return false;
            }
            data.rcb_pay = 1;
            data.password = $('input[name=loginpassword]').val();
        }

        var available_predeposit = parseInt($('input[name=available_predeposit]').val());
        if (available_predeposit > 0) {
            if ($('#usepdpy').prop('checked')) {//使用预存款
                var passwd_verify = parseInt($('input[name=passwd_verify]').val());
                if (passwd_verify != 1) {//验证密码失败
                    return false;
                }

                var pd_pay = 1;
                data.pd_pay = pd_pay;
                var passwd = $('input[name=loginpassword]').val();
                data.password = passwd;
            } else {
                var pd_pay = 0;
                data.pd_pay = pd_pay;
            }
        } else {
            var pd_pay = 0;
            data.pd_pay = pd_pay;
        }


        $.ajax({
            type: 'post',
            url: ApiUrl + '/index.php?act=member_buy&op=buy_step2',
            data: data,
            dataType: 'json',
            /* lyq@newland 添加开始   **/
            /* 时间：2015/06/29        **/
            beforeSend: function() {
                // 显示页面覆盖层
                $("#wxpay_loading_mask").show();
                // 显示loading动画
                $("#wxpay_loading").show();
            },
            success: function(result) {
                // 隐藏页面覆盖层
                $("#wxpay_loading_mask").hide();
                // 隐藏loading动画
                $("#wxpay_loading").hide();
                /* lyq@newland 添加结束   **/

                checklogin(result.login);

                if (result.datas.error) {
                    $.sDialog({
                        skin: "red",
                        content: result.datas.error,
                        okBtn: false,
                        cancelBtn: false
                    });
                    return false;
                }

                if (result.datas.pay_sn.pay_sn != '') {
                    location.href = WapSiteUrl + '/tmpl/member/order_list.html?order_state=10';
                }
                return false;
            }
        });
    });

    /* lyq@newland 添加开始 **/
    /* 时间：2015/08/21      **/
    // 配送方式点击事件
    $('input[name=deliver_type]').click(function() {
        // 自取
        if ($(this).val() == '0') {
            // 显示自取点信息
            $("#self_receive").show();
            /* yzp@newland 添加开始 **/
            /* 时间：2016/04/15**/
            // 隐藏奶卡
            $("#mc_deliver").hide();
            /* yzp@newland 添加结束**/
            if ($.trim($("#self_receive_spot_info").html()) !== "") {
                // 显示自取点详细信息
                $("#self_receive_spot_info").show();
            } else if ($.trim($("#self_error").html()) !== "") {
                // 显示自取点错误信息
                $("#self_error").show();
            }
            // 解绑 奶卡配送选项的点击事件
            $("input[name=mc_deliver_type]").unbind("click");
            /* lyq@newland 添加开始 **/
            /* 时间：2015/09/17     **/
            // 隐藏配送价格
            $(".normal_price").hide();
            // 显示自取价格
            $(".self_price").show();
            // 以自取价格为准，更新店铺总价&商品总价
            update_store_price("self");
            /* lyq@newland 添加结束 **/
        }
        // 配送
        else {
            // 隐藏自取点信息
            $("#self_receive").hide();
            // 隐藏自取点详细信息
            $("#self_receive_spot_info").hide();
            // 隐藏自取点错误信息
            $("#self_error").hide();

            // 如果 奶卡配送未被选中
            if (!$("input[name=mc_deliver_type]").is(":checked")) {
                // 模拟点击奶卡配送
                $("input[name=mc_deliver_type]").trigger("click");
            }
            // 绑定 奶卡配送选项的点击事件
            $("input[name=mc_deliver_type]").bind("click", function() {
                /* yzp@newland 修改**/
                /* 时间：2016/4/15**/
//                // 禁止取消
//                return false;
                /* 修改结束**/
            });
            /* lyq@newland 添加开始 **/
            /* 时间：2015/09/17     **/
            // 显示配送价格
            $(".normal_price").show();
            // 隐藏自取价格
            $(".self_price").hide();
            // 以配送价格为准，更新店铺总价&商品总价
            update_store_price("normal");
            /* lyq@newland 添加结束 **/
        }
    });

    /* lyq@newland 添加开始 **/
    /* 时间：2015/09/17     **/
    /**
     * 更新店铺总价&商品总价
     * @param {type} type 更新类型 配送normal/自取self
     */
    function update_store_price(type) {
        // 订单总价
        var total_price = 0.0;
        // 循环店铺
        $(".store_p").each(function() {
            // 店铺ID
            var store_id = $(this).attr("store_id");
            // 店铺总价对象
            var stObj = $("#st" + store_id);
            // 店铺运费
            var stran = pf($("#store" + store_id).html());
            // 新店铺总价
            var new_st = pf($(stObj).attr(type + "_price"));
            // 店铺总价属性
            $(stObj).attr("store_price", p2f(new_st));
            // 店铺总价显示
            $(stObj).html(p2f(new_st + stran));
            // 订单总价累加
            total_price += new_st + stran;
        });
        // 更新订单总价显示
        $("#total_price").html(p2f(total_price));
        // 更新隐藏input中的订单总价
        $("input[name=total_price]").val(p2f(total_price));
    }
    /* lyq@newland 添加结束 **/

    // 点击“选择自取点”事件
    $("#select_self_receive_spot").click(function() {
        /* lyq@newland 添加开始 **/
        /* 时间：2015/09/15      **/
        // 可用自取点
        var self_cds;
        // 可用自取点 值为 true 时
        if (typeof (avilable_self_cds) == 'boolean' && avilable_self_cds === true) {
            // 全部自取点可选
            self_cds = '';
        }
        // 可用自取点 值为 false 时
        else if (typeof (avilable_self_cds) == 'boolean' && avilable_self_cds === false) {
            $.sDialog({
                skin: "red",
                content: "无可用自取点，请分别支付不同店铺的商品！",
                okBtn: false,
                cancelBtn: false
            });
            $(".s-dialog-wrapper").css({"max-width": "50%", "text-align": "center"});
            return false;
        }
        // 存在可用自取点时
        else {
            self_cds = '&self_cds=' + avilable_self_cds;
        }
        /* lyq@newland 添加结束 **/

        // 购物信息页面url参数
        var cart_query_str = "";
        // 购物车进入
        if (ifcart == 1) {
            cart_query_str = "ifcart=1&cart_id=" + cart_id;
        }
        // 商品详情进入
        else {
            cart_query_str = "goods_id=" + goods_id + "&buynum=" + number + "&delivery_type=" + delivery_type + "&milk_product_num=" + milk_product_num + "&milk_card_type=" + milk_card_type + '&book_time=' + book_time;
        }
        // 跳转至自取点选择页面
        location.href = WapSiteUrl + "/milk/nearby_stores.html?" + cart_query_str + self_cds;
    });
    /* lyq@newland 添加结束 **/

    function showDetial(parent) {
        $(parent).find(".buys1-edit-btn").show();
        $(parent).find(".buys1-hide-list").addClass("hide");
        $(parent).find(".buys1-hide-detail").removeClass("hide");
    }
    function hideDetail(parent) {
        $(parent).find(".buys1-edit-btn").hide();
        $(parent).find(".buys1-hide-list").removeClass("hide");
        $(parent).find(".buys1-hide-detail").addClass("hide");
    }

    /* lyq@newland 添加开始   **/
    /* 时间：2015/06/15        **/
    /**
     * 清空 录入新地址 部分
     */
    function clear_addr_form() {
        // 清空 录入新地址 部分的input输入框
        $("#addresslist input").val('');
        // 初始化 录入新地址 部分的select选框
        $("#addresslist select").val('');
    }
    /* lyq@newland 添加结束   **/
});