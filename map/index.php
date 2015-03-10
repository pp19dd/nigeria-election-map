<?php require("legend.php"); ?>
<!doctype html>
<html>
<head>
    <title>Nigerian Election Map - <?php echo $year ?> - <?php echo $lang ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript" src="raphael-min.js"></script>
    <script type="text/javascript" src="rainbowvis.js"></script>
    <script type="text/javascript" src="candidate.js?rand=<?php echo rand() ?>"></script>

<script type="text/javascript">
var map_class = -1;
var map_data = <?php echo json_encode($svg); ?>;
var paper;

// resident objects
var map = { };
var map_city = { };

var TRANSLATE = { x: 0, y: -155 };
var LANG = "<?php echo $lang ?>";
var text = {
    en: {
        state_name_key: "State Name",
        state: "state",
        majority: "majority",
        party: "party",
        votes: "% of Votes"
    },
    ha: {
        state_name_key: "State Name HA",
        state: "Jiha",
        majority: "Rinjaye",
        party: "Jamiyya",
        votes: "% kuri’u"
    }
}

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

<?php readfile( "../colors.js") ?>


var range = <?php
    echo json_encode(unserialize(file_get_contents("../states/_grand_range.txt")));
?>;

// city data
<?php
chdir( ".." );
define( "BORROWING_DATA", true );
include( "1b_cities.php" );
chdir( "map" );
?>

var cities = <?php echo json_encode($city_data); ?>;

// generate voting palette dynamically, using a single state row
var rainbows = { };

var legend = {
    party_a: <?php echo json_encode($party_a); ?>,
    party_b: <?php echo json_encode($party_b); ?>,
    total_votes: 0
}

function compute_legend(state, maj) {
    //console.info(state, maj);
    //console.info( legend.party_a.symbol );

    legend.total_votes += maj.total;

    legend.party_a.vote_count += parseInt(state[legend.party_a.symbol]);
    legend.party_b.vote_count += parseInt(state[legend.party_b.symbol]);

    if( maj.req.a >= 25.0 ) { legend.party_a.state_count++; }
    if( maj.req.b >= 25.0 ) { legend.party_b.state_count++; }
}

// run-once
function addRainbow(k) {
    rainbows[k] = new Rainbow();
    rainbows[k].setNumberRange(0, 100);
    var randy = parseInt(Math.random() * colourNames.length);

    // empty data exception
    if( k == "" ) {
        randy = 0; // empty
        rainbows[k].setSpectrum("#" + colourNames[randy].value, "#000000" );
        return;
    }

    if( k == legend.party_a.symbol ) {
        rainbows[k].setSpectrum("#ffffff", legend.party_a.fill );
        return(false);
    }
    if( k == legend.party_b.symbol ) {
        rainbows[k].setSpectrum("#ffffff", legend.party_b.fill );
        return(false);
    }

    if( k == "PDP" ) randy = 15; // 2011: south
    if( k == "CPC" ) randy = 11; // 2011: north
    if( k == "ACN" ) randy = 37; // 2011: exception 42 ok

    rainbows[k].setSpectrum("#ffffff", "#" + colourNames[randy].value );
}

function addRainbows(row) {
    addRainbow("");
    for( var k in row ) {
        if( k.length <= 5 ) {
            addRainbow(k);
        }
    }
}

function draw_legend() {

    var legend_paper1 = Raphael("map_legend1", 630, 70);
    var legend_paper2 = Raphael("map_legend2", 630, 115);

    legend.party_a.obj = new candidate();
    legend.party_b.obj = new candidate();

    legend.party_a.obj
        .init(legend_paper1, "#map_legend1")
        .setName(legend.party_a.fname, legend.party_a.lname, legend.party_a.symbol)
        .setPicture(legend.party_a.image)
        .setVotes(legend.party_a.vote_count, legend.total_votes)
        .setOffset("party_label", 104, 20)
        .setStates(legend.party_a.state_count)
        .setRainbowColor(legend.party_a.fill)
        .draw();
    ;
    legend.party_b.obj
        .init(legend_paper2, "#map_legend2")
        .setName(legend.party_b.fname, legend.party_b.lname, legend.party_b.symbol)
        .setPicture(legend.party_b.image)
        .setVotes(legend.party_b.vote_count, legend.total_votes)
        .setOffset("party_label", 144, 20)
        .setStates(legend.party_b.state_count)
        .setRainbowColor(legend.party_b.fill)
        .draw()
    ;

    //$("#party_" + legend.party_a.symbol).html( legend.party_a.state_count );
    // $("#party_" + legend.party_b.symbol).html( legend.party_b.state_count );
    //console.info( "done") ;
//  console.dir( legend );
//legend.par
}

// var hexColour = rainbow.colourAt(item.rows);

function tooltip(state, x, y) {
    //var status = paper.text(10,10,"status");

    var html = "";

    // FIXME: erm why is this breaking without try ?
    try {
        var data = map[state].__data;
        var maj = map[state].__maj;
        html += "<strong>" + data[text[LANG].state_name_key] + " " + text[LANG].state+ "</strong>";
        if( maj.maj == "" ) {
            html += "<p>&nbsp;</p>";
        } else {
            html += "<p>" + text[LANG].majority + ": " + maj.maj + " " + text[LANG].party + "</p>";
        }
        html += Math.round(maj.percentage * 10) / 10 + " " + text[LANG].votes;
    } catch( err ) {
    }



    $("#tooltip_status").html(html);
}


