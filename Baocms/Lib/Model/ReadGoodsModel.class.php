<?php



class ReadGoodsModel extends CommonModel{
    protected $pk   = 'goods_id';
    protected $tableName =  'goods';
	 protected $_validate = array(
        array( ),
        array( ),
        array( )
    ); 
    protected $connection = 'DB_R_CONFIG';
    
    
   
}