<?php 


class PassportAction extends CommonAction{

	private $create_fields = array('account', 'password', 'nickname');


    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['account'] = htmlspecialchars($_POST['account']);   
        if (!isMobile($data['account'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'只允许手机号注册' ,'hasmore' => false ,'page_total' => 1 );
            $this->stringify($data);
        }
        $data['password'] = htmlspecialchars($_POST['password']); //整合UC的时候需要

        if (empty($data['password']) || strlen($_POST['password']) < 6) {
            session('verify', null);
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'密码长度必须要在6个字符以上' ,'hasmore' => false ,'page_total' => 1 );
            $this->stringify($data);
        }
        $data['nickname'] = $data['account'];
        $data['ext0'] = $data['account']; //兼容UCENTER
        $data['mobile'] = $data['account'];
        $data['reg_ip'] = get_client_ip();
        $data['reg_time'] = NOW_TIME;
        return $data;
    }

    public function register() {
        if ($this->isPost()) {
            if (isMobile($mobile = htmlspecialchars($_POST['account']))) {
                if (!$scode = trim($_POST['scode'])) {
                    $data = array('code' => self::BAO_SCODE_ERROR ,'datas' =>'请输入短信验证码！' ,'hasmore' => false ,'page_total' => 1 );
                    $this->stringify($data);
                }              
                $verify_model = D('Mobileverify');                       
                if (!$verify_model->checkVerify($mobile,$scode)) {
                    $data = array('code' => self::BAO_SCODE_ERROR ,'datas' =>'请输入正确的短信验证码！' ,'hasmore' => false ,'page_total' => 1 );
                    $this->stringify($data);
                }
            }		
            $data = $this->createCheck();
			/*
            $invite_id = (int) session('invite_id');
            if (!empty($invite_id)) {
                $data['invite_id'] = $invite_id;
            }*/
            $password2 = $this->_post('password2');
            if ($password2 !== $data['password']) {
                $data = array('code' => self::BAO_REG_PSWD_ERROR,'datas' =>'两次密码不一致' ,'hasmore' => false ,'page_total' => 1 );
                $this->stringify($data);
            }
            //开始其他的判断了
            if (true == D('Passport')->register($data)) {
                $data = array('code' => self::BAO_REQUEST_SUCCESS,'datas' =>'恭喜您注册成功' ,'hasmore' => false ,'page_total' => 1 );
                //TODO
                $this->stringify($data);
            }
            $data = array('code' => self::BAO_DB_ERROR,'datas' => D('Passport')->getError() ,'hasmore' => false ,'page_total' => 1 );
            $this->stringify($data);
        }
    }

