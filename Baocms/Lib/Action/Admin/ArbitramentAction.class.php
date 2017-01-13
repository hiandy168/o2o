<?php

/**
 * Created by PhpStorm.
 * User: 曾和平
 * Date: 2016/10/18
 * Time: 13:19
 */
class ArbitramentAction extends CommonAction
{
    public function _initialize() {
        parent::_initialize();
    }

    /**
     * @var array
     * 大后台管理的相关表单：bao_arbitrament, 各板块的订单表的相关字段(`refund_status`, `pay_status`, `arbitrament_status`, `store_arbitrament_status`),
     */

    // 订单类型  超市   外卖   美食   酒店   房产
    // array('1' => 'chaoshi', '2' => 'ele', '3' => 'meishi', '4' => 'hotel', '5' => 'house')
    // $order_type_detail与$order_type是一一对应关系，顺序不能改动，添加也要按顺序，
    // 订单的id均为
    // 若此处的信息更改，则对应的买家中心、移动端接口和卖家中心的信息也应更改
    private $order_type = array(1, 2, 3, 4, 5);
    private $order_type_table = array(1 => 'Chaoshi', 2 => 'Ele', 4 => 'Hotel', 5 => 'HouseStore');
    private $order_type_detail = array(1 => 'Chaoshi_order', 2 => 'Ele_Order', 4 => 'Hotel_order', 5 => 'House_order');

    // 后台审查，合法性验证
    private $agrees = array(0, 1);

    /**peace
     * 仲裁列表页面
     * 排序：创建时间
     */
    public function index(){
        $ArbitramentModel = M('Arbitrament');
        import('ORG.Util.Page');
        // 页面筛选条件
        $map = array();

        if(cookie(md5('ArbitramentSearchIndexMessage'))){
            $map = cookie(md5('ArbitramentSearchIndexMessage'));
            if(cookie(md5('ArbitramentSearchIndexMessageId'))){
                $map['order_id'] = cookie(md5('ArbitramentSearchIndexMessageId'));
            }
        };

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // 搜索信息待完善
            if ($keyword = (int)$this->_param('keyword', 'htmlspecialchars')) {
                if($keyword != ''){
                    $map['order_id'] = array('LIKE', '%' . $keyword . '%');
                    $this->assign('keyword', $keyword);
                }
            }
            if($keyword == false){
                unset($map['order_id']);
            }

            // 订单类型搜索
            if ($order_type = (int) $this->_param('order_type')) {
                if(in_array($order_type, array(1,2,3,4,5))){
                    $map['order_type'] = $order_type;
                    $this->assign('order_type', $order_type);
                }else{
                    unset($map['order_type']);
                }
            }

            // 仲裁处理状态
            if ($status= (int) $this->_param('status')) {
                if(in_array($status, array(1,2))){
                $map['status'] = $status;
                $this->assign('status', $status);
                }else{
                    unset($map['status']);
                }
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'GET'){
            $order_id_temp = substr($map['order_id'][1], 1, -1);
            if($order_id_temp != ''){
                $this->assign('keyword', $order_id_temp);
            }
        }
//        var_dump($map);

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('ArbitramentSearchIndexMessage'), $map, 900);
        cookie(md5('ArbitramentSearchIndexMessageId'), $map['order_id'], 900);

