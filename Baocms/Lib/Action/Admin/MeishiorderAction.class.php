<?php

/*
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class MeishiorderAction extends CommonAction {

    public function index() {
        $MeishiOrder = M('Meishi_order');
        import('ORG.Util.Page'); // 导入分页类

        // 显示待审核的评论
        $map = array('closed' => 0);

        // 保存搜索信息到cookie中
        if(cookie(md5('MeishiOrderSearchIndexMessage'))){
            $map = cookie(md5('MeishiOrderSearchIndexMessage'));
            if(cookie(md5('MeishiOrderSearchIndexMessageOrder'))){
                $map['order_id'] = cookie(md5('MeishiOrderSearchIndexMessageOrder'));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 搜索信息待完善
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['order_id'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }

            if (!$keyword) {
                // 根据提交方式判断，是否清除cookie相应信息
                unset($map['name']);
            }

            // 支付状态搜索
            if ($pay_status = (int)$this->_param('pay_status')) {
                if ($pay_status == 100) {
                    unset($map['pay_status']);
                } elseif ($pay_status == 99) {
                    $map['pay_status'] = 0;
                } else {
                    $map['pay_status'] = $pay_status;
                }
                $this->assign('pay_status', $map['pay_status']);
            }

            // 支付方式搜索
            if ($pay_type = (int)$this->_param('pay_type')) {
                if ($pay_type == 100) {
                    unset($map['pay_type']);
                } elseif ($pay_type == 2) {
                    $map['pay_type'] = 2;
                } elseif ($pay_type == 3) {
                    $map['pay_type'] = 3;
                } else {
                    $map['pay_type'] = 4;
                }
                $this->assign('pay_type', $map['pay_type']);
            }

            // 订单状态搜索
            if ($status = (int)$this->_param('status')) {
                if ($status == 100) {
                    unset($map['status']);
                } else if ($status == 99) {
                    $map['status'] = 0;
                } else {
                    $map['status'] = $status;
                }
                $this->assign('status', $map['status']);
            }

            // 订单类型
            if ($product_type = (int)$this->_param('product_type')) {
                if ($product_type == 100) {
                    unset($map['product_type']);
                } else {
                    $map['product_type'] = $product_type;
                }
                $this->assign('product_type', $map['product_type']);
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $keyword = substr(str_replace(' ', '', $map['order_id'][1]), 1, -1);
            if($keyword != ''){
                $this->assign('keyword', $keyword);
            }
        }

        // 保存搜索信息到cookie中，有效时间15分钟
        cookie(md5('MeishiOrderSearchIndexMessage'), $map, 900);
        cookie(md5('MeishiOrderSearchIndexMessageOrder'), $map['order_id'], 900);
//        var_dump($map);

        // 分页
        $Page = new Page($MeishiOrder->where($map)->count(), 25);   // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();   // 分页显示输出

        $list = $MeishiOrder
            ->field('order_id, store_id, user_id, use_time, money, phone, num, pay_status, pay_type, status, create_time, product_type')
            ->where($map)
            ->order(array('order_id' => 'desc'))
            ->limit($Page->firstRow, $Page->listRows)
            ->select();
//        var_dump($list[0]);

        // 选中状态
        $this->assign('pay_status_2', $map['pay_status']);
        $this->assign('pay_type_2', $map['pay_type']);
        $this->assign('status_2', $map['status']);
        $this->assign('product_type_2', $map['product_type']);
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $show);   // 赋值分页输出
        $this->display();   // 输出模板
    }

    public function delete($order_id = 0) {
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Meishiorder');
            $obj->save(array('order_id' => $order_id, 'closed' => 1));
            $this->baoSuccess('取消订单成功！', U('meishiorder/index'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Meishiorder');
                foreach ($order_id as $id) {
                    $detail = $obj->find($id);
                    if ($detail['status'] >= 1) {
                        $obj->save(array('order_id' => $id, 'closed' => 1));
                    }
                }
                $this->baoSuccess('取消订单成功！', U('meishiorder/index'));
            }
            $this->baoError('请选择要取消的订单');
        }
    }

    /**peace
     * 接收一个订单的order_id
     * 订单详情页面
     */
    public function detail($order_id){
        // 有效的订单
        $map = array('closed' => 0);
        if(is_numeric($order_id) && ($order_id = (int)$order_id)){
            $orderModel = D('MeishiOrder');
            $list = $orderModel
                ->field('bao_meishi_order.*, bao_meishi.store_name, bao_users.nickname as user_name')
                ->join('LEFT JOIN bao_meishi ON bao_meishi_order.store_id=bao_meishi.store_id')
                ->join('LEFT JOIN bao_users ON bao_meishi_order.user_id=bao_users.user_id')
                ->where($map)
                ->find($order_id);
            if(!$list){
                $this->baoError('该订单不存在或无效');
            }
//            var_dump($list);

            // html页面ajax请求订单商品详情；

            $this->assign('list', $list);
            $this->display();
        }
        $this->baoError('该数据无效，请重新选择');
    }

    /**peace
     * ajax请求订单商品详情；
     */
    public function productDetail($order_id = 0, $create_time = 0){
        // 数据合法性验证
        if(!(is_numeric($order_id) && $order_id = (int)$order_id)){
            echo json_encode(array('msg' => '非法id数据', 'error' => '400'));
        }
        if(!(is_numeric($create_time) && $create_time = (int)$create_time)){
            echo json_encode(array('msg' => '非法数据', 'error' => '400'));
        }

        // 查找该订单数据
        $findCreate_time = M('MeishiOrder')
            ->where(array('order_id' => $order_id))
            ->getField('create_time');
        if($findCreate_time != $create_time){
            echo json_encode(array('msg' => '未找到订单信息', 'error' => '204'));
        }

        // 查找订单商品
        $findOrderProducts = M('MeishiOrderProduct')
            ->alias('ord')
            ->field('ord.*, pro.product_name')
            ->join('LEFT JOIN bao_meishi_product pro ON pro.product_id=ord.product_id')
            ->where(array('order_id' => $order_id))
            ->select();
        if(!$findOrderProducts){
            echo json_encode(array('msg' => '未找到订单商品信息', 'error' => '204'));
        }
        echo json_encode(array('msg' => '执行成功', 'info' => $findOrderProducts, 'error' => '200'));
    }

    /**peace
     * 初始化搜索引擎，订单列表
     */
    public function initialIndex(){
        cookie(md5('MeishiOrderSearchIndexMessage'), NULL, 0);
        $this->baoSuccess('初始化成功', U('meishiorder/index'), 1000);
    }

}
