<?php

if( !isset( $_GET['mode'] ) ) $_GET['mode'] = '';

switch( $_GET['mode'] ) {
    case 'write':
        file_put_contents( "cooked/data-original.js", $_POST['data'] );
        die;
    break;

    
}
    

$data = json_decode(file_get_contents("cooked/data-original.js"));

#echo "<PRE>";print_r( $data ); die;

function rounder($p) {
    return( sprintf("%.1f", $p) );
}

function coordinates($x) {
    $c = array();
    $f = array();
    
    foreach( $x as $p ) {
        $c[] = array(rounder($p[2]->x), rounder($p[2]->y));
        $f[] = rounder($p[2]->x);
        $f[] = rounder($p[2]->y);
    }
    
    #return( $f );
    return( $c );
}

$shorter = array();
foreach( $data as $prefecture ) {
    
    $shorter[] = (object)array(
        "name" => $prefecture->name,
        "description" => $prefecture->description,
        "coordinates" => coordinates($prefecture->coordinates)
    );
    
}    

file_put_contents( "cooked/data-cooked.js", json_encode($shorter) );

echo "<PRE>";print_r( $shorter ); die;
