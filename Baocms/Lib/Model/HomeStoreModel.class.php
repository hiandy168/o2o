<?php
/*
 * 首页推荐模型
 * 作者：刘弢
 * QQ：473139299
 */
class HomeStoreModel extends CommonModel{
    protected $_validate = array(
        array('type',array('meishi','hotel','house'),'所属版块不存在',0,'in'),
        array('orderby','check_order','排序id必须为正整数',1,'callback'),
    );
    public function check_is_home($store_id, $type) {
        $res = $this->where(array('store_id'=>$store_id, 'type'=>$type))->find();
        if ($res){
            return 1;
        }
        return 0;
    }
    public function cancel_home($store_id, $type) {
        $res = $this->where(array('store_id'=>$store_id, 'type'=>$type))->delete();
        return $res;
    }
    public function get_type() {
        return array(
            'ele' => '外卖',
            'chaoshi' => '超市',
            'meishi' => '美食',
            'hotel' => '酒店',
            'house' => '房产',
        );
    }
    protected function check_order($orderby) {
        if ($orderby > 0){
            return true;
        }else {
            return false;
        }
    }
}