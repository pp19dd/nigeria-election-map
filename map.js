
var paper;
var map = [];
var map_labels = [];

var styles = {
    size: {
        width: P_WIDTH,
        height: P_HEIGHT,
        enforced_width: P_WIDTH,    // used for tooltip box
        enforced_height: P_HEIGHT
    },
    margin: {
        all: 20,
        top: 50,
        bottom: 50,
        left: 50,
        right: 50
    },
    title: {
        'font-size': '18px',
        color: 'black'
    },
    area: {
        init: {
            'stroke-width': 0.5,
            stroke: 'gray',
            fill: 'white'
        },
        over: { fill: 'silver' },
        out: { fill: 'white' }
    },
    city: {
        marker: { 'fill': 'white' },
        label: {
            'text-anchor': 'start',
            'font-size': '12px',
            //'stroke': 'black',
            'fill': 'black'
            //'stroke-width': 0.5
        }
    },
    line: {
        'stroke-width': 6,
        stroke: '#625352',
        'stroke-dasharray': "."
    },
    line2: {
        'stroke-width': 3,
        stroke: 'gray',
        'stroke-dasharray': "."
    },
    line_text: {
        fill: '#625352',
        'font-size': '12px',
        'font-style': 'oblique'
    },
    line_text2: {
        fill: 'crimson',
        'font-size': '10px'
    }
}

var range = {
    x: { min: +Infinity, max: -Infinity, delta: 0 },
    y: { min: +Infinity, max: -Infinity, delta: 0 }
};

function cook_data() {

    $.ajax({
        type: "POST",
        url: "cook.php?mode=write",
        data: { data: JSON.stringify(data) },
        dataType: "json",
        success: function(data) {

        }
    });

}

function export_data() {
    var xpdata = [];
    for( var i = 0; i < map.length; i++ ) {
        xpdata[i] = map[i].__count;
    }
    document.getElementById('export_container').value = JSON.stringify(xpdata);
}

function recompute_status() {
    var x = parseInt($("#status").attr("tooltip_x")) - 5;
    var y = parseInt($("#status").attr("tooltip_y")) - 5;
    var w = parseInt($("#status").width()) + 10;
    var h = parseInt($("#status").height()) + 10;

    var half = w / 2;
    var bottom = 60;

    var nx = x - half;
    var ny = y + bottom;

    if( nx < 0) nx = 0;
    // if( nx > P_WIDTH-(half*2)) nx = P_WIDTH-(half*2);
    if( nx > styles.size.enforced_width-w) nx = styles.size.enforced_width - w - 2;

    // if( ny > P_HEIGHT - bottom ) ny = P_HEIGHT - bottom;
    // if( ny > styles.size.enforced_height - bottom ) ny = styles.size.enforced_height - bottom;

    // new direction: halfway flip
    if( y > (styles.size.enforced_height / 2) ) ny = y - bottom;


    $("#status").css({ top: ny, left: nx });
}

function status(t) {
    if( t == false ) {
        $("#status").hide();
        $("#status_inner").html("");
        return(false);
    }

    $("#status_inner").html(t);
    recompute_status();
    $("#status").show();
}

function status2(t) {
    document.getElementById('status2').innerHTML = t;
}

// iterate over a "polygon" definition (prefecture, sub-prefecture)
function iterate(a, p) {
    this.__index = 0;
    this.__total = 0;

    var list = [];
    if( typeof p == "undefined" ) {
        for( var i = 0; i < data.length; i++ ) {
            list.push(i);
        }
    } else {
        list.push( p );
    }

    // total computation
    for( var i in list ) {
        this.__total += data[list[i]].coordinates.length;
    }

    for( var i in list ) {
        for( var j = 0; j < data[list[i]].coordinates.length; j++ )(function(b) {
            this.__index++;
            a(b);
        })(data[list[i]].coordinates[j]);
    }
}

function detect_subprefecture(check_x, check_y) {
    for( var i = 0; i < map.length; i++ ) {
        if( Raphael.isPointInsidePath(map[i].getPath(), check_x, check_y) ) {
            return([i]);
        }
    }
    return([]);
}

function draw_points2() {
    if( map.length == 51 ) {
        var poly_counts = [
            0,8,0,0,9,7,0,0,0,0,52,62,28,0,0,322,20,0,6,63,5,13,1,1,3,0,1,15,
            9,3,0,0,1,8,3,8,0,3,1,2,1,0,0,1,0,0,0,1,410,0,0
        ];
    } else {
        var poly_counts = [8,0,16,0,142,0,342,74,18,1,27,9,15,3,1,411,0];
    }

    for( var i = 0; i < poly_counts.length; i++ ) {
        map[i].__count = poly_counts[i];
    }
}

// warning: slow function (dozens of minutes?)
// using point-in-polygon, recomputes polygon counts
// replaced in production with draw_points2(), which has baked-in values
function draw_points() {

    var report = "<ul>";

    for( var i = 0; i < mining_sites.features.length; i++ )(function(site, counter){

        ///////////if( counter > 3 ) return(false);

        var temp = new pt(site.geometry.coordinates);
        // paper.circle(temp.x, temp.y, 2);

        var found = detect_subprefecture(temp.x, temp.y);
        for( var j = 0; j < found.length; j++ ) {
            map[found[j]].__count++;
        }

        if( found.length == 0 ) {
            report += "<li>no polygon found for point " + counter + " (" + temp.x, temp.y + ")" + "</li>";
            paper.circle(temp.x, temp.y, 2);
        }
        //var temp = proj4("WGS84", "GOOGLE", site.geometry.coordinates);

    })(mining_sites.features[i], i);

    report += "</ul>";
    status2(report);
}

function update_map() {
    var R = new Rainbow();
    // R.setSpectrum('#f8f8ff', 'blue', 'navy');
    // R.setSpectrum('white', '#0EBFE9');

    //R.setSpectrum('white', '#0099CC');  // blue
    //R.setSpectrum('white', '#ff0080');    // pink
    //R.setSpectrum('white', '#0099CC', '#ff0080'); // hybrid

    R.setNumberRange(0, 410);
    R.setSpectrum('white', '#688bb3');  // blue


    for( var i = 0; i < map.length; i++ )(function(poly) {
        // poly.attr( { fill: '#' + R.colourAt(poly.__count) } );
        //poly.animate({
        poly.__fill = '#' + R.colourAt(poly.__count);

        poly.attr({ fill: poly.__fill });
        //fill:
        //}, 100 + (100*i), "<>");

        //poly.attr(  );
    })(map[i]);
}

