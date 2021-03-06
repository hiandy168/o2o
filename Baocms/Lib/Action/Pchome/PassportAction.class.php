<?php

//header("Access-Control-Allow-Origin:*"); 

class PassportAction extends CommonAction {

    private $create_fields = array('account', 'password', 'nickname');

    public function register() {
        if ($this->isPost()) {
             $is_agree = $this->_post('is_agree');
            if (!$is_agree) {
                $this->baoError('请同意注册条约!', 2000, true);
            }
            if (isMobile(htmlspecialchars($_POST['account']))) {
                if (!$scode = trim($_POST['scode'])) {
                    $this->baoError('请输入短信验证码！');
                }
                if (!$mobile = htmlspecialchars($_POST['account'])) {
                    $this->baoError('请输入正确的手机号码');
                }
                $verify_model = D('Mobileverify');                
                $scode2 = $verify_model->checkVerify($mobile,$scode);
                
                
                if (!$scode2) {
                    $this->baoError('请输入正确的短信验证码！');
                }
            } else {
                $yzm = $this->_post('yzm');
                if (strtolower($yzm) != strtolower(session('verify'))) {
                    session('verify', null);
                    $this->baoError('验证码不正确!', 2000, true);
                }
            }           
            $data = $this->createCheck();
           
            
            $password2 = $this->_post('password2');
            if ($password2 !== $data['password']) {
                session('verify', null);
                $this->baoError('两次密码不一致', 3000, true);
            }
            
            //开始其他的判断了
            if (D('Passport')->register($data)) {   
                if (isMobile($mobile)){
                    $verify_model->delVerify($mobile);                    
                }
                $this->baoSuccess('恭喜您，注册成功！', U('pcucenter/index/index','','html',false,C('BASE_SITE')));
            }
            $this->baoError(D('Passport')->getError(), 3000, true);
        } else {
            SESSION('user_register_refresh_page', NOW_TIME, 360);
            SESSION('user_register_send_sms', false, 360);
            $this->display();
        }
    }
   
     public function sendsms() {
         // 验证码发送合法性验证
         if(!isset($_SESSION['user_register_refresh_page'])){
             die('过期的注册页面');
         }

        if (!$mobile = htmlspecialchars($_POST['mobile'])) {
            die('请输入正确的手机号码');
        }
        if (!isMobile($mobile)) {
            die('请输入正确的手机号码');
        }

         if ($user = D('Users')->getUserByAccount($mobile)) {
             die('手机号码已经存在！');
         }
         if ($user = D('Users')->getUserByMobile($mobile)) {
             die('手机号码已经存在！');
         }

         if(isset($_SESSION['user_register_refresh_page']) && (NOW_TIME - SESSION('user_register_refresh_page')) < 5){
             die('短信发送失败，请重新再试！');
         }

         if(NOW_TIME - SESSION('user_register_send_sms') < 60){
             die('短信发送过频繁，请稍后再试！');
         }

        // 短信发送时间
         SESSION('user_register_send_sms', NOW_TIME, 360);

        $randstring = D('Mobileverify')->getVerify($mobile);

        D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        die('1');
    }

    /**
     *发送手机验证码
     */
/*       public function sendmobilesms(){
        if (!$mobile = htmlspecialchars($_POST['mobile'])) {
            die('请输入正确的手机号码');
        }
        if (!isMobile($mobile)) {
            die('请输入正确的手机号码');
        }
        $randstring = D('Mobileverify')->getVerify($mobile);
        
        D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        die('1');
     } */

    /**
     *发送手机验证码
     */
/*       public function sendlinkmobilesms(){
        if (!$mobile = htmlspecialchars($_POST['mobile'])) {
            die('请输入正确的联系人手机号码');
        }
        if (!isMobile($mobile)) {
            die('请输入正确的联系人手机号码');
        }
        $randstring = D('Mobileverify')->getVerify($mobile);
        
        D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        die('1');
     } */

    public function logout() {

        D('Passport')->logout();
        //$this->success('退出登录成功！', U('index/index'));
        if($_GET['ref_url']){
            $ref_url = $_GET['ref_url'];
            
        }else{
            $ref_url = $_SERVER['HTTP_REFERER'];
        }
        header("location:".$ref_url);
    }

    public function login() {
        if ($this->isPost()) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->baoError('验证码不正确!', 2000, true);
            }
            $account = $this->_post('account');
            if (empty($account)) {
                session('verify', null);
                $this->baoError('请输入用户名!', 2000, true);
            }

