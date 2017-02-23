<?php

/* 
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 锦尚中国
 * 邮件: youge@baocms.com  QQ 800026911
 */

class  TongjiAction extends CommonAction{
    
    
    public function  index(){
        
        $showdata =  D('Tuanorder')->source();
        $weeks = D('Tuanorder')->weeks();
        $this->assign('weeks',$weeks);
        $this->assign('showdata',$showdata);
        $this->display();
    }
    
    
    public function quanming(){
         if(($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date)+86400;
           
        }else{
            $bg_date = date('Y-m-d',NOW_TIME-86400*30);
            $bg_time = strtotime($bg_date);
            $end_time = NOW_TIME;
            $end_date = TODAY;
        }
        $this->assign('bg_date',$bg_date);
        $this->assign('end_date',$end_date);

        $this->assign('tongji1',D('Quanming')->tongjiComm($bg_time,$end_time));
        $this->assign('tongji2',D('Quanming')->tongjiNum($bg_time,$end_time));
        $this->display();
    }
    
    public function money(){
        if(($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date)+86400;
           
        }else{
            $bg_date = date('Y-m-d',NOW_TIME-86400*30);
            $bg_time = strtotime($bg_date);
            $end_time = NOW_TIME;
        }
        $this->assign('bg_date',$bg_date);
        $this->assign('end_date',$end_date);
        $this->assign('money',D('Tuanorder')->money($bg_time,$end_time));
        var_dump(D('Tuanorder')->money($bg_time,$end_time));
        $this->assign('money_yue',D('Tuanorder')->money_yue());
        $this->display();
    }
    
    
    public function  laiyuan(){
        $bg_date = $this->_param('bg_date', 'htmlspecialchars');
        $end_date = $this->_param('end_date', 'htmlspecialchars');
        if(empty($bg_date) || empty($end_date)){
            $bg_date = date('Y-m-d',NOW_TIME-86400*30);
            $end_date = TODAY;
        }
        $this->assign('bg_date',$bg_date);
        $this->assign('end_date',$end_date);
        
        $this->assign('laiyuan',D('Tongji')->laiyuan($bg_date,$end_date));
        
        $this->display();
    }
    
    public function lmoney(){
        
        $bg_date = $this->_param('bg_date', 'htmlspecialchars');
        $end_date = $this->_param('end_date', 'htmlspecialchars');
        if(empty($bg_date) || empty($end_date)){
            $bg_date = date('Y-m-d',NOW_TIME-86400*30);
            $end_date = TODAY;
        }
        $this->assign('bg_date',$bg_date);
        $this->assign('end_date',$end_date);
        
        $this->assign('laiyuan',D('Tongji')->lmoney($bg_date,$end_date));
        
        $this->display();
        
    }
    
    
    public function tuiguan(){
        
        $bg_date = $this->_param('bg_date', 'htmlspecialchars');
        $end_date = $this->_param('end_date', 'htmlspecialchars');
        if(empty($bg_date) || empty($end_date)){
            $bg_date = date('Y-m-d',NOW_TIME-86400*30);
            $end_date = TODAY;
        }
        $this->assign('bg_date',$bg_date);
        $this->assign('end_date',$end_date);
    
        $this->assign('tuiguan',D('Tongji')->tuiguan($bg_date,$end_date));
        $this->assign('tmoney',D('Tongji')->tmoney($bg_date,$end_date));
        $this->display();
    }
    
    public function keyword(){
        
        $bg_date = $this->_param('bg_date', 'htmlspecialchars');
        $end_date = $this->_param('end_date', 'htmlspecialchars');
        if(empty($bg_date) || empty($end_date)){
            $bg_date = date('Y-m-d',NOW_TIME-86400*30);
            $end_date = TODAY;
        }
        $this->assign('bg_date',$bg_date);
        $this->assign('end_date',$end_date);
    
        $this->assign('keyword',D('Tongji')->keyword($bg_date,$end_date));
        $this->assign('kmoney',D('Tongji')->kmoney($bg_date,$end_date));
        $this->display();
    }

