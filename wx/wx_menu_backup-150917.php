<?php

require_once("wx_core.php");

// 定义框架路径
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
// 引用框架核心类
if (!@include(dirname(dirname(__FILE__)).'/global.php')) exit('global.php isn\'t exists!');
if (!@include(BASE_CORE_PATH.'/nl_wx_shop.php')) exit('nl_wx_shop.php isn\'t exists!');
// 执行框架
Base::run(FALSE);

$access_token = Model()->table('setting')->where(array('name' => 'wx_access_token'))->select();

$get_current_menu = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token='.$access_token[0]['value'];

$result_str = getHttpResponseGET($get_current_menu);
echo $result_str;
exit;

$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token[0]['value'];

$para = '{
        "button": [{
                "type": "view",
                "name": "微商城",
                "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbf22b887fc929ff8&redirect_uri=http://fresh.cenler-shop.com/wx/index.php&response_type=code&scope=snsapi_userinfo&state=code#wechat_redirect"
            }, {
                "name": "新鲜送",
                "sub_button": 
                        [{
                            "type": "view",
                            "name": "心乐鲜牛奶",
                            "url": "'.BASE_SITE_URL.'/wx/index.php?gc_id=1001"
                        }, {
                            "type": "view",
                            "name": "心乐酸牛奶",
                            "url": "'.BASE_SITE_URL.'/wx/index.php?gc_id=1002"
                        }, {
                            "type": "view",
                            "name": "巴氏酸牛奶",
                            "url": "'.BASE_SITE_URL.'/wx/index.php?gc_id=1004"
                        }]
            }, {
                "type": "view",
                "name": "手机订奶",
                "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbf22b887fc929ff8&redirect_uri=http://fresh.cenler-shop.com/wx/milk_index.php&response_type=code&scope=snsapi_userinfo&state=nearby_store#wechat_redirect"
            }]
    }';

$result_str = getHttpResponsePOST($url, $para);

$result_arr = json_decode($result_str);
    
echo $result_arr->errmsg;
exit;