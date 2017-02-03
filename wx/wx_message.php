<?php
/* lyq@newland 添加  **/
/* 时间：2015/07/01  **/
/* 微信消息相关      **/

/* lyq@newland 添加  **/
/* 时间：2015/07/15  **/
/* 微信消息相关 第二版  **/

require_once("wx_core.php");

/**
 * 微信消息模块
 * 
 * @author lyq@newland
 */
class wx_message {
    // 请求的url
    private $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=';
    
    public function __construct() {
        // 获取access_token
        $result = Model()->table('setting')->where(array('name' => 'wx_access_token'))->field('value')->find();
        // 拼接access_token
        $this->url .= $result['value'];
    }
    
    /**
     * 发送消息
     * 
     * @param array $data 消息模板需要的数据
     * @param string $msg_code 消息模板代码
     * @return object 微信消息相应对象
     */
    public function send_message($data = array(), $msg_code ='') {
        // 获取post参数
        $param = $this->format_param($data, $msg_code);
        // 远程请求发送消息
        $response = getHttpResponsePOST($this->url, $param);
        // 返回json解密后的响应对象
        return json_decode($response);
    }
    
    /**
     * 获取请求需要的post参数
     * 
     * @param array $data 消息模板需要的数据
     * @param string $msg_code 消息模板代码
     * @return string json加密后的post参数
     */
    private function format_param($data, $msg_code) {
        switch ($msg_code) {
            // 购买成功消息模板
            case 'pay_success':
                return $this->tmpl_pay_success($data);
            // 奶卡订购付款成功
            case 'pay_milk_success':
                return $this->tmpl_pay_milk_success($data);
            //发货提醒
            case 'sendMessage':
                return $this->tmpl_send_success($data);
            // 模板代码不存在时
            default:
                return '';
        }
    }
    
    /**
     * 购买成功消息模板
     * 
     * @param array $data 消息模板需要的数据
     * @return string json加密后的post参数
     */
    private function tmpl_pay_success($data) {
        // 消息模板参数
        $param = array(
            "touser" => $data['member_wx_id'],
            "template_id" => "i2EavcCGdpG_TKLnP8LUKSb3f2T9QZfFr5SPg025QTU",
            "url" => $data['url'],
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array(
                    "value" => $data['first'],
                    "color" => "#173177"
                ),
                "orderno" => array(
                    "value" => $data['order_sn'],
                    "color" => "#173177"
                ),
                "amount" => array(
                    "value" => $data['pay_amount'] . '元',
                    "color" => "#173177"
                ),
                "remark" => array(
                    "value" => $data['remark'],
                    "color" => "#173177"
                )
            )
        );
        // 返回json加密后的消息模板参数
        return json_encode($param);
    }
    
    /**
     * 奶卡订购付款成功
     * 
     * @param array $data 消息模板需要的数据
     * @return string json加密后的post参数
     */
    private function tmpl_pay_milk_success($data) {
        // 消息模板参数
        $param = array(
            "touser" => $data['member_wx_id'],
            "template_id" => "2vEc-9sLzTy3H9YlS0aTJFINg2-HDE_GdueroLu6eG0",
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array(
                    "value" => $data['first'],
                    "color" => "#173177"
                ),
                "keyword1" => array(
                    "value" => $data['keyword1'],
                    "color" => "#173177"
                ),
                "keyword2" => array(
                    "value" => $data['keyword2'],
                    "color" => "#173177"
                ),
                "keyword3" => array(
                    "value" => $data['keyword3'],
                    "color" => "#173177"
                ),
                "remark" => array(
                    "value" => $data['remark'],
                    "color" => "#173177"
                )
            )
        );
        // 需要点击消息跳转功能时
        if (!empty($data['url'])) {
            $param['url'] = $data['url'];
        }
        // 返回json加密后的消息模板参数
        return json_encode($param);
    }
    
    
     /**
     * 发货提醒消息模板
     * 
     * @param array $data 消息模板需要的数据
     * @return string json加密后的post参数
     */
    private function tmpl_send_success($data) {
        // 消息模板参数
        $param = array(
            "touser" => $data['member_wx_id'],
            "template_id" => "R1qsAQKgdjlajqCTHsgpgsEmUfblm6vAsG1vLVddg_s",
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array(
                    "value" => $data['first'],
                    "color" => "#173177"
                ),
                "keyword1" => array(
                    "value" => $data['keyword1'],
                    "color" => "#173177"
                ),
                "keyword2" => array(
                    "value" => $data['keyword2'],
                    "color" => "#173177"
                ),
                "remark" => array(
                    "value" => $data['remark'],
                    "color" => "#173177"
                )
            )
        );
        // 返回json加密后的消息模板参数
        return json_encode($param);
    }
}