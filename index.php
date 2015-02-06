<?php

function stats($file) {
    $data = unserialize(file_get_contents($file));
    echo count($data["coordinates"]);
}

function geocode($file, $id) {
    $f = $file . ".geo";
    if( file_exists($f) ) {
        echo ".";
    } else {
        echo "<div class='error'><a target='target_" . $id . "' href='2_geocode.php?file=" . $file . "'>Missing</a></div>";
    }
}

function zero($file, $id) {
    $f = $file . ".zero";
    if( file_exists($f) ) {
        echo ".";
    } else {
        echo "<div class='error'><a target='target_" . $id . "' href='3_zero.php?file=" . $file . "'>Missing</a></div>";
    }
}

function scale($file, $id) {
    $f = $file . ".scale";
    if( file_exists($f) ) {
        echo ".";
    } else {
        echo "<div class='error'>Missing</div>";
    }
}

$files = glob("states/*.dat");
?>
<style>
.c2, .c3 { text-align: right }
.error, .error a { background-color: crimson; color: white; padding:4px }
iframe { border: 0; height: 20px; }
</style>

<p><a href="1_kml_to_states.php">Regenerate Files from KML</a></p>

<table>
    <tr>
        <th>Data File</th>
        <th>Filesize</th>
        <th>Polygons</th>
        <th>Geocoding</th>
        <th>Zeroed</th>
        <th>Scaled</th>
    </tr>
<?php foreach( $files as $k => $file ) { ?>
    <tr>
        <td><?php echo $file ?></td>
        <td class='c2'><?php echo number_format(filesize($file)); ?></td>
        <td class='c3'><?php stats($file, $k) ?></td>
        <td class='c4'><?php geocode($file, $k) ?></td>
        <td class='c5'><?php zero($file, $k) ?></td>
        <td class='c6'><?php scale($file, $k) ?></td>
        <td class='f'><iframe name="target_<?php echo $k ?>"></iframe></td>
    </tr>
<?php } ?>
</table>
