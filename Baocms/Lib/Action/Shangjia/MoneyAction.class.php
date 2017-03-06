<?php

class MoneyAction extends CommonAction
{

    protected function _initialize()
    {
        parent::_initialize();
        $this->assign('money', $this->shop['money']);

    }

    public function index()
    {
        $map = array('user_id' => $this->uid);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars'))
            && ($end_date = $this->_param('end_date', 'htmlspecialchars'))
        ) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time),
                array('EGT', $bg_time));
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
        $Usermoneylogs = D('Usermoneylogs');
        import('ORG.Util.Page'); // 导入分页类
        //        $map = array('user_id' => $this->uid);
        $count = $Usermoneylogs->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 16); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Usermoneylogs->where($map)->order(array('log_id' => 'desc'))
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    public function staticCate($shop_id)
    {
        $unionModel = new Model();
        $rawResult = $unionModel
            ->field(
                '"chaoshi" as type,"超市" as type_name,shop_id,store_id,store_name'
            )
            ->table(C('DB_PREFIX') . 'chaoshi')
            ->where('audit=1 and closed=0 and shop_id=' . $shop_id)
            ->union(
                array('field' => '"ele" as type,"外卖" as type_name,shop_id,store_id,store_name',
                    'table' => C('DB_PREFIX') . 'ele',
                    'where' => 'audit=1 and closed=0  and shop_id=' . $shop_id), true
            )
            ->union(
                array('field' => '"house_store" as type,"房产" as type_name,shop_id,store_id,store_name',
                    'table' => C('DB_PREFIX') . 'house_store',
                    'where' => 'audit=1 and closed=0 and shop_id=' . $shop_id), true
            )
            ->union(
                array('field' => '"house" as type,"美食" as type_name,shop_id,store_id,store_name',
                    'table' => C('DB_PREFIX') . 'meishi',
                    'where' => 'audit=1 and closed=0 and shop_id=' . $shop_id), true
            )
            ->union(
                array('field' => '"hotel" as type,"酒店" as type_name,shop_id,store_id,store_name',
                    'table' => C('DB_PREFIX') . 'hotel',
                    'where' => 'audit=1 and closed=0 and shop_id=' . $shop_id), true
            )
            ->select();
        $result = array();

        foreach ($rawResult as $val) {
            if (in_array($val['type'], array_keys($result))) {
                $result[$val['type']]['info'][]
                    = array('store_id' => $val['store_id'],
                    'store_name' => $val['store_name'],
                    'type' => $val['type']);
                $result[$val['type']]['ids'][] = $val['store_id'];
            } else {
                $result[$val['type']] = array(
                    'name' => $val['type_name'],
                    'ids' => array($val['store_id']),
                    'info' => array(array('store_id' => $val['store_id'],
                        'store_name' => $val['store_name'],
                        'type' => $val['type']))
                );
            }

        }
        return $result;
    }

    public function shopmoney()
    {
        $this->assign('store_info', $this->staticCate($this->shop['shop_id']));
        $map = array('shop_id' => $this->shop_id);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars'))
            && ($end_date = $this->_param('end_date', 'htmlspecialchars'))
        ) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time),
                array('EGT', $bg_time));
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
        // 加入分店筛选
        $store_cate = I('store_cate', 0);
        if ($store_cate) {
            $this->assign('store_cate', $store_cate);
            list($type, $ids) = explode('_', $store_cate);
            $map['store_id'] = array('in', explode(',', $ids));
            // 购买类型1：社区超市2：外卖3：美食4：酒店5：房产
            switch ($type) {
                case 'chaoshi':
                    $map['store_type'] = 1;
                    break;
                case 'ele':
                    $map['store_type'] = 2;
                    break;
                case 'meishi':
                    $map['store_type'] = 3;
                    break;
                case 'hotel':
                    $map['store_type'] = 4;
                    break;
                case 'house':
                    $map['store_type'] = 5;
                    break;
            }
        }
        $Storemoneylogs = D('Storemoneylogs');
        import('ORG.Util.Page'); // 导入分页类
        //        $map = array('user_id' => $this->uid);
        $count = $Storemoneylogs->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 16); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Storemoneylogs->where($map)->order(array('log_id' => 'desc'))
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    public function fetch_sms()
    {
        dump($this->member);
        /*$result = array(
            'status' => 1,
            'msg' => '发送成功'
        );
        if (isset($this->shop['tel'])) {
            $code = rand(1000, 9999);
            D('Sms')->sendSms('money_bank_sms', $this->shop['tel'], array(
                'code' => $code
            ));
            session('shop_fetch_code', $code);
        } else if (isset($this->member['mobile'])) {
            $code = rand(1000, 9999);
            D('Sms')->sendSms('money_bank_sms', $this->member['mobile'], array(
                'code' => $code
            ));
            session('shop_fetch_code', $code);
        } else {
            $result = array(
                'status' => 0,
                'msg' => '没有设置手机号'
            );
        }
        echo json_encode($result);*/
    }

    public function tixian()
    {
        $data = D('ShopFetchAccount')->where(array('shop_id' => $this->shop['shop_id']))
        ->find();
        if (!$data) {
            $this->error('请先绑定银行卡', U('Shangjia/money/bind'));
        }
        if (IS_POST) {
            if (I('post.pin', 0) != $this->member['pin']) {
                $this->error('支付密码错误');
            }
            $money = I('money', 0 ,'floatval');
            if ($money < 1 || $money > $this->shop['money']) {
                $this->error('金额错误');
            }
            $data['user_id'] = $this->uid;
            $arr = array();
            $arr['shop_id'] = $this->shop_id;
            $arr['money'] = $money;
            $arr['addtime'] = NOW_TIME;
            $arr['account'] = $this->member['account'];
            $arr['bank_name'] = $data['bank_name'];
            $arr['bank_num'] = $data['bank_num'];
            $arr['bank_realname'] = $data['bank_realname'];
            $arr['bank_branch'] = $data['bank_branch'];
            $arr['bank_telephone'] = $data['bank_telephone'];
            $arr['create_time'] = time();
            D("ShopCash")->add($arr);
            M()->execute(" update bao_shop set money = money - {$money} where shop_id = {$this->shop["shop_id"]}");
            $userInfo = $this->member;
            D('Sms')->sendSms('money_back_want', $userInfo['mobile'], array(
                'money' => $money
            ));
            $this->Success('申请成功', U('money/tixianlog'));
        } else {
            $this->assign('info', D('Usersex')->getUserex($this->uid))
            ->assign('data',$data);
            $this->display();
        }
    }

    public function tixianlog()
    {
        $map = array('shop_id' => $this->shop_id);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars'))
            && ($end_date = $this->_param('end_date', 'htmlspecialchars'))
        ) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date) + 86400;
            $map['addtime'] = array(array('ELT', $end_time),
                array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['addtime'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['addtime'] = array('ELT', $end_time);
            }
        }
        $Shopcash = D('ShopCash');
        import('ORG.Util.Page'); // 导入分页类
        $count = $Shopcash->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 16); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Shopcash->where($map)->order(array('cash_id' => 'desc'))
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }


    public function tjmonth()
    {
        $this->assign('store_info', $this->staticCate($this->shop['shop_id']));
        $store_cate = I('store_cate', 0);
        if ($store_cate) {
            $this->assign('store_cate', $store_cate);
            list($type, $ids) = explode('_', $store_cate);
            // 购买类型1：社区超市2：外卖3：美食4：酒店5：房产
            switch ($type) {
                case 'chaoshi':
                    $map['store_type'] = 1;
                    break;
                case 'ele':
                    $map['store_type'] = 2;
                    break;
                case 'meishi':
                    $map['store_type'] = 3;
                    break;
                case 'hotel':
                    $map['store_type'] = 4;
                    break;
                case 'house':
                    $map['store_type'] = 5;
                    break;
            }
        }
        $this->assign('store_cate', $store_cate);
        $Storemoneylogs = D('Storemoneylogs');
        import('ORG.Util.Page');// 导入分页类
        $count = $Storemoneylogs->tjmonthCount(
            "", $this->shop_id, $ids, $map['store_type']
        );// 查询满足要求的总记录数
        $Page = new Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        $list = $Storemoneylogs->tjmonth(
            "", $this->shop_id, $Page->firstRow, $Page->listRows, $ids,
            $map['store_type']
        );

        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function tjday()
    {
        $this->assign('store_info', $this->staticCate($this->shop['shop_id']));
        $store_cate = I('store_cate', 0);
        if ($store_cate) {
            $this->assign('store_cate', $store_cate);
            list($type, $ids) = explode('_', $store_cate);
            // 购买类型1：社区超市2：外卖3：美食4：酒店5：房产
            switch ($type) {
                case 'chaoshi':
                    $map['store_type'] = 1;
                    break;
                case 'ele':
                    $map['store_type'] = 2;
                    break;
                case 'meishi':
                    $map['store_type'] = 3;
                    break;
                case 'hotel':
                    $map['store_type'] = 4;
                    break;
                case 'house':
                    $map['store_type'] = 5;
                    break;
            }
        }
        $this->assign('store_cate', $store_cate);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars'))
            && ($end_date = $this->_param('end_date', 'htmlspecialchars'))
        ) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date) + 86400;
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            $bg_time = NOW_TIME - 86400 * 30;
            $bg_date = date('Y-m-d', $bg_time);
            $end_date = date('Y-m-d', NOW_TIME);
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
            $end_time = NOW_TIME + 86400;
        }
        $data = D('Storemoneylogs')->money(
            $bg_time, $end_time, $this->shop_id, $ids, $map['store_type']
        );
        $this->assign('data', $data);
        $this->display();
    }

    public function bind()
    {
        if (IS_AJAX) {
            if (I('code', 0) != session('shop_fetch_code')) {
                echo json_encode(array('status' => 0, 'msg' => '验证码错误'));
            } else {
                echo json_encode(array('status' => 1, 'msg' => '验证码正确'));
            }
        } else if (IS_POST) {
            $data = I('post.');
            $data['bank_telephone'] = $this->member['mobile'];
            $have = D('ShopFetchAccount')->where(array('shop_id' => $this->shop['shop_id']))
                ->find();
            if (isset($have)) {
                $result = D('ShopFetchAccount')->where(array('shop_id' => $this->shop['shop_id']))->save($data);
            } else {
                $data['shop_id'] = $this->shop['shop_id'];
                $data['bank_telephone'] = $this->member['mobile'];
                $result = D('ShopFetchAccount')->add($data);
            }

            if ($result) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $bind_info = D('ShopFetchAccount')
                ->where(array('shop_id' => $this->shop['shop_id']))
                ->find();
            $this->assign('info', $bind_info)->display();
        }

    }

    public function bind_sms()
    {
        $result = array(
            'status' => 1,
            'msg' => '发送成功'
        );
        if (isset($this->shop['tel'])) {
            $code = rand(1000, 9999);
            D('Sms')->sendSms('money_bank_sms', $this->shop['tel'], array(
                'code' => $code
            ));
            session('shop_fetch_code', $code);
        } else if (isset($this->member['mobile'])) {
            $code = rand(100000, 999999);
            D('Sms')->sendSms('money_bank_sms', $this->member['mobile'], array(
                'code' => $code
            ));
            session('shop_fetch_code', $code);
        } else {
            $result = array(
                'status' => 0,
                'msg' => '没有设置手机号'
            );
        }
        echo json_encode($result);
    }

}
