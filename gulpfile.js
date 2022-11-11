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
const run = require('gulp-run'); // run commands

// project
const projectZipFile = 'fv-wordpress-flowplayer.zip';

// translation
const team = 'foliovision'
const textDomain = 'fv-wordpress-flowplayer';
const package = 'fv-wordpress-flowplayer';
const translationFile = 'fv-wordpress-flowplayer.pot';
const bugReport = 'https://foliovision.com/support';

// files to check
const freedomplayerDistCSS = ['./node_modules/freedomplayer/dist/skin/skin.css']
const freedomPlayerDistJS = ['./node_modules/freedomplayer/dist/freedomplayer.min.js']
const freedomPlayerCSS = ['./css/freedomplayer.css', './css/freedomplayer-additions.css'];
const modulesJs = ['./flowplayer/modules/fv-player.js', './flowplayer/modules/*.module.js'];
const youtubeJS = './flowplayer/fv-player-youtube.dev.js';
const dashJS = './flowplayer/fv-player-dashjs.dev.js';
const loaderJS = './flowplayer/fv-player-loader.dev.js'
const projectPHPWatchFiles = ['*.php', './controller/**/*.php', './models/**/*.php', './view/**/*.php'];

function updateBrowserList() {
  return run('npx browserslist@latest --update-db --yes').exec();
}

function updateFreedomPlayerModule() {
  return run('npm install freedomplayer@latest').exec();
}

function copyFreedomPlayerCSS() {
  return src(freedomplayerDistCSS)
  .pipe(rename(function (path) {
    path.basename = "freedomplayer";
    path.extname = ".css";
  }))
    .pipe(dest('./css/'));
}

function copyFreedomPlayerJS() {
  return src(freedomPlayerDistJS)
    .pipe(dest('./flowplayer/'));
}

// concat js files + uglify
function jsModulesMinify() {
  return src(modulesJs)
    .pipe(babel({"presets": [
      ["@babel/preset-env", {"modules": false} ]
    ]}))
    .pipe(concat('fv-player.min.js'))
    .pipe(uglify({mangle: true}).on('error', console.error))
    .pipe(dest('./flowplayer/')
  );
}

function jsFilessMinify() {
  return src([dashJS, youtubeJS, loaderJS])
    .pipe(babel({"presets": [
      ["@babel/preset-env", {"modules": false} ]
    ]}))
    .pipe(uglify({mangle: true}).on('error', console.error))
    .pipe(rename(function (path) {
      path.basename = path.basename.replace(/\.dev/, '');
      path.extname = ".min.js";
    }))
    .pipe(dest('./flowplayer/')
  );
}

function cssFreedomPlayer() {
  return src(freedomPlayerCSS)
    .pipe(concat('freedomplayer.min.css'))
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
exports.browserlist = updateBrowserList;
exports.zip = zipProject;
exports.pot = potFileGenerate;
exports.cssplayer = cssFreedomPlayer;
exports.freedomplayercss = copyFreedomPlayerCSS;
exports.freedomplayerjs= copyFreedomPlayerJS;
exports.freedomplayerupdate = updateFreedomPlayerModule;
exports.jsmodules = jsModulesMinify;
exports.jsfiles = jsFilessMinify;
exports.js = parallel( jsModulesMinify, jsFilessMinify );

exports.dev = series( parallel(jsModulesMinify, jsFilessMinify, cssFreedomPlayer) );

exports.release = series( updateFreedomPlayerModule, updateBrowserList, copyFreedomPlayerJS, copyFreedomPlayerCSS, parallel(jsModulesMinify, jsFilessMinify, cssFreedomPlayer, potFileGenerate), zipProject );