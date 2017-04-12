<?php defined('NlWxShop') or exit('Access Invalid!');?>
<?php
/**
 * 提现详细
 * @author zly
 */
?>

<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
<div class="ncsc-oredr-show">
  <div class="ncsc-order-info">
    <div class="ncsc-order-details">
      <div class="title"><?php echo $lang['store_show_order_info'];?></div>
      <div class="content">
        <dl>
          <dt><?php echo $lang['store_show_order_receiver'].$lang['nc_colon'];?></dt>
          <dd><?php echo $output['order_info']['extend_order_common']['reciver_name'];?>&nbsp; <?php echo @$output['order_info']['extend_order_common']['reciver_info']['phone'];?>&nbsp; <?php echo @$output['order_info']['extend_order_common']['reciver_info']['address'];?><?php echo $output['order_info']['extend_order_common']['reciver_info']['dlyp'] ? '[自提服务站]' : '';?></dd>
        </dl>
        <dl class="line">
          <dt><?php echo $lang['store_order_order_sn'].$lang['nc_colon'];?></dt>
          <dd><?php echo $output['order_info']['order_sn']; ?><a href="javascript:void(0);">更多<i class="icon-angle-down"></i>
            <div class="more"><span class="arrow"></span>
              <ul>
                <?php if($output['order_info']['payment_name']) { ?>
                <li><?php echo $lang['store_order_pay_method'].$lang['nc_colon'];?><span><?php echo $output['order_info']['payment_name']; ?>
                  <?php if($output['order_info']['payment_code'] != 'offline' && !in_array($output['order_info']['order_state'],array(ORDER_STATE_CANCEL,ORDER_STATE_NEW))) { ?>
                  (<?php echo '付款单号'.$lang['nc_colon'];?><?php echo $output['order_info']['pay_sn']; ?>)
                  <?php } ?>
                  </span></li>
                <?php } ?>
                <li><?php echo $lang['store_order_add_time'].$lang['nc_colon'];?><span><?php echo date("Y-m-d H:i:s",$output['order_info']['add_time']); ?></span></li>
                <?php if(intval($output['order_info']['payment_time'])) { ?>
                <li><?php echo $lang['store_show_order_pay_time'].$lang['nc_colon'];?><span><?php echo date("Y-m-d H:i:s",$output['order_info']['payment_time']); ?></span></li>
                <?php } ?>
                <?php if($output['order_info']['extend_order_common']['shipping_time']) { ?>
                <li><?php echo $lang['store_show_order_send_time'].$lang['nc_colon'];?><span><?php echo date("Y-m-d H:i:s",$output['order_info']['extend_order_common']['shipping_time']); ?></span></li>
                <?php } ?>
                <?php if(intval($output['order_info']['finnshed_time'])) { ?>
                <li><?php echo $lang['store_show_order_finish_time'].$lang['nc_colon'];?><span><?php echo date("Y-m-d H:i:s",$output['order_info']['finnshed_time']); ?></span></li>
                <?php } ?>
              </ul>
            </div>
            </a></dd>
        </dl>
        <dl>
          <dt></dt>
          <dd></dd>
        </dl>
      </div>
    </div>
      <div class="ncsc-order-condition">
          <?php if(!empty($output['order_info']['extract_id'])){?>
          <dl>
              <dt>审核状态：<?php echo $output['order_info']['extract_detail']['extract_message']; ?></dt>
          </dl>
          <?php } else {?>
          <dl>
              <dt>未提现</dt>
          </dl>
          <?php } ?>
          <?php if ($output['order_info']['extract_detail']['extract_remark'] != NULL) { ?>
              <ul>
                  <li>审核意见：<?php echo $output['order_info']['extract_detail']['extract_remark']; ?></li>
              </ul>
          <?php } ?>
    </div>
      </div>
      <div class="ncsc-order-contnet">
    <table class="ncsc-default-table order">
      <thead>
        <tr>
          <th class="w10">&nbsp;</th>
          <th class="w200" colspan="2"><?php echo $lang['store_show_order_goods_name'];?></th>
          <th class="w120"><?php echo $lang['store_show_order_price'];?></th>
          <th class="w60"><?php echo $lang['store_show_order_amount'];?></th>
          <th class=""><strong><?php echo '积分数 * 积分比例 = 抵扣总额（元）'; ?></strong></th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 0;?>
        <?php foreach($output['order_info']['goods_list'] as $k => $goods) { ?>
        <?php $i++;?>
        <tr class="bd-line">
          <td>&nbsp;</td>
          <td class="w50"><div class="pic-thumb"><img src="<?php echo $goods['image_60_url']; ?>" /></div></td>
          <td class="tl"><dl class="goods-name">
              <dt><?php echo $goods['goods_name']; ?></dt>
            </dl>
          </td>
          <td><?php echo $goods['goods_price']; ?></td>
          <td><?php echo $goods['goods_num']; ?></td>
              <?php if ($k == 0) { ?>
                  <td class="commis bdl bdr" rowspan="<?php echo count($output['order_info']['goods_list']); ?>">
                      <?php if (!empty($output['order_info']['extend_points']) && !empty($output['order_info']['points_cash_ratio'])) { ?>
                          <?php echo $output['order_info']['extend_points']; ?> * <?php echo $output['order_info']['points_cash_ratio'] / 100; ?> = <b><?php echo ncPriceFormat($output['order_info']['extend_points'] * $output['order_info']['points_cash_ratio'] / 100); ?></b>
                      <?php } ?>
                  </td>
                </tr>
            <?php } ?>
        <?php } ?>
      </tbody>
      <tfoot>
          <tr>
          <td colspan="15">
            <dl class="freight">
              <dd>
                <?php if(!empty($output['order_info']['shipping_fee']) && $output['order_info']['shipping_fee'] != '0.00'){ ?>
                <?php echo $lang['store_show_order_tp_fee'];?>: <span><?php echo $lang['currency'];?><?php echo $output['order_info']['shipping_fee']; ?></span>
                <?php }else{?>
                <?php echo $lang['nc_common_shipping_free'];?>
                <?php }?>
                <?php if($output['order_info']['refund_amount'] > 0) { ?>
                (<?php echo $lang['store_order_refund'];?>:<?php echo $lang['currency'].$output['order_info']['refund_amount'];?>)
                <?php } ?>
              </dd>
              </dl>
            <dl class="sum">
              <dt><?php echo $lang['store_order_sum'].$lang['nc_colon'];?></dt>
              <dd><em><?php echo $output['order_info']['order_amount']; ?></em>元</dd>
            </dl>
          </td>
         </tr>
      </tfoot>
    </table>
  </div>
    </div>
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
