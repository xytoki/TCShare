<?php
namespace xyToki\xyShare\Rules;
use Flight;
class referrer implements abstractRule{
    static function check($path,$rule){
        $hosts=explode(",",$rule['val']);
        if($_SERVER['HTTP_REFERER'] ==""){
            return (isset($rule['empty'])&&$rule['empty']=="false")?self::fail():XS_RULE_PASS;
        }
        $ref = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        if($rule['mode']&&$rule['mode']=="white"){
            $hosts[]=$_SERVER['HTTP_HOST'];
            foreach($hosts as $host){
                if(substr($ref, 0 - strlen($host)) == $host) {
                    return XS_RULE_PASS;
                } 
            }
            return self::fail();
        }else{
            foreach($hosts as $host){
                if(substr($ref, 0 - strlen($host)) == $host) {
                    return self::fail();
                } 
            }
            return XS_RULE_PASS;
        }
    }
    static function fail(){
        Flight::response()->status(403);
        echo "403";
        return XS_RULE_HALT;
    }
}