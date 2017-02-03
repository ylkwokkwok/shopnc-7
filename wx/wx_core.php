<?php

/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstring($para) {
	$arg  = "";
	while (list ($key, $val) = each ($para)) {
		$arg.=$key."=".$val."&";
	}
	//去掉最后一个&字符
	$arg = substr($arg,0,count($arg)-2);
	
	//如果存在转义字符，那么去掉转义
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
}

/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstringUrlencode($para) {
	$arg  = "";
	while (list ($key, $val) = each ($para)) {
		$arg.=$key."=".urlencode($val)."&";
	}
	//去掉最后一个&字符
	$arg = substr($arg,0,count($arg)-2);
	
	//如果存在转义字符，那么去掉转义
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
}

/**
 * 远程获取数据，POST模式
 * 注意：
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址
 * @param $para 请求的数据
 * @param $input_charset 编码格式。默认值：空值
 * return 远程输出的数据
 */
function getHttpResponsePOST($url, $para, $input_charset = '') {

	if (trim($input_charset) != '') {
		$url = $url."_input_charset=".$input_charset;
	}
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);//SSL证书认证
	curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
	curl_setopt($curl,CURLOPT_POST,true); // post传输数据
	curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
	$responseText = curl_exec($curl);
	//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
	curl_close($curl);
	
	return $responseText;
}

/**
 * 远程获取数据，GET模式
 * 注意：
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址
 * @param $cacert_url 指定当前工作目录绝对路径
 * return 远程输出的数据
 */
function getHttpResponseGET($url) {
	$curl = curl_init($url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);//SSL证书认证
	curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
	$responseText = curl_exec($curl);
	//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
	curl_close($curl);
	
	return $responseText;
}

/**
 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
 * 注意：服务器需要开通fopen配置
 * @param $word 要写入日志里的文本内容 默认值：空值
 */
function logResult($word='') {
        // 定义框架路径
        define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
        // 引用框架核心类
        if (!@include(dirname(dirname(__FILE__)).'/global.php')) exit('global.php isn\'t exists!');
        if (!@include(BASE_CORE_PATH.'/nl_wx_shop.php')) exit('nl_wx_shop.php isn\'t exists!');
        // 执行框架
        Base::run(FALSE);
        // 整理日志数据
        $log_info = array(
            'log_type' => '1',
            'log_date' => date('Y-m-d H:i:s'),
            'log_text' => $word
        );
        // 添加日志
        Model()->table('log')->insert($log_info);
//	$fp = fopen("log.txt","a");
//	flock($fp, LOCK_EX) ;
//	fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
//	flock($fp, LOCK_UN);
//	fclose($fp);
}