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
<script type="text/javascript" src="raphael-min.js"></script>
<script type="text/javascript">
var map_data = <?php echo json_encode($svg); ?>;
var paper;
var map = { };

var styles = {
    poly: { fill: 'gray', 'stroke-width': 0.5, stroke: 'black' },
    over: { fill: 'black' },
    status: { "font-size": 14, "text-anchor": "start" }
}

Raphael(function() {
    paper = Raphael("map", 640, 520);
    var status = paper.text(10,10,"status");
    status.attr( styles.status );

    for( var k in map_data )(function(state, path_string) {
        map[state] = paper.path(path_string);
        map[state].attr(styles.poly);
        map[state].__fill = map[state].attr("fill");

        map[state].mouseover(function() {
            this.stop().animate(styles.over, 300, "<>");
            status.attr("text", state);
            status.show();
        }).mouseout(function() {
            this.stop().animate({ fill: this.__fill }, 300, "<>");
            status.hide();
        });

    })(k, map_data[k]);
});
</script>
<style>
#map { width: 640px; height:520px }
</style>
<div id="map"></div>
