<?php defined('NlWxShop') or exit('Access Invalid!');?>
<?php
/**
 * 提现
 * @author zly
 */
?>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
<div class="alert alert-block mt10 mb10">
    <ul>
        <li><?php echo '【提现完成总金额】平台已经付款的总金额，非即时到账';?></li>
        <li><?php echo '【可提现金额】未申请提现的订单金额';?></li>
        <li><?php echo '【提现】一次提取所有可提现金额';?></li>
    </ul>
</div>
<div class="ncsc-form-default">
<form method="get" name='search_form' id='search_form'>
      <input type="hidden" id='act' name='act' value='store_extract_integration'/>
      <input type="hidden" id='op' name='op' value='store_extract_apply'/>
    <input type="hidden" id='extend_points' name='extend_money' value='<?php echo $output['extend_points']['extend_points']; ?>'/>
        <dl>
            <dt style="text-align: right">提现完成总金额</dt>
            <?php if($output['all_extend_points']['all_extend_points'] <= 0){?>
                    <dd><?php echo '0'; ?>（元）</dd>
            <?php } else {?>
                    <dd><?php echo $output['all_extend_points']['all_extend_points']; ?>（元）</dd>
            <?php } ?>
        </dl>
        <dl>
            <dt style="text-align: right">可提现金额</dt>
            <?php if($output['extend_points']['extend_points'] <= 0){?>
                    <dd><?php echo '0'; ?>（元）</dd>
            <?php } else {?>
                    <dd><?php echo $output['extend_points']['extend_points']; ?>（元）</dd>
            <?php } ?>
        </dl>
    <?php if ($output['extend_points']['extend_points'] > 0) { ?>
        <div class="bottom">
            <label class="submit-border"><input class='submit' type="button" onclick="extract_start_submit()" value="<?php echo '提现';?>"/></label>
        </div>
    <?php } ?>
</form>
</div>
<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8" ></script>
<script type="text/javascript">
function extract_start_submit(){
    $("#search_form").submit();
}
</script>

