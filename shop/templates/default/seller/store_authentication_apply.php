<!--
/* zly@newland 店铺申请认证页面 **/
/* 时间：2015/06/19 **/
// 店铺申请认证
-->
<style>
.hd2222 { border-left: solid 3px #28B779; padding-left: 6px; margin-bottom: 4px;}
</style>
<script>
    function storeApply(){
        if (confirm('请在七个工作日内，上交认证费用')) {
            location.href = 'index.php?act=store_info&op=store_authentication_apply&store_id=<?php echo $output['authentication']['store_id'] ?>';
           }
        return;
    }
</script>
<?php defined('NlWxShop') or exit('Access Invalid!'); ?>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
    <?php if (isset($output['authentication']['apply_authentication']) && $output['authentication']['apply_authentication'] == 0) { ?>
        <?php if ($notOwnShop = !checkPlatformStore()) { ?>
        <a href="javascript:void(0)" class="ncsc-btn ncsc-btn-green" onclick="storeApply()">申请认证</a>
        <?php } ?>
 </div>
    <?php } else if (isset($output['authentication']['apply_authentication']) && $output['authentication']['apply_authentication'] == 1 && $output['authentication']['authentication_state'] == 0) { ?>
<table>
  <thead>
    <tr>
        <th>
            <p>&nbsp;</p>
            <p class="hd2222"><?php echo '本店铺已经申请认证'; ?></p>
    <?php } else { ?>
            <?php if (isset($output['authentication']['authentication_state']) && $output['authentication']['authentication_state'] == 1) { ?>
                <p class="hd2222"><?php echo '店铺已经通过申请，已是被认证商家'; ?></p>
            <?php } ?>
    <?php } ?>
            <?php if (isset($output['authentication']['authentication_message']) && $output['authentication']['authentication_state'] != NULL) { ?>
            <p class="hd2222">审核意见：<?php echo $output['authentication']['authentication_message'] ?></p>
            <?php } ?> 
        </th>
    </tr>
  </thead>
</table>
