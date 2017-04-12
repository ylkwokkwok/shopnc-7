<?php

/**
 * 自取点
 *
 * * */
defined('NlWxShop') or exit('Access Invalid!');

class self_receiveControl extends SystemControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 查询自取点 ajax
     */
    public function search_self_receiveOp() {
        $sql = 'SELECT ';
        $sql.= '	self_receive_spot_cd, ';    // 查询：自取点编号
        $sql.= '	self_receive_nm, ';         // 查询：自取点名称
        $sql.= '	address ';                  // 查询：自取点地址
        $sql.= 'FROM ';
        $sql.= '	mst_self_receive ';
        $sql.= 'WHERE ';
        $sql.= '	(self_receive_nm LIKE "%'.$_POST['query'].'%" '; // 条件：自取点名称
        $sql.= '	OR address LIKE "%'.$_POST['query'].'%") ';      // 条件：自取点地址
        $sql.= '	AND delete_flag = "0" ';      // 条件：未删除
        // 已选自取点不为空
        if (!empty($_POST['self_cds'])) {
            // 条件：不包含已选的自取点
            $sql.= '	AND self_receive_spot_cd NOT IN ('.html_entity_decode($_POST['self_cds']).') ';
        }
        // 执行查询
        $result = Model()->query($sql);
        // 整理数据
        $arr = array('list' => empty($result) ? FALSE : $result);
        // 返回数据
        echo json_encode($arr);
//        echo json_encode(array('sql' => $sql));
    }
}
