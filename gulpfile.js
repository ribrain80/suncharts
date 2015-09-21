var gulp = require('gulp');
var os   = require('os');
var gulpif = require('gulp-if');
var uglify = require('gulp-uglify');
var minifyCss = require('gulp-minify-css');
var minimist = require('minimist');
var del = require('del');
var fs = require('fs');
var rsync = require('gulp-rsync');

var knownOptions = {
  string: 'env',
  default: { env: process.env.NODE_ENV || 'production' }
};

var options = minimist( process.argv.slice( 2 ), knownOptions );
console.log( options );

gulp.task( 'check', function() {
	console.log( "Running on " + os.hostname() );
	if( options.env == "production" ) {
		fs.createReadStream('.env.prod').pipe( fs.createWriteStream('.env') );
	}
});

gulp.task( 'clean', ['check'], function () {
  return del([ 'css/**/*']);
});

gulp.task( 'cssmin', ['clean'], function() {

  return gulp.src( 'source/css/*.css' )
    .pipe(minifyCss()) // only minify in production
    .pipe(gulp.dest('css'));
});

gulp.task('deploy', function() {

  var file = fs.readFileSync('rsync-excludelist', "utf8");
  var arr = file.split("\n");
  arr = arr.map(function (val) { return '!' + val; });

  gulp.src(process.cwd())
    .pipe( rsync( {
      recursive: true,
      destination: '../rsync-test/suncharts',
      progress: true,
      incremental: true,
      exclude: arr
  } ));

});

gulp.task( 'compile', [ 'check', 'clean', 'cssmin', 'deploy' ] );