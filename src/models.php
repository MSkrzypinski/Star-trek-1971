<?php

class Quadrant 
{
    public $name;
    public $klingons;
    public $stars;
    public $starbase;
    public $scanned;
    public $visited;
    
    public function __construct() 
    {
        $this->name = "";
        $this->klingons = 0;
        $this->stars = 0;
        $this->starbase = false;
        $this->scanned = false;
        $this->visited = false;
    }
}

class SectorType 
{
    public $empty = 1;
    public $star = 2;
    public $klingon = 3;
    public $enterprise = 4;
    public $starbase = 5;
}

class KlingonShip 
{
    public $sector_x;
    public $sector_y;
    public $shield_level;
    
    public function __construct() 
    {
        $this->sector_x = 0;
        $this->sector_y = 0;
        $this->shield_level = 0;
    }
}

class Game 
{
    public $star_date;
    public $time_remaining;
    public $energy;
    public $klingons;
    public $starbases;
    public $quadrant_x;
    public $quadrant_y;
    public $sector_x;
    public $sector_y;
    public $shield_level;
    public $navigation_damage;
    public $short_range_scan_damage;
    public $long_range_scan_damage;
    public $shield_control_damage;
    public $computer_damage;
    public $photon_damage;
    public $phaser_damage;
    public $photon_torpedoes;
    public $docked;
    public $destroyed;
    public $starbase_x;
    public $starbase_y;
    public $quadrants;
    public $sector;
    public $klingon_ships;
    public $condition;
    public $travel_cost;

    public function __construct() 
    {
        $this->star_date = 0;
        $this->time_remaining = 0;
        $this->energy = 0;
        $this->klingons = 0;
        $this->starbases = 0;
        $this->quadrant_x = 0;
        $this->quadrant_y = 0;
        $this->sector_x = 0;
        $this->sector_y = 0;
        $this->shield_level = 0;
        $this->navigation_damage = 0;
        $this->short_range_scan_damage = 0;
        $this->long_range_scan_damage = 0;
        $this->shield_control_damage = 0;
        $this->computer_damage = 0;
        $this->photon_damage = 0;
        $this->phaser_damage = 0;
        $this->photon_torpedoes = 0;
        $this->docked = false;
        $this->destroyed = false;
        $this->starbase_x = 0;
        $this->starbase_y = 0;
        $this->travel_cost = 1;
        $this->quadrants = array_fill(0, 8, array_fill(0, 8, new Quadrant()));
        $this->sector = array_fill(0, 8, array_fill(0, 8, new SectorType()));
        $this->klingon_ships = array();
        $this->condition = "Green";
        
    }
}

?>