function draw_city(city) {
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

    if( city.name == "Lagos" ) {
        var b = s.getBBox();
        var l = paper.rect( b.x, b.y, b.width, b.height ).attr(styles.trigger);

        l.mouseover( function() {
            over(map.lagos);
        }).mouseout( function() {
            out(map.lagos);
        });
    }

    map_city[city.name] = s;
}

// lagos only, 2015-02-12
function draw_cities() {
    $.each( cities, function(k, city) {

        if(
            city.name == "Lagos" ||
            city.name == "Abuja" ||
            city.name == "Port Harcourt" ||
            city.name == "Maiduguri"
        ) {
            draw_city(city);
        }

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

    tooltip( "", 0, 0 );
}


/* (maj return)...
maj: "PDP"
max: 268243
percentage: 64.67520506131346
total: 414754
*/
function setVotes(poly, state, maj) {
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

function load_data() {
    var key = "<?php echo $key ?>";
    $.ajax({
        dataType: "json",
        url: "http://projects.voanews.com/data/?key=" + key + "&callback=?",
        success: function(data) {
            data_loaded(data);
        }
    })
}

function determine_maj(state) {
    var max = 0;
    var maj = "";
    var total = 0;

    var lookup = {};

    for( var k in state )(function(state, votes) {
        // skip "state symbol" columns
        if( k.length > 5 ) return(false);

        total += votes;

        lookup[state] = votes;

        if( votes > max ) {
            max = votes;
            maj = state;
        }
    })(k, parseInt(state[k]));

    if( isNaN(max) ) max = 0;
    if( isNaN(total) ) total = 0;

    if( max != 0 && total != 0 ) {
        var percentage = (max / total) * 100;
    } else {
        var percentage = 0.0;
    }

    // nigerian law requires a candidate to have at least
    // 25% vote in 2/3 of the states

    return({
        req: {
            a: (lookup[legend.party_a.symbol] / total) * 100,
            b: (lookup[legend.party_b.symbol] / total) * 100
        },
        max: max,
        maj: maj,
        total: total,
        percentage: percentage
    });
}

function data_loaded(data) {
    addRainbows(data[0]);

    for( var state in data )(function(key, state) {
        var maj = determine_maj(state);

        compute_legend(state, maj);

        /* (maj return)...
            maj: "PDP"
            max: 268243
            percentage: 64.67520506131346
            total: 414754
        */

        map[key].__setVotes(maj);
        map[key].__data = state;

    })(data[state]["State Symbol"], data[state]);

    draw_legend();
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

function map_city_style(a) {
    $.each(map_city, function(k, v) {
        v[2].attr(a);
    });
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
            map_city_style( { "text-anchor": "start", "font-size": 32 });
        break;
        case 1:
            iterate( { "stroke-width": 0.5, "stroke": "black" });
            map_city_style( { "text-anchor": "middle", "font-size": 20 });
        break;
        case 2:
            iterate( { "stroke-width": 1, "stroke": "rgb(70,70,70)" });
            map_city_style( { "text-anchor": "middle", "font-size": 14 });
        break;
        case 3:
            iterate( { "stroke-width": 2, "stroke": "rgb(70,70,70)" });
            map_city_style( { "text-anchor": "middle", "font-size": 14 });
        break;
    }

}


function resize_map() {
    var w = $(window).width();
    var h = $(window).height();
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

    if( typeof legend.party_a.obj != "undefined" ) legend.party_a.obj.resize(nw, nh);
    if( typeof legend.party_b.obj != "undefined" ) legend.party_b.obj.resize(nw, nh);


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
    /////////make_table();

    // status.toFront();

    $(window).resize(function(e) {
        resize_map();
    });

    resize_map();
    check_map_class();

});
</script>

<style type="text/css">
body, html { width:100%; background-color: white; margin:0; padding:0; overflow: hidden; height:100%; margin:auto !important }
#map { background-color: white; width: 525px; height:370px; }
/*
#map_legend { background-color: white; height:90px; font-family: Arial; font-size: 12px }
#map_legend td { vertical-align: top; text-align: center }
*/
#counts li { width: 150px; float: left; }

#tooltip_status_container { width:0px; height:0px; position: absolute; z-index:100 }
.too-short #tooltip_status { /*background-color: red;*/ }
.too-short { display: inherit !important }
#tooltip_status, #tooltip_status p { line-height: 1.25em; }
#tooltip_status {
    width: 220px;
    height:80px;
    font-family: Arial;
    font-size: 12px;
    padding-left: 50px;
}

#table_data { display: none }
#vote_data tbody { height: 50px; overflow: auto !important }
</style>
</head>

<body>

<!--
<div style="position:absolute; width:0px; height:0px">
    <img id="lol" style="opacity:0.5" src="2015-03_nigeria-election-mockup.png" />
</div>
-->

<div id="map_legend1"></div>
<div id="map_legend2"></div>
<div id="map"></div>

<div id="tooltip_status_container">
    <div id="tooltip_status"></div>
</div>

<div id="table_data">
    <table id="vote_data">
        <thead>
            <tr>
                <th>Leading Party</th>
                <th>Votes</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

</body>
</html>
