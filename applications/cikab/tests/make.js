var fs = require('fs');
var exec = require('child_process').exec;
var config  = require('./_secure/config.js').Config;

var environment = process.argv[2];
var environments = new Array('gc1-ireland', 'gc2-ireland', 'gc3-ireland');

if(environments.indexOf(environment) >= 0){
    var envPath = '../../../instance-configuration/' + environment + '/';
    var localPath = '../../../';
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
    console.log('Please specify the rignt environment : ' + environments.toString());
}