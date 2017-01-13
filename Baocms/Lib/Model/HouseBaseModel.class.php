<?php
/**
 * @author : Lucifer
 * @createTime 2016-9-19
 */

class HouseBaseModel extends CommonModel
{
	protected $_validate =
        array(
        	array('city_id','require','城市不能为空！'),
        	array('house_name','require','楼盘名不能为空！'),
            array('delivery_time','require','交付时间不能为空！'),
            array('open_time','require','开盘时间不能为空！'),
            array('price','require','参考价格不能为空！'),
            array('property_fee','require','物业费不能为空！'),
            array('build_area','require','建筑面积不能为空！'),
            array('carport','require','车位不能为空！'),
            array('lng','require','经度不能为空！'),
            array('lat','require','纬度不能为空！'),
            array('greening_rate','require','绿化率不能为空！'),
            array('plot_ratio','require','容积率不能为空！'),         
            array('developers','require','开发商不能为空！'),
            array('property_company','require','物业公司不能为空！'),
            array('details','require','详情不能为空！'),
            );

   public $error_msg = '';
   
   public function checkData($data)
   {
   		foreach ($data as $k=>$v) {
   			foreach ($this->_validate as $vo) {
   				if (in_array($k, $vo)) {
   					if (!$this->check($v, $vo[1])) {
   						$this->error_msg = $vo[2];
   						return false;
   					}
   				}
   			}
   		}
   		return true;
   }
}