<?php

include 'models.php';

$sector_type = new SectorType();

#$game = new Game();

function print_strings($string_list) 
{
    foreach ($string_list as $string) 
    {
        echo $string . PHP_EOL;
    }
    echo PHP_EOL;
}
  function initialize_game() 
  {
        include 'strings.php';
        global $game;
        $game->quadrant_x = rand(0, 7);
        $game->quadrant_y = rand(0, 7);
        $game->sector_x = rand(0, 7);
        $game->sector_y = rand(0, 7);
        $game->star_date = rand(0, 50) + 2250;
        $game->energy = 2000;
        $game->photon_torpedoes = 10;
        $game->time_remaining = 125 + rand(0, 20);
        $game->klingons = 30 + rand(0, 15);
        $game->starbases = 3 + rand(0, 3);
        $game->destroyed = false;
        $game->navigation_damage = 0;
        $game->short_range_scan_damage = 0;
        $game->long_range_scan_damage = 0;
        $game->shield_control_damage = 0;
        $game->computer_damage = 0;
        $game->photon_damage = 0;
        $game->phaser_damage = 0;
        $game->shield_level = 0;
        $game->docked = false;
        $game->travel_cost = 3;

        $names = [];
        
        foreach ($quadrantNames as $name)
        {
            array_push($names, $name);
        }

        for ($i = 0; $i < 8; $i++) 
        {
            for ($j = 0; $j < 8; $j++) 
            {
                $index = rand(0, count($names) - 1);
                $quadrant = new Quadrant();
                $quadrant->name = $names[$index];
                $quadrant->stars = 1 + rand(0, 7);
                $game->quadrants[$i][$j] = $quadrant;
                array_splice($names, $index, 1);
            }
        }
        
        $klingon_count = $game->klingons;
        $starbase_count = $game->starbases;
        while ($klingon_count > 0 || $starbase_count > 0) 
        {
            $i = rand(0, 7);
            $j = rand(0, 7);
            $quadrant = $game->quadrants[$i][$j];
            if (!$quadrant->starbase && $starbase_count>0) 
            {
                $quadrant->starbase = true;
                $starbase_count--;
            }
            if ($quadrant->klingons < 3) 
            {
                $quadrant->klingons++;
                $klingon_count--;
            }
        }
        
}
function print_mission() {
    global $game;
    echo "Mission: Destroy {$game->klingons} Klingon ships in {$game->time_remaining} stardates with {$game->starbases} starbases." . PHP_EOL;
    echo PHP_EOL;
}
function generate_sector()
{
    global $game;
    global $sector_type;
    $quadrant = $game->quadrants[$game->quadrant_y][$game->quadrant_x];
    $starbase = $quadrant->starbase;
    $stars = $quadrant->stars;
    $klingons = $quadrant->klingons;
    $quadrant->visited = true;
    $game->klingon_ships = [];

    for($i=0;$i<8;$i++)
    {
        for($j=0;$j<8;$j++)
        {
            $game->sector[$i][$j] = $sector_type->empty;
        }

    }
    $game->sector[$game->sector_y][$game->sector_x] = $sector_type->enterprise;
    while($starbase == true || $stars > 0 || $klingons>0)
    {
        $i = rand(0,7);
        $j = rand(0,7);
        if(is_sector_region_empty($i,$j))
        {
            if($starbase == true)
            {
                $starbase = false;
                $game->sector[$i][$j] = $sector_type->starbase;
                $game->starbase_y = $i;
                $game->starbase_x = $j;
            }
            elseif($stars>0)
            {
                $game->sector[$i][$j] = $sector_type->star;
                $stars-=1;
            }
            elseif($klingons>0)
            {
                $game->sector[$i][$j] = $sector_type->klingon;
                $klingon_ship = new KlingonShip();
                $klingon_ship->shield_level = 300 + rand(0,199);
                $klingon_ship->sector_y = $i;
                $klingon_ship->sector_x = $j;
                array_push($game->klingon_ships,$klingon_ship);
                $klingons-=1;
            }
        }
    }
    $_SESSION['game'] = serialize($game);
}
function is_sector_region_empty($i,$j)
{
    global $sector_type;
    for($y = $i-1; $y<$i+1;$y++)
    {
        if(read_sector($y,$j-1) != $sector_type->empty && read_sector($y,$j+1) != $sector_type->empty)
        {
        return false;
        }
        return read_sector($i,$j) == $sector_type->empty;
    }
}
function read_sector($i,$j)
{
    global $game;
    global $sector_type;
    if($i<0 || $j<0 || $i>7 || $j>7)
    {
        return $sector_type->empty;
    }
    return $game->sector[$i][$j];
}

