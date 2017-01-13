<?php

/*
 * 作者：刘弢
 * 日期: 2016.5.26
 */

class ChaoshiAction extends CommonAction {
    
    public function index() {
        $Chaoshiorder = D('Chaoshiorder');
        $chaoshi_mod = D('Chaoshi');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('user_id' => $this->uid, 'closed' => 0); //这里只显示 实物
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date) + 86399;

            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $Chaoshiorder->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count,40); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Chaoshiorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        $orders = array();
        foreach($result as $v){
            $orders[] = $v['order_id'];
        }
        $user_ids = $order_ids = $addr_ids = $shops_ids = array();
        foreach ($list as $k => $val) {
            $list[$k]['chaoshi_info'] = $chaoshi_mod->find($val['store_id']);
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $user_ids[$val['user_id']] = $val['user_id'];        
        }
        if (!empty($order_ids)) {
            $orderproducts = D('Chaoshiorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $product_ids = $shop_ids = array();
            foreach ($orderproducts as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $this->assign('orderproducts', $orderproducts);
            $this->assign('products', D('Chaoshiproduct')->itemsByIds($product_ids));
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('cfg', D('Chaoshiorder')->getCfg());

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出    
        $this->display();
    }
    
    public function favorites() {
        $chaoshifavorites_model = D('Chaoshifavorites');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $chaoshifavorites_model->where($map)->count();
        $Page = new Page($count, 30);
        $show = $Page->show();
        $list = $chaoshifavorites_model->where($map)->order('favorites_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $store_ids = array();
        foreach ($list as $k => $val) {
            $store_ids[$val['store_id']] = $val['store_id'];
        }
        $this->assign('chaoshis', D('Chaoshi')->itemsByIds($store_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    public function delfavorites(){
        $favorites_id = I('favorites_id','','intval');
        if ($detial = D('Chaoshifavorites')->find($favorites_id)) {
            if ($detial['user_id'] == $this->uid) {
                D('Chaoshifavorites')->delete($favorites_id);
                D('Chaoshi')->updateCount($detial['store_id'], 'fans_num',-1); 
                $this->baoSuccess('取消收藏成功!', U('chaoshi/favorites'));
            }
        }
        $this->baoError('参数错误');
    }
 /**
  * 删除订单
  */   
    public function delorder() {
        $order_id = I('order_id','0','intval');
        if ($order_id) {
            if (!$detial = D('Chaoshiorder')->find($order_id)) {
                $this->baoError('订单不存在');
            }
            if ($detial['user_id'] != $this->uid) {
                $this->baoError('订单不存在');
            }
            if (!in_array($detial['status'], array('0','6'))) {
                $this->baoError('当前状态不能删除');
            }
            $obj = D('Chaoshiorder');
            if ($obj->save(array('order_id' => $order_id, 'closed' => 1))){
                $this->baoSuccess('删除成功！', U('chaoshi/index'));
            }            
        } else {
            $this->baoError('请选择要取消的订单');
        }
    }
 /**
  * 取消订单申请
  */   
    public function cancel() {
        $order_id = I('order_id','0','intval');
        if ($order_id) {
            if (!$detial = D('Chaoshiorder')->find($order_id)) {
                $this->baoError('订单不存在');
            }
            if ($detial['user_id'] != $this->uid) {
                $this->baoError('订单不存在');
            }
            if (!in_array($detial['status'], array('1','2'))) {
                $this->baoError('当前状态不能取消');
            }
            $obj = D('Chaoshiorder');
            if ($obj->save(array('order_id' => $order_id, 'status' => 5))){
                $chaoshi = D('Chaoshi')->find($detail['store_id']);
                D('Sms')->sendSms('chaoshi_order_cancel', $chaoshi['phone']);
                $this->baoSuccess('操作成功！等待商家确认', U('chaoshi/index'));
            }            
        } else {
            $this->baoError('请选择要取消的订单');
        }
    }
/**
 * 评论
 */
    public function comment($order_id) {
        $order_id = (int) $order_id;
        if (!$detail = D('Chaoshiorder')->find($order_id)) {
            $this->baoError('没有该订单');
            exit();
        } else {
            if ($detail['user_id'] != $this->uid) {
                $this->baoError('没有该订单');
                exit();
            }
        }
        $chaoshi_comment_model = D('Chaoshicomment');
        if ($chaoshi_comment_model->commentCheck($order_id, $this->uid)) {
            $this->error('已经评价过了');
            exit();
        }
        if ($this->_Post()) {
            if (!$data = $chaoshi_comment_model->create()){
                $this->baoError($chaoshi_comment_model->getError());
            }
            $data['user_id'] = $this->uid;
            $data['store_id'] = $detail['store_id'];
            $data['order_id'] = $order_id;
            $data['show_date'] = date('Y-m-d', NOW_TIME); 
            if ($comment_id = $chaoshi_comment_model->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val))
                        $local[] = $val;
                }
                if (!empty($local))
                    D('Chaoshicommentpics')->upload($comment_id, $local);
                    D('Users')->updateCount($this->uid, 'ping_num');
                    D('Chaoshiorder')->save(array('order_id'=>$order_id,'is_comment'=>1));
                    $this->baoSuccess('恭喜您点评成功!', U('chaoshi/index'));
            }
            $this->baoError('点评失败！');
        }else {
            $details = D('Chaoshi')->find($detail['store_id']);
            $this->assign('details', $details);
            $this->assign('order_id', $order_id);
            $this->display();
        }
    }
/**
 * 确认收货
 */   
    public function finish($order_id) {
        $order_id = (int) $order_id;
        if (!$detail = D('Chaoshiorder')->find($order_id)) {
            $this->baoError('没有该订单');
            exit();
        }
        if ($detail['user_id'] != $this->uid) {
            $this->baoError('您无权管理该订单');
            exit();
        }
        if ($detail['status'] != 2) {
            $this->baoError('该订单状态不正确');
            exit();
        }        
        if (D('Chaoshiorder')->overOrder($order_id)){
            D('Chaoshiorder')->where(array('order_id'=>$order_id))->save(array('finish_time'=>NOW_TIME));
            $chaoshi = D('Chaoshi')->find($detail['store_id']);
            D('Sms')->sendSms('chaoshi_order_ok', $chaoshi['phone']);
            $this->baoSuccess('收货完成！', U('chaoshi/index'));
        }
    }
}