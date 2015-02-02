<?php

class XY {
    public $x;
    public $y;

    function __construct($x = 0, $y = 0) {
        $this->x = $x;
        $this->y = $y;

        $this->temp = new Proj4php();
    }

    function transform() {
        $this->temp->transform(
            Proj4php::$WGS84,
            new Proj4phpProj('GOOGLE'),
            $this
        );
    }

    function scale_X_to($width) {
        
    }

    function scale_Y_to($height) {

    }

    function __toString() {
        return( sprintf( "%.1f, %.1f", $this->x, $this->y) );
    }
}

class XY_ranger {
    public $x = array();
    public $y = array();

    function addSingle($point) {
        $this->x[] = $point->x;
        $this->y[] = $point->y;

        return( $this );
    }

    function reset() {
        $this->x = array();
        $this->y = array();

        return( $this );
    }

    function add($xy) {
        if( is_array($xy) ) {
            foreach( $xy as $point ) {
                $this->addSingle($point);
            }
        } else {
            $this->addSingle($xy);
        }

        return( $this );
    }

    function getDelta($x_or_y) {
        return( $x_or_y["max"] - $x_or_y["min"] );
    }

    function getRange() {
        $r = array(
            "x" => array(
                "min" => min($this->x),
                "max" => max($this->x)
            ),
            "y" => array(
                "min" => min($this->y),
                "max" => max($this->y)
            )
        );
        $r["x"]["delta"] = $this->getDelta($r["x"]);
        $r["y"]["delta"] = $this->getDelta($r["y"]);
        return($r);
    }
}

class XY_points {
    public $points = array();
    public $temp;

    function __construct() {

    }

    function zeroPoints($range) {
        foreach( $this->points as $k => $point ) {
            $this->points[$k]->x -= $range["x"]["min"];
            $this->points[$k]->y -= $range["y"]["min"];
        }
    }

    function scaleTo( $range, $width, $height ) {
        foreach( $this->points as $k => $point ) {
            $this->points[$k]->x = ($point->y * $width) / $height;
            $this->points[$k]->y = ($point->x * $height) / $width;
        }
    }

    function loadPoints($points) {
        foreach( $points as $point ) {
            $xy = new XY($point[0], $point[1]);
            $xy->transform();
            $this->points[] = $xy;
        }
    }
}