        // 分页文件
        $Page = new Page($ArbitramentModel->where($map)->count(), 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        // 再另外查询用户的昵称
        $list = $ArbitramentModel
            ->alias('ar')
            ->field('ar.id, ar.order_type, ar.order_id, ar.user_nickname as consignee, ar.store_name, ar.create_time, ar.status, bao_users.nickname')
            ->join('LEFT JOIN bao_users ON ar.user_id=bao_users.user_id')
            ->where($map)
            ->order(array('ar.status' => 'ASC', 'ar.create_time' => 'DESC'))
            ->limit($Page->firstRow, $Page->listRows)
            ->select();

        // 选中搜索条件
        $this->assign('status_2', $map['status']);
        $this->assign('order_type_2', $map['order_type']);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    /**peace
     * @param int $arbitrament_id
     * 商家和买家的详细信息页面，双方的信息进行对比，并进行裁定
     * 测试使用的仲裁单号ID  31
     */
    public function detail($arbitrament_id = 31){
        // 表单提交
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // 退款金额
            $refund_money = I('post.refund_money', 0, 'intval');
            if($refund_money <= 0){
                $this->baoError('退款金额需大于0');
            }

            // 检验id合法性
            $findArbi = $this->checkArbitramentId(I('post.arbitrament_id', 0, 'intval'));

            // 判断订单类型有效性
            $findOrder = $this->checkOrderId($findArbi);

            // 单选框agree  0 => 同意买家    1 => 同意商家
            $agree = I('post.agree', 10, 'intval');
            if(!(is_numeric($agree) && $agree == (int)$agree)){
                $this->baoError('数值错误');
            }
            if(!in_array($agree, $this->agrees)){
                $this->baoError('非法的数值');
            }

            // 存放要更改的数值
            $changeArbi = array();       // 仲裁状态变化
            $dataRefundLogs = array();   // 退款日志状态
            $refund_status = 3;
            // 仲裁处理意向
            if($agree == 0){
                $changeArbi['arbitrament_status'] = 1;
                $refund_status = 2;
                $dataRefundLogs = array(
                    'status' => 2,
                );
            }
            if($agree == 1){
                $changeArbi['arbitrament_status'] = 2;
//                $dataRefundLogs = array(
//                    'status' => 3,
//                );
            }

            M()->startTrans();
            if(($agree == 0) && ($findArbi['order_type'] == 4)) {
                // 对于酒店仲裁
                // 同意退款，买家合理的退款方案
                if($findOrder['refund_status'] == 2){
                    $users_mod = D('Users');
                    $user_money_logs_mod = D('UserMoneyLogs');
                    $store_money_logs_mod = D('StoreMoneyLogs');
                    $tuik_logs_mod = D('HotelOrderTuikLogs');
                    $store_mod = D('Hotel');
                    $shop_mod = D('Shop');

                    // 查找商家日志表，已退款金额
                    $refund = '0.00';
                    $findStoreLogs = M('StoreMoneyLogs')
                        ->where(['order_id' => $findOrder['store_id'], 'order_type' => 4])
                        ->find();
                    if(!$findStoreLogs){
                        $this->baoError('已全额退款');
                    }else{
                        $refund = $findStoreLogs['money'];
                    }

                    $store = $store_mod->find($findOrder['store_id']);
                    // 退款金额重新赋值
                    $findOrder['pay_price'] = $refund;
                    $money = floor($refund_money*100)/100;
                    if ($money < 0.01 || $money > $findOrder['pay_price']) {
                        $this->baoError('退款金额有误');
                    }
                    $order_id = $findOrder['order_id'];
                    $difference_value = $findOrder['pay_price'] - $money;

                    $ip = get_client_ip();
//                    $inventory_res = D('HotelProduct')->where(array('product_id'=>$findOrder['order_room']['product_id']))->setInc('inventory',$findOrder['order_room']['num']);
                    if ($difference_value > 0){                                             //当不是退全款时，要把剩余的金额打给商家
                        $order_res = D('HotelOrder')->where(array('order_id'=>$order_id))
                            -> setField(array(
                                'penal_sum' => $difference_value,
                                'refund_money' => $money + $findOrder['refund_money'],
//                                'handle_refund_time' => NOW_TIME,
                                'refund_flow' => 1,
                                'handle_arbitrament_time' => NOW_TIME));
                        $user_money_res = $users_mod->where(array('user_id'=>$findOrder['user_id']))->setInc('money',$money);
                        $shop_money_res = $shop_mod->where(array('shop_id'=>$store['shop_id']))->setDec('money',$difference_value);
                        $tuik_logs_res = $tuik_logs_mod->where(array('order_id'=>$order_id))
                            ->setField(array(
                                'arbitrament_time' => NOW_TIME,
                                'arbitrament_refund_time' => NOW_TIME,
                                'arbitrament_refund_status' => 2,
                                'arbitrament_refund_money' => $money));
                        $user_money_logs_res = $user_money_logs_mod->add(array(            // 写用户资金日志表
                            'user_id' => $findOrder['user_id'],
                            'order_id' => $order_id,
                            'order_type' => 4,
                            'type' => 3,
                            'money' => $money,
                            'intro' => '酒店仲裁退款到余额',
                            'create_time' => NOW_TIME,
                            'create_ip' => $ip,
                        ));
                        $shop_money_logs_res = $store_money_logs_mod->add(array(         // 写商家资金日志表
                            'user_id' => $findOrder['user_id'],
                            'store_id' => $findOrder['store_id'],
                            'shop_id' => $store['shop_id'],
                            'store_type' => 4,
                            'money' => '-'.$difference_value,
                            'create_ip' => $ip,
                            'create_time' => NOW_TIME,
                            'order_id' => $order_id,
                            'intro' => '酒店订单'.$order_id.'仲裁退款',
                        ));
                        //$order_res && $user_money_res && $shop_money_res && $user_money_logs_res && $shop_money_logs_res && $tuik_logs_res && $inventory_res
                        if ($order_res && $user_money_res && $user_money_logs_res && $tuik_logs_res && $shop_money_logs_res) {
                            M()->commit();
                            $this->baoSuccess('操作成功',U('arbitrament/index'));
                        }else {
                            M()->rollback();
                            $this->baoError('操作失败');
                        }
                    }elseif ($difference_value == 0){                                       //退全款
                        $order_res = D('HotelOrder')->where(array('order_id'=>$order_id))
                            ->setField(array(
                                'pay_status'=>2,
                                'status'=>5,
                                'refund_status'=>2,
                                'handle_refund_time'=>NOW_TIME,
                                'penal_sum' => $difference_value,
                                'refund_money' => $money));
                        $user_money_res = $users_mod->where(array('user_id'=>$findOrder['user_id']))->setInc('money',$findOrder['pay_price']);
                        $shop_money_res = $shop_mod->where(array('shop_id'=>$store['shop_id']))->setDec('money',$money);
                        $tuik_logs_res = $tuik_logs_mod->where(array('order_id'=>$order_id))
                            ->setField(array(
                                'arbitrament_time' => NOW_TIME,
                                'arbitrament_refund_time' => NOW_TIME,
                                'arbitrament_refund_status' => 2,
                                'arbitrament_refund_money' => $money));
                        $user_money_logs_res = $user_money_logs_mod->add(array(            //写用户资金日志表
                            'user_id' => $findOrder['user_id'],
                            'order_id' => $order_id,
                            'order_type' => 4,
                            'type' => 3,
                            'money' => $money,
                            'intro' => '酒店仲裁退款到余额',
                            'create_time' => NOW_TIME,
                            'create_ip' => $ip,
                        ));
                        $shop_money_logs_res = $store_money_logs_mod->add(array(                               //写商家资金日志表
                            'user_id' => $findOrder['user_id'],
                            'store_id' => $findOrder['store_id'],
                            'shop_id' => $store['shop_id'],
                            'store_type' => 4,
                            'money' => '-'.$money,
                            'create_ip' => $ip,
                            'create_time' => NOW_TIME,
                            'order_id' => $order_id,
                            'intro' => '酒店订单'.$order_id.'仲裁退款',
                        ));
                        if ($order_res && $user_money_res && $user_money_logs_res && $tuik_logs_res) {
                            M()->commit();
                            $this->baoSuccess('操作成功',U('arbitrament/index'));
                        }else {
                            M()->rollback();
                            $this->baoError('操作失败');
                        }
                    }else {
                        $this->baoError('内部错误');
                    }
                }
                // 拒绝退款，买家合理的退款方案
                if ($findOrder['refund_status'] == 3){
                    $users_mod = D('Users');
                    $user_money_logs_mod = D('UserMoneyLogs');
                    $store_money_logs_mod = D('StoreMoneyLogs');
                    $tuik_logs_mod = D('HotelOrderTuikLogs');
                    $store_mod = D('Hotel');
                    $shop_mod = D('Shop');

                    $store = $store_mod->find($findOrder['store_id']);
                    $money = floor($refund_money*100)/100;
                    if ($money < 0.01 || $money > $findOrder['pay_price']) {
                        $this->baoError('退款金额有误');
                    }
                    $order_id = $findOrder['order_id'];
                    $difference_value = $findOrder['pay_price'] - $money;

                    $ip = get_client_ip();
                    $inventory_res = D('HotelProduct')->where(array('product_id'=>$findOrder['order_room']['product_id']))->setInc('inventory',$findOrder['order_room']['num']);
                    if ($difference_value > 0){                                             //当不是退全款时，要把剩余的金额打给商家
                        $order_res = D('HotelOrder')->where(array('order_id'=>$order_id))
                            -> setField(array(
                                'pay_status' => 2,
                                'status' => 5,
                                'refund_status' => 2,
                                'penal_sum' => $difference_value,
                                'refund_money' => $money,
                                'handle_refund_time' => NOW_TIME,
                                'refund_flow' => 1,
                                'handle_arbitrament_time' => NOW_TIME));
                        $user_money_res = $users_mod->where(array('user_id'=>$findOrder['user_id']))->setInc('money',$money);
                        $shop_money_res = $shop_mod->where(array('shop_id'=>$store['shop_id']))->setInc('money',$difference_value);
                        $tuik_logs_res = $tuik_logs_mod->where(array('order_id'=>$order_id))
                            ->setField(array(
                                'status' => 4,
                                'refund_endtime' => NOW_TIME ,
                                'audit_time' => NOW_TIME,
                                'arbitrament_time' => NOW_TIME,
                                'arbitrament_refund_time' => NOW_TIME,
                                'arbitrament_refund_status' => 2,
                                'arbitrament_refund_money' => $money));
                        $user_money_logs_res = $user_money_logs_mod->add(array(            //写用户资金日志表
                            'user_id' => $findOrder['user_id'],
                            'order_id' => $order_id,
                            'order_type' => 4,
                            'type' => 3,
                            'money' => $money,
                            'intro' => '酒店仲裁退款到余额',
                            'create_time' => NOW_TIME,
                            'create_ip' => $ip,
                        ));
                        $shop_money_logs_res = $store_money_logs_mod->add(array(                               //写商家资金日志表
                            'user_id' => $findOrder['user_id'],
                            'store_id' => $findOrder['store_id'],
                            'shop_id' => $store['shop_id'],
                            'store_type' => 4,
                            'money' => $difference_value,
                            'create_ip' => $ip,
                            'create_time' => NOW_TIME,
                            'order_id' => $order_id,
                            'intro' => '酒店订单'.$order_id.'退款后部分剩余资金',
                        ));
                        //$order_res && $user_money_res && $shop_money_res && $user_money_logs_res && $shop_money_logs_res && $tuik_logs_res && $inventory_res
                        if ($order_res && $user_money_res && $user_money_logs_res && $tuik_logs_res && $inventory_res && $shop_money_logs_res) {
                            M()->commit();
                            $this->baoSuccess('操作成功',U('arbitrament/index'));
                        }else {
                            M()->rollback();
                            $this->baoError('操作失败');
                        }
                    }elseif ($difference_value == 0){                                       //退全款
                        $order_res = D('HotelOrder')->where(array('order_id'=>$order_id))
                            -> setField(array(
                                'pay_status'=>2,
                                'status'=>5,
                                'refund_status'=>2,
                                'handle_refund_time'=>NOW_TIME,
                                'penal_sum' => $difference_value,
                                'refund_money' => $money));
                        $user_money_res = $users_mod->where(array('user_id'=>$findOrder['user_id']))->setInc('money',$findOrder['pay_price']);
                        $tuik_logs_res = $tuik_logs_mod->where(array('order_id'=>$order_id))
                            ->setField(array(
                                'status' => 4,
                                'refund_endtime' => NOW_TIME ,
                                'audit_time' => NOW_TIME,
                                'arbitrament_time' => NOW_TIME,
                                'arbitrament_refund_time' => NOW_TIME,
                                'arbitrament_refund_status' => 2,
                                'arbitrament_refund_money' => $money));
                        $user_money_logs_res = $user_money_logs_mod->add(array(            //写用户资金日志表
                            'user_id' => $findOrder['user_id'],
                            'order_id' => $order_id,
                            'order_type' => 4,
                            'type' => 3,
                            'money' => $money,
                            'intro' => '酒店仲裁退款到余额',
                            'create_time' => NOW_TIME,
                            'create_ip' => $ip,
                        ));
                        if ($order_res && $user_money_res && $user_money_logs_res && $tuik_logs_res && $inventory_res) {
                            M()->commit();
                            $this->baoSuccess('操作成功',U('arbitrament/index'));
                        }else {
                            M()->rollback();
                            $this->baoError('操作失败');
                        }
                    }else {
                        $this->baoError('内部错误');
                    }
                }
            }
            // 主要是恢复状态
            if(($agree == 1) && ($findArbi['order_type'] == 4)){
                // 同意退款，买家合理的退款方案
                $tuik_logs_mod = D('HotelOrderTuikLogs');
                $order_id = $findOrder['order_id'];
                $order_res = D('HotelOrder')->where(array('order_id'=>$order_id))-> setField(array('refund_flow' => 1, 'handle_arbitrament_time' => NOW_TIME));
                $tuik_logs_res = $tuik_logs_mod->where(array('order_id'=>$order_id))->setField(array('arbitrament_time' => NOW_TIME));
                if ($order_res && $tuik_logs_res) {
                    M()->commit();
                    $this->baoSuccess('操作成功',U('arbitrament/index'));
                }else {
                    M()->rollback();
                    $this->baoError('操作失败');
                }

            }
            // 酒店仲裁判断结束

            if(($agree == 0) && (($findArbi['order_type'] == 1) || ($findArbi['order_type'] == 2))){
                // 退款日志 bao_chaoshi/ele/etc..._order_tuik_logs
                $orderRefundLogs = $this->order_type_table[$findArbi['order_type']] . '_order_tuik_logs';
                $refundLogs = M($orderRefundLogs)
                    ->where(array('order_id' => $findArbi['order_id']))
                    ->save($dataRefundLogs);
                if (!$refundLogs) {
                    M()->rollback();
                    $this->baoError('退款失败，退款日志未完成');
                }
            }

            // 仲裁结果和状态
            $changeArbi['arbitrament'] = I('post.arbitrament_rst');
            $changeArbi['status'] = 2;
            // 处理人的信息
            $changeArbi['handle_id'] = $_SESSION['admin']['admin_id'];
            $changeArbi['handle_time'] = $_SERVER['REQUEST_TIME'];
//            var_dump($changeArbi);

            // 保存结果，开启事务
//            M()->startTrans();
            $saveArbi = M('Arbitrament')
                ->where(array('id' => $arbitrament_id))
                ->save($changeArbi);

            if(!$saveArbi){
                M()->rollback();
                $this->baoError('仲裁结果保存失败');
            }

            // 对应的订单状态，只有一个状态改为2
            // `arbitrament_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '仲裁0：未申请  1：已申请  2：已处理',
            $saveOrder = M($this->order_type_detail[$findArbi['order_type']])
                ->where(array('order_id' => $findArbi['order_id']))
//                ->save(array('arbitrament_status' => 2);
                ->save(array('arbitrament_status' => 2, 'refund_status' => $refund_status));
            if(!$saveOrder){
                M()->rollback();
                $this->baoError('订单状态更改失败');
            }

            // 买家退款开始或商家状态更改开始
            if($agree == 0){
                $this->refundUser($findArbi);
            }elseif ($agree == 1){
                $this->recoverOrder($findArbi);
            }else{
                $this->baoError('仲裁结果错误');
            }

            M()->commit();
            $this->baoSuccess('仲裁审查完成', U('Arbitrament/index'));
//            redirect('index',2,'仲裁完成...');
        }

        // 载入页面
        // 检验id合法性
        $findArbitrament = $this->checkArbitramentId($_GET['arbitrament_id']);

        // 判断订单类型有效性
        $findOrder = $this->checkOrderId($findArbitrament);
        // 查找店铺名和联系电话
        $storeInfo = M($this->order_type_table[$findArbitrament['order_type']])
            ->where(array('store_id' => $findOrder['store_id']))
            ->find();

        // 买家昵称
        $findNickname = M('Users')
            ->field('nickname')
            ->where(array('user_id' => $findOrder['user_id']))
            ->find();
        $findArbitrament['nickname'] = $findNickname['nickname'];

        // 查找订单对应的商品详情
        // 查找订单对应的商品详情
        $findOrderProducts = $this->orderProductDetail($findArbitrament['order_type'], $findArbitrament['order_id']);

        // 查找商家和买家上传的仲裁图片
        $findArbiPic = M('Arbitrament_pic')
            ->where(array('arbitrament_id' => $findArbitrament['id']))
            ->select();
        // 便利是否存在图片
        $storePicNum = 0;
        $userPicNum = 0;
        foreach($findArbiPic as $a){
            if($a['type'] == 1){
                ++ $storePicNum;
            }elseif($a['type'] == 2){
                ++ $userPicNum;
            }
        }

        // 对酒店的仲裁数据的单独处理
        if($findArbitrament['order_type'] == 4 && $findOrder['refund_status'] == 2){
            // 查找商家日志表，退款金额
            $findStoreLogs = M('StoreMoneyLogs')
                ->where(['order_id' => $findOrder['store_id'], 'order_type' => 4])
                ->find();
            if(!$findStoreLogs){
                $refund =[
                    'refunded' => $findOrder['pay_price'],
                    'unRefunded' => '0.00'
                ];
            }else{
                $refund =[
                    'refunded' => $findOrder['pay_price'] - $findStoreLogs['money'],
                    'unRefunded' => $findStoreLogs['money']
                ];
            }
            $this->assign('refund', $refund);
        }
        if($findArbitrament['order_type'] == 4 && $findOrder['refund_status'] == 3){
            $refund =[
                'refunded' => '0.00',
                'unRefunded' => $findOrder['pay_price']
            ];
            $this->assign('refund', $refund);
        }
//        var_dump($refund);

        // `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '处理状态1：未处理2：已处理',
        // 如果status是2，则显示详情；
        $this->assign('storeInfo', $storeInfo);
        $this->assign('orderProducts', $findOrderProducts);
        $this->assign('list', $findArbitrament);
        $this->assign('order', $findOrder);
        $this->assign('arbiPic', $findArbiPic);
        $this->assign('storePicNum', $storePicNum);
        $this->assign('userPicNum', $userPicNum);
        $this->display();
    }

