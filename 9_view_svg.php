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
    poly: { fill: 'silver', 'stroke-width': 0.5, stroke: 'black' }
}

Raphael(function() {
    paper = Raphael("map", 800, 600);
    for( var k in map_data )(function(state, paths) {
        // console.info( state, " = ", paths.length );
        var group = paper.set();

        for( var path in paths ) {
            group.push( paper.path( paths[path] ).attr(styles.poly) );
        }

        group.mouseover(function() {
            this.stop().animate({ fill: 'red' }, 300, "<>");
        }).mouseout(function() {
            this.stop().animate({ fill: 'silver' }, 300, "<>");
        });
        map[state] = group;

    })(k, map_data[k]);
});
</script>
<style>
#map { width: 800px; height:600px }
</style>

<div id="map"></div>
