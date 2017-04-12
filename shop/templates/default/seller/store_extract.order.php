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
      <input type="hidden" id='op' name='op' value='store_extract'/>
      <table class="search-form">
      <tr>
          <td>&nbsp;</td>
        <th><?php echo '下单时间';?></th>
        <td class="w240">
            <input type="text" class="text w70"  readonly="readonly" value="<?php echo $_GET['order_startdate'];?>" id="txt_startdate" name="order_startdate"/><label class="add-on">
            <i class="icon-calendar"></i>
            </label>
            &#8211;
            <input type="text" class="text w70"  readonly="readonly" value="<?php echo $_GET['order_enddate'];?>" id="txt_enddate" name="order_enddate"/><label class="add-on">
            <i class="icon-calendar"></i>
            </label>
        </td>
        <th><?php echo '订单编号';?></th>
        <td class="tc w70"><input type="text" name="order_sn" value="<?php echo $_GET['order_sn']?>"></input></td>
        <td class="tc w70"><label class="submit-border"><input class='submit' type="button" onclick="search_extract()" value="<?php echo $lang['nc_search'];?>"/></label></td>
      </tr>
    </table>
  </form>
  <table class="ncsc-default-table">
    <thead>
      <tr>
        <th class="w100"><?php echo '订单编号'; ?></th>
        <th class="w60"><?php echo '下单时间'; ?></th>
        <th class="w60"><?php echo ' 订单完成时间'; ?></th>
        <th class="w60"><?php echo '订单金额'; ?></th>
        <th class="w100"><strong><?php echo '积分数 * 积分比例 = 抵扣总额（元）'; ?></strong></th>
        <th class="w60"><?php echo '操作'; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($output['list'])>0) { ?>
      <?php foreach($output['list'] as $val) { ?>
      <tr class="bd-line">
        <td><?php echo $val['order_sn'] ?></td>
        <td><?php echo date("Y-m-d H:i:s",$val['add_time']);?></td>
        <?php if($val['finnshed_time'] != 0){?>
        <td><?php echo date("Y-m-d H:i:s",$val['finnshed_time']);?></td>
        <?php } else {?>
        <td></td>
        <?php } ?>
        <td>￥<?php echo $val['order_amount'];?></td>
        <td><?php echo $val['extend_points']; ?> * <?php echo $val['points_cash_ratio']/100; ?> = <b><?php echo ncPriceFormat($val['extend_points'] * $val['points_cash_ratio'] / 100); ?></b></td>
        <td><a href="index.php?act=store_extract_integration&op=show_order_detail&order_id=<?php echo $val['order_id'];?>"><?php echo '查看';?></a></td>
      </tr>
      <?php }?>
      <?php } else { ?>
      <tr>
        <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
      </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <?php  if (count($output['list'])>0) { ?>
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
