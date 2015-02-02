<!doctype html>
<html>
<head>


<script src="jquery.min.js"></script>

</head>

<body>
<?php
$url = "http://car-map.pp19dd.com/?inline";
// {assign var="mapurl" value="http://www.voanews.com/MediaAssets2/projects/car-diamonds/car-map-700x470.04.html"}

?>

<iframe id="id_car_map_iframe" style="margin:0 auto; display: block;" width="700" height="470" scrolling="no" frameborder="0"></iframe>

<script type="text/javascript">

function map_resize_compute() {
    // original size
    var ox = 700; var oy = 470;

    // current window width
    var ww = $(window).width();
    
    // estimated new map width
    var nw = ww - 150;
    
    // compute height based on w.
    var nh = parseInt((oy * nw) / ox);
    
    // initial font size
    var fs = 12;
    
    if( nw < 600 ) fs = 16;
    if( nw < 500 ) fs = 20;
    if( nw < 400 ) fs = 25;
    if( nw < 300 ) fs = 30;
    if( nw < 200 ) fs = 40;
    
    // can't go over original w+h
    if( nw > ox ) nw = ox;
    if( nh > oy ) nh = oy;
    
    return({
        nw: nw, nh: nh, fs: fs
    });
}

function map_resize() {
    
    var parsed = map_resize_compute();

    // apply new sizes
    $("#id_car_map_iframe").width(parsed.nw);
    $("#id_car_map_iframe").height(parsed.nh);
    // $("#id_car_map_iframe").attr( "src", "{$mapurl}?width=" + parsed.nw + "&height=" + parsed.nh + "&fontsize=" + parsed.fs);
    $("#id_car_map_iframe").attr( "src", "<?php echo $url ?>#width=" + parsed.nw + ",height=" + parsed.nh + ",fontsize=" + parsed.fs);
}


$(window).resize( function() {
    map_resize();
});

$(document).ready(function() {
    map_resize();
});
</script>

</body>
</html>
