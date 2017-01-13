<?php

/*
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class ArticlecateAction extends CommonAction {

    private $create_fields = array('cate_name', 'orderby');
    private $edit_fields = array('cate_name', 'orderby');

    public function index() {
        $Articlecate = D('Articlecate');
        $list = $Articlecate->where(['closed' => 0])->fetchAll();
        // 分页处理
//        $count = $Articlecate->where([])->count();   // 查询满足要求的总记录数
//        $Page = new \Page($count, 25);   // 实例化分页类 传入总记录数和每页显示的记录数
//        $show = $Page->show();   // 分页显示输出

        $this->assign('list', $list); // 赋值数据集
//        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create($parent_id=0) {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Articlecate');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('articlecate/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('parent_id',$parent_id);
            $this->display();
        }
    }
    
    public function child($parent_id=0){
        $datas = D('Articlecate')->fetchAll();
        $str = '';

        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['cate_id'] == $parent_id){
         
                foreach($datas as $var2){

                    if($var2['parent_id'] == $var['cate_id']){
                        $str.='<option value="'.$var2['cate_id'].'">'.$var2['cate_name'].'</option>'."\n\r";
           
                        foreach($datas as $var3){
                            if($var3['parent_id'] == $var2['cate_id']){
                                
                               $str.='<option value="'.$var3['cate_id'].'">&nbsp;&nbsp;--'.$var3['cate_name'].'</option>'."\n\r"; 
                                
                            }
                            
                        }
                    }  
                }
                             
              
            }           
        }
        echo $str;die;
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function edit($cate_id = 0) {
        if ($cate_id = (int) $cate_id) {
            $obj = D('Articlecate');
            if (!$detail = $obj->find($cate_id)) {
                $this->baoError('请选择要编辑的商家分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('articlecate/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家分类');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function delete($cate_id = 0) {
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Articlecate');
//            $obj->delete($cate_id);
            // 该分类是否存在子类
            $findSon = D('ArticleCate')->where(['parent_id' => $cate_id, 'closed' => 0])->find();
            if($findSon){
                $this->baoError('不能删除有子类的分类');
            }
            // 存在文章，不能删除
            $findArticle = D('Article')->where(['cate_id' => $cate_id])->find();
            if($findArticle){
                $this->baoError('不能删除有文章的分类');
            }
            $obj->where(['cate_id' => $cate_id])->save(['closed' => 1]);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('articlecate/index'));
        } else {
            $cate_id = $this->_post('cate_id', false);
            if (is_array($cate_id)) {
                $obj = D('Articlecate');
                // 该分类是否存在子类
                $findSon = D('ArticleCate')->where(['parent_id' => ['IN', $cate_id], 'closed' => 0])->find();
                if($findSon){
                    $this->baoError('不能删除有子类的分类');
                }
                foreach ($cate_id as $id) {
//                    $obj->delete($id);
                    $obj->where(['cate_id' => $id])->save(['closed' => 1]);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('articlecate/index'));
            }
            $this->baoError('请选择要删除的商家分类');
        }
    }
    
    public function update() {
        $orderby = $this->_post('orderby', false);
        $obj = D('Articlecate');
        foreach ($orderby as $key => $val) {
            $data = array(
                'cate_id' => (int) $key,
                'orderby' => (int) $val
            );
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->baoSuccess('更新成功', U('articlecate/index'));
    }

}