function print_game_status()
{
    global $game;
    if($game->destroyed == true)
    {
        echo("MISSION FAILED: ENTERPRISE DESTROYED!!!");
        PHP_EOL;
        PHP_EOL;
        PHP_EOL;
    }
    elseif($game->energy == 0)
    {
        echo ("MISSION FAILED: ENTERPRISE RAN OUT OF ENERGY.");
        PHP_EOL;
        PHP_EOL;
        PHP_EOL;
    }
    elseif($game->klingons == 0)
    {
        echo ("MISSION ACCOMPLISHED: ALL KLINGON SHIPS DESTROYED. WELL DONE!!!");
        PHP_EOL;
        PHP_EOL;
        PHP_EOL;
    }
    elseif($game->time_remaining == 0)
    {
        echo ("MISSION FAILED: ENTERPRISE RAN OUT OF TIME.");
        PHP_EOL;
        PHP_EOL;
        PHP_EOL;
    }
}
function short_range_scan($torpedo_shot,$short_navigation)
{
    global $game;
    if($game->short_range_scan_damage>0)
    {
        $_SESSION['logs'] .= "Short range scanner is damaged. Repairs are underway." . PHP_EOL;
    }
    else
    {
        $quadrant = $game->quadrants[$game->quadrant_y][$game->quadrant_x];
        $quadrant->scanned = true;
        print_sector($quadrant,$torpedo_shot,$short_navigation);
    }
}
function print_sector($quadrant,$torpedo_shot,$short_navigation) 
{
    global $game;
    $game->condition = "GREEN";
    if ($quadrant->klingons > 0) 
    {
        $game->condition = "RED";
    } 
    elseif ($game->energy < 300) 
    {
        $game->condition = "YELLOW";
    }
    $g_x = $game->quadrant_x + 1;
    $g_y = $game->quadrant_y + 1;
    $s_x = $game->sector_x + 1;
    $s_y = $game->sector_y + 1;
    $sb = '';
    print_sector_row($sb, 0, " Quadrant: [{$g_x},{$g_y}]",$torpedo_shot,$short_navigation);
    print_sector_row($sb, 1, " Sector: [{$s_x},{$s_y}]",$torpedo_shot,$short_navigation);
    print_sector_row($sb, 2, " Stardate: {$game->star_date}",$torpedo_shot,$short_navigation);
    print_sector_row($sb, 3, " Time remaining: {$game->time_remaining}",$torpedo_shot,$short_navigation);
    print_sector_row($sb, 4, " Condition: {$game->condition}",$torpedo_shot,$short_navigation);
    print_sector_row($sb, 5, " Energy: {$game->energy}",$torpedo_shot,$short_navigation);
    print_sector_row($sb, 6, " Shields: {$game->shield_level}",$torpedo_shot,$short_navigation);
    print_sector_row($sb, 7, " Photon Torpedoes: {$game->photon_torpedoes}",$torpedo_shot,$short_navigation);
    echo Print_numeration(8);

    if ($game->energy < 300) {

        $game->condition = "YELLOW";
    }
}
function print_sector_row($sb, $row, $suffix,$torpedo_shot,$short_navigation) {
    global $game;
    global $sector_type;
    $sb .= "<tr>";
    for ($column = 0; $column < 8; $column++) 
    {
        if($column == 0)
        {
            $column_to_print = $row + 1;
            $sb .= "<td>{$column_to_print}</td>" . PHP_EOL;
        }
        if ($game->sector[$row][$column] == $sector_type->empty) 
        {
            if($torpedo_shot && $game->photon_damage >0)
            {
               $sb .= print_torpedo_directions($row,$column);
            }
            elseif($short_navigation)
            {
                $p_row = $row+1;
                $p_column = $column+1;
                $sb .= "<td><input type='submit' name='short-range-navigation' class='empty' value='{$p_column},{$p_row}' /></td>" . PHP_EOL;
            }
            else
                $sb .= "<td><input type='submit' id={$row},{$column} class='empty' value='' /></td>" . PHP_EOL;
        } 
        elseif ($game->sector[$row][$column] == $sector_type->enterprise) 
        {
            $sb .= "<td><input type='submit' id={$row},{$column} class='enterprise' disabled='disabled' value='&lt;E&gt' /></td>" . PHP_EOL;
        } 
        elseif ($game->sector[$row][$column] == $sector_type->klingon) 
        {
            if($torpedo_shot && $game->photon_damage <= 0)
            {
                $sb .= "<td><input type='submit' name='klingon-{$column}{$row}' value='+K+' /></td>" . PHP_EOL;
            }
            elseif($torpedo_shot && $game->photon_damage > 0 && check_that_klingon_is_beside($row,$column))
            {
                $_SESSION['beside'] = true;
                $sb .= "<td><input type='submit' name='klingon-{$column}{$row}' value='+K+' /></td>" . PHP_EOL;
            }
            else
            {
                $sb .= "<td><input type='submit' name='klingon-{$column}{$row}' disabled='disabled' value='+K+' /></td>" . PHP_EOL;
            }
        } 

        elseif ($game->sector[$row][$column] == $sector_type->star) 
        {
            $sb .= "<td><input type='submit' id={$row},{$column} class='star' disabled='disabled' value='*' /></td>" . PHP_EOL;
        } 
        elseif ($game->sector[$row][$column] == $sector_type->starbase) 
        {
            $sb .= "<td><input type='submit' id={$row},{$column} class='starbase' disabled='disabled' value='>S<' /></td>" . PHP_EOL;
        }
    }
    if ($suffix != null) 
    {
        $sb .= "<td>$suffix</td>";
    }
    echo $sb .= "</tr>" . PHP_EOL;
}
function print_long_range_scan_panel() {
    global $game;
    global $sector_type;
    $sb = "";
    echo "Choose Quadrant";
    for ($row = 0; $row < 8; $row++)
    {
        $sb .= "<tr>";
        for ($column = 0; $column < 8; $column++)
        {
            $p_row = $row+1;
            $p_column = $column+1;
            $quadrant = $game->quadrants[$column][$row];
            if($game->quadrant_x == $row && $game->quadrant_y == $column)
            {
                $sb .= "<td><input type='submit' id={$row},{$column} disabled='disabled' value='({$p_row},{$p_column})'/>E</td>" . PHP_EOL;
            }
            elseif($quadrant->visited && $quadrant->starbase && $quadrant->klingons>0)
            {
                $sb .= "<td><input type='submit' name='long-range-navigation' id={$row},{$column} value='({$p_row},{$p_column})'/>KS</td>" . PHP_EOL;
            }
            elseif($quadrant->visited && $quadrant->starbase)
            {
                $sb .= "<td><input type='submit' name='long-range-navigation' id={$row},{$column} value='({$p_row},{$p_column})'/>S</td>" . PHP_EOL;
            }
            elseif($quadrant->visited && $quadrant->klingons>0)
            {
                $sb .= "<td><input type='submit' name='long-range-navigation' id={$row},{$column} value='({$p_row},{$p_column})'/>K</td>" . PHP_EOL;
            }
            elseif($quadrant->visited == true)
            {
                $sb .= "<td><input type='submit' name='long-range-navigation' id={$row},{$column} value='({$p_row},{$p_column})'/> </td>" . PHP_EOL;
            }
            else
            {
                $sb .= "<td><input type='submit' name='long-range-navigation' id={$row},{$column} value='({$p_row},{$p_column})'/>?</td>" . PHP_EOL;
            }
        }
        $sb .= "</tr>";
    }
    echo $sb .= "</tr>" . PHP_EOL;
        
}
function navigation($subsector_x,$subsector_y)
{
    global $game;
    global $sector_type;
    $subsector_x = (int)$subsector_x;
    $subsector_y = (int)$subsector_y;
    $subsector_x -= 1;
    $subsector_y -= 1;
    $xdiff = $game->sector_x - $subsector_x;
    $ydiff = $game->sector_y - $subsector_y;
    if ($xdiff==0)
    {
        $xdiff = 1;
    }
    if ($ydiff==0)
    {
        $ydiff = 1;
    }
    $energy_required = intval(sqrt($xdiff*$xdiff + $ydiff*$ydiff) * $game->travel_cost);

    if ($energy_required >= $game->energy) {
        $_SESSION['logs'] .= "MISSION FAILED: ENTERPRISE RAN OUT OF ENERGY.";
        $game->energy -= $energy_required;
    }
    elseif($game->time_remaining < 1)
    {
        $_SESSION['logs'] .= "MISSION FAILED: ENTERPRISE RAN OUT OF TIME.";
    }
    else 
    {

        
        $game->energy -= $energy_required;
        
        $game->sector[$game->sector_y ][$game->sector_x] = $sector_type->empty;
        $game->sector[$subsector_y][$subsector_x] = $sector_type->enterprise;
        $game->time_remaining -= 1;
        $game->star_date += 1;
        $game->sector_x  = $subsector_x;
        $game->sector_y = $subsector_y;

    }
    
    $last_quad_x = $game->quadrant_x;
    $last_quad_y = $game->quadrant_y;
    
    if (is_docking_location($game->sector_y, $game->sector_x)) {
        $game->energy = 2000;
        $game->photon_torpedoes = 10;
        $game->navigation_damage = 0;
        $game->short_range_scan_damage = 0;
        $game->long_range_scan_damage = 0;
        $game->shield_control_damage = 0;
        $game->computer_damage = 0;
        $game->photon_damage = 0;
        $game->phaser_damage = 0;
        $game->shield_level = 0;
        $game->docked = true;
    } else {
        $game->docked = false;
    }
    
   if ($last_quad_x != $game->quadrant_x || $last_quad_y != $game->quadrant_y) {
       $game->time_remaining -= 1;
       $game->star_date += 1;
   }
    
    short_range_scan(false,false);
    
    if ($game->docked) 
    {
        $_SESSION['logs'] .= "Enterprise successfully docked with starbase." . PHP_EOL;
    } 
    else 
    {
        if ($game->quadrants[$game->quadrant_y][$game->quadrant_x]->klingons > 0 && $last_quad_x == $game->quadrant_x && $last_quad_y == $game->quadrant_y) 
        {
            klingons_attack();
        } 
        elseif (repair_damage() == false) 
        {
            induce_damage(-1);
        }
    }
    return $game;
    
}
function is_docking_location($i, $j) {
    global $sector_type;
    for ($y = $i - 1; $y <= $i + 1; $y++) {
        for ($x = $j - 1; $x <= $j + 1; $x++) {
            if (read_sector($y, $x) == $sector_type->starbase) {
                return true;
            }
        }
    }
    return false;
}
function klingons_attack() {
    global $game;
    if ($game->quadrants[intval($game->quadrant_y)][intval($game->quadrant_x)]->klingons > 0) {
        for($i=0;$i<$game->quadrants[intval($game->quadrant_y)][intval($game->quadrant_x)]->klingons;$i++)
        {
            if ($game->docked) {
                $_SESSION['logs'] .= "Enterprise hit by ship. No damage due to starbase shields." . PHP_EOL;
            } else {
                $dist = distance($game->sector_x, $game->sector_y, rand(1,7), rand(1,7));
                $delivered_energy = 300 * rand(1, 2) * (1.0 - $dist / 11.3);
                $game->shield_level -= intval($delivered_energy);
                if ($game->shield_level < 0) {
                    $game->shield_level = 0;
                    $game->destroyed = true;
                    $_SESSION['logs'] .= "Enterprise hit by ship. MISSION FAILED: ENTERPRISE DESTROYED!!!" . PHP_EOL;
                    return false;
                }
                $_SESSION['logs']  .= "Enterprise hit by ship. Shields dropped to {$game->shield_level}." . PHP_EOL;
                if ($game->shield_level == 0) {
                    return true;
                }
            }
        }
        return true;
    }
    return false;
}
function distance($x1, $y1, $x2, $y2)
{
    $x = $x2 - $x1;
    $y = $y2 - $y1;
    return sqrt($x * $x + $y * $y);
}
function induce_damage($item) 
{
    global $game;
    
    $damage = 1 + rand(0, 4);
    if ($item < 0) {
        $item = rand(1, 6);
    }

    if ($item == 1) 
    {
        $game->shield_control_damage = $damage;
        $_SESSION['logs']  .= "Shield controls are malfunctioning." . PHP_EOL;
    } elseif ($item == 2) 
    {
        $game->photon_damage = $damage;
        $_SESSION['logs']  .= "Photon torpedo controls are malfunctioning." . PHP_EOL;
    } elseif ($item == 3) 
    {
        $game->phaser_damage = $damage;
        $_SESSION['logs']  .= "Phasers are malfunctioning." . PHP_EOL;
    }

}


