<?php
namespace xyToki\xyShare\Rules;
use Flight;
use TC;
class password implements abstractRule{
    const cookiePrefix="TCSharePassword_";
    static function check($path,$rule){
        $password=$rule['val'];
        $cName=self::cookiePrefix.md5($rule['route']);
        $error=false;
        if(isset($_COOKIE[$cName])){
            if($_COOKIE[$cName]===md5($cName.$password)){
                return XS_RULE_PASS;
            }else{
                $error=true;
            }
        }
        return self::template($cName,$error);
    }
    static function template($cname,$error){
        global $RUN;
        try{
            Flight::render($RUN['app']['theme']."/password",[
                "name"=>$cname,
                "error"=>$error
            ]);
        }catch(\Throwable $e){
            self::realtpl($cname,$error);
        }
        return XS_RULE_HALT;
    }
    static function realtpl($name,$error){
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>密码保护 - TCShare</title>
    <script>
        window.TC=window.TC||{};
        TC.passname="<?php echo $name;?>";
        TC.passerror=<?php echo $error?"true":"false";?>;
    </script>
    <script src="<?php echo TC::viewpath("/password.js");?>"></script>
</head>
<body>
    <div><?php echo $error?"密码错误":"请输入密码";?></div>
    <form onsubmit="return TC.password(document.getElementById('pass').value);">
        <input id="pass" type="password">
        <button>提交</button>
    </form>
</body>
</html>
<?php
    }
}