function add_city(lat, lng, label, population, style, style_text, cap, left) {
    // if( population < 50000 ) return(false);
    var a = new pt([lat, lng]);

    paper.circle( a.x, a.y, 6).attr(styles.city.marker).attr(style);
    if( cap == true ) {
        paper.circle( a.x, a.y, 3).attr( { fill: 'black' } );
    }
    //paper.text(a.x, a.y, "?").attr(styles.city.label);

    if( left == true ) {
        map_labels.push( paper.text(a.x - 8, a.y, label).attr(styles.city.label).attr(style_text) );
    } else {
        map_labels.push( paper.text(a.x + 8, a.y, label).attr(styles.city.label).attr(style_text) );
    }
}

function draw_cities() {

    //add_city(9.066667, 7.483333, "Abuja", 622000, {}, { fill: 'black'}, true );
    add_city(7.483333, 9.066667, "Abuja", 622000, {}, { fill: 'black'}, true );

    //add_city(6.524379, 3.379206, "Lagos", 622000, {}, { fill: 'black'}, true );
    add_city(3.379206, 6.524379, "Lagos", 622000, {}, { fill: 'black'}, true );





//  add_city(18.58333, 4.36666, "Bangui", 622000, {}, { fill: 'black'}, true );
    /*
    add_city(18.40420, 4.25580, "Bimbo", 124000, {}, { "text-anchor": "end", transform: "t-15,0" } );
    add_city(15.78944, 4.26139, "Berb�rati", 77000, {}, {} );
    add_city(15.87000, 4.94000, "Carnot", 45000, {}, {} );
    add_city(20.67211, 5.76391, "Bambari", 41000, {}, {} );
    add_city(15.59176, 5.94249, "Bouar", 40000, {}, {} );

    add_city(17.44863, 6.48743, "Bossangoa", 36000, {}, {} );
    add_city(21.98714, 6.53967, "Bria", 35000, {}, {} );
    add_city(22.81740, 4.74222, "Bangassou", 31000, {}, {} );
    add_city(16.05188, 3.52213, "Nola", 29000, {}, {} );
    add_city(19.18059, 7.00341, "Kaga-Bandoro", 24000, {}, {} );
    add_city(19.08059, 5.73128, "Sibut", 22000, {}, {} );
    add_city(17.98556, 3.87061, "Mba�ki", 22000, {}, { transform: "t-25,15" } );
    add_city(16.38039, 6.31859, "Bozoum", 20000, {}, {} );
    add_city(20.64962, 8.40985, "N'D�l�", 13000, {}, {} );
    add_city(22.77857, 10.29603, "Birao", 10000, {}, {} );

    add_city(26.49167, 5.39565, "Obo", 12000, {}, {} );

    add_city(21.18545, 4.31479, "Mobaye", 8000, {}, {} );
*/

//    add_city(17.46255,4.32146, "Boda", -1, {}, { 'text-anchor': 'end' }, false, true );
    //var a = new pt([18.583333333333332, 4.366666666666666]);
    //paper.circle(a.x, a.y, 5);
}

function gmap_polygons_to_path(array_in) {
    var output = [];

    for( var i in array_in ) {
        var temp = [array_in[i][1], array_in[i][0]];
        output.push( new pt(temp));
    }

    var line_string = "M" + output[0].x + "," + output[0].y;
    for( var i in output ) {
        line_string += " L" + output[i].x + "," + output[i].y;
    }

    return( line_string );
}

function geotext( lat, lng, text ) {
    var a = new pt([lat, lng]);
    var x = paper.text(a.x, a.y, text)
    return( x );
}

function draw_lines() {
    var i1 = [
        //[3.797371261950895, 21.072464065917984 ],
        [4.217117877242137, 20.36865242041017 ],
        [4.84849540927718, 20.642967300781265 ],
        [5.085455995130671, 20.76347358740236 ],
        [5.442099134300063, 20.608291702636734 ],
        [5.738412084903284, 20.34873970068361 ],
        [5.952077832469431, 19.944305496582047 ],
        [6.362233718255604, 19.671707229980484 ],
        [6.375335553760866, 19.267959671386734 ],
        [6.6940513159283555, 19.149856644042984 ],
        [6.737695087310102, 18.685745315917984 ],
        [6.790062822251119, 18.260696487792984 ],
        [7.067077641668892, 17.828445511230484 ],
        [7.379882440787109, 17.304702835449234 ],
        [7.746627782216787, 17.29461677587892 ],
        [8.319651561892156, 17.42735298193361 ]
    ];
    var i2 = [
        //[3.572617325848856, 25.005569534667984 ],
        [3.8050613630816312, 22.980136947998062 ],
        [4.566637758126129, 22.559099870094315 ],
        [5.0319872662423615, 22.269898729690567 ],
        [5.546282162881718, 22.548430042633072 ],
        [6.136735845314583, 23.10549266851808 ],
        [6.683166114407329, 23.4286022952881 ],
        [6.989694850221243, 22.91027076757814 ],
        [7.549883367601001, 23.056182937988297 ],
        [7.994072787140205, 23.52378852880861 ],
        [8.576922557097236, 23.426284866699234 ],
        [9.01364135811493, 23.648758011230484 ],
        [9.322192185128749, 24.049758987792984 ]
        //[9.398072539163314, 25.760940628417984 ]
    ];
    var i3 = [
        [5.226606327488924, 14.343338089355484 ],
        [5.420332477814344, 14.807510452636734 ],
        [5.553858425938596, 14.947586136230484 ],
        [5.83721313364111, 15.035476761230484 ],
        [6.018884029881447, 14.905716019042984 ],
        [6.0694133360548745, 14.715530472167984 ],
        [6.099455544823569, 14.214309769042984 ]
    ];
    var i4 = [
        [8.193050897934526, 16.892166214355484 ],
        [7.855809937312938, 17.006179886230484 ],
        [7.572750851980445, 17.065261917480484 ],
        [7.26277459401442, 16.879837112792984 ],
        [7.291509783301834, 16.35282905615236 ],
        [7.363828130487837, 15.869766312011734 ]
    ];

    var a = gmap_polygons_to_path(i1);
    var b = gmap_polygons_to_path(i2);
    // var c = gmap_polygons_to_path(i3);
    // var d = gmap_polygons_to_path(i4);

    paper.path(a).attr(styles.line);
    paper.path(b).attr(styles.line);
    // paper.path(c).attr(styles.line2);
    // paper.path(d).attr(styles.line2);

    /*
    paper.text(P_WIDTH/2,20, "Central African Republic").attr(
        styles.title
    );
    */



    /*
    map_labels.push( geotext(18.93993,9.5, "Seleka\nArea of\nInfluence").attr(styles.line_text) );
    map_labels.push( geotext(15.84179,9.15920, "Anti-Balaka\nArea of\nInfluence").attr(styles.line_text) );
    map_labels.push( geotext(25.97118,8.41910, "Lord's\nResistance\nArmy\nIncursions").attr(styles.line_text) );
    */

    map_labels.push( geotext(18.93993,9.5, TEXT[LANGUAGE].area_seleka).attr(styles.line_text) );
    map_labels.push( geotext(15.84179,9.15920, TEXT[LANGUAGE].area_anti_balaka).attr(styles.line_text) );
    map_labels.push( geotext(25.97118,8.41910, TEXT[LANGUAGE].area_lra).attr(styles.line_text) );

    //paper.text(85,150, "Unknown\nArmed\nGroups").attr(styles.line_text2);
    //paper.text(20,240, "FDPC - \nMiskine's\nGroup").attr(styles.line_text2);
}

