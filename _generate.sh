#!/bin/bash

function crunch() {
    v=$1
    w=$2
    h=$3

    wget \
	"http://car-map.pp19dd.com/?inline&width=${w}&height=${h}" \
	-O car-map-${w}x${h}.${v}.html
}

version="03"
crunch ${version} 700 470
# crunch ${version} 300 202


