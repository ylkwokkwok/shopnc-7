
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

function contains(arr, str) {
    var i = arr.length;
    while (i--) {
        if (arr[i] === str) {
            return true;
        }
    }
    return false;
}

$(function() {
    // loading画面内容
    var html = ''
            + '<div id="loading_page">'
            + '    <div class="loading">'
            + '        <div class="spinner">'
            + '            <div class="rect1"></div>'
            + '            <div class="rect2"></div>'
            + '            <div class="rect3"></div>'
            + '            <div class="rect4"></div>'
            + '            <div class="rect5"></div>'
            + '        </div>'
            + '    </div>'
            + '</div>';
    // 将loading画面内容加入body内容中的最前端
    $("body").prepend(html);

    // 获取当前页面url
    var url = document.location.href;
    // 所访问的页面不是购买页面时
    //if (url.indexOf('/milk/') < 0) {
    if (false) {
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
                        success: function (res) {
                            // 分享成功后的操作
                        }
                    });
                    // 分享给朋友
                    wx.onMenuShareAppMessage({
                        title: result.share_param.title, // 分享标题
                        desc: result.share_param.desc, // 分享描述
                        link: result.share_param.link, // 分享链接
                        imgUrl: result.share_param.imgUrl, // 分享图标
                        success: function (res) {
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
    }
});
    
/**
 * 绑定店铺点击事件
 * @param {type} search url中的参数部分
 * @param {type} key wap登录验证key
 */
function bind_address_click(search, key) {
    $(".self_receive_spot").click(function(){
        var self_receive_spot_cd = $(this).attr('self_receive_spot_cd');
        // 请求微信分享接口，更改分享事件相关信息
        $.ajax({
            type: "POST",
            url: ApiUrl + "/index.php?act=milk_store&op=select_store",
            data: {"key":key,"self_receive_spot_cd":self_receive_spot_cd},
            dataType: "json",
            success: function() {
                // url中无参数
                if (search === '') {
                    // 拼接自取点编号并跳转到订奶页面
                    location.href = "order_milk_card.html?self_receive_spot_cd=" + self_receive_spot_cd;
                }
                // url中有参数
                else {
                    // 拼接url参数和自取点编号并跳转到填写核对购物信息页面
                    location.href = WapSiteUrl+"/tmpl/order/buy_step1.html"+search+"&self_receive_spot_cd=" + self_receive_spot_cd;
                }
            }
        });
    });
}
    
/**
 * 提示信息
 * @param {type} msg 信息内容
 * @param {type} skin 显示外观
 * @returns {undefined}
 */
function show_message(msg, skin) {
    $.sDialog({
        skin: skin,
        content: msg,
        okBtn: false,
        cancelBtn: false
    });
    $(".s-dialog-wrapper").css({"max-width":"50%","text-align":"center"});
}

/* lyq@newland 添加开始 **/
/* 时间：2015/09/21     **/
/**
 * 检查快速订奶入口
 * @param {type} data ajax响应数据
 */
function check_quick_entr(data) {
    // 如果快速订奶入口已关闭
    if (data.quick_closed) {
        // 显示提示消息
        show_message('快速订奶入口已关闭，即将跳转到微商城...','red');
        // 2秒后跳转到微商城主页
        setTimeout(function(){
            location.href = WapSiteUrl;
        },2000);
        return false;
    }
    return true;
}
/* lyq@newland 添加结束 **/