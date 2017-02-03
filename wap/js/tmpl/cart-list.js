$(function() {
    var key = getcookie('key');
    if (key == '') {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
    } else {
        //初始化页面数据
        function initCartList() {
            $.ajax({
                url: ApiUrl + "/index.php?act=member_cart&op=cart_list",
                type: "post",
                dataType: "json",
                data: {key: key},
                success: function(result) {
                    if (checklogin(result.login)) {
                        if (!result.datas.error) {
                            var rData = result.datas;
                            /* wqw@newland 添加开始   **/
                            /* 时间：2015/06/04        **/
                            /* 功能ID：ADMIN006      **/
                            rData.SiteUrl = SiteUrl;
                            template.helper('in_array', function(str, arr) {
                                return $.inArray(str, arr);
                            });
                            /* wqw@newland 添加结束   **/
                            rData.WapSiteUrl = WapSiteUrl;

                            var html = template.render('cart-list', rData);
                            $("#cart-list-wp").html(html);
                            //删除购物车
                            $(".del").click(delCartList);
                            //购买数量，减
                            $(".minus-wp").click(minusBuyNum);
                            //购买数量加
                            $(".add-wp").click(addBuyNum);
                            //去结算
                            $(".pay_right").click(goSettlement);
                            $(".buynum").blur(buyNumer);

                            /* zz@newland 添加开始   **/
                            /* 时间：2016/03/03        **/
                            //单个商品结算按钮
                            $(".done").click(go_alone_Settlement);
                            /* zz@newland 添加结束   **/


                            /* lyq@newland 添加开始   **/
                            /* 时间：2015/06/01        **/
                            /* 功能ID：SHOP015         **/
                            // 店铺选框点击事件
                            $(".store_select").click(store_select_change);
                            // 商品选框点击事件
                            $(".cart_select").click(cart_select_change);
                            /* lyq@newland 添加结束   **/
                        } else {
                            alert(result.datas.error);
                        }

                        /* lyq@newland 添加开始 **/
                        /* 时间：2015/06/10      **/
                        /* wap端loading画面      **/
                        // 隐藏loading画面
                        $("#loading_page").hide();
                        /* lyq@newland 添加结束 **/
                    }
                }
            });
        }
        initCartList();
        //删除购物车
        function delCartList() {
            var cart_id = $(this).attr("cart_id");
            if (!cart_id) {
                return false;
            }
            $.ajax({
                url: ApiUrl + "/index.php?act=member_cart&op=cart_del",
                type: "post",
                data: {key: key, cart_id: cart_id},
                dataType: "json",
                success: function(res) {
                    if (checklogin(res.login)) {
                        if (!res.datas.error && res.datas == "1") {
                            initCartList();
                        } else {
                            alert(res.datas.error);
                        }
                    }
                }
            });
        }
        //购买数量减
        function minusBuyNum() {
            var self = this;
            editQuantity(self, "minus");
        }
        //购买数量加
        function addBuyNum() {
            var self = this;
            editQuantity(self, "add");
        }
        //购买数量增或减，请求获取新的价格
        function editQuantity(self, type) {
            var sPrents = $(self).parents(".cart-litemw-cnt")
            var cart_id = sPrents.attr("cart_id");
            var numInput = sPrents.find(".buy-num");
            var buynum = parseInt(numInput.val());
            var quantity = 1;
            if (type == "add") {
                quantity = parseInt(buynum + 1);
                // 
            } else {
                if (buynum > 1) {
                    quantity = parseInt(buynum - 1);
                } else {
                    $.sDialog({
                        skin: "red",
                        content: '购买数目必须大于1',
                        okBtn: false,
                        cancelBtn: false
                    });
                    return;
                }
            }
            $.ajax({
                url: ApiUrl + "/index.php?act=member_cart&op=cart_edit_quantity",
                type: "post",
                data: {key: key, cart_id: cart_id, quantity: quantity},
                dataType: "json",
                success: function(res) {
                    if (checklogin(res.login)) {
                        if (!res.datas.error) {
                            numInput.val(quantity);
                            sPrents.find(".goods-total-price").html(res.datas.total_price);

                            /* lyq@newland 修改开始   **/
                            /* 时间：2015/06/01        **/
                            /* 功能ID：SHOP015         **/
                            // 重新计算总价
                            calculate_total_price();
                            // 更新pre_value为input元素现在的值
                            numInput.attr('pre_value', numInput.val());
                            /* lyq@newland 修改结束   **/
                        } else {
                            $.sDialog({
                                skin: "red",
                                content: res.datas.error,
                                okBtn: false,
                                cancelBtn: false
                            });
                        }
                    }
                }
            });
        }
        //去结算
        function goSettlement() {
            //购物车ID
            var cartIdArr = [];

            var err_msg = '';
            /* lyq@newland 修改开始   **/
            /* 时间：2015/06/01        **/
            /* 功能ID：SHOP015         **/
            $(".cart_checked").each(function() {
                var cartId = $(this).attr("cart_id");
                var cartNum = parseInt($(".buy-num[cart_id='" + cartId + "']").val());
                /* lyq@newland 添加开始   **/
                /* 时间：2015/11/03        **/
                var p_limit = parseInt($(this).attr("purchase_limit"));
                var goods_id = $(this).attr("goods_id");
                var buyed_num = 0;
                $.ajax({
                    type: 'post',
                    url: ApiUrl + '/index.php?act=member_buy&op=get_goods_buyed_count',
                    data: {key: key, goods_id: goods_id},
                    async: false,
                    dataType: 'json',
                    success: function(result) {
                        buyed_num = result.datas.buyed_num;
                    }
                });
                if (p_limit > 0 && buyed_num + cartNum > p_limit) {
                    err_msg = '商品【' + $(this).attr('goods_name') + '】超出限购数量！';
                    return false;
                }
                /* lyq@newland 添加结束   **/
                var cartIdNum = cartId + "|" + cartNum;
                cartIdArr.push(cartIdNum);
            });
            if (err_msg != '') {
                $.sDialog({
                    skin: "red",
                    content: err_msg,
                    okBtn: false,
                    cancelBtn: false
                });
                $(".s-dialog-wrapper").css({"max-width": "60%", "text-align": "center"});
                return;
            }

            var cart_id = cartIdArr.toString();
            if (cart_id == '') {
                $.sDialog({
                    skin: "red",
                    content: "请选择需要结算的商品！",
                    okBtn: false,
                    cancelBtn: false
                });
                return;
            }
            /* lyq@newland 修改结束   **/


            window.location.href = WapSiteUrl + "/tmpl/order/buy_step1.html?ifcart=1&cart_id=" + cart_id;
        }

        /* zz@newland 添加开始   **/
        /* 时间：2016/03/03        **/
        //单个商品结算
        function go_alone_Settlement() {
            var cartId = $(this).attr("cart_id");
            var cartNum = parseInt($(".buy-num[cart_id='" + cartId + "']").val());
            window.location.href = WapSiteUrl + "/tmpl/order/buy_step1.html?ifcart=1&cart_id=" + cartId + "|" + cartNum;
        }
        /* lyq@newland 添加结束   **/
        //验证
        $.sValid.init({
            rules: {
                buynum: "digits"
            },
            messages: {
                buynum: "请输入正确的数字"
            },
            callback: function(eId, eMsg, eRules) {
                if (eId.length > 0) {
                    var errorHtml = "";
                    $.map(eMsg, function(idx, item) {
                        errorHtml += "<p>" + idx + "</p>";
                    });
                    $.sDialog({
                        skin: "red",
                        content: errorHtml,
                        okBtn: false,
                        cancelBtn: false
                    });
                }
            }
        });

        function buyNumer() {
            /* lyq@newland 修改开始   **/
            /* 时间：2015/06/01        **/
            /* 功能ID：SHOP015         **/
            // 获取失去焦点的input对象
            var input_obj = $(this);
            // 购物车ID
            var cart_id = input_obj.attr('cart_id');
            // 商品数量
            var quantity = parseInt(input_obj.val());
            // 验证商品数量的输入类型
            if ($.sValid()) {
                // 验证商品数量是否大于商品库存
                $.ajax({
                    url: ApiUrl + "/index.php?act=member_cart&op=cart_edit_quantity",
                    type: "post",
                    data: {key: key, cart_id: cart_id, quantity: quantity},
                    dataType: "json",
                    success: function(res) {
                        if (checklogin(res.login)) {
                            if (!res.datas.error) {
                                // 库存足够，更新成功
                                // 更新商品总价
                                $(".goods-total-price[cart_id='" + cart_id + "']").html(res.datas.total_price);
                                // 重新计算总价
                                calculate_total_price();
                                // 更新pre_value为input元素现在的值
                                input_obj.attr('pre_value', input_obj.val());
                            } else {
                                // 库存不足，更新失败
                                $.sDialog({
                                    skin: "red",
                                    content: res.datas.error,
                                    okBtn: false,
                                    cancelBtn: false
                                });
                                // 更新input元素值为之前的值（pre_value）
                                input_obj.val(input_obj.attr('pre_value'));
                            }
                        }
                    }
                });
            }
            /* lyq@newland 修改结束   **/
        }


        /* lyq@newland 添加开始   **/
        /* 时间：2015/06/01        **/
        /* 功能ID：SHOP015         **/

        /**
         * 店铺checkbox点击事件
         */
        function store_select_change() {
            if ($(this).attr("class").indexOf("store_checked") == -1) {
                // 选中店铺
                $(this).addClass('store_checked');
                /* zz@newland 添加开始 **/
                /* 时间：2016/03/3     **/
//选定按钮点击颜色改变
                $(this).addClass('blue');
                $(".cart_select").addClass('blue');

                /* zz@newland 添加结束 **/
                // 选中该店铺的所有商品
                $(".cart_select[store_id='" + $(this).attr('store_id') + "']").addClass('cart_checked');
            } else {
                // 取消选中店铺
                $(this).removeClass('store_checked');
                /* zz@newland 添加开始 **/
                /* 时间：2016/03/3     **/
//选定按钮点击颜色改变
                $(this).removeClass('blue');
                $(".cart_select").removeClass('blue');
                /* zz@newland 添加结束 **/
                // 取消选中该店铺的所有商品
                $(".cart_select[store_id='" + $(this).attr('store_id') + "']").removeClass('cart_checked');
            }
            // 重新计算总价
            calculate_total_price();
        }

        /**
         * 商品checkbox点击事件
         */
        function cart_select_change() {
            if ($(this).attr("class").indexOf("cart_checked") == -1) {
                // 选中商品
                /* zz@newland 添加开始 **/
                /* 时间：2016/03/3     **/
//选定按钮点击颜色改变
                $(this).addClass('cart_checked');
                $(this).addClass('blue');
                /* zz@newland 添加结束 **/
                // 选中该商品所属店铺
                $(".store_select[store_id='" + $(this).attr('store_id') + "']").addClass('store_checked');

                $(".store_select").addClass('blue');
                // 循环本店铺商品
                $(".cart_select[store_id='" + $(this).attr('store_id') + "']").each(function() {
                    // 如有未选中的商品
                    if ($(this).attr('class').indexOf("cart_checked") == -1) {
                        // 取消选中店铺
                        $(".store_select[store_id='" + $(this).attr('store_id') + "']").removeClass('store_checked');
                        $(".store_select").removeClass('blue');
                    }
                });
            } else {
                // 取消选中商品
                /* zz@newland 添加开始 **/
                /* 时间：2016/03/3     **/
//选定按钮点击颜色改变
                $(this).removeClass('cart_checked');
                $(this).removeClass('blue');

                $(".store_select").removeClass('blue');
                /* zz@newland 添加结束 **/
                // 取消选中该店铺
                $(".store_select[store_id='" + $(this).attr('store_id') + "']").removeClass('store_checked');
            }
            // 重新计算总价
            calculate_total_price();
        }

        /**
         * 计算总价
         */
        function calculate_total_price() {
            // 声明总价 0
            var totalPrice = parseFloat("0.00");
            // 循环选中商品
            $(".cart_checked").each(function() {
                // 取选中商品的商品总价
                var goodsTotal = $(".goods-total-price[cart_id='" + $(this).attr('cart_id') + "']");
                // 累加到总价
                totalPrice += parseFloat($(goodsTotal).html());
            });
            // 更新总价显示
            $(".total_price").html("合计：" + totalPrice.toFixed(2) + "元");
        }

        /* lyq@newland 添加结束   **/
    }
});