cat modules/fv-flowplayer.min.js > fv-flowplayer.min.dev.js &&
cat modules/*.module.js >> fv-flowplayer.min.dev.js &&
uglifyjs fv-flowplayer.min.dev.js > fv-flowplayer.min.js &&
rm fv-flowplayer.min.dev.js