<?php defined('InShopNC') or exit('Access Invalid!');?>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/index.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/home_index.js" charset="utf-8"></script>
<!--[if IE 6]>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ie6.js" charset="utf-8"></script>
<![endif]-->
<script type="text/javascript">
var uid = window.location.href.split("#V3");
var  fragment = uid[1];
if(fragment){
	if (fragment.indexOf("V3") == 0) {document.cookie='uid=0';}
else {document.cookie='uid='+uid[1];}
	}

</script>
<!--
<style type="text/css">
.category { display: block !important; }
</style>
-->
<div class="clear"></div>

<!-- HomeFocusLayout Begin-->
<div class="home-focus-layout"> <?php echo $output['web_html']['index_pic'];?>
    <!--
  <div class="right-sidebar">
	<!--
      <div class="policy">
          <ul>
              <li class="b1">七天包退</li>
              <li class="b2">正品保障</li>
              <li class="b3">闪电发货</li>
          </ul>
      </div>
    <?php if(!empty($output['group_list']) && is_array($output['group_list'])) { ?>
    <div class="groupbuy">
      <div class="title"><i>抢</i>近期抢购</div>
      <ul>
        <?php foreach($output['group_list'] as $val) { ?>
        <li>
          <dl style=" background-image:url(<?php echo gthumb($val['groupbuy_image1'], 'small');?>)">
            <dt><?php echo $val['groupbuy_name']; ?></dt>
            <dd class="price"><span class="groupbuy-price"><?php echo ncPriceFormatForList($val['groupbuy_price']); ?></span><span class="buy-button"><a href="<?php echo urlShop('show_groupbuy','groupbuy_detail',array('group_id'=> $val['groupbuy_id']));?>">立即抢</a></span></dd>
            <dd class="time"><span class="sell">已售<em><?php echo $val['buy_quantity'];?></em></span> <span class="time-remain" count_down="<?php echo $val['end_time']-TIMESTAMP; ?>"> <em time_id="d">0</em><?php echo $lang['text_tian'];?><em time_id="h">0</em><?php echo $lang['text_hour'];?> <em time_id="m">0</em><?php echo $lang['text_minute'];?><em time_id="s">0</em><?php echo $lang['text_second'];?> </span></dd>
          </dl>
        </li>
        <?php } ?>
      </ul>
    </div>
    <?php } ?>
      <div class="proclamation" style="height:328px;">
          <ul class="tabs-nav">
              <li style="width:100%">
                  <h3>销售排行榜</h3>
              </li>
          </ul>
          <div class="tabs-panel">
              <ul class="mall-news">
					<?php $output['xianshi_item'] = array_slice($output['xianshi_item'],0,5);
						foreach($output['xianshi_item'] as $key => $val){ ?>
						<li style="height:55px;overflow:visible">
							<ul>
								<li style="margin-left:3px;float:left;height:50px;padding-top:5px;">
									<a href="<?php echo urlShop('goods','index',array('goods_id'=> $val['goods_id']));?>"> 
										<img src="<?php echo thumb($val, 240);?>" style="width:50px;">
									</a>
								</li>
								<li style="height:50px;line-height:50px;margin-left:3px;float:left;white-space:nowrap;text-overflow:ellipsis;overflow: hidden;width:85px;"><?php echo $val['goods_name']; ?></li>
								<li style="height:50px;line-height:50px;margin-left:3px;float:left"><?php echo ncPriceFormatForList($val['goods_price']);?></li>
							</ul>
						</li>
					<?php } ?>
              </ul>
          </div>
      </div>
    <div class="proclamation">
      <ul class="tabs-nav">
        <li style="width:100%">
          <h3><?php echo $output['show_article']['notice']['ac_name'];?></h3>
        </li>
      </ul>
      <div class="tabs-panel">
        <ul class="mall-news">
          <?php if(!empty($output['show_article']['notice']['list']) && is_array($output['show_article']['notice']['list'])) { ?>
          <?php foreach($output['show_article']['notice']['list'] as $val) { ?>
          <li><i></i><a target="_blank" href="<?php echo empty($val['article_url']) ? urlShop('article', 'show',array('article_id'=> $val['article_id'])):$val['article_url'] ;?>" title="<?php echo $val['article_title']; ?>"><?php echo str_cut($val['article_title'],24);?> </a>
            <time>(<?php echo date('Y-m-d',$val['article_time']);?>)</time>
          </li>
          <?php } ?>
          <?php } ?>
        </ul>
      </div>
    </div>
      -->
  </div>
    -->
