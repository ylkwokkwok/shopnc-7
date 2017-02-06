<?php

/**
 * wechat php test
 */
//define your token
define("TOKEN", "tk18jStIKhOM992PpPUWVtvz0OF1npZL");

define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
if (!@include(dirname(dirname(__FILE__)) . '/global.php')) exit('global.php isn\'t exists!');
if (!@include(BASE_CORE_PATH . '/nl_wx_shop.php')) exit('nl_wx_shop.php isn\'t exists!');
Base::run(FALSE);

require_once("wx_common.php");

$wechatObj = new wechatCallbackapi();
//$wechatObj->valid();
$wechatObj->responseMsg();

class wechatCallbackapi {

    public function valid() {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg() {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
              the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            switch ($RX_TYPE){
                case "event":
                    if ($postObj->Event == 'subscribe') {   // 关注事件
                        echo $this->subscribe($postObj);
                    } elseif ($postObj->Event == 'LOCATION') {  // 用户定位推送
                        $this->update_location($postObj);
                    }
                    break;
                case "text":
                    echo $this->transmitService($postObj);
                    break;
            }
            
            exit;
        } else {
            echo "";
            exit;
        }
    }

    /**
     * 处理用户关注事件
     * 
     * @param type $postObj
     * 
     */
    private function subscribe($postObj) {
        $wx_common = new wx_common();
        // 获取access_token
        $result = Model()->table('setting')->where(array('name' => 'wx_access_token'))->field('value')->find();
        $token = $result['value'];
        $user_info = $wx_common->get_user_info($token, $postObj->FromUserName);
        $time = time();
        $msgType = "text";
        $contentStr = '欢迎【' . $user_info->nickname . '】关注cenler-fresh！';
        $this->logResult($contentStr);
        // 构造系统用户基本信息
        $member_info = array();
        // 用户的OPENID
        $member_info['member_wx_id'] = $user_info->openid;
        // 用户的昵称
        $member_info['member_name'] = $user_info->nickname;
        // 用户的头像
        $member_info['member_avatar'] = $user_info->headimgurl;
        // 用户的性别 值为1时是男性，值为2时是女性，值为0时是未知
        $member_info['member_sex'] = $user_info->sex;
        // 用户积分默认赠送20分
        $member_info['extend_points'] = C('points_reg');
        // 当前登录时间
        $member_info['member_login_time'] = TIMESTAMP;
        // 已关注?---?推广渠道等级
        $member_info['statis_vip'] = 0;
        // 关注标志：已关注
        $member_info['subscribe_flag'] = 1;

        $wx_common->update_user_info($member_info);

        return sprintf($this->get_text_tpl(), $postObj->FromUserName, $postObj->ToUserName, $time, $msgType, $contentStr);
    }

    private function get_text_tpl() {
        return "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";
    }

    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * @param $word 要写入日志里的文本内容 默认值：空值
     */
    private function logResult($word = '') {
        $fp = fopen("log.txt", "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    private function checkSignature() {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更新用户位置
     * 
     * @param type $postObj 微信推送数据对象
     */
    private function update_location($postObj) {
        // 获取转换后的经纬度（百度API需要的经纬度）
        $location_info = $this->geoconvert($postObj->Longitude.','.$postObj->Latitude);
        // 更新用户位置
        $data = array(
            'longitude' => $location_info->x,
            'latitude' => $location_info->y,
            'location_update_time' => $postObj->CreateTime,
            'member_wx_id' => $postObj->FromUserName,
        );
        Model()->table('member')->update($data);
    }
    
    /**
     * 转换经纬度坐标
     *   from GPS设备获取的角度坐标
     *   to   bd09ll(百度经纬度坐标)
     * @param type $location <经度>,<纬度>
     * @return type 转换结果
     */
    private function geoconvert($location) {
        // 百度 坐标转换服务
        $output = file_get_contents('http://api.map.baidu.com/geoconv/v1/?coords='.$location.'&from=1&to=5&ak=pSLSjNvheZxFvpGTqLWjFnqV');
        // 解析响应对象
        $obj = json_decode($output);
        // 返回转换结果
        return $obj->result[0];
    }
    
    /**
     * 回复多客服消息
     */
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
    
    
}

?>