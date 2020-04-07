<?php
namespace xyToki\xyShare\Providers;
use TC;
use Flight;
use Throwable;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use xyToki\xyShare\Cache;
use xyToki\xyShare\abstractInfo;
use xyToki\xyShare\authProvider;
use xyToki\xyShare\contentProvider;
use xyToki\xyShare\fileInfo;
use xyToki\xyShare\folderInfo;
use xyToki\xyShare\Errors\NotFound;
use xyToki\xyShare\Errors\NoPermission;
use xyToki\xyShare\Errors\NotAuthorized;
use xyToki\xyShare\Errors\NotConfigured;
use Symfony\Contracts\Cache\ItemInterface;

class caiyun implements contentProvider {
    private $sky;
    public $AK;
    public $SK;
    public $FD;
    public $BASE;
    public $token;
    public $keyPrefix="caiyun";
    const rootId="00019700101000000001";//Magic Number.Why?
    function __construct($options){
        if(empty($options['TOKEN'])){
            throw new NotAuthorized();
        }
        try{
            $j = json_decode($options['TOKEN']);
            if($j)$options['TOKEN'] = $j->cyToken;
        }catch(Throwable $e){}
        $this->cookie = $options['TOKEN'];
        $this->BASE = $options['BASE'];
        $this->NO_TRANSCODE = isset($options['app']['no_transcode'])?$options['app']['no_transcode']:false;
        $cookieJar = CookieJar::fromArray([
            '.mssc' => $options['TOKEN']
        ], 'caiyun.feixin.10086.cn');
        $this->http = new Client([
            'base_uri' => 'https://caiyun.feixin.10086.cn/portal/webdisk2',
            'timeout'  => 5.0,
            'cookies' => $cookieJar,
        ]);
    }
    private function getListById($id){
        $cache = Cache::getInstance();
        $key = $this->keyPrefix.".getListById.".md5($this->cookie).".".$id;
        $key = str_replace("/",".",$key);
        $cached=1;
        $res = $cache->get($key, function (ItemInterface $item) use(&$cached,$id) {
            $s_id = explode("/",$id);
            $cached=0;
            $item->expiresAfter(300);
            $res = $this->http->request("POST","/queryContentAndCatalog!disk.action", [
                'form_params' => [
                    "startNumber"=>'1',
                    "endNumber"=>'9999',
                    "contentID"=>$s_id[count($s_id)-1],
                    "path"=>$id
                ]
            ]);
            return (string)$res->getBody();
        }, isset($_GET['_tcshare_renew'])?INF:1.0);
        Flight::response()->header("X-TCShare-Caiyun-".rand(0,999),$key."=".($cached?"cached":"refreshed"));
        $r = json_decode($res,true);
        return $r['dci'];
    }
    private function getByPath($path){
        $path  = TC::path("/".$this->BASE."/".$path,false);
        if($path=="/"){
            return [
                "path"=>$this::rootId
            ];
        }else{
            $paths = explode("/",$path);
            array_shift($paths);
            $parent = [
                "path"=>$this::rootId
            ];
            foreach($paths as $i=>$n){
                if(!$parent['path']){
                    throw new NotFound;
                }
                $dir = $this->getListById($parent['path']);
                $current = false;
                foreach($dir['cataloginfos'] as $f){
                    if($f['catalogName']==$n){
                        $current = $f;
                        break;
                    }
                }
                foreach($dir['contents'] as $f){
                    if($f['contentName']==$n){
                        $current = $f;
                        break;
                    }
                }
                if(!$current){
                    throw new NotFound;
                }
                $parent = $current;
            }
        }
        return $current;
    }
    function getFileInfo($path){
        $fileInfo=$this->getByPath($path);
        if(isset($fileInfo['contentName'])){
            return new caiyunFileInfo($fileInfo,$this);
        }else{
            return new caiyunFolderInfo($fileInfo,$this);
        }
    }
    function listFiles($fileInfo){
        if(!$fileInfo instanceof caiyunFolderInfo)throw new \Exception();
        $list = $this->getListById($fileInfo->file['path']);
        $returns=[[],[]];
        $folders=TC::toArr($list['cataloginfos']);
        $files=TC::toArr($list['contents']);
        foreach($folders as $one){
            if(!$one)continue;
            $returns[0][]=new caiyunFolderInfo($one,$this);
        }
        foreach($files as $one){
            if(!$one)continue;
            $returns[1][]=new caiyunFileInfo($one,$this);
        }
        return $returns;
    }
}
class caiyunAuth {
    function __construct($options){
        $this->user = $options['USER'];
        $this->user = $options['PASS'];
        $cookieJar = CookieJar::fromArray([
        ], 'caiyun.feixin.10086.cn');
        $this->http = new Client([
            'base_uri' => 'https://caiyun.feixin.10086.cn/portal/webdisk2',
            'timeout'  => 5.0,
            'cookies' => $cookieJar,
        ]);
    }
}

