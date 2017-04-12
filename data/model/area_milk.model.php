<?php
/**
 * 奶品地区模型
 *
 
 */
defined('NlWxShop') or exit('Access Invalid!');
class area_milkModel extends Model {

    public function __construct() {
        parent::__construct('area_milk');
    }

    /**
     * 获取地址总量 
     *
     * @return mixed
     */
    public function getAreaMilkCount($condition = array()) {
        return $this->table('area_milk')->where($condition)->count();
    }

    /**
     * 获取地址列表
     *
     * @return mixed
     */
    public function getAreaMilkList($condition = array(), $fields = '*', $order = '', $group = '') {
        return $this->table('area_milk')->where($condition)->field($fields)->limit(false)->group($group)->order($order)->select();
    }

    /**
     * 获取地址详情
     *
     * @return mixed
     */
    public function getAreaMilkInfo($condition = array(), $fileds = '*') {
        return $this->table('area_milk')->where($condition)->field($fileds)->find();
    }

    /**
     * 更新地址详情
     * 
     * @param array $condition 更新条件 
     * @param array $update 更新内容 
     *
     * @return mixed
     */
    public function updateAreaMilk($condition = array(), $update = array()) {
        return $this->table('area_milk')->where($condition)->update($update);
    }

    /**
     * 更新地址详情
     * 
     * @param array $insert 添加内容 
     *
     * @return mixed
     */
    public function insertAreaMilk($insert = array()) {
        return $this->table('area_milk')->insert($insert);
    }

    /**
     * 取得配送奶站配送区域列表 
     * 
     * @param unknown $condition 查询条件 
     * @param string $pagesize 每页数 
     * @param string $fields 显示字段 
     * @param string $order 排序 
     * 
     * @return array 奶站信息 
     */
    public function getAreaStationList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null) {
        return $this->table('area_station')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 取得配送奶站配送区域
     * 
     * @param unknown $condition 查询条件 
     * @param string $fields 显示字段 
     * 
     * @return array 奶站信息 
     */
    public function getAreaStationInfo($condition = array(), $fields = '*') {
        return $this->table('area_station')->where($condition)->field($fields)->find();
    }

    /**
     * 添加配送奶站配送区域 
     * 
     * @param unknown $condition 查询条件 
     * @param string $fields 显示字段 
     */
    public function insertAreaStation($data = array()) {
        return $this->table('area_station')->insert($data);
    }

    /**
     * 更新配送奶站配送区域
     * 
     * @param unknown $condition 查询条件 
     * @param string $update 更新内容  
     */
    public function updateAreaStation($condition = array(), $update) {
        return $this->table('area_station')->where($condition)->update($update);
    }

    /**
     * 取得配送奶站配送区域数据
     * 
     * @param unknown $condition 查询条件 
     * @param string $fields 显示字段 
     * 
     * @return array 奶站信息 
     */
    public function getAreaStationCount($condition = array(), $fields = '*') {
        return $this->table('area_station')->where($condition)->count();
    }
}