function repair_damage() {
    global $game;
    if ($game->shield_control_damage > 0) {
        $game->shield_control_damage -= 1;
        if ($game->shield_control_damage == 0) {
            $_SESSION['game'] .= "Shield controls have been repaired." . PHP_EOL;
        }
        return true;
    }
   
    if ($game->photon_damage > 0) {
        $game->photon_damage -= 1;
        if ($game->photon_damage == 0) {
            $_SESSION['game'] .= "Photon torpedo controls have been repaired." . PHP_EOL;
        }
        return true;
    }

    if ($game->phaser_damage > 0) {
        $game->phaser_damage -= 1;
        if ($game->phaser_damage == 0) {
            $_SESSION['game'] .= "Phasers have been repaired." . PHP_EOL;
        }
        return true;
    }
    return false;
}
function print_numeration($length)
{
    $output = '<tr><td></td>';
    for($i=1;$i<=$length;$i++)
    {
        $output .= "<td>{$i}</td>" ;
    }
    $output .= '</tr>';
    return $output;
}

function print_panel()
{
    include 'strings.php';
    #session_start();
    global $game;
    
    if($game->destroyed || $game->klingons == 0 || $game->energy <= 0 || $game->time_remaining <= 0)
    {
        return $exitStrings;
    }
    if(isset($_POST['shield-control-sbm']) && $game->shield_control_damage <= 0) //click shield control
    {
        return $shieldStrings; //return shield panel
    }
    elseif(isset($_POST['phaser-control-sbm']) && $game->phaser_damage <= 0) 
    {
        return shieldInput($game->energy,'phaser');
    }
    elseif(isset($_POST['back-to-main-panel-sbm']))
    {
        return $commandStrings;
    }
    elseif(isset($_POST['add-shield-sbm'])) //click add energy to shields
    {
        $_SESSION['shield-command-type'] = 'add';
        return shieldInput($game->energy,'shield');
    }
    elseif(isset($_POST['navigation-sbm']))
    {
        return $navigationStrings;
    }
    elseif(isset($_POST['substract-shield-sbm'])) //click substract energy to shields
    {
        $_SESSION['shield-command-type'] = 'sub';
        return shieldInput($game->shield_level,'shield');
    }

    elseif(isset($_POST['shield-go-sbm'])) // click go
    {
        handle_shield_control($_SESSION['shield-command-type']);
        return $commandStrings;
    }
    elseif(isset($_POST['phaser-go-sbm'])) // click go
    {
        phaser_controls();
        return $commandStrings;
    }
    else
    {
        return $commandStrings;
    }
}
function handle_shield_control($command_type)
{
    global $game;
    if($command_type == 'add')
    {
        $value = $game->energy;
    }
    elseif($command_type == 'sub')
    {
        $value = $game->shield_level;
    }
    if($_POST['shield-input'] > $value || $_POST['shield-input'] < 0)
    {
        $error = true;
        $_SESSION['logs']  .= "Invalid amount of energy" . PHP_EOL;
    }
    if(isset($_POST['shield-go-sbm']) && !$error && $command_type == 'add')
    {
        $game->energy -= intval($_POST['shield-input']);
        $game->shield_level += intval($_POST['shield-input']);
    }
    elseif(isset($_POST['shield-go-sbm']) && !$error && $command_type == 'sub')
    {
        $game->energy += intval($_POST['shield-input']);
        $game->shield_level -= intval($_POST['shield-input']);
    }
    $_SESSION['shield-command-type'] = null;
    $_SESSION['game'] = serialize($game);
    
}
function print_torpedo_directions($row, $column)
{
    global $game;
    global $sector_type;
    
    if ($column < 7 && $row < 7 && $game->sector[$row+1][$column+1] == $sector_type->enterprise) 
    {
        return "<td><input type='submit' name='torpedo-direction-top-left' id={$row},{$column} class='torpedo_direction' value='\' /></td>" . PHP_EOL;
    }
    elseif ($row < 7 && $game->sector[$row+1][$column] == $sector_type->enterprise) 
    {
        return "<td><input type='submit' name='torpedo-direction-top' id={$row},{$column} class='torpedo_direction' value='|' /></td>" . PHP_EOL;
    }
    elseif ($row < 7  && $game->sector[$row+1][$column-1] == $sector_type->enterprise) 
    {
        return "<td><input type='submit' name='torpedo-direction-top-right' id={$row},{$column} class='torpedo_direction' value='/' /></td>" . PHP_EOL;
    }
    elseif ($column > 0  && $game->sector[$row][$column-1] == $sector_type->enterprise) 
    {
        return "<td><input type='submit' name='torpedo-direction-right' id={$row},{$column} class='torpedo_direction' value='-' /></td>" . PHP_EOL;
    }
    elseif ($column > 0 && $row > 0  && $game->sector[$row-1][$column-1] == $sector_type->enterprise) 
    {
        return "<td><input type='submit' name='torpedo-direction-bottom-right' id={$row},{$column} class='torpedo_direction' value='\' /></td>" . PHP_EOL;
    }
    elseif ($row > 0  && $game->sector[$row-1][$column] == $sector_type->enterprise) 
    {
        return "<td><input type='submit' name='torpedo-direction-bottom' id={$row},{$column} class='torpedo_direction' value='|' /></td>" . PHP_EOL;
    }
    elseif ($row > 0 && $column < 7  && $game->sector[$row-1][$column+1] == $sector_type->enterprise) 
    {
        return "<td><input type='submit' name='torpedo-direction-bottom-left' id={$row},{$column} class='torpedo_direction' value='/' /></td>" . PHP_EOL;
    }
    elseif ($column < 7  && $game->sector[$row][$column+1] == $sector_type->enterprise) 
    {
        return "<td><input type='submit' name='torpedo-direction-left' id={$row},{$column} class='torpedo_direction' value='-' /></td>" . PHP_EOL;
    }
    else
    {
        return "<td><input type='submit' id={$row},{$column} disabled='disabled' class='torpedo_direction' value='' /></td>" . PHP_EOL;
    }
    
}

