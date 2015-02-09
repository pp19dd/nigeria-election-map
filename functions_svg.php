<?php

// takes array of absolute coordinates, returns a path (d="") property
function polygon_to_path($polygon) {

    $first = array_shift($polygon);

    $path = array(
        sprintf( "M%.1f,%.1f", $first[0], $first[1] )
    );

    foreach( $polygon as $c) {
        $path[] = sprintf( "L%.1f,%.1f", $c[0], $c[1] );
    }

    return( implode(" ", $path ));

}
