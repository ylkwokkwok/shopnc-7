<?php

require_once("wx_core.php");
include_once("SDKRuntimeException.php");
require_once("wx_config.php");

/**
 * 微信接口相关共同类
 * 
 * @author HaoLiang <haoliang@newlandsystem.com>
 * @version 1.0
 */
class wx_common {

    // 微信公众平台APPID
    private $appid;
    // 微信公众平台APP SECRET
    private $app_secret;

    /**
     * 构造方法
     */
    function __construct() {
        $this->appid = wx_config::APPID;
        $this->app_secret = wx_config::APPSECRET;
    }

    /**
     * 获取全局TOKEN
     * 
     * @return Array TOKEN及有效期
     */
    function get_token() {
        logResult('get_token Start');
        // 请求的URL
        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        // 公众号的唯一标识
        $param_arr['appid'] = $this->appid;
        // 公众号的appsecret
        $param_arr['secret'] = $this->app_secret;
        $param_arr['grant_type'] = 'client_credential';
        // 返回经过urlencode编码的参数=参数值字符串
        $param_str = createLinkstringUrlencode($param_arr);
        logResult('get_token:param_str=' . $param_str);
        // 发送GET请求
        $responseText = getHttpResponseGET($url . '?' . $param_str);
        // 对返回结果进行数组化
        $result_arr = json_decode($responseText);

        return $result_arr;
    }

    /**
     * 获取用户信息
     *   开发者可通过OpenID来获取用户基本信息。
     * @param type $access_token access_token
     * @param type $openid openid
     * 
     * @return Array 用户信息
     */
    function get_user_info($access_token, $openid) {
        logResult('get_user_info Start');
        // 请求URL
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info';
        $param_arr = array();
        // 调用接口凭证
        $param_arr['access_token'] = $access_token;
        // 普通用户的标识，对当前公众号唯一
        $param_arr['openid'] = $openid;
        // 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
        $param_arr['lang'] = 'zh_CN';
        // 返回经过urlencode编码的参数=参数值字符串
        $param_str = createLinkstringUrlencode($param_arr);
        logResult('param_str=' . $param_str);
        // 发送GET请求
        $responseText = getHttpResponseGET($url . '?' . $param_str);
        // 对返回结果进行数组化
        $user_info = json_decode($responseText);

        logResult('nickname=' . $user_info->nickname);
        logResult('headimgurl=' . $user_info->headimgurl);

        return $user_info;
    }
    

    /**
     * 获取Access Token
     *   通过oauth接口获取access token
     * 
     * @param String $code code
     * @return Array 
     * {
     *    "access_token":"ACCESS_TOKEN",
     *    "expires_in":7200,
     *    "refresh_token":"REFRESH_TOKEN",
     *    "openid":"OPENID",
     *    "scope":"SCOPE",
     *    "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     * }
     */
    function get_access_token($code) {
        // 请求的URL
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        // 公众号的唯一标识
        $param_arr['appid'] = $this->appid;
        // 公众号的appsecret
        $param_arr['secret'] = $this->app_secret;
        // 填写第一步获取的code参数
        $param_arr['code'] = $code;
        // 填写为authorization_code
        $param_arr['grant_type'] = 'authorization_code';
        // 返回经过urlencode编码的参数=参数值字符串
        $param_str = createLinkstringUrlencode($param_arr);
        logResult('param_str=' . $param_str);
        // 发送GET请求
        $responseText = getHttpResponseGET($url . '?' . $param_str);
        // 对返回结果进行数组化
        $result_arr = json_decode($responseText);

        logResult('OPENID=' . $result_arr->openid);
        logResult('ACCESS_TOKEN=' . $result_arr->access_token);

        return $result_arr;
    }

