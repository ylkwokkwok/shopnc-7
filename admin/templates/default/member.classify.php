<?php defined('InShopNC') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_member_classify_manager']?></h3>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td>
            <ul>
                 <li><?php echo $lang['member_classify_index_help1'];?></li>
            </ul>
        </td>
      </tr>
    </tbody>
  </table>
  <form method="post" id="form_member" action="index.php?act=member&op=classifySave">
    <input type="hidden" name="form_submit" value="ok" />
    <textarea id="json" name="json" style="display:none" ></textarea>
    <table class="table tb-type2 nobdb">
        <tbody id="info-list">
            <?php if(count($output['list']) > 0){ ?>
                <?php foreach ($output['list'] as $key => $val){ ?>
                    <tr>
                        <td><?php echo $key + 1; ?></td>
                        <td><?php echo $lang['member_classify_name']; ?></td>
                        <td><input type="text" class="name" name="name[]" value="<?php echo $val['name']; ?>"></td>
                        <td><?php echo $lang['member_classify_discount']; ?></td>
                        <td><input type="number" class="discount" name="discount[]" value="<?php echo $val['discount']; ?>" ></td>
                        <td><input type="button" class="delete" value="删除" <?php if($key == 0){ ?>hidden<?php } ?> ></td>
                    </tr>
                <?php } ?>
            <?php }else{  ?>
                <tr>
                    <td>1</td>
                    <td><?php echo $lang['member_classify_name']; ?></td>
                    <td><input type="text" class="name" name="name[]"></td>
                    <td><?php echo $lang['member_classify_discount']; ?></td>
                    <td><input type="number" class="discount" name="discount[]"></td>
                    <td><input type="button" class="delete" value="删除" hidden ></td>
                </tr>
            <?php } ?>
        </tbody>
        <tbody>
            <tr>
                <td colspan="6">
                    <input type="button" class="add" value="新增">
                    <input type="button" class="save" value="保存">
                </td>
            </tr>
        </tbody>
    </table>
  </form>
</div>
<script>
$(function(){
    $('.add').click(function(){
        $('#info-list tr:last').after(function(){
            return $('#info-list tr:eq(0)').get(0).outerHTML;
        });
        $('#info-list tr:last input[type=text],#info-list tr:last input[type=number]').val('');
        $('#info-list tr:last input[type=button]').show();
        $('#info-list tr:last .error').remove();
        count();
    });
    $(window.document).on('click','.delete',function(){
        $(this).parents('tr').remove();
        count();
    });
    function count(){
        $('#info-list tr').each(function(index){
            $(this).find('td:eq(0)').text(index + 1);
        });
    }
    $('.save').click(function(){
        var flg = 0;
        $('#info-list tr').each(function(){
            if($(this).find('td:eq(2) input').val() == ''){
                flg = 1;
                $(this).find('.error').remove();
                $(this).find('td:eq(1)').append('<label class="error">用户分类不能为空</label>');
            }else{
                $(this).find('.error').remove();
            }
        });
        if(flg == 1){
            return false;
        }
        $('#form_member').submit();
    });
});
</script>
