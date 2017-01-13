<?php
class  SystemcontentAction extends  CommonAction{

    public function index(){
        $this->display('');
    }

    public function right_content(){
        $this->display('right_content');
    }
    public function detials(){
        $this->display();
    }


}