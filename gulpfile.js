/* eslint-env es6 */
/* eslint-disable */

const { src, series, parallel, dest, watch } = require('gulp');
const babel = require('gulp-babel'); // es6 -> es5
const uglify = require('gulp-uglify'); // js minify
const concat = require('gulp-concat'); // concat to single file
const rename = require("gulp-rename"); // rename files
const autoprefixer = require( 'gulp-autoprefixer' ); // css prefixing
const cleanCSS = require('gulp-clean-css'); // minify css
const wpPot = require('gulp-wp-pot'); // for generating the .pot file.
const sort = require('gulp-sort'); // recommended to prevent unnecessary changes in pot-file.
const zip = require('gulp-zip'); // zip project

// project
const projectZipFile = 'fv-player.zip';

// translation
const team = 'foliovision'
const textDomain = 'fv-player';
const package = 'fv-wordpress-flowplayer';
const translationFile = 'fv-player.pot';
const bugReport = 'https://foliovision.com/support';

// files to check
// const cssFrotend = ['./css/flowplayer.css', './css/fancybox.css', './css/lightbox.css', './css/colorbox.css'];
// const cssAdmin = ['./css/admin.css', './css/s3-browser.css', './css/s3-uploader.css'];
const freedomPlayerCSS = ['./css/skin.css', './css/fv-player-additions.css'];
const modulesJs = ['./freedom-video-player/modules/fv-player.js', './freedom-video-player/modules/*.module.js'];
const youtubeJS = './freedom-video-player/fv-player-youtube.dev.js';
const dashJS = './freedom-video-player/fv-player-dashjs.dev.js';
const loaderJS = './freedom-video-player/fv-player-loader.dev.js'
const projectPHPWatchFiles = ['*.php', './controller/**/*.php', './models/**/*.php', './view/**/*.php'];

// concat js files + uglify
function jsModulesMinify() {
  return src(modulesJs)
    .pipe(babel({"presets": [
      ["@babel/preset-env", {"modules": false} ]
    ]}))
    .pipe(concat('fv-player.min.js'))
    .pipe(uglify({ compress: { loops: false }, mangle: true}).on('error', console.error))
    .pipe(dest('./freedom-video-player/')
  );
}

function jsFilessMinify() {
  return src([dashJS, youtubeJS, loaderJS])
    .pipe(babel({"presets": [
      ["@babel/preset-env", {"modules": false} ]
    ]}))
    .pipe(uglify({ compress: { loops: false }, mangle: true}).on('error', console.error))
    .pipe(rename(function (path) {
      path.basename = path.basename.replace(/\.dev/, '');
      path.extname = ".min.js";
    }))
    .pipe(dest('./freedom-video-player/')
  );
}

function cssFreedomPlayer() {
  return src(freedomPlayerCSS)
    .pipe(concat('fv-player.min.css'))
    .pipe(autoprefixer())
    .pipe(cleanCSS())
    .pipe(dest('./css/'));
}

// generate .pot file
function potFileGenerate() {
  return src(projectPHPWatchFiles)
    .pipe(sort())
    .pipe(wpPot( {
      domain: textDomain,
      package: package,
      bugReport: bugReport,
      lastTranslator: "",
      team: team,
      headers: false
    } ))
    .pipe(dest('./languages/' + translationFile ))
}

// compresss plugin into zip file , exclude using !
function zipProject() {
  return src([
    "**/*",
    "!node_modules{,/**}",
    // "!vendor{,/**}", // keep vendor
    "!test{,**}",
    "!.*",
    "!gulpfile.js",
    "!package.json",
    "!package-lock.json",
    "!composer.json",
    "!composer.lock"
    ])
    .pipe(zip(projectZipFile))
    .pipe(dest('./dist'));
};

// export tasks
exports.zip = zipProject;
exports.pot = potFileGenerate;
exports.css = cssFreedomPlayer;
exports.jsmodules = jsModulesMinify;
exports.jsfiles = jsFilessMinify;
exports.js = parallel( jsModulesMinify, jsFilessMinify );

exports.default = parallel(jsModulesMinify, jsFilessMinify, cssFreedomPlayer, potFileGenerate);
