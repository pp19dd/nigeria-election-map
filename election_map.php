<?php
require( "class.svg.php" );

$svg_file = new SVG("election_map.svg");
$svg = $svg_file->info();
?>
<!doctype html>
<html>
<head>
    <title>Nigerian Election Map</title>

<script type="text/javascript" src="jquery.min.js"></script>
<script type="text/javascript" src="raphael-min.js"></script>
<script type="text/javascript" src="rainbowvis.js"></script>
<script type="text/javascript" src="tabletop.js"></script>

<script type="text/javascript">
var map_class = -1;
var map_data = <?php echo json_encode($svg); ?>;
var paper;

// resident objects
var map = { };
var map_city = { };

var TRANSLATE = { x: 0, y: -155 };
var styles = {
    poly: {
        fill: 'gray',
        'stroke-width': 0.5,
        stroke: 'black'
    },
    water_body: { fill: 'silver', 'stroke-width': 0.5, stroke: 'silver' },
    over: { stroke: 'black', fill: "black" },
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
            .translate(TRANSLATE.x, TRANSLATE.y + 25)
            .attr(styles.city.label)
        );

        var b = s.getBBox();
        var l = paper.rect( b.x, b.y, b.width, b.height ).attr(styles.trigger);

        l.mouseover( function() {
            over(map.lagos);
        }).mouseout( function() {
            out(map.lagos);
        });

        map_city.lagos = s;

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
    e.stop().animate({ fill: e.__fill }, 300, "<>");
    //e.stop().animate(styles.out, 300, "<>");
    //status.hide();

    //FIXME: remove this comment
    ////////////tooltip( "", 0, 0 );
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

    var computed_color = "#" + rainbows[maj.maj].colourAt(maj.percentage);
    poly.attr({ fill: computed_color });

    map[state].__fill = computed_color;

    map[state].__maj = maj;

    /*    $("#vote_data tbody").append(
        "<tr>" +
            "<td>" + state + "</td>" +
            "<td>" + maj.maj + "</td>" +
            "<td>" + maj.percentage + "</td>" +
        "</tr>"
    );
    */
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

function make_table() {
    var totals = { };

    for( var state in map )(function(key, data) {
        for( var k in data )(function(party, votes) {
            if( party.length > 5 ) return(false);
            if( typeof totals[party] == "undefined" ) totals[party] = 0;

            totals[party] += votes;
        })(k, parseInt(data[k]));
    })(state, map[state].__data);

    for( var k in totals )(function(party, votes) {
        $("#vote_data tbody").append(
            "<tr>" +
            "<td>" + party + "</td>" +
            "<td>" + votes + "</td>" +
            "</tr>"
        )
    })(k, totals[k]);

}

function iterate(p) {
    for( var k in map )(function(poly) {
        poly.attr(p);
    })(map[k]);
}

function check_map_class(w) {

    var new_map_class = map_class;

    // adjust stroke if needed
    if( w <= 350 ) new_map_class = 0;
    if( w > 350 ) new_map_class = 1;
    if( w > 500 ) new_map_class = 2;
    if( w > 800 ) new_map_class = 3;

    // if pixels < 440, shift tooltip below map
    if( w < 440 ) {
        $("#tooltip_status_container").addClass("too-short");
    } else {
        $("#tooltip_status_container").removeClass("too-short");
    }

    if( new_map_class == map_class ) return( false );

    map_class = new_map_class;

    switch( map_class ) {
        case 0:
            iterate( { "stroke-width": 0.2, "stroke": "black" });
            map_city.lagos[2].attr( { "text-anchor": "start", "font-size": 32 });
        break;
        case 1:
            iterate( { "stroke-width": 0.5, "stroke": "black" });
            map_city.lagos[2].attr( { "text-anchor": "middle", "font-size": 20 });
        break;
        case 2:
            iterate( { "stroke-width": 1, "stroke": "rgb(70,70,70)" });
            map_city.lagos[2].attr( { "text-anchor": "middle", "font-size": 14 });
        break;
        case 3:
            iterate( { "stroke-width": 2, "stroke": "rgb(70,70,70)" });
            map_city.lagos[2].attr( { "text-anchor": "middle", "font-size": 14 });
        break;
    }

    //console.log( "map class = " + map_class );
}


function resize_map() {
    var w = $(window).width();
    var h = $(window).height(); false
    // h  is proportioned 525 / 370

    // assume map isn't clipped by viewport
    var nw = w;
    var nh = (370*w)/525;

    // and if it is, recompute w for h
    if( nh > h) {
        nh = h;
        w = (525 * nh) / 370;
    }

    check_map_class(w);

    $("#map").css({ width: nw, height: nh });
    paper.setSize(nw, nh);
    paper.setViewBox(0, 0, 535, 370);


    // resize tooltip
    var x = $("#map").width() - 220;
    var y = $("#map").height() - 80;

    $("#tooltip_status").css( {
        "margin-left": x + "px",
        //"margin-top": -y + "px"
        "margin-top": "-80px"
    })


}

Raphael(function() {
    paper = Raphael("map", 525, 370);

    // status.attr( styles.status );
    // status.hide();

    draw_states({ trigger: false });
    draw_cities();
    draw_states({ trigger: true });

    load_data();
    make_table();

    // status.toFront();

    $(window).resize(function(e) {
        resize_map();
    });

    resize_map();
    check_map_class();

});
</script>

<style type="text/css">
body, html { background-color: silver; margin:0; padding:0; overflow: hidden; height:100%; margin:auto !important }
#map { background-color: white; width: 525px; height:370px; }
#counts li { width: 150px; float: left; }

#tooltip_status_container { width:0px; height:0px; position: absolute; z-index:100 }
.too-short #tooltip_status { background-color: red; }
.too-short { display: inherit !important }
#tooltip_status, #tooltip_status p { line-height: 1.25em; }
#tooltip_status {
    width: 220px;
    height:80px;
    font-family: Arial;
    font-size: 12px;
    padding-left: 50px;
}

#table_data { }
#vote_data tbody { height: 50px; overflow: auto !important }
</style>
</head>

<body>

<div id="map"></div>

<div id="tooltip_status_container">
    <div id="tooltip_status"></div>
</div>

<div id="table_data">
    <table id="vote_data">
        <thead>
            <th>Leading Party</th>
            <th>Votes</th>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

</body>
</html>
