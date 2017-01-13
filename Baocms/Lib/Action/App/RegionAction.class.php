<?php

class RegionAction extends CommonAction
{
    public function _initialize(){
        $this->region_mod = D('Region');
    }
	public function index(){
        
    }
    public function select_list($area_id,$select_name="area_name[]",$class="area_class"){
        $this->ajaxReturn($this->region_mod->create_select($area_id,$select_name,$class));
    }
    
}