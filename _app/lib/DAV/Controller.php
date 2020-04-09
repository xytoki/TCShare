<?php
namespace xyToki\xyShare\DAV;
use Sabre\DAV;
class Controller{
    function __construct($path,$client,$pass){
        $current=$client->getFileInfo("/");
        $rootDirectory = new Folder("/",$current,$client);
        $server = new DAV\Server($rootDirectory);
        $server->setBaseUri($path);
        $server->addPlugin(new DAV\Browser\Plugin());
        if(!empty($pass)){
            $authBackend = new DAV\Auth\Backend\BasicCallBack(function($username, $password) use($pass) {
                return "$username:$password"==$pass;
            });
            $authBackend->setRealm('TCShare WebDAV');
            $auth = new \Sabre\DAV\Auth\Plugin($authBackend);
            $server->addPlugin($auth);
        }
        $server->exec();
    }
}