<?php defined('NlWxShop') or exit('Access Invalid!');?>
<?php
    /* lyq@newland 添加开始 **/
    /* 时间：2015/06/08     **/
    // 新增 商家密码修改
    /* lyq@newland 添加结束 **/
?>

<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
    <form method="post"  action="index.php?act=seller_setting&op=change_pwd" id="seller_pwd_form">
        <input type="hidden" name="form_submit" value="ok" />
        <dl>
          <dt>旧密码<?php echo $lang['nc_colon'];?></dt>
          <dd>
            <input class="w200 text" name="old_pwd" type="text"  id="old_pwd" value="" maxlength="20"/>
          </dd>
        </dl>
        <dl>
          <dt>新密码<?php echo $lang['nc_colon'];?></dt>
          <dd>
            <input class="w200 text" name="new_pwd" type="text"  id="new_pwd" value="" maxlength="20"/>
          </dd>
        </dl>
        <dl>
          <dt>再次输入<?php echo $lang['nc_colon'];?></dt>
          <dd>
              <input class="text w200" name="new_pwd_conf" type="text"  id="new_pwd_conf" value="" maxlength="20"/>
          </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['store_goods_class_submit'];?>" id="pwd_form_submit"/></label>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function(){
        // 表单验证
        $('#seller_pwd_form').validate({
            onsubmit: true,
            submitHandler:function(form){
                // ajax提交表单
                ajaxpost('seller_pwd_form', '', '', 'onerror')
            },
            rules : {
                old_pwd: {
                    required: true,
                    minlength: 6,
                    maxlength: 20
                },
                new_pwd: {
                    required: true,
                    minlength: 6,
                    maxlength: 20
                },
                new_pwd_conf: {
                    required: true,
                    minlength: 6,
                    maxlength: 20,
                    equalTo:'#new_pwd'
                }
            },
            messages : {
                old_pwd: {
                    required: '必须输入！',
                    minlength: '密码不能少于6个字符！',
                    maxlength: '密码不能多于20个字符！'
                },
                new_pwd: {
                    required: '必须输入！',
                    minlength: '密码不能少于6个字符！',
                    maxlength: '密码不能多于20个字符！'
                },
                new_pwd_conf: {
                    required: '必须输入！',
                    minlength: '密码不能少于6个字符！',
                    maxlength: '密码不能多于20个字符！',
                    equalTo:'两次输入的新密码不一样！'
                }
            }
        });
    });
</script>
