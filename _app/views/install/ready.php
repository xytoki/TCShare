<!DOCTYPE html>
<html lang="zh_CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>xyShare Install</title>
    <script src="https://cdn.jsdelivr.net/npm/flyio@0.6.14/dist/fly.min.js"></script>
</head>
<body>
    <?php 
        if(isset($_GET['faild'])){
            echo "授权失败，请重试。";
        }
    ?>
    <h1>xyShare Install</h1>
    <div id="load">Please wait.....</div>
    <a id="link" style="display:none" href="javascript:">Click here to authorize</a><br><br>
        <script>
            var base=location.href.split("/-")[0];
            fly.get(base+"/-authurl",{callback:base+"/-callback?"}).then(function(e){
                document.getElementById("load").style.display="none";
                var a=document.getElementById("link");
                a.href=e.data.url;
                a.style.display="block";
            }).catch(function(err){
                if(err.response)document.getElementById("load").innerHTML="<pre>Error："+err.response.data+"</pre>";
            });
        </script>
</body>
</html>