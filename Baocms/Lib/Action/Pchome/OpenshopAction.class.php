<?php
/**
 * 描述:开店流程
 * 
 */
class OpenshopAction extends CommonAction{
    protected $shop_info;
    public function _initialize(){
        parent::_initialize();
        if (empty($this->uid)) {
            header("Location:" . U('pchome/passport/login'));
            die;
        }
        if(empty($this->member['mobile'])){
           $this->error("用户手机号码，未认证请先认证手机号码，再申请开店",U('Pcucenter/info/account','','html',false,C('BASE_SITE'))); 
        }
        //读店铺
         $this->shop_mod = D("Shop");        
         $this->shop_info =  $this->shop_mod->where(array('user_id'=>$this->uid))->find();
         $this->assign('shop_info',$this->shop_info);
    }
    //选择店铺类型
    public function index(){
       
        if(IS_POST){

            $shop_type = $_POST['data']['shop_type'];

            $this->redirect("pchome/Openshop/step1",array('shop_type'=>$shop_type,'sc_id'=>I('sc_id'),'st_id'=>I('st_id')));

        }
        $this->sc_id=I('sc_id');
        $this->st_id=I('st_id');
        
       $this->display(); 
    }
    //开店第一步
    public function step1(){
        $this->personl_step1();
    }
    //开店第一步设置商家信息
    public function personl_step1(){
       //店铺类型
        $store_class = D('StoreClass')->fetchAll();
        if(IS_POST){
            //增加商家
            if(!$this->shop_info){
               $data['shop_name']=$_POST['com_name'];
               $data['user_id']=$this->uid;
               $data['area_id']=$_POST['area_id'];
               $data['business_id']=$_POST['business_id'];
               $data['city_id']=$_POST['city_id'];
               $data['addr']=$_POST['address'];
               $data['lng']=$_POST['lng'];
               $data['lat']=$_POST['lat'];
               $data['logo']=$_POST['logo'];               
               $data = $this->shop_mod->create($data);
               if($data){
                $shop_id = $this->shop_mod->add($data);
               }else{
                $this->baoError($this->shop_mod->getError());
               }              
               $_POST['shop_id']=$shop_id;
               //var_dump($_POST);die; 
            }else {
               $_POST['shop_id']=$this->shop_info['shop_id'];
            }
//            exit;
           $store_mod = D($store_class[$_POST['sc_id']]['sc_module']);

           if(empty($_POST['store_id'])){
            unset($_POST['store_id']);
            $_POST['create_time'] = $_POST['update_time'] = NOW_TIME;
            $_POST['is_default'] =  $store_mod->where(array('shop_id'=>$_POST['shop_id'],'is_default'=>1))->count()?0:1;
            }
            
            $_POST['valid_time'] = NOW_TIME+365*86400;
//            var_dump($_POST);die;
            // update:remove begin
            /*
            if($_POST['sc_id']==2){
                $_POST['logo_img'] = $_POST['logo'];
            }
            */
            // update:remove end
            $_POST['store_name'] = $_POST['shop_name'];
            $_POST['store_logo'] = $_POST['logo'];

            $data = $store_mod->create($_POST);
       
            if($data){
                if(!$_POST['store_id']){
                	$data['update_time'] = $data['begin_time'] = NOW_TIME;
                    $_POST['store_id'] = $store_mod->add($data);
                }else{
 //                   $_POST[$store_mod->getPk()]=$_POST['store_id'];
                    $data['audit'] = 0;
                    $data['update_time'] = NOW_TIME;
                    $store_mod->save($data);            
                }
           }else{
                $this->baoError($store_mod->getError());
           }
           //检测我佣有的店铺
            $my_store = D('MyHaveStore')->where(array('uid'=>$this->uid,'sc_id'=>$_POST['sc_id']))->count();
            if(!$my_store){
                D('MyHaveStore')->add(array('uid'=>$this->uid,'sc_id'=>$_POST['sc_id']));
            }            
            if($_POST['level']=="1"){
               //$this->redirect("pchome/Openshop/personl_step2",array('st_id'=>$_POST['store_id'],'sc_id'=>$_POST['sc_id']));
               $this->baoJump(U("pchome/Openshop/personl_step2",array('st_id'=>$_POST['store_id'],'sc_id'=>$_POST['sc_id'])));
            }elseif($_POST['level']=="2"){
               $this->baoJump(U("pchome/Openshop/comon_step1",array('st_id'=>$_POST['store_id'],'sc_id'=>$_POST['sc_id'])));
            }
        }

        //获得店铺信息
        $sc_id=I('sc_id');
        $st_id=I('st_id');
        if($sc_id && $st_id){
            $store_mod = D($store_class[$sc_id]['sc_module']);
            $data = $store_mod->find($st_id);
               if ($data){
                   if($sc_id==5){
                        $data['logo'] = $data['store_logo'];
                    }
               }
//             if($data){
//                 if($store_class[$sc_id]['sc_module']=='Chaoshi'){
//                     $data['store_id']=$data['store_id'];
//                     unset($data['store_id']);
//                 }elseif($sc_id==5){
//                     $data['shop_name'] = $data['store_name'];
//                     $data['logo'] = $data['store_logo'];
//                 }elseif($sc_id==3){
//                     $data['shop_name'] = $data['store_name'];
//                     $data['logo'] = $data['store_logo'];
//                 }elseif($sc_id==4){
//                     $data['store_id'] = $data['hotel_id'];
//                     $data['shop_name'] = $data['store_name'];
//                     $data['logo'] = $data['store_logo'];
//                 }elseif($sc_id==2){
//                     $data['logo'] = $data['logo_img'];
//                 }
//             }
            //var_dump($data);
            $this->assign('store_info',$data);
        }
        $this->assign('curr_store_class',$sc_id);
        $this->assign('store_class',$store_class);
        
        $this->display('personl_step1');
    }
//    普通商家开第二步上传认证资料
    public function personl_step2(){
        $presonal_store_open_auth_mod = D('PresonalStoreOpenAuth');
        $this->assign('auth_info',$auth_info = $presonal_store_open_auth_mod->find($this->uid));
        if(IS_POST){
            $data = $presonal_store_open_auth_mod->create();
            $consent = I('consent','0','intval');
            if (!$consent) {
                $this->error('请阅读并同意开店协议');
            }
            if($auth_info){
                $presonal_store_open_auth_mod->where(array('uid'=>$this->uid))->save($data);
            }else{
                $data['uid'] = $this->uid;
                $presonal_store_open_auth_mod->add($data);
            }
            
            /*$store_class = D('StoreClass')->fetchAll();
            //var_dump($_POST);
            //var_dump($store_class[$_POST['sc_id']]['sc_module']);
            $store_mod = D($store_class[$_POST['sc_id']]['sc_module']);
            
            $pk = $store_mod->getPk();
            $store_mod->where(array($pk=>$_POST['store_id']))->save(array('audit'=>0));*/
                        
            $this->redirect("pchome/Openshop/personl_step3",array('st_id'=>$_POST['store_id'],'sc_id'=>$_POST['sc_id'])); 
               
            
        }
        //获得店铺信息
        $this->assign('st_id',I('st_id'));
        $this->assign('sc_id',I('sc_id'));
        $this->display();
    }
    //普通商家开店提示
    public function personl_step3(){
        $presonal_store_open_auth_mod = D('PresonalStoreOpenAuth');
        $this->assign('auth_info',$auth_info = $presonal_store_open_auth_mod->find($this->uid));
        $st_id = I('get.st_id', 0, 'intval');
        $sc_id = I('get.sc_id', 0, 'intval');
        if (!empty($st_id) && !empty($sc_id)) {
        	 $store_class = D('StoreClass')->fetchAll();
        	 $store_info = D($store_class[$sc_id]['sc_module'])->find($st_id);
        	 if (isset($store_info['audit']) && $store_info['audit'] != 0) {
        	 	if ($store_info['audit'] == 1) {
        	 		$this->redirect("pchome/Openshop/open_success",array('st_id'=>$st_id,'sc_id'=>$sc_id));
        	 	}
        	 	if ($store_info['audit'] == 2) {
        	 		$this->redirect("pchome/Openshop/open_fail",array('st_id'=>$st_id,'sc_id'=>$sc_id));
        	 	}
        	 	
        	 } else {
        	 	$this->display();
        	 }
        }      
    }
    //商家开店第一步认证
    public function comon_step1(){
        $com_store_open_auth_mod = D('ComStoreOpenAuth');
         if(IS_POST){
            if(!$_POST['agree']){
              $this->baoError("亲！要同意开店协议才可下一步，哟~");  
            }
            $_POST['other_pic']=implode(",",$_POST['other_pic']);
            $data = $com_store_open_auth_mod->create();
            if (!$data){
                $this->baoError($com_store_open_auth_mod->getError());
            }
            $data['store_class_id']=$_POST['sc_id'];
           if($data){
             if($data['id']){
                $com_store_open_auth_mod->save();
             }else{ 
                 unset($data['id']);
                 $id = $com_store_open_auth_mod->add($data);                
             }
             $this->baoJump(U('personl_step3', array('st_id'=>$_POST['store_id'],'sc_id'=>$_POST['sc_id'])));               
           }else{
            $this->baoError($com_store_open_auth_mod->getError());
           }           
        }
        if(I('st_id') && I('sc_id')){
            $audit_info = $com_store_open_auth_mod->where(array('store_id'=>I('st_id'),'store_class_id'=>I('sc_id')))->find();
            $audit_info['other_pic'] =  explode(',',$audit_info['other_pic']); 
            $this->assign('audit_info',$audit_info);
            
        }
        $this->assign('st_id',I('st_id'));
        $this->assign('sc_id',I('sc_id'));
        $this->display('comon_step1');
    }  
    //商家开店审核后的页面
    public function open_success()
    {
    	$st_id = I('get.st_id', 0, 'intval');
        $sc_id = I('get.sc_id', 0, 'intval');
        if ($st_id && $sc_id) {
        	$this->assign('st_id', $st_id);
        	$this->assign('sc_id', $sc_id);
        	$this->display('open_success');
        }
    }   


    public function open_fail(){
    	$st_id = I('get.st_id', 0, 'intval');
        $sc_id = I('get.sc_id', 0, 'intval');
        if ($st_id && $sc_id) {
        	$this->assign('st_id', $st_id);
        	$this->assign('sc_id', $sc_id);
        	$this->display('open_fail');
        }
    }

}