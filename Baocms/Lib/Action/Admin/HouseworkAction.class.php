<?php


class  HouseworkAction extends CommonAction{
	 private $create_fields = array('title', 'areas', 'price', 'unit', 'gongju', 'photo', 'name', 'tel');
	 private $edit_fields = array('title', 'areas', 'price', 'unit', 'gongju', 'photo', 'name', 'tel');
    
    public function index(){
        $Housework = D('Housework');
        import('ORG.Util.Page'); // 导入分页类
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|tel|contents'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }  
        if ($svc_id = (int) $this->_param('svc_id')) {
            $map['svc_id'] = $svc_id;
            $this->assign('svc_id', $svc_id);
        }
        $count = $Housework->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Housework->where($map)->order(array('housework_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('workTypes',$Housework->getCfg());
        $this->display(); // 输出模板
    }
    
    // 家政的项目配置
    public function setting(){
        $Housework = D('Housework');
        $this->assign('workTypes',$Housework->getCfg());
        $this->display(); // 输出模板
    }
    
    public function setting2($id){
        $Houseworksetting = D('Houseworksetting');
        $Housework = D('Housework');
        $id = (int)$id;
        $detail = $Houseworksetting->detail($id);
		
		
		 $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		 
		 
        if ($this->isPost()) {
            $data['title'] = htmlspecialchars($_POST['title']);
			
			
			//增加的
			$data['areas'] = implode(',', $data['areas']);
			
			
            $data['price'] = htmlspecialchars($_POST['price']);
            $data['unit']  = htmlspecialchars($_POST['unit']);
            $data['gongju']  = htmlspecialchars($_POST['gongju']);
            $data['photo']  = htmlspecialchars($_POST['photo']);
			
			
			//增加的
			
			
			$data['name']  = htmlspecialchars($_POST['name']);
			$data['tel']  = htmlspecialchars($_POST['tel']);
			
			
			
			
            $data['biz_time']  = htmlspecialchars($_POST['biz_time']);
            $data['contents'] = SecurityEditorHtml($_POST['contents']);
            $data['id'] = $id;
            $Houseworksetting->save($data);
            $this->baoSuccess('操作成功', U('housework/setting2',array('id'=>$id)));
        }else{
            $this->assign('workTypes',$Housework->getCfg());
			//查城市
			$this->assign('citys', D('City')->fetchAll());
			$this->assign('areas', D('Area')->fetchAll());	
			//查结束
			
            $this->assign('detail', $detail);
            $this->display(); 
        }
    }
    
    public function edit($housework_id){
        if ($housework_id = (int) $housework_id) {
            $obj = D('Housework');
            if (!$detail = $obj->find($housework_id)) {
                $this->baoError('请选择要编辑的活动');
            }
            if ($this->isPost()) {
                $data['is_real'] = (int)$this->_post('is_real');
                $data['num']     = (int)  $this->_post('num');
				$data['areas'] = implode(',', $data['areas']);
                $data['gold']    = (int) $this->_post('gold');
                $data['housework_id'] = $housework_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('housework/index'));
                }
                $this->baoError('操作失败');
            } else {
    
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的活动');
        }
        
        
    }
    
     public function delete($housework_id = 0) {
        if (is_numeric($housework_id) && ($housework_id = (int) $housework_id)) {
            $obj = D('Housework');
            $obj->delete($housework_id);
            $this->baoSuccess('删除成功！', U('housework/index'));
        } else {
            $housework_id = $this->_post('housework_id', false);
            if (is_array($housework_id)) {
                $obj = D('Housework');
                foreach ($housework_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('housework/index'));
            }
            $this->baoError('请选择要删除的预约');
        }
    }
    
}