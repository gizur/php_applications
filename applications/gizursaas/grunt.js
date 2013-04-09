'use strict';

module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    nodeunit: {
      files: ['test/**/*_test.js'],
    },
    
    // Plugin tasks
    //-------------

    doccoh: {
        src: ['*.js', 'app/**/*.js']
    },

    clean: {
        folder: "docs/"
    },
  });

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-exec');
  grunt.loadNpmTasks('grunt-doccoh');
  grunt.loadNpmTasks('grunt-clean');

  // Default task.
  grunt.registerTask('default', ['doccoh']);

};