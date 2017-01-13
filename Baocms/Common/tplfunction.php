<?php
	
	//获得表字段信息
	function get_table_info($id,$table,$field){
		$info = M($table)->find($id);
		return $info[$field];
	}
	
	/**
	 * [获得所在地区的所有父级]
	 * @param  [int] $area_id [区域ID]
	 * @param  string $connect [父亲间的连接方式]
	 * @param  [string] $field [要连接的字段]
	 * @return [string]          [所有父级]
	 */
	function get_area_parent($area_id,$connect=" - ",$field="areaname"){
		$sql = "SELECT T2.areaid,T2.areaname FROM (SELECT @r AS _id,(SELECT @r := parentid FROM bao_region WHERE areaid = _id) AS parent_id,@l := @l + 1 AS lvl FROM (SELECT @r := ".$area_id.", @l := 0) vars,bao_region h WHERE @r <> 0) T1 JOIN bao_region T2 ON T1._id = T2.areaid ORDER BY T1.lvl DESC;";
		$mode = M("region");
		$parent_list = $mode->query($sql);
		$parent_lists=array();
		foreach ($parent_list as $key => $value) {
			$parent_lists[]=$value[$field];
		}
		return implode($connect, $parent_lists);
	}
	/**
	 *功能：获得得文件路径
	 *参数：$table 表名,$field 字段名,$id 表ID
	 *时间：2015-8-10
	 *作者：王恒
	 */
	function get_remote_file_path($file_id,$field=""){
	    if(empty($file_id)){
	    	if($field){
	        	return $field;
	        }else{
	        	return "http://".$_SERVER['HTTP_HOST']."/".__ROOT__."/Public/images/default.jpg";
	        }
	    }
	    $checkstatus = is_numeric($file_id);
	    if(!$checkstatus){
	       
	        return "http://".$_SERVER['HTTP_HOST'].'/attachs/'.$file_id;
	    }
	    
	     $mode = M('File','pic_',"DB_CONFIG3");
	    $info = $mode->find($file_id);

	     if(empty($info)){
	        return "http://".$_SERVER['HTTP_HOST']."/".__ROOT__."/Public/static/images/default.jpg";

	     }
	//    p($info);die;
	    return C('PICLIB_URL')."Uploads/Download/".$info["savepath"].$info["savename"];
	}
    /**
     * get_cate_url()
     * 
     * @param mixed $url
     * @return
     */
    function get_cate_url($url){
        
        return $ret_url;
    }