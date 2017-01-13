<?php

/* 
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshifavoritesModel extends CommonModel{
    protected $pk   = 'favorites_id';
    protected $tableName =  'chaoshi_favorites';
    
    public function check($store_id,$user_id){
        $data = $this->find(array('where'=>array('store_id'=>(int)$store_id,'user_id'=>(int)$user_id)));
        return $this->_format($data);
    }
    
}