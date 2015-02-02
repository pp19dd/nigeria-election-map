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
    
    function q1($ref, $sel) {
        $temp = $this->xpath->query($sel, $ref);
        return( trim($temp->item(0)->nodeValue) );
    }
    
    function text_to_coords($coords) {
        #return(array(0,1));
        $ret = array();
        $temp = explode("\n", trim($coords));
        foreach( $temp as $k => $v ) {
            $line = explode(",", $v);
            $ret[] = $line;
        }
        return( $ret );
        echo "<PRE>"; print_r( $temp ); die;
    }
    
    function parse() {
        $placemarks = $this->xpath->query("//{$this->ns}:Placemark");
        
        // name, description, coordinates
        foreach( $placemarks as $placemark)  {
            
            $name = $this->q1($placemark, "{$this->ns}:name");
            $desc = $this->q1($placemark, "{$this->ns}:description");
            $coords = $this->q1($placemark, "{$this->ns}:*//{$this->ns}:coordinates");
            
            $this->data[] = array(
                "name" => $name,
                "description" => $desc,
                "coordinates" => $this->text_to_coords($coords)
            );
            
            #print_r( $coords ); die;
        }
        
    }
    
    function json() {
        echo json_encode($this->data);
    }
    
}