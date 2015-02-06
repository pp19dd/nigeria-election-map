<?php

class KML {
    public $doc;
    public $xpath;
    public $data = array();
    public $ns = "parser";
    public $rootns;

    function __construct($file) {
        $this->doc = new DOMDocument();
        $this->doc->loadXML(utf8_encode(file_get_contents($file)));
        $this->xpath = new DOMXPath($this->doc);

        $this->do_namespace();
        $this->parse();
    }

    function do_namespace() {
        $this->rootns = $this->doc->lookupNamespaceUri($this->doc->namespaceURI);
        $this->xpath->registerNamespace($this->ns, $this->rootns);
    }

    function q1($ref, $sel, $index = null) {
        $r = array();

        $temp = $this->xpath->query($sel, $ref);
        for( $i = 0; $i < $temp->length; $i++ ) {
            $r[] = trim($temp->item($i)->nodeValue);
        }

        if( is_null($index) ) return( $r[0] );
        return( $r );
    }

    function text_to_coords_single($coords) {
        $ret = array();
        $temp = explode("\n", trim($coords));
        foreach( $temp as $k => $v ) {
            $line = explode(",", $v);
            $ret[] = $line;
            ########if( $k >= 9 ) break;
        }
        return( $ret );
    }

    function text_to_coords($coords) {
        $r = array();
        foreach( $coords as $coord ) {
            $r[] = $this->text_to_coords_single($coord);
        }
        return( $r );
    }

    function parse() {
        $placemarks = $this->xpath->query("//{$this->ns}:Placemark");

        // name, description, coordinates
        foreach( $placemarks as $placemark)  {

            $name = $this->q1($placemark, "{$this->ns}:name");
            $desc = $this->q1($placemark, "{$this->ns}:description");
            $coords = $this->q1(
                $placemark, "{$this->ns}:*//{$this->ns}:coordinates",
                true
            );

            $this->data[] = array(
                "name" => $name,
                "description" => $desc,
                "coordinates" => $this->text_to_coords($coords)
            );
        }
    }

    function json() {
        echo json_encode($this->data);
    }

}
