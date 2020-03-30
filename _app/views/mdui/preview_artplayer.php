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
            .assMobile {
                position: absolute !important;
                top: 0;
                left: 0;
                z-index: 21;
                pointer-events: none;
            }
        </style>
    </head>
    <body>
        <div id="player"></div>
        <script src="https://cdn.jsdelivr.net/npm/assjs/dist/ass.js"></script>
        <script>
            async function any(pms){
                for(let i of pms){
                    try{
                        let res = await i();
                        return res;
                    }catch(e){}
                }
                throw new Error();
            }
        </script>
        <script>
        var _NAME = "<?php echo $file->name();?>";
        var _EXT  = "<?php echo $file->extension();?>";
        var _CONF = "${dir}${name}.ass;${dir}${name}.srt<?php echo isset($_ENV['XS_SUBTITLE_FIND'])?";".$_ENV['XS_SUBTITLE_FIND']:""?>";
    
        function render(template, data) {
            return template.replace(/\${(.+?)}/g, ($1, $2) => {
                const key = $2.trim();
                if (data.hasOwnProperty(key)) {
                    return data[key];
                }
                return $1;
            })
        }
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
            //whitelist: [(ua)=>{ return true; }]
        });
        var paths = decodeURIComponent(location.href.replace(location.search,""));
        var _DIR = paths.replace(_NAME,"");
        var _NNAME = _NAME.split(".");
        _NNAME.pop();
        _NNAME = _NNAME.join(".");
        var pmList=[];
        var subList=_CONF.split(";");
        for(let i of subList){
            if(!i)continue;
            (function(i){
                pmList.push(function(){
                    let url = render(i,{dir:_DIR,name:_NNAME});
                    return fly.request({
                        url:url+"?TC_direct",
                        type:url.indexOf("srt")>-1?"srt":"ass",
                    })
                });
            })(i)
        }
        any(pmList).then(loadSub);
        function loadSub(data){
            var type = data.request.type;
            var content = data.data;
            if(type=='ass'){
                //ass存在
                let assContain = art.template.$layer||art.template.$container
                var assBox = document.createElement("div");
                assContain.appendChild(assBox);
                assBox.id="assBox"
                if(art.template.$layer){
                    art.subtitle.switch('data:text/plain,', 'ass', 'ass');
                }else{
                    assBox.className="assMobile"
                }
                art.ass = new ASS(content,art.template.$video,{
                    container:document.getElementById("assBox")
                });
                art.template.$layer&&art.ass.resize();
                art.on("subtitle:toggle",function(s){
                    assBox.style.display=='none'?assBox.style.display="block":assBox.style.display='none';
                });
                art.on('resize', function(args) {
                    art.ass.resize();
                });
            }else{
                var url = URL.createObjectURL(new Blob([data.data]));
                art.subtitle && art.subtitle.switch(url,type,type);
            }
        }
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