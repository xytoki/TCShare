<?php
namespace xyToki\xyShare;
use Symfony\Component\Dotenv\Dotenv;
use Throwable;

class Config{
    static function loadFromEnv(){
        $dotenv = new Dotenv();
        $envList=[
            ".env",
            ".env.local",
            ".env.runtime"
        ];
        foreach($envList as $one){
            $tmpfile=dirname(_LOCAL)."/".$one;
            if(is_file($tmpfile))$dotenv->load($tmpfile);
        }
        list($keys,$apps,$rules)=self::parseEnv();
        global $TC;
        $TC=[
            "Keys"=>[],
            "Apps"=>[],
            "Rules"=>[]
        ];
        $TC['Keys']=array_merge($TC['Keys'],$keys);
        $TC['Apps']=array_merge($TC['Apps'],$apps);
        $TC['Rules']=array_merge($TC['Rules'],$rules);
    }
    static function parseEnv(){
        ksort($_ENV);
        $rules=[];
        $keys=[];
        $apps=[];
        foreach($_ENV as $k=>$value){
            $k.="_[END]";
            list($prefix,$type,$appid,$key)=explode("_",$k,4);
            //XS_APP_someapp_KEY=value
            //0  1   2       3
            $key=str_replace("_[END]","",$key);
            $key=str_replace("[END]","",$key);
            if($prefix!="XS")continue;
            if($type=="APP"){
                $apps[$appid]=isset($apps[$appid])?$apps[$appid]:[];
                $key=strtolower($key);
                if($key=="")$key="route";
                $apps[$appid][$key]=$value;
            }elseif($type=="KEY"){
                $keys[$appid]=isset($keys[$appid])?$keys[$appid]:[];
                $keys[$appid]["ID"]=$appid;
                $key=strtoupper($key);
                if($key=="")$key="provider";
                $keys[$appid][$key]=$value;
            }elseif($type=="SEC"){
                $rules[$appid]=isset($rules[$appid])?$rules[$appid]:[];
                $rules[$appid]["ID"]=$appid;
                $key=strtolower($key);
                if($key=="")$key="route";
                $rules[$appid][$key]=$value;
            }
        }
        return [$keys,$apps,$rules];
    }
    static function saveToEnvFile($key,$value){
        $envfile=dirname(_LOCAL)."/".".env.runtime";
        try{
            $envcontent=file_get_contents($envfile);
            if(!$envcontent)throw new \Error();
        }catch(Throwable $e){
            $envcontent ="# xyShare runtime config file\n";
            $envcontent.="# DO NOT EDIT\n";
        }
        $envList=explode("\n",$envcontent);
        $envKv=[];
        foreach($envList as $a){
            $a=explode("=",$a);
            $b=array_shift($a);
            if(!$b)continue;
            $envKv[$b]=trim(implode("=",$a));
            if($envKv[$b][0]=='"'){
                $envKv[$b]=substr($envKv[$b],1,strlen($envKv[$b])-2);
            }
        }
        $envKv[$key]=$value;
        $envKv["lastUpdateAt"]=date("Y-m-d H:i:s");
        $envcontent="";
        foreach($envKv as $k=>$one){
            if(!$k)continue;
            $envcontent.=$k;
            if(!strstr($k,"#")){
                $envcontent.="=";
            }
            if(strstr($one," ")){
                $one='"'.$one.'"';
            }
            $envcontent.=$one;
            $envcontent.="\n";
        }
        return file_put_contents($envfile,$envcontent);
    }
    static function write($key,$value){
        global $TC;
        if(defined('XY_IS_SCF')){
            return false;
        }else if(defined('XY_USE_CONFPHP')){
            return false;
        }else{
            return self::saveToEnvFile($key,$value);
        }
    }
}