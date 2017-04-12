<?php defined('NlWxShop') or exit('Access Invalid!');?>
<?php
/**
 * 提现
 * @author zly
 */
?>

<div class="tabmenu">
    <?php include template('layout/submenu');?>
    <a class="ncsc-btn ncsc-btn-green" href="<?php echo urlShop('store_extract_integration','extract_start')?>"><?php echo ' 提现 '?></a>
</div>
  <form method="get" name='search_form' id='search_form'>
      <input type="hidden" name="form_submit" value="ok" />
      <input type="hidden" id='act' name='act' value='store_extract_integration'/>
      <input type="hidden" id='op' name='op' value='index'/>
      <table class="search-form">
      <tr>
          <td>&nbsp;</td>
        <th><?php echo '申请时间';?></th>
        <td class="w240">
            <input type="text" class="text w70"  readonly="readonly" value="<?php echo $_GET['txt_startdate'];?>" id="txt_startdate" name="txt_startdate"/><label class="add-on">
                <i class="icon-calendar"></i>
            </label>
            &#8211;
            <input type="text" class="text w70"  readonly="readonly" value="<?php echo $_GET['txt_enddate'];?>" id="txt_enddate" name="txt_enddate"/><label class="add-on">
                <i class="icon-calendar"></i>
            </label>
        </td>
        <th><?php echo '审核状态';?></th>
        <td class="w60">
            <select class="w80" name="select_state">
                <option value="0" <?php if (!$_GET['select_state'] == '0'){ echo 'selected=true';}?>><?php echo $lang['nc_please_choose'];?></option>
                <?php if (!empty($output['extract_state'])){?>
                <?php foreach ($output['extract_state'] as $k=>$v){?>
                <option value="<?php echo $v[0]; ?>" <?php if ($_GET['select_state'] == $v[0]){echo 'selected=true';}?>><?php echo $v[1];?></option>
                <?php }?>
                <?php }?>
            </select>
        </td>
        <td class="tc w70"><label class="submit-border"><input class='submit' type="button" onclick="search_extract()" value="<?php echo $lang['nc_search'];?>"/></label></td>
      </tr>
    </table>
  </form>
  <table class="ncsc-default-table">
    <thead>
      <tr>
        <th class="w100"><?php echo '申请日期'; ?></th>
        <th class="w60"><?php echo '申请金额'; ?></th>
        <th class="w60"><?php echo '审核状态'; ?></th>
        <th class="w100"><?php echo '审核意见'; ?></th>
        <th class="w100"><?php echo '付款日期'; ?></th>
        <th class="w100"><?php echo '付款错误描述'; ?></th>
        <th class="w60"><?php echo '审核人'; ?></th>
        <th class="w100"><?php echo '审核日期'; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($output['list'])>0) { ?>
      <?php foreach($output['list'] as $val) { ?>
      <tr class="bd-line">
        <td><?php echo date("Y-m-d H:i:s",$val['extract_date']);?></td>
        <td>￥<?php echo $val['extract_money'];?></td>
        <td><?php if($val['extract_flg'] !='' && $val['extract_flg'] == 0){
                echo '未审核';
            } else if($val['extract_flg'] !='' && $val['extract_flg'] == 1) {
                echo '审核通过';
            } else if($val['extract_flg'] !='' && $val['extract_flg'] == 2){
                echo '审核未通过';
            } else {
                echo '已付款';
            }?>
        </td>
        <td><?php echo $val['extract_remark'];?></td>
        <?php if($val['pay_time'] != 0){?>
        <td><?php echo date("Y-m-d H:i:s",$val['pay_time']);?></td>
        <?php } else { ?>
        <td></td>
        <?php } ?>
        <td><?php echo $val['payment_err_mes'];?></td>
        <td><?php echo $val['admin_name'];?></td>
        <?php if($val['check_time'] != 0){?>
        <td><?php echo date("Y-m-d H:i:s",$val['check_time']);?></td>
        <?php } else { ?>
        <td></td>
        <?php } ?>
      </tr>
      <?php }?>
      <?php } else { ?>
      <tr>
        <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
      </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <?php  if (count($output['list'])>0) {?>
      <tr>
        <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
      </tr>
      <?php } ?>
    </tfoot>
  </table>
<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8" ></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#txt_startdate').datepicker();
	$('#txt_enddate').datepicker();
});
function search_extract(){
    $("#search_form").submit();
}
</script>
