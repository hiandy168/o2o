<?php

/**
 * Created by PhpStorm.
 * User: 增和平
 * Date: 2017/1/7
 * Time: 10:57
 */
class ShopcashAction extends CommonAction
{
    /**peace
     * 商家资金管理
     */
    public function index(){
        $map = [];
        if(isset($_POST['account']) && I('post.account')){
            $map['bank_realname'] = I('post.account');
            $this->assign('account', $map['bank_realname']);
        }
        if(isset($_POST['status'])){
            $status = I('post.status');
            if(in_array($status, [0,1,2])){
                $map['status'] = $status;
                $this->assign('status', $map['status']);
            }
        }
        //
        import('ORG.Util.Page');
        // 状态
//        $map['status'] = 0;
        $count = M('ShopCash')->where($map)->count();
        $page = new Page($count, 15);
        $show = $page->show();                               // 显示分页信息
        $fields = ['cash_id', 'bank_name', 'bank_num', 'bank_branch', 'bank_realname', 'bank_telephone', 'reason', 'addtime', 'money', 'status'];
        $cashInfo = M('ShopCash')->field($fields)->where($map)->order('cash_id DESC')->limit($page->firstRow, $page->listRows)->select();

        // 显示信息
        $this->assign('page', $show);
        $this->assign('cash', $cashInfo);
        $this->display();

    }

    /**peace
     * @param int $cash_id
     * 提现成功
     */
    public function audit($cash_id = 0){
        $shopCash = D('ShopCash');
        if (is_numeric($cash_id) && ($cash_id = (int)$cash_id)) {
            $data = $shopCash->find($cash_id);
            if ($data['status'] == 0) {
                $arr = array();
                $arr['cash_id'] = $cash_id;
                $arr['status'] = 1;
                M()->startTrans();
                $setStatus = $shopCash->save($arr);

                $shopInfo = D('Shop')->field(['user_id'])->where(array('shop_id' => $data['shop_id']))->find();
                $userMobile = D('Users')->field(['mobile'])->where(array('user_id' => $shopInfo['user_id']))->find();
                // 商家资金日志
                $datas = [
                    'user_id' => $shopInfo['user_id'],
                    'shop_id' => $data['shop_id'],
                    'store_id' => '',
                    'store_type' => 0,
                    'order_id' => 0,
                    'money' => '-'.$data['money'],
                    'intro' => '商家提现',
                    'create_time' => time(),
                    'create_ip' => get_client_ip(),
                ];
                $storeLog = M('StoreMoneyLogs')->add($datas);
                if(!$setStatus || !$storeLog){
                    $this->baoSuccess('操作失败！', U('shopcash/index'));
                }

                D('Sms')->sendSms('money_cash_ok', $userMobile['mobile'], array('money' => $data['money']));
                M()->commit();
                $this->baoSuccess('操作成功！', U('shopcash/index'));
            } else {
                $this->baoError('请不要重复操作');
            }
        } else {
            $cash_id = $this->_post('cash_id', FALSE);
            if (!is_array($cash_id)) {
                $this->baoError('请选择要审核的提现');
            }
            foreach ($cash_id as $id) {
                $data = $shopCash->find($id);
                if ($data['status'] > 0) {
                    continue;
                }
                $arr = array();
                $arr['cash_id'] = $id;
                $arr['status'] = 1;
                M()->startTrans();
                $setStatus = $shopCash->save($arr);
                $shopInfo = D('Shop')->field(['user_id'])->where(array('shop_id' => $data['shop_id']))->find();
                $userMobile = D('Users')->field(['mobile'])->where(array('user_id' => $shopInfo['user_id']))->find();
                // 商家资金日志
                $datas = [
                    'user_id' => $shopInfo['user_id'],
                    'shop_id' => $data['shop_id'],
                    'store_id' => '',
                    'store_type' => 0,
                    'order_id' => 0,
                    'money' => '-'.$data['money'],
                    'intro' => '商家提现',
                    'create_time' => time(),
                    'create_ip' => get_client_ip(),
                ];
                $storeLog = M('StoreMoneyLogs')->add($datas);
                if(!$setStatus || !$storeLog){
                    $this->baoSuccess('操作失败！', U('shopcash/index'));
                }

                D('Sms')->sendSms('money_cash_ok', $userMobile['mobile'], array('money' => $data['money']));
            }
            M()->commit();
            $this->baoSuccess('操作成功！', U('shopcash/index'));
        }
    }

    /**peace
     * 提现失败
     */
    public function refuse(){
        $cash_id = I('post.cash_id', 0, 'intval');
        $value = $this->_param('value', 'htmlspecialchars');
        if (empty($value)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请填写提现失败原因'));
        }
        if (empty($cash_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '参数错误'));
        }
        M()->startTrans();
        $data = D('ShopCash')->where(array('cash_id' => $cash_id))->find();
        $setFail = M('ShopCash')->save(array('cash_id' => $cash_id, 'status' => 2, 'reason' => $value));
        $setMoney= M('Shop')->where(['shop_id' => $data['shop_id']])->setInc('money', $data['money']);
        if(!$setFail || !$setMoney){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '操作失败', 'url' => U('shopcash/index')));
        }

        $shopInfo = D('Shop')->field(['user_id'])->where(array('shop_id' => $data['shop_id']))->find();
        $userMobile = D('Users')->field(['mobile'])->where(array('user_id' => $shopInfo['user_id']))->find();

        D('Sms')->sendSms('money_cash_no', $userMobile['mobile'], array('money' => $data['money']));
        M()->commit();
        $this->ajaxReturn(array('status' => 'error', 'msg' => '提现失败', 'url' => U('shopcash/index')));
    }

}