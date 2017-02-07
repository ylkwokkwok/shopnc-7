<?php
/**
 * 手机端令牌模型
 */

defined('InShopNC') or exit('Access Invalid!');

class mb_user_tokenModel extends Model{
    public function __construct(){
        parent::__construct('mb_user_token');
    }

    /**
	 * 查询
     *
	 * @param array $condition 查询条件
     * @return array
	 */
    public function getMbUserTokenInfo($condition) {
        return $this->where($condition)->find();
    }

    public function getMbUserTokenInfoByToken($token) {
        if(empty($token)) {
            return null;
        }
        return $this->getMbUserTokenInfo(array('token' => $token));
    }

	/**
	 * 新增
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function addMbUserToken($param){
        return $this->insert($param);	
	}
	
	/**
	 * 删除
	 *
	 * @param int $condition 条件
	 * @return bool 布尔类型的返回结果
	 */
	public function delMbUserToken($condition){
        return $this->where($condition)->delete();
	}
    /* zp@newland 添加开始 **/
    /* 时间：2017/02/06 **/
    /* 获取OPENID */
	public function getOpenId($condition){
        $model = Model();
        return $model->table('mb_user_token,member')
                ->join('left')
                ->on('mb_user_token.member_id = member.member_id')
                ->field('member.member_wx_id, member.member_id')
                ->select();
    }
    /* zp@newland 添加结束 **/
}