    /**
     * 获取用户信息
     *   通过oauth方式获取用户基本信息
     * 
     * @param type $access_token access_token
     * @param type $openid openid
     * @return Array
     * {
     *    "openid":" OPENID",
     *    "nickname": NICKNAME,
     *    "sex":"1",
     *    "province":"PROVINCE"
     *    "city":"CITY",
     *    "country":"COUNTRY",
     *    "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46", 
     *    "privilege":[
     * 	    "PRIVILEGE1"
     * 	    "PRIVILEGE2"
     *     ],
     *     "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     * }
     */
    function get_user_info_by_oauth($access_token, $openid) {
        logResult('get_user_info Start');
        // 构造请求URL和参数
        $url = 'https://api.weixin.qq.com/sns/userinfo';
        $param_arr = array();
        // 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
        $param_arr['access_token'] = $access_token;
        // 用户的唯一标识
        $param_arr['openid'] = $openid;
        // 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
        $param_arr['lang'] = 'zh_CN';
        // 返回经过urlencode编码的参数=参数值字符串
        $param_str = createLinkstringUrlencode($param_arr);
        logResult('param_str=' . $param_str);
        // 发送GET请求
        $responseText = getHttpResponseGET($url . '?' . $param_str);
        // 对返回结果进行数组化
        $user_info = json_decode($responseText);

        logResult('nickname=' . $user_info->nickname);
        logResult('headimgurl=' . $user_info->headimgurl);

        return $user_info;
    }

    /**
     * 添加用户信息
     *   用户点击商城首页菜单后，跳转到商城首页，并更新用户信息。
     *   初次登陆的时候，添加用户信息
     *   二次及以上登陆的时候，更新用户头像信息
     * 
     * @param type $wx_user_info
     */
    function update_user_info($wx_user_info) {
        logResult('update_user_info START');
        $model_member = Model()->table('member');
        // 获取微信用户的OPENID
        $open_id = $wx_user_info['member_wx_id'];
        // 获取指定OPENID的用户数量
        $user_count = $model_member->where(array('member_wx_id' => $open_id))->count();
        logResult('user_count=' . $user_count);
        // 用户第一次登陆的时候
        if ($user_count == 0) {
            // 会员注册时间
            $wx_user_info['member_time'] = TIMESTAMP;
            // 添加用户信息
            $member_id = $model_member->insert($wx_user_info);
            logResult('member_id=' . $member_id);
        } else {
            // 用户非第一次登陆的时候
            // 获取用户最新的头像地址
            $update_user_info = array();
            $update_user_info['member_avatar'] = $wx_user_info['member_avatar'];
            if ($wx_user_info['statis_vip'] == '0') {
                $update_user_info['statis_vip'] = $wx_user_info['statis_vip'];
            }
            // 更新头像地址
            $model_member->where(array('member_wx_id' => $open_id))->update($update_user_info);
        }
    }

    /* lyq@newland 添加开始 **/
    /* 时间：2015/06/26     **/
    /**
     * 添加用户信息(被邀请人)
     *   用户点击微信分享链接，跳转到商城首页，并更新用户信息。
     *   初次登陆的时候，添加用户信息，更新邀请人信息
     *   二次及以上登陆的时候，更新用户头像信息
     * 
     * @param type $wx_user_info
     * @param type $inviter_code 邀请人代码
     */
    function update_invited_user_info($wx_user_info, $inviter_code) {
        logResult('update_invited_user_info START');
        $model_member = Model()->table('member');
        // 获取微信用户的OPENID
        $open_id = $wx_user_info['member_wx_id'];
        // 获取指定OPENID的用户数量
        $user_count = $model_member->where(array('member_wx_id' => $open_id))->count();
        logResult('user_count=' . $user_count);
        // 用户第一次登陆的时候
        if ($user_count == 0) {
            if ($inviter_code != '') {
                // 获取邀请人信息
                $inviter_info = $model_member->where(array('member_wx_id' => array('like', '%' . $inviter_code)))->select();
                // 邀请人ID
                $wx_user_info['inviter_id'] = empty($inviter_info) ? '' : $inviter_info[0]['member_id'];
            } else {
                $wx_user_info['inviter_id'] = '';
            }
            // 会员注册时间
            $wx_user_info['member_time'] = TIMESTAMP;
            // 添加用户信息
            $member_id = $model_member->insert($wx_user_info);
            logResult('member_id=' . $member_id);
            if ($wx_user_info['inviter_id'] != '') {    // 邀请人ID不为空时
                /* lyq@newland 修改开始 **/
                /* 时间：2015/07/08    **/
                // 给邀请人增加推广积分(从商城运营配置中取得)
                Model()->execute('UPDATE `' . DBPRE . 'member`'
                                . ' SET extend_points = extend_points + '.C('points_invite')
                                . ' WHERE member_id = '.$wx_user_info['inviter_id']);
                
                logResult('member_id=' . $wx_user_info['inviter_id'] . ' add 10 extend_points');
                /* lyq@newland 修改结束 **/
            }
        } else {
            // 用户非第一次登陆的时候
            // 获取用户最新的头像地址
            $member_avatar = $wx_user_info['member_avatar'];
            // 更新头像地址
            $model_member->where(array('member_wx_id' => $open_id))->update(array('member_avatar' => $member_avatar));
        }
    }
    /* lyq@newland 添加结束 **/
    
