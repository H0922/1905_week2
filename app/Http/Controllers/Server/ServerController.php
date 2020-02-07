<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use CURLFile;                                                                                            
class ServerController extends Controller
{
    // public new CURLFile::_construct ( string $filename [, string $mimetype [, string $postname ]] );
    public function goods(){
        // echo 'server页面';
        // echo '<hr>';
        $str=$_GET['str'];
        echo '原文>>>'.$str;
        echo '<hr>';
        $method='AES-256-CBC';
        $key='asdfuysdfhnjsdfsdbnj';
        $iv='RTHGHIHBJHJ22siw';
        $enc_data=openssl_encrypt($str,$method,$key,OPENSSL_RAW_DATA,$iv);
        echo '加密后'.$enc_data;
        // die;
        $base=base64_encode($enc_data);
        dump($base);
        $bas=urlencode($base);
        dump($bas);
        $url='http://1905client.com/client/goods?str='.$bas;
        dump($url);
        $data=file_get_contents($url);
        echo $data;
    }
    //对称加密AES
    public function good(){
        //接受回来的数据
        $str=[
            'name'=>'黄晓博',
            'age'=>'20',
            'email'=>'737051678@qq.com',
            'tel'=>13355402270,
        ];
        dump($str);
        echo '<hr>';
        //把数组转换为json
        $str=json_encode($str);
        dump($str);
        echo '<hr>';
        $method='AES-256-CBC';    //加密方式
        $key='asdfuysdfhnjsdfsdbnj';    //加密的密钥
        $iv='RTHGHIHBJHJ22siw';     //必须为16位
        $enc_data=openssl_encrypt($str,$method,$key,OPENSSL_RAW_DATA,$iv);
        echo '加密后'.$enc_data;
        // die;
        //因为加密后的密文是不可读的文本，将加密后的数据转换base64
        $base=base64_encode($enc_data);
        dump($base);
        //base64转换以后带有特殊符号，用此函数进行url编码
        $bas=urlencode($base);
        dump($bas);
        $url='http://1905client.com/client/good?str='.$bas;
        dump($url);
        //发起get请求
        $data=file_get_contents($url);
        echo $data;
    }
    //非对称加密rsa
    public function rsa(){
        //要加密的数据
        $u='huangxiaobo';
        echo '原始数据'.$u;
        echo '<hr>';
        //取出密钥文件内容
        $priv_key=file_get_contents(storage_path('key/prie.key'));
        //执行加密rsa加密
        openssl_private_encrypt($u,$enc_data,$priv_key);
        echo '加密数据：'.$enc_data;
        echo '<hr>';
        //转换为数据可见
        $base=base64_encode($enc_data);
        echo 'base64_encode：'.$base;
        echo '<hr>';
        //编译url
        $urlencode=urlencode($base);
        echo 'urlencode：'.$urlencode;
        echo '<hr>';
        $url='http://1905client.com/client/rsa?data='.$urlencode;
        echo 'url：'.$url;
        echo '<hr>';
        $get=file_get_contents($url);
        echo $get;
    }




    //curl测试
    public function curlpost(){
        //请求地址
        $url='http://1905api.comcto.com/test/curl2';
        //请求数据
        $data=[
            'name'=>'黄晓博',
            'age'=>'20',
            'email'=>'737051678@qq.com',
            'tel'=>13355402270,
        ];
        //初始化
        $ch=curl_init();
        //设置参数
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,1);
        //传输数据和类型
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        //开启会话发起请求
        curl_exec($ch);
        //关闭请求
        curl_close($ch);
        dd($ch);
    }
        public function curlfile(){
            // dd(444444);
            $url='http://1905api.comcto.com/test/curl3';
            $data=[
               'img1' => new \CURLFile('t.jpg')
            ];
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
            curl_exec($ch);
            curl_close($ch);
            dd($ch);
        }

        //签名
        public function sing(){
            //yan发送的数据
            $data=[
                'name'=>'黄晓博',
                'age'=>'20',
                'email'=>'737051678@qq.com',
                'tel'=>13355402270,
                'time'=>time()
            ];
            dump($data);
            //将数据字典式排顺序
            ksort($data);
            dump($data);
            //给字典序排序完成的数据转换为字符串
            $str='';
            foreach($data as $k=>$v){
                $str .= $k .'='.$v.'&';
            }   
            //把字符串的最后一个&符去掉
            $str=rtrim($str,'&');
            echo $str;
            echo '<hr>';
            //取出私钥
            $prive=file_get_contents(storage_path('key/prie.key'));
            // dd($prive);
            //编写签名
            openssl_sign($str,$signdate,$prive,OPENSSL_ALGO_SHA256);
            echo $signdate;
            //编写后的签名是不可读的   进行bsae64转换成可读状态  
            //  base有特殊符用urlencode进行uirl编译
            $sign=urlencode(base64_encode($signdate));
            dump($sign);
            //调用验证接口
            $url="http://1905client.com/client/sign?".$str.'&sign='.$sign;
            dump($url);
            //发起get请求
            $cb=file_get_contents($url);
            echo $cb;

        }

        public function sign2(){
             $sign_key="fijkfdfnmi";
             $data=[
                'name'=>'黄晓博',
                'age'=>'20',
                'email'=>'737051678@qq.com',
                'tel'=>13355402270,
                'time'=>time()
            ];
            ksort($data);
            // dump($data);
            $str="";
            foreach($data as $k=>$v){
                $str.=$k.'='.$v.'&';
            }
            $str=rtrim($str,'&');
            echo $str;
            echo '<hr>';
            $sign_jia=md5($str.$sign_key);
            echo $sign_jia;
            $url='http://1905client.com/client/sign2?'.$str.'&sign='.$sign_jia;
            dd($url);
            $a=file_get_contents($url);
            echo $a;
        }
}
