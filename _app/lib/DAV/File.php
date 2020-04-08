<?php
namespace xyToki\xyShare\DAV;
use Sabre\DAV;
class File extends DAV\File {
    private $file;
    private $client;
    private $path;
    function __construct($path,$file,$client) {
        $this->path = $path;
        $this->file = $file;
        $this->client = $client;
    }
    function getName() {
        return $this->file->name();
    }
    function get() {
        $url = $this->file->url();
        if(!$url){
            //Url not present in list.Got it from client.
            $this->file = $this->client->getFileInfo($this->path);
            $url = $this->file->url();
        }
        header("HTTP/1.1 301 TCShare Redirect");
        header("Location: ".$url);
        exit;
    }
    function getSize() {
        return $this->file->size();
    }
    function getETag() {
        return '"' . $this->file->size() . '"';
    }
}