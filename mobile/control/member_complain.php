<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('NlWxShop') or exit('Access Invalid!');

/* lyq@newland 添加开始   **/
/* 时间：2015/05/27       **/
/* 功能ID：SHOP009        **/

/**
 * Description of member_complain
 * 交易投诉
 *
 * @author Liyiquan
 */
class member_complainControl extends mobileMemberControl {
    // 定义投诉状态常量 
    // 10-新投诉
    const STATE_NEW = 10;
    // 20-投诉通过转给被投诉人
    const STATE_APPEAL = 20;
    // 30-被投诉人已申诉
    const STATE_TALK = 30;
    // 40-提交仲裁
    const STATE_HANDLE = 40;
    // 99-已关闭
    const STATE_FINISH = 99;
    
    //投诉是否通过平台审批
    // 1未通过
    const STATE_UNACTIVE = 1;
    // 2通过
    const STATE_ACTIVE = 2;
    
    //put your code here
    function __construct() {
        parent::__construct();
    }
    
    /**
     * 投诉列表 页面显示
     */
    public function complain_listOp() {
        
        $model_complain = Model('complain');
        /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $model_store = Model('store');
        $temp = $model_store->goods_vip_list();
        if (!empty($temp)){
                foreach ($temp as $value){
                        $stroe_vip_list[] = $value['store_id'];
                }
        }else{
            $stroe_vip_list[] = '';
        }
        /* wqw@newland 添加结束   **/
        // 查询条件
        $condition = array();
        // 查询条件 排序
        $condition['order'] = 'complain_state asc,complain_id desc';
        // 查询条件 原告ID
        $condition['accuser_id'] = $this->member_info['member_id'];
        // 判断投诉状态
        switch(intval($_GET['select_complain_state'])) {
            case 1:
                // 为结束
                $condition['progressing'] = 'true';
                break;
            case 2:
                // 已结束
                $condition['finish'] = 'true';
                break;
            default :
                $condition['state'] = '';
        }
        // 获取投诉列表
        $list = $model_complain->get_complain($condition, $this->page);
        // 判断投诉列表是否为空
        if (!empty($list) && is_array($list)) {
            // 不为空
            // 添加投诉状态说明
            foreach ($list as &$complain) {
                switch (intval($complain['complain_state'])) {
                    case 10:
                        $complain['complain_state_content'] = '新投诉';
                        break;
                    case 20:
                        $complain['complain_state_content'] = '待申诉';
                        break;
                    case 30:
                        $complain['complain_state_content'] = '对话中';
                        break;
                    case 40:
                        $complain['complain_state_content'] = '待仲裁';
                        break;
                    case 99:
                        $complain['complain_state_content'] = '已关闭';
                        break;
                }
            }
        }
        // 获取总页数
        $page_count = $model_complain->gettotalpage();
        // ajax响应数组
        $array_data = array();
        // 添加投诉列表到ajax响应数组
        $array_data['list'] = $list;
        // 获得投诉商品列表
        $goods_list = $model_complain->getComplainGoodsList($list);
        // 判断商品列表是否为空
        if (!empty($goods_list) && is_array($goods_list)) {
            // 不为空
            // 获取商品图片
            foreach ($goods_list as &$value) {
                // 商品图片完整url
                $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            }
        }
        // 添加投诉商品列表到ajax响应数组
        $array_data['goods_list'] = $goods_list;
         /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $array_data['stroe_vip_list'] = $stroe_vip_list;
        // 返回响应数据
        output_data($array_data, mobile_page($page_count));
    }
    
    /**
     * 投诉详细 页面显示
     */
    public function complain_detailOp() {
        // ajax响应数组
        $array_data = array();
        /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $model_store = Model('store');
        $temp = $model_store->goods_vip_list();
        if (!empty($temp)){
                foreach ($temp as $value){
                        $stroe_vip_list[] = $value['store_id'];
                }
        }else{
            $stroe_vip_list[] = '';
        }
        /* wqw@newland 添加结束   **/
        // 投诉ID
        $complain_id = intval($_GET['complain_id']);
        // 获取投诉详细信息
        $complain_info = $this->get_complain_info($complain_id);
        // 添加投诉详细信息到ajax响应数组
        $array_data['complain_info'] = $complain_info;
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $complain_info['order_id'];
        
        $model_refund = Model('refund_return');
        // 获取订单信息
        $order_list = $model_refund->getRightOrderList($condition, $complain_info['order_goods_id']);
        // 生成图片url
        foreach ($order_list['goods_list'] as &$value) {
            // 商品图片完整url
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
        }
        // 添加订单信息到ajax响应数组
        $array_data['order_list'] = $order_list;
        
        // 获取物流列表
        $express_list  = rkcache('express',true);
        // 添加物流列表到ajax响应数组
        $array_data['express_list'] = $express_list;
         /* wqw@newland 修改开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $array_data['stroe_vip_list'] = $stroe_vip_list;
        /* wqw@newland 修改结束   **/
        // 返回响应数据
        output_data($array_data);
    }
    
