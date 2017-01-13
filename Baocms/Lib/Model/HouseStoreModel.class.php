<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 下午 3:27
 */
    class HouseStoreModel extends CommonModel
    {
        
        protected $pk = 'store_id';
            protected $tableName = 'house_store';
            protected $_validate=  array(
                array('store_name','require','店铺名必填'),
                array('store_name','','店铺名已存在',0,'unique',0),
                array('city_id','require','请选择所在城市'),
                array('lng','require','店铺经度不能为空'),
                array('lat','require','店铺纬度不能为空'),
             );
        //删除标示
        public $flag = array(
            'exist' => 0,
            'delete' => 1,
        );
    }