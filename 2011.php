<?php
$t = trim(file_get_contents("2011.dat"));
$t = explode("\n", $t);

$header = array_shift($t);
$parties = array();
$states = array();

function inc(&$a, $e) {
    if( !isset($a[$e] ) ) $a[$e] = 0;
    $a[$e]++;
}

// load data, find breakdowns
foreach( $t as $e ) {
    $e = explode("\t", $e);

    $state = trim($e[0]);
    $party = trim($e[1]);
    $votes = trim($e[2]);
    $percentage = trim($e[3]);

    inc($parties, $party);
    inc($states, $state);
}

function sweep( $find_state, $find_party ) {
    global $t;

    foreach( $t as $e ) {
        $e = explode("\t", $e);

        $state = trim($e[0]);
        $party = trim($e[1]);
        $votes = trim($e[2]);
        $percentage = trim($e[3]);

        if( $find_state == $state && $find_party == $party ) {
            return(array(
                "state" => $state,
                "party" => $party,
                "votes" => $votes,
                "percentage" => $percentage
            ));
        }
    }
    return( "ERROR" );
}

// 2011: 37 states, 20 parties checksum ok
#print_r( $parties ); print_r( $states );

echo "<table border='1'>";
echo "<tr><td>State</td><td>";
echo implode("</td><td>", array_keys($parties) );
echo "</td></tr>";

foreach( $states as $state => $count_s ) {
    echo "<tr>";
    echo "<td>{$state}</td>";
    foreach( $parties as $party => $count_p ) {
        $x = sweep($state, $party);
        $res = $x["votes"];
        printf( "<td>%s</td>", $res );
    }
    echo "</tr>";
}
echo "</table>";
