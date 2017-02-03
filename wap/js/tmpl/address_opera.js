$(function() {
    var key = getcookie('key');
    $.ajax({
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

    $("select[name=prov]").change(function() {
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

    $("select[name=city]").change(function() {
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

    /* lyq@newland 修改开始   **/
    /* 时间：2015/06/16        **/
    /* 修改验证规则            **/
    $.sValid.init({
        rules: {
            true_name: {
                required: true,
                length_range: {
                    max: 15,
                    min: 2
                }
            } ,
            mob_phone: {
                required: true,
                digits_length: 11
            } ,
            tel_phone: {
                digits_length_range: {
                    max: 12,
                    min: 7
                }
            } ,
            prov_select: {
                required: true
            } ,
            city_select: {
                required: true
            } ,
            region_select: {
                required: true
            } ,
            address: {
                required: true,
                maxlength: 50
            }
        },
        messages: {
            true_name: {
                required: "请填写姓名！",
                length_range: "收货人姓名2-15个字符限制！"
            } ,
            mob_phone: {
                required: "请填写手机号码！",
                digits_length: "手机号码11位数字限制！"
            } ,
            tel_phone: {
                digits_length_range: "电话号码7-12位数字限制！"
            } ,
            prov_select: {
                required: "请选择省份！"
            } ,
            city_select: {
                required: "请选择城市！"
            } ,
            region_select: {
                required: "请选择区县！"
            } ,
            address: {
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

    $('.add_address').click(function() {
        if ($.sValid()) {
//            var index = $('select[name=prov]')[0].selectedIndex;
//            var aa = $('select[name=prov]')[0].options[index].innerHTML;


            var true_name = $('input[name=true_name]').val();
            var mob_phone = $('input[name=mob_phone]').val();
            var tel_phone = $('input[name=tel_phone]').val();
            var city_id = $('input[name=city]').val();//$('select[name=city]').val();
            var area_id = $('select[name=region]').val();
            var address = $('input[name=address]').val();

//            var prov_index = $('select[name=prov]')[0].selectedIndex;
//            var city_index = $('select[name=city]')[0].selectedIndex;
            var region_index = $('select[name=region]')[0].selectedIndex;
            var area_info = '辽宁省大连市' + $('select[name=region]')[0].options[region_index].innerHTML;

            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=member_address&op=address_add",
                data: {key: key, true_name: true_name, mob_phone: mob_phone, tel_phone: tel_phone, city_id: city_id, area_id: area_id, address: address, area_info: area_info},
                dataType: 'json',
                success: function(result) {
                    if (result) {
                        location.href = WapSiteUrl + '/tmpl/member/address_list.html';
                    } else {
                        location.href = WapSiteUrl;
                    }
                }
            });
        }
    });

});