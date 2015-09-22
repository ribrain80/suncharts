var gulp = require('gulp');
var os   = require('os');
var gulpif = require('gulp-if');
var uglify = require('gulp-uglify');
var minifyCss = require('gulp-minify-css');
var minimist = require('minimist');
var del = require('del');
var fs = require('fs');
var rsync = require('gulp-rsync');
var git = require('gulp-git');
var todo = require('gulp-todo');

var knownOptions = {
  string: 'env',
  default: { env: process.env.NODE_ENV || 'production' }
};

var macchinaPonte = os.hostname() == 'riccardo-Latitude-E6430';
var options = minimist( process.argv.slice( 2 ), knownOptions );
options.branch = undefined == options.branch ? 'develop' : options.branch;

gulp.task( 'check', function() {
  
  console.log( "Running on " + os.hostname() );
  console.log( options );
  if( !macchinaPonte ) {
    console.log( "NON SEI SULLA MACCHINA PONTE!");
    return false;
  }
  
  // if( options.env == "production" ) {
  //  fs.createReadStream('.env.prod').pipe( fs.createWriteStream('.env') );
  // }
});

// generate a todo.md from your javascript files 
// gulp.task('todo', function() {
//     gulp.src('js/**/*.js')
//         .pipe(todo())
//         .pipe(gulp.dest('./')); // -> Will output a TODO.md with your todos 
// });

gulp.task( 'git', [ 'check' ], function() {

  git.checkout( options.branch, function (err) {
    if (err) throw err;
  });

  git.pull('origin', options.branch, {}, function (err) {
    if (err) throw err;
  });
});

gulp.task( 'clean', ['git'], function () {
  return del([ 'css/**/*']);
});

gulp.task( 'cssmin', ['clean'], function() {

  return gulp.src( 'source/css/*.css' )
    .pipe(minifyCss()) // only minify in production
    .pipe(gulp.dest('css'));
});

gulp.task('sync', ['cssmin'], function() {

  var file = fs.readFileSync('rsync-excludelist', "utf8");
  var arr = file.split("\n");

  gulp.src( process.cwd() )
    .pipe( rsync( {
      recursive: true,
      destination: '../rsync-test/suncharts',
      progress: true,
      incremental: true,
      exclude: arr
  } ));

});

gulp.task( 'pull', [ 'git' ] );
gulp.task( 'compile', [ 'check', 'pull', 'clean', 'cssmin' ] );
gulp.task( 'deploy', [ 'check', 'pull', 'clean', 'cssmin', 'sync' ] );