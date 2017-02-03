$(function() {
    var key = getcookie('key');
    if (key == '') {
        location.href = 'login.html';
    }
    var member_id = '';
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=member_index",
        data: {key: key},
        dataType: 'json',
        success: function(result) {
            checklogin(result.login);
            $('#user_name').val(result.datas.member_info.user_name);
            $('#true_name').val(result.datas.member_info.member_truename);
            $('#member_email').val(result.datas.member_info.member_email);
            $('#member_mobile').val(result.datas.member_info.member_mobile);
            $('#luck_num').html(result.datas.member_info.luck_num);
            member_id = result.datas.member_info.luck_num;
            var sex = result.datas.member_info.member_sex;
            if (sex == '男') {
                $('input[id=sex1]').attr("checked", 'checked');
            } else if (sex == '女') {
                $('input[id=sex2]').attr("checked", 'checked');
            }
            $('#avatar').attr("src", result.datas.member_info.avator);
            $("#loading_page").hide();
        }
    });

    $('input[name=sex]').click(function() {
        $('input[name=sex]').removeAttr("checked");
        $(this).attr("checked", 'checked');
    });



//更新个人信息
    $('.add_done').click(function() {
        if ($.sValid()) {
            var user_name = $('#user_name').val();
            var true_name = $('#true_name').val();
            var member_email = $('#member_email').val();
            var member_mobile = $('#member_mobile').val();
            var sex = $('input[checked=checked]').attr("value");

            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=member_index&op=member_edit",
                data: {
                    key: key,
                    member_id: member_id,
                    user_name: user_name,
                    true_name: true_name,
                    member_email: member_email,
                    member_mobile: member_mobile,
                    sex: sex
                },
                dataType: 'json',
                success: function(result) {
                    if (result) {
                        location.href = WapSiteUrl + '/tmpl/member/per_info.html';
                    } else {
                        location.href = WapSiteUrl;
                    }
                }
            });

        }
    });

    $.sValid.init({
        rules: {
            user_name: {
                required: true,
                length_range: {
                    max: 25,
                    min: 2
                }
            },
            true_name: {
                length_range: {
                    max: 10,
                    min: 0
                }
            },
            member_email: {
                email: true
            },
            member_mobile: {
                required: true,
                digits_length: 11
            }
        },
        messages: {
            user_name: {
                required: "请填写昵称！",
                length_range: "收货人姓名2-25个字符限制！"
            },
            true_name: {
                length_range: "姓名0-10字符限制！"
            },
            member_email: {
                email: "邮箱格式不正确!"
            },
            member_mobile: {
                required: "请填写手机号码！",
                digits_length: "手机号码11位数字限制！"
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





});