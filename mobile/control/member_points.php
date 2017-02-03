<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('NlWxShop') or exit('Access Invalid!');

/* lyq@newland 添加开始   **/
/* 时间：2015/05/29       **/
/* 功能ID：SHOP013        **/

/* lyq@newland 修改   **/
/* 时间：2015/06/05   **/
/* 功能ID：SHOP013    **/
// 改变被赠送会员的查询/修改条件 member_name -> member_id

/* lyq@newland 修改   **/
/* 时间：2015/07/08   **/
// 会员金米 ->> 金米

/**
 * Description of member_points
 * 金米相关
 *   金米转赠
 * 
 * @author Liyiquan
 */
class member_pointsControl extends mobileMemberControl {
    //put your code here
    function __construct() {
        parent::__construct();
    }
    
    /**
     * 金米转赠
     */
    public function send_pointsOp() {
        $model_member = Model('member');
        // 接收 被赠送会员的幸运号
        $receiver_luck_num = trim($_POST['receiver_luck_num']);
        // 接收 赠送金米数
        $points = intval($_POST['points']);
        
        try {
            // 事务开始
            $model_member->beginTransaction();
            
            // 根据 会员ID 获取 会员信息
            $member_info = $model_member->getMemberInfo(array('member_id' => $this->member_info['member_id']));
            // 避免数据更新不及时，重新验证金米数
            if (intval($member_info['extend_points']) - $points < 0) {
                throw new Exception('金米数有变动！');
            }
            // 根据 被赠送会员的幸运号 获取 被赠送会员的信息
            $receiver_info = $model_member->getMemberInfo(array('member_id' => $receiver_luck_num));
            // 判断被赠送会员是否存在
            if (empty($receiver_info)) {
                throw new Exception('被赠送会员不存在！');
            }
            
            // 更新 当前会员的金米数 减少
            $sender_result = $model_member->execute('UPDATE `' . DBPRE . 'member`'
                                                    . ' SET extend_points = extend_points - ' . $points
                                                    . ' WHERE member_id = ' . $this->member_info['member_id']);
            // 添加 当前会员金米变更log
            //$this->_add_points_log($member_info, $receiver_info, $points, 'send', $model_member);

            // 更新 目标会员的金米数 增加
            $receiver_result = $model_member->execute('UPDATE `' . DBPRE . 'member`'
                                                    . ' SET extend_points = extend_points + ' . $points
                                                    . ' WHERE member_id = ' . $receiver_luck_num);
            if ($sender_result === FALSE || $receiver_result === FALSE) {
                throw new Exception('赠送失败！');
            }
            // 添加 目标会员金米变更log
            //$this->_add_points_log($member_info, $receiver_info, $points, 'receive', $model_member);

            // 事务结束
            $model_member->commit();
            
            // 转赠成功
            output_data('success');

        } catch (Exception $e) {
            // 数据库操作异常，回退数据库操作
            $model_member->rollback();
            // 返回错误信息
            output_error($e->getMessage());
        }
    }
    
    /**
     * 添加金米操作日志
     * @param array $sender_info 赠送者 会员信息
     * @param array $receiver_info 接收者者 会员信息
     * @param int $points 金米数
     * @param string $stage 操作
     * @param model(obj) $model 模型对象
     */
    private function _add_points_log($sender_info, $receiver_info, $points, $stage, $model) {
        // 需要插入数据
        $add_info = array();
        if ($stage == 'send') {
            // pl_memberid 会员编号
            $add_info['pl_memberid'] = $sender_info['member_id'];
            // pl_membername 会员名称
            $add_info['pl_membername'] = $sender_info['member_name'];
            // pl_points 金米数负数表示扣除
            $add_info['pl_points'] = -$points;
            // pl_desc 操作描述 金米转赠  赠送/接收
            $add_info['pl_desc'] = "转赠金米给会员 ".$receiver_info['member_name'];
        } elseif ($stage == 'receive') {
            // pl_memberid 会员编号
            $add_info['pl_memberid'] = $receiver_info['member_id'];
            // pl_membername 会员名称
            $add_info['pl_membername'] = $receiver_info['member_name'];
            // pl_points 金米数负数表示扣除
            $add_info['pl_points'] = $points;
            // pl_desc 操作描述 金米转赠  赠送/接收
            $add_info['pl_desc'] = "接收会员 ".$sender_info['member_name']." 的金米转赠";
        }
        // pl_addtime 添加时间
        $add_info['pl_addtime'] = time();
        // pl_stage 操作阶段 send/receive
        $add_info['pl_stage'] = $stage;
        
        // 执行插入数据
        $model->table('points_log')->insert($add_info);
    }
}
