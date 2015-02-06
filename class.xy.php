<?php

class XY {
    public $x;
    public $y;

    function __construct($x = 0, $y = 0) {
        $this->x = $x;
        $this->y = $y;
    }

    function __toString() {
        return( sprintf( "%.1f,%.1f", $this->x, $this->y) );
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
        unset( $this->x );
        unset( $this->y );

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
        $this->temp = new Proj4php();
    }

    function zeroPoints($range) {
        foreach( $this->points as $k => $point ) {
            $this->points[$k]->x -= $range["x"]["min"];
            $this->points[$k]->y -= $range["y"]["min"];
        }
    }

    function scaleTo( $sx, $sy) {
        foreach( $this->points as $k => $point ) {
            $this->points[$k]->x = $point->x * $sx;
            $this->points[$k]->y = $point->y * $sy;
        }
    }

    function transform(&$point) {
        $this->temp->transform(
            Proj4php::$WGS84,
            new Proj4phpProj('GOOGLE'),
            $point
        );
    }

    function loadPoints($points) {
        foreach( $points as $point ) {
            $xy = new XY($point[0], $point[1]);
            $this->transform($xy);
            $this->points[] = $xy;
        }
    }

    function simpleCoordinates($height) {
        $r = array();
        foreach( $this->points as $point ) {
            $r[] = sprintf("%0.1f,%0.1f", $point->x, $height - $point->y);
        }
        return( $r );
    }
}
