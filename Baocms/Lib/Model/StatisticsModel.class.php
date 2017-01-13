<?php

/**
 * 功能 .
 * 作者/重构: Administrator
 * Date: 2016/11/11
 */
class StatisticsModel extends CommonModel
{

    public function fetch($user_id, $store_id = '')
    {
        if (trim($store_id)) {
            $where['user_id'] = $user_id;
        }
        $where['store_id'] = $store_id;
    }

}