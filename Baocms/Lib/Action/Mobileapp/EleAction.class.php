<?php 

class EleAction extends CommonAction{

	public function index(){

		$_GET['p']=I('p');
		$field = '*';
		$map=array();
		//$city_id=I('city_id');
		//$map['city_id']=$city_id;
		//$map['is_open'] = 1;
		$_GET['p']=I('p');
		$lng=I('lng');
		$lat=I('lat');
		$order = I('order','','trim');
    if($lng&&$lat){
    	import('ORG.Util.Page'); // 导入分页类
    	$ele_model = D('Ele');
    	$orderby = '';
    	switch ($order) {
    		case 'd':
    			$orderby = array('since_money' => 'asc');
    			break;
    		case 'm':
    			$orderby = array('distribution' => 'asc');
    			break;
    		case 's':
    			$orderby = array('month_num' => 'desc');
    			break;
    		default:
    			$orderby = array('month_num' => 'desc');
    			break;
    	}
    	$full=I('full');
    if($full ==1){
    	$map['full_money']=array('NEQ','0');//满多少有优惠
    
    			}
    	$is_pay=I('is_pay');
    	if($is_pay ==2){
    		$map['is_pay']=1;//是否支持在线支付
    				}
    	$is_new=I('is_new');
    	if($is_new ==3){
    		//print_r($is_new);
    		$map['is_new']=1;//是付支持新用户优惠
    	}
    
    
    	$field .= ',1000*ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.'* 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380 as mydistance';
    	$map['range'] = array('exp','> `mydistance`');
    	$lists = $ele_model->table("(select $field from ".$ele_model->getTableName().") as temptable")->where($map)->order($orderby)->select();
    
    	$count = count($lists);  // 查询满足要求的总记录数
    	$Page = new Page($count,10); // 实例化分页类 传入总记录数和每页显示的记录数
    	//print_r($Page);
    
    	$list=$ele_model->table("(select $field from ".$ele_model->getTableName().") as temptable")->where($map)->limit($Page->firstRow.','.$Page->listRows)->order($orderby)->select();
    
    	foreach($list as $key=>$val){$list[$key]['logo']=get_remote_file_path($val['logo']); }
    if($_GET['p']<=ceil($count/10)){
    	$data['code']=200;
    	$data['hasmore']=true;
    	$data['datas']=$list;
    	$data['page_total']=ceil($count/10);
    	//print_r($data);
    	$this->ajaxReturn($data);
    
    }else{
    
    	$data['code']=200;
    	$data['hasmore']=false;
    	$data['datas']='';
    	$data['page_total']=ceil($count/10);
    
    	$this->ajaxReturn($data);
    	}
    
    }else{
    
    	$data['code']=400;
    	$data['datas']='';
    	$data['hasmore']=false;
    	$data['page_total']=0;
    	$this->ajaxReturn($data);
    
    }
  
    }
    
    
    
    public function city_id(){
    
    	$city=I('city');
    	$lng=I('lng');
    	$lat=I('lat');
    $citys=M('city');
    	$dada=$citys->where('name="'.$city.'"')->find();
    
    if($dada){
    
    	$data['code']=200;
    	$data['datas']=$dada['city_id'];
    	$data['hasmore']=false;
    	$data['page_total']=0;
    	$this->ajaxReturn($data);
    
    
    }else{
    	$tap['lng']=$lng;
    	$tap['lat']=$lat;
    	$tap['pinyin']=get_pinyin($city);
    	$tap['first_letter']=strtoupper(substr($tap['pinyin'],0,1));
    	$tap['is_open']=1;
    	$tap['name']=$city;
    	//print_r($tap);
    if($city&&$lng&&$lat){
        
    	//print_r($citys->add($tap));
    	$data['code']=1;
    	$data['datas']=$citys->add($tap);
    	$data['hasmore']=false;
    	$data['page_total']=0;
    	$this->ajaxReturn($data);
    
    
    }else{
    
    	$data['code']=400;
    	$data['datas']='';
    	$data['hasmore']=false;
    	$data['page_total']=0;
    	$this->ajaxReturn($data);   
    } 
    
    }
          
    }

}