function draw_map(cooked) {
    //console.info( "draw_map()" );
    // loop through sub-prefectures
    //console.info( "length = " + data.length );
    for( i = 0; i < data.length; i++ )(function(points, index, meta) {

        if( typeof cooked == "undefined" ) {
            var pathcoords = "M" + points[0][2].x + "," + points[0][2].y;
            iterate( function(p) {
                pathcoords += "L" + p[2].x +"," + p[2].y + " "
            }, index);

            //console.info( "pathcoords.a length = " + pathcoords.length );
        } else {
            var pathcoords = "M" + points[0][0] + "," + points[0][1];
            iterate( function(p) {
                pathcoords += "L" + p[0] +"," + p[1] + " "
            }, index);
            //console.info( "pathcoords.b length = " + pathcoords.length );
        }

        var polygon = paper.path(pathcoords);
        /*
        polygon.attr(styles.area.init).mouseover( function(e) {
            //this.toFront().glow({color: 'black', width: 10 });
            status(
                "<span class='meta_name'>" + meta.name + " " + meta.desc + "</span><br/><span class='meta_count'>" +
                //meta.len +
                //" points, count =" +
                // "Diamond mines: " + this.__count + "</span>"
                TEXT[LANGUAGE].diamond_mines + this.__count + "</span>"
            );
            //this.stop().animate(styles.area.over, 200, "<>");
            this.attr(styles.area.over);
        }).mouseout( function() {
            status(false);
            //this.stop().animate(styles.area.out, 200, "<>");
            this.attr(styles.area.out).attr({fill: this.__fill});
        }).mousemove( function(e) {
            $("#status").attr({ tooltip_x: e.clientX, tooltip_y: e.clientY });

            //$("#status").css({top: e.clientY, left: e.clientX });
            recompute_status();
        });
*/
        // number of dia mines
        polygon.__count = 0;
        map.push( polygon );

    })(data[i].coordinates, i, {
        name: data[i].name,
        desc: data[i].description,
        len: data[i].coordinates.length
    });

}

