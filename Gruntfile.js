module.exports = function(grunt) {
    'use strict';

    // Project configuration.
    grunt.initConfig({
        eslint: {
            amd: {
                src: ['amd/src/*.js'],
                options: {
                    configFile: '.eslintrc',
                    fix: grunt.option('fix')
                }
            }
        },
        
        uglify: {
            amd: {
                files: [{
                    expand: true,
                    cwd: 'amd/src/',
                    src: '*.js',
                    dest: 'amd/build/',
                    ext: '.min.js'
                }],
                options: {
                    sourceMap: true,
                    sourceMapIncludeSources: true
                }
            }
        },

        watch: {
            amd: {
                files: ['amd/src/*.js'],
                tasks: ['amd']
            }
        }
    });

    // Load plugins
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-eslint');

    // Register tasks
    grunt.registerTask('amd', ['eslint:amd', 'uglify:amd']);
    grunt.registerTask('default', ['amd']);
}; 