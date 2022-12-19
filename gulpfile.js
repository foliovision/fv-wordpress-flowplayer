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
const projectZipFile = 'fv-wordpress-flowplayer.zip';

// translation
const team = 'foliovision'
const textDomain = 'fv-wordpress-flowplayer';
const package = 'fv-wordpress-flowplayer';
const translationFile = 'fv-wordpress-flowplayer.pot';
const bugReport = 'https://foliovision.com/support';

// files to check
// const cssFrotend = ['./css/flowplayer.css', './css/fancybox.css', './css/lightbox.css', './css/colorbox.css'];
// const cssAdmin = ['./css/admin.css', './css/s3-browser.css', './css/s3-uploader.css'];
const modulesJs = ['./flowplayer/modules/fv-player.js', './flowplayer/modules/*.module.js'];
const devJS = [ './flowplayer/fv-player-youtube.dev.js', './flowplayer/fv-player-loader.dev.js']
const projectPHPWatchFiles = ['*.php', './controller/**/*.php', './models/**/*.php', './view/**/*.php'];

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
  return src(devJS)
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
    // "!vendor{,/**}",
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
exports.jsmodules = jsModulesMinify;
exports.jsfiles = jsFilessMinify;
exports.js = parallel( jsModulesMinify, jsFilessMinify );

exports.default = series( parallel(jsModulesMinify, jsFilessMinify, potFileGenerate) , zipProject )