function draw_areas_of_influence() {
    //paper.path("M628,78 L625,78 L624,78 L619,78 L610,78 L603,78 L598,78 L597,78 L593,78 L591,78 L587,78 L584,78 L580,78 L578,78 L576,78 L575,78 L572,78 L569,78 L565,78 L562,78 L559,78 L557,78 L555,78 L551,78 L549,79 L546,79 L543,79 L540,80 L538,80 L536,80 L535,80 L532,80 L530,81 L528,81 L526,81 L525,81 L523,81 L521,81 L520,81 L517,81 L515,81 L512,80 L509,79 L508,79 L506,79 L504,78 L502,78 L500,78 L497,78 L495,78 L494,78 L493,78 L492,78 L490,78 L489,78 L486,78 L484,78 L482,78 L481,78 L480,78 L478,78 L477,79 L476,79 L475,80 L474,80 L472,82 L470,82 L469,83 L468,83 L466,84 L465,85 L463,85 L463,86 L462,87 L461,87 L460,87 L459,88 L458,88 L457,89 L456,89 L455,89 L454,90 L453,91 L452,92 L449,93 L447,96 L445,97 L444,99 L444,100 L443,100 L443,102 L441,105 L441,106 L441,107 L440,107 L440,109 L439,109 L439,111 L438,112 L438,113 L438,115 L438,116 L438,117 L438,118 L437,119 L437,120 L437,121 L437,123 L437,125 L438,126 L438,127 L438,128 L439,130 L439,131 L439,133 L439,134 L439,135 L439,136 L439,137 L439,139 L439,140 L439,142 L439,143 L439,144 L438,146 L437,148 L436,149 L436,150 L434,151 L433,152 L432,153 L431,154 L430,154 L429,155 L428,156 L428,157 L427,158 L427,159 L426,160 L425,161 L425,162 L424,164 L424,166 L422,168 L421,170 L420,171 L419,172 L418,173 L418,174 L417,175 L417,176 L417,178 L416,180 L416,182 L415,184 L414,185 L414,187 L413,189 L413,190 L413,192 L413,193 L413,194 L414,194 L415,194 L416,195 L417,197 L418,197 L418,199 L418,200 L419,201 L420,201 L421,202 L423,203 L424,204 L426,204 L426,205 L426,206 L427,206 L428,207 L429,207 L429,208 L430,208 L431,209 L432,210 L433,210 L433,211 L433,212 L433,213 L433,216 L431,219 L431,221 L430,223 L429,225 L428,225 L428,226 L427,226 L426,227 L426,228 L425,228 L425,229 L424,230 L422,232 L421,232 L421,233 L420,233 L420,234 L420,235 L418,238 L416,241 L414,244 L412,246 L410,249 L409,249 L408,250 L406,251 L403,254 L400,256 L399,257 L398,260 L397,260 L397,261 L396,261 L396,262 L395,263 L394,264 L393,267 L392,267 L392,268 L391,268 L391,269 L390,270 L389,272 L388,272 L387,274 L387,275 L386,275 L386,277 L386,278 L386,279 L385,281 L384,282 L384,283 L383,286 L383,287 L382,289 L382,290 L382,291 L382,292 L382,293 L383,295 L383,296 L383,297 L384,299 L385,301 L385,304 L386,304 L386,305 L387,308 L388,311 L389,312 L391,315 L392,316 L394,318 L394,319 L395,320 L396,321 L397,324 L398,325 L399,327 L401,330 L402,331 L403,334 L404,334 L406,336 L406,337 L406,338 L407,339 L408,340 L409,342 L410,344 L410,345 L411,346 L412,346 L412,347 L413,348 L415,349 L416,349 L418,351 L419,351 L420,352 L423,353 L424,353 L425,354 L426,354 L427,354 L428,354 L429,354 L430,354 L431,354 L432,354 L436,355 L437,355 L438,355 L439,355 L443,355 L444,355 L454,355 L456,356 L457,356 L461,356 L461,357 L463,357 L468,358 L469,358 L473,359 L474,359 L477,360 L479,360 L481,361 L483,361 L484,361 L486,361 L487,361 L495,361 L496,362 L497,362 L499,363 L500,363 L502,363 L504,364 L506,364 L507,364 L509,365 L511,365 L518,366 L519,366 L520,366 L521,366 L523,366 L525,366 L526,366 L528,366 L538,367 L540,367 L542,367 L545,367 L548,367 L551,367 L554,367 L558,367 L580,367 L585,367 L590,367 L594,367 L598,367 L601,367 L604,367 L608,367 L622,368 L625,368 L628,368 L631,369 L634,369 L637,369 L639,369 L642,369 L644,369 L646,369 L659,369 L661,369 L664,369 L667,369 L669,369 L674,369 L674,370)").
    //attr({ "stroke-width": 0, fill: "silver"}).toBack();

    var L = "M3,1 L7,1 L16,1 L20,1 L37,2 L42,2 L59,2 L62,2 L74,3 L76,3 L82,3 L85,3 L91,4 L98,5 L104,5 L105,5 L111,5 L113,6 L118,6 L119,6 L126,6 L132,6 L134,6 L137,6 L138,6 L140,6 L142,6 L143,6 L143,5 L145,5 L148,5 L152,5 L154,5 L156,5 L159,5 L159,6 L159,7 L159,12 L157,24 L154,33 L153,39 L153,41 L152,47 L152,49 L151,58 L150,65 L150,67 L150,73 L149,80 L149,81 L149,87 L149,88 L149,93 L149,98 L149,99 L149,106 L149,109 L149,111 L149,114 L149,118 L149,119 L149,120 L149,124 L149,128 L149,129 L149,132 L149,134 L148,136 L147,137 L147,138 L147,140 L146,142 L146,143 L145,144 L144,148 L144,149 L143,153 L142,153 L140,158 L140,159 L140,160 L140,161 L140,164 L140,167 L141,169 L141,170 L141,171 L142,173 L142,174 L143,175 L143,177 L145,179 L146,179 L149,181 L150,181 L153,183 L157,185 L161,188 L162,188 L165,190 L169,193 L174,195 L176,196 L176,197 L177,197 L180,199 L181,199 L182,200 L184,201 L186,202 L188,203 L189,204 L192,205 L192,206 L196,207 L200,209 L202,210 L208,210 L209,210 L214,210 L215,210 L216,210 L220,210 L221,210 L222,210 L225,211 L226,212 L227,213 L233,217 L234,217 L237,218 L238,220 L239,220 L239,221 L242,223 L244,224 L248,225 L249,226 L254,228 L255,228 L256,228 L256,229 L256,230 L256,231 L257,231 L259,235 L259,236 L264,240 L264,241 L265,241 L270,245 L271,245 L275,248 L276,249 L280,252 L280,253 L282,256 L283,257 L284,258 L285,260 L286,260 L287,262 L291,264 L293,266 L295,268 L296,268 L296,269 L298,272 L299,272 L299,273 L301,275 L301,276 L303,277 L303,278 L303,279 L303,281 L303,282 L303,283 L303,287 L303,290 L303,291 L303,292 L303,293 L303,294 L302,299 L302,300 L302,301 L301,305 L300,305 L300,306 L300,307 L300,308 L300,309 L298,313 L297,314 L295,318 L295,319 L292,322 L290,326 L290,327 L289,327 L290,328 L291,330 L292,330 L296,335 L297,335 L300,338 L302,340 L303,340 L304,341 L305,341 L306,342 L308,343 L310,345 L313,347 L315,349 L316,349 L316,350 L318,352 L318,354 L319,356 L319,358 L320,360 L320,361 L321,365 L322,370 L323,371 L324,377 L324,378 L326,383 L327,389 L328,391 L330,398 L330,399 L330,400 L330,401 L330,403 L331,409 L331,410 L331,418 L331,420 L331,427 L331,428 L331,432 L331,433 L330,433 L326,431 L323,430 L319,429 L318,428 L317,427 L316,427 L315,427 L314,427 L305,427 L301,427 L287,427 L285,426 L273,425 L272,425 L267,424 L266,424 L265,424 L264,424 L258,424 L255,424 L244,425 L241,426 L228,427 L226,427 L215,428 L213,428 L211,428 L201,428 L199,428 L190,430 L188,430 L176,430 L174,430 L163,430 L161,430 L150,430 L148,430 L138,430 L136,431 L126,431 L118,431 L117,431 L115,431 L113,431 L106,430 L105,430 L100,430 L99,430 L95,429 L94,429 L94,428 L90,428 L90,427 L88,427 L87,427 L85,426 L84,426 L83,426 L82,426 L81,426 L80,426 L76,425 L76,424 L75,424 L74,424 L72,424 L68,423 L67,423 L66,423 L65,423 L64,423 L63,423 L56,423 L55,423 L54,423 L53,423 L52,423 L51,423 L50,423 L49,423 L48,423 L47,423 L45,423 L43,423 L42,423 L41,423 L40,423 L39,423 L38,423 L33,423 L32,423 L31,423 L30,423 L29,423 L26,423 L23,423 L22,423 L20,423 L19,423 L18,423 L17,423 L15,424 L14,424 L13,424 L11,423 L10,423 L9,423 L8,423 L8,422 L8,421 L8,418 L8,417 L7,413 L7,412 L7,411 L7,409 L7,408 L7,406 L7,405 L7,404 L7,403 L7,401 L7,400 L7,399 L7,397 L7,396 L7,395 L7,394 L8,389 L8,388 L8,387 L8,386 L8,381 L8,380 L8,378 L8,377 L8,376 L8,375 L9,370 L9,368 L9,367 L9,366 L9,365 L8,364 L8,363 L8,361 L7,356 L7,355 L7,354 L7,353 L7,348 L7,347 L7,345 L7,344 L7,343 L7,342 L7,341 L7,339 L7,338 L7,336 L7,334 L7,333 L7,332 L7,330 L7,324 L7,323 L7,322 L7,320 L7,318 L7,317 L7,315 L7,312 L7,311 L7,309 L7,307 L7,306 L7,305 L7,303 L7,301 L7,300 L7,298 L7,296 L7,294 L7,292 L8,288 L8,286 L9,284 L9,282 L9,277 L9,276 L9,271 L9,270 L9,269 L8,265 L8,264 L8,262 L7,261 L7,260 L7,259 L7,258 L7,255 L6,254 L6,253 L6,251 L6,250 L5,248 L5,246 L5,245 L4,244 L4,241 L4,236 L4,234 L4,231 L4,230 L4,228 L4,226 L4,223 L4,213 L4,210 L4,208 L4,207 L4,205 L4,202 L4,201 L4,199 L4,198 L4,196 L4,194 L4,193 L4,192 L4,191 L4,190 L4,189 L3,186 L3,185 L3,184 L3,183 L3,182 L4,180 L4,179 L4,177 L4,174 L5,172 L5,170 L5,167 L5,166 L5,165 L6,164 L6,162 L6,159 L6,157 L6,156 L6,155 L6,154 L6,152 L6,151 L6,149 L6,148 L6,147 L6,146 L6,145 L6,142 L6,141 L6,139 L6,138 L6,136 L7,135 L7,134 L7,132 L7,131 L8,128 L8,127 L8,124 L8,123 L9,121 L9,118 L10,115 L10,113 L11,111 L11,110 L11,108 L11,107 L11,105 L11,104 L11,103 L11,100 L11,99 L11,82 L11,81 L11,79 L11,77 L11,76 L11,75 L11,74 L11,72 L11,71 L11,70 L11,68 L11,67 L11,66 L11,64 L11,63 L11,61 L11,60 L11,59 L11,58 L11,56 L11,54 L11,53 L11,51 L11,50 L11,49 L11,47 L11,46 L11,43 L11,42 L10,40 L10,39 L10,38 L10,37 L10,36 L10,35 L10,33 L9,30 L9,29 L9,26 L9,25 L9,24 L9,23 L9,21 L9,19 L8,18 L8,17 L8,16 L8,15 L8,13 L7,12 L7,11 L6,10 L6,9 L6,8 L5,7 L5,6 L5,5 L5,4 L5,3 Z";
    var R = "M635,14 L631,17 L628,21 L627,21 L622,25 L617,29 L610,33 L606,36 L605,37 L600,39 L599,40 L597,42 L596,42 L593,44 L589,48 L584,51 L581,53 L578,54 L578,55 L577,56 L576,56 L574,57 L574,58 L571,59 L570,60 L567,61 L567,62 L564,64 L563,64 L561,65 L560,66 L558,67 L557,68 L555,69 L553,70 L552,71 L550,73 L549,74 L547,76 L544,77 L544,78 L543,78 L542,78 L541,79 L539,79 L538,79 L536,79 L535,79 L534,79 L532,79 L531,79 L530,79 L528,80 L527,80 L524,80 L521,80 L520,80 L517,80 L516,81 L515,81 L513,81 L509,81 L504,81 L501,81 L499,81 L498,81 L497,81 L496,81 L495,81 L494,81 L491,81 L488,81 L487,81 L484,81 L482,81 L481,81 L478,82 L474,83 L470,83 L469,84 L467,85 L466,85 L466,86 L465,86 L461,88 L459,89 L458,90 L455,92 L453,93 L453,94 L452,95 L451,95 L451,96 L450,96 L450,97 L448,98 L448,99 L447,99 L447,100 L445,101 L445,102 L444,102 L443,105 L442,105 L441,107 L441,109 L439,111 L439,112 L439,113 L438,113 L438,114 L438,115 L438,116 L438,118 L438,119 L438,121 L438,122 L438,124 L438,125 L438,127 L438,128 L439,130 L439,131 L440,131 L440,132 L440,134 L440,135 L440,136 L440,138 L440,139 L440,140 L440,142 L440,143 L440,145 L440,147 L439,148 L439,149 L438,151 L435,153 L433,154 L432,155 L431,155 L430,155 L428,156 L426,158 L425,158 L423,160 L422,160 L421,161 L421,162 L420,163 L420,164 L420,166 L420,167 L419,168 L418,170 L418,171 L417,172 L417,173 L417,174 L416,177 L415,181 L414,183 L413,185 L412,186 L412,187 L412,188 L412,190 L412,193 L413,195 L413,196 L414,196 L414,197 L415,199 L415,200 L416,201 L417,201 L421,203 L422,203 L425,205 L426,205 L428,206 L430,206 L430,207 L431,207 L431,208 L431,210 L431,212 L431,214 L431,215 L431,216 L431,218 L431,219 L430,221 L429,223 L428,224 L426,227 L423,230 L420,233 L420,234 L419,234 L418,237 L417,237 L416,239 L415,240 L414,242 L413,243 L413,244 L412,244 L412,245 L410,247 L410,248 L409,248 L409,249 L407,251 L404,254 L403,255 L402,256 L402,257 L401,257 L401,258 L401,259 L400,259 L400,260 L399,261 L397,266 L395,268 L394,270 L392,274 L391,274 L390,275 L389,276 L389,277 L389,278 L389,280 L388,282 L388,283 L387,285 L386,286 L386,288 L385,288 L384,289 L383,289 L383,290 L383,292 L383,295 L383,296 L384,299 L384,300 L385,302 L386,302 L387,304 L387,305 L388,305 L390,308 L391,309 L392,309 L394,312 L394,313 L394,314 L395,316 L395,317 L396,318 L397,319 L398,321 L399,324 L400,324 L401,326 L402,327 L403,328 L403,329 L403,330 L404,332 L405,334 L405,335 L406,336 L406,337 L407,340 L407,341 L409,345 L409,346 L411,349 L412,349 L412,350 L413,350 L413,351 L415,351 L417,351 L417,352 L424,355 L425,355 L427,355 L428,356 L429,356 L430,356 L431,356 L433,356 L434,356 L438,356 L439,356 L440,356 L441,356 L442,356 L447,356 L448,356 L451,356 L452,356 L454,356 L455,356 L456,356 L457,356 L461,356 L462,356 L463,356 L464,356 L465,356 L469,356 L475,357 L476,357 L478,357 L479,357 L486,358 L487,359 L491,360 L492,360 L493,361 L494,361 L495,361 L496,362 L498,362 L499,362 L500,363 L504,364 L505,364 L505,365 L505,366 L505,367 L508,368 L510,369 L514,370 L515,370 L516,370 L517,371 L518,371 L525,374 L526,374 L527,374 L529,375 L530,376 L531,376 L532,377 L540,379 L542,380 L544,381 L546,382 L548,382 L551,383 L554,384 L557,385 L560,386 L563,387 L566,388 L584,394 L588,395 L590,395 L592,396 L595,396 L597,397 L599,397 L600,398 L601,398 L605,400 L606,400 L607,401 L608,401 L609,401 L611,402 L613,403 L615,403 L616,404 L617,404 L618,404 L619,405 L623,407 L625,408 L626,408 L627,409 L630,411 L631,411 L631,412 L632,412 L633,413 L634,413 L635,414 L636,415 L637,416 L636,416 L636,415 L636,411 L636,410 L636,406 L637,393 L637,389 L638,384 L638,382 L638,380 L639,377 L639,375 L639,374 L639,372 L639,365 L637,360 L637,359 L636,358 L635,353 L634,352 L634,351 L633,349 L633,348 L633,347 L633,346 L632,345 L632,344 L631,344 L631,342 L631,341 L631,339 L631,338 L631,337 L631,336 L631,335 L631,334 L631,324 L631,322 L631,319 L631,317 L631,316 L631,315 L631,313 L631,312 L631,304 L631,299 L631,297 L631,294 L631,290 L631,289 L631,286 L631,285 L631,282 L631,278 L632,277 L632,273 L632,269 L632,267 L633,263 L633,261 L633,259 L633,257 L633,253 L633,251 L633,250 L633,247 L633,246 L633,244 L633,242 L633,240 L633,236 L633,234 L633,232 L633,231 L633,229 L633,227 L633,225 L633,222 L633,220 L633,219 L633,217 L633,214 L633,213 L633,211 L633,210 L633,209 L633,207 L633,204 L633,202 L633,201 L633,200 L633,198 L633,196 L633,195 L633,193 L633,189 L633,187 L633,185 L633,183 L633,182 L633,178 L633,177 L633,175 L633,173 L632,172 L631,169 L631,166 L631,164 L631,160 L631,156 L632,151 L632,149 L633,148 L633,144 L633,142 L633,141 L633,140 L633,137 L633,136 L633,129 L633,128 L633,126 L633,123 L633,121 L633,119 L632,118 L632,116 L632,115 L632,111 L631,109 L631,107 L631,101 L631,96 L631,93 L631,89 L631,87 L631,83 L631,82 L630,80 L630,78 L630,77 L630,76 L630,74 L630,73 L630,71 L630,69 L630,68 L630,67 L630,66 L630,65 L630,64 L630,63 L630,62 L630,60 L630,59 L630,57 L630,55 L630,54 L630,53 L630,51 L630,43 L630,40 L630,39 L630,38 L630,37 L630,36 L630,35 L630,34 L630,32 L630,31 L630,30 L630,29 L630,26 L630,25 L630,24 L630,23 L630,22 L631,21 L632,20 L632,19 L633,18 Z";
    var M = "M146,125 L145,124 L145,119 L146,118 L148,106 L151,94 L152,86 L152,83 L153,75 L155,62 L155,54 L155,49 L155,47 L156,41 L156,39 L158,32 L158,30 L159,26 L160,21 L161,17 L162,14 L162,13 L162,8 L162,6 L162,5 L162,4 L162,3 L162,2 L162,1 L163,0 L164,0 L168,0 L175,0 L178,1 L189,1 L199,1 L200,1 L206,1 L207,1 L214,1 L223,1 L231,1 L238,1 L244,1 L252,1 L259,1 L261,1 L273,1 L285,1 L294,1 L303,1 L310,1 L312,1 L313,1 L314,1 L320,2 L321,2 L329,3 L337,4 L338,4 L359,5 L362,5 L364,5 L378,5 L381,5 L388,5 L403,5 L405,5 L407,5 L414,5 L420,5 L421,5 L424,5 L425,5 L435,5 L436,5 L438,5 L440,5 L442,5 L451,5 L453,5 L463,5 L466,5 L478,5 L485,5 L487,5 L491,5 L493,5 L494,5 L495,5 L503,4 L512,4 L520,4 L530,4 L536,4 L537,4 L542,4 L546,4 L551,4 L556,4 L561,5 L563,5 L568,5 L569,5 L574,4 L582,4 L584,4 L588,3 L590,3 L598,3 L599,2 L601,2 L602,1 L604,1 L609,1 L614,1 L615,1 L615,2 L615,4 L611,7 L609,9 L599,16 L588,24 L579,31 L572,38 L565,45 L561,51 L555,57 L553,58 L549,62 L547,65 L545,67 L544,69 L544,70 L543,71 L542,72 L542,73 L541,74 L541,75 L540,75 L539,76 L538,78 L537,79 L536,79 L534,79 L531,79 L527,79 L526,79 L520,79 L516,79 L512,79 L508,79 L507,79 L502,79 L498,79 L494,79 L493,79 L488,80 L487,80 L486,80 L482,80 L479,80 L476,81 L472,83 L471,83 L469,83 L466,84 L465,85 L459,87 L457,87 L456,88 L454,89 L453,90 L451,91 L449,94 L448,94 L448,95 L447,95 L447,96 L445,98 L444,99 L443,101 L441,104 L440,105 L439,107 L439,108 L437,111 L436,113 L436,114 L436,116 L436,117 L436,122 L436,124 L436,125 L436,130 L436,131 L437,134 L437,136 L437,137 L437,138 L437,140 L437,144 L437,147 L437,148 L437,149 L437,151 L437,153 L436,155 L434,157 L433,157 L433,158 L432,158 L429,161 L428,161 L426,163 L425,164 L423,166 L421,168 L421,169 L420,171 L419,172 L418,174 L417,175 L416,177 L416,178 L416,181 L416,183 L416,184 L415,187 L415,189 L415,190 L414,191 L414,192 L414,194 L414,196 L416,199 L416,200 L419,204 L420,204 L421,205 L424,206 L427,207 L429,208 L430,209 L431,211 L431,213 L431,214 L432,215 L432,216 L432,217 L432,218 L432,219 L432,220 L432,221 L432,222 L432,223 L432,225 L432,226 L432,227 L432,228 L431,229 L429,232 L428,233 L426,236 L425,237 L424,237 L423,239 L422,239 L422,240 L421,240 L421,241 L420,241 L419,241 L419,242 L418,242 L418,243 L417,244 L416,245 L413,248 L412,248 L412,249 L411,250 L410,251 L409,251 L409,252 L407,254 L407,255 L406,256 L404,257 L403,260 L401,261 L400,263 L396,268 L395,269 L395,270 L394,272 L393,273 L391,276 L390,277 L389,279 L387,282 L387,283 L387,284 L386,285 L386,286 L385,287 L384,288 L384,289 L384,290 L383,291 L383,294 L383,295 L383,296 L383,297 L383,298 L385,302 L385,303 L387,307 L387,308 L388,309 L388,311 L389,311 L390,313 L391,315 L391,316 L392,316 L393,318 L393,319 L393,320 L394,321 L395,324 L395,325 L397,328 L397,329 L398,330 L399,331 L400,333 L403,335 L403,336 L404,336 L405,338 L406,338 L407,338 L407,339 L408,339 L408,340 L410,341 L411,342 L411,343 L412,343 L413,344 L414,344 L414,345 L415,346 L417,347 L422,349 L423,349 L425,349 L426,349 L428,349 L431,349 L432,349 L434,349 L435,349 L437,349 L438,349 L440,349 L441,350 L464,354 L466,354 L468,355 L472,355 L473,355 L476,357 L478,357 L480,358 L481,359 L482,359 L484,359 L485,359 L486,360 L487,360 L488,360 L490,361 L491,361 L492,361 L493,362 L495,363 L497,363 L498,364 L500,364 L501,365 L503,366 L505,366 L507,367 L508,367 L510,367 L511,368 L513,368 L520,371 L521,371 L523,372 L526,373 L529,374 L531,375 L533,375 L535,376 L536,377 L538,377 L540,377 L541,378 L543,379 L545,379 L548,381 L549,382 L551,383 L552,384 L553,384 L555,385 L556,385 L557,386 L559,386 L559,387 L560,387 L561,387 L562,388 L565,389 L565,390 L566,390 L569,392 L570,392 L571,392 L577,396 L581,397 L583,398 L585,398 L586,399 L587,399 L588,399 L591,401 L592,402 L593,403 L595,404 L599,407 L600,407 L601,408 L602,409 L603,409 L605,410 L606,411 L607,411 L608,412 L612,414 L613,414 L614,415 L616,416 L617,417 L618,417 L619,417 L620,418 L621,418 L621,419 L622,419 L623,419 L623,420 L624,420 L626,421 L626,422 L626,423 L625,423 L619,425 L616,426 L614,426 L612,427 L609,428 L607,428 L605,428 L600,429 L586,430 L584,430 L582,430 L579,430 L576,430 L572,430 L570,430 L563,430 L560,430 L557,430 L554,430 L547,430 L540,430 L537,430 L531,431 L528,431 L525,431 L522,431 L519,432 L516,432 L509,432 L505,432 L501,432 L498,432 L491,432 L488,433 L485,433 L478,433 L475,433 L471,433 L467,433 L463,433 L456,433 L453,433 L450,433 L447,433 L444,433 L438,433 L435,433 L431,433 L429,433 L426,433 L419,433 L416,433 L413,433 L408,433 L406,432 L404,432 L402,432 L401,432 L383,432 L381,432 L378,432 L377,432 L376,432 L375,431 L374,430 L373,430 L373,429 L372,429 L370,429 L367,428 L364,427 L352,425 L350,425 L349,425 L348,425 L346,425 L345,425 L338,424 L337,424 L335,424 L330,424 L329,424 L326,424 L324,423 L323,423 L320,423 L319,423 L318,423 L315,423 L311,423 L311,422 L313,418 L313,416 L313,414 L313,411 L313,400 L313,397 L313,393 L313,391 L313,388 L313,387 L313,386 L313,384 L313,382 L314,381 L314,380 L314,379 L314,377 L314,376 L314,375 L315,375 L315,374 L315,373 L316,371 L316,370 L316,369 L317,366 L318,363 L318,362 L318,361 L318,359 L318,358 L318,357 L318,356 L318,355 L318,354 L317,352 L316,350 L316,349 L315,349 L312,348 L311,347 L310,347 L309,346 L309,345 L307,345 L306,344 L304,342 L302,341 L301,340 L299,339 L297,338 L296,338 L296,337 L294,336 L293,335 L292,334 L291,332 L291,331 L290,331 L289,330 L289,329 L290,325 L290,323 L292,321 L293,319 L294,317 L295,316 L295,315 L296,313 L297,311 L298,310 L298,308 L299,307 L301,300 L301,299 L302,297 L302,296 L303,295 L303,293 L303,292 L303,290 L303,285 L303,282 L303,281 L303,280 L303,279 L303,274 L303,273 L302,271 L302,269 L301,266 L299,264 L295,256 L292,255 L290,253 L289,252 L288,252 L287,251 L286,250 L285,250 L284,249 L283,249 L281,248 L280,248 L278,247 L277,247 L276,247 L274,247 L273,247 L272,247 L270,247 L269,247 L267,247 L266,247 L265,246 L264,246 L264,245 L263,245 L261,243 L260,242 L258,240 L256,237 L255,236 L255,235 L254,234 L252,233 L252,231 L251,230 L250,230 L248,228 L247,228 L247,227 L246,227 L245,227 L243,226 L241,225 L240,225 L238,224 L236,223 L234,222 L232,220 L231,219 L229,218 L228,217 L227,216 L226,215 L225,214 L225,212 L224,211 L223,210 L222,209 L221,209 L214,209 L212,209 L208,209 L206,209 L202,209 L200,209 L193,209 L191,209 L189,208 L187,208 L185,208 L183,207 L182,207 L181,205 L180,204 L178,203 L176,202 L174,201 L173,200 L172,200 L171,199 L169,198 L166,197 L162,195 L159,194 L158,193 L156,192 L155,191 L153,190 L152,189 L151,189 L150,189 L149,188 L148,186 L148,185 L147,184 L146,182 L146,180 L146,179 L145,179 L145,178 L144,177 L142,173 L141,167 L140,165 L140,163 L140,160 L140,158 L141,157 L141,155 L141,154 L141,152 L141,151 L141,150 L141,149 L142,146 L142,145 L142,143 L142,142 L142,141 L142,140 L142,139 L142,137 L142,136 L142,135 L142,134 L143,133 Z";

    var H = { fill: "silver", opacity: 0.5, "stroke-width": 0, transform: "S1.1" };
    var S = { fill: "white", opacity: 0, "stroke-width": 0, transform: "S1.1" };

    var PL = paper.path(L).attr(S);
    var PR = paper.path(R).attr(S);
    var PM = paper.path(M).attr(S);

    function hide(ZR) {
        ZR.stop().animate(S, 300, "<");
    }

    function hide_all() {
        //PL.stop().animate(H, 400, "<>", function() { PL.toBack(); });
        //PM.stop().animate(H, 400, "<>", function() { PM.toBack(); });
        //PR.stop().animate(H, 400, "<>", function() { PR.toBack(); });
    //    PL.attr(S).toBack();
    //    PM.attr(S).toBack();
        //PR.attr(S).toBack();
        hide(PL);
        hide(PM);
        hide(PR);
    }

    PL.toBack();
    PR.toBack();
    PM.toBack();

    function action(XZ) {
        XZ.mouseover(function() {
            hide_all();
            // this.attr(S);
            //this.toFront().stop().animate(S, 400, "<>");
        //    this.attr(H);//.toFront();

            this.stop().animate(H, 300, "<");
        }).mouseout(function() {
        //    this.attr(S);

            //this.stop().animate(S, 300, "<>");
            hide_all();
        });
    }

    action(PL);
    action(PM);
    action(PR);

    //paper.path(L).hide();

}