/*     public function sendsms() {
        if (!$mobile = htmlspecialchars($_POST['account'])) {
            $data = array('code' => self::BAO_PHONE_ERROR ,'datas' =>'请输入正确的手机号码' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
        if (!isMobile($mobile)) {
            $data = array('code' => self::BAO_PHONE_ERROR ,'datas' =>'请输入正确的手机号码' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
        if ($user = D('Users')->getUserByAccount($mobile)) {
            $data = array('code' => self::BAO_PHONE_EXIST_ERROR ,'datas' =>'手机号码已经存在' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
		if ($user = D('Users')->getUserByMobile($mobile)) {
            $data = array('code' => self::BAO_PHONE_EXIST_ERROR ,'datas' =>'手机号码已经存在' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
            
        }
        $randstring = D('Mobileverify')->getVerify($mobile);

        D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        $data = array('code' => self::BAO_REQUEST_SUCCESS ,'datas' =>'发送成功' ,'hasmore' => false ,'page_total' => 1);
        $this->stringify($data);
    } */

    public function third(){
        if (!$type = htmlspecialchars($_POST['type'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
        if (!$openid = htmlspecialchars($_POST['openid'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
        if (!$token = htmlspecialchars($_POST['token'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
        $data = array(
            'type' => $type,
            'open_id' => $openid,
            'token' => $token
        );
        $this->thirdlogin($data);
    }



    private function setuid($uid,$user_token){
        $data = array(
            'uid' => $uid,
            'token' => $user_token
        );
        D('Users')->save($data);
        $data = array('uid' => $uid);
        $users = D('Users')->where($data)->find();
        return $users;
    }

     private function thirdlogin($data) {
        $user_token = md5(uniqid());
        $bind = 0;
        $users = 0;
        if ($this->_CONFIG['connect']['debug']) { //调试状态下 可以直接就登录 不是调试状态就要走绑定用户名的流程
            $data['type'] = 'test'; //DEBUG状态是直接登录
            $connect = D('Connect')->getConnectByOpenid($data['type'], $data['open_id']);
            if (empty($connect)) {
                $connect = $data;
                $connect['connect_id'] = D('Connect')->add($data);
            } else {
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'token' => $data['token']));
            }
            if (empty($connect['uid'])) {
                $account = $data['type'] . rand(100000, 999999) . '@qq.com';
                $user = array(
                    'account' => $account,
                    'password' => rand(100000, 999999),
                    'nickname' => $data['type'] . $connect['connect_id'],
                    'ext0' => $account,
                    'create_time' => NOW_TIME,
                    'create_ip' => get_client_ip(),
                );
                if (!D('Passport')->register($user))
                    $this->error('创建帐号失败');

                $token = D('Passport')->getToken();
                $connect['uid'] = $token['uid'];
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $connect['uid']));
            }

            setUid($connect['uid']);
            if(IS_WEIXIN) {
                cookie('access', $connect['connect_id']);
                $back_url = cookie('wx_back_url');
                $back_url = $back_url ? $back_url :U('index/index') ;
                header("Location:".$back_url);
            }
            header("Location:" . U('index/index'));
            die;
        } else {
            $connect = D('Connect')->getConnectByOpenid($data['type'], $data['open_id']);
            if (empty($connect)) {
                $connect = $data;
                $connect['connect_id'] = D('Connect')->add($data);
            } else {
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'token' => $data['token']));
            }
            if (empty($connect['uid'])) {
                if($this->uid){
                    D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $this->uid));
                    $this->stringify(array('code'=>'200'));
                }else{
                    session('connect', $connect['connect_id']);
                    if($data['type']=='wx') {
                        /*
                        cookie('access', $connect['connect_id']);
                        $back_url = cookie('wx_back_url');
                        $back_url = $back_url ? $back_url :U('index/index') ;
                        header("Location:".$back_url);
                        */
                    }
                    $bind = 1;
                }
            } else {
                $users = $this->setuid($connect['uid'],$user_token);
                if($data['type']=='wx') {
                    /*
                    cookie('access', $connect['connect_id']);
                    $back_url = cookie('wx_back_url');
                    $back_url = $back_url ? $back_url :U('index/index') ;
                    header("Location:".$back_url);
                    */
                }
                
            }
            $this->stringify(array('code'=>'200','datas' => array('bind'=>$bind,'user_token'=>$user_token,'user_info'=>$users) ,'hasmore' => false ,'page_total' => 1));
            die;
        }
    }

    public function bind() {
        if (!$this->isPost()) {
            return;
        }
        if (!$p_type = htmlspecialchars($_POST['p_type'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
        if (!$type = htmlspecialchars($_POST['type'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
        if (!$openid = htmlspecialchars($_POST['openid'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
        if (!$token = htmlspecialchars($_POST['token'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
        if($type==='0'){
            if (!$account = htmlspecialchars($_POST['account'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
            }
            if (!$password = htmlspecialchars($_POST['password'])) {
            $data = array('code' => self::BAO_INPUT_ERROR ,'datas' =>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
            }
            $passport = D('passport');
            if($passport->login($account,$password)){
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $this->uid));
                $token     = $passport->getToken();
                $user_info = $passport->getUserInfo();
                $data = array('code' => self::BAO_LOGIN_SUCCESS,'datas' => array('msg' =>'登录成功！','data' => array('user_token'=>$token,'user_info'=>$user_info) ,'hasmore' => false ,'page_total' => 1));
                $this->stringify($data);
            }else{
                $this->stringify(array('code'=>self::BAO_INPUT_ERROR));
            }
        }else{
            $data = $this->createCheck();
            $password2 = $this->_post('password2');
            if ($password2 !== $data['password']) {
                $data = array('code' => self::BAO_REG_PSWD_ERROR,'datas' =>'两次密码不一致' ,'hasmore' => false ,'page_total' => 1 );
                $this->stringify($data);
            }
            //开始其他的判断了
            if (true == D('Passport')->register($data)) {
                $data = array('code' => self::BAO_REQUEST_SUCCESS,'datas' =>'恭喜您绑定成功' ,'hasmore' => false ,'page_total' => 1 );
                $this->stringify($data);
            }
            $data = array('code' => self::BAO_DB_ERROR,'datas' => D('Passport')->getError() ,'hasmore' => false ,'page_total' => 1 );
            $this->stringify($data);
        }
       // $this->display();
    }

	
	public function index(){
		//$this->redirect('login');
	}


    public function login() {
//        $this->ajaxReturn(['code' => '124334']);
        if($this->uid){
            $data = array('code' => self::BAO_LOGIN_ALREADY ,'datas' =>'您已经登录了,不要重复登录!' ,'hasmore' => false ,'page_total' => 1 );
            $this->stringify($data);
        }
        
        if ($this->isPost()) {        
            if(!$account = $this->_post('account')){
                $data = array('code' => self::BAO_LOGIN_ACCOUNT_ERROR,'datas' =>'请输入用户名！' ,'hasmore' => false ,'page_total' => 1 );
                $this->stringify($data);
            }
            if(!$password = $this->_post('password')){
                $data = array('code' => self::BAO_LOGIN_PSWD_ERROR,'datas' =>'请输入登录密码！' ,'hasmore' => false ,'page_total' => 1 );
                $this->stringify($data);
            }

            $passport = D('Passport');
            if (true == $passport->login($account, $password)) {
                $token     = $passport->getToken();
                $user_info = $passport->getUserInfo();
                $is_shop = D('shop')->where(array('user_id'=>intval($user_info['user_id'])))->count();
                $user_info['face'] = get_remote_file_path($user_info['face']);
                $user_info['is_shop'] = $is_shop?1:0;

                import('Baocms.Rongyun.RongCloud');
                $appKey = 'e0x9wycfxebfq';
                $appSecret = 'NtQCUb6zYJ';
                $jsonPath = "jsonsource/";
                $RongCloud = new RongCloud($appKey,$appSecret);
                $result = $RongCloud->user()->getToken($user_info['user_id'], $user_info['nickname'], get_remote_file_path($user_info['face']));
                
                $data = array('code' => self::BAO_LOGIN_SUCCESS,'datas' =>array('msg' =>'登录成功！','data' =>array('user_token'=>$token,'session_id'=>session_id(),'user_info'=>$user_info,'rongyun'=>json_decode($result))) ,'hasmore' => false ,'page_total' => 1);
                $this->stringify($data);
            } else {
                $data = array('code' => self::BAO_LOGIN_ERROR ,'datas' => D('Passport')->getError() ,'hasmore' => false ,'page_total' => 1 );
                $this->stringify($data);
            }
        }
    }

    public function record(){
        if ($this->isPost()) {
            $user_id = htmlspecialchars($this->_post('user_id'));
            $app_type = htmlspecialchars($this->_post('app_type'));
            $data['user_id'] = $user_id;
            $user = D('Users')->where($data)->find();
            if(!$user){
                $data = array('code'=> self::BAO_DB_ERROR ,'datas'=>D('Users')->getError() ,'hasmore' => false ,'page_total' => 1);
                $this->stringify($data);
            }else{
                $data['user_id'] = $user_id;
                $data['app_type'] = $app_type;
                $ret = M('app_user')->add($data);
                $data = array('code'=>self::BAO_REQUEST_SUCCESS ,'datas'=>'' ,'hasmore' => false ,'page_total' => 1);
                $this->stringify($data);
            }
        }else{
            $data = array('code'=>self::BAO_DB_ERROR ,'datas'=>'' ,'hasmore' => false ,'page_total' => 1);
            $this->stringify($data);
        }
    }


	public function newpwd() {
		$yzm = $this->_param('yzm');
		$account = $this->_param('account');
        if (empty($account)) {
            session('verify', null);
			$data = array('code' => self::BAO_INPUT_ERROR, 'datas'=>"请输入用户名!" ,'hasmore' => false ,'page_total' => 1);
        }else if(!$user = D('Users')->getUserByAccount($account)){
			 session('verify', null);
			 $data = array('code'=>self::BAO_USER_NOT_EXISTS,'datas'=>'用户不存在!' ,'hasmore' => false ,'page_total' => 1);
		}else{
			$way = $this->_param('way');
			$password = rand_string(8, 1);
			switch ($way) {
				case 1:
					$email = $this->_param('email');
					if (empty($email) || $email != $user['email']) {
						$data = array('code'=>self::BAO_INPUT_ERROR,'datas'=>'邮件不正确!' ,'hasmore' => false ,'page_total' => 1);
					}else{
						D('Passport')->uppwd($user['account'], '', $password);
						D('Email')->sendMail('email_newpwd', $email, '重置密码', array('newpwd' => $password));
						$data = array('code'=>self::BAO_REQUEST_SUCCESS ,'datas'=>'重置密码成功!' ,'hasmore' => false ,'page_total' => 1);
					}
                    break;
				default:
					$mobile = $this->_param('mobile');
					if (empty($mobile) || $mobile != $user['mobile']) {
						$data = array('code'=>self::BAO_INPUT_ERROR,'datas'=>'手机号码不正确!' ,'hasmore' => false ,'page_total' => 1);
					}else{
						D('Passport')->uppwd($user['account'], '', $password);
						D('Sms')->sendSms('sms_newpwd', $mobile, array('newpwd' => $password));
						$data = array('code'=>self::BAO_REQUEST_SUCCESS ,'datas'=>'重置密码成功！' ,'hasmore' => false ,'page_total' => 1);
					}
                    break;
			}
		}
		$this->stringify($data);
    }

    public function hello()
    {
        $this->stringify(array('code'=>111111,'datas'=>'OOOOOOOOOK' ,'hasmore' => false ,'page_total' => 1));
    }
}
