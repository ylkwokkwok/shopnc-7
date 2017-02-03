<?php
/**
 * 投诉模型
 */
defined('InShopNC') or exit('Access Invalid!');
class complainModel extends model{
    public function __construct() {
        parent::__construct('complain');
    }

    /**
     * 投诉数量
     * @param array $condition
     * @return int
     */
    public function getComplainCount($condition) {
        return $this->where($condition)->count();
    }

	/*
	 * 构造条件
	 */
	private function getCondition($condition){
		$condition_str = '' ;
        if(!empty($condition['complain_id'])) {
            $condition_str.= " and  complain_id = '{$condition['complain_id']}'";
        }
        if(!empty($condition['complain_state'])) {
            $condition_str.= " and  complain_state = '{$condition['complain_state']}'";
        }
        if(!empty($condition['accuser_id'])) {
            $condition_str.= " and  accuser_id = '{$condition['accuser_id']}'";
        }

        if(APP_ID == 'mobile' || APP_ID == 'wx'){
            /*zly@newland 添加检索条件：投诉次数    开始  **/
            /*时间：2015/06/11                           **/
            /*功能ID:ADMIN008                            **/
            // 检索条件种类为投诉次数
            if (!empty($condition['accused_id']) && is_array($condition['accused_id'])) {
                $str_accused_id = '';
                // 整理店家
                foreach ($condition['accused_id'] as $accused_arr) {
                    $str_accused_id .= $accused_arr['accused_id'] . ',';
                }
                // 截取店家除最后一位
                $accused_id = substr($str_accused_id, 0, strlen($str_accused_id) - 1);
                // 生成检索条件
                $condition_str.= " and accused_id in ($accused_id)";
                /*zly@newland 添加检索条件：投诉次数     结束 **/
            } elseif (!empty($condition['accused_id'])) {
                $condition_str.= " and  accused_id = '{$condition['accused_id']}'";
            } elseif (is_array ($condition['accused_id']) && count($condition['accused_id']) == 0) {
                $condition_str.= " and 1 = 2 ";
            }
        }else{
            if(!empty($condition['accused_id'])) {
                $condition_str.= " and  accused_id = '{$condition['accused_id']}'";
            } elseif (!empty($condition['accused_id'])) {
                $condition_str.= " and  accused_id = '{$condition['accused_id']}'";
            } elseif (is_array ($condition['accused_id']) && count($condition['accused_id']) == 0) {
                $condition_str.= " and 1 = 2 ";
            }
        }

        if(!empty($condition['order_id'])) {
            $condition_str.= " and  order_id = '{$condition['order_id']}'";
        }
        if(!empty($condition['order_goods_id'])) {
            $condition_str.= " and  order_goods_id = '{$condition['order_goods_id']}'";
        }
        if(!empty($condition['accused_progressing'])) {
            $condition_str.= " and complain_state > 10 and complain_state < 90 ";
        }
        if(!empty($condition['progressing'])) {
            $condition_str.= " and  complain_state < 90 ";
        }
        if(!empty($condition['finish'])) {
            $condition_str.= " and  complain_state = 99 ";
        }
        if(!empty($condition['accused_finish'])) {
            $condition_str.= " and  complain_state = 99 and complain_active = 2 ";
        }
        if(!empty($condition['accused_all'])) {
            $condition_str.= " and  complain_active = 2 ";
        }
        if(!empty($condition['complain_accuser'])) {
            $condition_str.= " and  accuser_name like '%".$condition['complain_accuser']."%'";
        }
        if(!empty($condition['complain_accused'])) {
            $condition_str.= " and  accused_name like '%".$condition['complain_accused']."%'";
        }
        if(!empty($condition['complain_subject_content'])) {
            $condition_str.= " and  complain_subject_content like '%".$condition['complain_subject_content']."%'";
        }
        if(!empty($condition['complain_datetime_start'])) {
            $condition_str.= " and  complain_datetime > '{$condition['complain_datetime_start']}'";
        }
        if(!empty($condition['complain_datetime_end'])) {
            $end = $condition['complain_datetime_end'] + 86400;
            $condition_str.= " and  complain_datetime < '$end'";
        }
		return $condition_str;
    }

	/*
	 * 增加
	 * @param array $param
	 * @return bool
	 */
	public function saveComplain($param){
		return Db::insert('complain',$param) ;
	}

	/*
	 * 更新
	 * @param array $update_array
	 * @param array $where_array
	 * @return bool
	 */
	public function updateComplain($update_array, $where_array){
		$where = $this->getCondition($where_array) ;
		return Db::update('complain',$update_array,$where) ;
    }

	/*
	 * 删除
	 * @param array $param
	 * @return bool
	 */
	public function dropComplain($param){
		$where = $this->getCondition($param) ;
		return Db::delete('complain', $where) ;
	}