</div>
<!--HomeFocusLayout End-->

<div class="home-sale-layout wrapper">
  <div class="left-layout"> <?php echo $output['web_html']['index_sale'];?> </div>
  <?php if(!empty($output['xianshi_item']) && is_array($output['xianshi_item'])) { ?>
  <div class="right-sidebar">
    <div class="title">
      <h3><?php echo $lang['nc_xianshi'];?></h3>
    </div>
    <div id="saleDiscount" class="sale-discount">
      <ul>
        <?php foreach($output['xianshi_item'] as $val) { ?>
        <li>
          <dl>
            <dt class="goods-name"><?php echo $val['goods_name']; ?></dt>
            <dd class="goods-thumb"><a href="<?php echo urlShop('goods','index',array('goods_id'=> $val['goods_id']));?>"> <img src="<?php echo thumb($val, 240);?>"></a></dd>
            <dd class="goods-price"><?php echo ncPriceFormatForList($val['xianshi_price']); ?> <span class="original"><?php echo ncPriceFormatForList($val['goods_price']);?></span></dd>
            <dd class="goods-price-discount"><em><?php echo $val['xianshi_discount']; ?></em></dd>
            <dd class="time-remain" count_down="<?php echo $val['end_time']-TIMESTAMP;?>"><i></i><em time_id="d">0</em><?php echo $lang['text_tian'];?><em time_id="h">0</em><?php echo $lang['text_hour'];?> <em time_id="m">0</em><?php echo $lang['text_minute'];?><em time_id="s">0</em><?php echo $lang['text_second'];?> </dd>
            <dd class="goods-buy-btn"></dd>
          </dl>
        </li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <?php } ?>
</div>
<div class="wrapper">
  <div class="mt10">
    <div class="mt10"><?php echo loadadv(11,'html');?></div>
  </div>
</div>
<!--StandardLayout Begin--> 
<?php echo $output['web_html']['index'];?> 
<!--StandardLayout End-->
<!--StandardLayout Begin-->
<?php echo $output['web_html']['index_org'];?>
<!--StandardLayout End-->
<div class="wrapper">
  <div class="mt10"><?php echo loadadv(9,'html');?></div>
</div>
<!--link Begin-->
<!-- 友情链接
<div class="full_module wrapper">
  <h2><b><?php echo $lang['index_index_link'];?></b></h2>
  <div class="piclink">
    <?php if(is_array($output['$link_list']) && !empty($output['$link_list'])) {
		  	foreach($output['$link_list'] as $val) {
		  		if($val['link_pic'] != ''){
		  ?>
    <span><a href="<?php echo $val['link_url']; ?>" target="_blank"><img src="<?php echo $val['link_pic']; ?>" title="<?php echo $val['link_title']; ?>" alt="<?php echo $val['link_title']; ?>" width="88" height="31" ></a></span>
    <?php
		  		}
		 	}
		 }
		 ?>
    <div class="clear"></div>
  </div>
  <div class="textlink">
    <?php 
		  if(is_array($output['$link_list']) && !empty($output['$link_list'])) {
		  	foreach($output['$link_list'] as $val) {
		  		if($val['link_pic'] == ''){
		  ?>
    <span><a href="<?php echo $val['link_url']; ?>" target="_blank" title="<?php echo $val['link_title']; ?>"><?php echo str_cut($val['link_title'],16);?></a></span>
    <?php
		  		}
		 	}
		 }
		 ?>
    <div class="clear"></div>
  </div>
</div>-->
<!--link end-->

<div class="footer-line"></div>
<!--首页底部保障开始-->
<!--
<?php require_once template('layout/index_ensure');?>
-->
<!--首页底部保障结束-->
<!--StandardLayout Begin-->
<!-- 底部竖状定位条
<div class="nav_Sidebar">
<a class="nav_Sidebar_1" href="javascript:;" ></a>
<a class="nav_Sidebar_2" href="javascript:;" ></a>
<a class="nav_Sidebar_3" href="javascript:;" ></a>
<a class="nav_Sidebar_4" href="javascript:;" ></a>
<a class="nav_Sidebar_5" href="javascript:;" ></a>
<a class="nav_Sidebar_6" href="javascript:;" ></a> 
<a class="nav_Sidebar_7" href="javascript:;" ></a>
<a class="nav_Sidebar_8" href="javascript:;" ></a>
</div>-->
<!--StandardLayout End-->