<?php
namespace xyToki\xyShare\Errors;
class NotFound extends \Exception{
    protected $message = NULL;
    function __construct(){
        $this->message = "文件不存在";
    }
}