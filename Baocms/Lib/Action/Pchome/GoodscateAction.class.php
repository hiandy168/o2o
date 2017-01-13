<?php



class GoodscateAction extends CommonAction {
    
     public function _initialize() {
        parent::_initialize();
        $this->goods_cate_mod =D("GoodsCate");
    }
    public function select_goods_cate_view($cid){
        if($cid){
         $cate = $this->goods_cate_mod->where(array('parent_id'=>$cid))->select();
         $this->goods_cate_list = $cate;
        }
        $this->display();
        
        
    }
    	public function childs($parent_id=0){
        $datas = D('Goodscate')->fetchAll();
        $str = '';

        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['cate_id'] == $parent_id){
         
                foreach($datas as $var2){

                    if($var2['parent_id'] == $var['cate_id']){
                        $str.='<option value="'.$var2['cate_id'].'">'.$var2['cate_name'].'</option>'."\n\r";
           
                        foreach($datas as $var3){
                            if($var3['parent_id'] == $var2['cate_id']){
                                
                               $str.='<option value="'.$var3['cate_id'].'">&nbsp;&nbsp;--'.$var3['cate_name'].'</option>'."\n\r"; 
                                
                            }
                            
                        }
                    }  
                }      
            }           
        }
        echo $str;die;
    }

   

}
