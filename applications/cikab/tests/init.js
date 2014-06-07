
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
var fs = require('fs');

// Configs
// =======
// Load / read the AWS credentials
AWS.config.loadFromPath('./_secure/credentials.json');

// Load the configurations
var config  = require('./_secure/config.js').Config;

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

// Group all Tests
// ===============
exports.group = {
    // #### Check sales order count in vTiger
    // 
    "Checking Sales Orders in vTiger ('Created','Approved')" : function(test){
        connection.query("SELECT SO.salesorderid, SO.salesorder_no FROM " +
            "vtiger_salesorder SO " + 
            "WHERE SO.sostatus IN ('Created','Approved') " +
            "", function(err, rows, fields) {
                if (err) throw err;
                
                test.ok(true, "OK");
                test.done();
                
                console.log("Sales Order in vTiger : " + rows.length);
            });
    },
    // #### Check sales order count in integration DB
    // 
    "Checking Sales Order In Integration Database ('created', 'approved')" : function(test){
        int_connection.query("SELECT salesorder_no, " +
            "accountname " +
            "FROM sales_orders " +
            "WHERE set_status IN ('created', 'approved') " +
            "GROUP BY salesorder_no, accountname", function(err, rows, fields) {
                if (err) throw err;
                
                test.ok(true, "OK");
                test.done();
                
                console.log("Sales Order in integrationDB : " + rows.length);                
            });
    },
    // #### Check Queue for messages
    // 
    "Checking Queue" : function(test){
        var sqs = new AWS.SQS();
        var params = {
            QueueUrl: config.Q_URL,
            AttributeNames: new Array('All')
        };
        sqs.client.getQueueAttributes(params, function(err, data) {
            if (!err) {                
                var cnt = data.Attributes.ApproximateNumberOfMessages;
                
                test.ok(true + "OK");
                
                console.log('Messages in Queue : ' + cnt);
            }else{
                test.ok(false, "Failed due to error : " + err);
            }
            test.done();            
        });
    },
    
    // **Check Amazon S3 *
    "Checking Amazon S3 for SET files" : function(test){
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
                console.log("Today processed SET files in S3: "+lengthS3); 
         } 
         test.done();
      });  
    },
    // #### Check files in FTP server
    // 
    "Checking FTP" : function(test){
        fs.readdir(config.LOCAL_FTP_FOLDER, function(err, stats){
            if (err){
                test.ok(false, "Error reading directory : " + err);
            }else{
                test.ok(true, "OK");
                console.log('Files available in ' + config.LOCAL_FTP_FOLDER + ' : ' + stats.length);
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
