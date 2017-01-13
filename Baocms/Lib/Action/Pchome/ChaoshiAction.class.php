<?php

class ChaoshiAction extends CommonAction {

    protected $cart = array();
    protected $total_money;

    public function _initialize() {
        parent::_initialize();
       // $chaoshiproducts = $this->_getCartGoods();
//        foreach ($chaoshiproducts as $k => $val) {
//            $this->total_money += $val['total_price'];
//            $cart_num+= $val['cart_num'];
//            $carts[] = $val['product_id'] . '_' . $val['cart_num'];
//        }        
//        
//        $this->assign('chaoshiproducts', $chaoshiproducts);
//        $this->assign('total_money', $this->total_money);
//        $this->assign('cartnum', $cart_num);
//        $this->cart = join('|', $carts);
    }

    protected function helpCates(){
        // 获取帮助列表
        $sql = "SELECT `cate_id`,`cate_name`,`parent_id` FROM `bao_article_cate` WHERE `parent_id`=(SELECT `cate_id` FROM `bao_article_cate` WHERE `parent_id`=0 AND `cate_name`='系统信息') AND `closed`=0 ORDER BY `orderby` LIMIT 0,6";
        $getHelpCates = M('ArticleCate')->query($sql);
//        var_dump($getHelpCates);
        // 子分类
        $ids = [];
        foreach ($getHelpCates as &$getHelpCate){
            $ids[] = $getHelpCate['cate_id'];
        }
        // 类名
        $getHelpCates[0]['icon'] = '__TMPL__statics/css/chaoshi/images/user1.gif';
        $getHelpCates[1]['icon'] = '__TMPL__statics/css/chaoshi/images/user2.gif';
        $getHelpCates[2]['icon'] = '__TMPL__statics/css/chaoshi/images/user3.gif';
        $getHelpCates[3]['icon'] = '__TMPL__statics/css/chaoshi/images/user4.gif';
        $getHelpCates[4]['icon'] = '__TMPL__statics/css/chaoshi/images/user5.gif';
        $getHelpCates[5]['icon'] = '__TMPL__statics/css/chaoshi/images/user6.gif';
        $sql = "SELECT `cate_id`,`cate_name`,`parent_id` FROM `bao_article_cate` WHERE `parent_id` IN (".implode(',', $ids).") AND `closed`=0 ORDER BY `orderby`";
        $getHelpSons = M('ArticleCate')->query($sql);
        // 每个分类限制4个
//        $helpSons = [];
        foreach ($getHelpCates as &$getHelpCate){
            $num = 0;
            foreach ($getHelpSons as &$getHelpSon){
                if($getHelpCate['cate_id'] == $getHelpSon['parent_id'] && $num < 5){
                    $getHelpCate['son'][] = $getHelpSon;
                }
            }
            unset($num);
        }
        return $getHelpCates;
    }

    // update:remove begin
    private function _getCartGoods() {
        $carts = cookie('chaoshiproducts');
        if (empty($carts)){
            return null;
        }           
        $carts = explode('|', $carts);
        $ids = $nums = array();
       
        foreach ($carts as $key => $val) {
            $local = explode('_', $val);
            $local[0] = (int) $local[0];
            $local[1] = (int) $local[1];
            if (!empty($local[0]) && !empty($local[1]) && $local[1] > 0) {
                $ids[$local[0]] = $local[0];
                $nums[$local[0]] = $local[1];
            }
        }
        $store_id = I('store_id');
        $where['store_id'] =  $store_id;
        $where['product_id'] = array('in',$ids);
        $products = D('Chaoshiproduct')->where($where)->select();
        foreach ($products as $k => $v){
            $myids[] = $v['product_id'];
        }
        
        $chaoshiproducts = D('Chaoshiproduct')->itemsByIds($myids);
        foreach ($chaoshiproducts as $k => $val) {
            $chaoshiproducts[$k]['cart_num'] = $nums[$val['product_id']];
            $chaoshiproducts[$k]['total_price'] = $nums[$val['product_id']] * $val['price'];
        }
        $cookies = array();
        foreach ($nums as $k => $v) {
            $cookies[] = $k . '_' . $v;
        }
        //$cookiestr = join('|', $cookies);
        //setcookie('chaoshiproducts', join('|', $cookies),NOW_TIME + 604800, '/');
        //$_COOKIE['chaoshiproducts'] = $cookiestr;
        return $chaoshiproducts;
    }
    // update:remove end

