<?php
/**
 * 店铺认证
 * 作者：王恒
 * 时间：2016-5-13
 */


class ShopauthAction extends CommonAction {
    //申请列表
   public function apply() {
        $OpenShopAuth_mod = D('OpenShopAuth');
        import('ORG.Util.Page'); // 导入分页类
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['link_mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $OpenShopAuth_mod->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $OpenShopAuth_mod->order(array('update_time' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        
        $this->assign("type_list",array(1=>"个人",2=>"企业"));
        $this->assign("auth_status_list",array(1=>"待审核",2=>"审核不通过",3=>"审核通过"));
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
   //查看并审核
   public function see(){
    $this->OpenShopAuth_mod = D('OpenShopAuth');
    $auth_info = $this->OpenShopAuth_mod->find(I('auth_id'));
  
    
    $this->auth_info=$auth_info;
    //var_dump($auth_info);
    if($auth_info['auth_type']==1){
        $this->see_prosonl();
    }elseif($auth_info['auth_type']==2){
        $this->see_enter();
    }
    
   }
   //删除认证
   public function delete($auth_id){
        if(empty($auth_id)){
          $this->baoError('非法操作');  
        }
        $openshopauth_mod = D('OpenShopAuth');
        if($openshopauth_mod->delete($auth_id)){
            $this->baoSuccess('删除成功！', U('shopauth/apply'));
        }else{
            $this->baoError('删除失败！', U('shopauth/apply'));
        }
   }
   //查看个人信息
   private function see_prosonl(){
    if(IS_POST){
        
        $data['auth_status'] = I('auth_status');
        $data['auth_error_note'] = I('auth_error_note');
        $data['auth_id'] = I('auth_id');
        $this->OpenShopAuth_mod->save($data);
         if($data['auth_status']==3){
            if(I('link_mobile')){
                D('Sms')->sendSms('openshop_ok', I('link_mobile'));
                
                
            }
            $shop_info = D('Shop')->where(array('user_id'=>$this->auth_info['uid']))->find();
                
                if(!$shop_info){
                    $shop_data['user_id'] = $this->auth_info['uid'];
                    $shop_data['shop_name'] = $this->auth_info['realname']."的小店";
                    $shop_data['auth_id'] = $this->auth_info['auth_id'];
                    $shop_data['create_time'] = NOW_TIME;
                    $shop_data['audit'] = 1;
                    D('Shop')->add($shop_data);
                }
        }elseif($data['auth_status']==2){
            if(I('link_mobile')){
                D('Sms')->sendSms('openshop_err', I('link_mobile'),array('reason'=>I('auth_error_note')));
            }
        }
        $this->baoSuccess("操作成功");
    }
    
    //var_dump($this->auth_info);
    
    $this->display('see_prosonl');
   }
   //查看企业信息
   private function see_enter(){
    if(IS_POST){
        
        $data['auth_status'] = I('auth_status');
        $data['auth_error_note'] = I('auth_error_note');
        $data['auth_id'] = I('auth_id');
        $this->OpenShopAuth_mod->save($data);
        if($data['auth_status']==3){
            if(I('link_mobile')){
                D('Sms')->sendSms('openshop_ok', I('link_mobile'));
               
            }
             $shop_info = D('Shop')->where(array('user_id'=>$this->auth_info['uid']))->find();
                if(!$shop_info){
                    $shop_data['user_id'] = $this->auth_info['uid'];
                    $shop_data['shop_name'] = $this->auth_info['company_name'];
                    $shop_data['auth_id'] = $this->auth_info['auth_id'];
                    $shop_data['create_time'] = NOW_TIME;
                    $shop_data['audit'] = 1;
                    D('Shop')->add($shop_data);
                }
        }elseif($data['auth_status']==2){
            if(I('link_mobile')){
                
                D('Sms')->sendSms('openshop_err', I('link_mobile'),array('reason'=>I('auth_error_note')));
            }
        }
        $this->baoSuccess("操作成功");
    }
    $this->display('see_enter');
   }
    
  

}
