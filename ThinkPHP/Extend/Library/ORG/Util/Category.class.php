<?php

/**
* 处理无限级分类
*/
class Category{
	/**/
	static public function unlimitedForLevel($cate, $html="--", $pid = 0, $level=0,$fix='pid',$id='id',$forlevel=0)
	{
		$arr = array();
		foreach ($cate as $v) {


			if ($v[$fix] == $pid) {
				
				if($forlevel>0){
					if($level>=$forlevel){
						
						break;
					}
				}
				$v['level'] = $level + 1;
				$v['html'] = str_repeat($html, $level);
				$arr[] = $v;
				$arr = array_merge($arr, self::unlimitedForLevel($cate,$html,$v[$id],$level+1,$fix,$id,$forlevel));
			}
		}
		return $arr;
	}
	/*组合多维数组*/
	static public function unlimitedForLayer($cate, $name='child', $access = null, $pid = 0,$pname='pid',$tname='id'){
		$arr = array();
		foreach ($cate as $v) {
			if (is_array($access)) {
				$v['access'] = in_array($v[$tname], $access)?1:0;
			}
			if ($v[$pname] == $pid) {
				$v[$name] = self::unlimitedForLayer($cate, $name, $access,$v[$tname],$pname,$tname);
				$arr[] = $v;
			}
		}
		return $arr;
	}
	/*递归返回所有上级*/
	static public function getParents($cate, $id){
		$arr = array();
		foreach ($cate as $v) {
			if ($v['id'] == $id) {
				$arr[] = $v;
				$arr = array_merge($arr, self::getParents($cate, $v['pid']));
			}
		}

		return $arr;
	}

		/*递归返回所有上级*/
	static public function getParents2($cate, $id){
		$arr = array();
		foreach ($cate as $v) {
			if ($v['areaid'] == $id) {
				$arr[] = $v;
				$arr = array_merge($arr, self::getParents2($cate, $v['parentid']));
			}
		}

		return $arr;
	}
	//递归返回所有下级
	static public function  getchildren($cates,$id){
		$arr = array();
		foreach($cates as $v){
	  		if($v['pid'] == $id){
	   			$arr[] = $v;
	   			$arr = array_merge($arr, self::getchildren($cates,$v['id']));
			}
		}
		return $arr;
	}
	//根据给的PID得出是这个PID的所有分类
	static public function getpidlist($cates, $pid){
		$arr = array();
		foreach ($cates as $v) {
			if ($v['pid'] == $pid) {
				$arr[] = $v;
			}
		}
		return $arr;
	}
}
