module.exports = function(grunt) {
    "use strict";
    // Project configuration.
    grunt.initConfig({
        nodeunit: {
            files: ['test/**/*_test.js']
        },
        // Plugin tasks
        //-------------
        doccoh: {
            src: ['*.js', 'app/**/*.js', 'config/*.js']
        },
        lint: {
            src: ['app.js', 'app/**/*.js', 'config/*.js']
        },
        clean: {
            folder: "docs/"
        }
    });

    // These plugins provide necessary tasks.
    grunt.loadNpmTasks('grunt-exec');
    grunt.loadNpmTasks('grunt-doccoh');
    grunt.loadNpmTasks('grunt-clean');
    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Default task.
    grunt.registerTask('default', ['doccoh']);

};