<?php

class MobileverifyModel extends CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'Mobile_verify';

    public function getVerify($mobile) {
        $res = $this->where(array('mobile'=>$mobile))->delete();    
        $randstring = rand_string(6, 1);
        $data['mobile'] = $mobile;
        $data['verify'] = $randstring;
        $this->add($data);
        return $randstring;
    }

    public function delVerify($mobile) {
        return $this->where(array('mobile'=>$mobile))->delete();
    }
    
    //验证码验证
    public function checkVerify($mobile,$code){
        $s_code = $this->where(array('mobile'=>$mobile))->getField('verify');
        if($s_code==$code){
            $this->delVerify($mobile);
            return true;
        }else{
            return false;
        }
    }
} 