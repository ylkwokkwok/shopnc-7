<?php

defined('NlWxShop') or exit('Access Invalid!');

class milk_storeControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
        
        /* lyq@newland 添加开始 **/
        /* 时间：2015/09/21     **/
        // 快速订奶入口已关闭时
        if (C('quick_order_milk') == '0') {
            // ajax响应关闭结果
            output_data(array('quick_closed' => true));
            exit;
        }
        /* lyq@newland 添加结束 **/
    }

    /**
     * 附近的自取点
     */
    public function nearby_storesOp() {
        // 获取用户位置信息
        $location_info = Model('member')->where(array('member_id' => $this->member_info['member_id']))
                                        ->field('longitude,latitude,location_update_time')
                                        ->find();
        // 用户最后定位时间距现在超过30分钟
        if (time() - intval($location_info['location_update_time']) > 1800) {
            // 提示定位过期
            output_data(array('expired' => true));
            exit;
        }
        // 获取店铺列表并输出
        $this->output_store_list($location_info['latitude'], $location_info['longitude'], 1000);
    }

    /**
     * 搜索周围的自取点
     */
    public function search_nearby_storesOp() {
        // 根据关键字检索地址信息
        $output = file_get_contents('http://api.map.baidu.com/place/v2/search?q='.urlencode($_POST['keyword'])
                                    .'&region='.urlencode('大连').'&output=json&ak=pSLSjNvheZxFvpGTqLWjFnqV');
        $obj = json_decode($output);
        // 未获取到地址信息
        if (empty($obj->results) || empty($obj->results[0]->location)) {
            // 提示 暂无记录
            output_data(array('store_list' => null, 'position_desc' => '定位失败'));
            exit;
        }
        // 获取店铺列表并输出
        $this->output_store_list($obj->results[0]->location->lat, $obj->results[0]->location->lng, 1000);
    }
    
    /**
     * 定位到当前位置
     */
    public function search_position_storesOp() {
        // 获取转换后的经纬度（百度API需要的经纬度）
        $location_info = $this->geoconvert($_POST['lng'].','.$_POST['lat']);
        // 获取店铺列表并输出
        $this->output_store_list($location_info->y, $location_info->x, 1000);
    }
    /* zp@newland 添加开始 **/
    /* 时间：2017/02/06 **/
    /**
     * 获取店铺列表并输出
     * @param type $lat 纬度
     * @param type $lng 经度
     * @param type $apart 相距范围 单位：米
     */
    private function output_store_list($lat, $lng, $apart = 0) {
        $mst_self_receive = Model('mst_self_receive');
        $store_list = $mst_self_receive->store_list($lat, $lng, $apart);
        // 添加log
        $this->add_log('【查询自取点】自取点列表：'.(empty($store_list)?'未查询到附近的自取点':serialize($store_list)));
        
        output_data(array('store_list' => $store_list, 'position_desc' => $this->geocoder_render_reverse($lat, $lng)));
    }
    /* zp@newland 添加结束 **/
    /**
     * 获取奶品相关商品列表
     */
    public function get_product_listOp() {
        // 取得地址信息 
        $address_info = Model('address')->getDefaultAddressInfo(array('member_id'=>$this->member_info['member_id']));
        // 来瓶取得 
        $model_goods = Model('goods');
        $milk_gc_list = $model_goods->getMilkProduct();
        // 奶品列表
        $product_list = array();
        foreach ($milk_gc_list as $value) {
            /* lyq@newland 修改开始 **/
            /* 时间：2015/09/17 - 2015/09/18    **/
            // 获取相应奶品下的商品信息
            /* zp@newland 添加开始 **/
            /* 时间：2017/02/06 **/
            $goods_list = $model_goods->getMilkInfo($_POST['type'],$value['O_Number']);
            /* zp@newland 添加结束 **/
            /* lyq@newland 修改结束 **/
            // 商品信息不为空时
            if (!empty($goods_list)) {
                // 增加奶品信息
                $product_list[] = array(
                    'milk_cd' =>  $value['O_Number'],
                    'milk_name' => $value['O_Name'],
                    'goods' => $goods_list
                );
            }
        }
        // 返回奶品列表
        output_data(array('product_list' => $product_list, 'address_info'=>$address_info));
    }
    
    /**
     * 获取客户编号
     *   根据log_id获取订单数据
     *   根据订单数据查询客户编号
     */
    public function get_customer_cdOp() {
        // 记录ID
        $log_id = $_POST['log_id'];
        // 根据订奶记录ID查询未付款订单信息
        $data = Model('milk_order_log')->where('log_id = '.$log_id.' and pay_time is not null and assign_flag = 1')->field('order_data')->find();
        // 已分配奶卡时
        if ($data) {
            // 反序列化订奶记录数据，获得订单信息
            $order_data = unserialize($data['order_data']);
            /* zp@newland 添加开始 **/
            /* 时间：2017/02/06 **/
            $condition = array(
                'member_id' => $order_data['member_id'],
                'customer_name' => $order_data['name'],
                'address' => $order_data['address'],
                'tel' => $order_data['tel'],
                'customer_cd' => array(array('like',$order_data['self_receive_spot_cd'].'%')),
            );
            $model = Model('mst_customer');
            $result = $model->get_milk_order_info($condition);
            /* zp@newland 添加结束 **/
            // 查询到的客户编号
            $customer_cd = $result[0]['customer_cd'];
        }
        // 未分配奶卡
        else {
            // 无客户编号
            $customer_cd = '';
        }
        // 返回客户编号
        output_data(array('customer_cd' => $customer_cd));
    }
    
    /**
     * 客户选择自取点时的操作
     *   自取点编号存储到session中(废除)
     *   log记录
     */
    public function select_storeOp() {
        //$_SESSION['self_receive_spot_cd'] = $_POST['self_receive_spot_cd'];
        $this->add_log('【选择自取点】自取点编号：'.$_POST['self_receive_spot_cd']);
    }
    
    /**
     * 添加log
     * @param type $log_text log文字信息
     */
    private function add_log($log_text) {
        // wap端日志信息
        $log_info = array(
            'log_type' => '2',
            'log_date' => date('Y-m-d H:i:s'),
            'log_text' => '会员【'.$this->member_info['member_id'].'】,'.$log_text
        );
        // 插入日志
        Model()->table('log')->insert($log_info);
    }
    
    /**
     * 逆地理编码服务
     * @param type $lat 纬度
     * @param type $lng 经度
     * @return type 位置：街道+街道号码
     */
    private function geocoder_render_reverse($lat, $lng) {
        // 百度 逆地理编码服务
        $output = file_get_contents('http://api.map.baidu.com/geocoder/v2/?ak=pSLSjNvheZxFvpGTqLWjFnqV&callback=renderReverse&location='.$lat.','.$lng.'&output=json&pois=0');
        // 解析响应对象
        $obj = json_decode(rtrim(ltrim($output,'renderReverse&&renderReverse('),')'));
        // 返回街道+街道号码
        return $obj->result->addressComponent->street.$obj->result->addressComponent->street_number;
    }
    
    /**
     * 转换经纬度坐标
     *   from GPS设备获取的角度坐标
     *   to   bd09ll(百度经纬度坐标)
     * @param type $location <经度>,<纬度>
     * @return type 转换结果
     */
    private function geoconvert($location) {
        // 百度 坐标转换服务
        $output = file_get_contents('http://api.map.baidu.com/geoconv/v1/?coords='.$location.'&from=1&to=5&ak=pSLSjNvheZxFvpGTqLWjFnqV');
        // 解析响应对象
        $obj = json_decode($output);
        // 返回转换结果
        return $obj->result[0];
    }
}
