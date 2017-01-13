<?php

/*
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class HouseAttributeModel extends CommonModel {

    protected $pk = 'attr_id';
    protected $tableName = 'House_attribute';
  //  protected $token = 'bao_house_attribute';
    protected $orderby = array('orderby'=>'asc');
   
    public function checkAuth($auth) {
        $data = $this->fetchAll();
        foreach ($data as $row) {
         /*   if ($auth == $row['menu_action']) {
                return true;
            }*/
        }
        return false;
    }

    

}