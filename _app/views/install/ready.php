<!DOCTYPE html>
<html lang="zh_CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>xyShare Install</title>
    <script src="https://cdn.jsdelivr.net/npm/flyio@0.6.14/dist/fly.min.js"></script>
</head>
<body>
    <h1>xyShare Install</h1>
    <div id="load">Please wait.....</div>
    <a id="link" href="javascript:">Click here to authorize</a><br><br>
        <script>
            var base=location.href.split("-")[0];
            fly.get(base+"-authurl",{callback:base+"-callback?"}).then(function(e){
                document.getElementById("load").style.display="none";
                var a=document.getElementById("link");
                a.href=e.data.url;
                a.style.display="block";
            });
        </script>
</body>
</html>