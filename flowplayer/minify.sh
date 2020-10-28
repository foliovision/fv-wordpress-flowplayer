#!/bin/bash
cat modules/fv-player.js > fv-player.min.dev.js &&
cat modules/*.module.js >> fv-player.min.dev.js &&
uglifyjs fv-player.min.dev.js > fv-player.min.js &&
rm fv-player.min.dev.js