    /**
     * 添加交易投诉 页面显示
     */
    public function add_complainOp() {
        // ajax响应数组
        $array_data = array();
        /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $model_store = Model('store');
        $temp = $model_store->goods_vip_list();
        if (!empty($temp)){
                foreach ($temp as $value){
                        $stroe_vip_list[] = $value['store_id'];
                }
        }else{
            $stroe_vip_list[] = '';
        }
        /* wqw@newland 添加结束   **/
        // 订单ID
        $order_id = intval($_GET['order_id']);
        // 订单商品ID
        $goods_id = intval($_GET['goods_id']);
        // 参数验证
        if ($order_id < 1 || $goods_id < 1) {
            output_error('参数错误');
        }
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $order_id;
        
        $model_refund = Model('refund_return');
        // 获取订单信息
        $order_info = $model_refund->getRightOrderList($condition, $goods_id);
        // 生成图片url
        foreach ($order_info['goods_list'] as &$value) {
            // 商品图片完整url
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
        }
        // 添加订单信息到ajax响应数组
        $array_data['order'] = $order_info;
        
        // 获取物流列表
        $express_list  = rkcache('express',true);
        // 添加物流列表到ajax响应数组
        $array_data['express_list'] = $express_list;
        
        $model_order = Model('order');
        // 检查订单是否可以投诉
        $if_complain = $model_order->getOrderOperateState('complain',$order_info);
        if($if_complain < 1) {
            output_error('参数错误');
        }
        // 检查是不是正在进行投诉
        if($this->check_complain_exist($goods_id)) {
            output_error('您已经投诉了该订单请等待处理');
        }

        /* 整理退款投诉主题开始 **/
        
        $model_complain_subject = Model('complain_subject');
        // 参数 空
        $param = array();
        // 获取投诉类型
        $complain_subject_list = $model_complain_subject->getActiveComplainSubject($param);
        // 验证投诉主题是否存在
        if(empty($complain_subject_list)) {
            output_error('投诉主题不存在请联系管理员');
        }
        
        // 数据关系整理 订单信息中需要包含订单内商品所继承的商品信息
        $order_info['extend_order_goods'] = $order_info['goods_list'];
        // 数据关系整理 向订单列表中添加订单信息
        $order_list[$order_id] = $order_info;
        // 获取订单相关的退款退货信息
        $order_list = $model_refund->getGoodsRefundList($order_list);
        // 退款投诉
        if(intval($order_list[$order_id]['extend_complain'][$goods_id]) == 1) {
            // 投诉主题
            $complain_subject = Model()->table('complain_subject')->where(array('complain_subject_id'=> 1))->select();
            // 向投诉类型列表中添加退款投诉主题
            $complain_subject_list = array_merge($complain_subject, $complain_subject_list);
        }
        
        /* 整理退款投诉主题结束 **/
        
        // 添加投诉类型列表到ajax响应数组
        $array_data['subject_list'] = $complain_subject_list;
        // 添加订单商品ID到ajax响应数组
        $array_data['goods_id'] = $goods_id;
        /* wqw@newland 添加开始   　**/
        /* 时间：2015/06/08       **/
        /* 功能ID：ADMIN006       **/
        $array_data['stroe_vip_list'] = $stroe_vip_list;
        /* wqw@newland 添加结束   **/
        // 返回响应数据
        output_data($array_data);
    }
    
