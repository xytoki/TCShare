<?php
namespace xyToki\xyShare\Providers;

use xyToki\xyShare\Cache;
use xyToki\xyShare\authProvider;
use xyToki\xyShare\contentProvider;
use xyToki\xyShare\abstractInfo;
use xyToki\xyShare\fileInfo;
use xyToki\xyShare\folderInfo;
use xyToki\xyShare\Errors\NoPermission;
use xyToki\xyShare\Errors\NotAuthorized;
use xyToki\xyShare\Errors\NotConfigured;
use TC;
use Flight;
use Tsk\OneDrive\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Tsk\OneDrive\Services\OneDriveService;
class onedrive implements contentProvider {
    public $redirectUrl = 'https://tcshare-r.now.sh/';
    public $clientId = "be064381-a5ed-4399-970a-f9c2cd6eee99";
    public $clientSecret = "JIpGtK?xAuQe-V-B0On-7rRnDE8CyMJ1";
    public $keyPrefix;
    public $selectParams = '$top=9999&$expand=thumbnails&select=id,name,size,@microsoft.graph.downloadUrl,lastModifiedDateTime,createdDateTime,folder,file';
    public $cacheConfig = [
        "getFileInfo"=>600,
        "listFiles"  =>180
    ];
    function __construct($options){
        $this->client = new Client();
        $this->keyPrefix="onedrive";
        $this->init($options);
    }
    function init($options){
        if($options['AK']==""&&$options['SK']==""){

        }elseif($options['AK']==""||$options['SK']==""){
            throw new NotConfigured();
        }else{
            $this->clientId=$options['AK'];
            $this->clientSecret=$options['SK'];
            $this->redirectUrl=isset($options['redirect'])?$options['redirect']:$this->redirectUrl;
        }
        if(empty($options['ACCESS_TOKEN'])){
            throw new NotAuthorized();
        }
        $this->refreshToken=$options['ACCESS_TOKEN'];
        $this->basePath=$options['BASE'];
        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setScopes([
            'offline_access',
            'files.readwrite.all'
        ]);
        $this->getToken();
    }
    function getToken(){
        $cache = Cache::getInstance();
        if($this->token)return $this->token;
        $key = $this->keyPrefix.".accessToken.".md5($this->refreshToken);
        $key = str_replace(["/","\\"],".",$key);
        $cached=1;
        $this->token = $cache->get($key, function (ItemInterface $item) use(&$cached) {
            $cached=0;
            $item->expiresAfter(3500);
            return $this->client->refreshToken($this->refreshToken);
        }, 1.0);
        $this->client->setAccessToken($this->token);
        Flight::response()->header("X-TCShare-OneDrive-Token",$cached?"cached":"refreshed");
    }
    function finPath($file,$child=false){
        $file = TC::path("/".$this->basePath."/".$file,false);
        $ret = $file=="/"?"":(":$file".($child?":":""));
        return $ret;
    }
    function getFileInfo($file){
        $path = $this->finpath($file);
        $uri = $this->client::API_BASE_PATH."me/drive/root".$path;
        $req = new Request('GET',$uri."?".$this->selectParams);
        $res = $this->client->send($req);
        $res['path'] = $file;
        if(isset($res['folder'])){
            return new onedriveFolderInfo((array)$res);
        }else{
            return new onedriveFileInfo((array)$res);
        }
    }
    function listFiles($folderInfo){
        if(!$folderInfo instanceof onedriveFolderInfo)throw new \Exception();
        $file = $folderInfo->file['path'];
        $path = $this->finpath($file,true);
        $uri = $this->client::API_BASE_PATH."me/drive/root".$path."/children";
        $req = new Request('GET',$uri."?".$this->selectParams);
        $res = $this->client->send($req);
        $returns=[[],[]];
        $files=TC::toArr($res['value']);
        foreach($files as $one){
            if(!$one)continue;
            $one['path'] = str_replace("//","/",$file."/".$one['name']);
            if(isset($one['folder'])){
                $returns[0][] = new onedriveFolderInfo($one);
            }else{
                $returns[1][] = new onedriveFileInfo($one);
            }
        }
        return $returns;
    }
    function getCacheKey(onedriveFolderInfo $info){
        return $info->file['path'];
    }
}
class onedriveAuth implements authProvider{
    protected $client;
    public $token;
    public $keyPrefix;
    public $redirectUrl = 'https://tcshare-r.now.sh/';
    public $clientId = "be064381-a5ed-4399-970a-f9c2cd6eee99";
    public $clientSecret = "JIpGtK?xAuQe-V-B0On-7rRnDE8CyMJ1";
    function __construct($options){
        $this->client = new Client();
        $this->keyPrefix="onedrive";
        $this->init($options);
    }
    function init($options){
        if($options['AK']==""&&$options['SK']==""){

        }elseif($options['AK']==""||$options['SK']==""){
            throw new NotConfigured();
        }else{
            $this->clientId=$options['AK'];
            $this->clientSecret=$options['SK'];
            $this->redirectUrl=isset($options['FD'])?$options['FD']:$this->redirectUrl;
        }
        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setScopes([
            'offline_access',
            'files.readwrite.all'
        ]);
        $this->client->setRedirectUri($this->redirectUrl);
    }
    function url($callback){
        $url = (string)$this->client->createAuthUrl();
        return $url."&state=".urlencode($callback);
    }
    function getToken($code=""){
        if(empty($code))$code=$_GET['code'];
        $this->token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
        $refreshHash = md5($this->token['refresh_token']);
        $cache = Cache::getInstance();
        $key = $this->keyPrefix.".accessToken.".$refreshHash;
        $key = str_replace(["/","\\"],".",$key);
        $acctkItem = $cache->getItem($key);
        $acctkItem->expiresAfter(3500);
        $acctkItem->set($this->token);
        $cache->save($acctkItem);
    }
    function token(){
        return $this->token['refresh_token'];
    }
    function needRenew(){
        return false;
    }
    function expires(){
        return "";
    }
}
class onedriveAbstractInfo implements abstractInfo{
    public $file;
    function __construct($file){
        $this->file=$file;
    }
    public function isFolder(){
        
    }
    public function name(){
        return $this->file['name'];
    }
    public function timeModified(){
        return date("Y-m-d H:i:s",strtotime($this->file['lastModifiedDateTime']));
    }
    public function timeCreated(){
        return date("Y-m-d H:i:s",strtotime($this->file['createdDateTime']));
    }
}
class onedriveFileInfo extends onedriveAbstractInfo implements fileInfo{
    function __construct($file){
        parent::__construct($file);
    }
    public function isFolder(){
        return false;
    }
    public function url(){
        return $this->file['@microsoft.graph.downloadUrl'];
    }
    public function size(){
        return $this->file['size'];
    }
    public function extension(){
        return TC::ext($this->name());
    }
    public function thumbnail(){
        if(!isset($this->file['thumbnails'][0])){
            return false;
        }
        return $this->file['thumbnails'][0]['small']['url'];
    }
}
class onedriveFolderInfo extends onedriveAbstractInfo implements folderInfo{
    function __construct($file){
        parent::__construct($file);
    }
    public function isFolder(){
        return true;
    }
    public function hasIndex(){
        //Not Implemented.
        //TODO
        return false;
    }
}