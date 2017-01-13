<?php

/*
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class FangchanattrAction extends CommonAction {

    private $create_fields = array('parent_id', 'attr_name','help_name');
    private $edit_fields = array('parent_id', 'attr_name','help_name');

    public function index() {
        $datas= D('HouseAttribute')->where(array('closed'=>0))->fetchAll();
        $this->assign('datas', $datas);
        $this->display();
    }

    public function create($parent_id = 0) {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('HouseAttribute');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('Fangchanattr/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $menu = D('HouseAttribute')->fetchAll();
            $this->assign('datas', $menu);
            $this->assign('parent_id', (int) $parent_id);
            $this->display();
        }
    }

   /* public function action($parent_id = 0) {
        if (!$parent_id = (int) $parent_id)
            $this->baoError('请选择正确的父级菜单');
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $new = $this->_post('new', false);
            $obj = D('Menu');
            foreach ($data as $k => $val) {
                $local = array();
                $local['menu_id'] = (int) $k;
                $local['attr_name'] = htmlspecialchars($val['attr_name'], ENT_QUOTES, 'UTF-8');
                $local['orderby'] = (int) $val['orderby'];
                $local['menu_action'] = htmlspecialchars($val['menu_action'], ENT_QUOTES, 'UTF-8');
                $local['is_show'] = (int) $val['is_show'];
                if (!empty($local['attr_name']) && !empty($local['menu_id']) && !empty($val['menu_action'])) {
                    $obj->save($local);
                }
            }
            if (!empty($new)) {
                foreach ($new as $k => $val) {
                    $local = array();
                    $local['attr_name'] = htmlspecialchars($val['attr_name'], ENT_QUOTES, 'UTF-8');
                    $local['orderby'] = (int) $val['orderby'];
                    $local['menu_action'] = htmlspecialchars($val['menu_action'], ENT_QUOTES, 'UTF-8');
                    $local['is_show'] = (int) $val['is_show'];
                    $local['parent_id'] = $parent_id;
                    if (!empty($local['attr_name']) && !empty($val['menu_action'])) {
                        $obj->add($local);
                    }
                }
            }
            $obj->cleanCache();
            $this->baoSuccess('更新成功', U('menu/index'));
        } else {
            $menu = D('Menu')->fetchAll();
            $this->assign('datas', $menu);
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }*/

    public function update() {
        $orderby = $this->_post('orderby', false);
        $obj = D('HouseAttribute');
        foreach ($orderby as $key => $val) {
            $data = array(
                'attr_id' => (int) $key,
                'orderby' => (int) $val
            );
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->baoSuccess('更新成功', U('menu/index'));
    }

    public function edit($attr_id = 0) {
        if ($attr_id = (int) $attr_id) {
            $obj = D('HouseAttribute');
            $menu = $obj->fetchAll();
            if (!isset($menu[$attr_id])) {
                $this->baoError('请选择要编辑的菜单');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['attr_id'] = $attr_id;
                if ($obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('Fangchanattr/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $menu[$attr_id]);
                $this->assign('datas', $menu);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的菜单');
        }
    }

    public function delete($attr_id = 0) {
        if ($attr_id = (int) $attr_id) {
            $obj = D('HouseAttribute');
            $menu = $obj->fetchAll();
            foreach ($menu as $val) {
                if ($val['parent_id'] == $attr_id)
                    $this->baoError('该菜单下还有其他子菜单');
            }
            $obj->where(array('attr_id'=>$attr_id))->save(array('closed'=>1));
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('Fangchanattr/index'));
        }
        $this->baoError('请选择要删除的菜单');
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['parent_id'] = (int) $data['parent_id'];
        if (empty($data['attr_name'])) {
            $this->baoError('请输入属性名称/值');
        }
        $data['attr_name'] = htmlspecialchars($data['attr_name'], ENT_QUOTES, 'UTF-8');
        $data['closed'] = 0;
        return $data;
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['parent_id'] = (int) $data['parent_id'];
        if (empty($data['attr_name'])) {
            $this->baoError('请输入菜单名称');
        }
        $data['attr_name'] = htmlspecialchars($data['attr_name'], ENT_QUOTES, 'UTF-8');
        $data['closed'] = 0;
        return $data;
    }

}