<?php
/**
 * @author : Lucifer
 * @createTime 2016-9-22
 */
class HotelRoomModel extends CommonModel
{ 
    //删除标示
    public $closed = array(
    						'exist' => 0,
    						'delete' => 1,
    					);
    //
    public $audit = array(
    						1 => '待审核',
    						2 => '通过',
    						3 => '未通过'
    					);

	public $roomCate = array(
					       1 => '标准间',
					       2 => '豪华单间',
					       3 => '总统套房',
					       4 => '其它',
					   );


    public $roomType = array(
				            1 => '单人床',
				            2 => '双人床',
				            3 => '三人床',
				            4 => '其它',
				        );
    					
    //根据商品id删除商品
    public function deleteById($id)
    {
    	return $this->where(array('room_id' => $id))->save(array('closed' => $this->closed['delete']));
    }
    
    //根据店铺id删除商品
    public function deleteByStore($storeId)
    {
    	return $this->where(array('hotel_id' => $storeId))->save(array('closed' => $this->closed['delete']));
    }
}