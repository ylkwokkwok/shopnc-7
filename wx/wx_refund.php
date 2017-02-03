<?php
/**
 * 退款申请接口-demo
 * ====================================================
 * 注意：同一笔单的部分退款需要设置相同的订单号和不同的
 * out_refund_no。一笔退款失败后重新提交，要采用原来的
 * out_refund_no。总退款金额不能超过用户实际支付金额(现
 * 金券金额不能退款)。
*/
    require_once("wx_core.php");
	require_once("wx_common.php");
    
    function refund($param_arr = array()) {
        // 商户订单号
        $out_trade_no = $param_arr['out_trade_no'];
        // 微信订单号
        $transaction_id = $param_arr['transaction_id'];
        // 商户退款单号
        $out_refund_no = $param_arr['out_refund_no'];
        // 总金额
        $total_fee = $param_arr['total_fee'];
        // 退款金额
        $refund_fee = $param_arr['refund_fee'];
        // 操作员
        $op_user_id = $param_arr['user_id'];
        
        //使用退款接口
		$refund = new Refund_pub();
        
		//设置必填参数
		$refund->setParameter("transaction_id","$transaction_id");//微信订单号
		$refund->setParameter("out_trade_no","$out_trade_no");//商户订单号
		$refund->setParameter("out_refund_no","$out_refund_no");//商户退款单号
		$refund->setParameter("total_fee",$total_fee);//总金额
		$refund->setParameter("refund_fee",$refund_fee);//退款金额
		$refund->setParameter("op_user_id", "$op_user_id");//操作员

		//调用结果
		$refundResult = $refund->getResult();
        
        //商户根据实际情况设置相应的处理流程,此处仅作举例
		if ($refundResult["return_code"] == "FAIL") {
            logResult("通信出错：".$refundResult['return_msg']);
		} else {
            logResult("业务结果：".$refundResult['result_code']);
			logResult("微信退款单号：".$refundResult['refund_id']);
			logResult("退款金额：".$refundResult['refund_fee']);
		}
        
        return $refundResult;
    }

	
?>
