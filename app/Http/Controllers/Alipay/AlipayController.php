<?php

namespace App\Http\Controllers\Alipay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\ApiUserModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
class AlipayController extends Controller
{
   public function alipay()
   {
       //沙箱支付宝网关
        $url='https://openapi.alipaydev.com/gateway.do';

       //公共请求参数
        $appid='2016101300673001';
        $method = 'alipay.trade.page.pay';
        $charset = 'utf-8';
        $signtype = 'RSA2';
        $sign = '';
        $timestamp = date('Y-m-d H:i:s');
        $version = '1.0';
        $return_url = 'http://api.bianaoao.top/alipay/return';       // 支付宝同步通知
        $notify_url = 'http://api.bianaoao.top/alipay/notify';     // 支付宝异步通知地址
        $biz_content = '';

        //请求参数
        $out_trade_no = time() . rand(1111,9999);       //商户订单号
        $product_code = 'FAST_INSTANT_TRADE_PAY';
        $total_amount = 514704.22;
        $subject = '测试订单' . $out_trade_no;

        $request_param = [
            'out_trade_no'  => $out_trade_no,
            'product_code'  => $product_code,
            'total_amount'  => $total_amount,
            'subject'       => $subject
        ];

        $param = [
            'app_id'        => $appid,
            'method'        => $method,
            'charset'       => $charset,
            'sign_type'     => $signtype,
            'timestamp'     => $timestamp,
            'version'       => $version,
            'notify_url'    => $notify_url,
            'return_url'    => $return_url,
            'biz_content'   => json_encode($request_param)
        ];

        ksort($param);

        $str = "";
        foreach($param as $k=>$v)
        {
            $str .= $k . '=' . $v . '&';
        }

        $str = rtrim($str,'&');

        //jisuanqianming
        $key = storage_path('keys/app_priv');
        $priKey = file_get_contents($key);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($str, $sign, $res, OPENSSL_ALGO_SHA256);
        // dd($res);
        $sign = base64_encode($sign);
        $param['sign'] = $sign;
        $param_str = '?';
        foreach($param as $k=>$v){
            $param_str .= $k.'='.urlencode($v) . '&';
        }
        $param_str = rtrim($param_str,'&');
        $url = $url . $param_str;
        //发送GET请求
        //echo $url;die;
        header("Location:".$url);

   }

   //支付同步跳转
   public function return(){
       echo "支付成功 同步跳转";
   }

   //支付宝异步跳转
   public function notify(){
         // 1 接收 支付宝的POST数据
        //$data1 = file_get_contents("php://input");
        $data2 = json_encode($_POST);
        //$log1 = date('Y-m-d H:i:s') . ' >>> ' .$data1 . "\n";
        $log2 = date('Y-m-d H:i:s') . ' >>> ' .$data2 . "\n";
        //file_put_contents('alipay.log',$log1,FILE_APPEND);
        file_put_contents('logs/alipay.log',$log2,FILE_APPEND);
        $data = $_POST;
        $sign = base64_decode($data['sign']);
        unset($data['sign_type']);
        unset($data['sign']);
        //echo '<pre>';print_r($data);echo '</pre>';
        $d = [];
        // 2 url_decode
        foreach($data as $k=>$v){
            $d[$k] = urldecode($v);
        }
        //echo '<pre>';print_r($d);echo '</pre>';die;
        ksort($d);
        $str = "";
        foreach($d as $k=>$v){
            $str .= $k . '=' . $v . '&';
        }
        //带签名字符串
        $str = rtrim($str,'&');
        //读取公钥文件
        $pubKey = file_get_contents(storage_path('keys/ali_pub'));
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);
        // 验证签名
        $result = (bool)openssl_verify($str, $sign, $res, OPENSSL_ALGO_SHA256);
        //释放资源
        openssl_free_key($res);
        if($result){
            $log = date('Y-m-d H:i:s') . ' >>> 验签通过 1' . "\n\n";
            file_put_contents("logs/alipay.log",$log,FILE_APPEND);
        }else{
            $log = date('Y-m-d H:i:s') . ' >>> 验签失败 0' . "\n\n";
            file_put_contents("logs/alipay.log",$log,FILE_APPEND);
        }
        echo 'success';
   }

    protected function verify($data, $sign) {
        //读取公钥文件
        $pubKey = file_get_contents(storage_path('keys/ali_pub'));
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, $sign, $res, OPENSSL_ALGO_SHA256);
        //释放资源
        openssl_free_key($res);
        var_dump($result);
        return $result;
    }


    //注册
    public function create(){
        $pass1=request()->input('pass1');
        $pass2=request()->input('pass2');
        if($pass1 != $pass2){
            $json=[
                'errno'=>1006,
                'msg'=>'两次密码不一致'
            ];
            return $json;
        }
        $user_name=ApiUserModel::where('user_name','=',request()->input('user_name'))->value('user_name');
        if(request()->input('user_name') == $user_name ){
            $json=[
                'errno'=>1011,
                'msg'=>'用户名已存在'
            ];
            return $json;
        }
        $password=password_hash($pass1,PASSWORD_BCRYPT);
        $data=[
            'user_name'=>request()->input('user_name'),
            'user_password'=>$password,
            'user_emai'=>request()->input('user_emai'),
            'last_login'=>time(),
            'last_ip'=>$_SERVER['REMOTE_ADDR'],
        ];
        // dd($data);
        $userid=ApiUserModel::insertGetId($data);
        dump($userid);
    }
    //登录
    public function login(){
        $name=request()->input('user_name');
        $paw=request()->input('user_password');
        $data=ApiUserModel::where('user_name','=',$name)->first();
        if($data){
            $pas=password_verify($paw,$data->user_password);
           if($pas){
                $token=Str::random(32);
                $json=[
                    'errno'=>0,
                    'msg'=>'ok',
                    'data'=>[
                        'token'=>$token
                    ]
                ];
           }else{
               $json=[
                   'errno'=>1001,
                   'msg'=>'密码不正确'
               ];
           }
        }else{
        $json=[
            'errno'=>1002,
            'msg'=>'用户名不存在'
        ];
        }
        return $json;
    }

    //浏览记录
    public function userlist(){
        // dump($_SERVER);
        $reuqest_url=md5($_SERVER['REQUEST_URI']);
        $token=$_SERVER['HTTP_TOKEN'];
        $key='str:count:u:'.$token.':url:'.$reuqest_url; 
        $count=Redis::incr($key);   
        echo '浏览次数'.$count;  
        
    }



    //加密 
    public function  jia(){
        $str='huangxiaobo';
        
        echo '原始>>>>>'.$str;
        echo '<hr>';
        $strlen=strlen($str);
        $jia="";
        for($i=0;$i<$strlen;$i++){
            // echo $i;
        //    echo ord($str[$i])+3;echo '<br>';\
            //十进制加密
            $q=ord($str[$i])+1;
            // echo chr($q);echo '<br>';
            $a=chr($q);
            $jia.=$a;

        }
        echo '加密后>>>>>'.$jia;
        echo '<hr>';
        $jilen=strlen($jia);
        $j="";
        for($i=0;$i<$jilen;$i++){
            $b=ord($jia[$i])-1;
            $s=chr($b);
            $j.=$s;
        }
        echo '解密后>>>>>'.$j;
    }

}