    //超市列表
    public function shoplist() {

        import('ORG.Util.Page'); // 导入分页类
        $chaoshi_model = D('Chaoshi');

        $map = array('closed' => 0, 'audit' => 1,'status'=>0);

        $field = '*';
        
        $order = I('order','','trim');
        $orderby = '';
        switch ($order) {
            case 'd':
                $orderby = array('distribution' => 'asc');
                break;
            case 'm':
                $orderby = array('month_num' => 'desc');
                break;
            case 's':
                $orderby = array('since_money' => 'asc');
                break;
            default:
                $orderby = array('store_id' => 'desc');
                break;
        }   
        $point = $_COOKIE['current_search_point'];
        $lng = $point['lng'];
        $lat = $point['lat'];
        if ($lng && $lat){
            $field .= ',1000*ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.'* 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380 as mydistance';
            $map['distance'] = array('exp','> `mydistance`');
        }
        $is_pay = I('is_pay','0','intval');
        if ($is_pay){
            $map['is_pay'] = 1;
        }
        $is_new = I('is_new','0','intval');
        if ($is_new){
            $map['is_new'] = 1;
        }
        $is_fan = I('is_fan','0','intval');
        if ($is_fan){
            $map['is_fan'] = 1;
        }

        $map['city_id'] = $this->city_id;
        if(I('post.chaoshi','')){

            $product=M('chaoshi_product');
            if(IS_POST){
                $name=I('chaoshi');
                $where['product_name']=array('like',$name.'%');
                $dada=$product->where($where)->select();

                foreach($dada as $key=>$val){
                    $data[]=$val['store_id'];
                };

                $map['store_id']=array('in',$data);
            }
        };
        //因为别名不能直接当字段使用，所以使用嵌套查询
        $count = $chaoshi_model->table("(select $field from ".$chaoshi_model->getTableName().") as temptable")->where($map)->order($orderby)->count();    
        $Page = new Page($count,20); // 实例化分页类 传入总记录数和每页显示的记录数        
        $list = $chaoshi_model->table("(select $field from ".$chaoshi_model->getTableName().") as temptable")->where($map)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();
        $show = $Page->show(); // 分页显示输出
      

        $this->assign('city_name',$this->city['name']);
        $this->assign('is_fan',$is_fan);
        $this->assign('is_new',$is_new);
        $this->assign('is_pay',$is_pay);
        $this->assign('order',$order);
        $this->assign('lng',$lng);
        $this->assign('lat',$lat);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        // 帮助导航
        $this->assign('helpCates', $helpCates = $this->helpCates());
        $this->display();

    }
    
