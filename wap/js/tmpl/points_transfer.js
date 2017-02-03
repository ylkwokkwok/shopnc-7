/* lyq@newland 添加开始   **/
/* 时间：2015/05/29        **/
/* 功能ID：SHOP013         **/
/* 积分转赠                **/

/* lyq@newland 修改   **/
/* 时间：2015/06/05   **/
/* 功能ID：SHOP013    **/
// 改变被赠送会员的查询/修改条件 会员名称 -> 会员幸运号
// 增加会员幸运号 数字check

$(function() {
    var key = getcookie('key');
    // 判断是否已登录
    if (key == '') {
        location.href = 'login.html';
    }

    // 登录会员名称
    var owner_luck_num;
    // 现有积分
    var own_points;
    // 获取用户数据
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_index",
        data: {key: key},
        dataType: 'json',
        success: function(result) {
            // 检查是否登录
            checklogin(result.login);
            // 登录会员幸运号
            owner_luck_num = result.datas.member_info.luck_num;
            /* lyq@newland 修改开始   **/
            /* 时间：2015/07/08       **/
            // 会员积分 ->> 推广积分
            own_points = result.datas.member_info.extend_points;
            /* lyq@newland 修改结束   **/
            
            // 将现有积分数显示到页面
            $("#own_points").html(own_points);

            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/10      **/
            /* wap端loading画面      **/
            // 显示页面内容
            $(".address-opera").show();
            // 隐藏loading画面
            $("#loading_page").hide();
            /* lyq@newland 添加结束 **/
        }
    });

    /**
     * 验证表单信息
     * @returns {Boolean} 验证成功/失败 true/false
     */
    function form_check() {
        // 验证标志
        var err_flg = true;
        // 错误信息
        var errorHtml = "";
        // 被赠送会员幸运号
        var receiver_luck_num = $.trim($("#receiver_luck_num").val());
        // 赠送积分数
        var points = $.trim($("#points").val());
        // 验证 被赠送会员的幸运号
        errorHtml += name_check(receiver_luck_num);
        // 验证 赠送积分数
        errorHtml += points_check(points);
        // 是否有错误
        if (errorHtml != '') {
            // 有错误
            // 验证标志 失败
            err_flg = false;
            // 显示错误信息
            $(".error-tips").html(errorHtml).show();
        } else {
            // 无错误
            // 清空并隐藏错误信息
            $(".error-tips").html("").hide();
        }
        // 返回验证标志
        return err_flg;
    }

    /**
     * 验证 被赠送会员的幸运号
     * @param {type} receiver_luck_num 被赠送会员的幸运号
     * @returns {String} 错误信息
     */
    function name_check(receiver_luck_num) {
        // 验证被赠送会员的幸运号 不为空
        if (receiver_luck_num == '') {
            // 返回错误信息
            return "<p>请填写幸运号！</p>";
        }
        // 验证被赠送会员的幸运号 必须正整数
        if (!/^[1-9]\d*$/.test(Number(receiver_luck_num))) {
            // 返回错误信息
            return "<p>幸运号必须为正整数！</p>";
        }
        // 验证被赠送会员的幸运号 不能超过11个数字
        if (receiver_luck_num.length > 11) {
            // 返回错误信息
            return "<p>幸运号不能超过11个数字！</p>";
        }
        // 验证被赠送会员的幸运号 不能送给自己
        if (receiver_luck_num == owner_luck_num) {
            // 返回错误信息
            return "<p>不能赠送给自己！</p>";
        }
        // 没有错误，返回空字符串
        return "";
    }

    /**
     * 验证 赠送积分数
     * @param {type} points 赠送积分数
     * @returns {String} 错误信息
     */
    function points_check(points) {
        // 现有积分数
        var max_points = parseInt(own_points);
        // 验证赠送积分数 不为空
        if (points == '') {
            // 返回错误信息
            return "<p>请填写赠送金米数量！</p>";
        }
        // 验证赠送积分数 必须为正整数
        if (!/^[1-9]\d*$/.test(Number(points))) {
            // 返回错误信息
            return "<p>赠送金米数量必须正整数！</p>";
        }
        // 验证赠送积分数 必须在 1～最大积分数 范围内
        if (parseInt(points) < 1 || parseInt(points) > max_points) {
            // 返回错误信息
            return "<p>赠送金米数量必须在 1～" + max_points + " 范围内！</p>";
        }
        // 没有错误，返回空字符串
        return "";
    }

    /**
     * 转赠 按钮点击事件
     */
    $('.send_points').click(function() {
        // 验证表单数据
        if (form_check()) {
            // 验证通过
            // 被赠送会员的名称
            var receiver_luck_num = $.trim($("#receiver_luck_num").val());
            // 赠送积分数
            var points = $.trim($("#points").val());
            // ajax积分转赠
            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=member_points&op=send_points",
                data: {key: key, receiver_luck_num: receiver_luck_num, points: points},
                dataType: 'json',
                success: function(result) {
                    checklogin(result.login);
                    // 是否返回错误信息
                    if (!result.datas.error) {
                        $.sDialog({
                            content: '转赠成功！',
                            okBtn: false,
                            cancelBtnText: '返回',
                            cancelFn: function() {
                                // 跳转至个人中心
                                location.href = WapSiteUrl + '/tmpl/member/member.html';
                            }
                        });
                    } else {
                        $.sDialog({
                            content: result.datas.error,
                            okBtn: false,
                            cancelBtnText: '返回',
                            cancelFn: function() {
                                location.reload(true);
                            }
                        });
                    }
                }
            });
        }
    });

});
/* lyq@newland 添加结束   **/