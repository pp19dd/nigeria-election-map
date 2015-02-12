<?php

function stats($file) {
    $data = unserialize(file_get_contents($file));
    echo count($data["coordinates"]);
}

function action($file, $ext, $id, $href) {
    $f = $file . $ext;
    $class = "error"; $label = "Missing";
    if( file_exists($f) ) {
        $class = "ok";
        $label = "ok";
    }

?>
<div class='button <?php echo $class ?>'
    onclick='action(this, "status_<?php echo $id ?>", "<?php echo $href ?>?file=<?php echo $file ?>");'
><?php echo $label ?></div>
<?php
}

$files = glob("states/*.dat");
?>
<script src="jquery.min.js"></script>
<script>
function action(that, id, href) {
    $(that).fadeOut();

    $.ajax({
        url: href,
        dataType: "html",
        success: function(data) {
            $("#" + id).html( data );
            $(that).fadeIn();
        }
    });
}
</script>
<style>
.button { cursor: pointer }
.c2, .c3 { text-align: right }
.error, .error a, .ok { background-color: crimson; color: white; padding:4px }
.ok { background-color: limegreen; color: white; }
.nw { white-space: nowrap }
.sp { width: 150px !important; }
td.act { width: 150px !important; }
div.number {
    background-color: limegreen;
    color: white;
    float: left;
    width: 20px;
    border:1px solid white;
    border-radius:20px;
    text-align: center;
}
iframe { width: 100%; border: 0; height: 20px; }
table { width: 100% }
</style>

<p>
    <div style="float:left"><div class="number">1</div> <a href="1_kml_to_states.php">Regenerate Files from KML</a></div>
    <div class="number">1b</div> <a href="1b_cities.php">Generate cities as KML</a>
</p>
<p><div class="number">4</div> <a href="4_grand_range.php">Compute grand Range from *.range</a></p>
<p><div class="number">8</div> <a href="8_generate_combined_svg.php">Generate combined SVG file</a></p>
<p><div class="number">9</div> <a href="9_view_svg.php">View SVG as RaphaelJS</a></p>

<table>
    <tr>
        <th>Data File</th>
        <th>Filesize</th>
        <th>Polygon</th>
        <th class="nw sp"><div class="number">2</div>Geocode</th>
        <th class="nw sp"><div class="number">3</div>Range</th>
        <th class="nw sp"><div class="number">5</div>Zero</th>
        <th class="nw sp"><div class="number">6</div>Scale</th>
        <th class="nw sp"><div class="number">7</div>SVG</th>
        <th class="nw sp"></th>
        <th style="width:40%">Status</th>
    </tr>
<?php foreach( $files as $k => $file ) { ?>
    <tr>
        <td><?php echo $file ?></td>
        <td class='c2'><?php echo number_format(filesize($file)); ?></td>
        <td class='c3'><?php stats($file, $k) ?></td>
        <td class='c4 act'><?php action($file, ".geo", $k, "2_geocode.php") ?></td>
        <td class='c5 act'><?php action($file, ".range", $k, "3_range.php") ?></td>
        <td class='c6 act'><?php action($file, ".zero", $k, "5_zero.php") ?></td>
        <td class='c7 act'><?php action($file, ".scale", $k, "6_scale.php") ?></td>
        <td class='c8 act'><?php action($file, ".svg", $k, "7_svg.php") ?></td>
        <td class='c9 act'><a href="9_view_svg.php?file=<?php echo $file ?>.svg">View</a></td>
        <td class='f'><div id="status_<?php echo $k ?>"></div></td>
    </tr>
<?php } ?>
</table>
