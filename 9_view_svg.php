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

var styles = {
    poly: { fill: 'gray', 'stroke-width': 0.5, stroke: 'silver' },
    over: { fill: 'black' },
    status: { "font-size": 14, "text-anchor": "start" }
}

var range = <?php
    echo json_encode(unserialize(file_get_contents("states/_grand_range.txt")));
?>;

var cities = [
    { name: "Lagos", lat: 6.524379, lng: 3.379206 },
    { name: "Kano", lat: 12.0, lng: 8.516667 },
    { name: "Abuja", lat: 9.066667, lng: 7.483333 }
];

function geocode(input_point) {

    var temp = proj4("WGS84", "GOOGLE", input_point );
    input_point.x = parseFloat(temp.y);
    input_point.y = parseFloat(temp.x);

    // range falls within

    // zero
    input_point.x = input_point.x - range.x.min;
    input_point.y = input_point.y - range.y.min;

    // scale
    var width = 640;
    var height = 520;
    var ratio = range.x.max / range.y.max;
    var sx = width / range.x.max;
    var sy = height / range.y.max;
    input_point.x = input_point.x * sx;
    input_point.y = input_point.y * sy;

    // inversion correction
    input_point.y = height - input_point.y;
    // input_point.y += 100;
    return( input_point );
}

function draw_cities() {
    $.each( cities, function(k, city) {
        var pt = geocode({ x: city.lat, y: city.lng });
        pt.y -= 155 +80;
        paper.circle( pt.x, pt.y, 10 );
        paper.text( pt.x, pt.y, city.name );
        console.info( city );
        console.info( pt );
        console.info( "-------------------------------" );
    });
}

Raphael(function() {
    paper = Raphael("map", 525, 370);
    var status = paper.text(10,10,"status");
    status.attr( styles.status );

    for( var k in map_data )(function(state, path_string) {

        // if( state == "g11" ) return(false);
        // if( state == "water_body" ) return(false);

        map[state] = paper.path(path_string);
        map[state].attr(styles.poly);
        map[state].translate(0, -155);
        map[state].__fill = map[state].attr("fill");

        map[state].mouseover(function() {
            this.stop().animate(styles.over, 300, "<>");
            status.attr("text", state);
            status.show();
        }).mouseout(function() {
            this.stop().animate({ fill: this.__fill }, 300, "<>");
            status.hide();
        });

        $("#counts").append( "<li>" + state + "</li>" );
    })(k, map_data[k]);

    draw_cities();
});
</script>
<style>
body, html { background-color: silver }
#map { background-color: white; width: 525px; height:370px; }
#counts li { width: 150px; float: left; }
</style>
<div id="map"></div>
<ul id="counts"></ul>
