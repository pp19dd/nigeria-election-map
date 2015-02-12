<?php
#require( "proj4php-master/src/proj4php/proj4php.php" );
#require( "class.kml.php" );

#echo "<PRE>";

require( "class.xy.php" );


$range_file = "states/_grand_range.txt";
if( !file_exists($range_file) ) die( "error: {$range_file} is missing");
$range = unserialize(file_get_contents($range_file));

$file = $_GET['file'] . ".geo";
if( !file_exists($file) ) die( "error: {$file} is missing");
$data = unserialize(file_get_contents($file));

$count = 0;


foreach( $data["coordinates"] as $k1 => $polygons ) {
    foreach( $polygons as $k2 => $point ) {

        $data["coordinates"][$k1][$k2][0] = $point[0] - $range["x"]["min"];
        $data["coordinates"][$k1][$k2][1] = $point[1] - $range["y"]["min"];

        $count++;
    }
}

$out = $_GET['file'] . ".zero";

file_put_contents( $out, serialize($data) );

?>

<?php echo number_format($count) ?> points.
