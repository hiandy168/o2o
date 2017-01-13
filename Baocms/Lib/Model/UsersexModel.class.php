<?php
class  UsersexModel extends  CommonModel{
     protected $pk   = 'user_id';
     protected $tableName =  'users_ex';
    
     public function getUserex($user_id){
         $user_id = (int)$user_id;
         $data = $this->find($user_id);
         if(empty($data)){
             $data = array(
                 'user_id'  =>$user_id,
                 'last_uid' => 0,
                 'views'    => 0
             );
             $this->add($data);
         }
         return $data;
     }
    
}