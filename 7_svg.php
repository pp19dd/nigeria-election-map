<?php

require( "functions_svg.php");
?>

<style>
body, html { margin:0; padding:0 }
</style>

<?php
/*
$range_file = "states/_grand_range.txt";
if( !file_exists($range_file) ) die( "error: {$range_file} is missing");
$range = unserialize(file_get_contents($range_file));
*/
$file = $_GET['file'] . ".scale";
if( !file_exists($file) ) die( "error: {$file} is missing");
$data = unserialize(file_get_contents($file));

$temp = basename($file);
$temp2 = pathinfo($temp);
$temp3 = pathinfo($temp2["filename"]);
$basename = $temp3["filename"];

// ===========================================================================
// scaling routine
// ===========================================================================

$SVG = file_get_contents( "_svg_header.txt");
$SVG .= "<!-- COMPOSE BEGIN -->\n";
$SVG .= "<g id=\"{$basename}\">\n";

foreach( $data["coordinates"] as $k1 => $polygons ) {
    $SVG .= "\t<g>\n";
    $SVG .= "\t\t<path d=\"" . polygon_to_path($polygons) . "\"/>\n";
    $SVG .= "\t</g>\n";
}

$SVG .= "</g>\n<!-- COMPOSE END -->\n";
$SVG .= "</svg>";

$out = $_GET['file'] . ".svg";
file_put_contents( $out, $SVG );

echo number_format(filesize($out)) . " bytes";
