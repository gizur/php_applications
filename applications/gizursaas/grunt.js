module.exports = function(grunt) {

    "use strict";

    // Project configuration.
    grunt.initConfig({

        // Standard tasks
        //---------------

        pkg: '<json:package.json>',
        test: {
            files: ['test/**/*.js']
        },
        lint: {
            files: ['grunt.js', 'app.js', 'app/**/*.js', 'config/*.js']
        },
        watch: {
            files: '<config:lint.files>',
            tasks: 'default'
        },
        jshint: {
            files: ['grunt.js', 'lib/*.js'],
            options: {
                curly: true,
                eqeqeq: true,
                immed: true,
                latedef: true,
                newcap: true,
                noarg: true,
                sub: true,
                undef: true,
                boss: true,
                eqnull: true,
                browser: true,                
                node: true
            },
            globals: {
                jQuery: true,
                Stapes: true,
                crossroads: true,
                hasher: true,
                $: true
            }
        },
        min: {
            dist: {
                src: ['app.js', 'app/**/*.js'],
                dest: 'dist/built.min.js'
            }
        },

        // Plugin tasks
        //-------------

        doccoh: {
            src: ['*.js', 'node_modules/helpers/*.js']
        },

        clean: {
            folder: "docs/"
        }

    });

    // grunt plugins
    grunt.loadNpmTasks('grunt-exec');
    grunt.loadNpmTasks('grunt-doccoh');
    grunt.loadNpmTasks('grunt-clean');

    // Default task.
    grunt.registerTask('default', 'clean lint test doccoh');


};