class caiyunHook{
    function __construct($client){
        $this->client = $client;
        $this->run();
    }
    function run(){

    }
}

class caiyunAbstractInfo implements abstractInfo{
    public $file;
    function __construct($file){
        $this->file=$file;
    }
    public function isFolder(){
        
    }
    public function name(){
        return isset($this->file['contentName'])?$this->file['contentName']:$this->file['catalogName'];
    }
    public function timeModified(){
        return $this->formatCaiyunTime($this->file['updateTime']);
    }
    public function timeCreated(){
        return $this->formatCaiyunTime($this->file['uploadTime']);
    }
    private function formatCaiyunTime($d){
        $ds = str_split($d);
        array_splice($ds, 4, 0, ['-']);
        array_splice($ds, 7, 0, ['-']);
        array_splice($ds, 10, 0, [' ']);
        array_splice($ds, 13, 0, [':']);
        array_splice($ds, 16, 0, [':']);
        return implode("",$ds);
    }
}
class caiyunFileInfo extends caiyunAbstractInfo implements fileInfo{
    function __construct($file,$client){
        parent::__construct($file);
        $this->client=$client;
    }
    public function isFolder(){
        return false;
    }
    public function url(){
        $res = $this->client->http->get('/downLoadAction!downloadToPC.action?shareContentIDs='.$this->file['contentID']);
        return json_decode($res->getBody(),true)['redirectURL'];
    }
    public function preview(){
        switch ($this->client->NO_TRANSCODE){
            case "true":
                $transcode = false;
            break;
            case "except_ios":
                if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')){
                    $transcode = true;
                }else{
                    $transcode = false;
                }
            break;
            default:
                $transcode = true;
        }
        if($transcode&&$this->file['presentHURL']){
            return $this->file['presentHURL'];
        }
        return $this->url();
    }
    public function size(){
        return $this->file['contentSize'];
    }
    public function extension(){
        return strtolower($this->file['contentSuffix']);
    }
    public function thumbnail(){
        if(empty($this->file['thumbnailURL'])){
            return false;
        }
        return !empty($this->file['bigthumbnailURL'])?$this->file['bigthumbnailURL']:$this->file['thumbnailURL'];
    }
}
class caiyunFolderInfo extends caiyunAbstractInfo implements folderInfo{
    function __construct($file,$client){
        parent::__construct($file);
        $this->client=$client;
    }
    public function isFolder(){
        return true;
    }
    public function zipDownload(){
        $key="caiyun.zipDownloadFolder.".$this->file['catalogID'];
        return Cache::getInstance()->get($key, function (ItemInterface $item){
            $item->expiresAfter(600);
            $res = $this->client->http->post('/downLoadAction!downLoadZipPackage.action',[
                'form_params' => [
                    'catalogList'=>$this->file['catalogID']."|".$this->file['catalogType'],
                    'recursive'=>1,
                    'zipFileName'=>'TCShare_'.$this->file['catalogName'],
                    'contentList'=>""
                ]
            ]);
            return json_decode($res->getBody(),true)['downloadUrl'];
        },isset($_GET['_tcshare_renew'])?INF:1.0);
    }
    public function hasIndex(){
        //Not Implemented.
        //TODO
        return false;
    }
}