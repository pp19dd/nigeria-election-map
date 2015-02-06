<?php

set_time_limit( 900 );

define( "DEBUG_POLY_LIMIT", 3);

function pre($a, $die = true) {
    echo "<PRE style='padding:1em; background-color: silver'>";
    print_r( $a );
    echo "</PRE>";
    if( $die ) die;
}

function PHP_SCRIPT_FUNCTION( $file ) {
    if( !isset( $_GET['inline'] ) ) {
        printf(
            "<script type=\"text/javascript\" src=\"%s?rand=%s\"></script>\n",
            $file,
            rand()
        );
        return;
    }

    printf( "\n<script type=\"text/javascript\">\n" );
    readfile( $file );
    printf( "\n</script>\n" );
}

require( "proj4php-master/src/proj4php/proj4php.php" );
require( "class.kml.php" );
require( "class.xy.php" );

$map = new KML("kml/NGA_adm1.kml");     # data.count = 1
#$map = new KML("kml/NGA_adm1.kml");     # data.count = 38
#$map = new KML("kml/NGA_adm2.kml");     # data.count = 775

$point_collection = array();
$ranger = new XY_ranger();

// ===========================================================================
// step 1: process all points, from all data[], compute range
// ===========================================================================
foreach( $map->data as $k => $poly ) {
    if( $k > DEBUG_POLY_LIMIT) continue;

    $point_collection[$k] = array();

    // for coordinate system conversion
    $temp = new XY_points();

    #pre( $poly );
    foreach( $poly["coordinates"] as $groups ) {
        foreach( $groups as $group ) {
            $temp->loadPoints( $group ); #pairs of [lat, lng] ?
            $ranger->add( $temp->points );
            $point_collection[$k][] = $temp; # $temp->points;
        }
    }
}

echo "done";
die;
#pre( $point_collection );
// ===========================================================================
// step 2: lets zero the coordinates out, based on computed range
// ===========================================================================
$range = $ranger->getRange();
#pre($range, false);
#echo "Before zeroing: <PRE style='color:red'>"; print_r( $range ); echo "</PRE>";

$ranger->reset();
foreach( $point_collection as $k => $groups ) {
    if( $k > DEBUG_POLY_LIMIT) continue;

    foreach( $groups as $group_k => $group ) {
        $point_collection[$k][$group_k]->zeroPoints($range);# = $group->zeroPoints($range);
        $ranger->add( $group->points );
    }
}
$range = $ranger->getRange();
#pre($range, false);

#echo "After zeroing:<PRE style='color:green'>"; print_r( $range ); echo "</PRE>";

// ===========================================================================
// step 3: we intend to resize points to width x height
// our range indicates actual coordinates are
// width: $range["x"]["delta"]    height: $range["x"]["delta"]
// when scaled to either, which one would violate the width*height constraint?
// ===========================================================================
$width = 640;
$height = 480;
$ratio = $range["x"]["max"] / $range["y"]["max"];

###var_dump( $ratio ); die;
$sx = $width / $range["x"]["max"];
$sy = $height / $range["y"]["max"];




$ranger->reset();
$simple_coordinates = array();
foreach( $point_collection as $k => $points ) {
    if( $k > DEBUG_POLY_LIMIT) continue;

    foreach( $points as $group ) {
        $group->scaleTo($sx, $sy);
        $simple_coordinates[$k] = $group->simpleCoordinates($height);
        $ranger->add( $group->points );
    }
    #print_r( $points->points );
    ######$points->scaleTo($sx, $sy);
    #print_r( $points->points );
    #die;
    ###$simple_coordinates[] = $points->simpleCoordinates($height);
    #$ranger->add( $points->points );
}
$range = $ranger->getRange();
#echo "After scaling: <PRE style='color:orange'>"; print_r( $range ); echo "</PRE>";
#die;


#echo "<PRE>";print_r($simple_coordinates); echo "</PRE>";
#echo json_encode($simple_coordinates);
#$ratio = $range["x"]["delta"] / $range["y"]["delta"];
#var_dump($ratio); echo "<br/>";

// at a locked aspect ratio, do these projections
// violate our width, height constraints?
#$try_x = ($range["x"]["delta"] * $height) / $range["y"]["delta"];
#$try_y = ($range["y"]["delta"] * $width) / $range["x"]["delta"];

#echo "{$try_x} * {$try_y}";

$svg = file_get_contents("_svg_header.txt");

#echo "<table border='1'>";
foreach( $simple_coordinates as $k => $coords ) {

    $path = "m ";
    /*printf(
        "<tr><td>%s</td><td>%s</td></tr>",
        $k,
        count($coords)
    );*/

    $path .= implode(" L", $coords);

    $id = 2000 + $k;

    $svg .= sprintf(
"<path
    style=\"fill:none;fill-rule:evenodd;stroke:#000000;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1\"
    d=\"%s\"
    id=\"path%s\" />\n",
        $path,
        $id
    );

}
$svg .= "</g></svg>";

#echo "</table>";
###########echo "<PRE>";echo htmlentities($svg); die;
?>
<!doctype html>
<html>
<head>
    <title>Nigeria Election Map</title>

<?php PHP_SCRIPT_FUNCTION("raphael-min.js"); ?>
<?php PHP_SCRIPT_FUNCTION("rainbowvis.min.js"); ?>
<?php PHP_SCRIPT_FUNCTION("jquery.1.4.2.min.js"); ?>
<?php PHP_SCRIPT_FUNCTION("jquery.ba-bbq.min.js"); ?>
<script type="text/javascript">
var d_paper;
var map = [];
var map_coords;

function draw_poly(coords) {
    var path = "M" + coords[0];
    for( i = 1; i < coords.length; i++) {
        path += " L" + coords[i];
    }
    var polygon = d_paper.path( path );
    return( polygon );
}

Raphael(function() {
    d_paper = Raphael("map", 800, 600);
    map_coords = <?php echo json_encode($simple_coordinates); ?>

    $.each(map_coords, function(k,v) {
        map.push( draw_poly(v) );
    });
});

</script>
</head>
<body>
    <div id="map"></div>
</body>
</html>
