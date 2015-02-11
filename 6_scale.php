<?php
require( "class.xy.php" );
?>

<style>
body, html { margin:0; padding:0 }
</style>

<?php

$range_file = "states/_grand_range.txt";
if( !file_exists($range_file) ) die( "error: {$range_file} is missing");
$range = unserialize(file_get_contents($range_file));

$file = $_GET['file'] . ".geo";
if( !file_exists($file) ) die( "error: {$file} is missing");
$data = unserialize(file_get_contents($file));

// ===========================================================================
// scaling routine
// ===========================================================================

$width = 640;
$height = 520;
$ratio = $range["x"]["max"] / $range["y"]["max"];

$sx = $width / $range["x"]["max"];
$sy = $height / $range["y"]["max"];

$count = 0;


foreach( $data["coordinates"] as $k1 => $polygons ) {
    foreach( $polygons as $k2 => $point ) {

        $x = $point[0] - $range["x"]["min"];
        $y = $point[1] - $range["y"]["min"];

        // no, should be using the delta value
        #$x = $point[0] - $range["x"]["delta"];
        #$y = $point[1] - $range["y"]["delta"];

        $x = $x * $sx;
        $y = $y * $sy;

        $data["coordinates"][$k1][$k2][0] = $x;
        $data["coordinates"][$k1][$k2][1] = $height - $y;

        $count++;
    }
}

$out = $_GET['file'] . ".scale";

file_put_contents( $out, serialize($data) );

?>

<?php echo number_format($count) ?> points.