    /**
     * 保存交易投诉
     */
    public function save_complainOp() {
        // 投诉信息
        $input = array();
        // 投诉信息 订单ID
        $input['order_id'] = intval($_POST['input_order_id']);
        // 投诉信息订单商品ID
        $input['order_goods_id'] = intval($_POST['input_goods_id']);
        // 查询条件
        $condition = array();
        // 查询条件 会员ID
        $condition['buyer_id'] = $this->member_info['member_id'];
        // 查询条件 订单ID
        $condition['order_id'] = $input['order_id'];
        
        $model_order = Model('order');
        // 获取订单信息
        $order_info = $model_order->getOrderInfo($condition);
        // 检查订单是否可以投诉
        $if_complain = $model_order->getOrderOperateState('complain',$order_info);
        if($if_complain < 1) {
            output_error('参数错误');
        }
        // 检查是不是正在进行投诉
        if($this->check_complain_exist($input['order_goods_id'])) {
            output_error('您已经投诉了该订单请等待处理');
        }
        // 拆分投诉主题参数，获得投诉主题ID、投诉主题内容
        list($input['complain_subject_id'],$input['complain_subject_content']) = explode(',',trim($_POST['input_complain_subject']));
        // 投诉信息 投诉内容
        $input['complain_content'] = trim($_POST['input_complain_content']);
        // 投诉信息 原告id
        $input['accuser_id'] = $order_info['buyer_id'];
        // 投诉信息 原告名称
        $input['accuser_name'] = $order_info['buyer_name'];
        // 投诉信息 被告id
        $input['accused_id'] = $order_info['store_id'];
        // 投诉信息 被告名称
        $input['accused_name'] = $order_info['store_name'];
        // 投诉信息 投诉时间
        $input['complain_datetime'] = time();
        // 投诉信息 投诉状态
        $input['complain_state'] = self::STATE_NEW;
        // 投诉信息 投诉是否通过平台审批
        $input['complain_active'] = self::STATE_UNACTIVE;

        $model_complain = Model('complain');
        // 保存投诉信息
        $state = $model_complain->saveComplain($input);
        if ($state) {
            output_data('success');
        } else {
            output_data('error');
        }
    }
    
    /**
     * 取消交易投诉
     */
    public function cancel_complainOp() {
        // 投诉ID
        $complain_id = intval($_GET['complain_id']);
        // 获取投诉信息
        $complain_info = $this->get_complain_info($complain_id);
        // 判断投诉状态 状态为10 即新投诉时才可以取消
        if(intval($complain_info['complain_state']) === 10) {
            $model_complain = Model('complain');
            // 根据投诉ID取消投诉
            $model_complain->dropComplain(array('complain_id' => $complain_id));
            // 取消投诉成功
            output_data('success');
        } else {
            // 状态不符，取消投诉失败
            output_data('faild');
        }
    }
    
    /**
     * 根据投诉ID获取投诉对话
     */
    public function get_complain_talkOp() {
        // 投诉ID
        $complain_id = intval($_POST['complain_id']);
        // 投诉信息
        $complain_info = $this->get_complain_info($complain_id);
        
        $model_complain_talk = Model('complain_talk');
        // 参数
        $param = array();
        // 参数 投诉ID
        $param['complain_id'] = $complain_id;
        // 投诉对话列表
        $complain_talk_list = $model_complain_talk->getComplainTalk($param);
        // 响应数组
        $talk_list = array();
        // 偏移量
        $i = 0;
        if(!empty($complain_talk_list) && is_array($complain_talk_list)) {
            foreach($complain_talk_list as $talk) {
                // 发言人类型(1-投诉人/2-被投诉人/3-平台)
                $talk_list[$i]['css'] = $talk['talk_member_type'];
                // 对话发表时间
                $talk_list[$i]['talk'] = date("Y-m-d H:i:s",$talk['talk_datetime']);
                // 拼接对话角色
                switch($talk['talk_member_type']){
                    case 'accuser':
                        $talk_list[$i]['talk'] .= '投诉人';
                        break;
                    case 'accused':
                        $talk_list[$i]['talk'] .= '被投诉店铺';
                        break;
                    case 'admin':
                        $talk_list[$i]['talk'] .= '管理员';
                        break;
                    default:
                        $talk_list[$i]['talk'] .= '未知';
                }
                // 发言状态(1-显示/2-不显示)
                if(intval($talk['talk_state']) === 2) {
                    $talk['talk_content'] = '<该对话被管理员屏蔽>';
                }
                // 拼接对话
                $talk_list[$i]['talk'].= '('.$talk['talk_member_name'].')说:'.$talk['talk_content'];
                $i++;
            }
        }
        // 转码
        if (strtoupper(CHARSET) == 'GBK') {
            $talk_list = Language::getUTF8($talk_list);
        }
        // 返回响应数组
        if(empty($talk_list)){
            output_data('none');
        } else {
            output_data($talk_list);
        }
    }
    
