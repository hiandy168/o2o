<?php



class ComStoreOpenAuthModel extends CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'com_store_open_auth';
    protected $_validate=  array(
        array('com_name','require','公司名不能为空'),
        array('business_license','require','营业执照不能为空'),
    );
	


}