    //评论 列表
    public function comment() {
        import('ORG.Util.Page'); // 导入分页类
        $chaoshicomment_model = D('Chaoshicomment');
        $lng = I('lng');
        $lat = I('lat');
        $store_id = I('store_id','0','intval');
        if (!$detail = D('Chaoshi')->find($store_id)){
            $this->error('超市不存在');
        }
        $all_map = array('closed' => 0,'store_id'=>$store_id,'audit'=>1);            
        $all_lists = $chaoshicomment_model->where($all_map)->select();
        $all_count = count($all_lists);  // 查询满足要求的总记录数 
        
        $h_map = array('closed' => 0,'store_id'=>$store_id,'score'=>array('eq','5'),'audit'=>1);            
        $h_lists = $chaoshicomment_model->where($h_map)->select();
        $h_count = count($h_lists);  // 查询满足要求的总记录数 
        
        $m_map = array('closed' => 0,'store_id'=>$store_id,'score'=>array('between','2,4'),'audit'=>1);            
        $m_lists = $chaoshicomment_model->where($m_map)->select();
        $m_count = count($m_lists);  // 查询满足要求的总记录数 
        
        $l_map = array('closed' => 0,'store_id'=>$store_id,'score'=>array('eq','1'),'audit'=>1);            
        $l_lists = $chaoshicomment_model->where($l_map)->select();
        $l_count = count($l_lists);  // 查询满足要求的总记录数 
        
        $type = I('type');
        if ($type == 'h'){
            $lists = $h_lists;
            $count = $h_count;
        }
        elseif ($type == 'm'){
            $lists = $m_lists;
            $count = $m_count;
        }
        elseif ($type == 'l'){
            $lists = $l_lists;
            $count = $l_count;
        }else {
            $lists = $all_lists;
            $count = $all_count;
        }
        
        $Page = new Page($count,20); // 实例化分页类 传入总记录数和每页显示的记录数    
        $show = $Page->show(); // 分页显示输出  
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        
        foreach ($list as $k => $v){
            $user_ids[$v['user_id']] = $v['user_id'];
            $comment_ids[] = $v['comment_id'];
        }      
        $users = D('Users')->itemsByIds($user_ids);
        $pics = D('Chaoshicommentpics')->where(array('comment_id'=>array('in',$comment_ids)))->select();  
 
        $this->assign('all_count',$all_count);
        $this->assign('h_count',$h_count);
        $this->assign('m_count',$m_count);
        $this->assign('l_count',$l_count);
        $this->assign('type',$type);        
        $this->assign('users',$users);
        $this->assign('detail',$detail);
        $this->assign('store_id',$store_id);
        $this->assign('pics',$pics);
        $this->assign('lng',$lng);
        $this->assign('lat',$lat);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
        
    }

    public function index() {
        $chaoshi_model = D('Chaoshi');
        // where条件为：认证通过、对应的城市、推荐、正在营业、商家状态正常且存在的超市
        $map = array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id, 'is_tuijian' => 1, 'is_open' => 1, 'status' => 0);
        $tuijian_list = $chaoshi_model
            ->where($map)
            ->order('store_id desc')
            ->limit(9)                     // 推荐的上限为 9；
            ->select();
        $this->assign('list',$tuijian_list);
        $this->display();
    }
    
    public function shop(){     
        $chaoshi_model = D('Chaoshi');
        $chaoshi_product_model = D('Chaoshiproduct');
        $chaoshi_product_cate_model = D('Chaoshiproductcate');
        $cart_model = D('ChaoshiCart');
        $store_id = I('store_id','0','intval');
        $where = array('store_id'=>$store_id, 'closed' => 0, 'audit' => 1);
        $detail = $chaoshi_model->where($where)->find();
        if (!$detail || $detail['city_id'] != $this->city_id){
            $this->redirect('pchome/chaoshi/index');
        }
        import('ORG.Util.Page'); // 导入分页类
        $map = array('store_id'=>$store_id, 'closed' => 0, 'audit' => 1, 'is_out' => 0, 'inventory' => array('gt',0));
        

        $cate_id = I('cate_id','0','intval');
        if ($cate_id) {

            $map['cate_id'] = $cate_id;

        }
        $order = I('order','','trim');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('sold_num' => 'desc');
                break;
            case 'p':
                $orderby = array('price' => 'asc');
                break;
            default:
                $orderby = array('product_id' => 'desc');
                break;
        }
        $search = I('get.search');
        if($search){
            $map['product_name'] = array('like','%'.$search.'%');
        }
        $count = $chaoshi_product_model->where($map)->count();
        $page = new Page($count,48);
        $list = $chaoshi_product_model->where($map)->order($orderby)->limit($page->firstRow.','.$page->listRows)->select();
        $show = $page->show();

        $cates = $chaoshi_product_cate_model->getProductCate($store_id);
        
        $lng = I('lng');
        $lat = I('lat');
        
        $cart = $cart_model->get_store_cart_info($this->uid,$store_id);
        //计算订单差价
        $chajia = $detail['since_money']-$cart['total'];
        $chajia = $chajia>0?$chajia:0;
