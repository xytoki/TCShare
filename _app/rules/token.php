<?php
/*
 *  _tcshare=md5(path.token.(exptime-basetime)).(exptime-basetime)
 *  Example token calcuating code:
    function token($path,$secret,$exps){
        $baseTime=1580646002;        //2020-02-02 20:20:02 CST
        $exptime=time()+$exps-$baseTime;
        $usign=substr(md5($path.$secret.$exptime),0,16);
        return $usign.dechex($exptime);
    }
 * echo "_tcshare=",token("/test-path/000000?testkey=a&","tcshare_demo_key",300);
 */
namespace xyToki\xyShare\Rules;
use Throwable;

class token implements abstractRule{
    const baseTime="1580646002";        //2020-02-02 20:20:02 CST
    static function check($url,$rule){
        try{
            $secret=$rule['val'];
            if(isset($_GET['_tcshare'])){
                $path=str_replace("_tcshare=".$_GET['_tcshare'],"",$url);
                $path=str_replace("?&","?",$path);
                if(substr($path,-1,1)=="&"){
                    $path=substr($path,0,strlen($path)-1);
                }
                if(substr($path,-1,1)=="?"){
                    $path=substr($path,0,strlen($path)-1);
                }
                $usign=substr($_GET['_tcshare'],0,16);
                $exptime=hexdec(substr($_GET['_tcshare'],16));
                $psign=substr(md5($path.$secret.$exptime),0,16);
                if($psign===$usign && $exptime+self::baseTime >= time()){
                    return XS_RULE_SKIP;
                }
            }
            return XS_RULE_PASS;
        }catch(Throwable $e){}
        return XS_RULE_PASS;
    }
}