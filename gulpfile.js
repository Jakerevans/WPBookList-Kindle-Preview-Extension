/**
Mostly derived from https://bitsofco.de/a-simple-gulp-workflow
npm install gulp
npm install --save-dev gulp-sass
npm install --save-dev gulp-concat
npm install --save-dev gulp-uglify
npm install --save-dev gulp-util
npm install --save-dev gulp-rename
npm install --save-dev gulp-babel
npm install --save-dev gulp-zip
npm install --save-dev del
 */

// First require gulp.
var gulp   = require( 'gulp' ),
	sass   = require( 'gulp-sass' ),
	concat = require( 'gulp-concat' ),
	uglify = require( 'gulp-uglify' ),
	gutil  = require( 'gulp-util' ),
	rename = require( 'gulp-rename' ),
	zip    = require( 'gulp-zip' ),
	del    = require( 'del' );

// Define file sources.
var sassFrontendSource        = [ 'dev/scss/wpbooklist-kindle-main-frontend.scss' ];
var sassFrontendSourcePartial = [ 'dev/scss/_wpbooklist-kindle-frontend-ui.scss' ];
var sassBackendSource         = [ 'dev/scss/wpbooklist-kindle-main-admin.scss' ];
var sassBackendSourcePartial  = [ 'dev/scss/_wpbooklist-kindle-backend-ui.scss' ];
var sassWatch                 = [ 'dev/scss/*.scss' ];
var jsBackendSource           = [ 'dev/js/backend/*.js' ];
var jsFrontendSource          = [ 'dev/js/frontend/*.js' ];
var jsFrontendWatch           = [ 'dev/js/frontend/*.js' ];
var jsBackendWatch            = [ 'dev/js/backend/*.js' ];

// Define default task.
gulp.task( 'default', function() {

});

// Task to compile Frontend SASS file.
gulp.task( 'sassFrontendSource', function() {
	gulp.src( sassFrontendSource )
		.pipe(sass({
			outputStyle: 'compressed'
		})
			.on( 'error', gutil.log ) )
		.pipe(gulp.dest( 'assets/css' ) )
});

// Task to compile Backend SASS file
gulp.task( 'sassBackendSource', function() {
	gulp.src( sassBackendSource )
		.pipe(sass({
			outputStyle: 'compressed'
		})
			.on( 'error', gutil.log) )
		.pipe(gulp.dest( 'assets/css' ) );
});

// Task to concatenate and uglify js files
gulp.task( 'concatAdminJs', function() {
	gulp.src(jsBackendSource ) // use jsSources
		.pipe(concat( 'wpbooklist_kindle_admin.min.js' ) ) // Concat to a file named 'script.js'
		.pipe(uglify() ) // Uglify concatenated file
		.pipe(gulp.dest( 'assets/js' ) ); // The destination for the concatenated and uglified file
});

// Task to concatenate and uglify js files
gulp.task( 'concatFrontendJs', function() {
	gulp.src(jsFrontendSource ) // use jsSources
		.pipe(concat( 'wpbooklist_kindle_frontend.min.js' ) ) // Concat to a file named 'script.js'
		.pipe(uglify() ) // Uglify concatenated file
		.pipe(gulp.dest( 'assets/js' ) ); // The destination for the concatenated and uglified file
});

gulp.task( 'copyassets', function () {
	gulp.src([ './assets/**/*' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-kindlepreview_dist' ) );
});

gulp.task( 'copyincludes', function () {
	gulp.src([ './includes/**/*' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-kindlepreview_dist' ) );
});

gulp.task( 'copyquotes', function () {
	gulp.src([ './quotes/**/*' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-kindlepreview_dist' ) );
});

gulp.task( 'copyconfig', function () {
	gulp.src([ './wpbooklistconfig.ini' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-kindlepreview_dist' ) );
});

gulp.task( 'copyreadme', function () {
	gulp.src([ './readme.txt' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-kindlepreview_dist' ) );
});

gulp.task( 'copylang', function () {
	gulp.src([ './languages/**/*' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-kindlepreview_dist' ) );
});

gulp.task( 'copymainfile', function () {
	gulp.src([ './wpbooklist-kindlepreview.php' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-kindlepreview_dist' ) );
});

gulp.task( 'zip', function () {
	return gulp.src( '../wpbooklist-kindlepreview_dist/**' )
		.pipe(zip( 'wpbooklist-kindlepreview.zip' ) )
		.pipe(gulp.dest( '../wpbooklist-kindlepreview_dist' ) );
});

gulp.task( 'cleanzip', function(cb) {
	del([ '../wpbooklist-kindlepreview_dist/**/*' ], {force: true}, cb);
});

gulp.task( 'clean', function(cb) {
	del([ '../wpbooklist-kindlepreview_dist/**/*', '!../wpbooklist-kindlepreview_dist/wpbooklist-kindlepreview.zip' ], {force: true}, cb);
});

// Task to watch for changes in our file sources
gulp.task( 'watch', function() {
	gulp.watch(sassWatch,[ 'sassFrontendSource', 'sassBackendSource' ]);
	gulp.watch(jsFrontendWatch,[ 'concatFrontendJs' ]);
	gulp.watch(jsBackendWatch,[ 'concatAdminJs' ]);
});

// Default gulp task
gulp.task( 'default', [ 'sassFrontendSource', 'sassBackendSource', 'concatAdminJs', 'concatFrontendJs', 'watch' ]);


//gulp.task( 'default', [ 'cleanzip' ]);

//gulp.task( 'default', [ 'copyassets', 'copyincludes', 'copyquotes', 'copyconfig', 'copyreadme', 'copylang', 'copymainfile' ]);

//gulp.task( 'default', [ 'zip' ]);

//gulp.task( 'default', [ 'clean' ]);