<?php

/**
 * 描述：前台首页面
 * 作者：王恒
 * 时间：2016-14-19
 * 联系方式：QQ337886915  
 */

class IndexAction extends CommonAction {
    
     public function _initialize() {
        parent::_initialize();
        $this->type = D('Keyword')->fetchAll();
        $this->assign('types', $this->type);
    }

    public function index() {
        $this->display();
    }
    
/*    public function test(){
        setcookie('chaoshiproducts',null,time()-1);
    }*/
    
    
}
