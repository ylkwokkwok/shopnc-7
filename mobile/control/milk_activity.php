<?php

defined('NlWxShop') or exit('Access Invalid!');

class milk_activityControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 活动页面初始化
     */
    public function indexOp() {
        // 返回数据初始化 错误码：0
        $datas = array('error' => 0);
        // 会员已绑定手机号时
        if ($this->member_info['member_mobile_bind'] == '1') {
            // 错误码：1
            $datas['error'] = 1;
            // 会员真实姓名
            $datas['truename'] = $this->member_info['member_truename'];
            // 会员绑定的手机号
            $datas['mobile'] = $this->member_info['member_mobile'];
            // 绑定日期
            $datas['bind_date'] = $this->member_info['member_mobile_bind_time'];
        }
        // 返回数据
        output_data($datas);
    }
    
    /**
     * 发送验证码
     */
    public function send_verifyOp() {
        // 返回数据初始化 错误码：0
        $datas = array('error' => 0);
        // 获取手机号
        $mobile = $_POST['mobile'];
        // 根据手机号查询用户信息
        $existed_user_info = Model()->table('member')->where('member_mobile = "' . $mobile . '"')->select();
        // 用户已存在
        if (count($existed_user_info) > 0) {
            // 错误码：1
            $datas['error'] = 1;
            // 提示信息
            $datas['msg']   = '该手机号已被使用';
        }
        else {
            // 获取随机验证码	
            $verify = rand(123456, 999999);
            // 发送短信
            $result = $this->send_sms($mobile, $verify);
            // 短信发送成功
            if ($result == '0') {
                $_SESSION['mobile'] = $mobile;
                $_SESSION['verify'] = $verify;
                $_SESSION['verify_expire'] = time()+180;
                $datas['msg'] = '验证码已发送，收到后请及时使用，3分钟内有效';
            }
            // 发送失败
            else {
                // 错误码：1
                $datas['error'] = 1;
                // 提示信息
                $datas['msg']   = '操作频繁，请稍后再试';
            }
        }
        // 返回数据
        output_data($datas);
    }
    
    public function bind_mobileOp() {
        // 返回数据初始化 错误码：0
        $datas = array('error' => 0);
        // 获取真实姓名
        $truename = $_POST['truename'];
        // 获取手机号
        $mobile = $_POST['mobile'];
        // 获取验证码
        $verify = $_POST['verify'];
        // 手机号验证失败
        if ($mobile != $_SESSION['mobile']) {
            // 错误码：1
            $datas['error'] = 1;
            // 提示信息
            $datas['msg']   = '手机号码已更改，请重新获取验证码';
        }
        // 验证码验证失败
        elseif ($verify != $_SESSION['verify']) {
            // 错误码：1
            $datas['error'] = 1;
            // 提示信息
            $datas['msg']   = '验证码错误填写错误';
        }
        // 验证码过期
        elseif (time() > $_SESSION['verify_expire']) {
            // 错误码：1
            $datas['error'] = 1;
            // 提示信息
            $datas['msg']   = '验证码过期，请重新获取验证码';
        }
        // 验证通过
        else {
            // 需要更新的信息
            $update_info = array(
                'member_truename' => $truename, // 真实姓名
                'member_mobile'   => $mobile,   // 手机号
                'member_mobile_bind' => 1,      // 已绑定
                'member_mobile_bind_time' => date('Y-m-d H:i:s') // 手机号绑定时间
            );
            // 更新会员信息
            Model()->table('member')
                   ->where('member_id = '.$this->member_info['member_id'])
                   ->update($update_info);
            // 清除session信息
            unset($_SESSION['mobile']);
            unset($_SESSION['verify']);
            unset($_SESSION['verify_expire']);
        }
        // 返回数据
        output_data($datas);
    }
    
    /**
     * 发送短信
     * @param type $mobile 手机号
     * @param type $verify 验证码
     * @return type 执行结果 0成功，1失败
     */
    private function send_sms($mobile, $verify) {
        header("Content-Type: text/html; charset=UTF-8");

        $flag = 0;
        $params = ''; //要post的数据 	
        //以下信息自己填以下
        $argv = array(
            'name' => 'dxwxinleruye', //必填参数。用户账号
            'pwd' => '7B57974D0A9234C69F426E720015', //必填参数。（web平台：基本资料中的接口密码）
            'content' => '您的验证码是：' . $verify . '。请不要把验证码泄露给其他人。如非本人操作，可不用理会！【心乐乳业】',
            'mobile' => $mobile, //必填参数。手机号码。多个以英文逗号隔开
            'stime' => '', //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
            'sign' => '', //必填参数。用户签名。
            'type' => 'pt', //必填参数。固定值 pt
            'extno' => ''    //可选参数，扩展码，用户定义扩展码，只能为数字
        );
        //print_r($argv);exit;
        //构造要post的字符串 
        //echo $argv['content'];
        foreach ($argv as $key => $value) {
            if ($flag != 0) {
                $params .= "&";
                $flag = 1;
            }
            $params.= $key . "=";
            $params.= urlencode($value); // urlencode($value); 
            $flag = 1;
        }
        $url = "http://web.duanxinwang.cc/asmx/smsservice.aspx?" . $params; //提交的url地址
        $result = file_get_contents($url);
        $con = substr($result, 0, 1);  //获取信息发送后的状态

        return $con;
    }
}
