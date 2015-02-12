<?php
#require( "proj4php-master/src/proj4php/proj4php.php" );
#require( "class.kml.php" );
require( "class.xy.php" );


$file = $_GET['file'] . ".geo";
if( !file_exists($file) ) die( "error: {$file} is missing");

$geo = unserialize(file_get_contents($file));

######echo "<PRE>";print_r( $geo );die;

$ranger = new XY_ranger();

$count = 0;

foreach( $geo["coordinates"] as $polygons ) {
    foreach( $polygons as $polygon ) {
        $xy = new stdClass();
        $xy->x = $polygon[0];
        $xy->y = $polygon[1];

        $ranger->addSingle($xy);
    }
}

$range = $ranger->getRange();

$out = $_GET['file'] . ".range";

file_put_contents( $out, serialize($range) );

printf(
    "X: [%.2f - %.2f], Y: [%.2f - %.2f]",
    $range["x"]["min"],
    $range["x"]["max"],
    $range["y"]["min"],
    $range["y"]["max"]
);
