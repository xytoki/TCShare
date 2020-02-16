<?php
namespace xyToki\xyShare\Providers;
use xyToki\xyShare\authProvider;
use xyToki\xyShare\contentProvider;
use xyToki\xyShare\fileInfo;
use xyToki\xyShare\folderInfo;

use Tsk\OneDrive\Client;

class onedrive implements contentProvider {
    function __construct(){

    }
    function getFileInfo($file){}
    function listFiles($file){}
}
class onedriveAuth implements authProvider{
    protected $client;
    public $clientId;
    public $clientSecret;
    function __construct($options){
        $this->client = new Client();
        $this->init($options);
    }
    function init($options){
        $this->clientId=$options['AK'];
        $this->clientSecret=$options['SK'];
        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setRedirectUri('http://localhost/-callback');
        $this->client->setScopes([
            'offline_access',
            'files.readwrite.all'
        ]);
    }
    function url(){
        return $this->client->createAuthUrl();
    }
    function getToken($code=""){
        if(empty($code))$code=$_GET['code'];
        $this->token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
    }
    function token(){
        return json_encode($this->token);
    }
    function needRenew(){
        return false;
    }
    function expires(){
        return "";
    }
}
class onedriveFileInfo{
    function __construct(){

    }
}
class onedriveFolderInfo{
    function __construct(){

    }
    
}