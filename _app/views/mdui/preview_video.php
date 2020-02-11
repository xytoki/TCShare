<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Preview of <?php echo $name;?></title>
        <link crossorigin="anonymous" integrity="sha384-WBkDouo/0CCXxPpQ0M6rTUkTGZL30VNhKNg07BZy/8Le4IXY4jv/ihAvI1J9+s4b" href="https://lib.baomitu.com/dplayer/1.25.0/DPlayer.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/pearplayer@latest/dist/pear-player.min.js"></script>
        <script crossorigin="anonymous" integrity="sha384-6CyF/JZDLd9z4fvRApunnpxkvVlYkSK1WPTbA9u3v2e/HgviJP3b9HVX2gD+/1CC" src="https://lib.baomitu.com/dplayer/1.25.0/DPlayer.min.js"></script>
        <style>
            html,body{
                margin:0;
            }
        </style>
    </head>
    <body>
        <div id="dplayer"></div>
        <script>
        var dp = new DPlayer({
            container: document.getElementById('dplayer'),
            video: {
                url: '<?php echo $fileDownloadUrl;?>',
                /*type: 'pearplayer',
                customType: {
                    'pearplayer': function (video, player) {
                        new PearPlayer(video, {
                            src: video.src,
                            autoplay: player.options.autoplay
                        });
                    }
                }*/
            }
        });
        </script>
    </body>
</html>