//         echo '<pre>';
//         print_r($cart);
//         exit();
        $this->assign('cart',$cart);
        $this->assign('store_id',$store_id);    //供模板调用当前筛选条件
        $this->assign('cate_id',$cate_id);
        $this->assign('lng',$lng);
        $this->assign('lat',$lat);

        $this->assign('search', $search);
        $this->assign('order', $order);
        $this->assign('count',$count);          //商品数量
        $this->assign('cates',$cates);          //商品分类
        $this->assign('page',$show);
        $this->assign('list',$list);            //商品列表
        $this->assign('detail',$detail);        //商家详情
        $this->assign('chajia',$chajia);        //购物车差价
        // 帮助导航
        $this->assign('helpCates', $helpCates = $this->helpCates());
        $this->display();
    }
    
    public function city(){
        $city = D('city');
        $this->assign('citys',$city->fetchAll());
        $this->display();
    }  
    
    public function pay() {
        $order_id = I('order_id','','intval');
        // 帮助导航
        $helpCates = $this->helpCates();
        $helpCates[0]['icon'] = 'foot-icon foot-icon-user';
        $helpCates[1]['icon'] = 'foot-icon foot-icon-service';
        $helpCates[2]['icon'] = 'foot-icon foot-icon-pay';
        $helpCates[3]['icon'] = 'foot-icon foot-icon-love';
        $helpCates[4]['icon'] = 'foot-icon foot-icon-set';
        $helpCates[5]['icon'] = 'foot-icon foot-icon-cooperate';
        $this->assign('helpCates', $helpCates);
//        var_dump($helpCates);
        if (!empty($order_id)) {
            $order = D('Chaoshiorder')->find($order_id);
            //var_dump($order);
             //获得商家信息
            $chaoshi_mod = M('chaoshi');
            
            
            if($order['user_id'] != $this->uid){
                $this->error("不能修改别人的订单!", U('chaoshi/index'));
            }
            if($order['status'] != 0){
                $this->error("该订单不能修改!", U('chaoshi/index'));
            }
            $order_product = D('Chaoshiorderproduct')->where(array('order_id' => $order_id))->select();
            $product_ids = $nums = array();
            foreach ($order_product as $key => $v) {
                $product_ids[$v['product_id']] = $v['product_id'];
                $nums[$v['product_id']] = $v['num'];
            }
            $cart_goods = D('Chaoshiproduct')->itemsByIds($product_ids);
            $shop_ids = array();
            foreach ($cart_goods as $k => $val) {
                $cart_goods[$k]['cart_num'] = $nums[$k];
            }
            
            $this->assign('chaoshi_info',$chaoshi = D('Chaoshi')->find($order['store_id']));
            
            $this->assign('order_info',$order);
            $this->assign('order_id', $order_id);
            $this->assign('store_id',$order['store_id']);
            $this->assign('cart_goods', $cart_goods);
            $this->display('change_cart');
        } else {
            $cart_model = D('ChaoshiCart');
            $chaoshi_order_model = D('Chaoshiorder');
            $store_id = I('store_id','','intval');
            $chaoshi = D('Chaoshi')->find($store_id);
//            var_dump($chaoshi);

            $cart = $cart_model->get_store_cart_info($this->uid,$store_id);
            if ($cart['total'] < $chaoshi['since_money']){
                $this->error('未达到起送价');
            }
           
            if ($chaoshi['is_fan'] && $cart['total'] >= $chaoshi['full_money']) { //满足满减的条件 立马减几块钱                                
                $discount_money = $chaoshi['discount_money'];
                if ($discount_money > 0){
                    $this->assign('discount_money',$discount_money);
                }                
            }
            if ($chaoshi['is_new'] && $chaoshi_order_model->checkIsNew($this->uid, $store_id)) { //如果是新单                                
                $new_money = $chaoshi['new_money'];
                if ($new_money > 0){
                    $this->assign('new_money',$new_money);
                }              
            }

            if (empty($cart['list'])) {
                $this->error("亲还没有选购产品呢!");
            }
            $cart_goods = $cart['list'];
            
// echo '<pre>';
// print_r($cart_goods);
// exit();            
            $this->assign('store_id',$store_id);
            $this->assign('chaoshi_info',$chaoshi);
            $this->assign('cart_goods', $cart_goods);
            $this->assign('payment', D('Payment')->getPayments());
            $this->display();
        }
    }
    
    public function cartdel() {
        $cart_model = D('ChaoshiCart');
        $product_id = I('product_id','0','intval');
        if ($cart_model->where(array('user_id'=>$this->uid,'product_id'=>$product_id))->delete()) {
            $this->ajaxReturn(array('status'=>'success','msg'=>'删除成功'));
        }else{
            $this->ajaxReturn(array('status'=>'error','msg'=>'删除失败'));
        }
    }
    
    public function pay2() {        
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $user_addr_mod = D('UserAddr');
        $chaoshi_order_model = D('Chaoshiorder');
        $cart_model = D('ChaoshiCart');       
        $payment_model = D('Payment');
        $store_id = I('store_id','','intval');
        
        $chaoshi = D('chaoshi')->find($store_id);        
        if (!$chaoshi['is_open']){
            $this->baoError('超市已打烊');
        
        }
        $shop = D('Shop')->where(array('user_id'=>$this->uid))->find();
        if ($shop['shop_id'] == $chaoshi['shop_id']){
            $this->baoError('不能购买自家的商品');
        }
        
        if(!$_POST['addr_id']){
            $this->baoError('请选择收货地址');
        }
        
        $address_info=$user_addr_mod->find($_POST['addr_id']);
        $_POST['phone']=$address_info['mobile'];
        $_POST['name']=$address_info['name'];
        $_POST['receipt_addr']=$address_info['addr'];
        
        if (!$data = $chaoshi_order_model->create()){
            $this->baoError($chaoshi_order_model->getError());
        }
        $cart_list = $cart_model->get_store_cart_info($this->uid,$store_id);
        if (empty($cart_list['list'])){
            $this->baoError('购物车中没有商品');
        }
        $tnum = 0;       
        foreach ($cart_list['list'] as $key=>$val){
            if($val['product_info']['is_out'] == 1 || $val['product_info']['closed'] == 1 || ($val['product_info']['audit'] !=1) || $val['product_info']['inventory'] <= 0){
                $this->baoError("商品【".$val['product_info']['product_name']."】不能购买");
            }
            $tnum += $val['num'];
            $data['order_products'][] = array('product_id'=>$val['product_id'],
                'price'=>$val['price'],
                'num'=>$val['num'],
                'total_price'=>$val['total_price'],
            );
            if ($val['product_info']['inventory'] < $val['num']) {
                $this->baoError('亲！商品<'.$val['product_info']['product_name'].'>库存不够了,只剩'.$val['product_info']['inventory'].'件了！');
            }
        }
        if (!$code = $this->_post('code')) {
            $this->baoError('请选择支付方式！');
        }
        if ($code != 'wait') {
            $payment = $payment_model->checkPayment($code);
            if (empty($payment)) {
                $this->baoError('该支付方式不存在');
            }
        }
        $pay_types = $payment_model->getOrderPayTypes();
        $pay_type = $pay_types[$code];
        
        $total_price = $cart_list['total'] + $chaoshi['logistics'];
        $pay_price = $total_price;
        if ($chaoshi['is_fan'] && $cart_list['total'] >= $chaoshi['full_money']) { //满足满减的条件 立马减几块钱
            $pay_price = $pay_price-$chaoshi['discount_money'];
            $discount_money = $chaoshi['discount_money'];
        }
        if ($chaoshi['is_new'] && $chaoshi_order_model->checkIsNew($this->uid, $store_id)) { //如果是新单
            $pay_price = $pay_price-$chaoshi['new_money'];
            $new_money = $chaoshi['new_money'];
        }
        $pay_price = $pay_price>0 ? $pay_price : 0.01;
        $send_date = I('send_date');
        $send_time = I('send_time'); //送达时间
        $send_time = strtotime($send_date.$send_time);
        $data['send_time'] = $send_time;
        $data['user_id'] = $this->uid;
        $data['num'] = $tnum;
        $data['logistics'] = $chaoshi['logistics'];
        $data['total_price'] = $total_price;                   //订单总价（结算价格+运费）
        $data['settlement_price'] = $cart_list['total'];       //结算价格（商品价格*数量）
        $data['pay_price'] = $pay_price;                       //实际支付金额
        $data['new_money'] = $new_money?$new_money:0;
        $data['discount_money'] = $discount_money?$discount_money:0;
        $data['pay_type'] = $pay_type;
        
        if ($cart_list['total'] < $chaoshi['since_money']){
            $this->baoError('未达到起送价');
        }  
//          echo '<pre>';print_r($data);exit();
        if ($order_id = D('Chaoshiorder')->relation(true)->add($data)){
            //清除购物车
            $cart_model->where(array('user_id'=>$this->uid,'store_id'=>$store_id))->delete();            
        }else {
            $this->baoError('下单失败');
        }
        $data['order_id'] = $order_id;
        $order = $data;
        if ($code == 'wait') { //如果是货到付款
            $payment_model->chaoshiSold($order_id);                       
            if (D('Chaoshiorder')->save(array('order_id' => $order_id,'status' => 1,'pay_type' => 1))){                     
                $chaoshi = D('Chaoshi')->find($order['store_id']);                
                D('Sms')->sendSms('chaoshi_new_order', $chaoshi['phone']);                       
                $this->baoSuccess('恭喜您下单成功！', U('Pcucenter/chaoshiorder/index','','html',false,C('BASE_SITE')));
            }            
        } else {
            $logs = array(
                'type' => 'chaoshi',
                'user_id' => $this->uid,
                'order_id' => $order_id,
                'code' => $code,
                'money' => $order['pay_price'],
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $log_id = D('Paymentlogs')->add($logs);            
            $this->baoSuccess('下单成功，请支付',U('payment/payment', array('log_id' => think_encrypt($log_id))));
        }
    }
    /**
     * 去支付（从个人中心点击）
     */
    public function to_pay() {
        $order_id = I('get.order_id','0','intval');
        $logs = D('Paymentlogs')->where(array('type'=>'chaoshi','order_id'=>$order_id))->find();
        $this->redirect('payment/payment', array('log_id' => $logs['log_id']));
    }
    
    public function order_change() {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }       
        $order_id = (int) $_POST['order_id'];
        $order = D('Chaoshiorder')->find($order_id);
        $chaoshi = D('chaoshi')->find($order['store_id']);        
        if($order['user_id'] != $this->uid){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '不能修改别人的订单'));
        }
        if($order['status'] != 0){
            $this->error("该订单不能修改!", U('chaoshi/index'));
        }
        $num = I('num');
        foreach ($num as $k => $v) {
            $v = (int)$v;
            if (empty($v)){
                unset($num[$k]);
            }else {
                $product_ids[$k] = $k;
            }
        }
        if (empty($product_ids)){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没有选择商品'));
        }
        $products = D('Chaoshiproduct')->itemsByIds($product_ids);
        foreach ($products as $key => $val) {
            if ($val['closed'] != 0 || $val['audit'] != 1 || $val['inventory'] <= 0) {
                unset($products[$key]);
            }
        }
        if (empty($products)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '很抱歉，您提交的产品暂时不能购买！'));
        }
        if ($val['inventory'] < $num[$val['product_id']]) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！商品<'.$val['product_name'].'>库存不够了,只剩'.$val['inventory'].'件了！'));
        }    
        $tprice = 0;
        $ip = get_client_ip();
        $orderproducts = array();
        foreach ($products as $val) {
            $price = $val['price'] * $num[$val['product_id']];
            $tprice+= $price;
            $orderproducts = array(
                'num' => $num[$val['product_id']],
                'price' => $val['price'],
                'total_price' => $price,
                'update_time' => NOW_TIME,
                'update_ip' => $ip,
            );
            D('Chaoshiorderproduct')->where(array('order_id' => $order_id, 'product_id' => $val['product_id']))->setField($orderproducts); //忽略报错
        }
        if ($tprice<$chaoshi['since_money']){
           $this->ajaxReturn(array('status' => 'error', 'msg' => "未达到起送价{$chaoshi['since_money']}元"));
        }
        $total_price = $tprice + $chaoshi['logistics'];               
        if(false !== D('Chaoshiorder')->save(array('order_id'=>$order_id,'total_price'=>$total_price,'send_start_time'=>$_POST['send_start_time'],'send_end_time'=>$_POST['send_end_time'],'pay_price'=>$total_price,'settlement_price'=>$tprice,'update_time'=>NOW_TIME,'update_ip'=>$ip))){
            $this->ajaxReturn(array('status' => 'success', 'msg' => '成功修改订单，正在跳转到支付页面','url'=>U('chaoshi/pay', array('order_id' => $order_id))));
        }else{
            $this->ajaxReturn(array('status' => 'error', 'msg' => '修改订单失败'));
        }
    }
    
    public function favorites()
    {    
        if (empty($this->uid)) {
           $this->ajaxReturn(array('status'=>'login','message'=>'请先登录！'));
        }
        $store_id = I('store_id','','intval');    
        if (!$detail = D('Chaoshi')->find($store_id)) {   
            $this->ajaxReturn(array('status'=>'error','message'=>'没有该商家！'));    
        }   
        if ($detail['closed']) {  
           $this->ajaxReturn(array('status'=>'error','message'=>'该商家已经被删除'));  
        }   
        if (D('Chaoshifavorites')->check($store_id, $this->uid)) {    
           $this->ajaxReturn(array('status'=>'error','message'=>'您已经收藏过该商家了！'));    
        }    
        $data = array(    
            'store_id' => $store_id,    
            'user_id' => $this->uid,    
            'create_time' => NOW_TIME,    
            'create_ip' => get_client_ip());    
        if (D('Chaoshifavorites')->add($data)) {    
            D('Chaoshi')->updateCount($store_id, 'fans_num');    
            $this->ajaxReturn(array('status'=>'success','message'=>'收藏成功！'));    
        }    
        $this->ajaxReturn(array('status'=>'error','message'=>'收藏失败！'));  
    }
    public function set_cookie($name,$value){
        $count = count($_COOKIE[$name]);
        if(!in_array($value,$_COOKIE[$name])){
                setcookie($name."[$count]",$value,NOW_TIME+30*86400,"/");
        }        
        setcookie("current_search_addr",$value,NOW_TIME+30*86400,"/");
    }
    //设置经纬度
    public function set_cookie_lng_lat($lng,$lat){
         setcookie("current_search_point[lng]",$lng,NOW_TIME+30*86400,"/");
         setcookie("current_search_point[lat]",$lat,NOW_TIME+30*86400,"/");
        
    }
    
    //超市购物车订单检测
    public function cart_check(){
        
        if(!$this->uid){
            $this->ajaxReturn(array('status'=>'login','msg'=>"请先登录！"));
        }
        if(!I('store_id')){
            $this->ajaxReturn(array('status'=>'error','msg'=>"非法操作"));
        }
        if(!I('shop_id')){
            $this->ajaxReturn(array('status'=>'error','msg'=>"非法操作"));
        }
        
        $chaoshi_uid = D('Shop')->where(array('shop_id'=>I('shop_id')))->getField('user_id');
        if($this->uid == $chaoshi_uid){
            D('ChaoshiCart')->where(array('store_id'=>I('store_id')))->delete();
            $this->ajaxReturn(array('status'=>'error','msg'=>"不能购买自家的商品"));
        }
        $qisongjia = D('Chaoshi')->where(array('store_id'=>I('store_id')))->getField('since_money');
        $cart_info = D('ChaoshiCart')->get_store_cart_info($this->uid,I('store_id'));
       
        if($qisongjia>$cart_info['total']){
            $this->ajaxReturn(array('status'=>'error','msg'=>"总价小于起送价"));
        }
        $this->ajaxReturn(array('status'=>'success','msg'=>"结算中……。",'url'=>U('pay',array('store_id'=>I('store_id')))));

    }


}