function draw_legend() {

    var sections = 10;
    var max = 410;

    var R2 = new Rainbow();
    // R2.setSpectrum('white', '#0099CC');
    R2.setSpectrum('white', '#688bb3');  // blue

    R2.setNumberRange(0, sections);

    //paper.rect(50,P_HEIGHT - 50, P_WIDTH - 100, 30);

    var padding = 50;
    var h = 30;
    var w = ((P_WIDTH/1.5) - (padding*2))/sections;
    var y = P_HEIGHT - padding;

    for( var i = 0; i < sections; i++ ) {

        var x = 240 + padding + (i * w);


        var step = max / sections;

        paper.rect(x, y, w, h).attr({
            fill: '#' + R2.colourAt(i)
        });

        // var caption = i + " / " + 410;
        // var caption = (i*step) + "-" + ((i+1)*step);
        var caption = parseInt(i*step);// + "-" + ((i+1)*step);

        paper.text(x + (w/2), y+(h/2), caption);
    }



}

function set_point_range() {
    iterate( function(xy) {
        xy[2].setRange(range);
    });
}

function compute_point_range() {
    iterate( function(xy) {
        if( xy[2].x > range.x.max ) range.x.max = xy[2].x;
        if( xy[2].x < range.x.min ) range.x.min = xy[2].x;
        if( xy[2].y > range.y.max ) range.y.max = xy[2].y;
        if( xy[2].y < range.y.min ) range.y.min = xy[2].y;
    });
}

