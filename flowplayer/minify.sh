#!/bin/bash
FILE=modules/flowplayer.js
if test -f "$FILE"; then
  cat modules/flowplayer.js > fv-flowplayer.min.dev.js
else 
  cat modules/flowplayer.min.js > fv-flowplayer.min.dev.js
fi
cat modules/fv-player.js >> fv-flowplayer.min.dev.js &&
cat modules/*.module.js >> fv-flowplayer.min.dev.js &&
uglifyjs fv-flowplayer.min.dev.js > fv-flowplayer.min.js &&
rm fv-flowplayer.min.dev.js
