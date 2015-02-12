<?php
require_once( "class.svg.php" );


$city_data = array(
    array( "name" => "Lagos",         "lat" => 6.524379,  "lng" => 3.379206 ),
    array( "name" => "Kano",          "lat" => 12.0,      "lng" => 8.516667 ),
    array( "name" => "Abuja",         "lat" => 9.066667,  "lng" => 7.483333 ),
    array( "name" => "Ibbadan",       "lat" => 7.396389,  "lng" => 3.916667 ),
    array( "name" => "Kaduna",        "lat" => 10.516667, "lng" => 7.433333 ),
    array( "name" => "Port Harcourt", "lat" => 4.790000,  "lng" => 6.993333 ),
    array( "name" => "Aba",           "lat" => 5.116667,  "lng" => 7.366667 ),
    array( "name" => "Ogbomosho",     "lat" => 8.133333,  "lng" => 4.25 ),
    array( "name" => "Maiduguri",     "lat" => 11.833333, "lng" => 13.15 ),
    array( "name" => "Benin City",    "lat" => 6.317600,  "lng" => 5.6145 ),
    array( "name" => "Zaria",         "lat" => 11.066667, "lng" => 7.7 ),
);

$city_reduced = array();
foreach( $city_data as $city ) {
    $city_reduced[] = array( $city["lng"], $city["lat"] );
}

$cities = array(
    "name" => "_cities",
    "description" => "meta",
    "coordinates" => array($city_reduced)
);

if( isset( $_GET['BORROWING_DATA'])) define( "BORROWING_DATA", true );

if( !defined("BORROWING_DATA") ) {
    echo "<PRE>";
    file_put_contents( "states/_cities.dat", serialize($cities));
    print_r( $cities );
    die;
}

// assume $city data will be read
// also assume this has been run once, extract coordinates

function city_svg_to_coordinates() {
    global $city_data;

    $svg_file = new SVG("states/_cities.dat.svg");
    $svg = $svg_file->info();
    $temp = explode(" ", $svg["_cities"]);

    foreach( $temp as $k => $t ) {
        preg_match( "/([0-9|\.]+),([0-9|\.]+)/", $t, $r );
        $city_data[$k]["x"] = $r[1];
        $city_data[$k]["y"] = $r[2];
    }
}

#echo "<PRE>";
#print_r( $city_data );
city_svg_to_coordinates();
