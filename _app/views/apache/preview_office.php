<?php
$key=TC::createCachedUrl($file->url());
$url=TC::path(Flight::request()->base."/_app/cached/".$key,false);
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Preview of <?php echo $file->name();?></title>
        <style>
            html,body,iframe{
                margin:0;
                width: 100%;
                height:100%;
                overscroll-behavior: none;
            }
        </style>
    </head>
    <body>
        <iframe id="owa" allowfullscreen="true" src="about:blank" frameborder="0" sandbox="allow-popups allow-same-origin allow-scripts allow-forms"></iframe>
    </body>
    <script>
        document.getElementById("owa").src="https://owa-box.vips100.com/op/view.aspx?src="+encodeURIComponent(location.origin+"<?php echo $url;?>")
    </script>
</html>