<?php

/**
 * 我的地址
 *
 *
 */
defined('NlWxShop') or exit('Access Invalid!');

class member_addressControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 地址列表
     */
    public function address_listOp() {
        $model_address = Model('address');
        $address_list = $model_address->getAddressList(array('member_id' => $this->member_info['member_id']));
        output_data(array('address_list' => $address_list));
    }

    /* zz@newland 添加开始 * */
    /* 时间：2016/03/08    * */

    //默认地址修改
    public function default_addressOp() {
        $model_address = Model('address');
        $where = array('member_id' => $this->member_info['member_id'], 'is_default' => 1);
        $model_address->table('address')->editAddress(array('is_default' => 0), $where);
        $result = $model_address->table('address')->editAddress(array('is_default' => 1), array('address_id' => $_POST['set_id']));
        output_data('设置成功');
    }

    /* zz@newland 添加结束 * */

    /**
     * 地址详细信息
     */
    public function address_infoOp() {
        $address_id = intval($_POST['address_id']);

        $model_address = Model('address');

        $condition = array();
        $condition['address_id'] = $address_id;
        $address_info = $model_address->getAddressInfo($condition);
        if (!empty($address_id) && $address_info['member_id'] == $this->member_info['member_id']) {
            output_data(array('address_info' => $address_info));
        } else {
            output_error('地址不存在');
        }
    }

    /**
     * 删除地址
     */
    public function address_delOp() {
        $address_id = intval($_POST['address_id']);

        $model_address = Model('address');

        $condition = array();
        $condition['address_id'] = $address_id;
        $condition['member_id'] = $this->member_info['member_id'];

        $is_default = $model_address->is_default($condition);
        /* zz@newland 添加开始 * */
        /* 时间：2016/03/09    * */
        //查看添加的地址是不是唯一的地址，如果是唯一设为默认
        $model_address->delAddress($condition);
        if ($is_default['is_default'] == '1') {
            $model_address->default_Address($condition['member_id']);
        }
        /* zz@newland 添加结束 * */
        output_data('1');
    }

    /**
     * 新增地址
     */
    public function address_addOp() {
        $model_address = Model('address');

        $address_info = $this->_address_valid();


        $result = $model_address->addAddress($address_info);
        if ($result) {
            $is_defaule = $model_address->getAddressCount(array('member_id' => $address_info['member_id']));
            if ($is_defaule == '1') {
                $model_address->table('address')->editAddress(array('is_default' => 1), array('member_id' => $address_info['member_id']));
            }
            output_data(array('address_id' => $result));
        } else {
            output_error('保存失败');
        }
    }

    /**
     * 编辑地址
     */
    public function address_editOp() {
        $address_id = intval($_POST['address_id']);

        $model_address = Model('address');

        //验证地址是否为本人
        $address_info = $model_address->getOneAddress($address_id);
        if ($address_info['member_id'] != $this->member_info['member_id']) {
            output_error('参数错误');
        }

        $address_info = $this->_address_valid();

        $result = $model_address->editAddress($address_info, array('address_id' => $address_id));
        if ($result) {
            output_data('1');
        } else {
            output_error('保存失败');
        }
    }

    /**
     * 验证地址数据
     */
    private function _address_valid() {
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input" => $_POST["true_name"], "require" => "true", "message" => '姓名不能为空'),
            array("input" => $_POST["area_info"], "require" => "true", "message" => '地区不能为空'),
            array("input" => $_POST["address"], "require" => "true", "message" => '地址不能为空'),
            array("input" => $_POST['tel_phone'] . $_POST['mob_phone'], 'require' => 'true', 'message' => '联系方式不能为空')
        );
        $error = $obj_validate->validate();
        if ($error != '') {
            output_error($error);
        }

        $data = array();
        $data['member_id'] = $this->member_info['member_id'];
        $data['true_name'] = $_POST['true_name'];
        $data['area_id'] = intval($_POST['area_id']);
        $data['city_id'] = intval($_POST['city_id']);
        $data['area_info'] = $_POST['area_info'];
        $data['address'] = $_POST['address'];
        $data['tel_phone'] = $_POST['tel_phone'];
        $data['mob_phone'] = $_POST['mob_phone'];


        return $data;
    }

    /**
     * 地区列表
     */
    public function area_listOp() {
        $area_id = intval($_POST['area_id']);

        $model_area = Model('area');

        $condition = array();
        if ($area_id > 0) {
            $condition['area_parent_id'] = $area_id;
        } else {
            $condition['area_deep'] = 1;
        }
        $area_list = $model_area->getAreaList($condition, 'area_id,area_name');
        output_data(array('area_list' => $area_list));
    }

}
