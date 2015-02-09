<?php

function stats($file) {
    $data = unserialize(file_get_contents($file));
    echo count($data["coordinates"]);
}

function action($file, $ext, $id, $href) {
    $f = $file . $ext;
    if( file_exists($f) ) {
        echo "<div class='ok'>ok</div>";
    } else {
?>
<div class='error'>
    <a
        target='target_<?php echo $id ?>'
        href='<?php echo $href ?>?file=<?php echo $file ?>'
    >
        Missing
    </a>
</div>
<?php
    }
}

$files = glob("states/*.dat");
?>
<style>
.c2, .c3 { text-align: right }
.error, .error a, .ok { background-color: crimson; color: white; padding:4px }
.ok { background-color: limegreen; color: white; }
.nw { white-space: nowrap }
.sp { width: 80px !important; }
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

<p><div class="number">1</div> <a href="1_kml_to_states.php">Regenerate Files from KML</a></p>
<p><div class="number">4</div> <a href="4_grand_range.php">Compute grand Range from *.range</a></p>
<p><div class="number">8</div> <a href="8_generate_combined_svg.php">Generate combined SVG file</a></p>

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
        <th style="width:50%">Status</th>
    </tr>
<?php foreach( $files as $k => $file ) { ?>
    <tr>
        <td><?php echo $file ?></td>
        <td class='c2'><?php echo number_format(filesize($file)); ?></td>
        <td class='c3'><?php stats($file, $k) ?></td>
        <td class='c4'><?php action($file, ".geo", $k, "2_geocode.php") ?></td>
        <td class='c5'><?php action($file, ".range", $k, "3_range.php") ?></td>
        <td class='c6'><?php action($file, ".zero", $k, "5_zero.php") ?></td>
        <td class='c7'><?php action($file, ".scale", $k, "6_scale.php") ?></td>
        <td class='c7'><?php action($file, ".svg", $k, "7_svg.php") ?></td>
        <td class='f'><iframe name="target_<?php echo $k ?>"></iframe></td>
    </tr>
<?php } ?>
</table>
