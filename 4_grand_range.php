<?php
require( "class.xy.php" );
$ranges = glob("states/*.range");

$ranger = new XY_ranger();

?>
<table border='1'>
    <tr>
        <th rowspan="2">file</th>
        <th colspan="2">min</th>
        <th colspan="2">max</th>
    </tr>
    <tr>
        <th>x</th>
        <th>y</th>
        <th>x</th>
        <th>y</th>
    </tr>
<?php

foreach( $ranges as $range_file ) {
    $temp = unserialize(file_get_contents($range_file));

    $xy1 = new StdClass();
    $xy1->x = $temp["x"]["min"];
    $xy1->y = $temp["y"]["min"];
    $ranger->addSingle( $xy1 );

    $xy2 = new StdClass();
    $xy2->x = $temp["x"]["max"];
    $xy2->y = $temp["y"]["max"];
    $ranger->addSingle( $xy2 );

?>
<tr>
    <td><?php echo basename($range_file) ?></td>
    <td><?php printf( "%.1f", $xy1->x ); ?></td>
    <td><?php printf( "%.1f", $xy1->y ); ?></td>
    <td><?php printf( "%.1f", $xy2->x ); ?></td>
    <td><?php printf( "%.1f", $xy2->y ); ?></td>
</tr>
<?php

}
?>

</table>

<?php
$grand_range = $ranger->getRange();

file_put_contents( "states/_grand_range.txt", serialize($grand_range));

echo "<PRE>";
print_r( $grand_range );
