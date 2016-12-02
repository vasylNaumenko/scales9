<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>9 balls and scales</title>
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/main.css">
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/main.js"></script>

</head>
<body>
<header>
    <div class="logo"></div>
    <div class="headline">Welcome to the 9 balls game</div>
    <div>
        <span class="button" data-role="autoplay">Auto play</span>
        <span class="button" data-role="autoreplay">Auto replay</span>
    </div>
</header>
<div class="playzone-wrapper">
    <div class="card" data-role="init">
        <div class="header">
            Choose the heaviest ball
        </div>
        <div class="content"></div>
    </div>

    <div id="scales"></div>

    <div class="controls">
        <span id="play_again" class="button">Play again</span>
        <div id="autorestart" style="display: none;" data-role="autorestart"><span class="content"></span></div>
    </div>
</div>

<div class="replays-wrapper">
    <div class="card" data-role="replays">
        <div class="header">
            Replays
        </div>
        <div class="content"></div>
    </div>
</div>


</body>
</html>