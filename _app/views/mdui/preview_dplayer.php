<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Preview of <?php echo $file->name();?></title>
        <link crossorigin="anonymous" integrity="sha384-WBkDouo/0CCXxPpQ0M6rTUkTGZL30VNhKNg07BZy/8Le4IXY4jv/ihAvI1J9+s4b" href="https://lib.baomitu.com/dplayer/1.25.0/DPlayer.min.css" rel="stylesheet">
        <script src="<?echo TC::viewpath("/static/js/DPlayer.nocross.min.js");?>"></script>
        <script src="https://cdn.jsdelivr.net/npm/flyio/dist/fly.min.js"></script>
        <style>
            html,body{
                margin:0;
            }
            .dplayer-menu .dplayer-menu-item::before {
                padding: 0 10px;
                line-height: 30px;
                color: #eee;
                font-size: 13px;
                display: inline-block;
                vertical-align: middle;
                width: 100%;
                box-sizing: border-box;
                white-space: nowrap;
                text-overflow: ellipsis;
                overflow: hidden;
            }
            .dplayer-danmaku {
                display: block !important;
            }
        </style>
    </head>
    <body>
        <div id="dplayer"></div>
        <script src="https://cdn.jsdelivr.net/npm/assjs/dist/ass.js"></script>
        <script>
        var dp = new DPlayer({
            container: document.getElementById('dplayer'),
            video: {
                url: '<?php echo (method_exists($file,"preview"))?$file->preview():$file->url();?>',
            },
            subtitle:{
                url:"data:text/plain,"
            }
        });
        var paths = location.href.replace('?'+location.search,"").split(".");
        paths[paths.length-1] = "srt";
        var srtUrl = paths.join(".");
        paths[paths.length-1] = "ass";
        var assUrl = paths.join(".");
        fly.get(srtUrl+"?TC_direct").then(function(res){
            //srt存在
            //dplayer暂时不支持动态字幕，srt加载不了
        }).catch(function(){ 
            fly.get(assUrl+"?TC_direct").then(function(res){
                //ass存在
                var assBox = document.createElement("div");
                dp.template.danmaku.appendChild(assBox);
                assBox.id="assBox"
                dp.ass = new ASS(res.data,dp.template.video,{
                    container:document.getElementById("assBox")
                });
                dp.on("subtitle_show",function(){
                    assBox.style.display="block";
                });
                dp.on("subtitle_hide",function(){
                    assBox.style.display='none';
                });
                window.onresize = function() {
                    dp.ass.resize();
                };
                dp.on('canplay', function() {
                    dp.ass.resize();
                });
            })
        })
        var style = document.createElement('style');
        style.type = 'text/css';
        style.innerHTML = ".dplayer-menu-item:last-child::before {content: 'TCShare <?php echo TC_VERSION;?> / dpPlayer "+DPlayer.version+"';}";
        document.getElementsByTagName('head')[0].appendChild(style);
        </script>
    </body>
</html>