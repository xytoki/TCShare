<?php
namespace xyToki\xyShare\DAV;
use Sabre\DAV;
use TC;
class Folder extends DAV\Collection {
    private $client;
    private $current;
    private $path;
    function __construct($path,$current,$client) {
        $this->current = $current;
        $this->client = $client;
        $this->path = $path;
    }
    function getChildren() {
        list($folders,$files)=$this->client->listFiles($this->current);
        $res=[];
        foreach($folders as $r){
            $res[]=new Folder(TC::path($this->path."/".$r->name()),$r,$this->client);
        }
        foreach($files as $r){
            $res[]=new File(TC::path($this->path."/".$r->name()),$r,$this->client);
        }
        return $res;
    }
    function childExists($name){
        return true;
    }
    function getName() {
        return $this->current->name();
    }
    function getLastModified(){
      	return strtotime($this->current->timeModified());
    }
    public function getQuotaInfo(){
        return [
            0,
            1024*1024*1024
        ];
    }
}