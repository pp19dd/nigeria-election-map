
function candidate() {

}

candidate.prototype.init = function(d_paper, d_div) {
    this.paper = d_paper;
    this.div = d_div;

    this.offset = { };

    this.styles = {
        count_label: { "text-anchor": "end", "font-size": 12, "fill": "#ffffff" },
        first: { "text-anchor": "start", "font-size": 18, "fill": "#333333" },
        last: { "text-anchor": "start", "font-size": 28, "font-weight": "bold", "fill": "#222222" },
        party: { "text-anchor": "start", "font-size": 14, "fill": "#666666" },
        border: { "stroke-width": 3 },
        state: { "stroke": "silver" },
        bar1: { "stroke-width": 0, "fill": "#eeeeee" },
        line: {stroke: 'silver', 'stroke-width': 1}
    };

    this.e = { };

    return(this);
}

candidate.prototype.getOffset = function(label) {
    if( typeof this.offset[label] == "undefined" ) {
        return( { x: 0, y: 0 });
    }
    return( this.offset[label] );
}

candidate.prototype.setOffset = function(label, x, y) {
    this.offset[label] = { x: x, y: y };
    return(this);
}

candidate.prototype.setName = function(first, last, party) {
    this.name = {
        first: first,
        last: last
    }
    this.party = party;
    return(this);
}

candidate.prototype.setVotes = function(num, total) {
    this.votes = num;
    this.total_votes = total;
    return(this);
}

candidate.prototype.setStates = function(num) {
    this.count_states = num;
    return(this);
}

candidate.prototype.setPicture = function(url) {
    this.picture_url = url;
    return(this);
}

candidate.prototype.setRainbowColor = function(c) {
    this.RainbowColor = c;
    return(this);
}

candidate.prototype.draw = function() {
    this.e.label = {
        first: this.paper.text(60,30,this.name.first),
        last:  this.paper.text(60,56,this.name.last),
        party: this.paper.text(60 + this.getOffset("party_label").x, 60, "(" + this.party + ")")
    };

    this.e.label.first.attr(this.styles.first);
    this.e.label.last.attr(this.styles.last);
    this.e.label.party.attr(this.styles.party);

    this.e.picture = this.paper.image(this.picture_url, 12, 24, 40, 40);
    this.e.picture_border = this.paper.rect(12,24,40,40);
    this.e.picture_border.attr(this.styles.border);
    this.e.picture_border.attr({ stroke: this.RainbowColor });

    this.e.states = [];

    var start = 274;
    var swidth = 353;
    var bwidth = 353;

    // required states (25% or more of a vote)
    var NUM_STATES = 38;
    var dx = swidth / NUM_STATES;

    // required: need at least 2/3 states (25) with 25% or more of a vote
    var req_mark = 25;

    for( var i = 0; i < NUM_STATES; i++ ) {
        var x = start + (i * dx);
        var y = 32;
        var r = 3.5;
        this.e.states.push(
            this.paper.circle( x, y, r )
        );
        this.e.states[i].attr(this.styles.state);

        if( i < this.count_states ) {
            this.e.states[i].attr({
                fill: this.RainbowColor,
                stroke: this.RainbowColor
            });
        }

        if( i == req_mark-1) {
            this.e.line = this.paper.path("M" + x + "," + y + "m0,-10 l0,20").attr(this.styles.line);
            this.e.states[i].hide();
        }
    }

    if( this.count_states >= req_mark ) {
        this.e.line.attr( { stroke: this.RainbowColor });
    }

    // vote counts
    this.e.vote_indicator_bgr = this.paper.rect(start - 3.5, 45, bwidth, 20).attr( this.styles.bar1 );

    var v = 100 * (this.votes / this.total_votes);
    var computed_width = (swidth / 100) * v;
    this.e.vote_indicator = this.paper.rect(start - 3.5, 45, 0, 20).attr( this.styles.bar1 ).attr({ fill: this.RainbowColor });
    this.e.vote_indicator.animate({ width: computed_width }, 300, "<>");

    // vote count label
    var count_label = v.toFixed(1);
    this.e.vote_label = this.paper.text( start - 10 + computed_width, 55, count_label + "%").attr( this.styles.count_label );
}

candidate.prototype.resize = function(nw, nh) {
    return(false);

    nh = nh / 4.403508771929825;
    $(this.div).css({ width: nw, height: nh });
    this.paper.setSize(nw, nh);
    paper.setViewBox(0, 0, 535, 115);
}
