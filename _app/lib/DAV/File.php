<?php
namespace xyToki\xyShare\DAV;
use Sabre\DAV;
use Mimey\MimeTypes;
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
    function getLastModified(){
      	return strtotime($this->file->timeModified());
    }
    function getContentType(){
        $mimes = new MimeTypes;
        $filemime=$mimes->getMimeType($this->file->extension());
        if(!$filemime)$filemime = "application/octet-stream";
        return $filemime;
    }
    public function getQuotaInfo(){
        return [
            0,
            1024*1024*1024
        ];
    }
}