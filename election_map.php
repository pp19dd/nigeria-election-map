<?php
require( "class.svg.php" );

$svg_file = new SVG("election_map.svg");
$svg = $svg_file->info();
?>
<script type="text/javascript" src="jquery.min.js"></script>
<script type="text/javascript" src="raphael-min.js"></script>
<script type="text/javascript" src="rainbowvis.js"></script>
<script type="text/javascript" src="tabletop.js"></script>

<script type="text/javascript">
var map_data = <?php echo json_encode($svg); ?>;
var paper;
var map = { };
var TRANSLATE = { x: 0, y: -155 };
var styles = {
    poly: {
        fill: 'gray',
        'stroke-width': 0.5,
        stroke: 'black'
    },
    water_body: { fill: 'silver', 'stroke-width': 0.5, stroke: 'silver' },
    over: { opacity: 0.5, stroke: 'white' },
    out: { opacity: 1, stroke: 'black' },
    status: { "font-size": 14, "text-anchor": "start" },
    city: {
        dot: { fill: "black" },
        label: { "text-anchor": "middle", "font-size": 14 }
    },
    trigger: { fill: "white", "stroke-width": 0, opacity: 0 }
};

<?php readfile( "colors.js") ?>


var range = <?php
    echo json_encode(unserialize(file_get_contents("states/_grand_range.txt")));
?>;

<?php
define( "BORROWING_DATA", true );
include( "1b_cities.php" );
?>

var cities = <?php echo json_encode($city_data); ?>;

// generate voting palette dynamically, using a single state row
var rainbows = { };

// run-once
function addRainbows(row) {
    for( var k in row ) {
        if( k.length <= 5 ) {
            var randy = parseInt(Math.random() * colourNames.length);

            if( k == "PDP" ) randy = 15; // 2011: south
            if( k == "CPC" ) randy = 11; // 2011: north
            if( k == "ACN" ) randy = 9; // 2011: exception

            rainbows[k] = new Rainbow();
            rainbows[k].setNumberRange(0, 100);
            rainbows[k].setSpectrum("#ffffff", "#" + colourNames[randy].value );
        }
    }
}


// var hexColour = rainbow.colourAt(item.rows);

function tooltip(state, x, y) {
    //var status = paper.text(10,10,"status");

    var html = "";

    // FIXME: erm why is this breaking without try ?
    try {
        var data = map[state].__data;
        var maj = map[state].__maj;
        html += "<strong>" + data["State Name"] + " State</strong>";
        html += "<p>Majority: " + maj.maj + " party</p>";
        html += "by " + Math.round(maj.percentage * 10) / 10 + " % of the votes";
    } catch( err ) {
        //console.info( err );
        //console.info( state );
    }



    $("#tooltip_status").html(html);

    x = 300; y = 290;
    $("#tooltip_status").css( {
        "margin-left": x + "px",
        "margin-top": y + "px"
    })
}

// lagos only, 2015-02-12
function draw_cities() {
    $.each( cities, function(k, city) {

        if( city.name != "Lagos") return(false);
        var s = paper.set();

        s.push( paper
            .circle( city.x, city.y, 5 )
            .translate(TRANSLATE.x, TRANSLATE.y)
            .attr( styles.city.dot )
        );

        s.push( paper
            .circle( city.x, city.y, 8 )
            .translate(TRANSLATE.x, TRANSLATE.y)
        );

        s.push( paper
            .text( city.x, city.y, city.name )
            .translate(TRANSLATE.x, TRANSLATE.y + 15)
            .attr(styles.city.label)
        );

        var b = s.getBBox();
        var l = paper.rect( b.x, b.y, b.width, b.height ).attr(styles.trigger);

        l.mouseover( function() {
            over(map.lagos);
        }).mouseout( function() {
            out(map.lagos);
        });

    });
}

function over(e) {
    e.stop().animate(styles.over, 300, "<>");
    //status.attr("text", e.__state);
    var x = e.getBBox();
    tooltip( e.__state, x.cx, x.cy );
    //status.show();
}

