<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/16
 * Time: 13:21
 */
class StatisticsTradingAction extends CommonAction
{
    public function _initialize(){
        parent::_initialize();
    }

    /**peace
     * 交易额日月年统计，板块统计
     */
    public function index(){
        $map = [];
        $type = false;
//        $map['closed'] = 0;
//        $map['audit'] = ['IN', [0, 1]];
//        $map_sql = ' AND `closed` = 0 AND `audit` IN (0,1)';
        $map_sql = '';

        // 默认有30天
        $searchDays = 30;
        $nowTime = NOW_TIME;
        $this->assign('end_date', date('Y-m-d', NOW_TIME));
        // 搜索
        if(isset($_POST['bg_date']) && isset($_POST['end_date'])){
            $bgDate = I('post.bg_date', '');
            $endDate = I('post.end_date', '');
            $bgTime = strtotime($bgDate);
            $endTime = strtotime($endDate);
            if($bgTime && $endTime && ($endTime >= $bgTime + 86400) && $endTime < NOW_TIME){
                $bgTime = $bgTime - 86400;
                $searchDays = ceil(($endTime - $bgTime)/86400);
                $nowTime = $endTime;
                $this->assign('bg_date', $bgDate);
                $this->assign('end_date', $endDate);
            }
        }
        if(isset($_POST['city']) && I('post.city', 0, 'intval')){
            $map['city_id'] = I('post.city', 0, 'intval');
            $map_sql .= ' AND `city_id` = '.$map['city_id'];
            $this->assign('city', $map['city_id']);
        }
        if(isset($_POST['area']) && I('post.area', 0, 'intval')){
            $map['area_id'] = I('post.area', 0, 'intval');
            $map_sql .= ' AND `area_id` = '.$map['area_id'];
            $this->assign('area', $map['area_id']);
        }
        if(isset($_POST['type']) && I('post.type', 0, 'intval')){
            $type = I('post.type', 0, 'intval');
            $this->assign('type', $type);
        }

        $storeTypes = M('StoreClass')->field(['sc_id', 'sc_module'])->select();
        // 总交易金额统计
        // 板块搜索
        $storeClass = [];
        $module = '';
        $moduleStore = '';
        foreach ($storeTypes as &$storeType){
            $storeClass[] = $storeType['sc_id'];
            $storeType['module'] = $storeType['sc_module'];
            if($storeType['sc_module'] == 'HouseStore'){
                $storeType['sc_module'] = 'HouseOrder';
            }else{
                $storeType['sc_module'] = $storeType['sc_module'].'Order';
            }
            $storeType['bao_store'] = 'bao'.$this->lCase($storeType['module']);
            $storeType['bao_order'] = 'bao'.$this->lCase($storeType['sc_module']);
        }

        // 所属板块合法性验证
        if(!in_array($type, $storeClass)){
            $type = 0;
        }
        if($type != 0){
            foreach ($storeTypes as &$storeType){
                if($storeType['sc_id'] == $type){
                    $module = $storeType['sc_module'];
                    $moduleStore = $storeType['module'];
                    $baoStore = 'bao'.$this->lCase($storeType['module']);
                    $baoOrder = 'bao'.$this->lCase($storeType['sc_module']);
                }
            }
        }

        // 是否关联查询对应的店铺表
        $join = '';
        $totalMoney = 0;
        if(!$type){
//            var_dump($storeTypes);echo '<br/>';
            foreach ($storeTypes as &$storeType){
                if(isset($_POST['city']) && I('post.city', 0, 'intval') || isset($_POST['area']) && I('post.area', 0, 'intval')){
                    $join = 'LEFT JOIN '.$storeType['bao_store'].' ON '.$storeType['bao_order'].'.store_id='.$storeType['bao_store'].'.store_id';
                }
                if($storeType['sc_module'] == 'MeishiOrder' || $storeType['sc_module'] == 'HouseOrder'){
                    $field = ['SUM('.$storeType['bao_order'].'.money) AS money'];
                } else{
                    $field = ['SUM(pay_price) AS money'];
                }
                $subscript = $storeType['bao_order'].'.status';
                $map[$subscript] = $this->checkStatus($storeType['sc_id'],$storeTypes);
                $totalMoney += M($storeType['sc_module'])->join($join)->field($field)->where($map)->find()['money'];
                unset($map[$subscript]);
//                var_dump($storeType);echo '<br/>';
            }
        }
        if($type){
            if(isset($_POST['city']) && I('post.city', 0, 'intval') || isset($_POST['area']) && I('post.area', 0, 'intval')){
                $join = 'LEFT JOIN '.$baoStore.' ON '.$baoOrder.'.store_id='.$baoStore.'.store_id';
            }
            if($module == 'MeishiOrder' || $module == 'HouseOrder'){
                $field = ['SUM('.$baoOrder.'.money) AS money'];
            }else{
                $field = ['SUM(pay_price) AS money'];
            }
            $subscript = $baoOrder.'.status';
            $map[$subscript] = $this->checkStatus($type,$storeTypes);
            $totalMoney = M($module)->field($field)->join($join)->where($map)->find()['money'];
            unset($map[$subscript]);
        }
//        var_dump($count);
        if(!$totalMoney || $totalMoney == NULL){
            $totalMoney = '0.00';
        }
        $this->assign('totalMoney', $totalMoney);

        // 日趋势
        $beginSearchTime = strtotime(date('Y-m-d', $nowTime));
        $endSearchTime = strtotime(date('Y-m-d', $nowTime + 86400))-1;
        // 查询范围判断
        if($module == false){
            // sql拼接
            $sql = 'SELECT SUM(money) AS money,from_unixtime(create_time, "%Y-%m-%d") AS temp_date,from_unixtime(create_time, "%m-%d") AS temp_days FROM (';
//            var_dump($storeTypes);
            foreach ($storeTypes as &$storeType){
                if($storeType['sc_module'] == 'MeishiOrder' || $storeType['sc_module'] == 'HouseOrder'){
                    $field = $storeType['bao_order'].'.money';
                }else{
                    $field = 'pay_price AS money';
                }
                // 多表查询
                // 初始化$joins
                $joins = '';
                if((isset($_POST['city']) && I('post.city', 0, 'intval')) || (isset($_POST['area']) && I('post.area', 0, 'intval'))){
                        $joins = 'LEFT JOIN '.$storeType['bao_store'].' ON '.$storeType['bao_order'].'.store_id='.$storeType['bao_store'].'.store_id';
                }
                // 添加已完成状态判断
                $subscript = $storeType['bao_order'].'.status';
                $temp_map_sql = $map_sql;
                $map_sql .= ' AND '.$subscript.'='.$this->checkStatus($storeType['sc_id'],$storeTypes);
                $sql .= 'SELECT create_time,'.$field.' FROM '.$storeType['bao_order'].' '.$joins.' WHERE `create_time` >= '.($beginSearchTime - 86400*($searchDays - 1)).' AND `create_time` <= '.$endSearchTime.$map_sql.' UNION ';
                $map_sql = $temp_map_sql;
            }
            $sql = substr_replace($sql, '', -6);
            $sql .= ') `temp_statistics` GROUP BY temp_date ORDER BY temp_date DESC LIMIT '.$searchDays;
//            var_dump($sql);
            $getStoresDays = M()->query($sql);
//            var_dump($getStoresDays[0]);
        }else{
            if($module == 'MeishiOrder' || $module == 'HouseOrder'){
                $field = 'SUM('.$baoOrder.'.money) AS money';
            }else{
                $field = 'SUM(pay_price) AS money';
            }
            // 添加已完成状态判断
            $subscript = $baoOrder.'.status';
            $map[$subscript] = $this->checkStatus($type,$storeTypes);
            $map['create_time'] = [['egt', $beginSearchTime - 86400*($searchDays - 1)], ['elt', $endSearchTime]];
            $getStoresDays = M($module)
                ->field([$field, 'from_unixtime(create_time, "%Y-%m-%d") AS temp_date', 'from_unixtime(create_time, "%m-%d") AS temp_days'])
                ->join($join)
                ->where($map)
                ->group('temp_date')
                ->order('temp_date DESC')
                ->limit($searchDays)
                ->select();
            unset($map[$subscript]);
        }
        // 数据处理
        $tempStores = [];
        for($i = 0; $i < $searchDays; ++ $i){
            $tempStores[$i] = [
                'money' => 0,
                'temp_date' => date('Y-m-d', $beginSearchTime - 86400 * $i),
                'temp_day' => date('m-d', $beginSearchTime - 86400 * $i)
            ];
            foreach ($getStoresDays as &$getStoresDay){
                if($getStoresDay['temp_date'] == $tempStores[$i]['temp_date']){
                    $tempStores[$i] = [
                        'money' => $getStoresDay['money'],
                        'temp_day' => $tempStores[$i]['temp_day']
                    ];
                }
            }
        }
        $day = []; $money = [];
        foreach (array_reverse($tempStores) as &$tempStore){
            $day[] = '"'.$tempStore['temp_day'].'"';
            $money[] = $tempStore['money'];
        }
//        var_dump($money);
        $this->assign('store_day', ['day' => implode(',', $day), 'money' => implode(',', $money), 'money_begin' => array_reverse($tempStores)[0]['temp_day'], 'money_end' => $tempStores[0]['temp_day']]);

        // 月趋势
        $searchMonths = 12;
        $beginSearchTime = strtotime(date('Y-m-d', $nowTime)) - 86400 * 365;
//        var_dump(date('Y-m-d', $beginSearchTime));
        // 查询范围判断
        if($module == false){
            // sql拼接
            $sql = 'SELECT SUM(money) AS money,from_unixtime(create_time, "%Y-%m") AS temp_date FROM (';
            foreach ($storeTypes as &$storeType){
                if($storeType['sc_module'] == 'MeishiOrder' || $storeType['sc_module'] == 'HouseOrder'){
                    $field = $storeType['bao_order'].'.money';
                }else{
                    $field = 'pay_price AS money';
                }
                // 多表查询
                $joins = '';
                if((isset($_POST['city']) && I('post.city', 0, 'intval')) || (isset($_POST['area']) && I('post.area', 0, 'intval'))){
                        $joins = 'LEFT JOIN '.$storeType['bao_store'].' ON bao_'.$storeType['bao_order'].'.store_id='.$storeType['bao_store'].'.store_id';
                }
                // 添加已完成状态判断
                $subscript = $storeType['bao_order'].'.status';
//                $subscript = str_replace('housestore', 'house', $subscript);
                $temp_map_sql = $map_sql;
                $map_sql .= ' AND '.$subscript.'='.$this->checkStatus($storeType['sc_id'],$storeTypes);
                $sql .= 'SELECT create_time,'.$field.' FROM '.$storeType['bao_order'].' '.$joins.' WHERE `create_time` >= '.$beginSearchTime.$map_sql.' UNION ';
                $map_sql = $temp_map_sql;
            }
            $sql = substr_replace($sql, '', -6);
            $sql .= ') `temp_statistics` GROUP BY temp_date ORDER BY temp_date DESC LIMIT '.$searchMonths;
            $getStoresMonths = M()->query($sql);
        }else{
            if($module == 'MeishiOrder' || $module == 'HouseOrder'){
                $field = 'SUM('.$baoOrder.'.money) AS money';
            }else{
                $field = 'SUM(pay_price) AS money';
            }
            $subscript = $baoOrder.'.status';
            $map[$subscript] = $this->checkStatus($type,$storeTypes);
            $map['create_time'] = ['egt', $beginSearchTime];
            $getStoresMonths = M($module)
                ->field([$field, 'from_unixtime(create_time, "%Y-%m") AS temp_date'])
                ->join($join)
                ->where($map)
                ->group('temp_date')
                ->order('temp_date DESC')
                ->limit($searchMonths)
                ->select();
            unset($map[$subscript]);
        }

        // 数据处理
        $tempStores = [];
        for($i = 0; $i < $searchMonths; ++ $i){
            $tempStores[$i] = [
                'money' => '0',
                'temp_date' => date('Y-m', strtotime("-$i month",$nowTime))
            ];
        }
//        var_dump($tempStores);
        foreach ($getStoresMonths as $getStoresMonth){
            foreach ($tempStores as &$tempStore){
                if($getStoresMonth['temp_date'] == $tempStore['temp_date']){
                    $tempStore = [
                        'money' => $getStoresMonth['money'],
                        'temp_date' => $getStoresMonth['temp_date']
                    ];
                }
            }
        }
//        var_dump($getStoresMonths);
        $month = []; $money = [];
        foreach (array_reverse($tempStores) as $tempStore){
            $month[] = '"'.$tempStore['temp_date'].'"';
            $money[] = $tempStore['money'];
        }
        $this->assign('store_month', ['month' => implode(',', $month), 'money' => implode(',', $money)]);

        // 年趋势
        $searchYears = 12;
        $beginSearchTime = strtotime(date('Y', NOW_TIME)) - 86400 * 365 * ($searchYears - 1);
        // 查询范围判断
        if($module == false){
            // sql拼接
            $joins = '';
            $sql = 'SELECT SUM(money) AS money,from_unixtime(create_time, "%Y") AS temp_date FROM (';
            foreach ($storeTypes as &$storeType){
                if($storeType['sc_module'] == 'MeishiOrder' || $storeType['sc_module'] == 'HouseOrder'){
                    $field = $storeType['bao_order'].'.money';
                }else{
                    $field = 'pay_price AS money';
                }
                // 多表查询
                if((isset($_POST['city']) && I('post.city', 0, 'intval')) || (isset($_POST['area']) && I('post.area', 0, 'intval'))){
                        $joins = 'LEFT JOIN '.$storeType['bao_store'].' ON '.$storeType['bao_order'].'.store_id='.$storeType['bao_store'].'.store_id';
                }
                // 添加已完成状态判断
                $subscript = $storeType['bao_order'].'.status';
                $temp_map_sql = $map_sql;
                $map_sql .= ' AND '.$subscript.'='.$this->checkStatus($storeType['sc_id'],$storeTypes);
                $sql .= 'SELECT create_time,'.$field.' FROM '.$storeType['bao_order'].' '.$joins.' WHERE `create_time` >= '.$beginSearchTime.$map_sql.' UNION ';
                $map_sql = $temp_map_sql;
            }
            $sql = substr_replace($sql, '', -6);
            $sql .= ') `temp_statistics` GROUP BY temp_date ORDER BY temp_date DESC LIMIT '.$searchYears;
//            var_dump($sql);
            $getStoresYears = M()->query($sql);
        }else{
            if($module == 'MeishiOrder' || $module == 'HouseOrder'){
                $field = 'SUM('.$baoOrder.'.money) AS money';
            }else{
                $field = 'SUM(pay_price) AS money';
            }
            $subscript = $baoOrder.'.status';
            $map[$subscript] = $this->checkStatus($type,$storeTypes);
            $map['create_time'] = ['egt', $beginSearchTime];
            $getStoresYears = M($module)
                ->field([$field, 'from_unixtime(create_time, "%Y") AS temp_date'])
                ->join($join)
                ->where($map)
                ->group('temp_date')
                ->order('temp_date DESC')
                ->limit($searchYears)
                ->select();
            unset($map[$subscript]);
        }
//        var_dump($getStoresYears);

        // 数据处理
        $tempStores = [];
        for($i = 0; $i < $searchYears; ++ $i){
            $tempStores[$i] = [
                'money' => '0',
                'temp_date' => date('Y', $nowTime) - $i
            ];
        }
        foreach ($getStoresYears as $getStoresYear){
            foreach ($tempStores as &$tempStore){
                if($getStoresYear['temp_date'] == $tempStore['temp_date']){
                    $tempStore = [
                        'money' => $getStoresYear['money'],
                        'temp_date' => $getStoresYear['temp_date']
                    ];
                }
            }
        }
        $year = []; $money = [];
        foreach (array_reverse($tempStores) as $tempStore){
            $year[] = '"'.$tempStore['temp_date'].'"';
            $money[] = $tempStore['money'];
        }
        $this->assign('store_year', ['year' => implode(',', $year), 'money' => implode(',', $money)]);

        $this->display();
    }

