<?php
/**
 * 订单管理
 */
defined('InShopNC') or exit('Access Invalid!');
class mst_customer_cardModel extends Model {
    public function __construct(){
        parent::__construct('mst_customer_card');
    }
    public function get_valid_milk_cards($used_milk_card_list, $milk_cd, $card_num){
        $sql = 'SELECT ';
        $sql.= '    MIN(milk_card_cd) start_range, ';
        $sql.= '    MAX(milk_card_cd) end_range ';
        $sql.= 'FROM ';
        $sql.= '    ( ';
        $sql.= '        SELECT ';
        $sql.= '            milk_card_cd, ';
        $sql.= '            rn, ';
        $sql.= '            milk_card_cd - rn AS diff ';
        $sql.= '        FROM ';
        $sql.= '            ( ';
        $sql.= '                SELECT ';
        $sql.= '                    milk_card_cd ,@milk_card_cd :=@milk_card_cd + 1 rn ';
        $sql.= '                FROM ';
        $sql.= '                    mst_milk_card , ';
        $sql.= '                    (SELECT @milk_card_cd := 0) AS milk_card_cd ';
        $sql.= '                WHERE ';
        $sql.= '                    mst_milk_card.milk_cd = "'.$milk_cd.'" ';
        $sql.= '                AND mst_milk_card.active_flag = "0" ';
        $sql.= '                AND ( ';
        $sql.= '                    mst_milk_card.owner_user = "" ';
        $sql.= '                    OR mst_milk_card.owner_user IS NULL ';
        $sql.= '                ) ';
        $sql.= '                AND mst_milk_card.delete_flag = "0" ';
        // 已分配的奶卡列表 不为空时
        if (!empty($used_milk_card_list)) {
            // 查询区间中排除已分配的奶卡
            $sql.= '            AND milk_card_cd NOT IN ('.implode(',', $used_milk_card_list).')  ';
        }
        $sql.= '            ) AS b ';
        $sql.= '    ) AS c ';
        $sql.= 'GROUP BY ';
        $sql.= '    diff ';
        $sql.= 'HAVING end_range - start_range + 1 >= '.$card_num.' ';
        $sql.= 'LIMIT 1 ';
        return Model()->query($sql);
    }

    public function queryAllItem($condition){
        $this->table_prefix = '';
        return $this->where($condition)->select();
    }

    public function updateAction($array){
        $this->table_prefix = '';
        $this->update($array);
    }
}
