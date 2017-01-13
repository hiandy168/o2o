<?php

class CityAction extends CommonAction
{
    public function index()
    {
        $citylists = array();
        foreach ($this->citys as $val) {
            if ($val['is_open'] == 1) {
                $a = strtoupper($val['first_letter']);
                $citylists[$a][] = $val;
            }
        }
        $url = $_SERVER['HTTP_REFERER'];
        ksort($citylists);
        if (strpos($_SERVER['HTTP_REFERER'], 'Jiudian/room/index')) {
            $url = U('Jiudian/index/index', '', true, false, C('BASE_SITE'));
        }
        if (strpos($_SERVER['HTTP_REFERER'], 'fangchan/room/index')) {
            $url = U('fangchan/index/index', '', true, false, C('BASE_SITE'));
        }
        $this->assign('citylists', $citylists);
        $this->assign('url', $url);
        $this->display();
    }

    public
    function get_area($city_id = 0)
    {
        $city_id || $this->ajaxReturn(array('status' => 0, 'data' => "", "info" => "暂无数据"));
        $children = D('Area')->where(array('city_id' => $city_id))->select();
        if ($children) {
            $this->ajaxReturn(array('status' => 1, 'data' => $children, "info" => "获取成功"));
        } else {
            $this->ajaxReturn(array('status' => 0, 'data' => "", "info" => "暂无数据"));
        }
    }

    public
    function get_citylist()
    {
        $data = D('City')->cache()->select();
        $callback = $_GET['callback'];

        if ($data) {
            echo $callback . '(' . json_encode(array('status' => 1, 'data' => $data, "info" => "获取成功")) . ')';
            die;
        } else {
            echo $callback . '(' . json_encode(array('status' => 0, 'data' => $data, "info" => "暂无数据")) . ')';
            die;
        }

    }


    /*	public function test()
        {
            import('ORG/Net/IpLocation');

            $IpLocation = new IpLocation('UTFWry.dat');
            //$result = $IpLocation->getlocation('58.60.63.216');
            $result = $IpLocation->getlocation('182.137.14.169');
            //var_dump($this->citys);exit();
            //$city = D('City')->autoregister($_SERVER['REMOTE_ADDR']);
            $current = get_current_address_by_baidu($_SERVER['REMOTE_ADDR']);
            //$name = $current->content->address_detail->city;

            var_dump($current);echo 111;
            if($current->status) {
               $name = $current->content->address_detail->city;
               if (empty($name) || stristr($name, 'null')) {
                       $current = get_current_address_by_baidu("182.137.14.169");
                     $name = $current->content->address_detail->city;
               }
               var_dump($current);exit();
           } else {
                  $current = get_current_address_by_baidu("182.137.14.169");
               $name = $current->content->address_detail->city;
           }
        }*/
}