<?php
namespace xyToki\xyShare\Providers;
use TC;
use xyToki\xyShare\abstractInfo;
use xyToki\xyShare\authProvider;
use xyToki\xyShare\contentProvider;
use xyToki\xyShare\fileInfo;
use xyToki\xyShare\folderInfo;
use xyToki\xyShare\Errors\NotFound;
use xyToki\xyShare\Errors\NoPermission;
use xyToki\xyShare\Errors\NotAuthorized;
use xyToki\xyShare\Errors\NotConfigured;
use xyToki\xyShare\Providers\Ctyun\Sky;
use xyToki\xyShare\Providers\Ctyun\SkyHandle;

class ctyun implements contentProvider {
    private $sky;
    public $AK;
    public $SK;
    public $FD;
    public $BASE;
    public $token;
    public $cacheConfig = [
        "getFileInfo"=>180,
        "listFiles"  =>180
    ];
    function __construct($options){
        if($options['AK']==""||$options['SK']=="")
            throw new NotConfigured();
        if(empty($options['ACCESS_TOKEN'])){
            throw new NotAuthorized();
        }
        $this->sky=new SkyHandle(
            $options['AK'],
            $options['SK'],
            $options['ACCESS_TOKEN']
        );
        $this->AK=$options['AK'];
        $this->SK=$options['SK'];
        $this->FD=$options['FD'];
        $this->BASE=$options['BASE'];
        $this->token=$options['ACCESS_TOKEN'];
        if(is_numeric($options['CACHE_INFO']))$this->cacheConfig['getFileInfo']=$options['CACHE_INFO'];
        if(is_numeric($options['CACHE_LIST']))$this->cacheConfig['listFiles']=$options['listFiles'];
    }
    function getHandler(){
        return $this->sky;
    }
    private function finpath($path){
        return TC::path("/我的应用/".$this->FD."/".$this->BASE."/".$path,false);
    }
    function getFileInfo($path){
        $finpath=$this->finpath($path);
        $fileInfo=$this->sky->getFileInfo($finpath);
        if($this->FD==""||isset($fileInfo['code'])&&$fileInfo['code']=="PermissionDenied"){
            throw new \Error("应用无访问<code>".$finpath."</code>文件夹的权限，请检查应用目录是否正确填写");
        }
        if(isset($fileInfo['code'])&&$fileInfo['code']=="FileNotFound"){
            if($path=="/"||$path==""){
                throw new \Error("请手动建立<code>".$finpath."</code>文件夹");
            }
            throw new NotFound();
        }
        if(isset($fileInfo['code'])&&isset($fileInfo['message'])){
            throw new \Error("API错误: ".$fileInfo['message']);
        }
        //有md5的，都是文件
        if(!is_array($fileInfo['md5'])){
            return new ctyunFileInfo($fileInfo);
        }else{
            return new ctyunFolderInfo($fileInfo);
        }
    }
    function listFiles($fileInfo){
        if(!$fileInfo instanceof ctyunFolderInfo)throw new \Exception();
        $list=$this->sky->listFiles($fileInfo->file['id'])['fileList'];
        $returns=[[],[]];
        $folders=TC::toArr($list['folder']);
        $files=TC::toArr($list['file']);
        foreach($folders as $one){
            if(!$one)continue;
            $returns[0][]=new ctyunFolderInfo($one);
        }
        foreach($files as $one){
            if(!$one)continue;
            $returns[1][]=new ctyunFileInfo($one);
        }
        return $returns;
    }
    function getCacheKey(ctyunFolderInfo $info){
        return $info->file['id'];
    }
}
class ctyunAuth implements authProvider{
    protected $sky;
    function __construct($options){
        $this->AK=$options['AK'];
        $this->SK=$options['SK'];
        $this->prevToken=$options['ACCESS_TOKEN'];
        if(!$this->prevToken)$this->prevToken="";
        $this->sky=new Sky($this->AK,$this->SK);
    }
    function url($callback){
        return $this->sky->getAuthorizeURL($callback);
    }
    function getToken($code=""){
        if(empty($code))$code=$_GET['code'];
        $this->acctk=$this->sky->getAccessToken("code",$code);
        //if($this->prevToken&&$this->prevToken!=$this->token()){
        //    throw new \Error("AccessToken Mismatch");
        //}
    }
    function needRenew(){
        return true;
    }
    function token(){
        return $this->acctk['accessToken'];
    }
    function expires(){
        return date("Y-m-d H:i:s",$this->acctk['expiresIn']/1000);
    }
}
class ctyunAbstractInfo implements abstractInfo{
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
        return $this->file['lastOpTime'];
    }
    public function timeCreated(){
        return $this->file['createDate'];
    }
}
class ctyunFileInfo extends ctyunAbstractInfo implements fileInfo{
    function __construct($file){
        parent::__construct($file);
    }
    public function isFolder(){
        return false;
    }
    public function url(){
        return $this->file['fileDownloadUrl'];
    }
    public function size(){
        return $this->file['size'];
    }
    public function extension(){
        return TC::ext($this->name());
    }
    public function thumbnail(){
        if(!isset($this->file['icon'])){
            return false;
            //No Thumbnail.
        }
        return $this->file['icon']['smallUrl'];
    }
}
class ctyunFolderInfo extends ctyunAbstractInfo implements folderInfo{
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