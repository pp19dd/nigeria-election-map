
function dino_paper_toolkit(paper) {
    this.paper = paper;
    this.path_string = "";
    this.count = 0;

    this.rect = paper.rect(0,0,paper.width, paper.height);
    this.rect.attr({fill: "#eee", opacity:0.3});

    this.anchor = {
        x: 0, y: 0
    }

    this.path = null;
    var that = this;

    this.rect.mousemove(function(e) {
        var o = $(paper.canvas).offset();

        this.x = e.pageX - o.left;
        this.y = e.pageY - o.top;
    });

    this.rect.drag(function(dx, dy) {   // move
        if( this.count == 0 ) {
            this.path_string += "M" + this.x + "," + this.y;

            that.anchor.x = this.x;
            that.anchor.y = this.y;

            this.path = this.paper.path(this.path_string).attr({ "stroke-width": 2, opacity: 0.5 });

        } else {
            this.path_string += " L" + (dx + that.anchor.x) + "," + (dy + that.anchor.y);
            this.path.attr("path", this.path_string);
        }
        this.count++;
    }, function() {                     // start
        this.count = 0;
        this.path_string = "";
    },
    function() {                        //up
        // this.paper.path(this.path_string);
        // console.info( this.path_string);

        console.dir( this.path.getBBox() );
        console.info( this.path_string );
    });

}
