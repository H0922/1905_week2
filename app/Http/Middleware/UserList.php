<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Redis;
use Closure;

class UserList
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $reuqest_url=md5($_SERVER['REQUEST_URI']);
        $token=$_SERVER['HTTP_TOKEN'];
        $key='str:count:u:'.$token.':url:'.$reuqest_url; 
        $count=Redis::get($key);
        if($count >=5){
            echo '您频繁的访问此接口，请稍后十秒钟再试';
            Redis::expire($key,10);
            die;
        }
        return $next($request);
    }
}
