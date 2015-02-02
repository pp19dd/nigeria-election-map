<?php

require( "proj4php-master/src/proj4php/proj4php.php" );
require( "class.kml.php" );
require( "class.xy.php" );

$map = new KML("kml/NGA_adm0.kml");     # data.count = 1
#$map = new KML("kml/NGA_adm1.kml");     # data.count = 38
#$map = new KML("kml/NGA_adm2.kml");     # data.count = 775

$point_collection = array();
$ranger = new XY_ranger();

// ===========================================================================
// step 1: process all points, from all data[], compute range
// ===========================================================================
foreach( $map->data as $poly ) {
    $temp = new XY_points();
    $temp->loadPoints( $poly["coordinates"] ); #pairs of [lat, lng] ?
    $ranger->add( $temp->points );
    $point_collection[] = $temp; # $temp->points;
}

// ===========================================================================
// step 2: lets zero the coordinates out, based on computed range
// ===========================================================================
$range = $ranger->getRange();
echo "<PRE style='color:red'>"; print_r( $range ); echo "</PRE>";
$ranger->reset();
foreach( $point_collection as $points ) {
    $points->zeroPoints( $range );
    $ranger->add( $temp->points );
}
$range = $ranger->getRange();
echo "<PRE style='color:green'>"; print_r( $range ); echo "</PRE>";

// ===========================================================================
// step 3: we intend to resize points to width x height
// our range indicates actual coordinates are
// width: $range["x"]["delta"]    height: $range["x"]["delta"]
// when scaled to either, which one would violate the width*height constraint?
// ===========================================================================
$width = 640;
$height = 480;

$ratio = $range["x"]["delta"] / $range["y"]["delta"];
var_dump($ratio); echo "<br/>";

// at a locked aspect ratio, do these projections
// violate our width, height constraints?
$try_x = ($range["x"]["delta"] * $height) / $range["y"]["delta"];
$try_y = ($range["y"]["delta"] * $width) / $range["x"]["delta"];

echo "{$try_x} * {$try_y}";
