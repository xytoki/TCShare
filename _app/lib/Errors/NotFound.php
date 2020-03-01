<?php
namespace xyToki\xyShare\Errors;
class NotFound implements \Throwable{
    public function getMessage(){
        return "文件不存在";
    }
}