
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null)
        return unescape(r[2]);
    return null;
}

function addcookie(name, value, expireHours) {
    var cookieString = name + "=" + escape(value) + "; path=/";
    //判断是否设置过期时间
    if (expireHours > 0) {
        var date = new Date();
        date.setTime(date.getTime + expireHours * 3600 * 1000);
        cookieString = cookieString + "; expire=" + date.toGMTString();
    }
    document.cookie = cookieString;
}

function getcookie(name) {
    var strcookie = document.cookie;
    var arrcookie = strcookie.split("; ");
    for (var i = 0; i < arrcookie.length; i++) {
        var arr = arrcookie[i].split("=");
        if (arr[0] == name)
            return arr[1];
    }
    return "";
}

function delCookie(name) {//删除cookie
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getcookie(name);
    if (cval != null)
        document.cookie = name + "=" + cval + "; path=/;expires=" + exp.toGMTString();
}

function checklogin(state) {
    if (state == 0) {
        location.href = WapSiteUrl + '/tmpl/member/login.html';
        return false;
    } else {
        return true;
    }
}

function contains(arr, str) {
    var i = arr.length;
    while (i--) {
        if (arr[i] === str) {
            return true;
        }
    }
    return false;
}

function buildUrl(type, data) {
    switch (type) {
        case 'keyword':
            return WapSiteUrl + '/tmpl/product_list.html?keyword=' + encodeURIComponent(data);
        case 'special':
            return WapSiteUrl + '/special.html?special_id=' + data;
        case 'goods':
            return WapSiteUrl + '/tmpl/product_detail.html?goods_id=' + data;
        case 'url':
            return data;
    }
    return WapSiteUrl;
}
//bottom nav 33 hao-v3 by 33h ao.com Qq 1244 986 40
$(function() {
    /* lyq@newland 添加开始 **/
    /* 时间：2015/06/10      **/
    /* wap端loading画面      **/
    // loading画面头部内容
    var header_html = '';
    if (document.title === '首页') {    // 加载首页时显示内容
        /* lyq@newland 删除开始 **/
        /* 时间：2015/07/13      **/
//        header_html +=
//                '<header class="main">'
//                + '    <div class="header-wrap" style="/*position: fixed;width:100%;top:0px;*/">'
//                + '        <div class="htsearch-wrap with-home-logo">'
//                + '            <input type="text" class="htsearch-input clr-999" value="" placeholder="搜索商品" readonly="readonly">'
//                + '            <a href="javascript:void(0);" class="search-btn" onclick="return false;"></a>'
//                + '        </div>'
//                + '    </div>'
//                + '</header>';
        /* lyq@newland 删除结束 **/
    } else if (document.title === '店铺详情') {    // 加载店铺详情页时显示内容
        header_html +=
                '    <div class="header-wrap" style="position: fixed;width:100%;top:0px;">'
                + '        <div class="htsearch-wrap">'
                + '            <input type="text" class="htsearch-input clr-999" value="" placeholder="搜索全站商品" readonly="readonly">'
                + '            <a href="javascript:void(0);" class="search-btn" onclick="return false;"></a>'
                + '        </div>'
                + '    </div>';
    } else {    // 加载其他页时显示内容
        header_html +=
                '    <div class="header-wrap" style="position: fixed;width:100%;top:0px;">'
                + '        <h2>' + document.title + '</h2>'
                + '    </div>';
    }

    // loading画面内容
    var html = ''
            + '<div id="loading_page">'
            + header_html
            + '    <div class="loading">'
            + '        <div class="spinner">'
            + '            <div class="rect1"></div>'
            + '            <div class="rect2"></div>'
            + '            <div class="rect3"></div>'
            + '            <div class="rect4"></div>'
            + '            <div class="rect5"></div>'
            + '        </div>'
            + '    </div>'
            + '    <div class="bottom-mask" style="position: fixed;width:100%;bottom:0px;">'
            + '        <div style=" height:40px;">'
            + '            <div id="nav-tab" style="bottom:-40px;">'
            + '                <div id="nav-tab-btn">'
            + '                    <i class="fa fa-chevron-down"></i>'
            + '                </div>'
            + '                <div class="clearfix tab-line nav">'
            + '                    <div class="tab-line-item" style="width:22%;">'
            + '                        <a href="javascript:void(0)">'
            /* zz@newland 修改开始 **/
            /* 时间：2016/03/3     **/
            //在每一个文字中添加style font-size:2.0em; 修改文字位置
            + '                            <i class="fa fa-home" style="font-size:2.0em;"></i><br>首页'
            + '                        </a>'
            + '                    </div>'
            + '                    <div class="tab-line-item" style="width:22%;">'
            + '                        <a href="javascript:void(0)">'
            + '                            <i class="fa fa-th-list" style="font-size:2.0em;"></i><br>分类'
            + '                        </a>'
            + '                    </div>'
            + '                    <div class="tab-line-item get_down" style="width:12%;line-height:40px;padding-top:5px;"><br>'
            + '                    </div>'
            + '                    <div class="tab-line-item" style="width:22%;position: relative;">'
            + '                        <a href="javascript:void(0)">'
            + '                            <i class="fa fa-shopping-cart" style="font-size:2.0em;"></i><br>购物车'
            + '                        </a>'
            + '                    </div>'
            /* zz@newland 修改结束 **/
            + '                    <div class="tab-line-item" style="width:22%;">'
            + '                        <a href="javascript:void(0)">'
            + '                            <i class="fa fa-user" style="font-size:2.0em;"></i><br>会员中心'
            + '                        </a>'
            + '                    </div>'
            + '                </div>'
            + '            </div>'
            + '        </div>'
            + '    </div>'
            + '</div>';
    // 将loading画面内容加入body内容中的最前端
    $("body").prepend(html);
    /* lyq@newland 添加结束 **/


    setTimeout(function() {
        if ($("#content .container").height() < $(window).height()) {
            $("#content .container").css("min-height", $(window).height());
        }
    }, 300);
    $("#bottom .nav .get_down").click(function() {
        $("#bottom .nav").animate({"bottom": "-50px"});
        $("#nav-tab").animate({"bottom": "0px"});
    });
    $("#nav-tab-btn").click(function() {
        $("#bottom .nav").animate({"bottom": "0px"});
        $("#nav-tab").animate({"bottom": "-40px"});
    });
    setTimeout(function() {
        $("#bottom .nav .get_down").click();
    }, 500);
    $("#scrollUp").click(function(t) {
        $("html, body").scrollTop(300);
        $("html, body").animate({
            scrollTop: 0
        }, 300);
        t.preventDefault()
    });

    // 获取当前页面url
    var url = document.location.href;
    // 所访问的页面不是购买页面时
    //if (url.indexOf('/order/') < 0) {
    if (false) {
        /* lyq@newland 添加开始  **/
        /* 时间：2015/06/26       **/
        // 请求微信分享接口，更改分享事件相关信息
        $.ajax({
            type: "POST",
            url: SiteUrl + "/wx/wx_share.php",
            dataType: "json",
            success: function(result) {
                // 通过config接口注入权限验证配置
                wx.config({
                    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                    appId: result.wx_config.appid, // 必填，公众号的唯一标识
                    timestamp: result.wx_config.timestamp, // 必填，生成签名的时间戳
                    nonceStr: result.wx_config.noncestr, // 必填，生成签名的随机串
                    signature: result.wx_config.signature, // 必填，签名，见附录1
                    jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                });
                // 通过ready接口处理成功验证
                wx.ready(function() {
                    /**
                     * 以下事件的success方法中参数的通用属性errMsg
                     *   1.调用成功时："xxx:ok" ，其中xxx为调用的接口名
                     *   2.用户取消时："xxx:cancel"，其中xxx为调用的接口名
                     *   3.调用失败时：其值为具体错误信息
                     */
                    // 分享到朋友圈
                    wx.onMenuShareTimeline({
                        title: result.share_param.title, // 分享标题
                        link: result.share_param.link, // 分享链接
                        imgUrl: result.share_param.imgUrl, // 分享图标
                        success: function(res) {
                            // 分享成功后的操作
                        }
                    });
                    // 分享给朋友
                    wx.onMenuShareAppMessage({
                        title: result.share_param.title, // 分享标题
                        desc: result.share_param.desc, // 分享描述
                        link: result.share_param.link, // 分享链接
                        imgUrl: result.share_param.imgUrl, // 分享图标
                        success: function(res) {
                            // 分享成功后的操作
                        }
                    });
                });
                // 通过error接口处理失败验证
                wx.error(function(res) {
                    //alert(res.errMsg);
                    $.ajax({
                        type: "POST",
                        url: SiteUrl + "/wx/refresh_token.php",
                        dataType: "json",
                        success: function(result) {
                            window.location.reload(true);
                        }
                    });
                });
            }
        });
        /* lyq@newland 添加结束  **/
    }
});