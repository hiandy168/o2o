<?php

/*
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class GoodsshopcateModel extends CommonModel {

    protected $pk = 'cate_id';
    protected $tableName = 'goods_shopcate';
    protected $token = 'goods_shopcate';
    protected $orderby = array('orderby' => 'asc');
    
    public function delcate($cate_id){
        $this->where(array('parent_id'=>$cate_id))->delete();
        $this->delete($cate_id);
    }
    
    public function  getParentsId($id){
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        return $parent_id;
    }
    /**
     * 因为每个商家的分类不一样，父类fetchAll方法的缓存不适用
     * {@inheritDoc}
     * @see CommonModel::fetchAll()
     */
    public function fetchAll($field='*',$where=array()){
            $result = $this->field($field);
            if ( ! empty($where))
            {
                $result = $result->where($where);
            }
            $result = $result->order($this->orderby)->select();
            $data = array();
            foreach($result  as $row){
                $data[$row[$this->pk]] = $this->_format($row);
            }
            return $data;
    }
    
    public function getall($field='*',$where=array()){
        $local = array();
        $data = $this->fetchAll($field,$where);
        foreach($data  as $val){
            if($val['parent_id'] == 0){
                $local[$val['cate_id']]=$val;
                foreach($data as  $val1){
                    if($val1['parent_id'] == $val['cate_id']){
                        $local[$val['cate_id']]['child'][]=$val1;
                    }
                }
            }
        }
        return $local;
    }
}