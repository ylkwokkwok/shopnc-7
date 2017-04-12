<?php defined('NlWxShop') or exit('Access Invalid!');?>
<style type="text/css">
.sticky .tabmenu { padding: 0;  position: relative; }
  #bg{ display: none; position: absolute; top: 0%; left: 0%; width: 100%; height: 100%; background-color: black; z-index:1001; -moz-opacity: 0.7; opacity:.50; filter: alpha(opacity=50);}

</style>
<div class="wrap">
  <div class="step-title"><em>收货信息及交易详情</em></div>
  <form name="deliver_form" method="POST" id="deliver_form" action="index.php?act=store_deliver&op=check&order_id=<?php echo $_GET['order_id'];?>" onsubmit="ajaxpost('deliver_form', '', '', 'onerror');return false;">
    <input type="hidden" value="<?php echo getReferer();?>" name="ref_url">
    <input type="hidden" value="ok" name="form_submit">
    <table class="ncsc-default-table order deliver">
      <tbody>
        <?php if (is_array($output['order_info']) and !empty($output['order_info'])) { ?>
        <tr>
          <td colspan="20" class="sep-row"></td>
        </tr>
        <tr>
          <th colspan="20"><a href="index.php?act=store_order_print&order_id=<?php echo $output['order_info']['order_id'];?>" target="_blank" class="fr" title="<?php echo $lang['store_show_order_printorder'];?>"/><i class="print-order"></i></a><span class="fr mr30"></span><span class="ml10"><?php echo $lang['store_order_order_sn'].$lang['nc_colon'];?><?php echo $output['order_info']['order_sn']; ?></span><span class="ml20"><?php echo $lang['store_order_add_time'].$lang['nc_colon'];?><em class="goods-time"><?php echo date("Y-m-d H:i:s",$output['order_info']['add_time']); ?></em></span>
        </tr>
        <?php foreach($output['order_info']['extend_order_goods'] as $k => $goods_info) { ?>
        <tr>
          <td class="bdl w10"></td>
          <td class="w50"><div class="pic-thumb"><img src="<?php echo cthumb($goods_info['goods_image'],'60',$$output['order_info']['store_id']); ?>" /></div></td>
          <td class="tl"><dl class="goods-name">
              <dt><?php echo $goods_info['goods_name']; ?></dt>
              <dd><strong>￥<?php echo $goods_info['goods_price']; ?></strong>&nbsp;x&nbsp;<em><?php echo $goods_info['goods_num'];?></em>件</dd>
            </dl></td>
          <?php if ((count($output['order_info']['extend_order_goods']) > 1 && $k ==0) || (count($output['order_info']['extend_order_goods']) == 1)){?>
          <td class="bdl bdr order-info" rowspan="<?php echo count($output['order_info']['extend_order_goods']);?>" style="width:100px;">
            <dl>
              <dt style="width:50%;"><?php echo $lang['store_deliver_shipping_amount'].$lang['nc_colon'];?></dt>
              <dd style="width:50%;">
                <?php if (!empty($output['order_info']['shipping_fee']) && $output['order_info']['shipping_fee'] != '0.00'){?>
                <?php echo $output['order_info']['shipping_fee'];?>
                <?php }else{?>
                <?php echo $lang['nc_common_shipping_free'];?>
                <?php }?>
              </dd>
            </dl>
          </td>
          <?php }?>
        </tr>
        <?php }?>
        <tr>
          <td colspan="20" class="tl bdl bdr" style="padding:8px" id="address"><strong class="fl"><?php echo $lang['store_deliver_buyer_adress'].$lang['nc_colon'];?></strong><span id="buyer_address_span"><?php echo $output['order_info']['extend_order_common']['reciver_name'];?>&nbsp;<?php echo $output['order_info']['extend_order_common']['reciver_info']['phone'];?>&nbsp;<?php echo $output['order_info']['extend_order_common']['reciver_info']['address'];?></span><?php echo $output['order_info']['extend_order_common']['reciver_info']['dlyp'] ? '[自提服务站]' : '';?>
        </tr>
        <?php } else { ?>
        <tr>
          <td colspan="20" class="norecord"><i>&nbsp;</i><span><?php echo $lang['no_record'];?></span></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
     <div class="step-title mt30"><em>奶站设置操作</em></div>
     <div class="deliver-sell-info">
         奶站名称： <input type="text" class="text w150" name="station_name" id="station_name" value="" readonly="true">
     </div>
    <div class="step-title mt30"><em>审核操作</em></div>
    <div class="deliver-sell-info">
      <strong class="fl">状态：</strong>
      <span id="seller_address_span">
            <input type="radio"<?php if (in_array($output['order_info']['milk_tohome_delivery_flag'], array('0', '2'))){echo ' checked';}?> name="milk_tohome_delivery_flag" id="d1" value="0"onchange="change();"/>
            <label for="d1">不可配送</label>
            &nbsp;
            <input type="radio"<?php if ($output['order_info']['milk_tohome_delivery_flag'] == '1'){echo ' checked';}?> name="milk_tohome_delivery_flag" id="d2" value="1" onchange="change();"/>
            <label for="d2">可配送</label>
      </span>
      <br>
           <a id ="but_submit" href="javascript:void(0);" onclick="show();" class="ncsc-btn-mini" style="margin-top:10px;"<i class="icon-edit"></i>提交</a>
           <a id="station" href="javascript:void(0);" onclick="showStaion();" class="ncsc-btn-mini" style="margin-top:10px;display:none;" ><i class="icon-edit"></i>设置奶站</a>
    </div>
    <input type="hidden"  id="milkStation"  name="milkStation" value="<?php echo  $output['milk_station']?>"/>
    <input type="hidden"  id="memberInfor"  name="memberInfor" value="<?php echo $output['member_info']['member_wx_id'] ?>"/>
    <input type="hidden"  id="order_sn"  name="order_sn" value="<?php echo $output['order_sn'] ?>"/>
    <!--yzp@newland 2016/03/04 增加
        增加custmoer_cd 隐藏字段
    -->
    <input type="hidden" id="Custmoer" name="Custmoer" value="<?php echo $output['custmoer'] ?>"/>
    <input type="hidden" id="milk_name" name="milk_name" value="<?php echo $output['milk_name'] ?>"/>
  </form>
</div>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery_dialog.css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery_dialog.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript">
$(function(){
    $(".ncsc-path").append('<i class="icon-angle-right"></i>审核配送地址');
    if($("#milkStation").val()!==""){
        $("#station_name").val($("#milkStation").val());
        $('#station').text("设置奶站");
         $("#but_submit").show();
         $("#station").show();
         $("input[name='milk_tohome_delivery_flag']").each(function(){
             if($(this).val()==1){
                 $(this).prop("checked",true);
             }
         });
         $("input:radio:not([checked])").prop("disabled",true);
    }
    change();
});

/**
 *
 * 提交事件 
 * 
 * @returns {undefined}
 * 
 */
function show(){
     if($("input[name='milk_tohome_delivery_flag']:checked").val() == 1){
        if($("#station_name").val()!=""){
            $("#deliver_form").submit();
        }else{
            alert("请您先设置奶站！");
        }
    }else{
         $("#deliver_form").submit();
    }
  }
  
  /**
   * 
   * 弹出窗体
   * 
   * @returns {undefined}
   * 
   */
function showStaion(){
        if($("#milkStation").val()!==""){
             JqueryDialog.Open1('设置奶站', "http://promotion.cenler.com/salesPromotion/customerShop.do?openid="+$("#memberInfor").val()+"&order_sn="+$("#order_sn").val()+"&pageFlag=1", 800, 600,false,false,true);
        }else{
             JqueryDialog.Open1('设置奶站', "http://promotion.cenler.com/salesPromotion/customerShop.do?openid="+$("#memberInfor").val()+"&order_sn="+$("#order_sn").val()+"&pageFlag=0", 800, 600,false,false,true); 
        } 
       
}
/**
 * 
 * 根据状态显示隐藏按钮
 * 
 * @returns {undefined}
 */
function change(){
    if($("input[name='milk_tohome_delivery_flag']:checked").val() == 1){
        $("#but_submit").show();
        $("#station").show();
    }else{
        $("#but_submit").show();
        $("#station").hide();
    }
}

function closePop(){
    $("input[name='form_submit']").val('');
    $("#deliver_form").attr('onsubmit', '');
    $("#deliver_form").submit();
}
</script>
