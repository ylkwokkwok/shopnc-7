<?php

require_once("wx_core.php");
require_once("wx_common.php");

function send_redpack($param_arr = array()) { 
    
    // 接受收红包的用户
    $re_openid = $param_arr['openid'];
    // 红包金额
    $total_amount = $param_arr['total_amount'];
    // 红包祝福语
    $wishing = $param_arr['wishing'];
    // 活动名称
    $act_name = $param_arr['act_name'];
    // 备注
    $remark = $param_arr['remark'];
    
    $send_redpack = new SendRedpack_pub();
    
    //设置必填参数
    $send_redpack->setParameter("re_openid","$re_openid");// 总金额
    $send_redpack->setParameter("total_amount",$total_amount);// 红包金额
    $send_redpack->setParameter("min_value",$total_amount);// 红包金额
    $send_redpack->setParameter("max_value",$total_amount);// 红包金额
    $send_redpack->setParameter("total_num", 1);// 红包发放总人数
    $send_redpack->setParameter("wishing", $wishing);// 红包祝福语
    $send_redpack->setParameter("client_ip", $_SERVER["REMOTE_ADDR"]);// 调用接口的机器Ip地址
    $send_redpack->setParameter("act_name", "$act_name");// 活动名称
    $send_redpack->setParameter("remark", "$remark");// 备注
    
    //调用结果
    $refundResult = $send_redpack->getResult();

    //商户根据实际情况设置相应的处理流程,此处仅作举例
    if ($refundResult["return_code"] == "FAIL") {
        logResult("通信出错：".$refundResult['return_msg']);
    } else {
        logResult("业务结果：".$refundResult['result_code']);
        logResult("微信退款单号：".$refundResult['refund_idrefund_id']);
        logResult("退款金额：".$refundResult['refund_fee']);
    }

    return $refundResult;


}


