<?php
/**
 * 描述：地址模型
 * 时间：2015-7-28 12:09
 * 作者：王恒
 * 邮箱：337886915@qq.com
 */
class RegionModel extends CommonModel {
    protected $pk = 'areaid';
    protected $tableName = 'region';
    protected $token = 'region';
    //获得父级ID
    public function get_parent_id($id){
        $pk = $this->pk;
        $where[$pk] = $id;
        return $this->where($where)->getField('parentid');
    }
    //获得父级详细信息
    public function get_parent_info($id){
        $parent_id = $this->get_parent_id($id);
        return $parent_id?$this->find($parent_id):'';
    }
    /**
     *描述：获得所有你父级
     *参数：$id 地区ＩＤ，$is_contain 是否包含本信息
     *增加时间：2015-8-10
     *更新时间：2015-8-14
     */
    public function get_all_parent_info($id,$is_contain=false){
        $return=array();
        $parent_info = $this->get_parent_info($id);
        if($is_contain){
         $parent_info = $this->find($id);  
        }
        while($parent_info){
            $return[]=$parent_info;
            $parent_info = $this->get_parent_info($parent_info[$this->pk]);
        }
        sort($return);
        return $return;
    }
    /**
     *描述：获得父级字符串连接
     *
     */
    public function str_all_parent($id,$is_contain=false,$connect=""){
        $str="";
        $all_parent = $this->get_all_parent_info($id,$is_contain);
        foreach($all_parent as $k=>$v){
            $str.=$v['areaname'].$connect;
        }
        return $str;
    }
    /**
     *描述：获得同级
     *时间：2015-8-13　17：50
     */
    public function get_sibling_info($id){
        $parent_id = $this->get_parent_id($id);

        return $this->order('areaid asc')->where(array('parentid'=>$parent_id))->select();
    }
    /**
     *描述：获得地址联动的当前位置信息
     *参数: $id 地区ＩＤ，$is_contain 是否包含本信息
     *增加时间：2015-8-13　18:01
     *更新时间：2015-8-14　11：31
     */
    public function get_current_location($id,$is_contain=false){
        $location = $this->get_all_parent_info($id,$is_contain);
        $varname[0] ="privonce"; 
        $varname[1] ="city"; 
        $varname[2] ="county"; 
        $varname[3] ="village"; 
        foreach ($varname as $k => $v) {
               if(isset($location[$k])){ //判断当前地区所在的层级
                    $location[$v]=$this->get_sibling_info($location[$k]['areaid']);
               }else{
                //没有该层级通过上级层查出兄弟层
                $parent_id = $location[$varname[$k-1]][0]['areaid'];
                if(isset($location[$k-1])){
                    $parent_id = $location[$k-1]['areaid'];
                }
                
                    $child_id = $this->get_first_child_id($parent_id);
                    $location[$v]=$this->get_sibling_info($child_id);
               } 
        }
        return $location;
        
    }
    /**
     *描述：根据区域名获得表中的ＩＤ，（带检数据存在否）
     *参数：$area_name 区域名
     *时间：2015-8-14　10：36
     */
    public function get_areaid_by_name($area_name){
        $where['areaname'] = $area_name;
          $areaid = $this->where($where)->getField('areaid');

          if(empty($areaid)){
            $where=array();
            $where['areaname'] = $area_name?array('like',$area_name."%"):'';
            $areaid = $this->where($where)->order('areaid asc')->getField('areaid');
            if(empty($areaid)){
                return '';
            }else{
                return $areaid;
            }
          }else{
            return $areaid;
          }
    }
    /**
     *描述：获得地区的下级地区
     *参数：$parentid
     *作者：王恒
     *时间：2015-8-14 14:50
     */
    public function get_child_area($parentid){
        $where['parentid'] = $parentid;
        return $this->where($where)->select();
    }
    public function get_child_area_copy($parentid=0,$order="asc"){
        $where['parentid'] = $parentid;
        if(!empty($order)){
            return $this->where($where)->order("areaid $order")->field("areaid,areaname")->select();
        }
        return $this->where($where)->select();
    }
    /**
     *描述：获得地区的下级地区字串符
     *参数：$parentid
     *作者：王恒
     *时间：2015-8-14 15:00
     */
    public function childrens_to_string($parentid){
        $childrens  = $this->get_child_area($parentid);
        if(empty($childrens)){
            return false;
        }else{
            $string = "<option value=''>--请选择--</option>";
            foreach ($childrens as $k => $v) {
                $string .= "<option value={$v['areaid']}>{$v['areaname']}</option>";
            } 
        }
        
      return $string;  
    }
    /**
     *描述：生成地区联动下级地区
     * 参数：$area_id 当前地点ID
     * 时间：2015-9-11
     */
     public function create_select($area_id,$select_name="area_name[]",$class="area_class"){
        $option = $this->childrens_to_string($area_id);
        if(empty($option)){
            echo "";
        }else{
            $str = "<select name='".$select_name."' class=".$class.">";
            $str .= $option;
            $str .= "</select>";
            echo $str;die;
        }
     }
    /**
     *描述:获得当前地区的第一个孩子
     *参数：$parentid
     *作者：王恒
     *时间：2015-8-14　16：48
     */
    public function get_first_child_info($id){
        return $this->where(array('parentid'=>$id))->order('areaid asc')->find();
    }
    /**
     *描述:获得当前地区的第一个孩子
     *参数：$parentid
     *作者：王恒
     *时间：2015-8-14　16：48
     */
    public function get_first_child_id($id){
        $info = $this->get_first_child_info($id);
        return $info['areaid'];
    }
    /**
     * 描述：获得百度地图经纬度
     * 参数：$area
     * 
     */
     public function get_poi_by_adress($area=''){
        
        if(empty($area)){
           $area = "四川省成都市高新区天府三街"; 
        }
        $return = file_get_contents("http://api.map.baidu.com/geocoder/v2/?address=".$area."&output=json&ak=K6NUCfSkWYRoWgpp7ssdfPc2");
        $return = json_decode($return);
        return $return->result->location;
     }
}

