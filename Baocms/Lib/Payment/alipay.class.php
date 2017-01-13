<?php
require_once("alipay/alipay_submit.class.php");
require_once("alipay/alipay_notify.class.php");

class alipay {

    public function getCode($logs, $setting) {            
        $real_method = $setting['service'];
        switch ($real_method) {
            case '0':
                $service = 'trade_create_by_buyer';
                break;
            case '1':
                $service = 'create_partner_trade_by_buyer';
                break;
            case '2':
                $service = 'create_direct_pay_by_user';
                break;
            
        }
//         $parameter = array(
//             'service' => $service,
//             'partner' => $setting['alipay_partner'],
//             '_input_charset' => 'utf-8',
//             'notify_url' => __HOST__ . U( 'pchome/payment/respond', array('code' => 'alipay')),
//             'return_url' => __HOST__ . U( 'pchome/payment/respond', array('code' => 'alipay')),
//             /* 业务参数 */
//             'subject' => $logs['subject'],
//             'out_trade_no' => $logs['subject'] . $logs['logs_id'],
//             'price' => $logs['logs_amount'],
//             'quantity' => 1,
//             'payment_type' => 1,
//             /* 物流参数 */
//             'logistics_type' => 'EXPRESS',
//             'logistics_fee' => 0,
//             'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
//             /* 买卖双方信息 */
//             'seller_email' => $setting['alipay_account']
//         );
//         ksort($parameter);
//         reset($parameter);

//         $param = '';
//         $sign = '';
//         foreach ($parameter as $key => $val) {
//             $param .= "$key=" . urlencode($val) . "&";
//             $sign .= "$key=$val&";
//         }
//         $param = substr($param, 0, -1);
//         $sign = substr($sign, 0, -1) . $setting['alipay_key'];
        $parameter = array(
            "service"       => $service,
            "partner"       => $setting['alipay_partner'],
            "seller_id"  => $setting['alipay_partner'],
            "payment_type"	=> "1",
            "notify_url"	=> U( 'payment/index/respond','',true,false,C('BASE_SITE')),
            "return_url"	=> U( 'payment/index/respond','',true,false,C('BASE_SITE')),
            "_input_charset"	=> strtolower('utf-8'),
            "out_trade_no"	=> $logs['subject'] . $logs['logs_id'],
            "subject"	=> $logs['subject'],
            "total_fee"	=> $logs['logs_amount'],
//            "show_url"	=> '',
            //"app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
//            "body"	=> "总价：" . $logs['logs_amount'] . "元",
            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
            //如"参数名"	=> "参数值"   注：上一个参数末尾需要“,”逗号。     
        );
        $AlipaySubmit = new AlipaySubmit($setting);
        //除去待签名参数数组中的空值和签名参数
        $para_filter = paraFilter($parameter);       
        //对待签名参数数组排序
        $para = argSort($para_filter);
        //生成签名结果
        $sign = $AlipaySubmit->buildRequestMysign($para);
        $para['sign'] = $sign;
        $para['sign_type'] = 'RSA';
        foreach ($para as $key => $val) {
            $param .= "$key=" . urlencode($val) . "&";            
        }
        $param = substr($param, 0, -1);        
//        $button = '<div style="text-align:center"><input type="button" class="payment" onclick="window.open(\'https://www.alipay.com/cooperate/gateway.do?' . $param . '&sign=' . md5($sign) . '&sign_type=MD5\')" value=" 立刻支付 " /></div>';
        $button = '<div style="text-align:center"><input type="button" class="payment" onclick="window.location.href=\'https://www.alipay.com/cooperate/gateway.do?' . $param . '\'" value=" 立刻支付 " /></div>';
        return $button;
    }

    public function respond($alipay_config) {
        if (!empty($_POST)) {
            foreach ($_POST as $key => $data) {
                $_GET[$key] = $data;
            }
        }
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        if (!$verify_result) {
            return false;
        }
        $payment = D('Payment')->getPayment($_GET['code']);
        $logs_id = str_replace($_GET['subject'], '', $_GET['out_trade_no']);
        $logs_id = trim($logs_id);
        $logs_id = think_decrypt($logs_id);        
        /* 检查支付的金额是否相符 */
        if (!D('Payment')->checkMoney($logs_id, $_GET['total_fee'])) {
            return false;
        }

        if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS' || $_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
            /* 改变订单状态 */
            D('Payment')->logsPaid($logs_id);
            return true;
        } else {
            return false;
        }
    }

}