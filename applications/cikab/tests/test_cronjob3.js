
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
var AWS = require('aws-sdk');
var fs = require('fs');
var exec = require('child_process').exec;

// Configs
// =======
// Load / read the AWS credentials
AWS.config.loadFromPath('./_secure/credentials.json');

// Load the configurations
var config  = require('./_secure/config.js').Config;

// Configure Expected Test Result
// ==============================

var messagesInQueueBefore = 0, 
messagesInQueueAfter = 0,
fileInFTPBefore = 3,
fileInFTPAfter = 3;
// Group all Tests
// ===============
exports.group = {
    // #### Check Queue before hitting cron job 3
    //
    "Checking SQS for messages before hitting Cron job 3" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            AttributeNames: new Array('All')
        };
        sqs.client.getQueueAttributes(params, function(err, data) {
            if (!err) {
                var result = false;
                var cnt = data.Attributes.ApproximateNumberOfMessages;
                if(cnt == messagesInQueueBefore)
                    result = true;
                
                test.ok(result, messagesInQueueBefore + " messages expected, " + cnt + " found.");
                test.done();
            }else{
                test.ok(false, "Failed due to error : " + err);
                test.done();
            }            
        });
    },
    // #### Check files before hitting cron job 3
    //
    "Checking Files at FTP before hitting Cron job 3" : function(test){
        fs.readdir(config.LOCAL_FTP_FOLDER, function(err, stats){
            if (err) throw err;
            var result = false;
            var cnt = stats.length;
            if(cnt == fileInFTPBefore)
                result = true;
            test.ok(result, fileInFTPAfter + " expected but found " + cnt + " at " + config.LOCAL_FTP_FOLDER);
            test.done();
        });
    },
    // #### Hit Cron Job 3
    //
    "Hitting Cron Job 3" : function(test){
        exec("chmod +x " + config.PHP_BATCHES_3, function (error, stdout, stderr) {
            if (error !== null)
                console.log("Error in chmod +x " + config.PHP_BATCHES_3);
            else{
                exec(config.PHP_BATCHES_3, function (error, stdout, stderr) {
                    if (error !== null){
                        test.ok(false, "Error executing " + config.PHP_BATCHES_3);
                        test.done();
                    }else{
                        test.ok(true, "Executed : " + config.PHP_BATCHES_3 + " : " + stdout);
                        test.done();
                    }
                });
            }
        });
    },
    // #### Check SQS messages after hitting cron job 3
    //
    "Checking SQS for messages after hitting Cron job 3" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            AttributeNames: new Array('All')
        };
        sqs.client.getQueueAttributes(params, function(err, data) {
            if (!err) {
                
                var result = false;
                var cnt = data.Attributes.ApproximateNumberOfMessages;
                if(cnt == messagesInQueueAfter)
                    result = true;
                
                test.ok(result, messagesInQueueAfter + " messages expected, " + cnt + " found.");
                test.done();
            }else{
                test.ok(false, "Failed due to error : " + err);
                test.done();
            }            
        });
    },
    // #### Check SQS messages after hitting cron job 3
    //
    "Checking Files at FTP after hitting Cron job 3" : function(test){
        fs.readdir(config.LOCAL_FTP_FOLDER, function(err, stats){
            if (err) throw err;
            var result = false;
            var cnt = stats.length;
            if(cnt == fileInFTPAfter)
                result = true;
            test.ok(result, fileInFTPAfter + " expected but found " + cnt + " at " + config.LOCAL_FTP_FOLDER);
            test.done();
        });
    }
};