<?php

/*
 * 作者：刘弢
 * QQ：473139299
 */

class ChaoshiorderAction extends ChaoshiAction {

    protected $status = 0;
    protected $chaoshi;

    public function _initialize() {
        parent::_initialize();
    }
    
    
    public function index() {
        $this->status = array('in',array(0,1,2,3,4,5));
        $this->showdata();
        $this->display(); // 输出模板
    }

    public function waitsend() {
        $this->status = 1;
        $this->showdata();
        $this->display('index'); // 输出模板
    }
    
    public function sended() {
        $this->status = 2;
        $this->showdata();
        $this->display('index'); // 输出模板
    }
    
    public function waitpay() {
        $this->status = 0;
        $this->showdata();
        $this->display('index'); // 输出模板
    }

    public function over() {
        $this->status = 5;
        $this->showdata();
        $this->display('index'); // 输出模板
    }

    // update:remove begin
     /*    public function canceling() {
        $this->status = 4;
        $this->refund_status = 1;
        $this->showdata();
        $this->display('index'); // 输出模板
    }*/
    // update:remove end
    
    public function refunded() {
        $this->status = 4;
        //$this->refund_status = 2;
        $this->showdata();
        $this->display('index'); // 输出模板
    }

    public function canceled() {
        $this->status = 3;
        $this->showdata();
        $this->display('index'); // 输出模板
    }
    
    
    public function count(){
        
        $dvo = D('DeliveryOrder'); // 实例化User对象
        $bg_date = strtotime(I('bg_date',0,'trim'));
        $end_date = strtotime(I('end_date',0,'trim'));
        $this->assign('btime',$bg_date);
        $this->assign('etime',$end_date);
        
        if($bg_date && $end_date){
            $pre_btime = date('Y-m-d H:i:s',$bg_date);
            $pre_etime = date('Y-m-d H:i:s',$end_date);
            $this->assign('pre_btime',$pre_btime);
            $this->assign('pre_etime',$pre_etime);
        }
        $map = array();
        $map['shop_id'] = $this->shop_id;
        $map['type'] = 1;
        if($bg_date && $end_date){
           $map['create_time'] = array('between',array($bg_date,$end_date)); 
        }
        import('ORG.Util.Page');// 导入分页类
        $count      = $dvo->where($map)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $dvo->where($map)->order('order_id desc')->limit($Page->firstRow.','.$Page->listRows)->relation(true)->select();
//        print_r($list);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
        
    }
    
    
    function delivery_count(){
        $delivery_id = I('did',0,'intval,trim');
        $btime = I('btime',0,'trim');
        $etime = I('etime',0,'trim');
        $map = array();
        if($btime && $etime){
           $map['create_time'] = array('between',array($btime,$etime)); 
        }
        if(!$delivery_id || !($this->shop_id)){
            $this->ajaxReturn(array('status'=>'error','message'=>'错误'));
        }else{
            $map['delivery_id'] = $delivery_id;
            $map['shop_id'] = $this->shop_id;
            $map['type'] = 1;
            $count = D('DeliveryOrder') ->where($map)-> count();
            if($count){
                $this->ajaxReturn(array('status'=>'success','count'=>$count));
            }else{
                $this->ajaxReturn(array('status'=>'error','message'=>'错误'));
            }
        }
    }
    private function showdata() {
        $Chaoshiorder = D('Chaoshi_order');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('co.closed' => 0, 'co.store_id' => $this->chaoshi['store_id'], 'co.status' => $this->status);
        if ($this->refund_status){
            $map['co.refund_status'] = $this->refund_status;
        }
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date)+84600;
            $map['co.create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['co.create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['co.create_time'] = array('ELT', $end_time);
            }
        }        
        if ($keyword = I('keyword')) {
            $users = D('Users')->where(array('nickname' => array('LIKE', '%' . $keyword . '%')))->select();
            $uids = array();
            foreach ($users as $k => $v){
                $uids[] = $v['user_id'];
            }
            $map['co.user_id'] = array('in',$uids);
            $this->assign('keyword', $keyword);
        }
        
        if ($order_id = I('order_id','0','intval')) {
            $map['co.order_id'] = $order_id;
            $this->assign('order_id',$order_id);
        }

        $count = $Chaoshiorder->alias('co')->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数     
        $show = $Page->show(); // 分页显示输出
        // 超市订单表
        // 仲裁表
        $list = $Chaoshiorder
            ->field('co.*, bao_arbitrament.arbitrament_status as arbitrament_result')
            ->alias('co')
            ->join('LEFT JOIN bao_arbitrament ON bao_arbitrament.order_type=1 AND co.order_id=bao_arbitrament.order_id')
            ->where($map)
            ->order(array('co.order_id' => 'desc'))
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();        
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
        }
        if (!empty($order_ids)) {
            $orderproducts = D('Chaoshiorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
           // print_r($orderproducts);
            $product_ids = array();
            foreach ($orderproducts as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }
            $this->assign('orderproducts', $orderproducts);
          // print_r(D('Chaoshiproduct')->itemsByIds($product_ids));
          //  print_r($product_ids);
            $this->assign('products', D('Chaoshiproduct')->itemsByIds($product_ids));
        }
        if (!empty($user_ids)) {
            $users = D('Users')->itemsByIds($user_ids);
            $this->assign('users', $users);
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
    }

    public function queren($order_id) {
        $order_id = (int) $order_id;
        if (!$detail = D('Chaoshiorder')->find($order_id)) {
            $this->baoError('没有该订单');
        }
        if ($detail['store_id']!= $this->chaoshi['store_id']) {
            $this->baoError('您无权管理该订单');
        }
        if ($detail['status'] != 1) {
            $this->baoError('订单不合法');
        }
        D('Chaoshiorder')->save(array(
            'order_id' => $order_id,
            'status' => 2,
            'audit_time' => NOW_TIME
        ));
        $user = D('users')->find($detail['user_id']);
        D('Sms')->sendSms('chaoshi_order_send', $user['mobile'],array('nickname'=>$user['nickname']));
        $this->baoSuccess('已确认', U('chaoshiorder/index'));
    }
/**
 * 退款
 */
    public function tuikuan() {
        $chaoshi_product_model = D('Chaoshiproduct');
        $users_model = D('Users');
        $order_id = I('order_id','0','intval');
        if ($order_id) {           
            $obj = D('Chaoshiorder');
            $mod=D('ChaoshiOrderTuikLogs')->where(array('order_id'=>$order_id))->find();
            $mod['username']=$users_model->field('nickname')->find($mod['user_id']);
            //print_r(D('Users')->getLastSql());
            if (!$detail = $obj->find($order_id)) {        	
                $this->baoError('订单不存在');
            }
            if ($detail['store_id'] != $this->chaoshi['store_id']) {
                $this->baoError('订单不存在');
            }
            if ($detail['refund_status'] != 1) {
                $this->baoError('当前状态不能退款');
            }    
            $obj->startTrans();
            if (IS_POST){
                if ($obj->save(array('order_id' => $order_id, 'refund_status' => 2, 'pay_status' => 2)))
                {
                    if ($users_model->where(array('user_id' => $detail['user_id']))->setInc('money',$detail['pay_price'])) {
                        $user_money_log_res = D('Usermoneylogs')->add(array(
                                        'user_id' => $detail['user_id'],
                                        'order_id' => $detail['order_id'],
                                        'type' => 3,
                                        'order_type' => 1,
                                        'money' => $detail['pay_price'],
                                        'create_time' => NOW_TIME,
                                        'create_ip' => get_client_ip(),
                                        'intro' => '超市订单'.$detail['order_id'].'退款',
                                    ));
                        if (!$user_money_log_res) {
                        	$obj->rollback();
                            $this->baoError('操作失败！');
                        }
                        $saveData = array(
                        				'audit_ip' => get_client_ip(),
                        				'audit_time' => $_SERVER['REQUEST_TIME'],
                        				'status' => 2,
                        				'refund_endtime' => $_SERVER['REQUEST_TIME']
                  					);
                        $tuikuan_logs = D('ChaoshiOrderTuikLogs')->where(array('order_id'=>$order_id))->save($saveData);
                    	if (!$tuikuan_logs) {
                        	$obj->rollback();
                            $this->baoError('操作失败！');
                        }
                        $orderproduct = D('Chaoshiorderproduct') -> where(array('order_id' => $order_id)) -> select();
                        foreach ($orderproduct as $k => $v) {
                        	//$data = array('inventory' => array('exp', 'inventory+'.$v['inventory']), 'sold_num' => array('exp','sold_num-'.$v['sold_num']));
                            $productRes = $chaoshi_product_model->where(array('product_id' => $v['product_id']))->setInc('inventory',$v['num']);                	
                            if (!$productRes) {
                        		$obj->rollback();
                    			$this->baoError('操作失败！');
                        	}
                        }
                        $obj->commit();
                        $member = $users_model->find($detail['user_id']);
                        D('Sms')->sendSms('chaoshi_order_refund', $member['mobile'],array('nickname'=>$member['nickname'],'order_id'=>$order_id));
                        $this->baoSuccess('操作成功！', U('chaoshiorder/refunded'));
                    } else {
                    	$obj->rollback();
                    	$this->baoError('操作失败！');
                    }
                } else {
                	$obj->rollback();
                	$this->baoError('操作失败！');
                }          
            }else {
                $mod['refund_endtime'] = $mod['create_time'] + 3600*24*3 - time();
                $mod['d']=floor($mod['refund_endtime']/86400);
                $mod['h']=floor($mod['refund_endtime']%86400/3600);
                $mod['m']=floor($mod['refund_endtime']%86400%3600/60);
                $mod['s']=$mod['refund_endtime']%86400%3600%60;
                $this->assign('mod',$mod);
                $this->display();
            }     
        } else {
            $this->baoError('请选择要操作的订单');
        }
    }

    public function cancel() {
        $order_id = I('order_id','0','intval');       
        if ($order_id){
            $chaoshi_order_model = D('Chaoshiorder');
            $chaoshi_order_cancel_logs_model = D('Chaoshiordercancellogs');
            if (!$detail = $chaoshi_order_model->find($order_id)){
                $this->baoSuccess('超市订单不存在');
            }
            if ($detail['status'] != 0 && $detail['status']!=1){
                $this->baoSuccess('订单状态错误');
            }
            if ($detail['status'] == 1 && $detail['pay_type'] != 1){
                $this->baoSuccess('订单状态错误');
            }
            if (IS_POST){
                if ($chaoshi_order_model->save(array('order_id'=>$order_id,'status'=>3))){
                    $remark = I('remark');
                    $data = array(
                        'order_id' => $detail['order_id'],
                        'user_id' => $detail['user_id'],
                        'store_id' => $detail['store_id'],
                        //'type' => 1,
                        'handler' => 2,
                        'remark' => $remark,
                        'create_time' => NOW_TIME,
                        'create_ip' => get_client_ip(),
                    );
                    $chaoshi_order_cancel_logs_model->add($data);
                    $orderuser=D('Users')->where(array('user_id'=>$detail['user_id']))->find();
                    D('Sms')->sendSms('chaoshi_order_quxiao', $orderuser['mobile'],array('nickname'=>$orderuser['nickname'],'order_id'=>$order_id));
                    $this->baoSuccess('取消成功',U('Chaoshiorder/index'));
                }else {
                    $this->baoSuccess('取消失败');
                }
            }else {
               // $this->assign('detail',$detail);
                $this->display('quxiao');
            }         
        }else {
            $this->baoSuccess('参数错误');
        }
    }

    public function fahuo() {
        $chaoshiorder_model = D('Chaoshiorder');
        $order_id = I('order_id','0','intval');
        $detail = $chaoshiorder_model->find($order_id);
        $orderuser = D('Users')->find($detail['user_id']);       
        if (IS_POST){
            $data = $chaoshiorder_model->create();
            if ($detail['status'] != 1){
                $this->ajaxReturn(array('status'=>'error','msg'=>'非待发货订单'));
            }
            $data['status'] = 2;
            $data['audit_time'] = NOW_TIME;            
            $data['send_time'] = NOW_TIME;            
            if ($chaoshiorder_model->save($data)){                
                D('Sms')->sendSms('chaoshi_order_send', $detail['phone'],array('nickname'=>$orderuser['nickname']));
                $this->ajaxReturn(array('status'=>'success','msg'=>'操作成功'));
            }            
        }
        
        $this->assign('orderuser',$orderuser);
        $this->assign('detail',$detail);
        $this->display();
    }

    public function editprice() { 
        $chaoshiorder_model = D('Chaoshiorder');
        $payment_log_model = D('Paymentlogs');
        $order_id = I('order_id','0','intval');        
        $detail = $chaoshiorder_model->find($order_id);
        $orderuser = D('Users')->find($detail['user_id']);
        if (IS_POST){
            $price = I('price','-1');
            if ($detail['status'] != 0){
                $this->ajaxReturn(array('status'=>'error','msg'=>'该状态不允许修改价格'));
            }
            if ($price < 0.01 || empty($price)){
                $this->ajaxReturn(array('status'=>'error','msg'=>'价格不正确'));
            }
//             if ($price > $detail['pay_price']){
//                 $this->ajaxReturn(array('status'=>'error','msg'=>'价格不能大于原始价格'));
//             }
            $res = $chaoshiorder_model->where(array('order_id'=>$order_id))->setField('pay_price',$price);
            $ress = $payment_log_model->where(array('order_id'=>$order_id))->setField('money', $price);
            if ($res && $ress){
                $this->ajaxReturn(array('status'=>'success','msg'=>'修改成功'));
            }else {
                $this->ajaxReturn(array('status'=>'error','msg'=>'修改失败'));
            }
        }
        $this->assign('orderuser',$orderuser);
        $this->assign('detail',$detail);
        $this->display();
    }

    public function detail() {
        $order_id = I('order_id','0','intval');
        $detail = D('Chaoshiorder')
            ->alias('cs')
            ->field('cs.*, us.nickname as user_name, comm.create_time as comment_create_time')
            ->join('LEFT JOIN bao_users us ON us.user_id=cs.user_id')
//            ->join('LEFT JOIN bao_payment_logs pay ON pay.order_id=cs.order_id')
            ->join('LEFT JOIN bao_chaoshi_comment comm ON comm.order_id=cs.order_id')
            ->where(array('cs.order_id' => $order_id))
            ->find();
        // 仲裁判断
        if($detail['arbitrament_status'] == 2){
            $arbitramentUser = M('Arbitrament')
                ->field('arbitrament_status')
                ->where(array('order_id' => $detail['order_id'], 'order_type' => '1'))
                ->find();
            if(!in_array($arbitramentUser, array(1, 2))){
                $this->error('未知错误');
            }
            $this->assign('arbitramentUser', $arbitramentUser);
        }
        if(!$detail) {
            $this->error('订单不存在');
        }
//        var_dump($detail);

        $log = D('Paymentlogs')->where(array('order_id'=>$detail['order_id'], 'type' => 'chaoshi'))->find();
        if ($log){
            $this->assign('log',$log);
        }

        // 订单商品详情及商品名
        $orderproducts = D('Chaoshiorderproduct')
            ->alias('orpro')
            ->field('orpro.*, pro.product_name')
            ->join('LEFT JOIN bao_chaoshi_product pro ON pro.product_id=orpro.product_id')
            ->where(array('order_id' => $order_id))
            ->select();
        if(!$orderproducts){
            $this->error('该订单为空');
        }
        $this->assign('orderproducts', $orderproducts);
        $this->assign('detail',$detail);
        $this->display();

    }

    // update:remove begin
    public function detail_back() {
        $order_id = I('order_id','0','intval');
        if ($detail = D('Chaoshiorder')->find($order_id)){
            $orderproducts = D('Chaoshiorderproduct')->where(array('order_id' => $order_id))->select();
            $product_ids = array();
            foreach ($orderproducts as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }
            $orderuser = D('Users')->find($detail['user_id']);
            $log = D('Paymentlogs')->where(array('order_id'=>$detail['order_id']))->find();
            if ($log){
                $this->assign('log',$log);
            }
            $comment = D('Chaoshicomment')->where(array('order_id'=>$detail['order_id']))->find();
            if ($comment){
                $this->assign('comment',$comment);
            }
            $this->assign('orderuser',$orderuser);
            $this->assign('orderproducts', $orderproducts);
            $this->assign('products', D('Chaoshiproduct')->itemsByIds($product_ids));
            $this->assign('detail',$detail);
            $this->display();
        }else {
            $this->error('订单不存在');
        }
    }
    // update:remove end
    public function orderprint() {
        $order_id = I('order_id','0','intval');
        if ($detail = D('Chaoshiorder')->find($order_id)){
            $orderproducts = D('Chaoshiorderproduct')->where(array('order_id' => $order_id))->select();
            $product_ids = array();
            foreach ($orderproducts as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }        
            $orderuser = D('Users')->find($detail['user_id']);
            $log = D('Paymentlogs')->where(array('order_id'=>$detail['order_id']))->find();
            if ($log){
                $this->assign('log',$log);
            }
            $comment = D('Chaoshicomment')->where(array('order_id'=>$detail['order_id']))->find();
            if ($comment){
                $this->assign('comment',$comment);
            }
            
            $this->assign('orderuser',$orderuser);
            $this->assign('orderproducts', $orderproducts);
            $this->assign('products', D('Chaoshiproduct')->itemsByIds($product_ids));           
            $this->assign('detail',$detail);

            $this->display();            
        }else {
            $this->error('订单不存在');
        }        
    }

    public function tuihuo($order_id){
     $order=D('ChaoshiOrder')->where(array('order_id'=>$order_id))->find();
     $user=D('Users')->where(array('user_id'=>$order['user_id']))->find();
     if(IS_POST){
        if(D('ChaoshiOrder')->save(array('status'=>1,'order_id'=>$order_id))){
            //var_dump($user);
            D('Sms')->sendSms('chaoshi_order_tuihuo', $user['mobile'],array('nickname'=>$user['nickname'],'order_id'=>$order_id));
            $this->baoSuccess('操做成功',U('Chaoshiorder/index'));
        }else{
            $this->baoError('操做失败');
     }
     }else{
        $data=D('ChaoshiOrder')->find($order_id);
    if($data['status'] !=5){
        $this->baoError('该订单不存在');
    }else{
        $mod=D('ChaoshiOrderTuihLogs')->where(array('order_id'=>$order_id))->find();
        $mod['username']=D('Users')->field('nickname')->find($mod['user_id']);
        $mod['refund_endtime']=$mod['refund_endtime']-time();
        $mod['d']=floor($mod['refund_endtime']/86400);
        $mod['h']=floor($mod['refund_endtime']%86400/3600);
        $mod['m']=floor($mod['refund_endtime']%86400%3600/60);
        $mod['s']=$mod['refund_endtime']%86400%3600%60;
       // var_dump($mod);
    
    $this->assign('mod',$mod);    
    $this->display();
    }
 }
}

    public function jujue(){
        $order_id = I('order_id','0','intval');
        if ($order_id) {           
            $obj = D('Chaoshiorder');
            if (!$detail = $obj->find($order_id)) {
                $this->baoError('订单不存在');
            }
            if ($detail['store_id'] != $this->chaoshi['store_id']) {
                $this->baoError('订单不存在');
            }
            if ($detail['refund_status'] != 1) {
                $this->baoError('当前状态不能操作');
            }     
            $obj->startTrans();
            if (IS_POST){               
                $order_res = $obj->save(array('order_id' => $order_id, 'refund_status' => 3));  
                $saveData = array(
                        			'audit_ip' => get_client_ip(),
                        			'audit_time' => $_SERVER['REQUEST_TIME'],
                        			'status' => 3
                  				);
                $tuikuan_logs = D('ChaoshiOrderTuikLogs')->where(array('order_id'=>$order_id))->save($saveData);              
                if ($order_res && $tuikuan_logs){
                    $obj->commit();
                    $member = D('Users')->find($detail['user_id']);
                    D('Sms')->sendSms('chaoshi_order_refuse_refund', $member['mobile'],array('nickname'=>$member['nickname'],'order_id'=>$order_id));
                    $this->baoSuccess('操作成功！', U('chaoshiorder/refunded'));
                }else {
                    $obj->rollback();
                    $this->baoError('操作失败');
                }          
            }  
        }else {
            $this->baoError('请选择要操作的订单');
        }
    }
    
}