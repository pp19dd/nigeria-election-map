<?php

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

/*
note from smekosh, 2014-12-09: dimensions are 1100 x ___,  700 x ___ and 300 x ___
1100 x 739      700 x 470       300 x 202

original: 640 x 430
*/

$width = 640; $height = 430;
$width = 1100; $height = 739;
$width = 700; $height = 470;

if( isset( $_GET['width'] ) ) $width = intval( $_GET['width'] );
if( isset( $_GET['height'] ) ) $height = intval( $_GET['height'] );

require( "class.kml.php" );

//$car = new KML("CAF_adm0.kml");
###########$car = new KML("CAF_adm1.kml");
//$car = new KML("CAF_adm2.kml");

///////////////////
$map = new KML("kml/NGA_adm1.kml");
///////////////////$map = new KML("CAF_adm0.kml");
#$map = new KML("CAF_adm0.kml");
#echo "<PRE>"; echo( $map->json() ); die;


?>
<!doctype html>
<html>
<head>
<title>Central African Republic - General Distribution of Diamond Mines</title>
<meta charset="utf-8" />

<style>
body, html { margin:0; padding:0; }
#map {
    width: <?php echo $width ?>px;
    height: <?php echo $height ?>px;
}
body, html, #map { background-color: #fff8e5;  overflow: hidden !important; }
#status {
    position: absolute;
    background-color: white;
    border:1px solid gray;
    border-radius:5px;
    font-family: Arial;
    font-size: 12px;
    line-height: 1.5em;
    padding:5px;
    display: none;
}
.meta_name { font-weight: bold !important; white-space: nowrap; }
.meta_count { color: gray }
</style>
</head>
<body>

<div id="map"></div>
<div id="status">
    <div id="status_inner"></div>
</div>

<script type="text/javascript">
var data = <?php echo $map->json() ?>;

var P_WIDTH = <?php echo $width ?>;
var P_HEIGHT = <?php echo $height ?>;
var LANGUAGE = "english";
var TEXT = {
    english: {
        diamond_mines: "Diamond mines: ",
        area_seleka: "Seleka\nArea of\nInfluence",
        area_anti_balaka: "Anti-Balaka\nArea of\nInfluence",
        area_lra: "Lord's\nResistance\nArmy\nIncursions"
    },
    french: {
        diamond_mines: "Mines de diamant: ",
        area_seleka: "Zone sous\ncontrôle des\nSeleka",
        area_anti_balaka: "Zone sous\ncontrôle des\nAnti-Balaka",
        area_lra: "Incursions\nde la LRA"
    }
}

if( window.location.href.indexOf("language") != -1 ) LANGUAGE = "french";

function extract_whf_parms(url) {
    if( url.indexOf("width") == -1 ) return(false);
    if( url.indexOf("height") == -1 ) return(false);
    if( url.indexOf("fontsize") == -1 ) return(false);

    var mw = url.match(/width=([0-9]+)/);
    var mh = url.match(/height=([0-9]+)/);
    var ms = url.match(/fontsize=([0-9]+)/);

    var w = parseInt(mw[1]);
    var h = parseInt(mh[1]);
    var s = parseInt(ms[1]);

    return( { w: w, h: h, s: s } );
}

// ?width=400&height=200&fontsize=40
function resize_method_querystring() {
    var parsed = extract_whf_parms(window.location.href);
    return( parsed );
}

// #width=400,height=200,fontsize=40
function resize_method_hash() {
    $(window).bind('hashchange', function(e) {
        var parsed = extract_whf_parms($.param.fragment());
        if( parsed != false ) {
            resize_map( parsed.w, parsed.h, parsed.s );
        }
    });
    $(window).trigger( 'hashchange' );
}

function map_is_finished() {
    // var new_dimensions = resize_method_querystring();
    // resize_map( new_dimensions.w, new_dimensions.h, new_dimensions.s );

    resize_method_hash();
}
</script>

<?php PHP_SCRIPT_FUNCTION("proj4.js"); ?>
<?php PHP_SCRIPT_FUNCTION("class.pt.js"); ?>
<?php PHP_SCRIPT_FUNCTION("raphael-min.js"); ?>
<?php #PHP_SCRIPT_FUNCTION("mining_sites.js"); ?>
<?php PHP_SCRIPT_FUNCTION("rainbowvis.min.js"); ?>
<?php PHP_SCRIPT_FUNCTION("jquery.1.4.2.min.js"); ?>
<?php #PHP_SCRIPT_FUNCTION("class.toolkit.js"); ?>
<?php PHP_SCRIPT_FUNCTION("jquery.ba-bbq.min.js"); ?>

<?php #PHP_SCRIPT_FUNCTION("cooked/data-cooked.js"); ?>
<?php #PHP_SCRIPT_FUNCTION("map.min.js"); ?>
<?php PHP_SCRIPT_FUNCTION("map.js"); ?>

</body>
</html>
