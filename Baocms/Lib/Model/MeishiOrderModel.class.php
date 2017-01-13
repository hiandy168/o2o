<?php
class MeishiOrderModel extends RelationModel
{
    protected $_link=array(
        'order_goods'=>array(
            'mapping_type'=>HAS_MANY,
            'class_name'=>'meishi_order_goods',
            'foreign_key'=>'order_id',
        ),
        'meishi_info'=>array(
            'mapping_type'=>BELONGS_TO,
            'class_name'=>'Meishi',
            'foreign_key'=>'store_id',
        ),
        'user_info'=>array(
            'mapping_type'=>BELONGS_TO,
            'class_name'=>'Users',
            'foreign_key'=>'user_id',
        ),
        'goods_info'=>array(
            'mapping_type'=>MANY_TO_MANY,
            'class_name'=>'MeishiGoods',
            'relation_foreign_key'=>'goods_id',
            'foreign_key'=>'order_id',
            'relation_table'=>'bao_meishi_order_goods',
        )
);
   
}