    /**
     * 定时更新token
     * 
     * @param type $token
     */
    function refresh_token($token) {
        logResult('refresh_token START');
        $model_member = Model()->table('setting');
        logResult('refresh_token ->>>>>' . $token);
        $model_member->where(array('name' => 'wx_access_token'))->update(array('value' => $token));
    }
}

/**
 * 所有接口的基类
 */
class Common_util_pub {

    function __construct() {
        
    }

    function trimString($value) {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     * 	作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str.= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 	作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /**
     * 	作用：生成签名
     */
    public function getSign($Obj) {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . wx_config::KEY;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     * 	作用：array转xml
     */
    function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml.="<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml.="<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 	作用：将xml转为array
     */
    public function xmlToArray($xml) {
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        echo $array_data;
        return $array_data;
    }

    /**
     * 	作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml, $url, $second = 30) {
        //初始化curl        
        $ch = curl_init();
        echo $xml;
        echo $url;
        //设置超时
        //curl_setopt($ch, CURLOP_TIMEOUT, 30);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //curl_close($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /**
     * 	作用：使用证书，以post方式提交xml到对应的接口url
     */
    function postXmlSSLCurl($xml, $url, $second = 30) {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, wx_config::SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, wx_config::SSLKEY_PATH);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /**
     * 	作用：打印数组
     */
    function printErr($wording = '', $err = '') {
        print_r('<pre>');
        echo $wording . "</br>";
        var_dump($err);
        print_r('</pre>');
    }

}

/**
 * 请求型接口的基类
 */
class Wxpay_client_pub extends Common_util_pub {

    var $parameters; //请求参数，类型为关联数组
    public $response; //微信返回的响应
    public $result; //返回参数，类型为关联数组
    var $url; //接口链接
    var $curl_timeout; //curl超时时间

    /**
     * 	作用：设置请求参数
     */

    function setParameter($parameter, $parameterValue) {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 	作用：设置标配的请求参数，生成签名，生成接口参数xml
     */
    function createXml() {
        $this->parameters["appid"] = wx_config::APPID; //公众账号ID
        $this->parameters["mch_id"] = wx_config::MCHID; //商户号
        $this->parameters["nonce_str"] = $this->createNoncestr(); //随机字符串
        $this->parameters["sign"] = $this->getSign($this->parameters); //签名
        return $this->arrayToXml($this->parameters);
    }

    /**
     * 	作用：post请求xml
     */
    function postXml() {
        $xml = $this->createXml();
        $this->response = $this->postXmlCurl($xml, $this->url, $this->curl_timeout);
        return $this->response;
    }

    /**
     * 	作用：使用证书post请求xml
     */
    function postXmlSSL() {
        $xml = $this->createXml();
        $this->response = $this->postXmlSSLCurl($xml, $this->url, $this->curl_timeout);
        return $this->response;
    }

    /**
     * 	作用：获取结果，默认不使用证书
     */
    function getResult() {
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }

}

/**
 * 统一支付接口类
 */
class UnifiedOrder_pub extends Wxpay_client_pub {

    function __construct() {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //设置curl超时时间
        $this->curl_timeout = wx_config::CURL_TIMEOUT;
    }

    /**
     * 生成接口参数xml
     */
    function createXml() {
        try {
             logResult("开始：","开始");
            //检测必填参数
            if ($this->parameters["out_trade_no"] == null) {
                logResult("out_trade_no："."1");
                throw new SDKRuntimeException("缺少统一支付接口必填参数out_trade_no！" . "<br>");
            } elseif ($this->parameters["body"] == null) {
                 logResult("body："."2");
                throw new SDKRuntimeException("缺少统一支付接口必填参数body！" . "<br>");
            } elseif ($this->parameters["total_fee"] == null) {
                logResult("total_fee："."3");
                throw new SDKRuntimeException("缺少统一支付接口必填参数total_fee！" . "<br>");
            } elseif ($this->parameters["notify_url"] == null) {
                logResult("notify_url："."4");
                throw new SDKRuntimeException("缺少统一支付接口必填参数notify_url！" . "<br>");
            } elseif ($this->parameters["trade_type"] == null) {
                 logResult("trade_type："."5");
                throw new SDKRuntimeException("缺少统一支付接口必填参数trade_type！" . "<br>");
            } elseif ($this->parameters["trade_type"] == "JSAPI" &&
                    $this->parameters["openid"] == NULL) {
                  logResult("openid："."6");
                throw new SDKRuntimeException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！" . "<br>");
            }
            $this->parameters["appid"] = wx_config::APPID; //公众账号ID
             logResult("公众账号ID：". wx_config::APPID);
            $this->parameters["mch_id"] = wx_config::MCHID; //商户号
             logResult("mch_id：".wx_config::MCHID);
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; //终端ip	
             logResult("spbill_create_ip：". $_SERVER['REMOTE_ADDR']);
            $this->parameters["nonce_str"] = $this->createNoncestr(); //随机字符串
             logResult("nonce_str：".$this->createNoncestr());
            $this->parameters["sign"] = $this->getSign($this->parameters); //签名
             logResult("sign：".$this->getSign($this->parameters));
             logResult("arrayToXml：".$this->arrayToXml($this->parameters));
            return $this->arrayToXml($this->parameters);
        } catch (SDKRuntimeException $e) {
            die($e->errorMessage());
        }
    }

    /**
     * 获取prepay_id
     */
    function getPrepayId() {
        var_dump($this->result); 
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        $prepay_id = $this->result["prepay_id"];
        return $prepay_id;
    }

}

/**
* JSAPI支付——H5网页端调起支付接口
*/
class JsApi_pub extends Common_util_pub
{
	var $openid;//用户的openid
	var $parameters;//jsapi参数，格式为json
	var $prepay_id;//使用统一支付接口得到的预支付id

	function __construct() 
	{
	}
	

	
	function getConfigParam() {
		$config_param = array();
		$config_param['noncestr'] = $this->createNoncestr(17);
                // 获取setting表中的jsapi_ticket
                $ticket = Model()->table('setting')->where(array('name' => 'wx_jsapi_ticket'))->find();
                $config_param['jsapi_ticket'] = $ticket['value'];
		$config_param['timestamp'] = time();
                /* lyq@newland 修改开始   **/
                /* 时间：2015/06/26       **/
                // 请求微信API的当前url
		$config_param['url'] = $_SERVER['HTTP_REFERER'];
                /* lyq@newland 修改结束   **/
		$config_param['signature'] = $this->getSignature($config_param);
		$config_param['appid'] = wx_config::APPID;
		return $config_param;
	}
	
	
	private function getSignature($param_arr) 
	{
		$js_config_pram = array();
		foreach ($param_arr as $k => $v) {
            $js_config_pram[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($js_config_pram);
        $str = $this->formatBizQueryParaMap($js_config_pram, false);

		return sha1($str);
	}
		
	function getTicket() 
	{
		require_once("wx_core.php");
		$token = Model()->table('setting')->where(array('name' => 'wx_access_token'))->select();
		
		// 请求的URL
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
        $param_arr = array();
		// 填写第一步获取的code参数
        $param_arr['access_token'] = $token[0]['value'];
        // 填写为authorization_code
        $param_arr['type'] = 'jsapi';
		// 返回经过urlencode编码的参数=参数值字符串
        $param_str = createLinkstringUrlencode($param_arr);
		logResult('param_str=' . $param_str);
        // 发送GET请求
        $responseText = getHttpResponseGET($url . '?' . $param_str);
        // 对返回结果进行数组化
        $result_arr = json_decode($responseText);

        return $result_arr->ticket;
	}
	
        /**
         * 更新setting表中的 jsapi_ticket
         * 
         * @param type $jsapi_ticket jsapi_ticket
         */
        function refresh_ticket($jsapi_ticket) {
            logResult('refresh_ticket START');
            logResult('refresh_ticket ->>>>>' . $jsapi_ticket);
            Model()->table('setting')
                   ->where(array('name' => 'wx_jsapi_ticket'))
                   ->update(array('value' => $jsapi_ticket));
        }

	/**
	 * 	作用：设置prepay_id
	 */
	function setPrepayId($prepayId)
	{
		$this->prepay_id = $prepayId;
	}

	/**
	 * 	作用：设置Openid
	 */
	function setOpenid($openid)
	{
		$this->openid = $openid;
	}

	/**
	 * 	作用：设置jsapi的参数
	 */
	public function getParameters()
	{
		$jsApiObj["appId"] = wx_config::APPID;
		$timeStamp = time();
	    $jsApiObj["timeStamp"] = "$timeStamp";
	    $jsApiObj["nonceStr"] = $this->createNoncestr();
		$jsApiObj["package"] = "prepay_id=$this->prepay_id";
	    $jsApiObj["signType"] = "MD5";
	    $jsApiObj["paySign"] = $this->getSign($jsApiObj);
		$this->parameters = $jsApiObj;
		
		return $this->parameters;
	}
}

/**
 * 响应型接口基类
 */
class Wxpay_server_pub extends Common_util_pub 
{
	public $data;//接收到的数据，类型为关联数组
	var $returnParameters;//返回参数，类型为关联数组
	
	/**
	 * 将微信的请求xml转换成关联数组，以方便数据处理
	 */
	function saveData($xml)
	{
		$this->data = $this->xmlToArray($xml);
	}
	
	function checkSign()
	{
		$tmpData = $this->data;
		unset($tmpData['sign']);
		$sign = $this->getSign($tmpData);//本地签名
		if ($this->data['sign'] == $sign) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 获取微信的请求数据
	 */
	function getData()
	{		
		return $this->data;
	}
	
	/**
	 * 设置返回微信的xml数据
	 */
	function setReturnParameter($parameter, $parameterValue)
	{
		$this->returnParameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		return $this->arrayToXml($this->returnParameters);
	}
	
	/**
	 * 将xml数据返回微信
	 */
	function returnXml()
	{
		$returnXml = $this->createXml();
		return $returnXml;
	}
}

/**
 * 通用通知接口
 */
class Notify_pub extends Wxpay_server_pub 
{

}

/**
 * 退款申请接口
 */
class Refund_pub extends Wxpay_client_pub
{
	
	function __construct() {
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		try
		{
			//检测必填参数
			if($this->parameters["out_trade_no"] == null && $this->parameters["transaction_id"] == null) {
				throw new SDKRuntimeException("退款申请接口中，out_trade_no、transaction_id至少填一个！"."<br>");
			}elseif($this->parameters["out_refund_no"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数out_refund_no！"."<br>");
			}elseif($this->parameters["total_fee"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数total_fee！"."<br>");
			}elseif($this->parameters["refund_fee"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数refund_fee！"."<br>");
			}elseif($this->parameters["op_user_id"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数op_user_id！"."<br>");
			}
		   	$this->parameters["appid"] = wx_config::APPID;//公众账号ID
		   	$this->parameters["mch_id"] = wx_config::MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	/**
	 * 	作用：获取结果，使用证书通信
	 */
	function getResult() 
	{		
		$this->postXmlSSL();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}
	
}

/**
 * 企业付款接口
 */
class Transfer_pub extends Wxpay_client_pub
{
    function __construct() {
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
	}
    
    /**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		try
		{
			//检测必填参数
			if($this->parameters["partner_trade_no"] == null) {
				throw new SDKRuntimeException("企业付款接口中，缺少必填参数partner_trade_no！"."<br>");
			}elseif($this->parameters["openid"] == null){
				throw new SDKRuntimeException("企业付款接口中，缺少必填参数openid！"."<br>");
			}elseif($this->parameters["amount"] == null){
				throw new SDKRuntimeException("企业付款接口中，缺少必填参数amount！"."<br>");
			}elseif($this->parameters["desc"] == null){
				throw new SDKRuntimeException("企业付款接口中，缺少必填参数desc！"."<br>");
			}elseif($this->parameters["desc"] == null){
				throw new SDKRuntimeException("企业付款接口中，缺少必填参数desc！"."<br>");
			}
		   	$this->parameters["mch_appid"] = wx_config::APPID;//公众账号ID
		   	$this->parameters["mchid"] = wx_config::MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    // 校验用户姓名选项 NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账） 
            // OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
            $this->parameters['check_name'] = 'NO_CHECK';
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; //终端ip
            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/30    **/
            // 删除参数数组中的sing，避免循环付款时签名生成错误。
            unset($this->parameters["sign"]);
            /* lyq@newland 添加结束 **/
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
    
    /**
	 * 	作用：获取结果，使用证书通信
	 */
	function getResult() 
	{		
		$this->postXmlSSL();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}
}