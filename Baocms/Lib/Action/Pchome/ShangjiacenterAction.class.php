<?php
/**
 * 描述:店家中心调度室
 * 
 */
class ShangjiacenterAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        $this->open_shop_auth_mod = D('OpenShopAuth');
        $this->shop_auth_info = $this->open_shop_auth_mod->where(array('uid'=>$this->uid))->select(false);
        if(empty($this->shop_auth_info)){
            $this->redirect();
        }
    }
    public function index(){
        
    }
    
}