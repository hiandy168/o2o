<?php 
	class ReadGoodsCateModel extends CommonModel {

	    protected $pk = 'cate_id';
	    protected $tableName = 'goods_cate';
	    protected $token = 'goods_cate';
	    protected $orderby = array('orderby' => 'asc');
	    

	    public function  getParentsId($id){
	        $data = $this->fetchAll();
	        $parent_id = $data[$id]['parent_id'];
	        $parent_id2 = $data[$parent_id]['parent_id'];
	        if($parent_id2 == 0) return $parent_id;
	        return  $parent_id2;
    	}

	  
	    //获得无限级分类树
	    
	    public function unlimitedForLevel($pid=0,$level=0,$html='--'){
	    	import("ORG.Util.Category");
	    	$category_mod = new Category();
	    	$cate = $this->fetchAll();
	    	
	    	return $category_mod->unlimitedForLevel($cate, $html, $pid, 0,'parent_id',$this->pk,$level);
	    }
	    
	    
	    

	}