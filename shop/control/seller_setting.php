<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('NlWxShop') or exit('Access Invalid!');

/**
 * Description of seller_setting
 * 卖家设置 控制器
 *   1.修改密码
 * 
 * @author Liyiquan
 */
class seller_settingControl extends BaseSellerControl{
    //put your code here
    public function __construct() {
        parent::__construct();
        // 载入语言文件
        Language::read('member_store_index');
    }
    
    /**
     * 修改密码
     */
    public function change_pwdOP() {
        // 验证提交表单操作
        if (chksubmit()) {
            // 根据member_id查询用户信息
            $seller_info = Model('member')->getMemberInfo(array('member_id' => $_SESSION['member_id']));
            // 验证旧密码是否填写正确
            if ($seller_info['member_passwd'] == md5($_POST['old_pwd'])) {
                // 验证成功，更新密码
                Model()->table('member')->where(array('member_id' => $_SESSION['member_id']))->update(array('member_passwd' => md5($_POST['new_pwd'])));
                // 发送提示信息
                showDialog('密码修改成功！','index.php?act=seller_setting&op=change_pwd','succ');
            } else {
                // 验证失败，发送提示信息
                showDialog('旧密码错误！','','error');
            }
        }
        // 左边菜单信息
        $this->left_menu();
        // 右边小导航信息
        $this->profile_menu('seller_setting');
        // 特殊字段，用来隐藏不必要的信息
        Tpl::output('pwd', TRUE);
        // 载入页面
        Tpl::showpage('seller_setting.change_pwd');
    }
    
    
    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key='') {
        $menu_array = array(
            1 => array(
                'menu_key'  => 'seller_setting',
                'menu_name' => '修改密码',
                'menu_url'  => 'index.php?act=seller_setting&op=change_pwd'
            )
        );
        Tpl::output('member_menu', $menu_array);
        Tpl::output('menu_key', $menu_key);
    }
    
    /**
     * 左边菜单
     */
    private function left_menu() {
        $left_menu = array(
            1 => array(
                'act'  => 'seller_setting',
                'op'   => 'change_pwd',
                'name' => '修改密码'
            )
        );
        Tpl::output('left_menu', $left_menu);
    }
}
