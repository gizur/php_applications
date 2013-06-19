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

    // Default task.
    grunt.registerTask('default', ['doccoh']);

};