function check_that_klingon_is_beside($row,$column)
{
    global $game;
    global $sector_type;
    if (($column < 7 && $row < 7 && $game->sector[$row+1][$column+1] == $sector_type->enterprise) ||
     ($row < 7 && $game->sector[$row+1][$column] == $sector_type->enterprise) ||
     ($row < 7  && $game->sector[$row+1][$column-1] == $sector_type->enterprise) ||
     ($column > 0  && $game->sector[$row][$column-1] == $sector_type->enterprise) ||
     ($column > 0 && $row > 0  && $game->sector[$row-1][$column-1] == $sector_type->enterprise) ||
     ($row > 0  && $game->sector[$row-1][$column] == $sector_type->enterprise) ||
     ($row > 0 && $column < 7  && $game->sector[$row-1][$column+1] == $sector_type->enterprise) ||
    ($column < 7  && $game->sector[$row][$column+1] == $sector_type->enterprise) )
    {
        return true;
    }
    else
    {
        return false;
    }
}
function torpedo_control($direction) {
    global $game;
    global $sector_type;

    /*if ($game->photon_damage > 0) {
        $_SESSION['logs']  .= "Photon torpedo control is damaged. Repairs are underway.\n";
        return;
    }*/
    if ($game->photon_torpedoes == 0) {
        $_SESSION['logs']  .= "Photon torpedoes exhausted.\n";
        return;
    }

    if ($game->quadrants[intval($game->quadrant_y)][intval($game->quadrant_x)]->klingons <= 0) {
        $_SESSION['logs']  .= "There are no Klingon ships in this quadrant.\n";
        return;
    } 

    $_SESSION['logs']  .= "Photon torpedo fired...\n";
    $game->photon_torpedoes -= 1;
    $_SESSION['game'] = serialize($game);
    $angle = -(pi() * ($direction - 1.0) / 4.0);
    
    if (rand(0, 2) == 0) {
        $angle += (1.0 - 2.0 * rand(0.0, 1.0) * pi() * 2.0) * 0.03;
    }

    $x = $game->sector_x;
    $y = $game->sector_y;
    $vx = cos($angle) / 20;
    $vy = sin($angle) / 20;
    $last_x = $last_y = -1;
    $hit = false;

    while ($x >= 0 && $y >= 0 && round($x) < 8 && round($y) < 8) 
    {
        $new_x = intval(round($x));
        $new_y = intval(round($y));

        if ($last_x != $new_x || $last_y != $new_y) {
            $_SESSION['logs']  .= "  [" . ($new_x + 1) . "," . ($new_y + 1) . "]\n";
            $last_x = $new_x;
            $last_y = $new_y;
        }

        foreach ($game->klingon_ships as $ship) {
            if ($ship->sector_x == $new_x && $ship->sector_y == $new_y) {
                $_SESSION['logs']  .= "Klingon ship destroyed at sector [" . ($ship->sector_x + 1) . "," . ($ship->sector_y + 1) . "]" . PHP_EOL;
                $game->sector[$ship->sector_y][$ship->sector_x] = $sector_type->empty;
                $game->klingons -= 1;
                unset($game->klingon_ships[$ship]);
                $game->klingon_ships = array_values($game->klingon_ships);
                $game->quadrants[intval($game->quadrant_y)][intval($game->quadrant_x)]->klingons -= 1;
                $hit = true;
                $_SESSION['game'] = serialize($game);
                if ($game->klingons == 0)
                {
                    $_SESSION['logs'] .= "MISSION ACCOMPLISHED: ALL KLINGON SHIPS DESTROYED. WELL DONE!!!";
                }
                break;
            }
        }
        
        if ($hit) {
            break;
        }
        if ($game->sector[$new_y][$new_x] == $sector_type->starbase) {
            $game->starbases -= 1;
            $game->quadrants[intval($game->quadrant_y)][intval($game->quadrant_x)]->starbase = false;
            $game->sector[$new_y][$new_x] = $sector_type->empty;
            $_SESSION['logs']  .= "The Enterprise destroyed a Federation starbase at sector [" . ($new_x + 1) . "," . ($new_y + 1) . "]!" . PHP_EOL;
            $hit = true;
            $_SESSION['game'] = serialize($game);
            break;
        } elseif ($game->sector[$new_y][$new_x] == $sector_type->star) {
            $_SESSION['logs']  .= "The torpedo was captured by a star's gravitational field at sector [" . ($new_x + 1) . "," . ($new_y + 1) . "]." . PHP_EOL;
            $hit = true;
            break;
        }
        $x += $vx;
        $y += $vy;
    }

    if (!$hit) {
        $_SESSION['logs']  .= "Photon torpedo failed to hit anything." . PHP_EOL;
    }
    if ($game->quadrants[intval($game->quadrant_y)][intval($game->quadrant_x)]->klingons > 0) {
        klingons_attack();
    }
        
    $_SESSION['game'] = serialize($game);
}

