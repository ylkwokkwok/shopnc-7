<?php
/**
 * 订单管理
 * @author zp@newland
 * 2017/02/06 添加
 */
defined('InShopNC') or exit('Access Invalid!');
class extractModel extends Model {
    /**
     * 获取收款人信息、金额等
     */
    public function getExtractInfo($condition){
        $model = Model();
        return $model->table('extarct,member')
                        ->join('left')
                        ->on('extarct.extract_user = member.member_id')
                        ->field('extarct.extract_id,sum(extarct.extract_money) extract_money,member.member_id,member.member_wx_id')
                        ->where($condition)
                        ->group('member.member_id')
                        ->select();
    }
}
