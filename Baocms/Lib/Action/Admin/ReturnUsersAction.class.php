<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/24 0024
 * Time: 下午 2:18
 */
class ReturnUsersAction extends CommonAction
{

    public function returnsms()
    {
        import('ORG.Util.Page'); // 导入分页类
        $map = array();
        if($keyword = $this->_param('keyword',  'htmlspecialchars')){
            $map['mobile'] = array('LIKE', '%'.$keyword.'%');
        }
        $count=D('Suggestions')->where($map)->count();
        $Page       = new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $datas=D('Suggestions')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('create_time desc')->select();
      foreach($datas as $key=>$val)
      {
          $datas[$key]['imgs']=json_decode($val['imgs']);
      }
        $this->assign('list',$datas);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('keyword',$keyword);
        $this->display();


    }
    public function delete()
    {
     $id=$_POST['id'];
     $id=D('Suggestions')->where(array('id'=>array('IN',$id)))->delete();
     if($id)
     {
         $this->baoSuccess('删除成功',U('ReturnUsers/returnsms'));
     }

        $this->baoSuccess('删除失败');
    }

    public function detas($id)
    {
    $list=D('Suggestions')->where(['id'=>$id])->find();
        if(!$list)
        {
            $this->baoSuccess('无效参数');
        }
       $imgs=json_decode($list['imgs']);
    $this->assign('list',$list);
        $this->assign('imgs',$imgs);
      $this->display();
    }






















}