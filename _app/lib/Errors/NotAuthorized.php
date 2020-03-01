<?php
namespace xyToki\xyShare\Errors;
class NotAuthorized implements \Throwable{
    public function getMessage(){
        return "未授权";
    }
}