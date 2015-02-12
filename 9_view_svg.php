<?php

if( !isset( $_GET['file']) ) {

    $svg = glob("states/*.svg");
?>
<table>
<?php
    foreach( $svg as $file ) {

?>
<tr>
    <td><?php echo $file ?></td>
    <td><?php echo number_format(filesize($file)) ?></td>
    <td><a href="?file=<?php echo $file ?>">View</a></td>
</tr>
<?php

    }

    die;
}

require( "class.svg.php" );

$svg_file = new SVG($_GET['file']);
$svg = $svg_file->info();
?>
<script type="text/javascript" src="jquery.min.js"></script>
<script type="text/javascript" src="raphael-min.js"></script>
<script type="text/javascript" src="proj4.js"></script>
<script type="text/javascript">
var map_data = <?php echo json_encode($svg); ?>;
var paper;
var map = { };
var TRANSLATE = { x: 0, y: -155 };
var styles = {
    poly: { fill: 'gray', 'stroke-width': 0.5, stroke: 'silver' },
    water_body: { fill: 'silver', 'stroke-width': 0.5, stroke: 'silver' },
    over: { opacity: 0.5, stroke: 'gray' },
    out: { opacity: 1, stroke: 'silver' },
    status: { "font-size": 14, "text-anchor": "start" },
    city: {
        dot: { stroke: "orange" },
        label: { "text-anchor": "start" }
    }
}

var range = <?php
    echo json_encode(unserialize(file_get_contents("states/_grand_range.txt")));
?>;

<?php
define( "BORROWING_DATA", true );
include( "1b_cities.php" );
?>

var cities = <?php echo json_encode($city_data); ?>;

function draw_cities() {
    $.each( cities, function(k, city) {

        if( city.name != "Lagos") return(false);
        // var pt = geocode({ x: city.lat, y: city.lng });
        // pt.y -= 155 +80;
        /// city.y = 370 - city.y;

        // city.x += TRANSLATE.x;
        // city.y += TRANSLATE.y;

        paper.circle( city.x, city.y, 10 ).translate(TRANSLATE.x, TRANSLATE.y);
        paper
            .text( city.x, city.y, city.name )
            .translate(TRANSLATE.x + 15, TRANSLATE.y)
            .attr(styles.city.label);
    });
}

// two passes: first cosmetic, second event triggering
function draw_states(options, map_data, status) {
    for( var k in map_data )(function(state, path_string) {

        /*if( options.trigger == false && state == "abia" ) {
            console.info( state );
            //return( false );
        }
        if( state == "g11" ) state = "abia";
        */
        if( state == "g6" ) return(false);  // city geocode simulation

        if( state == "water_body" ) {
            map[state] = paper.path(path_string);
            map[state].attr(styles.water_body);
            map[state].translate(TRANSLATE.x, TRANSLATE.y);

            return(false);
        }

        // if( state == "g11" ) return(false);
        // if( state == "water_body" ) return(false);

        if( options.trigger == false ) {

            map[state] = paper.path(path_string);
            map[state].attr(styles.poly);
            map[state].translate(TRANSLATE.x, TRANSLATE.y);
            map[state].__fill = map[state].attr("fill");

            $("#counts").append( "<li>" + state + "</li>" );

        } else {

            map[state].__trigger = paper.path(path_string);
            map[state].__trigger.translate(TRANSLATE.x, TRANSLATE.y);
            map[state].__trigger.attr( { fill: "white", "stroke-width": 0, opacity: 0 } );

            map[state].__trigger.mouseover(function() {
                map[state].stop().animate(styles.over, 300, "<>");
                status.attr("text", state);
                status.show();
            }).mouseout(function() {
                // map[state].stop().animate({ fill: map[state].__fill }, 300, "<>");
                map[state].stop().animate(styles.out, 300, "<>");
                status.hide();
            });

        }

    })(k, map_data[k]);
}

Raphael(function() {
    paper = Raphael("map", 525, 370);
    var status = paper.text(10,10,"status");
    status.attr( styles.status );
    status.hide();

    draw_states({ trigger: false }, map_data, status);
    draw_cities();
    draw_states({ trigger: true }, map_data, status);

});
</script>
<style>
body, html { background-color: silver }
#map { background-color: white; width: 525px; height:370px; }
#counts li { width: 150px; float: left; }
</style>
<div id="map"></div>
<ul id="counts"></ul>
