<?php
/* 欢迎使用TCShare v2！这是一个天翼云的目录列表程序。
   在下面填写你的网盘信息，然后授权使用吧！
   v1和v2版本配置文件不兼容，麻烦手动修改一下！
   配置文件依然在config.php里！
  */
use Firebase\JWT\JWT;
define("_LOCAL",dirname(__FILE__)."/_app");
ini_set("display_errors",1);
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
date_default_timezone_set('PRC');
define("TC_VERSION",json_decode(file_get_contents("composer.json"),true)['version']);
require 'config.php';
require _LOCAL.'/vendor/autoload.php';
require _LOCAL.'/sky.class.php';
require _LOCAL.'/TC.class.php';
require _LOCAL.'/routes.php';
function TC_add(){
    Flight::set('flight.views.path', _LOCAL.'/views');
    global $TC;
    foreach($TC['Apps'] as $app){
        $base=$app['route'];
        if(substr($base,-1)=="/"){
            $base=substr($base,0,strlen($base)-1);
        }
        define("APP_BASE",$base);
        Flight::route($base."/*",function() use($app,$TC){
            Flight::response()->header("X-Powered-by","TCShare@xyToki");
            Flight::response()->header("X-TCshare-version",TC_VERSION);
            global $RUN;
            $RUN['app']=$app;
            $hasKey=false;
            foreach($TC['Keys'] as $k){
                if($k['ID']==$app['key']){
                    $RUN=array_merge($RUN,$k);
                    $hasKey=true;
                    break;
                }
            }
            if(!$hasKey){
                throw new Error("请正确配置Key");
            }
            $urlbase=Flight::request()->base;
            if($urlbase=="/")$urlbase="";
            $RUN=array_merge($RUN,$k);
            $RUN['URLBASE']=$urlbase;
            return true;
        });
        TC_MainRoute($base);
    }
}
TC_add();
if(isset($_ENV['TENCENTCLOUD_RUNENV'])){
    /* Environment is Tencent SCF */
    function main_handler($event, $context){
        return Flight::start($event, $context, dirname(__FILE__));
    }
}else{
    Flight::start();
}