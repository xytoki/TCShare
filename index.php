<?php
/* 
 * 欢迎使用TCShare v3！这是一个云盘目录列表程序。
 * @晓羽 2020.03
 */
use xyToki\xyShare\Config;
use xyToki\xyShare\Controller;
define("_LOCAL",dirname(__FILE__)."/_app");
ini_set("display_errors",1);
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
date_default_timezone_set('PRC');
define("TC_VERSION",json_decode(file_get_contents("composer.json"),true)['version']);
require _LOCAL.'/vendor/autoload.php';
define("XS_RULE_CHECK",-1);
define("XS_RULE_HALT",0);
define("XS_RULE_PASS",1);
define("XS_RULE_SKIP",PHP_INT_MAX);
function TC_add(){
    Flight::set('flight.views.path', _LOCAL.'/views');
    Controller::cachedUrl();
    global $TC;
    $bases=[];
    /* 访问规则 */
    Controller::rules($TC['Rules']);
    /* 主程序 */
    foreach($TC['Apps'] as $app){
        $base=$app['route'];
        if(substr($base,-1)=="/"){
            $base=substr($base,0,strlen($base)-1);
        }
        $bases[]=$base;
        Controller::prepare($app,$base);
        Controller::installer($base);
        Controller::disk($base);
    }

    Flight::map("notFound",function(){
        global $RUN;
        try{
            Flight::render($RUN['app']['theme']."/404");
        }catch(Throwable $e){
            Flight::render("404");
            return;
        }
    });
}
if(isset($_ENV['TENCENTCLOUD_RUNENV'])){
    /* Environment is Tencent SCF */
    define("XY_IS_SCF",true);
    Config::loadFromEnv();
    TC_add();
    function main_handler($event, $context){
        return Flight::start($event, $context, dirname(__FILE__));
    }
}else{
    Config::loadFromEnv();
    TC_add();
    Flight::start();
}