    /*
     * 根据投诉ID发布投诉对话
     */
    public function publish_complain_talkOp() {
        // 投诉ID
        $complain_id = intval($_POST['complain_id']);
        // 投诉对话内容
        $complain_talk = trim($_POST['complain_talk']);
        // 计算对话长度
        $talk_len = strlen($complain_talk);
        // 对话长度在 0-255范围内
        if($talk_len > 0 && $talk_len < 255) {
            // 投诉信息
            $complain_info = $this->get_complain_info($complain_id);
            // 投诉状态
            $complain_state = intval($complain_info['complain_state']);
            // 检查投诉是否是可发布对话状态
            if($complain_state > self::STATE_APPEAL && $complain_state < self::STATE_FINISH) {
                $model_complain_talk = Model('complain_talk');
                // 对话信息数组
                $param = array();
                // 对话信息 投诉ID
                $param['complain_id'] = $complain_id;
                // 对话信息 对话人ID
                $param['talk_member_id'] = $complain_info['accuser_id'];
                // 对话信息 对话人名字
                $param['talk_member_name'] = $complain_info['accuser_name'];
                // 对话信息 对话人类型
                $param['talk_member_type'] = $complain_info['member_status'];
                // 对话信息内容转码
                if (strtoupper(CHARSET) == 'GBK') {
                    $complain_talk = Language::getGBK($complain_talk);
                }
                // 对话信息 信息内容
                $param['talk_content'] = $complain_talk;
                // 对话信息 发言状态(1-显示/2-不显示)
                $param['talk_state'] =1;
                // 对话信息 对话管理员，屏蔽对话人的id
                $param['talk_admin'] = 0;
                // 对话信息 对话发表时间
                $param['talk_datetime'] = time();
                // 保存对话并返回状态
                if($model_complain_talk->saveComplainTalk($param)) {
                    output_data('success');
                } else {
                    output_error('对话提交失败');
                }
            } else {
                output_error('投诉状态已改变');
            }
        } else {
            // 对话长度超限
            output_error('对话长度请在255字以内');
        }
    }
    
    /*
     * 处理用户申请仲裁
     */
    public function apply_handleOp() {
        // 投诉ID
        $complain_id = intval($_POST['input_complain_id']);
        // 投诉信息
        $complain_info = $this->get_complain_info($complain_id);
        // 投诉状态
        $complain_state = intval($complain_info['complain_state']);
        // 检查当前是不是投诉状态
        if($complain_state < self::STATE_TALK || $complain_state === 99) {
            output_error('参数错误');
        }
        // 更新信息
        $update_array = array();
        // 更新信息 投诉状态 提交仲裁
        $update_array['complain_state'] = self::STATE_HANDLE;
        // 条件
        $where_array = array();
        // 条件 投诉ID
        $where_array['complain_id'] = $complain_id;
        
        $model_complain = Model('complain');
        // 保存投诉信息
        $complain_id = $model_complain->updateComplain($update_array,$where_array);
        output_data('success');
    }
    
    /**
     * 获取投诉信息
     *   根据投诉ID获取投诉信息
     * @param type $complain_id 投诉ID
     * @return array 投诉信息
     */
    private function get_complain_info($complain_id) {
        $model_complain = Model('complain');
        // 根据投诉ID获取投诉信息
        $complain_info = $model_complain->getoneComplain($complain_id);
        // 判断是否是当前会员的投诉
        if($complain_info['accuser_id'] != $this->member_info['member_id']) {
            // 不是，返回错误信息
            output_error('参数错误');
        }
        // 会员状态 原告
        $complain_info['member_status'] = 'accuser';
        // 投诉状态文本
        $complain_info['complain_state_text'] = $this->get_complain_state_text($complain_info['complain_state']);
        // 返回投诉信息
        return $complain_info;
    }
    
    /**
     * 获得投诉状态文本
     * @param type $complain_state 投诉状态
     * @return string 状态文本
     */
    private function get_complain_state_text($complain_state) {
        switch(intval($complain_state)) {
            case self::STATE_NEW:
                return '新投诉';
                break;
            case self::STATE_APPEAL:
                return '待申诉';
                break;
            case self::STATE_TALK:
                return '对话中';
                break;
            case self::STATE_HANDLE:
                return '待仲裁';
                break;
            case self::STATE_FINISH:
                return '已关闭';
                break;
            default:
                // 未找到状态文本，参数错误
                output_error('参数错误');
        }
    }
    
    /**
     * 检查投诉是否已经存在
     * @param type $goods_id 订单商品ID
     * @return bool 投诉是否存在
     */
    private function check_complain_exist($goods_id) {
        $model_complain = Model('complain');
        // 参数
        $param = array();
        // 参数 订单商品ID
        $param['order_goods_id'] = $goods_id;
        // 参数 申请投诉的会员ID
        $param['accuser_id'] = $this->member_info['member_id'];
        // 参数 投诉未关闭
        $param['progressing'] = 'ture';
        // 投诉是否存在 true/false
        return $model_complain->isExist($param);
    }
}

/* lyq@newland 添加结束   **/