<?php
/**
 * 订单管理
 */
defined('InShopNC') or exit('Access Invalid!');
class mst_self_receiveModel extends Model {
    public function __construct(){
        parent::__construct('mst_self_receive');
    }
    /**
     * 获取店铺列表并输出
     */
    public function store_list($lat, $lng, $apart = 0){
        $condition = array('delete_flag'=>0);
        $model = Model();
        $model->table_prefix = '';
        $model->table('mst_self_receive')->field('mst_self_receive.*,round(
                6378.138 * 2 * asin(sqrt(
                pow(sin((latitude * pi() / 180 - '.$lat.' * pi() / 180) / 2),2)
                + cos(latitude * pi() / 180) * cos('.$lat.' * pi() / 180)
                * pow(sin((longitude * pi() / 180 - '.$lng.' * pi() / 180) / 2),2)
            )) * 1000) AS apart
        ');
        if (!empty($_POST['self_cds'])) {
            $self_cds = explode(',', $_POST['self_cds']);
            // 循环拼接双引号
            foreach ($self_cds as $key => $value) {
                $self_cds[$key] = '"'.$value.'"';
            }
            $condition['self_receive_spot_cd'] = array('in',implode(',', $self_cds));
        }else if($apart !== 0){
            $model->having('apart <= ' . $apart);
        }
        $model->order('apart ASC');
        $rs = $model->select();
        $array = array();
        $mst_self_authority = Model('mst_self_authority');
        foreach ($rs as $key => $val){
            if($mst_self_authority->checkExist($val['self_receive_spot_cd'])){
                array_push($array,$val);
            }
        }
        return $array;
    }
}
