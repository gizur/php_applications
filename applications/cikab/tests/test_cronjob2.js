
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
var mysql = require("mysql");
var http    = require('http');
var fs = require('fs');
var exec = require('child_process').exec;

// Configs
// =======
// Load / read the AWS credentials
AWS.config.loadFromPath('./_secure/credentials.json');

// Load the configurations
var config  = require('./_secure/config.js').Config;

// If server requires secure connection
// use https
if(config.IS_HTTPS)
    http = require('https');

// Connection to vtiger MySQL db
var connection = mysql.createConnection({
    host: config.DB_HOST,
    user: config.DB_USER,
    password: config.DB_PASSWORD,
    database: config.DB_NAME
});

// Connection to integration MySQL db
var int_connection = mysql.createConnection({
    host: config.DB_I_HOST,
    user: config.DB_I_USER,
    password: config.DB_I_PASSWORD,
    database: config.DB_I_NAME
});

// #### Connect with the databases.
connection.connect();
int_connection.connect();

// Configure Expected Test Result
// ==============================

var messagesInQueueBefore = 0, 
messagesInQueueAfter = 33,
salesOrderIntegrationBefore = 33,
salesOrderIntegrationAfter = 0,
s3FilesBefore = 33,
s3FilesAfter = 66;
// Group all Tests
// ===============
exports.group = {
    // **Check sales orders in integration database before hitting cron job 2**
    "Checking Sales Order In Integration Database before hitting Cron job 2" : function(test){
        int_connection.query("SELECT salesorder_no, " +
            "accountname " +
            "FROM sales_orders " +
            "WHERE set_status IN ('created', 'approved') " +
            "GROUP BY salesorder_no, accountname", function(err, rows, fields) {
                if (err){
                    test.ok(false, "Error fetching sales orders : " + err);
                }else{
                    test.equal(rows.length, salesOrderIntegrationBefore, 
                    "sales order integration: " + rows.length + " & sales order integration before: "
                     + salesOrderIntegrationBefore + " should be equal!");
                }
                test.done();
            });
    },
    
    // **Check Queue before hitting cron job 2**
    "Checking SQS for messages before hitting Cron job 2" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            AttributeNames: new Array('All')
        };
        sqs.client.getQueueAttributes(params, function(err, data) {
            if (!err) {
                var cnt = data.Attributes.ApproximateNumberOfMessages;
                test.equal(cnt, messagesInQueueBefore, 
                    "messages in sqs: " + cnt + " & messages in sqs before: "
                     + messagesInQueueBefore + " should be equal!");
                            
            }else{
                test.ok(false, "Failed due to error : " + err);
            }
            test.done();            
        });
    },
    
    // **Check Amazon S3 before hitting cron job 2**
    "Checking Amazon S3 for SET files before hitting Cron job 2" : function(test){
        var dt = new Date();
        var dateFilter = dt.getFullYear()+''+("0" + (dt.getMonth() + 1)).slice(-2)
        +''+("0" + dt.getDate()).slice(-2); 
        var s3 = new AWS.S3();
        var params = {Bucket: 'gc3-archive',
        Prefix: 'seasonportal/SET-files/SET.GZ.FTP.IN.BST.'+dateFilter}
        s3.client.listObjects(params, function(err, data) {
         if(err) {
             test.ok(false, "Error fetching SET files from S3 : " + err);
         } else {
             var lengthS3;
             if(typeof data.Contents != 'undefined') {
                lengthS3 = data.Contents.length;
                } else { 
                lengthS3=0;
                }
            test.equal(lengthS3, s3FilesBefore, 
                    "SET files in S3 : " + lengthS3 + " & SET files in S3 before: "
                     + s3FilesBefore + " should be equal!");
 
         } 
         test.done();
      });  
    },
    
    // **Hit Cron Job 2**
    "Hitting Cron Job 2" : function(test){
        exec("chmod +x " + config.PHP_BATCHES_2, function (error, stdout, stderr) {
            if (error !== null)
                console.log("Error to execute file " + config.PHP_BATCHES_2 + 
                " \n"+error);
            else{
                exec(config.PHP_BATCHES_2, function (error, stdout, stderr) {
                    if (error !== null){
                        test.ok(false, "Error executing " + config.PHP_BATCHES_2 + 
                        " \n"+error);
                        test.done();
                    }else{
                        test.ok(true, "Executed " + config.PHP_BATCHES_2 + " : " + stdout);
                        test.done();
                    }
                });
            }
        });
    },
    // **Check Sales Orders In Integration Database After hitting Cron job 2**
    "Checking Sales Order In Integration Database After hitting Cron job 2" : function(test){
        int_connection.query("SELECT salesorder_no, " +
            "accountname " +
            "FROM sales_orders " +
            "WHERE set_status IN ('created', 'approved') " +
            "GROUP BY salesorder_no, accountname", function(err, rows, fields) {
                if (err){
                    test.ok(false, "Error fetching sales orders : " + err);
                }else{
                test.equal(rows.length, salesOrderIntegrationAfter, 
                    "sales order integration: " + rows.length + " & sales order integration after: "
                     + salesOrderIntegrationAfter + " should be equal!");
                }
                test.done();
            });
    },
    // **Check SQS messages after hitting cron job 2**
    "Checking SQS for messages" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            AttributeNames: new Array('All')
        };
        sqs.client.getQueueAttributes(params, function(err, data) {
            if (!err) {                
                var cnt = data.Attributes.ApproximateNumberOfMessages;
                test.equal(cnt, messagesInQueueAfter, 
                    "messages in sqs: " + cnt + " & messages in sqs after: "
                     + messagesInQueueAfter + " should be equal!");   
            }else{
                test.ok(false, "Failed due to error : " + err);
            } 
            test.done();           
        });
    },
    
    // **Check Amazon S3 after hitting cron job 2**
    "Checking Amazon S3 for SET files after hitting Cron job 2" : function(test){
        var dt = new Date();
        var dateFilter = dt.getFullYear()+''+("0" + (dt.getMonth() + 1)).slice(-2)
        +''+("0" + dt.getDate()).slice(-2); 
        var s3 = new AWS.S3();
        var params = {Bucket: 'gc3-archive',
        Prefix: 'seasonportal/SET-files/SET.GZ.FTP.IN.BST.'+dateFilter}
        s3.client.listObjects(params, function(err, data) {
         if(err) {
             test.ok(false, "Error fetching SET files from S3 : " + err);
         } else {
             var lengthS3;
             if(typeof data.Contents != 'undefined') {
                lengthS3 = data.Contents.length;
                } else { 
                lengthS3=0;
                }
            test.equal(lengthS3, s3FilesAfter, 
                    "SET files in S3 : " + lengthS3 + " & SET files in S3 after: "
                     + s3FilesAfter + " should be equal!");
 
         } 
         test.done();
      });  
    },
    
    // #### Closing connections
    // 
    // Reason behind putting closing connections in
    // a test is, not to close db connections
    // before all tests get finished.
    "Closing Connections" : function(test){
        connection.destroy();
        int_connection.destroy();
        test.ok(true, "Connections closed.");
        test.done();
    }
};