    /**peace
     * 板块类型
     */
    public function storeType(){
        // 板块搜索
        $storeTypes = M('StoreClass')->field(['sc_id', 'sc_name'])->select();
        echo json_encode(array('data' => $storeTypes, 'msg' => '找到区县信息', 'error' => '200'));
        exit;
    }

    protected function checkStatus($type,$storeTypes){
        $a = '';
        foreach ($storeTypes as $storeType){
            if($storeType['sc_id'] == $type){
                $a = $storeType['module'];
            }
        }
        if($a == 'Chaoshi'){
            $a = 5;
        }elseif ($a == 'Ele'){
            $a = 6;
        }elseif ($a == 'Hotel'){
            $a = 4;
        }elseif ($a == 'HouseStore'){
            $a = 2;
        }elseif ($a == 'Meishi'){
            $a = 2;
        }else{
            $a = 0;
        }
        return $a;
    }

    /**
     * 会员消费详情
     */
    public function users(){
        // 搜索
        $userName = I('post.user_name', '');
        if($userName == true){
            $userIds = M('Users')->field(['user_id'])->where(['nickname' => ['LIKE', $userName.'%']])->select();
            if(count($userIds) > 0){
                $ids = [];
                foreach ($userIds as $userId){
                    $ids[] = $userId['user_id'];
                }
                $map['user_id'] = ['IN', $ids];
            }
            $this->assign('userName', $userName);
        }

        import('ORG.Util.Page'); // 导入分页类
        $map['order_type'] = ['GT', 0];
        $map['order_id'] = ['GT', 0];
        // 交易总金额
        $totalDatas = M('UserMoneyLogs')
            ->field(['SUM(money) AS money', 'COUNT(DISTINCT user_id) AS counts'])
            ->where($map)
            ->find();
        $count = $totalDatas['counts'];
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $datas = M('UserMoneyLogs')
            ->alias('lo')
            ->field(['lo.user_id', 'SUM(lo.money) AS money', 'COUNT(DISTINCT lo.order_id,lo.order_type) AS order_num', 'us.nickname', 'us.money AS user_money'])
            ->join('LEFT JOIN bao_users us ON us.user_id=lo.user_id')
            ->where($map)
            ->group('lo.user_id')
            ->order('lo.user_id DESC')
            ->limit($Page->firstRow, $Page->listRows)
            ->select();
        foreach ($datas as&$data){
            $data['user_money'] = number_format($data['user_money'], 2, '.', ',');
            $data['money'] = number_format($data['money'], 2, '.', ',');
        }

        $this->assign('sumMoney', number_format(abs($totalDatas['money']), 2, '.', ','));
        $this->assign('page', $show);
        $this->assign('datas', $datas);
        $this->display();
    }

