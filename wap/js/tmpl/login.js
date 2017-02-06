$(function() {
//    $("#loading_page").hide();
//    $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbf22b887fc929ff8&redirect_uri=http://shopnc.siburuxue.org/wx/index.php&response_type=code&scope=snsapi_userinfo&state=code#wechat_redirect";
//    location.href=$url;
//    return;
    var memberHtml = '<a class="btn mr5" href="' + WapSiteUrl + '/tmpl/member/member.html?act=member">个人中心</a><a class="btn mr5" href="' + WapSiteUrl + '//tmpl/member/register.html">注册</a><a class="btn mr5" href="#">微信登录</a>';
    var act = GetQueryString("act");
    if (act && act == "member") {
        memberHtml = '<a class="btn mr5" id="logoutbtn" href="javascript:void(0);">注销账号</a>';
    }
    var tmpl = '<div class="footer">'
            + '<div class="footer-top">'
            + '<div class="footer-tleft">' + memberHtml + '</div>'
            + '<a href="javascript:void(0);"class="gotop">'
            + '<span class="gotop-icon"></span>'
            + '<p>回顶部</p>'
            + '</a>'
            + '</div>'
            + '<div class="footer-content">'
            + '<p class="link">'
            + '<a href="javascript:void(0);" class="standard">手机版首页</a>'
            + '<a href="javascript:void(0);">下载Android客户端</a>'
            + '</p>'
            /*+'<p class="copyright">'
             +'版权所有 2014-2015 © www.abc.com'
             +'</p>'*/
            + '</div>'
            + '</div>';
    var render = template.compile(tmpl);
    var html = render();
    $("#footer").html(html);
    //回到顶部
    $(".gotop").click(function() {
        /* lyq@newland 修改开始 **/
        /* 时间：2015/06/10      **/
        // 页面置顶
        document.body.scrollTop = 0;
        /* lyq@newland 修改结束 **/
    });
    var key = getcookie('key');
    $('#logoutbtn').click(function() {
        var username = getcookie('username');
        var key = getcookie('key');
        var client = 'wap';
        $.ajax({
            type: 'get',
            url: ApiUrl + '/index.php?act=logout',
            data: {username: username, key: key, client: client},
            success: function(result) {
                if (result) {
                    delCookie('username');
                    delCookie('key');
                    location.href = WapSiteUrl + '/tmpl/member/login.html';
                }
            }
        });
    });

    var referurl = document.referrer;//上级网址
    $("input[name=referurl]").val(referurl);
    $.sValid.init({
        rules: {
            username: "required",
            userpwd: "required"
        },
        messages: {
            username: "用户名必须填写！",
            userpwd: "密码必填!"
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
    $('#loginbtn').click(function() {//会员登陆
        var username = $('#username').val();
        var pwd = $('#userpwd').val();
        var client = 'wap';
        if ($.sValid()) {
            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=login",
                data: {username: username, password: pwd, client: client},
                dataType: 'json',
                success: function(result) {
                    if (!result.datas.error) {
                        if (typeof (result.datas.key) == 'undefined') {
                            return false;
                        } else {
                            addcookie('username', result.datas.username);
                            addcookie('key', result.datas.key);
                            location.href = referurl;
                        }
                        $(".error-tips").hide();
                    } else {
                        $(".error-tips").html(result.datas.error).show();
                    }
                }
            });
        }
    });

    /* lyq@newland 添加开始 **/
    /* 时间：2015/06/10      **/
    /* wap端loading画面      **/
    // 隐藏loading画面
    $("#loading_page").hide();
    /* lyq@newland 添加结束 **/
});