    public function  customercount(){
        $this->display();
    }

    public function  customerdetail(){
        $this->display();
    }

    public function  shopcount(){       
        $shop_model = M('Shop');
        $money_log_model = M('StoreMoneyLogs');
        
        $count = $shop_model->count();
        import('ORG.Util.Page'); // 导入分页类
        $Page = new Page($count, 25);     
        
        $bg_date = I('bg_date');
        $end_date = I('end_date');
        
        $join = 'LEFT JOIN bao_store_money_logs ON shop.shop_id = bao_store_money_logs.shop_id and bao_store_money_logs.order_id>0';
        if (!empty($bg_date)){            
            $bg_time = strtotime($bg_date);
            $join .= ' and bao_store_money_logs.create_time >='.$bg_time;
        }
        if (!empty($end_date)){            
            $end_time = strtotime($end_date)+86400;
            $join .= ' and bao_store_money_logs.create_time <='.$end_time;
        }        
        
        $shop_users = $shop_model
        ->alias('shop')
        ->field('shop.shop_id,shop.money,bao_users.nickname,sum(bao_store_money_logs.money) as sum_money,count(order_id) as order_count')    
        ->join('LEFT JOIN bao_users ON shop.user_id = bao_users.user_id')
        ->join($join)  //order_id>0 是为了只统计订单。不统计提现的金额
        ->group('shop_id')
        ->limit($Page->firstRow.','.$Page->listRows)
        ->select();
        
        //卖家交易总额
        $money_count  = $money_log_model
        ->alias('log')
        ->field('sum(log.money) as sum_money')
        ->where(array('order_id'=>array('gt',0)))
        ->find();
        
        $this->assign('Page',$Page->show())
        ->assign('shop_users',$shop_users)
        ->assign('money_count',$money_count)
        ->assign('bg_date',$bg_date)
        ->assign('end_date',$end_date);
        $this->display();
    }

    public function  shopdetail(){
        $shop_model = M('Shop');
        $money_log_model = M('StoreMoneyLogs');
        $shop_id = I('get.shop_id','0','intval');

        $bg_date = I('bg_date');
        $end_date = I('end_date');
        
        $join = 'LEFT JOIN bao_store_money_logs ON shop.shop_id = bao_store_money_logs.shop_id and bao_store_money_logs.order_id>0';
        $where = array('shop_id'=>$shop_id);
        
        $count = $money_log_model
        ->where($where)
        ->count();
        
        import('ORG.Util.Page'); // 导入分页类
        $Page = new Page($count, 25);
        
        if (!empty($bg_date)){
            $bg_time = strtotime($bg_date);
            $join .= ' and bao_store_money_logs.create_time >='.$bg_time;
            array_push($where, array('create_time'=>array('egt',$bg_time)));
        }
        if (!empty($end_date)){
            $end_time = strtotime($end_date)+86400;
            $join .= ' and bao_store_money_logs.create_time <='.$end_time;
            array_push($where, array('create_time'=>array('elt',$end_time)));
        }
        $shop_info = $shop_model
        ->alias('shop')
        ->where(array('shop.shop_id'=>$shop_id))
        ->field('shop.money,bao_users.nickname,sum(bao_store_money_logs.money) as sum_money')
        ->join('LEFT JOIN bao_users ON shop.user_id = bao_users.user_id')
        ->join($join)
        ->find();
               
        $logs = $money_log_model
        ->field('money,create_time,order_id')
        ->where($where)
        ->limit($Page->firstRow.','.$Page->listRows)
        ->select();
// echo '<pre>';print_r($logs);exit();
        $this->assign('Page',$Page->show())
        ->assign('shop_info',$shop_info)
        ->assign('logs',$logs)
        ->assign('bg_date',$bg_date)
        ->assign('end_date',$end_date)
        ->assign('shop_id',$shop_id);
        $this->display();
    }
    
}