    /**
     * 会员消费详情
     */
    public function userdetail(){
        // 搜索
        $this->assign('end_date', date('Y-m-d', NOW_TIME));
        if(isset($_POST['bg_date']) && isset($_POST['end_date'])){
            $bgDate = I('post.bg_date', '');
            $endDate = I('post.end_date', '');
            $bgTime = strtotime($bgDate);
            $endTime = strtotime($endDate);
            if($bgTime && $endTime && ($endTime >= $bgTime + 86400) && $endTime < NOW_TIME){
//                $bgTime = $bgTime - 86400;
                $endTime = $endTime + 86400;
//                $searchDays = ceil(($endTime - $bgTime)/86400);
//                $nowTime = $endTime;
                $if = true;
                $mapTemp = [['GT', $bgTime],['LT', $endTime]];
                $this->assign('bg_date', $bgDate);
                $this->assign('end_date', $endDate);
            }
        }
        $user_id = I('get.id', 0, 'intval');
        if($user_id){
            SESSION('trading_user_details_user_id', $user_id, 300);
        }
        if(!$user_id && SESSION('trading_user_details_user_id')){
            $user_id = SESSION('trading_user_details_user_id');
        }

        import('ORG.Util.Page'); // 导入分页类
        $map = ['user_id' => $user_id];
        $maps = ['user_id' => $user_id, 'order_type' => ['GT', 0]];
        // 交易总金额
        $sumMoney = M('UserMoneyLogs')
            ->field(['SUM(money) AS money'])
            ->where($maps)
            ->find();
        // 用户名和余额
        $userInfo = M('Users')
            ->field(['nickname', 'money'])
            ->where($map)
            ->find();
        $userInfo['money'] = number_format($userInfo['money'], 2, '.', ',');
        if(isset($if) && $if === true){
            $map['create_time'] = $mapTemp;
        }
        $count = M('UserMoneyLogs')->where($map)->count();
//        $count = count($sql);
//        dump($count);
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $datas = M('UserMoneyLogs')
            ->field(['from_unixtime(create_time, "%Y-%m-%d %h:%m:%s") AS create_time', 'money', 'type', 'order_type'])
            ->where($map)
            ->order('create_time DESC')
            ->limit($Page->firstRow, $Page->listRows)
            ->select();
        foreach ($datas as &$data){
            $data['money'] = number_format($data['money'], 2, '.', ',');
            $data['operate'] = $this->formatOperate($data['type'], $data['order_type']);
            unset($data['type']);
            unset($data['order_type']);
        }

        $this->assign('sumMoney', number_format(abs($sumMoney['money']), 2, '.', ','));
        $this->assign('page', $show);
        $this->assign('user', $userInfo);
        $this->assign('datas', $datas);
        $this->assign('id', $user_id);
        $this->display();
    }

    protected function formatOperate($type, $orderType){
        if($type == 1){
            $a = '会员充值';
        }elseif($type == 2){
            $a = $this->formatOpeSon($orderType).'消费';
        }elseif($type == 3){
            $a = $this->formatOpeSon($orderType).'退款';
        }else{
            $a = '其它';
        }
        return $a;
    }

    protected function formatOpeSon($orderType){
        if($orderType == 1){
            $a = '社区超市';
        }elseif($orderType == 2){
            $a = '外卖';
        }elseif($orderType == 3){
            $a = '美食';
        }elseif($orderType == 4){
            $a = '酒店';
        }elseif($orderType == 5){
            $a = '房产';
        }else{
            $a = '';
        }
        return $a;
    }

    protected function lCase($a) {
        $get = '';
        for ($i = 0; $i < strlen($a); ++ $i) {
            if(ord($a[$i]) <= 90 && ord($a[$i]) >= 65) {
                $get .= '_'.strtolower($a[$i]);
            }else{
                $get .= $a[$i];
            }
        }
        return $get;
    }

}