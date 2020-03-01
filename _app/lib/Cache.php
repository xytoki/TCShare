<?php
namespace xyToki\xyShare;
use \Symfony\Component\Cache\Adapter\RedisAdapter;
use \Symfony\Component\Cache\Adapter\MemcachedAdapter;
use \Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Cache{
    static $instance = false;
    static function getInstance(){
        if(!self::$instance){
            $adapter   = isset($_ENV['XS_CACHE_MODE'])?$_ENV['XS_CACHE_MODE']:"file";
            $cachepath = isset($_ENV['XS_CACHE_PATH'])?$_ENV['XS_CACHE_PATH']:null;
            $namespace = isset($_ENV['XS_CACHE_SALT'])?$_ENV['XS_CACHE_SALT']:"xyShare_".md5($_SERVER['HTTP_HOST']);
            $expiretime= is_numeric($_ENV['XS_CACHE_TIME'])?$_ENV['XS_CACHE_TIME']:1800;
            switch ($adapter){
                case "memcached":
                    if(!$cachepath)throw new \Error("Path is required for memcached caching");
                    $client = MemcachedAdapter::createConnection(explode(";",$cachepath));
                    self::$instance = new MemcachedAdapter($client,$namespace,$expiretime);
                break;
                case "redis":
                    if(!$cachepath)throw new \Error("Path is required for redis caching");
                    $client = RedisAdapter::createConnection($cachepath);
                    self::$instance = new RedisAdapter($client,$namespace,$expiretime);
                break;
                default:
                    self::$instance = new FilesystemAdapter($namespace,$expiretime,$cachepath);
            }
        }
        return self::$instance;
    }
}