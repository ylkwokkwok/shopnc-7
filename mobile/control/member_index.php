<?php

/**
 * 我的商城
 *
 *
 *
 *
 */
defined('NlWxShop') or exit('Access Invalid!');

class member_indexControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 我的商城
     */
    public function indexOp() {
        $member_info = array();
        /* lyq@newland 修改开始   * */
        /* 时间：2015/07/13       * */
        // 只有宽度大于10才截取
        if (mb_strwidth($this->member_info['member_name'], 'utf-8') > 12) {
            // 此处设定从0开始截取，取10个追加...，使用utf8编码
            // 注意追加的...也会被计算到长度之内
            $member_info['user_name'] = mb_strimwidth($this->member_info['member_name'], 0, 12, '...', 'utf-8');
        } else {
            $member_info['user_name'] = $this->member_info['member_name'];
        }

        /* lyq@newland 修改结束   * */

        /* lyq@newland 修改开始   * */
        /* 时间：2015/06/16       * */
        // 会员头像
        $member_info['avator'] = getMemberAvatarForUrl($this->member_info['member_avatar']);
        /* lyq@newland 修改结束   * */
        /* lyq@newland 修改开始   * */
        /* 时间：2015/07/08       * */
        // 推广积分
        $member_info['extend_points'] = $this->member_info['extend_points'];
        /* lyq@newland 修改结束   * */
        $member_info['point'] = $this->member_info['member_points'];
        // 会员经验值
        $member_info['member_exppoints'] = $this->member_info['member_exppoints'];

        $member_info['member_truename'] = $this->member_info['member_truename'];
        /* 周政@newland 添加开始   * */
        /* 时间：2016/03/02      * */
        /* 添加查询会员邮箱 手机号 性别 * */
        // 会员邮箱
        $member_info['member_email'] = $this->member_info['member_email'];
        // 会员手机号
        $member_info['member_mobile'] = $this->member_info['member_mobile'];
        // 会员性别
        $sex = $this->member_info['member_sex'];
        if ($sex == 1) {
            $member_info['member_sex'] = "男";
        } else if ($sex == 2) {
            $member_info['member_sex'] = "女";
        } else {
            $member_info['member_sex'] = "-";
        }
        /* zz@newland 添加结束   * */

        // 用户等级相应会员名
        $level_name = array(
            'V0' => '青铜会员',
            'V1' => '白银会员',
            'V2' => '黄金会员',
            'V3' => '钻石会员',
        );
        // 用户等级配置
        $member_grade = Model()->table('setting')->where('`name` = "member_grade"')->find();
        foreach (unserialize($member_grade['value']) as $value) {
            if (intval($member_info['member_exppoints']) >= $value['exppoints']) {
                $member_info['level'] = $level_name[$value['level_name']];
            }
        }

        $member_info['predepoit'] = $this->member_info['available_predeposit'];
        /* lyq@newland 添加开始   * */
        /* 时间：2015/05/14        * */
        /* 功能ID：SHOP005        * */
        // VIP会员标识
        $member_info['is_vip'] = $this->member_info['is_vip'];
        /* lyq@newland 添加结束   * */

        /* lyq@newland 添加开始   * */
        /* 时间：2015/05/15        * */
        /* 功能ID：SHOP012        * */
        // 消息推送状态
        $member_info['allow_push'] = $this->member_info['allow_push'];
        /* lyq@newland 添加结束   * */






        /* lyq@newland 添加开始   * */
        /* 时间：2015/06/05       * */
        // 幸运号（会员ID）
        $member_info['luck_num'] = $this->member_info['member_id'];
        /* lyq@newland 添加结束   * */

        /* zly@newland添加待付款、待收货数目显示开始* */
        /* 时间：2015/07/24                       * */
        $state_num = Model('order');
        // 会员ID
        $unreceived['buyer_id'] = $this->member_info['member_id'];
        // 未付款数量
        $member_info['not_pay_num'] = $state_num->not_pay_num($unreceived);
        // 待收货数量   
        $member_info['not_received_num'] = $state_num->not_received_num($unreceived);

        /* zz@newland添加待评价货数目显示开始* */
        /* 时间：2016/3/2                       * */
        // 待评价数量
        $member_info['not_valuate_num'] = $state_num->not_valuate_num($unreceived);
        /* zz@newland添加结束* */

        /* zly@newland添加待付款、待收货数目显示结束* */
        output_data(array('member_info' => $member_info));
    }

    
    
    /* zz@newland添加修改个人信息 开始* */
        /* 时间：2016/3/22                       * */
        // 修改个人信息   
    
    public function member_editOp() {
        $model_member = Model('member');
        $update = array();
        $where = $_POST['member_id'];

        $update['member_name'] = $_POST['user_name'];
        $update['member_truename'] = $_POST['true_name'];
        $update['member_mobile'] = $_POST['member_mobile'];
        $update['member_email'] = $_POST['member_email'];
        $update['member_sex'] = $_POST['sex'];

        $model_member->table('member')->where(array('member_id' => $where))->update($update);
        output_data($update);
    }
    /* zz@newland添加结束* */
    
    
    /* lyq@newland 添加开始   * */
    /* 时间：2015/05/15        * */
    /* 功能ID：SHOP012        * */

    /**
     * 更改消息推送状态
     */
    public function change_push_flagOp() {
        // 需要更新的消息推送状态
        $push_flg_to = intval($_POST['push_flg_to']);
        $model = Model('member');
        // 更新消息推送状态
        $model->table('member')
                ->where(array(
                    'member_id' => $this->member_info['member_id']
                ))
                ->update(array(
                    'allow_push' => $push_flg_to
        ));
        // 返回当前消息推送状态
        output_data(array('allow_push' => $push_flg_to));
    }

    /* lyq@newland 添加结束   * */

    /**
     * 
     * @param type $string
     * @param type $start
     * @param type $length
     * @return type
     */
    function gb_substr($string, $start, $length) {
        if (strlen($string) > $length) {
            $str = null;
            $len = $start + $length;
            for ($i = $start; $i < $len; $i++) {
                if (ord(substr($string, $i, 1)) > 0xa0) {
                    $str.=substr($string, $i, 2);
                    $i++;
                } else {
                    $str.=substr($string, $i, 1);
                }
            }
            return $str . '...';
        } else {
            return $string;
        }
    }

}
