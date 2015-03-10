<?php
require( "../class.svg.php" );

$svg_file = new SVG("../election_map.svg");
$svg = $svg_file->info();

$lang = "en";
if( isset( $_GET['lang']) ) $lang = $_GET['lang'];

$year = 2015;
if( isset( $_GET['year']) ) $year = intval($_GET['year']);

// information is mostly constant between 2011 - 2015
$ca = array(
    "symbol" => "CPC",
    "state_count" => 0,
    "vote_count" => 0,
    "fname" => "Muhammadu",
    "lname" => "Buhari",
    "image" => "buhari.png",
    "fill" => "#1b95ff"
);

$cb = array(
    "symbol" => "PDP",
    "state_count" => 0,
    "vote_count" => 0,
    "fname" => "Goodluck",
    "lname" => "Jonathan",
    "image" => "goodluck.png",
    "fill" => "#ff851b"
);

// but, some differences...
switch( $year ) {
    case 2011:
        $key = "14UU6pYCxSmZ2Z_cS7Ch8c7KSmueptSnGHoHcmcRZlJI";
        $party_a = $ca;
        $party_b = $cb;
        break;

    default: // 2015...
        $key = "1OkwSPJ-XOiBRvJ1oJGs8dLW0tBKRucUO4eRG43uoD5k";
        $party_a = $ca;
        $party_b = $cb;
        $party_a["symbol"] = "APC";
        break;
}
