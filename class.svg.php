<?php

class SVG {

    public $doc;
    public $xpath;
    public $rootNamespace;

    function __construct( $filename ) {

        # cheating: avoiding inkscape namespace complications
        # look for first:    <!-- COMPOSE BEGIN -->
        # and look for last: <!-- COMPOSE END -->

        # $marker_a = "<!-- COMPOSE BEGIN -->";
        # $marker_b = "<!-- COMPOSE END -->";

        # $xml = file_get_contents($filename);

        # $pos_a = stripos($xml, $marker_a);
        # $pos_b = strripos($xml, $marker_b);
        # $mid = substr($xml, $pos_a);

        # echo $mid; die;

        # var_dump($pos_a);
        # var_dump($pos_b);
        # die;

        $this->doc = new DOMDocument();
        $this->doc->load($filename);

        $this->rootNamespace = $this->doc->lookupNamespaceUri(
            $this->doc->namespaceURI
        );

        $this->xpath = new DOMXPath($this->doc);
        $this->xpath->registerNamespace(
            'svg',
            $this->rootNamespace
        );
    }

    function getPath($node) {
        $r = array();
        # $paths = $this->xpath->query("svg:g/svg:path", $node);
        $paths = $this->xpath->query("svg:path|svg:g/svg:path", $node);
        #echo "@" .$paths->length . "@ ";
        foreach( $paths as $path ) {
            $id = $path->getAttribute("id");
            #$r[$id] = $path->getAttribute("d");
            $r[] = $path->getAttribute("d");
        }
        return( implode(" ", $r ) );
    }

    function info() {
        $r = array();

        // FIXME: maybe addition of |svg:g broke things. :/
        $states = $this->xpath->query( "svg:g/svg:g|svg:g" );

        foreach( $states as $state ) {

            $id = $state->getAttribute("id");
            $r[$id] = $this->getPath($state);


        }
#echo "<PRE>"; print_r( $r) ; die;
        return( $r );
    }
}
