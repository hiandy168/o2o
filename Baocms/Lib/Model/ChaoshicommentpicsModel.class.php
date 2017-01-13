<?php



class ChaoshicommentpicsModel extends CommonModel{
    protected $pk   = 'pic_id';
    protected $tableName =  'chaoshi_comment_pics';
    
    public function upload($comment_id,$photos){
        $comment_id = (int)$comment_id;
        $this->delete(array("where"=>array('comment_id'=>$comment_id)));
        foreach($photos as $val){
            $this->add(array('pic'=>$val,'comment_id'=>$comment_id));
        }
        return true;
    }

    public function getPics($order_id){
        $order_id = (int)$order_id;
        return $this->where(array('comment_id'=>$comment_id))->select();
    }
    
}