<!DOCTYPE html>
<html>

<head>

    <style>
        html,
        body {
            font-size: 2vh;
            color: rgb(42, 197, 15);
            margin-left: 2.5%;
            font-family: monospace;
            justify-content: top;
            height: 100%;
        }
        input[type="submit"]{
        
        background: none;
        border: none;
        color: rgb(42, 197, 15);;
        text-decoration: none;
        cursor: pointer;
        }
        #map-table td:not(:last-child),tr:last-child td:last-child
        {
            text-align: center; 
            width: 2em;
            height: 1em;
            vertical-align: middle;
        }
        input[type="submit"]:disabled {
            cursor:default;
        }
    
    </style>

    <title>
        Star Trek Game
    </title>
</head>

<body style="background-color:black;">
    <div id="mapstatus">
        <div id="map2" style="margin-left: 2%;margin-top: 2%;margin-left: 5%; display:inline-block; font-size: 2vmin">
            <div id="map">
            <table id = "map-table">
                <form method="post">
                <?php
                    include 'startrek.php';
                    session_start();
                    global $game;
                    global $sector_type;
                    $sector_type = unserialize($_SESSION['sector_type']);
                    $game = unserialize($_SESSION['game']);
                    
                    if(isset($_POST['photon-torpedo-control-sbm']))
                    {
                        short_range_scan(true,false);
                        if($game->photon_damage <= 0 || $_SESSION['beside'])
                        {
                            $_SESSION['can_fire'] = 'true';
                        }
                    }
                    elseif($_SESSION['can_fire'] == 'true')
                    {
                        foreach($game->klingon_ships as $ship)
                        {
                            if(isset($_POST["klingon-{$ship->sector_x}{$ship->sector_y}"]))
                            {
                                torpedo_control(compute_direction($game->sector_x, $game->sector_y, $ship->sector_x, $ship->sector_y));
                            }
                        }
                        $_SESSION['beside'] = false;
                        $_SESSION['can_fire'] = 'false';
                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['torpedo-direction-top-left']))
                    {
                        #4
                        torpedo_control(4);
                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['torpedo-direction-top']))
                    {
                        #3
                        torpedo_control(3);
                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['torpedo-direction-top-right']))
                    {
                        #2
                        torpedo_control(2);
                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['torpedo-direction-right']))
                    {
                        #1
                        torpedo_control(1);
                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['torpedo-direction-bottom-right']))
                    {
                        #8
                        torpedo_control(8);
                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['torpedo-direction-bottom']))
                    {
                        #7
                        torpedo_control(7);
                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['torpedo-direction-bottom-left']))
                    {
                        #6
                        torpedo_control(6);
                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['torpedo-direction-left']))
                    {
                        #5
                        torpedo_control(5);
                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['short-range-navigation-sbm']))
                    {
                        short_range_scan(false,true);
                    }
                    elseif(isset($_POST['short-range-navigation']))
                    {
                        #echo substr($_POST['short-range-navigation'],0,1);
                        #echo substr($_POST['short-range-navigation'],2,1);
                        $_SESSION['game'] = serialize(navigation(substr(strval($_POST['short-range-navigation']),0,1),substr(strval($_POST['short-range-navigation']),2,1)));
                    }
                    elseif(isset($_POST['long-range-navigation-sbm']))
                    {
                        print_long_range_scan_panel();
                    }
                    elseif(isset($_POST['shield-go-sbm']) || isset($_POST['phaser-go-sbm']))
                    {
                        header("Location:game.php");
                    }
                   
                    elseif(isset($_POST['long-range-navigation']))
                    {
                        $game->quadrant_x = intval(substr(strval($_POST['long-range-navigation']),1,1))-1;
                        $game->quadrant_y = intval(substr(strval($_POST['long-range-navigation']),3,1))-1;
                        $game->quadrants[$game->quadrant_y][$game->quadrant_x];
                        $_SESSION['logs'] = '';
                        if (repair_damage() == false) 
                        {
                            induce_damage(-1);
                        }
                        if($game->time_remaining > 0)
                        {
                            $time = rand(0,3);
                            $game->time_remaining -= $time;
                            $game->star_date += $time;
                        }
                        if ($game->time_remaining <= 0)
                        {
                            $_SESSION['logs'] .= "MISSION FAILED: ENTERPRISE RAN OUT OF TIME.";
                        }
                        $_SESSION['game'] = serialize($game);
                        $game->docked = false;
                        generate_sector();

                        short_range_scan(false,false);
                    }
                    elseif(isset($_POST['exit-sbm']))
                    {
                        header("Location:index.php");
                    }
                    else
                    {
                        short_range_scan(false,false);
                    }
                    //for($i=0;$i<)
                ?>
                </form>
                </table>
            </div>
        </div>
        <div id="status" style="margin-left: 2%;display:inline-block; vertical-align: top; font-size: 2vmin"></div>
    </div>

    <div id="gameOutput" style="font-size: 2vmin;margin-left: 0%; margin-top: 0px;">
        <pre>
        <textarea 
            readonly=true  
            id="gameOutputBox" 
            rows="10"
            style="
            resize: none;
            height: 90%;
            width: 70%;
            padding: 0px;
            font-size: 24px; 
            font-family: monospace;
            background-color: black; color:#39FF14; border-color: #39FF14""> 
            <?php
                echo "Mission: Destroy {$game->klingons} Klingon ships in {$game->time_remaining} stardates with {$game->starbases} starbases." . PHP_EOL;
                echo $_SESSION['logs'];
            ?>
        </textarea>
        </pre>
    </div>


    <div id="bottomOfScreenLeft" style="display:inline-block; vertical-align: top; margin-left: 5%; font-size: 2vmin">

        <div id="inputline">
            
            <div id="inputPrompt" style="font-size: 1.5vmin">
                <?php 
                  
                    $list = print_panel();
                
                    foreach ($list as $string) 
                    {
                        echo ( "<div>" . $string . "</div>" ."</br>");
                    }
                    
                ?>
            </div>
           
        </div>
    </div> <!-- bottom left -->


<div id="bottomOfScreenRight" style="display:inline-block; vertical-align: left; margin-left: 5%; font-size: 1.5vmin">
    <pre>
        KEY:
        <span>&lt;E&gt;</span> : ENTERPRISE
         *  : STAR
        +K+ : KLINGON
        >S< : STARBASE  
    </pre>
</div>


</body>

</html>
