<?php

namespace App\Http\Controllers\Sign;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SignController extends Controller
{

    
    public function sign1(){
        //代签名的数据
        $data='huangxiaobo123456';

        //密钥路劲
        $path=storage_path('key/priv.key');

        //获取钥
        $pkeyid=openssl_pkey_get_private("file://".$path);
       // dump($pkeyid);die;
        //非对称加密算法
        openssl_sign($data,$signature,$pkeyid,OPENSSL_ALGO_SHA256);
       // echo $signature;
        // 释放密钥资源
        openssl_free_key($pkeyid);

        //base64  编码   方便传输
        $sign_str=base64_encode($signature);
        $sign_url=urlencode($sign_str);
       
        $url='http://1905client.com/check?data='.$data.'&sign='.$sign_url;
        $get=file_get_contents($url);
        echo $get;

    }
}
