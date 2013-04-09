
//------------------------------
//
// 2013-03-08, Prabhat Khera
//
// Copyright Gizur AB 2012
//
// Functions:
//  * Test jobs cron-style
//
// Install with dependencies: npm install 
//
// Documentation is 'docco style' - http://jashkenas.github.com/docco/
//
// Using Google JavaScript Style Guide - 
// http://google-styleguide.googlecode.com/svn/trunk/javascriptguide.xml
//
//------------------------------


"use strict";


// Includes
// =========
var fs = require('fs');
var exec = require('child_process').exec;

// Read 3rd argument from the command line
var environment = process.argv[2];

// Expected environments
var environments = new Array('gc1-ireland', 'gc2-ireland', 'gc3-ireland');

// Check if environment is valid,
// and copy all the config files as per the environment
// entered in the command line.
if(environments.indexOf(environment) >= 0){
    var envPath = '../../../instance-configuration/' + environment + '/';
    var localPath = '../../../';
    
    // List of files need to copy from environment
    var filesToCopy = new Array(
        'applications/cikab/php_batches/php-interfaces/config.inc.php',
        'applications/cikab/tests/_secure/config.js',
        'applications/cikab/tests/_secure/credentials.json'
    );

    filesToCopy.forEach(function(file){
        console.log('Coping : ' + envPath + file);
        fs.createReadStream(envPath + file).pipe(fs.createWriteStream(localPath + file));
    });
}else{
    // If enterd environment is not in the list,
    // throw an error.
    console.log('Please specify the rignt environment : ' + environments.toString());
}