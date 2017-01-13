<?php
/**
 * @author : Lucifer
 * @createTime 2016-12-2
 */

class HotelroomfacilityAction extends CommonAction
{
	public function index(){
        import('ORG.Util.Page'); 
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $map = array('closed' => 0);
        if ($keyword) {
            $map['attr_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);

        $hotelroomfacility = M('HotelRoomFacility');
        $count = $hotelroomfacility->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 

        $list = $hotelroomfacility
            ->where($map)
            //->order(array('order_by' => 'ASC'))
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $this->assign('list', $list);   
        $this->assign('page', $show); 
        $this->display();
    }

    public function create(){
        if(IS_AJAX){
            $data['attr_name']=$_POST['attr_name'];
            $data['closed'] = 0;
            
            $hotelroomfacility = M('HotelRoomFacility');
            $res = $hotelroomfacility->where($data)->count();
	        if($res){
	            $this->ajaxReturn(array('status'=>'no','info'=>"该信息已存在"));
	        }
	        $data['create_time'] = $_SERVER['REQUEST_TIME'];
            $status = M('HotelRoomFacility')->add($data);
            if($status){
                $this->ajaxReturn(array('status'=>'yes','info'=>'增加成功'));
            }
            $this->ajaxReturn(array('status'=>'no','info'=>'增加失败'));
        }
        $this->display();
    }

    //编辑
    public function edit($id = 0){
        $hotelroomfacility = M('HotelRoomFacility');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data['attr_name'] = $_POST['attr_name'];
            $data['closed'] = 0;
            $id = (int)$_POST['id'];
            $data['id'] = array('neq', $id);
        	$res = $hotelroomfacility->where($data)->count();
	        if ($res) {
	            $this->baoError('该信息已存在');
	        }
	        unset($data['id']);
            $rst = $hotelroomfacility
                ->where(array('id' => $id))
                ->save($data);
            if ($rst) {
                $this->baoSuccess('操作成功', U('hotelroomfacility/index'));
            }
            $this->baoError('操作失败');
        }
        if(is_numeric($id) && ($id = (int) $id)){
            if (!$detail = $hotelroomfacility->find($id)) {
                $this->baoError('请选择要编辑的酒店服务信息');
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $this->baoError('请选择要编辑的酒店服务信息');
        }
    }

    public function delete($ids = 0) {
        $hotelroomfacility = M('HotelRoomFacility');
       
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $ids = I('post.ids', 0, 'intval');
            if (!is_array($ids)) {
                $this->baoError('请选择要删除的酒店设施信息');
            }
            foreach ($ids as $id) {
                if(!$hotelroomfacility->where(array('id' => $id))->setField('closed', 1)){
                    $this->baoError('删除失败');
                };
            }
            $this->baoSuccess('删除成功！', U('hotelroomfacility/index'));
        }
        if (is_numeric($ids) && ($id = (int) $ids)) {
            if($hotelroomfacility->where(array('id' => $id))->setField('closed', 1)){
                $this->baoSuccess('删除成功！', U('hotelroomfacility/index'));
            }else{
                $this->baoError('删除失败');
            }
        }
    }
}