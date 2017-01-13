<?php
class  TestAction extends CommonAction{
    
    public function index(){
       exit(date('Y-m-d H:i:s',time()));
    }
 
    
}