	/*
	 *  获得投诉列表
	 *  @param array $condition
	 *  @param obj $page 	//分页对象
	 *  @return array
	 */
	public function getComplain($condition='',$page='', $where = '', $complain_state = '') {
        if(APP_ID == 'mobile' || APP_ID == 'wx'){
            /*zly@newland 添加检索条件：投诉次数 开始**/
            /*时间：2015/06/11                      **/
            /*功能ID:ADMIN008                       **/
            if (isset($where) && $where != '') {
                // 查询对应投诉状态下投诉次数ID
                $sql = "SELECT
                           ss.accused_id
                        FROM
                        (
                            SELECT
                                    accused_id,
                                    count(*) AS Thum
                            FROM
                                    ".DBPRE."complain
                            WHERE
                                    complain_state = $complain_state
                            GROUP BY
                                    accused_id
                        )   AS ss
                        WHERE
                            ss.Thum = $where";
                // 整理被投诉店家
                $accused_id_arr = $this->query($sql);
                if (is_null($accused_id_arr)) {
                    $accused_id_arr = array();
                }
                $condition['accused_id'] = $accused_id_arr;
            }
            /*zly@newland 添加检索条件：投诉次数  结束**/
        }
        $param = array() ;
        $param['table'] = 'complain' ;
        $param['where'] = $this->getCondition($condition);
        $param['order'] = $condition['order'] ? $condition['order']: ' complain_id desc ';
        return Db::select($param,$page);
	}

	/* lyq@newland 添加开始   * */
    /* 时间：2015/05/27        * */
    /* 功能ID：SHOP009         * */

    /**
     * 获取投诉列表
     *   WAP端
     * @param type $condition 查询条件
     * @param type $pagesize 每页显示数
     * @param type $field 字段
     * @return type
     */
    public function get_complain($condition, $pagesize = '', $field = '*') {
        // where条件
        $where = '1=1 ' . $this->getCondition($condition);
        // order条件
        $order = $condition['order'] ? $condition['order'] : ' complain_id desc ';
        // 获取投诉列表
        $list = $this->table('complain')->field($field)->where($where)->page($pagesize)->order($order)->select();
        // 返回投诉列表
        return $list;
    }

    /* lyq@newland 添加结束   * */
	/*
	 *  获得投诉商品列表
	 *  @param array $complain_list
	 *  @return array
	 */
	public function getComplainGoodsList($complain_list) {
        $goods_ids = array();
	    if (!empty($complain_list) && is_array($complain_list)) {
    	    foreach ($complain_list as $key => $value) {
    	        $goods_ids[] = $value['order_goods_id'];//订单商品表编号
    	    }
	    }
	    $condition = array();
	    $condition['rec_id'] = array('in', $goods_ids);
        return $this->table('order_goods')->where($condition)->key('rec_id')->select();
	}

	/*
	 *  检查投诉是否存在
	 *  @param array $condition
	 *  @param obj $page 	//分页对象
	 *  @return array
	 */
	public function isExist($condition='') {
        $param = array() ;
        $param['table'] = 'complain' ;
        $param['where'] = $this->getCondition($condition);
        $list = Db::select($param);
        if(empty($list)) {
            return false;
        } else {
            return true;
        }
	}

    /*
     *   根据id获取投诉详细信息
     */
    public function getoneComplain($complain_id) {
        $param = array() ;
    	$param['table'] = 'complain';
    	$param['field'] = 'complain_id' ;
    	$param['value'] = intval($complain_id);
    	return Db::getRow($param) ;
    }
	/**
	 * 总数
	 *
	 */
	public function getCount($condition) {
		$condition_str	= $this->getCondition($condition);
		$count	= Db::getCount('complain',$condition_str);
		return $count;
	}

    /* zly@newland 投诉名单开始 **/
    /* 时间：2015/06/01            **/
    /* 功能ID：ADMIN008            **/
    /**
     * 投诉名单
     * @param type $condition 检索条件
     * @param type $order 排序方式
     * @param type $where 检索条件：投诉次数
     * @param type $downlaod_state 投诉状态
     * @return type 投诉名单信息
     */
    public function dowmload_complain_list($condition='', $order='',$where='',$downlaod_state='') {
        // 查询对应投诉状态下投诉次数ID
        if (isset($where) && $where != '') {
            $sql = "
                SELECT
                   ss.accused_id
                FROM
                (
                    SELECT
                            accused_id,
                            count(*) AS Thum
                    FROM
                            ".DBPRE."complain
                    WHERE
                            complain_state = $downlaod_state
                    GROUP BY
                            accused_id
                )   AS ss
                WHERE
                    ss.Thum = $where";
            // 整理被投诉店家
            $accused_id_arr = $this->query($sql);
            if (is_null($accused_id_arr)) {
                $accused_id_arr = array();
            }
            $condition['accused_id'] = $accused_id_arr;
        }
        $param = array();
        // 整理需要下载的字段
        $param['field'] = (
                'order_id,accuser_name,accused_name,complain_subject_content,'
                . 'complain_content,complain_datetime,'
                . 'complain_handle_datetime,appeal_message,appeal_datetime,'
                . 'final_handle_message,final_handle_datetime,complain_active'
                );
        // 规定从complain表里查询
        $param['table'] = 'complain';
        // 整理查询条件
        $param['where'] = $this->getCondition($condition);
        // 排序方式
        $param['order'] = $order;
        // 返回查询数据
        $param['limit'] = FALSE;
        return Db::select($param);
    }
    /* zly@newland 投诉名单结束 * */
}
