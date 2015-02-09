<?php

function extract_file( $file ) {
    $marker_1 = "<!-- COMPOSE BEGIN -->";
    $marker_2 = "<!-- COMPOSE END -->";

    $dummy = file_get_contents( $file );
    $pos_a = stripos( $dummy, $marker_1);
    $pos_b = stripos( $dummy, $marker_2);

    $middle = substr(
        $dummy,
        $pos_a,
        ($pos_b - $pos_a) + strlen($marker_2)
    );

    return( $middle );
}

$SVG = file_get_contents( "_svg_header.txt" );
$SVG .= "\n<g>";

$files = glob("states/*.svg");
foreach( $files as $file ) {
    $svg = extract_file($file);

    $SVG .= $svg;
    #echo "\n";
    #echo $svg;
}

$SVG .= "</g>\n<!-- COMPOSE END -->\n";
$SVG .= "</svg>";

$OUT = "states/_combined.svg";
file_put_contents( $OUT, $SVG );
echo number_format(filesize($OUT));
