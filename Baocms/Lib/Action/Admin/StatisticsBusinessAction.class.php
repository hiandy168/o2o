<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/16
 * Time: 13:21
 */
class StatisticsBusinessAction extends CommonAction
{
    /**peace
     * 商家日月年统计，板块统计
     */
    public function index(){
        $map = [];
        $type = 0;
        $map['closed'] = 0;
        $map['audit'] = ['IN', [0, 1]];
        $map_sql = ' AND `closed` = 0 AND `audit` IN (0,1)';

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
        // 总店铺统计
        $count = 0;
        // 板块搜索
        $storeClass = [];
        $module = '';
        foreach ($storeTypes as $storeType){
            $storeClass[] = $storeType['sc_id'];
        }
        // 所属板块合法性验证
        if(!in_array($type, $storeClass)){
            $type = 0;
        }
        if($type != 0){
            foreach ($storeTypes as $storeType){
                if($storeType['sc_id'] == $type){
                    $module = $storeType['sc_module'];
                }
            }
        }
        if($type == false){
            foreach ($storeTypes as $storeType){
                $count += M($storeType['sc_module'])->where($map)->count();
            }
        }else{
            $count = M($module)->where($map)->count();
        }
//        var_dump($count);
        $this->assign('count', $count);

        // 日趋势
        $beginSearchTime = strtotime(date('Y-m-d', $nowTime));
        $endSearchTime = strtotime(date('Y-m-d', $nowTime + 86400))-1;
        // 查询范围判断
        if($module == false){
            // sql拼接
            $sql = 'SELECT COUNT(*) AS num,from_unixtime(begin_time, "%Y-%m-%d") AS temp_date,from_unixtime(begin_time, "%m-%d") AS temp_days FROM (';
            foreach ($storeTypes as $storeType){
                $sql .= 'SELECT begin_time FROM bao_'.strtolower($storeType['sc_module']).' WHERE `begin_time` >= '.($beginSearchTime - 86400*($searchDays - 1)).' AND `begin_time` <= '.$endSearchTime.$map_sql.' UNION ';
            }
            $sql = substr_replace($sql, '', -6);
            // 特殊表名替换, 若表含两个单词必须重新处理
            $sql = str_replace('housestore', 'house_store', $sql);
            $sql .= ') `temp_statistics` GROUP BY temp_date ORDER BY temp_date DESC LIMIT '.$searchDays;
            $getStoresDays = M()->query($sql);
        }else{
            $map['begin_time'] = [['egt', $beginSearchTime - 86400*($searchDays - 1)], ['elt', $endSearchTime]];
            $getStoresDays = M($module)
                ->field(['COUNT(*) AS num', 'from_unixtime(begin_time, "%Y-%m-%d") AS temp_date', 'from_unixtime(begin_time, "%m-%d") AS temp_days'])
                ->where($map)
                ->group('temp_date')
                ->order('store_id DESC')
                ->limit($searchDays)
                ->select();
        }
        // 数据处理
        $tempStores = [];
        for($i = 0; $i < $searchDays; ++ $i){
            $tempStores[$i] = [
                'num' => 0,
                'temp_date' => date('Y-m-d', $beginSearchTime - 86400 * $i),
                'temp_day' => date('m-d', $beginSearchTime - 86400 * $i)
            ];
            foreach ($getStoresDays as $getStoresDay){
                if($getStoresDay['temp_date'] == $tempStores[$i]['temp_date']){
                    $tempStores[$i] = [
                        'num' => $getStoresDay['num'],
                        'temp_day' => $tempStores[$i]['temp_day']
                    ];
                }
            }
        }
        $day = []; $num = [];
        foreach (array_reverse($tempStores) as $tempStore){
            $day[] = '"'.$tempStore['temp_day'].'"';
            $num[] = $tempStore['num'];
        }
        $this->assign('store_day', ['day' => implode(',', $day), 'num' => implode(',', $num), 'store_day_begin' => array_reverse($tempStores)[0]['temp_day'], 'store_day_end' => $tempStores[0]['temp_day']]);

        // 月趋势
        $searchMonths = 12;
        $beginSearchTime = strtotime(date('Y-m-d', $nowTime)) - 86400 * 365;
//        var_dump(date('Y-m-d', $beginSearchTime));
        // 查询范围判断
        if($module == false){
            // sql拼接
            $sql = 'SELECT COUNT(*) AS num,from_unixtime(begin_time, "%Y-%m") AS temp_date FROM (';
            foreach ($storeTypes as $storeType){
                $sql .= 'SELECT begin_time FROM bao_'.strtolower($storeType['sc_module']).' WHERE `begin_time` >= '.$beginSearchTime.$map_sql.' UNION ';
            }
            $sql = substr_replace($sql, '', -6);
            // 特殊表名替换, 若表含两个单词必须重新处理
            $sql = str_replace('housestore', 'house_store', $sql);
            $sql .= ') `temp_statistics` GROUP BY temp_date ORDER BY temp_date DESC LIMIT '.$searchMonths;
            $getStoresMonths = M()->query($sql);
        }else{
            $map['begin_time'] = ['egt', $beginSearchTime];
            $getStoresMonths = M($module)
                ->field(['COUNT(*) AS num', 'from_unixtime(begin_time, "%Y-%m") AS temp_date'])
                ->where($map)
                ->group('temp_date')
                ->order('store_id DESC')
                ->limit($searchMonths)
                ->select();
        }

        // 数据处理
        $tempStores = [];
        for($i = 0; $i < $searchMonths; ++ $i){
            $tempStores[$i] = [
                'num' => '0',
                'temp_date' => date('Y-m', strtotime("-$i month",$nowTime))
            ];
        }
        foreach ($getStoresMonths as $getStoresMonth){
            foreach ($tempStores as &$tempStore){
                if($getStoresMonth['temp_date'] == $tempStore['temp_date']){
                    $tempStore = [
                        'num' => $getStoresMonth['num'],
                        'temp_date' => $getStoresMonth['temp_date']
                    ];
                }
            }
        }
        $month = []; $num = [];
        foreach (array_reverse($tempStores) as $tempStore){
            $month[] = '"'.$tempStore['temp_date'].'"';
            $num[] = $tempStore['num'];
        }
        $this->assign('store_month', ['month' => implode(',', $month), 'num' => implode(',', $num)]);

        // 年趋势
        $searchYears = 12;
        $beginSearchTime = strtotime(date('Y', NOW_TIME)) - 86400 * 365 * ($searchYears - 1);
//        var_dump(date('Y', $beginSearchTime));
        // 查询范围判断
        if($module == false){
            // sql拼接
            $sql = 'SELECT COUNT(*) AS num,from_unixtime(begin_time, "%Y") AS temp_date FROM (';
            foreach ($storeTypes as $storeType){
                $sql .= 'SELECT store_id,begin_time FROM bao_'.strtolower($storeType['sc_module']).' WHERE `begin_time` >= '.$beginSearchTime.$map_sql.' UNION ';
            }
            $sql = substr_replace($sql, '', -6);
            // 特殊表名替换, 若表含两个单词必须重新处理
            $sql = str_replace('housestore', 'house_store', $sql);
            $sql .= ') `temp_statistics` GROUP BY temp_date ORDER BY temp_date DESC LIMIT '.$searchYears;
            $getStoresYears = M()->query($sql);
        }else{
            $map['begin_time'] = ['egt', $beginSearchTime];
            $getStoresYears = M($module)
                ->field(['COUNT(*) AS num', 'from_unixtime(begin_time, "%Y") AS temp_date'])
                ->where($map)
                ->group('temp_date')
                ->order('temp_date DESC')
                ->limit($searchYears)
                ->select();
        }
//        var_dump($getStoresYears);

        // 数据处理
        $tempStores = [];
        for($i = 0; $i < $searchYears; ++ $i){
            $tempStores[$i] = [
                'num' => '0',
                'temp_date' => date('Y', $nowTime) - $i
            ];
        }
        foreach ($getStoresYears as $getStoresYear){
            foreach ($tempStores as &$tempStore){
                if($getStoresYear['temp_date'] == $tempStore['temp_date']){
                    $tempStore = [
                        'num' => $getStoresYear['num'],
                        'temp_date' => $getStoresYear['temp_date']
                    ];
                }
            }
        }
        $month = []; $num = [];
        foreach (array_reverse($tempStores) as $tempStore){
            $month[] = '"'.$tempStore['temp_date'].'"';
            $num[] = $tempStore['num'];
        }
        $this->assign('store_year', ['year' => implode(',', $month), 'num' => implode(',', $num)]);

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

}