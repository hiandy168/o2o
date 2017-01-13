<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/25 0025
 * Time: 下午 7:23
 */

class HotelOrderModel extends RelationModel
{
    protected $_link =array(
            'order_room'=>array(
                'mapping_type'=>HAS_ONE,
                'class_name'=>'hotel_order_room',
                'foreign_key'=>'order_id',
            ),
            'hotel_info'=>array(
                'mapping_type'=>BELONGS_TO,
                'class_name'=>'hotel',
                'foreign_key'=>'hotel_id',
            ),
            'user_info'=>array(
                'mapping_type'  => BELONGS_TO,
                'class_name'    => 'Users',
                'foreign_key'   => 'user_id',
                'mapping_fields'=>'nickname,user_id'
                ),
            'room_list'=>array(
                'mapping_type'=>MANY_TO_MANY,
                'class_name'=>'hotel_room',
                'relation_foreign_key'=>'room_id',
                'foreign_key'=>'order_id',
                'relation_table'=>'bao_hotel_order_room',
            ),
            'logs_info'=>array(
                'mapping_type'  => HAS_ONE,
                'class_name'    => 'HotelOrderCancelLogs',
                'foreign_key'   => 'order_id',
                )
        );
/*
 * 作者：刘弢
 */

    protected $_validate = array(
        array('plan_time','require','入住时间必需！',1,'',1),  
        array('planleave_time','require','离店时间必需！',1,'',1),  
        array('phone','require','电话号码必需！',1,'',1),  
    );

    protected $_auto = array(
        array('code','crate_code',1,'callback'),
        array('create_time','time',1,'function'),
        array('create_ip','get_client_ip',1,'function'),
    );

    protected function crate_code(){
        $code = rand_string(9,1);
        $res = $this->where(array('code'=>$code))->find();
        if ($res){
            $this->crate_code();
        }else {
            return $code;
        }
    }


}