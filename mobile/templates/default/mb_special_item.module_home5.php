<?php defined('NlWxShop') or exit('Access Invalid!'); ?>

<div class="index_block home1">
    <?php if(!empty($vv['title'])) {?>
    <div class="title"><?php echo $vv['title']; ?></div>
    <?php } ?>
    <div class="content">
        <div nctype="item_image" class="item">
            <a nctype="btn_item" href="javascript:;" data-type="<?php echo $vv['type']; ?>" data-data="<?php echo $vv['data']; ?>">
                <img nctype="image" src="<?php echo $vv['image1']; ?>" alt="">
            </a>
             <a nctype="btn_item" href="javascript:;" data-type="<?php echo $vv['type']; ?>" data-data="<?php echo $vv['data']; ?>">
                <img nctype="image" src="<?php echo $vv['image2']; ?>" alt="">
            </a>
             <a nctype="btn_item" href="javascript:;" data-type="<?php echo $vv['type']; ?>" data-data="<?php echo $vv['data']; ?>">
                <img nctype="image" src="<?php echo $vv['image3']; ?>" alt="">
            </a>
             <a nctype="btn_item" href="javascript:;" data-type="<?php echo $vv['type']; ?>" data-data="<?php echo $vv['data']; ?>">
                <img nctype="image" src="<?php echo $vv['image4']; ?>" alt="">
            </a>
        </div>
        