    /**peace
     * 退款列表
     * 暂时弃用
     */
    public function refund(){
        $ArbitramentModel = M('Arbitrament');
        import('ORG.Util.Page');
        // 页面筛选条件
        $map = array('bao_arbitrament.status' => 2, 'bao_arbitrament.order_type' => $this->order_type[0]);    // 下标为0，值为1，表示社区超市

        // 搜索信息待完善
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['bao_arbitrament.order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

        // 订单类型搜索
        if ($order_type = (int) $this->_param('order_type')) {
            if($order_type != 1){
                $map['bao_arbitrament.order_type'] = $order_type;
                $this->assign('order_type', $order_type);
            }
        }

        // 退款处理状态
        if ($refund= (int) $this->_param('refund')) {
            if($refund != 100){
                $map['bao_arbitrament.refund'] = $refund;
                $this->assign('refund', $refund);
            }
        }

        // 订单类型
        if(!in_array($order_type, $this->order_type)){
            // 订单类型初始化为 1；
            $order_type = 1;
        }

        // 分页文件
        $Page = new Page($ArbitramentModel->where($map)->count(), 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        // 再另外查询用户的昵称
        // 使用的表单 bao_arbitrament, bao_users, bao_(chaoshi/ele/house/...)_order
        if(!in_array($order_type, $this->order_type)){
            $this->baoError('不存在的订单类型');
        }

        // 获取对应的订单表名
        $order_name = 'bao_'.strtolower($this->order_type_detail[$order_type]);
        $type_name = 'bao_'.strtolower($this->order_type_table[$order_type]);
        // 拼接链接对应的表单sql语句
        $joinType_consignee_phone = 'LEFT JOIN '.$order_name.' ON bao_arbitrament.order_id='.$order_name.'.order_id';
        $joinType_store_phone = 'LEFT JOIN '.$type_name.' ON bao_arbitrament.store_id='.$type_name.'.store_id';
        // 获取对应的表单字段信息，包括收货人电话,店铺电话
        $field = 'bao_arbitrament.*, bao_users.nickname, '.$order_name.'.phone as consignee_phone, '.$type_name.'.phone as store_phone';
        $list = $ArbitramentModel
            ->field($field)
            ->join('LEFT JOIN bao_users ON bao_arbitrament.user_id=bao_users.user_id')
            // 获取收货人联系电话
            ->join($joinType_consignee_phone)
            // 获取商家联系电话
            ->join($joinType_store_phone)
            ->where($map)
            ->order(array('bao_arbitrament.refund' => 'ASC', 'bao_arbitrament.status' => 'ASC', 'bao_arbitrament.create_time' => 'DESC'))
            ->limit($Page->firstRow, $Page->listRows)
            ->select();
//        var_dump($list[0]);

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    /**peace
     * 买家合理，退款给买家
     * `refund` tinyint(1) DEFAULT '1' COMMENT '退款处理，1未处理，2已退款，3继续正常流程',
     */
    protected function refundUser($findArbitrament){
        // 检验id合法性
//        $findArbitrament = $this->checkArbitramentId($_GET['arbitrament_id']);

        // 判断订单类型有效性
        $findOrder = $this->checkOrderId($findArbitrament);

        // 更改仲裁refund状态，退款给买家    // 开启事务
//        M()->startTrans();
        $this->changeRefund(2, $findArbitrament['id']);

        // 订单状态更改
        // status => 恢复之前状态(1，2)；refund_status => 1; pay_status => 1; referer_status => 合法性验证;
        // referer_status => 合法性验证;
        if(!in_array($findOrder['referer_status'], array(1,2))){
            M()->rollback();
            $this->baoError('退款失败，申请退款时订单状态错误');
        }

        // 退款取消订单
        $this->saveOrderStatusUser($findArbitrament['order_type'], $findOrder);
        return true;
    }

    /**peace
     * 商家合理，继续订单流程
     * `refund` tinyint(1) DEFAULT '1' COMMENT '退款处理，1未处理，2已退款，3继续正常流程',
     */
    protected function recoverOrder($findArbitrament){
        // 检验id合法性
//        $findArbitrament = $this->checkArbitramentId($_GET['arbitrament_id']);

        // 判断订单类型有效性
        $findOrder = $this->checkOrderId($findArbitrament);

        // 更改仲裁refund状态，恢复订单的状态
        $this->changeRefund(3, $findArbitrament['id']);

        // 订单状态更改
        // status => 恢复之前状态(1，2)；refund_status => 1; pay_status => 1; referer_status => 合法性验证;
        // referer_status => 合法性验证;
        if(!in_array($findOrder['referer_status'], array(1,2))){
            M()->rollback();
            $this->baoError('申请退款时订单状态错误');
        }

        // 恢复订单表状态
        $this->saveOrderStatusRecover($findArbitrament['order_type'], $findOrder);
        return true;
    }

    /**peace
     * 订单的商品详情
     * @param $order_type
     * @param $order_id
     */
    protected function orderProductDetail($order_type, $order_id){
        $orderTable = '';
        $field = '';
        $join = '';
        if($order_type == 1){
            $orderTable = 'ChaoshiOrderProduct';
            $field = 'bao_chaoshi_order_product.*, bao_chaoshi_product.product_name';
            $join = 'LEFT JOIN bao_chaoshi_product ON bao_chaoshi_product.product_id=bao_chaoshi_order_product.product_id';
        }elseif ($order_type == 2){
            $orderTable = 'EleOrderProduct';
            $field = 'bao_ele_order_product.*, bao_ele_product.product_name';
            $join = 'LEFT JOIN bao_ele_product ON bao_ele_product.product_id=bao_ele_order_product.product_id';
        }elseif ($order_type == 4){
            $orderTable = 'HotelOrderProduct';
            $field = 'bao_hotel_order_product.*, bao_hotel_product.product_name';
            $join = 'LEFT JOIN bao_hotel_product ON bao_hotel_product.product_id=bao_hotel_order_product.product_id';
        }else{
            $this->baoError('信息错误');
        }
        $findOrderProducts = M($orderTable)
            ->field($field)
            ->join($join)
            ->where(array('order_id' => $order_id))
            ->select();
        if(!$findOrderProducts){
            $this->baoError('查询错误');
        }

        return $findOrderProducts;
    }

    /**peace
     * 仲裁号判定及其合法性
     */
    protected function checkArbitramentId($arbitrament_id = 0){
        // 判定数据有效性
        if(!(is_numeric($arbitrament_id) && $arbitrament_id == (int)$arbitrament_id)){
            $this->baoError('仲裁ID信息错误');
        }

        // 判定存在性
        $findArbitrament = M('Arbitrament')
            ->where(array('id' => $arbitrament_id))
            ->find();
        if(!$findArbitrament){
            $this->baoError('未找到该仲裁ID');
        }
        return $findArbitrament;
    }

    /**peace
     * 订单判定及其合法性
     */
    protected function checkOrderId($findArbitrament){
        // 判断订单类型有效性
        if(!in_array($findArbitrament['order_type'], $this->order_type)){
            M()->rollback();
            $this->baoError('无效的订单类型');
        }

        // 查找对应的订单
        $findOrder = M($this->order_type_detail[$findArbitrament['order_type']])
            ->where(array('order_id' => $findArbitrament['order_id']))
            ->find();
        if(!$findOrder){
            M()->rollback();
            $this->baoError('未找到该订单');
        }
        return $findOrder;
    }

    /**peace
     * 更改仲裁refund状态
     */
    protected function changeRefund($refund, $id){
        $changeRefund = M('Arbitrament')
            ->where(array('id' => $id))
            ->save(array('refund' => $refund));
        if(!$changeRefund){
            M()->rollback();
            $this->baoError('退款失败，状态更改失败');
        }
        return true;
    }

    /**peace
     * 仲裁裁定以后状态变化, 买家合理并退款
     */
    protected function saveOrderStatusUser($order_type, $findOrder){
        $recover_status = M($this->order_type_detail[$order_type])
            ->where(array('order_id' => $findOrder['order_id']))
            // Status:4，refund_status:2，pay_status:2
//            ->save(array('status' => 4, 'pay_status' => 2));
            ->save(array('status' => 4, 'refund_status' => 2, 'pay_status' => 2));
        if(!$recover_status){
            M()->rollback();
            $this->baoError('非法的仲裁状态处理');
        }

        // 退款给用户，订单实际支付金额转入用户余额
        // 查找用户信息
        $findUser = M('Users')
            ->where(array('user_id' => $findOrder['user_id']))
            ->find();
        if(!$findUser){
            M()->rollback();
            $this->baoError('退款失败，未找到该用户');
        }

        $usersRst = M('Users')
            ->where(array('user_id' => $findOrder['user_id']))
            ->setInc('money', $findOrder['pay_price']);
        if(!$usersRst){
            M()->rollback();
            $this->baoError('退款失败，退款记录未完成');
        }

        // 添加转账日志
        $dataLogs = array(
            'user_id' => $findOrder['user_id'],
            'order_id' => $findOrder['order_id'],
            'order_type' => $order_type,
            'type' => 3,
            'money' => '-'.$findOrder['pay_price'],
            'intro' => '仲裁退款',
            'create_time' => $_SERVER['REQUEST_TIME'],
            'create_ip' => $_SERVER['REMOTE_ADDR']
        );
        $logRst = M('UserMoneyLogs')
            ->add($dataLogs);
        if(!$logRst){
            M()->rollback();
            $this->baoError('退款失败，用户交易日志未完成');
        }

        // 退款日志 bao_chaoshi/ele/etc..._order_tuik_logs
        $orderRefundLogs = $this->order_type_table[$order_type].'_order_tuik_logs';
        $dataRefundLogs = array(
            'status' => 4,
            'refund_endtime' => $_SERVER['REQUEST_TIME']
        );
        $refundLogs = M($orderRefundLogs)
            ->where(array('order_id' => $findOrder['order_id']))
            ->save($dataRefundLogs);
        if(!$refundLogs){
            M()->rollback();
            $this->baoError('退款失败，退款日志未完成');
        }

        // 还原库存，联合查询bao_chaoshi_order_product, bao_chaoshi_product,
        $tableOrderProduct = $this->order_type_table[$order_type].'_order_product';
        $tableProduct = 'bao_'.strtolower($this->order_type_table[$order_type]).'_product';
        $tableProductInventory = $this->order_type_table[$order_type].'_product';

        $findOrderProducts = M($tableOrderProduct)
            ->alias('op')
            ->field('op.product_id, op.num, pro.inventory')
            ->join('LEFT JOIN '.$tableProduct.' pro ON op.product_id=pro.product_id')
            ->where(array('op.order_id' => $findOrder['order_id']))
            ->select();
        foreach ($findOrderProducts as $findOrderProduct){
            if($findOrderProduct['inventory'] == NULL || !isset($findOrderProduct['inventory'])){
                M()->rollback();
                $this->baoError('退款失败，未找到全部订单商品');
            }
            $saveInventory = M($tableProductInventory)
                ->where(array('product_id' => $findOrderProduct['product_id']))
                ->setInc('inventory', $findOrderProduct['num']);
            if(!$saveInventory){
                M()->rollback();
                $this->baoError('退款失败，未退回全部商品库存');
            }
        }
//        M()->commit();
//        return $this->baoSuccess('该订单已退款', U('arbitrament/refund'));
        return true;
    }

    /**peace
     * 仲裁裁定以后状态变化，商家合理，继续订单流程
     */
    protected function saveOrderStatusRecover($order_type, $findOrder){
        $recover_status = M($this->order_type_detail[$order_type])
            ->where(array('order_id' => $findOrder['order_id']))
            // Status:恢复之前状态（状态只能是1,2），refund_status:3，pay_status:1
            ->save(array('status' => $findOrder['referer_status'], 'refund_status' => 3, 'pay_status' => 1));
        if(!$recover_status){
            M()->rollback();
            $this->baoError('非法的仲裁状态处理');
        }
//        M()->commit();
//        $this->baoSuccess('该订单已恢复仲裁申请前状态', U('arbitrament/refund'));
        return true;
    }

}