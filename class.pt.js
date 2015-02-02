// ===========================================================================
// point abstraction routines - requires a global 'range' and 'style'
// ===========================================================================

function pt(init_xy, skip) {

    // default mode: array [0, 1], convert to pixels, scale

    this.xy = [init_xy[0], init_xy[1]];

    this.convert();

    if( typeof skip == "undefined" ) {
        this.reset_to_0();
        this.scale_to();
    }

}

pt.prototype.convert = function() {
    var temp = proj4("WGS84", "GOOGLE", this.xy );

    this.x = parseFloat(temp[0]);
    this.y = -parseFloat(temp[1]);
}

pt.prototype.setRange = function(r) {
    this.range = r;
}

pt.prototype.reset_to_0 = function() {

    // ok i apparently lied about that one global
    /*var range = {
        x:{
            min:1605238.318961174,
            max:3057214.0264582178,
            delta:1451975.7074970438
        },
        y:{
            min:-1232965.304060658,
            max:-247248.50170064784,
            delta:985716.8023600101
        }
    }*/
/*
    // preserve for later
    this.original = {
        x: this.x,
        y: this.y
    }
*/
    this.x -= range.x.min;
    this.y -= range.y.min;
}

// cheating: eyeballed width, height
pt.prototype.scale_to = function() {
    this.x = this.x / 2200;  this.y = this.y / 2200;
    //this.x = this.x / 2;  this.y = this.y / 2;
}
