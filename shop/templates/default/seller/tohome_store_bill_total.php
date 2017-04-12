<?php defined('NlWxShop') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<form method="get" action="index.php" target="_self">
  <table class="search-form">
    <input type="hidden" name="act" value="tohome_store_bill" />
    <input type="hidden" name="op" value="index" />
    <tr>
       <td  class="w300">下单时间：&nbsp;<input type="text" class="text w70" name="query_start_date" id="query_start_date" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date" class="text w70" type="text" name="query_end_date" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
       <td class="w50"><label class="submit-border">
          <input type="submit" class="submit" value="<?php echo $lang['nc_common_search'];?>" />
       </label></td>
       <td class="w50"><label style="text-align:center" class="submit-border"><a  class="submit" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_step1"><span><?php echo $lang['nc_export'];?>Excel</span></a></label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table1">
  <thead>
    <tr>
      <!--<th class="w100"></th>-->
      <th>商品名称</th>
      <th>销售件数</th>
    </tr>
  </thead>
  <tbody>
     <?php if (!empty($output['resultlist']) && is_array($output['resultlist'])) { ?>
      <?php foreach($output['resultlist'] as $bill_info) { ?>
         <tr>
               <!--<td></td>-->
               <td><?php echo $bill_info['goods_name'];?></td>
               <td><?php echo $bill_info['num'];?></td>
         </tr>
      <?php }?>
    <?php } else { ?>
  </tbody>
  <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
  </tbody>
  <tfoot>
    <?php if (!empty($output['resultlist']) && is_array($output['resultlist'])) { ?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" ></script> 
<script type="text/javascript">
$(function(){
    $('#query_start_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date').datepicker({dateFormat: 'yy-mm-dd'});
});
</script>