<?php
namespace xyToki\xyShare\Errors;
class NoPermission implements \Throwable{
    public function getMessage(){
        return "无权限";
    }
}