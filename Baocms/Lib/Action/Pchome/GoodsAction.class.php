<?php



class GoodsAction extends CommonAction {
    
     public function _initialize() {
        parent::_initialize();
        $this->type = D('Keyword')->fetchAll();
        $this->assign('types', $this->type);
        $this->goods_mod =D("Goods");
    }
    public function select_view($cid=0,$recommend_type="recomment1"){
       //var_dump($_SESSION);
        $goods_cate_list =  D("GoodsCate")->where(array('parent_id'=>$cid))->select();
        //var_dump($goods_cate_list);
        $this->recommend_type = $recommend_type;
        $this->goods_cate_list = $goods_cate_list;
        $this->shop_id= I('shop_id');
        $this->display();
    }
    public function get_goods_list_for_table($cid,$shop_id=0){
        $cates = D('Goodscate')->getAllChildren($cid,$fix='parent_id',$id='cate_id',"--",3);
        
        $cate_ids=$cid;
        foreach($cates as $k=>$v){
            $cate_ids.=",".$v['cate_id'];
        }
        $goods_condition['cate_id'] = array('in',$cate_ids);
        
        if($shop_id){
         $goods_condition['shop_id'] = $shop_id;   
        }
        $this->goods_list = $this->goods_mod->where($goods_condition)->select();
        $this->display();
    }
     /**
     *描述：ajax获得一个商品信息以html的行式返回 
     *作者：王恒
     *时间：2016-4-20
     */
    public function get_one_goods_info_for_ul($goods_id=0,$recomend_type=1){
        
        $info = $this->goods_mod->find($goods_id);
        $this->vo = $info;
        $this->recomend_type=$recomend_type;
        $this->display();
        
    }
   
   

   

}