Raphael(function() {
    paper = Raphael(
        "map",
        P_WIDTH,
        P_HEIGHT
    );

    // mode 1: raw
    // data = <?php echo $car->json() ?>;

    // each point now has [0] -- lat, [1] -- lng, and [2] -- pt object
    iterate( function(xy) {
        xy.push( new pt(xy, false) );
    });

    compute_point_range();
    set_point_range(range);

    iterate( function(xy) {
        xy[2].reset_to_0();
        xy[2].scale_to();
    });

/*console.info(range.x);
console.info(range.y);
console.info( "i am done");*/
//return(false);

    draw_map();
//draw_legend();
/*
    // mode 2: cooked
    // data = <?php echo file_get_contents("cooked/data-cooked.js") ?>;
    draw_map(true);

    // draw_points(); // not cached, pip detection
    draw_points2();  // cached
    update_map();
    draw_lines();
*/
    draw_cities();
    //draw_legend();
//    draw_areas_of_influence();

//var x = new dino_paper_toolkit(paper);

    map_is_finished();
});

function resize_map(new_w, new_h, font_size) {
    styles.size.enforced_width = new_w;
    styles.size.enforced_height = new_h;

    paper.setSize(new_w, new_h);
    paper.setViewBox(0, 0, 700, 470, true);
    for( var i = 0; i < map_labels.length; i++ ) {
        map_labels[i].attr( { "font-size": font_size } );
    }
}
