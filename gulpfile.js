// Gulp.js configuration

// Modules
var gulp = require('gulp'),
    concat = require('gulp-concat'),
    rename = require('gulp-rename'),
    terser = require('gulp-terser'),
    csso = require('gulp-csso');

// Development mode?
var devBuild = (process.env.NODE_ENV !== 'production');

// Folders and files
var folders = {
        js: 'resources/assets/js',
        css: 'resources/assets/css',
        dist: 'dist'
    },

    files = {
        javascript: [
            folders.js + '/base.js',
            folders.js + '/home.js',
            folders.js + '/stats.js'
        ],
        stylesheet: [
            folders.css + '/base.css',
            folders.css + '/admin.css',
            folders.css + '/index.css',
            folders.css + '/stats.css'
        ]
    };

// Concat javascript files
gulp.task('js-polr', function() {
	return gulp.src(files.javascript)
        // .pipe(deporder())
        .pipe(concat('polr.js', {newLine: '\n\n'}))
        .pipe(gulp.dest(folders.dist));
});

gulp.task('js', ['js-polr']);

// Minify javascript files
gulp.task('js-polr-min', ['js-polr'], function() {
    return gulp.src(folders.dist + '/polr.js')
        // .pipe(stripdebug())
        .pipe(terser())
        .pipe(rename('polr.min.js'))
        .pipe(gulp.dest(folders.dist));
});

gulp.task('js-min', ['js-polr-min']);

// Minify the jaxon.debug.js file
gulp.task('css', function() {
	return gulp.src(files.stylesheet)
	    .pipe(concat('polr.css', {newLine: '\n\n'}))
	    .pipe(gulp.dest(folders.dist));
});

// Minify the jaxon language files
gulp.task('css-min', ['css'], function() {
    return gulp.src(folders.dist + '/polr.css')
        .pipe(csso())
        .pipe(rename('polr.min.css'))
        .pipe(gulp.dest(folders.dist));
});

// Minify all the files
gulp.task('default', ['js-min', 'css-min']);
