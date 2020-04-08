<?php
namespace xyToki\xyShare\DAV;
use Sabre\DAV;
class Controller{
    function __construct($path,$client){
        $this->client = $client;
        $current=$client->getFileInfo("/");
        $rootDirectory = new Folder("/",$current,$client);
        $server = new DAV\Server($rootDirectory);
        $server->setBaseUri($path);
        $server->addPlugin(new DAV\Browser\Plugin());
        $server->exec();
    }
}