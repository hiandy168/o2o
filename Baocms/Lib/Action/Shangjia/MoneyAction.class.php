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

    public function tixian()
    {
        if (IS_POST) {
            $money = I('money');
            if ($money <= 0) {
                $this->baoError('提现金额不合法');
            }
            if ($money > $this->member['money']
                || $this->member['money'] == 0
            ) {
                $this->baoError('余额不足，无法提现');
            }
            if (!$data['bank_name'] = htmlspecialchars($_POST['bank_name'])) {
                $this->baoError('开户行不能为空');
            }
            if (!$data['bank_num'] = htmlspecialchars($_POST['bank_num'])) {
                $this->baoError('银行账号不能为空');
            }

            if (!$data['bank_realname'] = htmlspecialchars(
                $_POST['bank_realname']
            )
            ) {
                $this->baoError('开户姓名不能为空');
            }
            $data['bank_branch'] = htmlspecialchars($_POST['bank_branch']);
            $data['user_id'] = $this->uid;

            $arr = array();
            $arr['user_id'] = $this->uid;
            $arr['money'] = $money;
            $arr['addtime'] = NOW_TIME;
            $arr['account'] = $this->member['account'];
            $arr['bank_name'] = $data['bank_name'];
            $arr['bank_num'] = $data['bank_num'];
            $arr['bank_realname'] = $data['bank_realname'];
            $arr['bank_branch'] = $data['bank_branch'];
            D("Userscash")->add($arr);
            D('Usersex')->save($data);
            D('Users')->addMoney($this->uid, -$money, '申请提现，扣款');
            $userInfo = $this->member;
            D('Sms')->sendSms('money_back_want', $userInfo['mobile'], array(
                'money' => $money
            ));
            $this->baoSuccess('申请成功', U('money/tixianlog'));
        } else {
            $this->assign('info', D('Usersex')->getUserex($this->uid));
            $this->display();
        }
    }

    public function tixianlog()
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
        $Userscash = D('Userscash');
        import('ORG.Util.Page'); // 导入分页类
        $count = $Userscash->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 16); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))
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

    public function bind(){
        $this->display();
    }

}