function out(e) {
    // map[state].stop().animate({ fill: map[state].__fill }, 300, "<>");
    e.stop().animate(styles.out, 300, "<>");
    //status.hide();
    tooltip( "", 0, 0 );
}


/* (maj return)...
maj: "PDP"
max: 268243
percentage: 64.67520506131346
total: 414754
*/
function setVotes(poly, state, maj) {
    // console.info( "setting " + state + " to " + maj.maj + " " + maj.percentage );
    // setting bauchi to CPC 81.6852308001893

    //poly.hide();
    poly.attr({ fill: "#" + rainbows[maj.maj].colourAt(maj.percentage) });

    map[state].__maj = maj;
}

// two passes: first cosmetic, second event triggering
function draw_states(options) {
    for( var k in map_data )(function(state, path_string) {

        /*if( options.trigger == false && state == "abia" ) {
            console.info( state );
            //return( false );
        }
        if( state == "g11" ) state = "abia";
        */
        if( state == "g6" ) return(false);  // city geocode simulation

        if( state == "water_body" ) {
            map[state] = paper.path(path_string);
            map[state].attr(styles.water_body);
            map[state].translate(TRANSLATE.x, TRANSLATE.y);

            return(false);
        }

        // if( state == "g11" ) return(false);
        // if( state == "water_body" ) return(false);

        if( options.trigger == false ) {

            map[state] = paper.path(path_string);
            map[state].attr(styles.poly);
            map[state].translate(TRANSLATE.x, TRANSLATE.y);
            map[state].__fill = map[state].attr("fill");
            map[state].__state = state;

            // $("#counts").append( "<li>" + state + "</li>" );

            map[state].__setVotes = function(maj) {
                setVotes(map[state], state, maj);
            }

        } else {

            map[state].__trigger = paper.path(path_string);
            map[state].__trigger.translate(TRANSLATE.x, TRANSLATE.y);
            map[state].__trigger.attr(styles.trigger);

            map[state].__trigger.mouseover(function() {
                over(map[state]);
            }).mouseout(function() {
                out(map[state]);
            });

        }

    })(k, map_data[k]);
}

/*
function load_data() {
    Tabletop.init({
        key: '14UU6pYCxSmZ2Z_cS7Ch8c7KSmueptSnGHoHcmcRZlJI',
        callback: function(data, tabletop) {
            // console.log(data)
            console.info( data );
            //console.info( JSON.stringify(data) );


        },
        simpleSheet: true
    });
}*/

function load_data() {
    data_loaded(<?php echo file_get_contents("2011.json"); ?>);
}

function determine_maj(state) {
    var max = 0;
    var maj;
    var total = 0;

    for( var k in state )(function(state, votes) {
        // skip "state symbol" columns
        if( k.length > 5 ) return(false);

        total += votes;

        if( votes > max ) {
            max = votes;
            maj = state;
        }
    })(k, parseInt(state[k]));

    return({
        max: max,
        maj: maj,
        total: total,
        percentage: (max / total) * 100
    });
}

function data_loaded(data) {
    addRainbows(data[0]);

    for( var state in data )(function(key, state) {
        var maj = determine_maj(state);

        /* (maj return)...
            maj: "PDP"
            max: 268243
            percentage: 64.67520506131346
            total: 414754
        */

        map[key].__setVotes(maj);
        map[key].__data = state;

    })(data[state]["State Symbol"], data[state]);
}

Raphael(function() {
    paper = Raphael("map", 525, 370);

    // status.attr( styles.status );
    // status.hide();

    draw_states({ trigger: false });
    draw_cities();
    draw_states({ trigger: true });

    load_data();

    // status.toFront();
});
</script>
<style>
body, html { background-color: silver }
#map { background-color: white; width: 525px; height:370px; }
#counts li { width: 150px; float: left; }
#tooltip_status_container { width:0px; height:0px; position: absolute; z-index:100 }
#tooltip_status {
    width: 220px;
    font-family: Arial;
    font-size: 12px;
    padding-left: 50px;
}
#tooltip_status, #tooltip_status p { line-height: 1.25em; }
</style>

<div id="tooltip_status_container">
    <div id="tooltip_status"></div>
</div>

<div id="map"></div>

<ul id="counts"></ul>
