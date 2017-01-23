<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/16
 * Time: 13:21
 */
class StatisticsUserAction extends CommonAction
{
    /**peace
     * 会员日月年统计
     */
    public function index(){
        // 搜索
        $bg_date = I('post.bg_date','');
        $end_date = I('post.end_date', '');
        $end_time = strtotime($end_date);
        if($end_time > NOW_TIME)$end_time = NOW_TIME;

        $searchDays = 30;
        $searchTime = NOW_TIME;
        $time = strtotime(date('Y-m-d', NOW_TIME));
        $this->assign('end_date', date('Y-m-d', NOW_TIME));

        if($bg_date && $end_date && (strtotime($bg_date) + 86400) < $end_time){
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', date('Y-m-d', $end_time));
            $searchDays = floor(($end_time - strtotime($bg_date))/86400 + 1);
            $time = $searchTime = $end_time;
        }
        // 总会员数
        $countTotal = M('Users')->count();
        $this->assign('count', $countTotal);
        // 日趋势
        $getAddUsers = M('Users')
            ->field(['COUNT(*) AS num', 'from_unixtime(reg_time, "%Y-%m-%d") AS temp_date'])
            ->where(['reg_time' => [['egt', $time - 3600*24*($searchDays - 1)], ['elt', $searchTime]]])
            ->group('temp_date')
            ->order('reg_time DESC')
            ->select();
//        var_dump($getAddUsers);

        $tempUsers = [];
        for($i = 0; $i < $searchDays; ++ $i){
            $tempUsers[$i] = [
                'num' => 0,
                'temp_date' => date('Y-m-d', $time - 86400 * $i),
                'temp_day' => date('m-d', $time - 86400 * $i)
            ];
            foreach ($getAddUsers as $getAddUser){
                if($getAddUser['temp_date'] == $tempUsers[$i]['temp_date']){
                    $tempUsers[$i] = [
                        'num' => $getAddUser['num'],
//                        'temp_date' => $getAddUser['temp_date'],
                        'temp_day' => $tempUsers[$i]['temp_day']
                    ];
                }
            }
        }
        $day = []; $num = [];
        foreach (array_reverse($tempUsers) as $tempUser){
            $day[] = '"'.$tempUser['temp_day'].'"';
            $num[] = $tempUser['num'];
        }
        $this->assign('user_day', ['day' => implode(',', $day), 'num' => implode(',', $num), 'user_day_begin' => array_reverse($tempUsers)[0]['temp_day'], 'user_day_end' => $tempUsers[0]['temp_day']]);

        // 月趋势
        $time = strtotime(date('Y-m', NOW_TIME));
        $monthAddUsers = M('Users')
            ->field(['COUNT(*) AS num', 'from_unixtime(reg_time, "%Y-%m") AS temp_month'])
            ->where(['reg_time' => ['gt', $time-3600*24*350]])
            ->group('temp_month')
            ->order('reg_time DESC')
            ->limit(12)
            ->select();
        $monthAddUsers = array_reverse($monthAddUsers);

        $monthArr = [];
        for ($i = 0; $i < 12; ++ $i){
            $monthArr[] = date('Y-m', $time - 86400*30*$i);
        }
        $months = []; $month = []; $num = [];
        foreach ($monthAddUsers as $monthAddUser){
            $months[] = $monthAddUser['temp_month'];
        }
        foreach (array_reverse($monthArr) as &$monthA){
            if (in_array($monthA, $months)) {
                $month[] = '"' . $monthA . '"';
                foreach ($monthAddUsers as $monthAddUser){
                    if($monthAddUser['temp_month'] == $monthA) $num[] = $monthAddUser['num'];
                }
            } else {
                $month[] = '"' . $monthA . '"';
                $num[] = 0;
            }
        }
        $this->assign('user_month', ['month' => implode(',', $month), 'num' => implode(',', $num)]);

        // 年趋势
        $time = strtotime(date('Y', NOW_TIME));
        $yearAddUsers = M('Users')
            ->field(['COUNT(*) AS num', 'from_unixtime(reg_time, "%Y") AS temp_year'])
            ->where(['reg_time' => ['gt', $time-86400*365*12]])
            ->group('temp_year')
            ->order('reg_time DESC')
            ->limit(12)
            ->select();
        $yearAddUsers = array_reverse($yearAddUsers);

        $yearArr = [];
        for ($i = 0; $i < 12; ++ $i){
            $yearArr[] = date('Y', NOW_TIME) - $i;
        }
        $years = []; $year = []; $num = [];
        foreach ($yearAddUsers as $yearAddUser){
            $years[] = $yearAddUser['temp_year'];
        }
        foreach (array_reverse($yearArr) as &$yearA){
            if (in_array($yearA, $years)) {
                $year[] = '"' . $yearA . '"';
                foreach ($yearAddUsers as $yearAddUser){
                    if($yearAddUser['temp_year'] == $yearA){
                        $num[] = $yearAddUser['num'];
                    }
                }
            } else {
                $year[] = '"' . $yearA . '"';
                $num[] = 0;
            }
        }
        $this->assign('user_year', ['year' => implode(',', $year), 'num' => implode(',', $num)]);

        $this->display();
    }

}