<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Preview of <?php echo $file->name();?></title>
        <script src="https://cdn.jsdelivr.net/npm/artplayer/dist/artplayer.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flyio/dist/fly.min.js"></script>
        <style>
            html,body{
                margin:0;
            }
            #player {
                width: 100%;
                height: 100%;
            }
            .art-contextmenu-version a {
                display: none;
            }
            .art-control-subtitle {
                display: none;
            }
            .hasSub .art-control-subtitle {
                display: inline-block;
            }
        </style>
    </head>
    <body>
        <div id="player"></div>
        <script src="https://cdn.jsdelivr.net/npm/assjs/dist/ass.js"></script>
        <script>
        window.art = new Artplayer({
            container: '#player',
            url: '<?php echo $file->url();?>',
            title: '<?php echo $file->name();?>',
            pip: true,
            setting: true,
            loop: true,
            flip: true,
            playbackRate: true,
            fullscreen: true,
            subtitleOffset: true,
            localSubtitle: true,
            subtitle:{
                url:'data:text/plain,'
            },
            whitelist: [(ua)=>{ return true; }]
        });
        var paths = location.href.replace('?'+location.search,"").split(".");
        paths[paths.length-1] = "srt";
        var srtUrl = paths.join(".");
        paths[paths.length-1] = "ass";
        var assUrl = paths.join(".");
        fly.get(srtUrl+"?TC_direct").then(function(res){
            //srt存在
            var url = URL.createObjectURL(new Blob([res.data]));
            art.subtitle.switch(url, 'srt', 'srt');
        }).catch(function(){ 
            fly.get(assUrl+"?TC_direct").then(function(res){
                //ass存在
                var assBox = document.createElement("div");
                art.template.$layer.appendChild(assBox);
                assBox.id="assBox"
                art.subtitle.switch('data:text/plain,', 'ass', 'ass');
                art.ass = new ASS(res.data,art.template.$video,{
                    container:document.getElementById("assBox")
                });
                art.on("subtitle:toggle",function(s){
                    assBox.style.display=='none'?assBox.style.display="block":assBox.style.display='none';
                });
                art.on('resize', function(args) {
                    art.ass.resize();
                });
            }).catch(function(){});
        })
        art.on("subtitle:switch",function(){
            document.body.classList.add("hasSub");
        });
        var style = document.createElement('style');
        style.type = 'text/css';
        style.innerHTML = ".art-contextmenu-version::before {content: 'TCShare <?php echo TC_VERSION;?> / artPlayer "+Artplayer.version+"';}";
        document.getElementsByTagName('head')[0].appendChild(style);
        </script>
    </body>
</html>