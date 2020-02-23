<?php
namespace xyToki\xyShare;
use Symfony\Contracts\Cache\ItemInterface;
class Provider{
    public $client;
    function __construct($name,$options){
        $this->name   = $name;
        $this->route  = $options['app']['route'];
        $this->client = new $name($options);
        $this->cache  = Cache::getInstance();
        $this->exptime= is_numeric($_ENV['XS_CACHE_TIME'])?$_ENV['XS_CACHE_TIME']:1800;
    }
    function __call($func,$args){
        $cc = $this->client->cacheConfig;
        $brgs = $args;
        if( !is_array($cc)||!isset($cc[$func]) ){
            return $this->direct($func,$args);
        }
        $expTime = is_numeric($cc[$func])?$cc[$func]:$this->exptime;
        if(is_object($brgs[0])){
            $brgs[0] = $this->client->getCacheKey($brgs[0]);
        }
        $key = $this->name.$this->route."/".$func."?".http_build_query($brgs);
        $key = str_replace(["/","\\"],".",$key);

        return $this->cache->get($key, function (ItemInterface $item) use($key,$func,$expTime,$args) {
            $item->expiresAfter($expTime);
            \Flight::response()->header( "X-TCS-Cache".str_repeat(" ",strlen($key)), "Missed ".$key);
            return $this->direct($func,$args);
        }, 1.0);
    }
    private function direct($func,$args){
        return call_user_func_array([$this->client,$func],$args);
    }
}