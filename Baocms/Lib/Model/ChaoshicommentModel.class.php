<?php

/*
 * 作者：刘弢
 * 日期: 2016.5.31
 */

class ChaoshicommentModel extends RelationModel {

    protected $pk = 'comment_id';
    protected $tableName = 'chaoshi_comment';
    protected $insertFields = array('score', 'speed', 'contents', 'reply');
    
    protected $_validate = array(
        array('score',array(1,5),'评分范围错误！',1,'between',1),
        array('contents','require','评论内容为必须！',1,'',1)
    );
    protected $_auto = array(
        array('create_time','time',1,'function'),
        array('create_ip','get_client_ip',1,'function'),
    );
    protected $_link = array(
        'comment_pics'=>array(
            'mapping_type'=>HAS_MANY,
            'class_name'=>'chaoshi_comment_pics',
            'foreign_key'=>'comment_id',
        ),
        'user_info'=>array(
            'mapping_type'=>BELONGS_TO,
            'class_name'=>'users',
            'foreign_key'=>'user_id',
        ),
        'order_info'=>array(
            'mapping_type'=>BELONGS_TO,
            'class_name'=>'chaoshi_order',
            'foreign_key'=>'order_id',
        ),
        'chaoshi_info'=>array(
            'mapping_type'=>BELONGS_TO,
            'class_name'=>'chaoshi',
            'foreign_key'=>'store_id',
        ),
        'product_list'=>array(
            'mapping_type'=>MANY_TO_MANY,
            'class_name'=>'chaoshi_product',
            'relation_foreign_key'=>'product_id',
            'foreign_key'=>'order_id',
            'relation_table'=>'bao_chaoshi_order_product',
        ),
         
    );
    public function commentCheck($order_id, $user_id) {
        $data = $this->find(array('where' => array('order_id' => (int) $order_id, 'user_id' => (int) $user_id)));
        return $this->_format($data);
    }

}