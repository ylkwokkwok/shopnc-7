<?php

if ($_POST['action_name'] != '') {
	include_once("wx_qrcode.php");

	$wx_qrcode = new wx_qrcode();
	$wx_qrcode->set_token();
	
	$paray_arr = array();
	
	$file_name_list = array();
	
	for ($i = 0; $i < $_POST['num']; $i++) {
		$paray_arr['action_name'] = $_POST['action_name'];
		if ($_POST['type_name'] == 'char') {
			// 临时二维码
			if ($_POST['action_name'] == 'QR_SCENE') {
				$paray_arr['expire_seconds'] = 604800;
				$paray_arr['scene_id'] = $_POST['action_info'];
			// 永久二维码
			} else {
				$paray_arr['scene_str'] = $_POST['action_info'];
			}
		} else {
			$action_info = intval($_POST['action_info']);
			// 临时二维码
			if ($_POST['action_name'] == 'QR_SCENE') {
				$paray_arr['expire_seconds'] = 604800;
				$paray_arr['scene_id'] = $action_info++;
			// 永久二维码
			} else {
				$paray_arr['scene_str'] = $action_info++;
			}
			
		}
		
		$file_name = time().rand(4,120).'.jpg';
		$file_name_list[] = $file_name;
		//创建并写入数据流，然后保存文件
		if (@$fp = fopen ('./'.$file_name, 'w+' )) {
			fwrite ($fp, $wx_qrcode->get_qrcode($paray_arr));
			fclose ($fp );
		}
		
	}

}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <form action="qrcode.php" method="post">
    		
            <table>
                <tr>
                    <td>
                        输入内容：<input type="text" name="action_info" />
                    </td>
                    <td>
                        <select name="action_name">
                            <option value="QR_SCENE">临时</option>
                            <option value="QR_LIMIT_SCENE">永久</option>
                        </select>
                    </td>
                	<td>
                        
                    </td>
                </tr>
                <tr>
                    <td>
                        输入数量：<input type="text" name="num" />
                    </td>
                    <td>
                        <select name="type_name">
                            <option value="number">数字</option>
                            <option value="char">字母</option>
                        </select>
                    </td>
                    	<td>
                        <input type="submit" value="生成" />
                    </td>
                </tr>
            </table>
            
            <?php 
            	if (isset($file_name_list)) { 
            		for($j=0; $j < count($file_name_list); $j++) {
            	
            	?>
            	<img src="<?php echo $file_name_list[$j] ?>"  height="100" width="100" />
            
            
            <?php 
            		}
            	} 
            ?>
        </form>
        
    </body>
</html>
