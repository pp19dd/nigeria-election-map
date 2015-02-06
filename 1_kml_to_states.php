<?php

require( "proj4php-master/src/proj4php/proj4php.php" );
require( "class.kml.php" );
require( "class.xy.php" );

if( !isset( $_GET['view']) ) {
    $map = new KML("kml/NGA_adm1.kml");     # data.count = 1

    foreach( $map->data as $k => $poly ) {
        $filename = sprintf(
            "states/%s.dat",
            strtolower(str_replace(" ", "_", $poly["name"]))
        );

        file_put_contents( $filename, serialize($poly) );

        printf( "wrote %s, %s bytes (<a href=\"?view=%s\">View</a>)<br/>\n",
            $filename,
            filesize($filename),
            $filename
        );
    }
} else {
    $data = unserialize(file_get_contents($_GET['view']));

    printf(
        "file: %s<br/>\npolygon count:%s<br/><pre>#\tcount\n",
        $_GET['view'],
        count($data["coordinates"])
    );

    foreach( $data["coordinates"] as $k => $polygons ) {
        printf(
            "%s\t%s\n",
            $k,
            count($polygons)
        );
    }

    #echo "<PRE>";
    #print_r( $data );
}