function compute_direction($x1, $y1, $x2, $y2)
{
    #compute_direction(game.sector_x, game.sector_y, ship.sector_x, ship.sector_y)))
    if($x1 == $x2)
    {
        if($y1 < $y2)
            $direction = 7;
        else
            $direction = 3;
    }
    elseif ($y1 == $y2)
    {
        if ($x1 < $x2)
            $direction = 1;
        else
            $direction = 5;
    }
    else
    {
        $dy = abs($y2 - $y1);
        $dx = abs($x2 - $x1);
        $angle = atan2($dy, $dx);
        if ($x1 < $x2)
        {
            if ($y1 < $y2)
                $direction = 9.0 - 4.0 * $angle / pi();
            else
                $direction = 1.0 + 4.0 * $angle / pi();
        }
        else
        {
            if ($y1 < $y2)
                $direction = 5.0 + 4.0 * $angle / pi();
            else
                $direction = 5.0 - 4.0 * $angle / pi();
        }
    }              
    return $direction;
}

function shieldInput($max_transfer,$type) 
{
    return [
    "Enter amount of energy (1-{$max_transfer})",   
    '<form method="post">',
    '<input maxlength="4" type="number" name="'. $type .'-input" style="font-size: 2vmin; font-family: monospace;background-color: black; color:#39FF14; border-color: #39FF14""/>',        
    '<input type="submit" id="Ok!" value="Ok!" name="'. $type .'-go-sbm" style="font-size: 2vmin;font-family: monospace;background-color: black; color:#39FF14; border-color: #39FF14" "/>',
    '</form>'
    ];
}   
function phaser_controls() {
    global $game;
    global $sector_type;
    

    if ($game->phaser_damage > 0) {
        $_SESSION['logs'] .= "Phasers are damaged. Repairs are underway." . PHP_EOL;
        return;
    }

    if ($game->quadrants[intval($game->quadrant_y)][intval($game->quadrant_x)]->klingons == 0) {
        $_SESSION['logs'] .= "There are no Klingon ships in this quadrant." . PHP_EOL;
        return;
    }
    
    $phaser_energy = $_POST['phaser-input'];

    if (!$phaser_energy || $phaser_energy < 1 || $phaser_energy > $game->energy) {
        $_SESSION['logs'] .= "Invalid energy level." . PHP_EOL;
        return;
    }

    $destroyed_ships = [];

    $game->energy -= (int)$phaser_energy;
    if($game->energy <=0)
    {
        $_SESSION['logs'] .= "MISSION FAILED: ENTERPRISE RAN OUT OF ENERGY.";
    }

    foreach ($game->klingon_ships as $ship) {

        $dist = distance($game->sector_x, $game->sector_y, $ship->sector_x, $ship->sector_y);
        $delivered_energy = $phaser_energy * (1.0 - $dist / 11.3);
        $ship->shield_level -= (int)$delivered_energy;
        $x = $ship->sector_x+1;
        $y = $ship->sector_y+1;
        if ($ship->shield_level <= 0) {
            $_SESSION['logs'] .= "Klingon ship destroyed at sector [{$x},{$y}]." . PHP_EOL;
            $destroyed_ships[] = $ship;
        } else {
            $_SESSION['logs'] .= "Hit ship at sector [{$x},{$y}]. Klingon shield strength dropped to {$ship->shield_level}." . PHP_EOL;
        }
    }

    foreach ($destroyed_ships as $ship) {
        $game->quadrants[intval($game->quadrant_y)][intval($game->quadrant_x)]->klingons -= 1;
        $game->klingons -= 1;
        $game->sector[$ship->sector_y][$ship->sector_x] = $sector_type->empty;
        $key = array_search($ship, $game->klingon_ships);
        if ($key !== false) {
            array_splice($game->klingon_ships, $key, 1);
        }
    }

    if ($game->quadrants[intval($game->quadrant_y)][intval($game->quadrant_x)]->klingons > 0) {
        klingons_attack();
    }
    short_range_scan(false,false);
    $_SESSION['game'] = serialize($game);
}

?>

