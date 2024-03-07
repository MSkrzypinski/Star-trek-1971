<!DOCTYPE html>
<html>
    <head>
        <style>
            html,body 
            {
                font-size: 2vh;
                color:rgb(42, 197, 15);
                margin-left: 2.5%;
                font-family: monospace;
                justify-content: top;
                height: 100%;
            }
            input[type=submit] 
            {
                color: rgb(0,0,0);
                background-color: rgb(42, 197, 15);
                font-size: 19px;
                border: 0px solid #1c5e5f;
                padding: 15px 50px;
                cursor: pointer
		    }
            
        </style>

        <title>
            Star Trek Game
        </title>
    </head>
<body style="background-color:black;">

<div class="container" style="margin-left:25%;width: 50%;text-align:center;">
    <pre style="text-align: left;">
            <?php
                include 'strings.php';
                foreach ($titleStrings as $string) 
                {
                    echo $string . "</br>";
                }
                echo "</br>";
            ?>
    </pre>
</div>                                           

<div class="containerA" id="newgameprompt" style="text-align: center;margin-left: 0;">
    <pre style="display: inline-block;text-align: left;margin-right:5%">
    <form method="post">
        <input type="submit" font-family="monospace" name="btn-new-game" value="New Game">
    </form>
        <?php

            if (isset($_POST['btn-new-game'])) 
            {
                session_start();
                session_destroy();
                session_start();
                include 'startrek.php';
                global $game;
                global $sector_type;
                initialize_game();
                generate_sector();
                #$_SESSION['logs'] .= "Mission: Destroy {$game->klingons} Klingon ships in {$game->time_remaining} stardates with {$game->starbases} starbases." . PHP_EOL;
                $_SESSION['game'] = serialize($game);
                $_SESSION['panel'] = 'commandStrings';
                $_SESSION['sector_type'] = serialize($sector_type);
                
                header("Location:game.php");
            }
        ?>
    </pre>
</div>    

</body>
</html>