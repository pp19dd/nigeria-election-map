<?php
require( "proj4php-master/src/proj4php/proj4php.php" );
require( "class.kml.php" );
require( "class.xy.php" );
?>

<style>
body, html { margin:0; padding:0 }
</style>

<?php
$data = unserialize(file_get_contents($_GET['file']));


$count = 0;

$temp = new Proj4php();
foreach( $data["coordinates"] as $k1 => $polygons ) {
    foreach( $polygons as $k2 => $point ) {

        $xy = new stdClass();
        $xy->x = $point[0];
        $xy->y = $point[1];

        $temp->transform(
            Proj4php::$WGS84,
            new Proj4phpProj('GOOGLE'),
            $xy
        );

        $data["coordinates"][$k1][$k2] = array(
            $xy->x,
            $xy->y
        );

        $count++;
    }
}

$out = $_GET['file'] . ".geo";

file_put_contents( $out, serialize($data) );

?>

<?php echo number_format($count) ?> points.
