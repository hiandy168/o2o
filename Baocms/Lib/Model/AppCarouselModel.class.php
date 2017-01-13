<?php
class AppCarouselModel extends CommonModel{
    protected $_validate = array(
        array('link_store_id','require','店铺id不能为空',3),
        array('photo','require','图片不能为空',3),
        array('bg_date','require','开始时间不能为空',3),
        array('end_date','require','结束时间不能为空',3),
    );
    
    public function get_type() {
        return array(            
            'hotel' => '酒店',
            'meishi' => '美食',
            'house' => '房产',
            'chaoshi'=>'超市',
            'ele'=>'外卖',
//            'houserent'=>'租房',
//            'housetwo'=>'二手房'
        );
    }
    
    public function get_model_name() {
        return array(            
            'hotel' => 'Hotel',
            'meishi' => 'Meishi',
            'house' => 'House',
            'chaoshi'=>'Chaoshi',
            'ele'=>'Ele',
//            'houserent'=>'HouseRent',
//            'housetwo'=>'HouseTwo'
        );
    }
}