            $password = $this->_post('password');
            if (empty($password)) {
                session('verify', null);
                $this->baoError('请输入登录密码!', 2000, true);
            }
            $backurl = $this->_post('backurl', 'htmlspecialchars_decode');
            //$backurl = htmlspecialchars_decode($backurl);
//            $this->baoError($password, 2000, true);
            if (empty($backurl))
                $backurl = U('pcucenter/index/index','','html',false,C('BASE_SITE'));
            if (true == D('Passport')->login($account, $password)) {
                $this->baoSuccess('恭喜您登录成功！', $backurl);
            }
            $this->baoError(D('Passport')->getError(), 3000, true);
        } else {

            if($_GET['ref_url']){

                $backurl = I('ref_url');
            }elseif (!empty($_SERVER['HTTP_REFERER']) &&  !strstr($_SERVER['HTTP_REFERER'], 'passport')) {
                $backurl = $_SERVER['HTTP_REFERER'];
            } else {
                $backurl = U('pcucenter/index/index','','html',false,C('BASE_SITE'));
            }
            $this->assign('backurl', $backurl);
            $this->display();
        }
    }

    public function bind() {
        $connect = session('connect');

        $this->assign('connect', D('Connect')->find($connect));

        $this->assign('types', array('qq' => '腾讯QQ', 'weixin' => '微信', 'weibo' => '微博'));
        $this->display();
    }

    public function login2() { //这里修改一下
        if ($this->isPost()) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->baoError('验证码不正确!', 2000, true);
            }
            $account = $this->_post('account');
            if (empty($account)) {
                session('verify', null);
                $this->baoError('请输入用户名!', 2000, true);
            }

            $password = $this->_post('password');
            if (empty($password)) {
                session('verify', null);
                $this->baoError('请输入登录密码!', 2000, true);
            }
            if (true == D('Passport')->login($account, $password)) {
                $this->baoLoginSuccess();
            }
            $this->baoError(D('Passport')->getError(), 3000, true);
        } else {
            $this->display();
        }
    }
	
	public function login3() { //这里修改一下
        if ($this->isPost()) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->niuError('验证码不正确!', 2000, true);
            }
            $account = $this->_post('account');
            if (empty($account)) {
                session('verify', null);
                $this->niuError('请输入用户名!', 2000, true);
            }

            $password = $this->_post('password');
            if (empty($password)) {
                session('verify', null);
                $this->niuError('请输入登录密码!', 2000, true);
            }
            if (true == D('Passport')->login($account, $password)) {
                $this->niuLoginSuccess();
            }
            $this->niuError(D('Passport')->getError(), 3000, true);
        } else {
            $this->display();
        }
    }

    public function check() {

        $this->display();
    }

    public function ajaxloging() {

        $this->display();
    }

	public function ajaxloging1() {

        $this->display();
    }
    
    public function asyn_login(){
        $this->display();
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['account'] = htmlspecialchars($_POST['account']);
        if (!isMobile($data['account']) && !isEmail($data['account'])) {
            session('verify', null);
            $this->baoError('用户名只允许手机号码或者邮件!', 2000, true);
        }
        $data['password'] = htmlspecialchars($data['password']); //整合UC的时候需要
        if (empty($data['password']) || strlen($data['password']) < 6) {
            session('verify', null);
            $this->baoError('请输入正确的密码!密码长度必须要在6个字符以上', 2000, true);
        }
        $data['nickname'] = $data['account'];
        if (isEmail($data['account'])) { //如果邮件的@前面超过15就不好了
            $local = explode('@', $data['account']);
            $data['ext0'] = $local[0];
        } else {
            $data['mobile'] = $data['account']; //绑定手机号
            $data['ext0'] = $data['account']; //兼容UCENTER
        }
        
        $data['reg_ip'] = get_client_ip();
        $data['reg_time'] = NOW_TIME;
        return $data;
    }

    //两种找回密码的方式 1个是通过邮件 //填写了2个就改密码相对来说是不太合理，但是毕竟逻辑和操作相对简单一些！
    public function forget() {
        SESSION('user_forget_refresh_page', NOW_TIME, 360);
        SESSION('user_forget_send_sms', false, 360);
        $this->display();
    }

    public function newpwd() {
        $user_model = D('Users');
        $verify = I('verify');
        $phone = I('phone');
        $reset_password_sms_verify = session('reset_password_sms_verify');
        if ($verify != $reset_password_sms_verify){
            $this->baoError('验证码错误');
        }
        $password = I('password','','trim');
        $repassword = I('repassword','','trim');
        if ($password != $repassword){
            $this->baoError('两次密码输入不一致');
        }
        if (strlen($password) < 6){
            $this->baoError('密码不能少于六位');
        }
        $user = $user_model->where(array('account'=>$phone))->find();
        if (!$user){
            $this->baoError('该用户不存在');   
        }            
        if ($user_model->where(array('account'=>$phone))->setField('password', md5($password))){
            session('reset_password_sms_verify',null);
            $this->baoSuccess('重置密码成功！', U('passport/login'));
        }        
    }
    
    public function send_sms_verify() {
        // 验证码发送合法性验证
        if(!isset($_SESSION['user_forget_refresh_page'])){
            $this->ajaxReturn(['code' => 'error' ,'msg' => '过期的页面！']);
        }

        if (!$mobile = htmlspecialchars($_POST['phone'])) {
            $data = array('code' => 'error' ,'msg' =>'请输入正确的手机号码');
            $this->ajaxReturn($data);
        }
        if (!isMobile($mobile)) {
            $data = array('code' => 'error' ,'msg' =>'请输入正确的手机号码');
            $this->ajaxReturn($data);
        }

        if (!$user = D('Users')->getUserByAccount($mobile)) {
            $this->ajaxReturn(['code' => 'error', 'msg' => '手机号码不存在！']);
        }
        if (!$user = D('Users')->getUserByMobile($mobile)) {
            $this->ajaxReturn(['code' => 'error', 'msg' => '手机号码不存在！']);
        }

        if(isset($_SESSION['user_forget_refresh_page']) && (NOW_TIME - SESSION('user_forget_refresh_page')) < 3){
            $this->ajaxReturn(['code' => 'error', 'msg' => '短信发送失败，请重新再试！']);
        }

        if(NOW_TIME - SESSION('user_forget_send_sms') < 60){
            $this->ajaxReturn(['code' => 'error', 'msg' => '短信发送过频繁，请稍后再试！']);
        }

        // 短信发送时间
        SESSION('user_forget_send_sms', NOW_TIME, 360);

        $randstring = rand_string(6, 1);
        session('reset_password_sms_verify',$randstring);
        
        D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        $data = array('code' => 'success' ,'msg' =>'发送成功');
        $this->ajaxReturn($data);
    }
    
    public function suc() {
        $way = (int) $this->_get('way');
        switch ($way) {
            case 1:
                $this->success('密码重置成功！请登录邮箱查看！', U('passport/login'));
                break;
            default:
                $this->success('密码重置成功！请查看验证手机！', U('passport/login'));
                break;
        }
    }

    public function wblogin() {
        $login_url = 'https://api.weibo.com/oauth2/authorize?client_id=' . $this->_CONFIG['connect']['wb_app_id'] . '&response_type=code&redirect_uri=' . urlencode(__HOST__ . U('passport/wbcallback'));
        header("Location:$login_url");
        die;
    }

    public function qqlogin() {
        $state = md5(uniqid(rand(), TRUE));

        session('state', $state);
        $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
                . $this->_CONFIG['connect']['qq_app_id'] . "&redirect_uri=" . urlencode(__HOST__ . U('passport/qqcallback'))
                . "&state=" . $state
                . "&scope=";
        header("Location:$login_url");
        die;
    }

    public function wxlogin() {
        $state = md5(uniqid(rand(), TRUE));

        session('state', $state);
        $login_url = 'https://open.weixin.qq.com/connect/qrconnect?appid=' . $this->_CONFIG['connect']['wx_app_id']
                . '&redirect_uri=' . urlencode(__HOST__ . U('passport/wxcallback')) . '&response_type=code&scope=snsapi_login&state=' . $state . '#wechat_redirect';
        header("Location:$login_url");
        die;
    }

    public function wbcallback() {

        import("@/Net.Curl");
        $curl = new Curl();

        $params = array(
            'grant_type' => 'authorization_code',
            'code' => $_REQUEST["code"],
            'client_id' => $this->_CONFIG['connect']['wb_app_id'],
            'client_secret' => $this->_CONFIG['connect']['wb_app_key'],
            'redirect_uri' => __HOST__ . U('passport/qqcallback')
        );
        $url = 'https://api.weibo.com/oauth2/access_token';
        $response = $curl->post($url, http_build_query($params));
        $params = json_decode($response, true);
        if (isset($params['error'])) {
            echo "<h3>error:</h3>" . $params['error'];
            echo "<h3>msg  :</h3>" . $params['error_code'];
            exit;
        }
        $url = 'https://api.weibo.com/2/account/get_uid.json?source=' . $this->_CONFIG['connect']['wb_app_key'] . '&access_token=' . $params['access_token'];
        $result = $curl->get($url);
        $user = json_decode($result, true);
        if (isset($user['error'])) {
            echo "<h3>error:</h3>" . $user['error'];
            echo "<h3>msg  :</h3>" . $user['error_code'];
            exit;
        }
        $data = array(
            'type' => 'weibo',
            'open_id' => $user['uid'],
            'token' => $params['access_token']
        );
        $this->thirdlogin($data);
    }

    public function wxcallback() {
        if ($_REQUEST['state'] == session('state')) {
            import("@/Net.Curl");
            $curl = new Curl();
            if (empty($_REQUEST["code"])) {
                $this->error('授权后才能登陆！', U('passport/login'));
            }
            $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->_CONFIG['connect']["wx_app_id"] .
                    '&secret=' . $this->_CONFIG['connect']["wx_app_key"] .
                    '&code=' . $_REQUEST["code"] . '&grant_type=authorization_code';
            $str = $curl->get($token_url);

            $params = json_decode($str, true);
            if (!empty($params['errcode'])) {
                echo "<h3>error:</h3>" . $params['errcode'];
                echo "<h3>msg  :</h3>" . $params['errmsg'];
                exit;
            }
            if (empty($params['openid'])) {
                $this->error('登录失败', U('passport/login'));
            }
            $data = array(
                'type' => 'weixin',
                'open_id' => $params['openid'],
                'token' => $params['refresh_token']
            );
            $this->thirdlogin($data);
        }
    }

    public function qqcallback() {
        import("@/Net.Curl");
        $curl = new Curl();
        if ($_REQUEST['state'] == session('state')) {
            $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
                    . "client_id=" . $this->_CONFIG['connect']["qq_app_id"] . "&redirect_uri=" . urlencode(__HOST__ . U('passport/qqcallback'))
                    . "&client_secret=" . $this->_CONFIG['connect']["qq_app_key"] . "&code=" . $_REQUEST["code"];
            $response = $curl->get($token_url);

            if (strpos($response, "callback") !== false) {
                $lpos = strpos($response, "(");
                $rpos = strrpos($response, ")");
                $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
                $msg = json_decode($response);
                echo "<h3>error:</h3>" . $msg->error;
                echo "<h3>msg  :</h3>" . $msg->error_description;
                exit;
            }
            $params = array();
            parse_str($response, $params);
            if (empty($params))
                die;
            $graph_url = "https://graph.qq.com/oauth2.0/me?access_token="
                    . $params['access_token'];

            $str = $curl->get($graph_url);
            if (strpos($str, "callback") !== false) {
                $lpos = strpos($str, "(");
                $rpos = strrpos($str, ")");
                $str = substr($str, $lpos + 1, $rpos - $lpos - 1);
            }

            $user = json_decode($str, true);
            if (isset($user['error'])) {
                echo "<h3>error:</h3>" . $user['error'];
                echo "<h3>msg  :</h3>" . $user['error_description'];
                exit;
            }
            if (empty($user['openid']))
                die;
            $data = array(
                'type' => 'qq',
                'open_id' => $user['openid'],
                'token' => $params['access_token']
            );
            $this->thirdlogin($data);
        }
    }

    private function thirdlogin($data) {
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
                if(!D('Passport')->register($user))                    $this->error ('创建帐号失败');
                
                $token =   D('Passport')->getToken();
                $connect['uid'] = $token['uid'];
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $connect['uid']));
            }

            setUid($connect['uid']);
            session('access', $connect['connect_id']);
            header("Location:" . U('pcucenter/index/index','','html',false,C('BASE_SITE')));
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
                    $this->success('绑定第三方登录成功',U('mcenter/information/index'));
                }else{
                    session('connect', $connect['connect_id']);
                    header("Location: " . U('passport/bind'));
                }
            } else {
                setUid($connect['uid']);
                session('access', $connect['connect_id']);
                header("Location:" . U('pcucenter/index/index','','html',false,C('BASE_SITE')));
            }
            die;
        }
    }

}
